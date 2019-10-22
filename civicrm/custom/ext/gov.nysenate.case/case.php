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

function case_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('case_civicrm_buildForm', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  if ($formName=='CRM_Case_Form_CaseView') {
    //11518 hide timeline/audit fields
    foreach (array('timeline_id', 'report_id') as $field) {
      if ($form->elementExists($field)) {
        $form->removeElement($field);
      }
    }

    //11541 Limit case roles available to add new role dialog to only those that apply to cases
    if ($form->elementExists('role_type')) {
      $ele =& $form->getElement('role_type');
      //Civi::log()->debug('case_civicrm_buildForm', array('ele' => $ele));

      $allowedTypes = array(
        '- select type -',
        'Case Manager is',
        'Case Coordinator is',
        'Support Staff is',
        'Non-District Staff is'
      );

      foreach ($ele->_options as $k => $opt) {
        if (!in_array($opt['text'], $allowedTypes)) {
          unset($ele->_options[$k]);
        }
      }
    }
  }

  if ($formName == 'CRM_Case_Form_Search') {
    if ($form->_formValues['contact_id']) {
      $form->assign('contact_id', $form->_formValues['contact_id']);

      $dn = civicrm_api3('contact', 'getvalue', array(
        'id' => $form->_formValues['contact_id'],
        'return' => 'display_name'
      ));
      $form->assign('display_name', $dn);
    }
  }
}

function case_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  /*Civi::log()->debug('case_civicrm_post', array(
    '$op' => $op,
    '$objectName' => $objectName,
    '$objectId' => $objectId,
    '$objectRef' => $objectRef,
  ));*/

  //2450 - notify case worker/coordinator when role created/changed
  if (in_array($op, array('edit', 'create')) &&
    $objectName == 'Relationship' &&
    $objectRef->case_id
  ) {
    //notify case worker with an email
    $caseID = $objectRef->case_id;
    $caseDetails = civicrm_api3('case', 'getsingle', array('id' => $caseID));
    //Civi::log()->debug('case_civicrm_post', array('caseDetails' => $caseDetails));

    //get client ID
    foreach ($caseDetails['contacts'] as $contact) {
      if ($contact['role'] == 'Constituent') {
        $clientID = $contact['contact_id'];
        $clientName = $contact['display_name'];
      }
    }

    //get case role email
    $roleEmail = civicrm_api3('contact', 'getvalue', array(
      'id' => $objectRef->contact_id_b,
      'return' => 'email',
    ));
    //Civi::log()->debug('case_civicrm_post', array('$roleEmail' => $roleEmail));

    if (!empty($clientID)) {
      $url = CRM_Utils_System::url(
        'civicrm/contact/view/case',
        "reset=1&action=view&cid={$clientID}&id={$caseID}",
        true
      );

      //prepare mail params
      $fromEmailAddress = CRM_Core_OptionGroup::values('from_email_address', NULL, NULL, NULL, ' AND is_default = 1');
      $mailParams = array(
        'toEmail' => $roleEmail,
        'subject' => "Case Role Created/Changed for: $clientName (Case ID: {$caseID})",
        'html' => "<p>You have been assigned a case for $clientName (Case ID: {$caseID})</p>
          <p><a href='$url' target=_blank>$url</a></p>",
        'from' => reset($fromEmailAddress),
      );
      //Civi::log()->debug('case_civicrm_post', array('$mailParams' => $mailParams));

      $mailingBackend = Civi::settings()->get('mailing_backend');
      if ($mailingBackend['outBound_option'] != 2) {
        CRM_Utils_Mail::send($mailParams);
      }
      else {
        CRM_Core_Error::debug_var('$mailParams - role', $mailParams);
      }
    }
  }//end case role email
}
