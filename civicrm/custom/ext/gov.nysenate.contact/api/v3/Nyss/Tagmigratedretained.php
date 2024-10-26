<?php

/**
 * Nyss.Tagmigratedretained API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_tagmigratedretained_spec(&$spec) {
  $spec['limit'] = [
    'title' => 'Limit',
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['dest'] = [
    'title' => 'Destination District ID',
    'type' => CRM_Utils_Type::T_INT,
  ];

  $spec['dryrun'] = [
    'title' => 'Dry Run',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.default' => 1,
  ];
}

/**
 * Nyss.Tagmigratedretained API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_tagmigratedretained($params) {
  $results = $tables = [];

  $bb = get_bluebird_instance_config();
  //Civi::log()->debug(__FUNCTION__, ['bb' => $bb]);

  if (!empty($params['dest'])) {
    $destTbl = 'migrate_'.$bb['district'].'_'.$params['dest'];

    //verify table exists
    $exists = CRM_Core_DAO::singleValueQuery("
      SELECT TABLE_NAME
      FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA LIKE %1
        AND TABLE_NAME LIKE '{$destTbl}'
    ", [
      1 => [$bb['civicrm_db_name'], 'String'],
    ]);

    if ($exists) {
      $tables[] = $destTbl;
    }
    else {
      throw new CRM_Core_Exception('Requested migration table does not exist.');
    }
  }
  else {
    $dao = CRM_Core_DAO::executeQuery("
      SELECT TABLE_NAME
      FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA LIKE %1
        AND TABLE_NAME LIKE 'migrate_%'
    ", [
      1 => [$bb['civicrm_db_name'], 'String'],
    ]);

    while ($dao->fetch()) {
      $tables[] = $dao->TABLE_NAME;
    }
  }

  $limitSql = (!empty($params['limit'])) ? "LIMIT {$params['limit']}" : '';

  foreach ($tables as $table) {
    list($ignore, $source, $dest) = explode('_', $table);
    /*Civi::log()->debug(__FUNCTION__, [
      'table' => $table,
      'source' => $source,
      'dest' => $dest,
    ]);*/

    $tagId = _nyss_tagmigratedretained_getTag($dest, $params['dryrun']);

    $dao = CRM_Core_DAO::executeQuery("
      SELECT migration.*
      FROM {$table} migration
      JOIN civicrm_contact c
        ON migration.contact_id = c.id
        AND c.is_deleted = 0
      {$limitSql}
    ");

    while ($dao->fetch()) {
      if (empty($params['dryrun'])) {
        \Civi\Api4\EntityTag::create(FALSE)
          ->addValue('entity_table', 'civicrm_contact')
          ->addValue('entity_id', $dao->contact_id)
          ->addValue('tag_id', $tagId)
          ->execute();
      }

      $results[$dest]['ids'][] = $dao->contact_id;
    }
  }

  foreach ($results as $dest => $result) {
    $results[$dest]['count'] = count($result['ids']);

    //if not running in dryrun, don't list IDs
    if (empty($params['dryrun'])) {
      unset($results[$dest]['ids']);
    }
  }

  //Civi::log()->debug(__FUNCTION__, ['results' => $results]);
  return civicrm_api3_create_success(['results' => $results], $params, 'Nyss', 'Tagmigratedretained');
}

function _nyss_tagmigratedretained_getTag($dest, $dryrun = 1) {
  $tagName = "2022 Redistricting Migration to {$dest} (retained)";

  if ($dryrun) {
    return $tagName;
  }

  $tag = \Civi\Api4\Tag::get(FALSE)
    ->addSelect('id')
    ->addWhere('name', '=', $tagName)
    ->addWhere('parent_id:name', '=', 'Keywords')
    ->execute()
    ->first();

  if (empty($tag['id'])) {
    $tag = \Civi\Api4\Tag::create(FALSE)
      ->addValue('name', $tagName)
      ->addValue('parent_id.name', 'Keywords')
      ->execute()
      ->first();
  }

  //Civi::log()->debug(__FUNCTION__, ['$tag' => $tag]);
  return $tag['id'];
}
