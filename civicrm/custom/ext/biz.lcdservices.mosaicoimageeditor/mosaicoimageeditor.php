<?php

/**
 * Resources:
 * https://github.com/voidlabs/mosaico/wiki
 * https://github.com/voidlabs/mosaico/issues/95
 * https://github.com/scaleflex/filerobot-image-editor/blob/master/examples/js/src/filerobot-init-example.js
 * https://github.com/veda-consulting-company/uk.co.vedaconsulting.mosaico/issues/347#issuecomment-555785659
 * https://scaleflex.github.io/filerobot-image-editor/
 * https://github.com/scaleflex/filerobot-image-editor#installation
 */

require_once 'mosaicoimageeditor.civix.php';
use CRM_Mosaicoimageeditor_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mosaicoimageeditor_civicrm_config(&$config) {
  _mosaicoimageeditor_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mosaicoimageeditor_civicrm_xmlMenu(&$files) {
  _mosaicoimageeditor_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mosaicoimageeditor_civicrm_install() {
  _mosaicoimageeditor_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function mosaicoimageeditor_civicrm_postInstall() {
  _mosaicoimageeditor_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mosaicoimageeditor_civicrm_uninstall() {
  _mosaicoimageeditor_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mosaicoimageeditor_civicrm_enable() {
  _mosaicoimageeditor_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mosaicoimageeditor_civicrm_disable() {
  _mosaicoimageeditor_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mosaicoimageeditor_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mosaicoimageeditor_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mosaicoimageeditor_civicrm_managed(&$entities) {
  _mosaicoimageeditor_civix_civicrm_managed($entities);
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
function mosaicoimageeditor_civicrm_caseTypes(&$caseTypes) {
  _mosaicoimageeditor_civix_civicrm_caseTypes($caseTypes);
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
function mosaicoimageeditor_civicrm_angularModules(&$angularModules) {
  _mosaicoimageeditor_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mosaicoimageeditor_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mosaicoimageeditor_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function mosaicoimageeditor_civicrm_entityTypes(&$entityTypes) {
  _mosaicoimageeditor_civix_civicrm_entityTypes($entityTypes);
}

function mosaicoimageeditor_civicrm_mosaicoConfig(&$config) {
  //Civi::log()->debug(__FUNCTION__, ['config' => $config]);
}

//https://github.com/veda-consulting-company/uk.co.vedaconsulting.mosaico/issues/347#issuecomment-555785659
function mosaicoimageeditor_civicrm_mosaicoScripts(&$scripts) {
  $extUrl = CRM_Core_Resources::singleton()->getUrl(E::LONG_NAME);
  $scripts[] = $extUrl.'js/filerobot-image-editor.min.js';
  $scripts[] = $extUrl.'js/FileRobotPlugin.js';
}

function mosaicoimageeditor_civicrm_mosaicoPlugins(&$plugins) {
  $plugins[] = 'function(viewModel) { frie(viewModel); }';
}
