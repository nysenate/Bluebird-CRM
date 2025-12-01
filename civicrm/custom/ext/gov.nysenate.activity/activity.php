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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function activity_civicrm_install() {
  _activity_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function activity_civicrm_enable() {
  _activity_civix_civicrm_enable();
}

function activity_civicrm_pageRun(&$page) {
  // Added - NYSS 11385 / Removed (deprecated) - NYSS 17345
  // See managed/SavedSearch_NYSS_My_Activities.mgd.php
  /*
  if ($pagename == 'CRM_Dashlet_Page_Activity') {
    CRM_Core_Resources::singleton()->addScript("
      CRM.$(function($) {
        $('li.widget-activity h3.widget-header').text('My Activities');
        $('th.crm-contact-activity-activity_date').html('Activity Date');
      });
    ");
  }
  */
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

function activity_civicrm_alterMailParams(&$params, $context) {
  /*Civi::log()->debug(__FUNCTION__, [
    'params' => $params,
    'context' => $context,
  ]);*/

  //13404
  if ($context == 'singleEmail' &&
    strpos($params['subject'], 'Constituent Activity') !== FALSE
  ) {
    $userID = CRM_Core_Session::singleton()->getLoggedInContactID();
    if (!empty($userID)) {
      [$userName, $userEmail] = CRM_Contact_BAO_Contact_Location::getEmailDetails($userID);

      if (!empty($userEmail)) {
        $checkEmail = toCheckEmail($userEmail, 'assignee');
        if (empty($checkEmail['assignee'])) {
          $fromEmail = trim("$userName <$userEmail>");
        }
      }
    }

    if (empty($fromEmail)) {
      $systemFromDefault = CRM_Core_OptionGroup::values('from_email_address', FALSE, FALSE, FALSE, ' AND is_default = 1 ');
      $fromEmail = $systemFromDefault[1];
    }

    $params['from'] = $fromEmail;
  }
}
