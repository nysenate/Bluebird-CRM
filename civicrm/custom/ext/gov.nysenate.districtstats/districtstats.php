<?php

require_once 'districtstats.civix.php';
use CRM_Districtstats_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function districtstats_civicrm_config(&$config) {
  _districtstats_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function districtstats_civicrm_xmlMenu(&$files) {
  _districtstats_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function districtstats_civicrm_install() {
  _districtstats_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function districtstats_civicrm_postInstall() {
  _districtstats_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function districtstats_civicrm_uninstall() {
  _districtstats_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function districtstats_civicrm_enable() {
  _districtstats_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function districtstats_civicrm_disable() {
  _districtstats_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function districtstats_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _districtstats_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function districtstats_civicrm_managed(&$entities) {
  _districtstats_civix_civicrm_managed($entities);
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
function districtstats_civicrm_caseTypes(&$caseTypes) {
  _districtstats_civix_civicrm_caseTypes($caseTypes);
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
function districtstats_civicrm_angularModules(&$angularModules) {
  _districtstats_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function districtstats_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _districtstats_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function districtstats_civicrm_entityTypes(&$entityTypes) {
  _districtstats_civix_civicrm_entityTypes($entityTypes);
}

function districtstats_civicrm_buildForm($formName, &$form) {
  //Civi::log()->debug('districtstats_civicrm_buildForm', ['formName' => $formName, 'form' => $form]);

  if ($formName == 'CRM_Contact_Form_Search_Advanced' &&
    CRM_Utils_Request::retrieve('context', 'String') == 'districtstats'
  ) {
    //Civi::log()->debug('districtstats_civicrm_buildForm', ['$_GET' => $_GET]);

    $defaults = [];
    $skip = ['reset', 'force', 'context', 'q', 'qfKey'];
    foreach ($_GET as $f => $v) {
      if (!in_array($f, $skip)) {
        $defaults[$f] = $v;
      }
    }

    //Civi::log()->debug('districtstats_civicrm_buildForm', ['$defaults' => $defaults]);
    if (!empty($defaults)) {
      //$form->setDefaults($defaults);
    }
  }
}
