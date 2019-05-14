<?php

$_MigrateMsgsDebug = FALSE;

/**
 * WebIntegration.MigrateMsgs API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_web_integration_migratemsgs_spec(&$spec) {
  $spec['action'] = [
    'title' => 'Action',
    'options' => ['migrate' => 'Migrate', 'purge' => 'Purge', 'migratepurge' => 'Migrate and Purge'],
    'api.required' => TRUE,
  ];

  $spec['type'] = [
    'title' => 'Record Type',
    'options' => [
      'nyss_directmsg' => 'Direct Messages',
      'nyss_contextmsg' => 'Contextual Messages',
      'both' => 'Both Direct and Contextual Messages',
    ],
    'api.required' => TRUE,
  ];

  $spec['limit'] = [
    'title' => 'Limit',
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['debug'] = [
    'title' => 'Debug',
    'options' => [1 => 'Enabled', 0 => 'Disabled'],
  ];
}

/**
 * WebIntegration.MigrateMsgs API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_web_integration_migratemsgs($params) {
  global $_MigrateMsgsDebug;

  $result = [];
  $action = CRM_Utils_Array::value('action', $params);
  $limit = CRM_Utils_Array::value('limit', $params);
  $type = CRM_Utils_Array::value('type', $params);
  $_MigrateMsgsDebug = CRM_Utils_Array::value('debug', $params);
  _wimm_Debug('params', $params);

  switch ($action) {
    case 'migrate':
      if ($type == 'both') {
        $result['nyss_directmsg.migrate'] = _wimm_Migrate($limit, 'nyss_directmsg');
        $result['nyss_contextmsg.migrate'] = _wimm_Migrate($limit, 'nyss_contextmsg');
      }
      else {
        $result[$type] = _wimm_Migrate($limit, $type);
      }

      break;

    case 'purge':
      if ($type == 'both') {
        $result['nyss_directmsg.purge'] = _wimm_Purge($limit, 'nyss_directmsg');
        $result['nyss_contextmsg.purge'] = _wimm_Purge($limit, 'nyss_contextmsg');
      }
      else {
        $result[$type] = _wimm_Purge($limit, $type);
      }

      break;

    case 'migratepurge':
      $result['nyss_directmsg.migrate'] = _wimm_Migrate($limit, 'nyss_directmsg');
      $result['nyss_contextmsg.migrate'] = _wimm_Migrate($limit, 'nyss_contextmsg');
      $result['nyss_directmsg.purge'] = _wimm_Purge($limit, 'nyss_directmsg');
      $result['nyss_contextmsg.purge'] = _wimm_Purge($limit, 'nyss_contextmsg');

      break;

    default:
      throw new API_Exception('Invalid Action');
  }

  return civicrm_api3_create_success(['processed' => $result], $params, 'WebIntegration', 'MigrateMsgs');
}

function _wimm_Debug($text, $var) {
  global $_MigrateMsgsDebug;

  if ($_MigrateMsgsDebug) {
    CRM_Core_Error::debug_var($text, $var, TRUE, TRUE, 'webint_migratemsgs');
  }
}

function _wimm_Migrate($limit, $type) {
  $limitSql = (!empty($limit)) ? "LIMIT {$limit}" : '';

  $typeMap = _wimm_TypeMap();
  _wimm_Debug('params', $typeMap);

  //get notes that have not yet been migrated
  $dao = CRM_Core_DAO::executeQuery("
    SELECT civicrm_note.*
    FROM civicrm_note
    LEFT JOIN civicrm_activity
      ON activity_type_id = %1
      AND source_record_id = civicrm_note.id
    WHERE entity_table = %2
      AND civicrm_activity.id IS NULL
    ORDER BY civicrm_note.id
    {$limitSql}
  ", [
    1 => [$typeMap[$type], 'Positive'],
    2 => [$type, 'String'],
  ]);

  $i = 0;
  while ($dao->fetch()) {
    _wimm_Debug('$dao', $dao);

    //build activity params
    $params = array(
      'activity_type_id' => $typeMap[$type],
      'source_contact_id' => $dao->entity_id,
      'target_id' => $dao->entity_id,
      'subject' => $dao->subject,
      'activity_date_time' => $dao->modified_date,
      'details' => $dao->note,
      'status_id' => 'Completed',
      'source_record_id' => $dao->id,
    );

    try {
      $result = civicrm_api3('activity', 'create', $params);
      _wimm_Debug('$result', $result);

      $i ++;
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug('_wimm_Migrate', [
        '$e' => $e,
        '$dao' => $dao,
        '$params' => $params,
      ]);
    }
  }

  return $i;
}

function _wimm_Purge($limit, $type) {
  $limitSql = (!empty($limit)) ? "LIMIT {$limit}" : '';

  $typeMap = _wimm_TypeMap();
  _wimm_Debug('params', $typeMap);

  //only get notes that *have* been migrated
  $dao = CRM_Core_DAO::executeQuery("
    SELECT civicrm_note.*
    FROM civicrm_note
    LEFT JOIN civicrm_activity
      ON activity_type_id = %1
      AND source_record_id = civicrm_note.id
    WHERE entity_table = %2
      AND civicrm_activity.id IS NOT NULL
    ORDER BY civicrm_note.id
    {$limitSql}
  ", [
    1 => [$typeMap[$type], 'Positive'],
    2 => [$type, 'String'],
  ]);

  $i = 0;
  while ($dao->fetch()) {
    _wimm_Debug('$dao', $dao);

    try {
      $result = civicrm_api3('note', 'delete', [
        'id' => $dao->id,
      ]);
      _wimm_Debug('$result', $result);

      $i ++;
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug('_wimm_Migrate', [
        '$e' => $e,
        '$dao' => $dao,
      ]);
    }
  }

  return $i;
}

function _wimm_TypeMap() {
  return [
    'nyss_directmsg' =>
      CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'website_direct_message'),
    'nyss_contextmsg' =>
      CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'website_contextual_message'),
  ];
}
