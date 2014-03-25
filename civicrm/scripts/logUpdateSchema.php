<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2013-05-31
 * Description: Update logging table schema
 */

$prog = basename(__FILE__);

require_once 'script_utils.php';
$optList = civicrm_script_init("d", array('debug'), FALSE);

drupal_script_init();
$bbcfg = get_bluebird_instance_config();

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

echo "updating logging table schemas...\n";
require_once 'CRM/Logging/Schema.php';
$logging = new CRM_Logging_Schema;
//CRM_Core_Error::debug_var('logging', $logging);

echo "fixing schema differences... \n";
$logging->fixSchemaDifferencesForAll();

echo "checking for existence of log_job_id... \n";
$logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];
$dao = CRM_Core_DAO::executeQuery("
  SELECT TABLE_NAME
  FROM   INFORMATION_SCHEMA.TABLES
  WHERE  TABLE_SCHEMA = '{$logDB}'
  AND    TABLE_TYPE = 'BASE TABLE'
  AND    TABLE_NAME LIKE 'log_civicrm_%'
");
while ($dao->fetch()) {
  $table = $dao->TABLE_NAME;
  $cols = CRM_Core_DAO::executeQuery("SHOW COLUMNS FROM $table FROM $logDB");

  $logJobFound = FALSE;
  while ( $cols->fetch() ) {
    if ( $cols->Field == 'log_job_id' ) {
      $logJobFound = TRUE;
      break;
    }
  }

  if ( !$logJobFound ) {
    if ( $optList['debug'] ) {
      echo "log_job_id column not found for: $table \n";
    }

    $sql = "ALTER TABLE {$logDB}.{$table} ADD log_job_id VARCHAR(64)";
    CRM_Core_DAO::executeQuery($sql);
  }
}

echo "finished updating schema. \n\n";
