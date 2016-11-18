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
$config = CRM_Core_Config::singleton();
$config->logging = TRUE;

echo "enable logging and rebuild triggers...\n";
require_once 'CRM/Logging/Schema.php';
$logging = new CRM_Logging_Schema;
$logging->enableLogging();

//set logging value in domain
echo "setting logging flag in setting record...\n";
CRM_Core_DAO::executeQuery("
  UPDATE civicrm_setting
  SET value = 'i:1;'
  WHERE name = 'logging';
");

echo "setting logging report permissions...\n";
CRM_Core_DAO::executeQuery("
  UPDATE civicrm_report_instance
  SET permission = 'access CiviReport'
  WHERE report_id LIKE 'logging/contact%';
");
