<?php

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

CRM_Core_DAO::executeQuery("SET NAMES UTF8 COLLATE utf8_unicode_ci;");
CRM_Core_DAO::executeQuery(CRM_Contact_BAO_Contact::DROP_STRIP_FUNCTION_43);
CRM_Core_DAO::executeQuery(CRM_Contact_BAO_Contact::CREATE_STRIP_FUNCTION_43);
