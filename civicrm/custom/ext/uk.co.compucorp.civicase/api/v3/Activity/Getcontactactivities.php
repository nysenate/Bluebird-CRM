<?php

/**
 * Defines the fields for the "getcontactactivities" action api.
 * The case id, contact id, and return fields are set to be required.
 *
 * @param array $params
 */
function _civicrm_api3_activity_getcontactactivities_spec(&$params) {
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
 * Returns the activities for the given contact, limited to a specific case.
 * This action is needed because the default "get" action does not filter out
 * activities that have been delegated to another contact and can't be queried
 * using the API since the condition is too complex for it.
 *
 * @param array $params
 */
function civicrm_api3_activity_getcontactactivities($params) {
  $contactActivitySelector = new CRM_Civicase_Activity_ContactActivitiesSelector();

  return $contactActivitySelector->getPaginatedActivitiesForContact($params);
}
