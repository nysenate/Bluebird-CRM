<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2012-06-01
 * Description: rebuild triggers
 */

$prog = basename(__FILE__);

require_once 'script_utils.php';
$optList = civicrm_script_init("", array(), False);
if (!$optList) {
  exit(1);
}

drupal_script_init();

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();
//CRM_Core_Error::debug('config',$config);

echo "Rebuilding triggers...\n";
CRM_Core_DAO::triggerRebuild( );
