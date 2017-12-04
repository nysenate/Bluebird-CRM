<?php

require_once 'case.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function case_civicrm_config(&$config) {
  _case_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function case_civicrm_xmlMenu(&$files) {
  _case_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function case_civicrm_install() {
  _case_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function case_civicrm_uninstall() {
  _case_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function case_civicrm_enable() {
  _case_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function case_civicrm_disable() {
  _case_civix_civicrm_disable();
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
function case_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _case_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function case_civicrm_managed(&$entities) {
  _case_civix_civicrm_managed($entities);
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
function case_civicrm_caseTypes(&$caseTypes) {
  _case_civix_civicrm_caseTypes($caseTypes);
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
function case_civicrm_angularModules(&$angularModules) {
_case_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function case_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _case_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function case_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function case_civicrm_navigationMenu(&$menu) {
  _case_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'gov.nysenate.case')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _case_civix_navigationMenu($menu);
} // */

function case_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('case_civicrm_buildForm', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  if ($formName == 'CRM_Case_Form_CaseView') {
    //11518 hide timeline/audit fields
    foreach (array('timeline_id', 'report_id') as $field) {
      if ($form->elementExists($field)) {
        $form->removeElement($field);
      }
    }

    //11482 limit case role field to office staff
    $staffGroupID = civicrm_api3('group', 'getvalue', array(
      'name' => 'Office_Staff',
      'return' => 'id',
    ));
    if ($staffGroupID) {
      $staffGroupJson = json_encode(array('params' => array('group' => $staffGroupID)));
      CRM_Core_Resources::singleton()
        ->addVars('NYSS', array('staffGroupJson' => $staffGroupJson));
      CRM_Core_Resources::singleton()
        ->addScriptFile('gov.nysenate.case', 'js/CaseView.js');
    }
  }

  //11482 - limit assignee field to office staff
  if (in_array($formName, array('CRM_Activity_Form_Activity', 'CRM_Case_Form_Activity'))) {
    if ($form->elementExists('assignee_contact_id')) {
      $staffGroupID = civicrm_api3('group', 'getvalue', array(
        'name' => 'Office_Staff',
        'return' => 'id',
      ));
      if ($staffGroupID) {
        $ele =& $form->getElement('assignee_contact_id');
        $apiParams = json_decode($ele->_attributes['data-api-params']);
        $apiParams->params->group = $staffGroupID;
        $ele->_attributes['data-api-params'] = json_encode($apiParams);

        /*Civi::log()->debug('ele', [
          'ele' => $ele,
          'staffGroupID' => $staffGroupID,
          'apiParams' => $apiParams,
        ]);*/
      }
    }
  }
}
