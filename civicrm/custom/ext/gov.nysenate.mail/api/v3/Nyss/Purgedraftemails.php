<?php

/**
 * Nyss.Purgedraftemails API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_Purgedraftemails_spec(&$spec) {
  $spec['dryrun'] = [
    'title' => ts('Dry run?'),
    'api.default' => 1,
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'options' => [1 => 'Yes', 0 => 'No'],
  ];
  $spec['modified'] = [
    'title' => 'Modified dates older than',
    'description' => 'Enter a fixed date or strtotime-compatible relative date (e.g. -1 year)',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1,
  ];
}

/**
 * Nyss.Purgedraftemails API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_Purgedraftemails($params) {
  //Civi::log()->debug(__FUNCTION__, ['params' => $params]);

  $mailingIds = [];

  try {
    $mailings = civicrm_api3('Mailing', 'get', [
      'sequential' => 1,
      'scheduled_date' => ['IS NULL' => 1],
      'modified_date' => ['<=' => date('y-m-d', strtotime($params['modified']))],
      'options' => ['limit' => 0],
    ]);

    foreach ($mailings['values'] as $mailing) {
      $mailingIds[] = $mailing['id'];

      if (empty($params['dryrun'])) {
        civicrm_api3('Mailing', 'delete', ['id' => $mailing['id']]);
      }
    }
  }
  catch (CiviCRM_API3_Exception $e) {}

  return civicrm_api3_create_success(['count' => count($mailingIds), 'ids' => $mailingIds], $params, 'Nyss', 'Purgedraftemails');
}
