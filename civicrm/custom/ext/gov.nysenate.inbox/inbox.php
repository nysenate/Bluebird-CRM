<?php

require_once 'inbox.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function inbox_civicrm_config(&$config) {
  _inbox_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function inbox_civicrm_xmlMenu(&$files) {
  _inbox_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function inbox_civicrm_install() {
  _inbox_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function inbox_civicrm_uninstall() {
  _inbox_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function inbox_civicrm_enable() {
  _inbox_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function inbox_civicrm_disable() {
  _inbox_civix_civicrm_disable();
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
function inbox_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _inbox_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function inbox_civicrm_managed(&$entities) {
  _inbox_civix_civicrm_managed($entities);
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
function inbox_civicrm_caseTypes(&$caseTypes) {
  _inbox_civix_civicrm_caseTypes($caseTypes);
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
function inbox_civicrm_angularModules(&$angularModules) {
_inbox_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function inbox_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _inbox_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function inbox_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function inbox_civicrm_navigationMenu(&$menu) {
  _inbox_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'gov.nysenate.inbox')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _inbox_civix_navigationMenu($menu);
} // */

function inbox_civicrm_permission(&$permissions) {
  $permissions['access inbox polling'] = array(
    'Bluebird: access inbox polling',
    'Access inbox polling tools.',
  );
}

function inbox_civicrm_entityRefFilters(&$filters) {
  //Civi::log()->debug('entityRefFilters', array('filters' => $filters));

  $filters['contact'][] = array(
    'key' => 'street_address',
    'value' => 'Street Address',
    'entity' => 'address',
    'type' => 'text',
  );

  $filters['contact'][] = array(
    'key' => 'city',
    'value' => 'City',
    'entity' => 'address',
    'type' => 'text',
  );

  $filters['contact'][] = array(
    'key' => 'postal_code',
    'value' => 'Postal Code',
    'entity' => 'address',
    'type' => 'text',
  );

  $filters['contact'][] = array(
    'key' => 'birth_date',
    'value' => 'Birth Date',
    'entity' => 'contact',
    'type' => 'date',
  );

  $filters['contact'][] = array(
    'key' => 'phone',
    'value' => 'Phone',
    'entity' => 'phone',
    'type' => 'text',
  );
}//entityRefFilters

function inbox_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('inbox_civicrm_buildForm', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  if ($formName == 'CRM_Profile_Form_Edit') {
    $ufGroup = $form->getVar('_ufGroup');
    if ($ufGroup['name'] == 'new_individual') {
      CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/new_individual.js');
      CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.inbox', 'css/new_individual.css');
    }

  }
}
