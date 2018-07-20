<?php

/**
 * Case.gettypestats API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_case_gettypestats_spec(&$spec) {
  $spec['my_cases'] = array(
    'title' => 'My Cases',
    'description' => 'Limit stats to only my cases',
    'type' => CRM_Utils_Type::T_BOOLEAN,
  );
  $spec['status_id.grouping'] = array(
    'title' => '',
    'description' => 'Limit stats based on the case status class',
    'type' => CRM_Utils_Type::T_STRING,
  );
}

/**
 * Case.gettypestats API
 *
 * This is provided by the CiviCase extension. It gives statistics for the case dashboard.
 *
 * @param array $params
 * @return array API result
 * @throws API_Exception
 */
function civicrm_api3_case_gettypestats($params) {
  // Fetch metadata (to help with denormalization).
  $caseTypes = civicrm_api3('CaseType', 'get', array(
    'options' => array('limit' => 0),
    'return' => 'title',
  ));
  $caseStatuses = civicrm_api3('OptionValue', 'get', array(
    'option_group_id' => 'case_status',
    'options' => array('limit' => 0),
    'return' => ['value', 'name', 'label', 'grouping'],
  ));
  $caseStatusIdx = CRM_Utils_Array::index(array('value'), $caseStatuses['values']);

  // Get the stats.
  $query = CRM_Utils_SQL_Select::from('civicrm_case a');
  $query->select(array('a.case_type_id as case_type_id, a.status_id as status_id, COUNT(a.id) as count'));
  $query->select('avg(datediff(coalesce(a.end_date, date(now())), a.start_date)) AS average_duration');
  if (!empty($params['my_cases'])) {
    \Civi\CCase\Utils::joinOnManager($query);
    $query->where('manager.id = ' . CRM_Core_Session::getLoggedInContactID());
  }
  if (!empty($params['status_id.grouping'])) {
    $statusesByGrouping = CRM_Utils_Array::index(array('grouping', 'value'), $caseStatuses['values']);
    $query->where('a.status_id IN (#statuses)', array(
      'statuses' => array_keys($statusesByGrouping[$params['status_id.grouping']]),
    ));
  }
  $query->groupBy('a.case_type_id, a.status_id');
  if (!empty($params['check_permissions'])) {
    $permClauses = array_filter(CRM_Case_BAO_Case::getSelectWhereClause('a'));
    $query->where($permClauses);
  }
  // Filter out deleted contacts
  $query->where("a.id IN (SELECT case_id FROM civicrm_case_contact ccc, civicrm_contact cc WHERE ccc.contact_id = cc.id AND cc.is_deleted = 0)");
  $isDeleted = (int) CRM_Utils_Array::value('is_deleted', $params, 0);
  $query->where('a.is_deleted = ' . $isDeleted);

  // Denormalize the stats data.
  $results = array();
  foreach ($query->execute()->fetchAll() as $row) {
    $results[] = array(
      'case_type_id' => $row['case_type_id'],
      'case_type_id.title' => $caseTypes['values'][$row['case_type_id']]['title'],
      'status_id' => $row['status_id'],
      'status_id.label' => $caseStatusIdx[$row['status_id']]['label'],
      'status_id.grouping' => $caseStatusIdx[$row['status_id']]['grouping'],
      'count' => $row['count'],
      'average_duration' => $row['average_duration'],
    );
  }

  return civicrm_api3_create_success($results, $params, 'Case', 'gettypestats');
}
