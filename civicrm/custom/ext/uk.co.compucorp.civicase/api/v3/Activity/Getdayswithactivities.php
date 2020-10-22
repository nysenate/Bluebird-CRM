<?php

/**
 * @file
 * Activity.getdayswithactivities file.
 */

/**
 * Activity.getdayswithactivities API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_activity_getdayswithactivities_spec(array &$spec) {
  $allowed = [
    'activity_date_time', 'activity_status_id', 'case_id', 'activity_type_id',
  ];
  $all = civicrm_api3('Activity', 'getfields', ['api_action' => 'get'])['values'];

  $spec = array_filter($all, function ($name) use ($allowed) {
    return in_array($name, $allowed);
  }, ARRAY_FILTER_USE_KEY);
}

/**
 * Returns list of unique YYYY-MM-DD dates with at least an activity.
 *
 * @param array $params
 *   Parameters to be passed to API call to obtain activities list.
 *
 * @return array
 *   API result with the list of days
 */
function civicrm_api3_activity_getdayswithactivities(array $params) {
  $query = CRM_Utils_SQL_Select::from('civicrm_activity a');
  $query->select(['a.activity_date_time']);

  if (!empty($params['case_id']) && !empty($params['case_filter'])) {
    throw new API_Exception("case_id and case_filter cannot be present at the same time.");
  }

  if (!empty($params['activity_type_id'])) {
    _civicrm_api3_activity_getdayswithactivities_handle_id_param($params['activity_type_id'], 'a.activity_type_id', $query);
  }

  if (!empty($params['activity_date_time'])) {
    _civicrm_api3_activity_getdayswithactivities_handle_id_param($params['activity_date_time'], 'a.activity_date_time', $query);
  }

  if (!empty($params['activity_status_id'])) {
    _civicrm_api3_activity_getdayswithactivities_handle_id_param($params['activity_status_id'], 'a.status_id', $query);
  }

  if (!empty($params['case_id'])) {
    _join_to_case($query, $params['case_id']);
  }

  if (!empty($params['case_filter'])) {
    $case_ids = _get_case_ids($params['case_filter']);

    if (empty($case_ids)) {
      return civicrm_api3_create_success([], $params, 'Activity', 'getdayswithactivities');
    }

    _join_to_case($query, ['IN' => _get_case_ids($params['case_filter'])]);
  }

  $query->groupBy('a.activity_date_time');
  $result = $query->execute()->fetchAll();

  $uniqueDates = array_unique(array_map(function ($row) {
    return explode(' ', $row['activity_date_time'])[0];
  }, $result));

  $params['sequential'] = 1;

  return civicrm_api3_create_success($uniqueDates, $params, 'Activity', 'getdayswithactivities');
}

/**
 * Creates a WHERE clause with the given API parameter and column name.
 *
 * @param string $column
 *   Column.
 * @param CRM_Utils_SQL_Select $query
 *   Query.
 */
function _civicrm_api3_activity_getdayswithactivities_handle_id_param($param, $column, CRM_Utils_SQL_Select $query) {
  $param = is_array($param) ? $param : ['=' => $param];

  $query->where(CRM_Core_DAO::createSQLFilter($column, $param));
}

/**
 * Apply Inner Join for Case related filters.
 *
 * @param CRM_Utils_SQL_Select $query
 *   Query.
 * @param array|string $value
 *   Value of the filter.
 */
function _join_to_case(CRM_Utils_SQL_Select $query, $value) {
  $query->join('ca', "INNER JOIN civicrm_case_activity AS ca ON a.id = ca.activity_id");

  _civicrm_api3_activity_getdayswithactivities_handle_id_param($value, 'ca.case_id', $query);
}

/**
 * Get the list of case ids.
 *
 * @param array $caseParams
 *   Case Parameters.
 *
 * @return array
 *   list of case ids.
 */
function _get_case_ids(array $caseParams) {
  $results = civicrm_api3('Case', 'getcaselist', array_merge([
    'return' => 'id',
    'options' => [limit => 0],
  ], $caseParams))['values'];

  return array_column($results, 'id');
}
