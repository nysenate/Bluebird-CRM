<?php

/**
 * @file
 * Activity.GetAll file.
 */

/**
 * Activity.GetAll API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_activity_getall_spec(array &$spec) {
  $spec = civicrm_api3('Activity', 'getfields')['values'];
}

/**
 * Activity.GetAll API.
 *
 * This API is similar to “Activity.get”. But if the 'target_contact_id' are
 * more than 25, it returns first 25 only.
 * Also adds a parameter `total_target_contacts`,
 * which contains the total number of target contacts before hiding.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   Activities
 */
function civicrm_api3_activity_getall(array $params) {
  $result = civicrm_api3('Activity', 'get', $params);

  if (!$result['is_error']) {
    _civicrm_api3_activity_getall_limitContacts($result['values'], 'target_contact');
    _civicrm_api3_activity_getall_limitContacts($result['values'], 'assignee_contact');
  }

  return $result;
}

/**
 * Limit contacts.
 *
 * @param array $result
 *   Results from which fields should be limited.
 * @param string $fieldName
 *   Name of the field to limit.
 */
function _civicrm_api3_activity_getall_limitContacts(array &$result, string $fieldName) {
  foreach ($result as &$record) {
    if (empty($record[$fieldName . '_id'])) {
      continue;
    }

    $contactIds = $record[$fieldName . '_id'];
    $limitContactIdsTo = 25;

    if (!empty($contactIds) && count($contactIds) > $limitContactIdsTo) {
      $record['total_' . $fieldName . 's'] = count($contactIds);
      $record[$fieldName . '_id'] = array_slice($record[$fieldName . '_id'], 0, $limitContactIdsTo, TRUE);
      $record[$fieldName . '_name'] = array_slice($record[$fieldName . '_name'], 0, $limitContactIdsTo, TRUE);
      $record[$fieldName . '_sort_name'] = array_slice($record[$fieldName . '_sort_name'], 0, $limitContactIdsTo, TRUE);
    }
  }

}
