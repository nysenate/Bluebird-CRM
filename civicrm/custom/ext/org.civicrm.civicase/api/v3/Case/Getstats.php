<?php

/**
 * Case.getstats API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_case_getstats_spec(&$spec) {
  $spec['my_cases'] = array(
    'title' => 'My Cases',
    'description' => 'Limit stats to only my cases',
    'type' => CRM_Utils_Type::T_BOOLEAN,
  );
}

/**
 * Case.getstats API
 *
 * This is provided by the CiviCase extension. It gives statistics for the case dashboard.
 *
 * @param array $params
 * @return array API result
 * @throws API_Exception
 */
function civicrm_api3_case_getstats($params) {
  $query = CRM_Utils_SQL_Select::from('civicrm_case a');
  $query->select(array('a.case_type_id as case_type_id, a.status_id as status_id, COUNT(a.id) as count'));
  if (!empty($params['my_cases'])) {
    \Civi\CCase\Utils::joinOnManager($query);
    $query->where('manager.id = ' . CRM_Core_Session::getLoggedInContactID());
  }
  $query->groupBy('a.case_type_id, a.status_id');
  if (!empty($params['check_permissions'])) {
    $permClauses = array_filter(CRM_Case_BAO_Case::getSelectWhereClause('a'));
    $query->where($permClauses);
  }
  // Filter out deleted contacts
  $query->where("a.id IN (SELECT case_id FROM civicrm_case_contact ccc, civicrm_contact cc WHERE ccc.contact_id = cc.id AND cc.is_deleted = 0)");
  $query->where("a.case_type_id IN (SELECT id FROM civicrm_case_type WHERE is_active = 1)");
  $isDeleted = (int) CRM_Utils_Array::value('is_deleted', $params, 0);
  $query->where('a.is_deleted = ' . $isDeleted);

  $result = $query->execute()->fetchAll();
  $caseTypes = civicrm_api3('CaseType', 'get', array('options' => array('limit' => 0), 'return' => 'id', 'is_active' => 1));
  $tabulated = array_fill_keys(array_keys($caseTypes['values']), array());
  $tabulated['all'] = array();
  foreach ($result as $row) {
    $tabulated[$row['case_type_id']][$row['status_id']] = $row['count'];
    $tabulated['all'] += array($row['status_id'] => 0);
    $tabulated['all'][$row['status_id']] += (int) $row['count'];
  }
  return civicrm_api3_create_success($tabulated, $params, 'Case', 'getstats');
}
