<?php

/**
 * Nyss.Cleanaddresses API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_cleanaddresses_spec(&$spec) {
  $spec['limit'] = [
    'title' => 'Limit',
    'type' => CRM_Utils_Type::T_INT,
  ];
}

/**
 * Nyss.Cleanaddresses API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_cleanaddresses($params) {
  $result = [
    'processed' => [],
    'errored' => [],
  ];

  $limit = CRM_Utils_Array::value('limit', $params);
  $limitSql = ($limit) ? "LIMIT {$limit}" : '';

  $dao = CRM_Core_DAO::executeQuery("
    SELECT *
    FROM civicrm_address
    WHERE (street_address IS NULL OR street_address = '')
      AND (supplemental_address_1 IS NULL OR supplemental_address_1 = '')
      AND (city IS NULL OR city = '')
      AND (postal_code IS NULL OR postal_code = '')  
    ORDER BY civicrm_address.id DESC
    {$limitSql}
  ");

  while ($dao->fetch()) {
    try {
      civicrm_api3('Address', 'delete', [
        'id' => $dao->id,
      ]);

      $result['processed'][] = $dao->id;
    }
    catch (CiviCRM_API3_Exception $e) {
      $result['errored'][] = $dao->id;
    }
  }

  $result['total_processed'] = count($result['processed']);
  $result['total_errored'] = count($result['errored']);

  return civicrm_api3_create_success(['results' => $result], $params, 'Nyss', 'Cleanaddresses');
}
