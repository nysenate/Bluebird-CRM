<?php

/**
 * JobLog.Purge API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_test_Integrationnotify_spec(&$spec) {
}

/**
 * JobLog.Purge API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_test_Integrationnotify($params) {
  $db = 'test.db';
  $type = 'testtype';
  $row = array(
    'val1',
    'val2',
  );
  $paramsTest = array(
    'param1',
    'param2',
    'param3',
  );
  $date = date('Y-m-d H:i:s');

  CRM_NYSS_BAO_Integration_Website::notifyError($db, $type, $row, $paramsTest, $date);

  return civicrm_api3_create_success(1, $params, 'NyssTest', 'IntegrationNotify');
}

