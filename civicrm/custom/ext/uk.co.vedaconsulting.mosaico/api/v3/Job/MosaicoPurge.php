<?php
use CRM_Mosaico_ExtensionUtil as E;

/**
 * Job.mosaico_purge API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_job_mosaico_purge_spec(&$spec) {
}

/**
 * Job.mosaico_purge API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_mosaico_purge($params) {
  CRM_Core_DAO::executeQuery('DELETE FROM civicrm_mosaico_msg_template');
  return civicrm_api3_create_success([], $params, 'Job', 'mosaico_purge');
}
