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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function webintegration_civicrm_install() {
  _webintegration_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function webintegration_civicrm_enable() {
  _webintegration_civix_civicrm_enable();
}

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
