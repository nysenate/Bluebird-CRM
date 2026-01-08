<?php

require_once 'newversion.civix.php';
use CRM_Newversion_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function newversion_civicrm_config(&$config) {
  _newversion_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function newversion_civicrm_install() {
  _newversion_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function newversion_civicrm_enable() {
  _newversion_civix_civicrm_enable();
}

function newversion_civicrm_pageRun(&$page) {
  //Civi::log()->debug(__FUNCTION__, ['page' => $page]);

  if (is_a($page, 'CRM_Contact_Page_DashBoard')) {
    if (_newversion_check()) {
      $message = 'This is the first time you have logged into Bluebird since a new version was released. Please take a moment to clear your browser cache to ensure your experience reflects the latest improvements.';
      CRM_Core_Session::setStatus($message, 'New Version Alert', ['expires' => 0]);
    }
  }
}

function _newversion_check() {
  $smarty = CRM_Core_Smarty::singleton();
  $bbVersion = trim($smarty->fetch('CRM/common/bbversion.tpl'));

  $userVersionVal = CRM_Core_DAO::singleValueQuery("
    SELECT value
    FROM civicrm_setting
    WHERE name = 'versioncheck'
      AND contact_id = %1
      AND domain_id = 1
  ", [
    1 => [(int) CRM_Core_Session::getLoggedInContactID(), 'Integer'],
  ]);

  if ($userVersionVal) {
    $userVersion = unserialize($userVersionVal);
  }
  else {
    $userVersion = '';
  }

  /*Civi::log()->debug(__FUNCTION__, [
    'bbVersion' => $bbVersion,
    'userVersion' => $userVersion,
  ]);*/

  if (empty($userVersion) || version_compare($userVersion, $bbVersion, 'lt')) {
    CRM_Core_DAO::singleValueQuery("
      REPLACE INTO civicrm_setting
      (name, value, domain_id, contact_id, created_date, created_id)
      VALUES
      ('versioncheck', %1, 1, %2, %3, %2)
    ", [
      1 => [serialize($bbVersion), 'String'],
      2 => [(int) CRM_Core_Session::getLoggedInContactID(), 'Integer'],
      3 => [date('YmdHis'), 'Timestamp'],
    ]);

    return TRUE;
  }

  return FALSE;
}
