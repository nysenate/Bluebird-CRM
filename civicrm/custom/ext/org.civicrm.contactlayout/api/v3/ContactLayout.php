<?php
use CRM_Contactlayout_ExtensionUtil as E;

/**
 * ContactLayout.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_contact_layout_create_spec(&$spec) {
  $spec['label']['api.required'] = 1;
  $spec['blocks']['api.required'] = 1;
}

/**
 * ContactLayout.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_contact_layout_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * ContactLayout.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_contact_layout_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * ContactLayout.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_contact_layout_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
