<?php
use CRM_Civicase_ExtensionUtil as E;

/**
 * CaseContactLock.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec
 *   description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_case_contact_lock_create_spec(&$spec) {
  $spec['case_id']['api.required'] = 1;
  $spec['contact_id']['api.required'] = 1;
}

/**
 * CaseContactLock.create API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_case_contact_lock_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * CaseContactLock.delete API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_case_contact_lock_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * CaseContactLock.get API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_case_contact_lock_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * CaseContactLock.lockcases API specification (optional)
 *
 * @param $spec
 */
function _civicrm_api3_case_contact_lock_lockcases_spec(&$spec) {
  $spec = civicrm_api3('CaseContactLock', 'getfields', array('api_action' => 'get'))['values'];

  $spec['case_id']['title'] = 'Case IDs';
  $spec['case_id']['description'] = 'Array of cases for which the locks need to be set.';
  $spec['case_id']['api.required'] = 1;

  $spec['contact_id']['title'] = 'Contact IDs';
  $spec['contact_id']['description'] = 'Array of contacts that need to be locked out of given cases.';
  $spec['contact_id']['api.required'] = 1;

  unset($spec['id']);
}

/**
 * Locks given cases for given contacts.
 *
 * @param $params
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_case_contact_lock_lockcases($params) {
  $cases = CRM_Utils_Array::value('case_id', $params, array());
  if (!is_array($cases) && is_numeric($cases)) {
    $cases = array($cases);
  }

  $contacts = CRM_Utils_Array::value('contact_id', $params, array());
  if (!is_array($contacts) && is_numeric($contacts)) {
    $contacts = array($contacts);
  }

  try {
    $result = CRM_Civicase_BAO_CaseContactLock::createLocks($cases, $contacts);
  } catch (Exception $exception) {
    return civicrm_api3_create_error($exception->getMessage(), $params);
  }

  return civicrm_api3_create_success($result, $params, 'CaseContactLock', 'lockcases');
}
