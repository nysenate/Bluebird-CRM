<?php

/**
 * Nyss.Deletetrashed API specification (optional)
 * #14497
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_deletetrashed_spec(&$spec) {
  $spec['modified_date'] = [
    'title' => 'Modified Date',
    'type' => CRM_Utils_Type::T_STRING,
    'description' => 'Date before which we will delete trashed contacts. Accepts strtotime() compatible values.',
    'api.default' => date('Y-m-d H:i:s'),
  ];
  $spec['dryrun'] = [
    'title' => 'Dry Run',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.default' => 1,
  ];
}

/**
 * Nyss.Deletetrashed API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_deletetrashed($params) {
  $params['return'] = TRUE;
  $count = CRM_NYSS_Contact_BAO::processTrashed($params);

  return civicrm_api3_create_success($count, $params, 'Nyss', 'Deletetrashed');
}
