<?php

require_once 'recalculate_recipients.civix.php';
use CRM_recalculate_recipients_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function recalculate_recipients_civicrm_config(&$config) {
  _recalculate_recipients_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function recalculate_recipients_civicrm_xmlMenu(&$files) {
  _recalculate_recipients_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function recalculate_recipients_civicrm_install() {
  _recalculate_recipients_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function recalculate_recipients_civicrm_postInstall() {
  _recalculate_recipients_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function recalculate_recipients_civicrm_uninstall() {
  _recalculate_recipients_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function recalculate_recipients_civicrm_enable() {
  _recalculate_recipients_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function recalculate_recipients_civicrm_disable() {
  _recalculate_recipients_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function recalculate_recipients_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _recalculate_recipients_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function recalculate_recipients_civicrm_managed(&$entities) {
  _recalculate_recipients_civix_civicrm_managed($entities);
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
function recalculate_recipients_civicrm_caseTypes(&$caseTypes) {
  _recalculate_recipients_civix_civicrm_caseTypes($caseTypes);
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
function recalculate_recipients_civicrm_angularModules(&$angularModules) {
  _recalculate_recipients_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function recalculate_recipients_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _recalculate_recipients_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function recalculate_recipients_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  //&apiWrappers is an array of wrappers, you can add your(s) with the hook.
  // You can use the apiRequest to decide if you want to add the wrapper (eg. only wrap api.Contact.create)
  if ($apiRequest['entity'] == 'Job' && $apiRequest['action'] == 'process_mailing') {
    $wrappers[] = new CRM_RecalculateRecipients_Wrapper();
  }
}
