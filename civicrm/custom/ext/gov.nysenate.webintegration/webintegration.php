<?php

require_once 'webintegration.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function webintegration_civicrm_config(&$config) {
  _webintegration_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function webintegration_civicrm_xmlMenu(&$files) {
  _webintegration_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function webintegration_civicrm_install() {
  _webintegration_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function webintegration_civicrm_uninstall() {
  _webintegration_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function webintegration_civicrm_enable() {
  _webintegration_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function webintegration_civicrm_disable() {
  _webintegration_civix_civicrm_disable();
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
function webintegration_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _webintegration_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function webintegration_civicrm_managed(&$entities) {
  _webintegration_civix_civicrm_managed($entities);
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
function webintegration_civicrm_caseTypes(&$caseTypes) {
  _webintegration_civix_civicrm_caseTypes($caseTypes);
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
function webintegration_civicrm_angularModules(&$angularModules) {
_webintegration_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function webintegration_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _webintegration_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function webintegration_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function webintegration_civicrm_navigationMenu(&$menu) {
  _webintegration_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'gov.nysenate.webintegration')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _webintegration_civix_navigationMenu($menu);
} // */

//TODO eventually move all related customizations to this extension

function webintegration_civicrm_alterMenu(&$items) {
  $items['civicrm/nyss/dashlet/webintegration/unmatched'] = array(
    'title' => 'Website Inbox Messages',
    'page_callback' => 'CRM_NYSS_WebIntegration_Page_UnMatched',
    'access_arguments' => array(array('access CiviCRM'), "and"),
  );
  $items['civicrm/nyss/ajax/unmatchedmessages'] = array(
    'page_callback' => 'CRM_NYSS_WebIntegration_Page_AJAX::getMessages',
    'access_arguments' => array(array('access CiviCRM'), "and"),
  );
}

function webintegration_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Activity_Form_Activity') {
    //retrieve webintegration msg ID and store as hidden field
    $msgID = CRM_Utils_Request::retrieve('msgId', 'Positive', $form);

    if ($msgID) {
      $msgNote = _webintegration_getNoteText($msgID);
      $form->addElement('hidden', 'msg_id', $msgID);
      $form->setDefaults(array(
        'subject' => _webintegration_extractSubject($msgNote),
        'details' => nl2br($msgNote),
      ));
    }

    /*Civi::log()->debug('webintegration_civicrm_buildForm', array(
      'msgID' => $msgID,
      'msgNote' => $msgNote,
      'msgNote_decode' => json_decode($msgNote),
    ));*/
  }
}//buildForm

function webintegration_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Activity_Form_Activity') {
    $params = $form->controller->exportValues($form->getVar('_name'));
    $msgID = CRM_Utils_Array::value('msg_id', $params);
    $aid = $form->_activityId;

    if ($msgID && $aid) {
      _webintegration_storeMessageActivity($msgID, $aid);
    }

    /*Civi::log()->debug('postProcess', array(
      //'form' => $form,
      'params' => $params,
      'msgID' => $msgID,
      'aid' => $aid,
    ));*/
  }
}//postProcess

function _webintegration_storeMessageActivity($msgID, $aid) {
  CRM_Core_DAO::executeQuery("
    INSERT IGNORE INTO nyss_web_msg_activity
    (note_id, activity_id)
    VALUES (%1, %2)
  ", array(
    1 => array($msgID, 'Integer'),
    2 => array($aid, 'Integer'),
  ));
}//_storeMessageActivity

function _webintegration_getNoteText($msgID) {
  $msgNote = CRM_Core_DAO::singleValueQuery("
    SELECT SQL_CALC_FOUND_ROWS n.note
    FROM civicrm_note n
    WHERE n.id = %1
  ", array(
    1 => array($msgID, 'Positive'),
  ));

  return $msgNote;
}

/**
 * @param $msgNote
 * @return string
 *
 * helper function to extract the subject line from the message body
 */
function _webintegration_extractSubject($msgNote) {
  preg_match('/Subject: (.*?)\n/', $msgNote, $matches);

  /*Civi::log()->debug('_webintegration_extractSubject', array(
    '$msgNote' => $msgNote,
    'matches' => $matches,
  ));*/

  return (!empty($matches[1])) ? $matches[1] : '';
}
