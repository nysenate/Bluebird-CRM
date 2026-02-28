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

  //implement coreResourceList to define location of custom ckeditor config file
  $extPath = Civi::resources()->getUrl('gov.nysenate.resources');
  $config = array_keys(array_filter($list, function($v){return !empty($v['config']) ? true : false;}));
  //Civi::log()->debug(__FUNCTION__, ['list' => $list, 'config' => $config]);

  if (!empty($config[0])) {
    $list[$config[0]]['config']['CKEditorCustomConfig'] = "{$extPath}/js/ckeditor.config.js";

    //set ckeditor location (seems to get messed up by our special directory handling)
    $list[$config[0]]['config']['wysisygScriptLocation'] = '/sites/all/modules/civicrm/js/wysiwyg/crm.ckeditor.js';
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
