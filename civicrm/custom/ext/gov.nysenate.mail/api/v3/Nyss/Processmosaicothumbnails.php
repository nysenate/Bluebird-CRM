<?php

/**
 * Nyss.Processmosaicothumbnails API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_Processmosaicothumbnails_spec(&$spec) {
}

/**
 * Nyss.Processmosaicothumbnails API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_Processmosaicothumbnails($params) {
  //Civi::log()->debug(__FUNCTION__, ['params' => $params]);

  try {
    $results = CRM_NYSS_Mail_Utils::createMosaicoThumbnails();
  }
  catch (CiviCRM_API3_Exception $e) {}

  return civicrm_api3_create_success($results, $params, 'Nyss', 'Processmosaicothumbnails');
}
