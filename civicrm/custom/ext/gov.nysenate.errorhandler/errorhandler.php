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
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function errorhandler_civicrm_install() {
  _errorhandler_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function errorhandler_civicrm_enable() {
  _errorhandler_civix_civicrm_enable();
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
