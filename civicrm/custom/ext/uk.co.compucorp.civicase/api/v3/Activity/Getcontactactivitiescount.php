<?php

/**
 * @file
 * Activity.getcontactactivitiescount file.
 */

/**
 * Defines the fields for the "getcontactactivitiescount" action api.
 *
 * The case id, contact id, and return fields are set to be required.
 *
 * @param array $params
 *   Parameters.
 */
function _civicrm_api3_activity_getcontactactivitiescount_spec(array &$params) {
  _civicrm_api3_activity_get_spec($params);

  $params['contact_id']['api.required'] = TRUE;
  $params['return'] = [
    'api.required' => TRUE,
    'api.default' => "assignee_contact_id",
    'name' => 'return',
    'title' => 'Return fields',
    'description' => 'The "assignee_contact_id" field is required for this action.',
  ];
}

/**
 * Returns the activity count for the given contact.
 *
 * @param array $params
 *   Parameters.
 *
 * @see civicrm_api3_activity_getcontactactivities
 */
function civicrm_api3_activity_getcontactactivitiescount(array $params) {
  $contactActivitySelector = new CRM_Civicase_Activity_ContactActivitiesSelector();

  return $contactActivitySelector->getActivitiesForContactCount($params);
}
