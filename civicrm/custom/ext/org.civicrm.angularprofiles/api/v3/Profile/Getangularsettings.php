<?php

/**
 * Profile.GetAngularSettings API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_profile_getangularsettings_spec(&$spec) {

}

/**
 * Profile.GetAngularSettings API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_profile_getangularsettings($params) {
  $returnValues = array(
    'PseudoConstant' => array(
      'locationType' => CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id'),
      'websiteType' => CRM_Core_PseudoConstant::get('CRM_Core_DAO_Website', 'website_type_id'),
      'phoneType' => CRM_Core_PseudoConstant::get('CRM_Core_DAO_Phone', 'phone_type_id'),
    ),
    'initialProfileList' => civicrm_api('UFGroup', 'get', array(
      'version' => 3,
      'sequential' => 1,
      'is_active' => 1,
      'rowCount' => 1000, // FIXME
    )),
    'contactSubTypes' => CRM_Contact_BAO_ContactType::subTypes(),
    'profilePreviewKey' => CRM_Core_Key::get('CRM_UF_Form_Inline_Preview', TRUE),
  );
  return civicrm_api3_create_success($returnValues, $params, 'Profile', 'getangularsettings');
}

