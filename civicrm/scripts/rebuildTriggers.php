<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2012-06-01
 * Description: rebuild triggers; optionally check for total number of existing triggers
 */

//define the expected number of triggers
define('TRIGCOUNT', 401);

$prog = basename(__FILE__);

require_once 'script_utils.php';
$optList = civicrm_script_init("c", array('check'), FALSE);
if (!$optList) {
  exit(1);
}

drupal_script_init();

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();
//CRM_Core_Error::debug('config',$config);

if ($optList['check']) {
  $sql = "SHOW TRIGGERS;";
  $trg = CRM_Core_DAO::executeQuery($sql);
  if ( $trg->N != TRIGCOUNT ) {
    echo "WARNING: This instance appears to be missing some triggers. Please run this script with no options to rebuild the triggers.\n";
    echo "Triggers counted: {$trg->N}\nExpected total: ".TRIGCOUNT."\n";
  }
  else {
    echo "It appears this instance has all triggers correctly built.\n";
  }
}
else {
  echo "Rebuilding triggers...\n";
  CRM_Core_DAO::executeQuery("SET NAMES UTF8 COLLATE utf8_unicode_ci;");
  Civi::service('sql_triggers')->rebuild(NULL, TRUE);
}
