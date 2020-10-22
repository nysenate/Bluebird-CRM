<?php

/**
 * Civi v4.6 does not provide all the API's we would need to define
 * FlexMailer in an extension, but you can patch core to simulate them.
 * These define()s tell core to enable any such hacks (if available).
 */

define('CIVICRM_FLEXMAILER_HACK_DELIVER', '\Civi\FlexMailer\FlexMailer::createAndRun');
define('CIVICRM_FLEXMAILER_HACK_SENDABLE', '\Civi\FlexMailer\Validator::createAndRun');
define('CIVICRM_FLEXMAILER_HACK_REQUIRED_TOKENS', 'call://civi_flexmailer_required_tokens/getRequiredTokens');
//define('CIVICRM_FLEXMAILER_HACK_SERVICES', '\Civi\FlexMailer\Services::registerServices');
//define('CIVICRM_FLEXMAILER_HACK_LISTENERS', '\Civi\FlexMailer\Services::registerListeners');

require_once 'flexmailer.civix.php';

use CRM_Flexmailer_ExtensionUtil as E;

/**
 * Define an autoloader for FlexMailer.
 *
 * FlexMailer uses the namespace 'Civi\FlexMailer', but the
 * autoloader in Civi v4.6 doesn't support this, so we provide
 * our own autoloader.
 *
 * TODO: Whenever v4.6 dies, remove this file and define the
 * autoloader in info.xml
 *
 * @link http://www.php-fig.org/psr/psr-4/examples/
 */
function _flexmailer_autoload($class) {
  $prefix = 'Civi\\FlexMailer\\';
  $base_dir = __DIR__ . '/src/';
  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }
  $relative_class = substr($class, $len);
  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
  if (file_exists($file)) {
    require $file;
  }
}

spl_autoload_register('_flexmailer_autoload');

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function flexmailer_civicrm_config(&$config) {
  _flexmailer_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function flexmailer_civicrm_xmlMenu(&$files) {
  _flexmailer_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function flexmailer_civicrm_install() {
  _flexmailer_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function flexmailer_civicrm_postInstall() {
  _flexmailer_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function flexmailer_civicrm_uninstall() {
  _flexmailer_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function flexmailer_civicrm_enable() {
  _flexmailer_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function flexmailer_civicrm_disable() {
  _flexmailer_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function flexmailer_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _flexmailer_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function flexmailer_civicrm_managed(&$entities) {
  _flexmailer_civix_civicrm_managed($entities);
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
function flexmailer_civicrm_caseTypes(&$caseTypes) {
  _flexmailer_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function flexmailer_civicrm_angularModules(&$angularModules) {
  _flexmailer_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function flexmailer_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _flexmailer_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function flexmailer_civicrm_navigationMenu(&$menu) {
  _flexmailer_civix_insert_navigation_menu($menu, 'Administer/CiviMail', [
    'label' => ts('Flexmailer Settings', array('domain' => 'org.civicrm.flexmailer')),
    'name' => 'flexmailer_settings',
    'permission' => 'administer CiviCRM',
    'child' => array(),
    'operator' => 'AND',
    'separator' => 0,
    'url' => CRM_Utils_System::url('civicrm/admin/flexmailer', 'reset=1', TRUE),
  ]);
  _flexmailer_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_container().
 */
function flexmailer_civicrm_container($container) {
  $container->addResource(new \Symfony\Component\Config\Resource\FileResource(__FILE__));
  \Civi\FlexMailer\Services::registerServices($container);
}

/**
 * Get a list of delivery options for traditional mailings.
 *
 * @return array
 *   Array (string $machineName => string $label).
 */
function _flexmailer_traditional_options() {
  return array(
    'auto' => E::ts('Automatic'),
    'bao' => E::ts('CiviMail BAO'),
    'flexmailer' => E::ts('Flexmailer Pipeline'),
  );
}
