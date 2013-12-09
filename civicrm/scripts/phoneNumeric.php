<?php

/**
 * Author:      Brian Shaughnessy
 * Date:        2013-09-13
 * Description: create phone numeric function and update phone numbers
 */

$prog = basename(__FILE__);

require_once 'script_utils.php';
$optList = civicrm_script_init("", array(), False);

drupal_script_init();

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

CRM_Core_DAO::executeQuery(CRM_Contact_BAO_Contact::DROP_STRIP_FUNCTION_43);
CRM_Core_DAO::executeQuery(CRM_Contact_BAO_Contact::CREATE_STRIP_FUNCTION_43);
CRM_Core_DAO::executeQuery("UPDATE civicrm_phone SET phone_numeric = civicrm_strip_non_numeric(phone)");

echo "Finished creating phone_numeric UDF and updating phone records.\n";
