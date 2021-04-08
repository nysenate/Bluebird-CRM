<?php

/**
 * @file
 * Case.getstats API file.
 */

use Civi\CCase\Utils;

/**
 * Case.getstats API specification.
 *
 * @param array $spec
 *   description of fields supported by this API call.
 */
function _civicrm_api3_case_getstats_spec(array &$spec) {
  $spec['case_manager'] = [
    'title' => 'Case Manager',
    'description' => 'Contact id of the case manager',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['contact_involved'] = [
    'title' => 'Contact Involved',
    'description' => 'Id of the contact involved as case roles',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['has_activities_for_involved_contact'] = [
    'title' => 'Has Activities For Involved Contact',
    'description' => "Has activities created by, assigned to, or targeting the involved contact",
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['my_cases'] = [
    'title' => 'My Cases',
    'description' => 'Limit stats to only my cases',
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
  $spec['case_type_id.case_type_category'] = [
    'title' => 'Case Type Category',
    'description' => 'The Case Type Category ID or Name to use as filter',
    'type' => CRM_Utils_Type::T_STRING,
    'pseudoconstant' => [
      'optionGroupName' => 'case_type_categories',
      'optionEditPath' => 'civicrm/admin/options/case_type_categories',
    ],
  ];
}

/**
 * Case.getstats API.
 *
 * This is provided by the CiviCase extension. It gives statistics for
 * the case dashboard.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result.
 *
 * @throws API_Exception
 */
function civicrm_api3_case_getstats(array $params) {
  $query = CRM_Utils_SQL_Select::from('civicrm_case a');
  $query->select(['a.case_type_id as case_type_id, a.status_id as status_id, COUNT(a.id) as count']);

  $caseTypes = _civicrm_api3_case_getstats_get_case_types($params, $query);

  if (!empty($params['my_cases'])) {
    Utils::joinOnRelationship($query, 'manager');
    $query->where('manager.id = ' . CRM_Core_Session::getLoggedInContactID());
  }

  if (!empty($params['case_manager'])) {
    CRM_Civicase_APIHelpers_CasesByManager::filter($query, $params['case_manager']);
  }

  if (!empty($params['contact_involved'])) {
    CRM_Civicase_APIHelpers_CaseDetails::handleContactInvolvedFilters($query, $params);
  }

  $query->groupBy('a.case_type_id, a.status_id');
  if (!empty($params['check_permissions'])) {
    $permClauses = array_filter(CRM_Case_BAO_Case::getSelectWhereClause('a'));
    $query->where($permClauses);
  }
  // Filter out deleted contacts.
  $query->where("a.id IN (SELECT case_id FROM civicrm_case_contact ccc, civicrm_contact cc WHERE ccc.contact_id = cc.id AND cc.is_deleted = 0)");
  $isDeleted = (int) CRM_Utils_Array::value('is_deleted', $params, 0);
  $query->where('a.is_deleted = ' . $isDeleted);

  $result = $query->execute()->fetchAll();
  $tabulated = array_fill_keys(array_keys($caseTypes['values']), []);
  $tabulated['all'] = [];
  foreach ($result as $row) {
    $tabulated[$row['case_type_id']][$row['status_id']] = $row['count'];
    $tabulated['all'] += [$row['status_id'] => 0];
    $tabulated['all'][$row['status_id']] += (int) $row['count'];
  }

  return civicrm_api3_create_success($tabulated, $params, 'Case', 'getstats');
}

/**
 * Returns the filtered case types according to the given parameters.
 *
 * @param array $params
 *   Filters related to the case type.
 * @param CRM_Utils_SQL_Select $query
 *   A query object that will be updated depending on the returned case types.
 *
 * @return array
 *   An API response containing the filtered case types.
 */
function _civicrm_api3_case_getstats_get_case_types(array $params, CRM_Utils_SQL_Select $query) {
  $isActiveCaseType = isset($params['case_type_id.is_active'])
    ? $params['case_type_id.is_active']
    : '1';
  $caseTypesParams = [
    'options' => ['limit' => 0],
    'return' => 'id',
    'is_active' => $isActiveCaseType,
  ];

  $caseTypes = [];

  if (!empty($params['case_type_id.case_type_category'])) {
    $caseTypesParams['case_type_category'] = _civicrm_api3_case_get_case_category_from_params($params);
    $caseTypes = civicrm_api3('CaseType', 'get', $caseTypesParams);
    _civicrm_api3_case_add_case_category_query_filter($query, $caseTypes);
  }

  if (!empty($params['case_type_id'])) {
    $caseTypesParams['id'] = $params['case_type_id'];
    $caseTypes = civicrm_api3('CaseType', 'get', $caseTypesParams);

    if (!empty($caseTypesParams['id']['IS NULL'])) {
      $caseTypes = ['values' => ['0' => ['id' => 'IS NULL']]];
    }

    _civicrm_api3_case_add_case_category_query_filter($query, $caseTypes);
  }

  if (empty($caseTypes)) {
    return civicrm_api3('CaseType', 'get', $caseTypesParams);
  }
  else {
    return $caseTypes;
  }
}

/**
 * Builds the query for the case category filter.
 *
 * @param object $query
 *   SQL query object.
 * @param array $caseTypes
 *   The CaseType array.
 */
function _civicrm_api3_case_add_case_category_query_filter($query, array $caseTypes) {
  $caseTypeIds = array_column($caseTypes['values'], 'id');

  if (empty($caseTypeIds)) {
    return;
  }

  $param = ['IN' => $caseTypeIds];

  if (isset($caseTypeIds[0]) && $caseTypeIds[0] === 'IS NULL') {
    $param = ['IS NULL' => []];
  }

  $query->join('ct', 'JOIN civicrm_case_type AS ct ON ct.id = a.case_type_id');
  $query->where(CRM_Core_DAO::createSQLFilter('ct.id', $param));
}

/**
 * Gets the case categories from the $params array.
 *
 * Currently, we only support the IN operator for passing array of categories.
 * it would not make sense to support operators like >= and <.
 *
 * @param array $params
 *   The $params array passed to the API.
 *
 * @return array|string
 *   Return Value.
 */
function _civicrm_api3_case_get_case_category_from_params(array $params) {
  $caseTypeCategory = $params['case_type_id.case_type_category'];
  if (is_array($caseTypeCategory) && !array_key_exists('IN', $caseTypeCategory)) {
    throw new InvalidArgumentException('The case_type_category parameter only supports the IN operator');
  }

  return $caseTypeCategory;
}
