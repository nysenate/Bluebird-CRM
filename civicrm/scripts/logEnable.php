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

CRM_Core_Config::singleton();

echo "enable logging and rebuild triggers...\n";
Civi::settings()->set('logging', TRUE);

$logging = new CRM_Logging_Schema;
$logging->fixSchemaDifferences(TRUE);
$logging->addReports();

Civi::service('sql_triggers')->rebuild(NULL, TRUE);

echo "setting logging report permissions...\n";

CRM_Core_DAO::executeQuery("
  UPDATE civicrm_report_instance
  SET permission = 'access CiviReport'
  WHERE report_id LIKE 'logging/contact%';
");
