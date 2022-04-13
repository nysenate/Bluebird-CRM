<?php

require_once 'tutorial.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function tutorial_civicrm_config(&$config) {
  _tutorial_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function tutorial_civicrm_xmlMenu(&$files) {
  _tutorial_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tutorial_civicrm_install() {
  _tutorial_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function tutorial_civicrm_postInstall() {
  _tutorial_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function tutorial_civicrm_uninstall() {
  _tutorial_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tutorial_civicrm_enable() {
  _tutorial_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function tutorial_civicrm_disable() {
  _tutorial_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function tutorial_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _tutorial_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function tutorial_civicrm_managed(&$entities) {
  _tutorial_civix_civicrm_managed($entities);
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
function tutorial_civicrm_caseTypes(&$caseTypes) {
  _tutorial_civix_civicrm_caseTypes($caseTypes);
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
function tutorial_civicrm_angularModules(&$angularModules) {
  _tutorial_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function tutorial_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _tutorial_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function tutorial_civicrm_entityTypes(&$entityTypes) {
  _tutorial_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_buildForm().
 */
function tutorial_civicrm_buildForm($formName, &$form) {
  CRM_Tutorial_BAO_Tutorial::load(implode('/', $form->urlPath));
}

/**
 * Implements hook_civicrm_pageRun().
 */
function tutorial_civicrm_pageRun(&$page) {
  CRM_Tutorial_BAO_Tutorial::load(implode('/', $page->urlPath));
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 */
function tutorial_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if ($entity === 'tutorial') {
    $permissions['tutorial']['mark'] = ['access CiviCRM'];
  }
}
