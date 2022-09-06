<?php

require_once 'errorhandler.civix.php';
// phpcs:disable
use CRM_Errorhandler_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function errorhandler_civicrm_config(&$config) {
  _errorhandler_civix_civicrm_config($config);

  // override the error handler
  $config = CRM_Core_Config::singleton();
  $config->fatalErrorHandler = '_errorhandler_civicrm_handler';
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function errorhandler_civicrm_xmlMenu(&$files) {
  _errorhandler_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function errorhandler_civicrm_install() {
  _errorhandler_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function errorhandler_civicrm_postInstall() {
  _errorhandler_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function errorhandler_civicrm_uninstall() {
  _errorhandler_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function errorhandler_civicrm_enable() {
  _errorhandler_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function errorhandler_civicrm_disable() {
  _errorhandler_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function errorhandler_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _errorhandler_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function errorhandler_civicrm_managed(&$entities) {
  _errorhandler_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Add CiviCase types provided by this extension.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function errorhandler_civicrm_caseTypes(&$caseTypes) {
  _errorhandler_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Add Angular modules provided by this extension.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function errorhandler_civicrm_angularModules(&$angularModules) {
  // Auto-add module files from ./ang/*.ang.php
  _errorhandler_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function errorhandler_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _errorhandler_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function errorhandler_civicrm_entityTypes(&$entityTypes) {
  _errorhandler_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function errorhandler_civicrm_themes(&$themes) {
  _errorhandler_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function errorhandler_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function errorhandler_civicrm_navigationMenu(&$menu) {
//  _errorhandler_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _errorhandler_civix_navigationMenu($menu);
//}

/**
 * Custom error handler.
 * This is registered as a callback in hook_civicrm_config().
 *
 * @param array $vars Array with the 'message' and 'code' of the error.
 * @param array $options_overrides
 */
function _errorhandler_civicrm_handler($vars, $options_overrides = []) {
  if (CRM_NYSS_Errorhandler_BAO::handle($vars)) {
    return TRUE;
  }

  // We let CiviCRM display the regular fatal error
  return FALSE;
}
