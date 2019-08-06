<?php

require_once 'resources.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function resources_civicrm_config(&$config) {
  _resources_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function resources_civicrm_xmlMenu(&$files) {
  _resources_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function resources_civicrm_uninstall() {
  _resources_civix_civicrm_uninstall();
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
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function resources_civicrm_disable() {
  _resources_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function resources_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _resources_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function resources_civicrm_managed(&$entities) {
  _resources_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function resources_civicrm_caseTypes(&$caseTypes) {
  _resources_civix_civicrm_caseTypes($caseTypes);
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
function resources_civicrm_angularModules(&$angularModules) {
_resources_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function resources_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _resources_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function resources_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function resources_civicrm_navigationMenu(&$menu) {
  _resources_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'gov.nysenate.resources')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _resources_civix_navigationMenu($menu);
} // */

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

  //implement coreResourceList to define location of custom ckeditor config file
  $extPath = Civi::resources()->getUrl('gov.nysenate.resources');
  $config = array_keys(array_filter($list, function($v){return !empty($v['config']) ? true : false;}));
  $list[$config[0]]['config']['CKEditorCustomConfig'] =
    "{$extPath}/js/ckeditor.config.js";
  //set ckeditor location (seems to get messed up by our special directory handling)
  $list[$config[0]]['config']['wysisygScriptLocation'] = '/sites/all/modules/civicrm/js/wysiwyg/crm.ckeditor.js';

  //set kcfinder maxImage settings
  $_SESSION['KCFINDER'] = array(
    'maxImageWidth' => 600,
    'maxImageHeight' => 2048,
  );

  //add special non-Admin css file
  global $user;
  $roles = $user->roles;
  $adminRoles = ['Administrator', 'Superuser'];
  $isAdmin = array_intersect($adminRoles, $roles);
  if (empty($isAdmin)) {
    CRM_Core_Resources::singleton()
      ->addStyleFile('gov.nysenate.resources', 'css/nonAdmin.css');
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
