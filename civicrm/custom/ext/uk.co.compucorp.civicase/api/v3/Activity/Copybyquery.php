<?php

/**
 * @file
 * Activity.CopyByQuery file.
 */

/**
 * Activity.CopyByQuery API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_activity_copybyquery_spec(array &$spec) {
  $spec['case_id'] = [
    'title' => 'Case ID',
    'api.required' => 1,
    'description' => 'Activity ID',
    'type' => CRM_Utils_Type::T_INT,
  ];
  $spec['id'] = [
    'title' => 'Activity ID',
    'description' => 'Activity ID',
  ];
  $spec['params'] = [
    'title' => 'Params for Activity Get',
    'description' => 'Array of parameters for Activity.Get API',
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['subject'] = [
    'title' => 'Activity Subject',
    'description' => 'Activity Subject',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * Activity.Copybyquery API.
 *
 * This API uses copies activities and creates new activities
 * with the case ID sent. The activity ID's to be copied can be sent in the id
 * parameter or they can be fetched with a call to Activity.get using
 * the parameters sent in params to query Activity.get API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_activity_copybyquery(array $params) {
  $activityQueryApiHelper = new CRM_Civicase_APIHelpers_ActivityQueryApi();
  $activityQueryApiHelper->validateParameters($params);
  $genericApiHelper = new CRM_Civicase_APIHelpers_GenericApi();

  if (!empty($params['id'])) {
    $activities = $genericApiHelper->getParameterValue($params, 'id');
  }
  else {
    $activityApiParams = $activityQueryApiHelper->getActivityGetRequestApiParams($params);
    $activities = array_column($genericApiHelper->getEntityValues('Activity', $activityApiParams, ['id']), 'id');
  }

  $activityIds = [];
  foreach ($activities as $activityId) {
    $caseActivityParams = [
      'activityID' => $activityId,
      'caseID' => $params['case_id'],
    ];
    if (!empty($params['subject'])) {
      $caseActivityParams['newSubject'] = $params['subject'];
    }

    $result = CRM_Activity_Page_AJAX::_convertToCaseActivity($caseActivityParams);
    if (empty($result['error_msg']) && !empty($result['newId'])) {
      $activityIds[] = $result['newId'];
    }
  }

  return civicrm_api3_create_success($activityIds, $params, 'Activity', 'copybyquery');
}
