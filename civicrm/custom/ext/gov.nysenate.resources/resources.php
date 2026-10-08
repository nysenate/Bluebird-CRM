<?php

require_once 'resources.civix.php';
use CRM_NYSS_Resources_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function resources_civicrm_config(&$config) {
  _resources_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function resources_civicrm_install() {
  _resources_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function resources_civicrm_enable() {
  _resources_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_alterResourceSettings().
 *
 * Since CiviCRM 5.45, CKEditor 4's config comes from the ckeditor4 extension
 * (one config file per preset). Load our config file ahead of it; ours chains
 * to the preset file via config.customConfig (see js/ckeditor.nyss-config.js).
 * This runs after every coreResourceList hook, so hook order doesn't matter.
 */
function resources_civicrm_alterResourceSettings(&$data) {
  $ckConfig = $data['config']['CKEditorCustomConfig'] ?? NULL;
  if (empty($ckConfig) || isset($data['config']['nyssCKEditorNextConfig'])) {
    return;
  }
  if (is_string($ckConfig)) {
    $ckConfig = ['default' => $ckConfig];
  }

  $nyssConfig = Civi::resources()->getUrl(E::LONG_NAME, 'js/ckeditor.nyss-config.js', TRUE);
  $data['config']['nyssCKEditorNextConfig'] = $ckConfig;
  $data['config']['CKEditorCustomConfig'] = array_fill_keys(array_keys($ckConfig), $nyssConfig);
}

function resources_civicrm_coreResourceList(&$list, $region) {
  /*Civi::log()->debug('resource_civicrm_coreResourceList', array(
    'list' => $list,
    'region' => $region,
  ));*/

  //this was creating conflict with the quicksearch; it appears autocomplete is included
  //with the base jquery.ui package, which is why our version was probably conflicting
  //Civi::resources()->addScriptFile('gov.nysenate.resources', 'js/jquery.autocomplete.js', 10, 'html-header');

  Civi::resources()->addScriptFile('gov.nysenate.resources', 'js/jquery.civicrm-validate.js', 10, 'html-header');
  Civi::resources()->addScriptFile('gov.nysenate.resources', 'js/jquery.tokeninput.js', 10, 'html-header');
  Civi::resources()->addScriptFile('gov.nysenate.resources', 'js/jquery-fieldselection.js', 10, 'html-header');

  if (!CRM_NYSS_BAO_NYSS::isPublicUrl()) {
    Civi::resources()->addScriptFile('gov.nysenate.resources', 'js/jobId.js');
    Civi::resources()->addScriptFile('gov.nysenate.resources', 'js/menuBar.js');
  }

  //set kcfinder maxImage settings
  $_SESSION['KCFINDER'] = [
    'maxImageWidth' => 600,
    'maxImageHeight' => 2048,
  ];

  //add special non-Admin css file
  global $user;
  $roles = $user->roles;
  $adminRoles = ['Administrator', 'Superuser'];
  $isAdmin = array_intersect($adminRoles, $roles);
  if (empty($isAdmin) && !CRM_NYSS_BAO_NYSS::isPublicUrl()) {
    CRM_Core_Resources::singleton()->addStyleFile(E::LONG_NAME, 'css/nonAdmin.css');
  }
}

function resources_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  /*Civi::log()->debug('resources_civicrm_alterTemplateFile', array(
    '$formName' => $formName,
    '$form' => $form,
    '$context' => $context,
    '$tplName' => $tplName,
  ));*/

  if ($tplName == 'CRM/common/fatal.tpl') {
    $tplName = 'CRM/NYSS/fatal.tpl';
  }
}

function resources_civicrm_pageRun(&$page) {
  //Civi::log()->debug('resources_civicrm_pageRun', array('page' => $page));

  if (in_array($page->getVar('_name'), array(
    'CRM_Contact_Page_View_Print'
  ))) {
    CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.resources', 'css/print_contact_summary.css');
  }
}
