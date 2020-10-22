<?php

use CRM_Civicase_Event_Listener_ActivityFilter as CivicaseActivityFilter;

/**
 * Activity.Getmonthswithactivities API specification
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_activity_Getmonthswithactivities_spec(&$spec) {
  $activityFields = civicrm_api3('Activity', 'getfields', array('api_action' => 'get'));
  $spec = $activityFields['values'];
}

/**
 * Returns list of unique [MM, YYYY] month-year pair with at least an activity
 *
 * @method Activity.Getmonthswithactivities API
 *
 * @param array $params
 * @return array API result with list of months
 * @see civicrm_api3_create_success
 * @throws API_Exception
 */
function civicrm_api3_activity_Getmonthswithactivities($params) {
  $passed_options = $params['options'] ? $params['options'] : [];
  $params = array_merge($params, [
    'sequential' => 1,
    'return' => 'activity_date_time',
    'options' => array_merge($passed_options, [
      'limit' => 0,
    ]),
  ]);

  if ($params['isMyActivitiesFilter']) {
    $activities = get_records_from_activity_getcontactactivities_api($params);
  } else {
    $activities = get_records_from_activity_get_api($params);
  }

  $grouped_activity_dates = [];

  foreach($activities as $activity) {
    list($activity_year, $activity_month) = explode('-', $activity['activity_date_time']);

    $activity_group_index = -1;
    foreach ($grouped_activity_dates as $key => $val) {
      if ($val['year'] === $activity_year && $val['month'] === $activity_month) {
        $activity_group_index = $key;

        break;
      }
    }

    if ($activity_group_index === -1) {
      $grouped_activity_dates[] = array(
        'year' => $activity_year,
        'month' => $activity_month,
        'count' => 1,
      );
    } else {
      $grouped_activity_dates[$activity_group_index]['count'] = $grouped_activity_dates[$activity_group_index]['count'] + 1;
    }
  }

  return civicrm_api3_create_success($grouped_activity_dates, $params, 'Activity', 'getmonthswithactivities');
}

/**
 * Get Activities when My Activity filter is true
 *
 * @param array $params
 * @return array activities
 */
function get_records_from_activity_getcontactactivities_api($params) {
  $contactActivitySelector = new CRM_Civicase_Activity_ContactActivitiesSelector();

  return $contactActivitySelector->getPaginatedActivitiesForContact($params)['values'];
}

/**
 * Get Activities when My Activity filter is not true
 *
 * @param array $params
 * @return array activities
 */
function get_records_from_activity_get_api($params) {
  $options = _civicrm_api3_get_options_from_params($params, FALSE, 'Activity', 'get');
  $sql = CRM_Utils_SQL_Select::fragment();

  if (isset($params['case_filter'])) {
    CivicaseActivityFilter::updateParams($params);
  }

  _civicrm_api3_activity_get_extraFilters($params, $sql);

  if (!empty($options['sort'])) {
    $sort = explode(', ', $options['sort']);

    foreach ($sort as $index => &$sortString) {
      list($sortField, $dir) = array_pad(explode(' ', $sortString), 2, 'ASC');
      if ($sortField == 'is_overdue') {
        $incomplete = implode(',', array_keys(CRM_Activity_BAO_Activity::getStatusesByType(CRM_Activity_BAO_Activity::INCOMPLETE)));
        $sql->orderBy("IF((a.activity_date_time >= NOW() OR a.status_id NOT IN ($incomplete)), 0, 1) $dir", NULL, $index);
        $sortString = '(1)';
      }
    }
    $params['options']['sort'] = implode(', ', $sort);
  }

  if (!empty($options['return']['is_overdue']) && (empty($options['return']['status_id']) || empty($options['return']['activity_date_time']))) {
    $options['return']['status_id'] = $options['return']['activity_date_time'] = 1;
    $params['return'] = array_keys($options['return']);
  }

  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, FALSE, 'Activity', $sql);
}
