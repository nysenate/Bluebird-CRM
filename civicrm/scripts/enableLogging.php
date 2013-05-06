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
echo "setting logging flag in domain record...\n";
CRM_Core_DAO::executeQuery("
  UPDATE civicrm_domain
  SET config_backend = REPLACE( config_backend, 'logging\';s:1:\'0', 'logging\';s:1:\'1' )
  WHERE id = 1;
");
CRM_Core_DAO::executeQuery('
  UPDATE civicrm_domain
  SET config_backend = REPLACE(config_backend, "logging\";s:1:\"0", "logging\";s:1:\"1")
  WHERE id = 1;
');
CRM_Core_DAO::executeQuery('
  UPDATE civicrm_domain
  SET config_backend = REPLACE(config_backend, "logging\";i:0", "logging\";s:1:\"1")
  WHERE id = 1;
');
