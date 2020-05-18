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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function newversion_civicrm_xmlMenu(&$files) {
  _newversion_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function newversion_civicrm_postInstall() {
  _newversion_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function newversion_civicrm_uninstall() {
  _newversion_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function newversion_civicrm_enable() {
  _newversion_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function newversion_civicrm_disable() {
  _newversion_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function newversion_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _newversion_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function newversion_civicrm_managed(&$entities) {
  _newversion_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function newversion_civicrm_caseTypes(&$caseTypes) {
  _newversion_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function newversion_civicrm_angularModules(&$angularModules) {
  _newversion_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function newversion_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _newversion_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function newversion_civicrm_entityTypes(&$entityTypes) {
  _newversion_civix_civicrm_entityTypes($entityTypes);
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

  $userVersion = CRM_Core_DAO::singleValueQuery("
    SELECT value
    FROM civicrm_setting
    WHERE name = 'versioncheck'
      AND contact_id = %1
      AND domain_id = 1
  ", [
    1 => [(int) CRM_Core_Session::getLoggedInContactID(), 'Integer'],
  ]);
  /*Civi::log()->debug(__FUNCTION__, [
    'bbVersion' => $bbVersion,
    'userVersion' => $userVersion,
  ]);*/

  if (empty($userVersion) || version_compare($userVersion, $bbVersion, 'lt')) {
    CRM_Core_DAO::singleValueQuery("
      REPLACE INTO civicrm_setting
      (name, value, domain_id, contact_id, created_date, created_id)
      VALUES
      ('versioncheck', %1, 1, %2, %3, 1)
    ", [
      1 => [$bbVersion, 'String'],
      2 => [(int) CRM_Core_Session::getLoggedInContactID(), 'Integer'],
      3 => [date('YmdHis'), 'Timestamp'],
    ]);

    return TRUE;
  }

  return FALSe;
}
