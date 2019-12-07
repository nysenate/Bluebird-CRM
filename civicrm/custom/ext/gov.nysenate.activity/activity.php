<?php

require_once 'activity.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function activity_civicrm_config(&$config) {
  _activity_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function activity_civicrm_xmlMenu(&$files) {
  _activity_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function activity_civicrm_install() {
  _activity_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function activity_civicrm_uninstall() {
  _activity_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function activity_civicrm_enable() {
  _activity_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function activity_civicrm_disable() {
  _activity_civix_civicrm_disable();
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
function activity_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _activity_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function activity_civicrm_managed(&$entities) {
  _activity_civix_civicrm_managed($entities);
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
function activity_civicrm_caseTypes(&$caseTypes) {
  _activity_civix_civicrm_caseTypes($caseTypes);
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
function activity_civicrm_angularModules(&$angularModules) {
_activity_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function activity_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _activity_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function activity_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('activity_civicrm_buildForm', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  //13050 assignee filter
  if ($formName == 'CRM_Activity_Form_Activity') {
    if ($form->elementExists('assignee_contact_id')) {
      $ele =& $form->getElement('assignee_contact_id');
      $apiParams = json_decode($ele->_attributes['data-api-params'], TRUE);
      $staffGroupID = civicrm_api3('group', 'getvalue',
        ['name' => 'Office_Staff', 'return' => 'id']);
      $apiParams['params']['group'] = $staffGroupID;
      $ele->_attributes['data-api-params'] = json_encode($apiParams);
      $ele->_attributes['data-create-links'] = FALSE;
    }
  }
}

function activity_civicrm_postProcess($formName, &$form) {
  /*Civi::log()->debug('activity_civicrm_postProcess', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  if (in_array($formName, array('CRM_Contact_Form_Task_Email')) &&
    $form->_action == CRM_Core_Action::ADD &&
    !empty($form->_activityId)
  ) {
    if (!empty($form->_ccContactIds) || !empty($form->_bccContactIds)) {
      $ccBccIds = array_merge($form->_ccContactIds, $form->_bccContactIds);
      //Civi::log()->debug('activity_civicrm_postProcess', array('$ccBccIds' => $ccBccIds));

      $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
      $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

      foreach ($ccBccIds as $cid) {
        try {
          civicrm_api3('activity_contact', 'create', [
            'activity_id' => $form->_activityId,
            'contact_id' => $cid,
            'record_type_id' => $targetID,
          ]);
        }
        catch (CiviCRM_API3_Exception $e) {}
      }
    }
  }
}
