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
$config->logging = 1;

//set logging value in settings
require_once "CRM/Core/BAO/Setting.php";
$params = array('logging' => 1);
CRM_Core_BAO_Setting::add($params);

echo "Enable Logging...\n";
require_once 'CRM/Logging/Schema.php';
$logging = new CRM_Logging_Schema;
$logging->enableLogging();

//CRM_Core_Error::debug('logging',$logging);

echo "Rebuild Triggers...\n";
CRM_Core_DAO::triggerRebuild( );

