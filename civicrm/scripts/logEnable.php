<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2012-04-13
 * Description: Enable logging and rebuild triggers. Implemented with v1.3.5
 */

$prog = basename(__FILE__);

require_once 'script_utils.php';
$optList = civicrm_script_init("", array(), False);

drupal_script_init();

require_once 'CRM/Core/Config.php';
CRM_Core_Config::singleton();

echo "enable logging and rebuild triggers...\n";

require_once 'CRM/Logging/Schema.php';
$logging = new CRM_Logging_Schema;
$logging->enableLogging();

try {
  civicrm_api3('setting', 'create', array('logging' => TRUE));
}
catch (CiviCRM_API3_Exception $e) {}

Civi::service('sql_triggers')->rebuild(NULL, TRUE);

echo "setting logging report permissions...\n";

CRM_Core_DAO::executeQuery("
  UPDATE civicrm_report_instance
  SET permission = 'access CiviReport'
  WHERE report_id LIKE 'logging/contact%';
");
