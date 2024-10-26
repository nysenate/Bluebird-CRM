<?php

/**
 * @file
 * Activity.DeleteByQuery file.
 */

/**
 * Activity.DeleteByQuery API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_activity_deletebyquery_spec(array &$spec) {
  $spec['id'] = [
    'title' => 'Activity ID',
    'description' => 'Activity ID',
  ];
  $spec['params'] = [
    'title' => 'Params for Activity Get',
    'description' => 'Array of parameters for Activity.Get API',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * Activity.DeletByQuery API.
 *
 * This API uses deletes activities using the Activity.Delete API
 * The activity ID's can be sent in the id parameter or they can be
 * fetched with a call to Activity.get using the parameters sent in params
 * to query Activity.get API and then delete the fetched ID's.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_activity_deletebyquery(array $params) {
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
    try {
      civicrm_api3('Activity', 'delete', [
        'id' => $activityId,
      ]);
      $activityIds[] = $activityId;
    } catch (Exception $e) {
    }

  }

  return civicrm_api3_create_success($activityIds, $params, 'Activity', 'copybyquery');
}
