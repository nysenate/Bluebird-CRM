<?php

require_once 'case.civix.php';
use CRM_NYSS_Case_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function case_civicrm_config(&$config) {
  _case_civix_civicrm_config($config);
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
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function case_civicrm_enable() {
  _case_civix_civicrm_enable();
}

function case_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('case_civicrm_buildForm', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  if ($formName=='CRM_Case_Form_CaseView') {
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/CaseView.js');

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

      $dn = civicrm_api3('contact', 'getvalue', [
        'id' => $form->_formValues['contact_id'],
        'return' => 'display_name'
      ]);
      $form->assign('display_name', $dn);
    }
  }

  if ($formName == 'CRM_Case_Form_ActivityToCase') {
    //Civi::log()->debug(__FUNCTION__, ['form' => $form, 'REQUEST'=> $_REQUEST]);
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.case', 'js/FileOnCase.js');

    $cid = CRM_Utils_Request::retrieve('cid', 'Positive');
    Civi::resources()->addVars('NYSS', [
      'cid' => $cid,
      'url' => CRM_Utils_System::url('civicrm/fileoncase/create', "reset=1&cid={$cid}")
    ]);
  }
}

function case_civicrm_pre($op, $objectName, $id, &$params) {
  /*Civi::log()->debug(__FUNCTION__, [
    '$op' => $op,
    '$objectName' => $objectName,
    '$id' => $id,
    '$params' => $params,
  ]);*/

  //14527 don't set relationship end date when creating a case with resolved status
  if ($objectName == 'Relationship' &&
    $op == 'create' &&
    !empty($params['case_id']) &&
    CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', 'Case Manager is', 'id', 'name_a_b') == $params['relationship_type_id'] &&
    !empty($params['end_date'])
  ) {
    $params['end_date'] = 'null';
  }
}

function case_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  /*Civi::log()->debug('case_civicrm_post', [
    '$op' => $op,
    '$objectName' => $objectName,
    '$objectId' => $objectId,
    '$objectRef' => $objectRef,
  ]);*/

  //2450 - notify case worker/coordinator when role created/changed
  if (in_array($op, ['edit', 'create']) &&
    $objectName == 'Relationship' &&
    $objectRef->case_id
  ) {
    //notify case worker with an email
    $caseID = $objectRef->case_id;
    $caseDetails = civicrm_api3('case', 'getsingle', ['id' => $caseID]);
    //Civi::log()->debug('case_civicrm_post', ['caseDetails' => $caseDetails]);

    //get client IDs
    $clientIDs = $clientNames = [];
    foreach ($caseDetails['contacts'] as $contact) {
      if ($contact['role'] == 'Constituent') {
        $clientIDs[] = $contact['contact_id'];
        $clientNames[] = $contact['display_name'];
      }
    }

    //get role contact ID (whichever id is NOT the clientID)
    if (!in_array($objectRef->contact_id_a, $clientIDs)) {
      $roleContactId = $objectRef->contact_id_a;
    }
    elseif (!in_array($objectRef->contact_id_b, $clientIDs)) {
      $roleContactId = $objectRef->contact_id_b;
    }
    else {
      //can't determine the role contact ID, so exit
      return;
    }

    //get case role email
    $roleEmail = civicrm_api3('contact', 'getvalue', [
      'id' => $roleContactId,
      'return' => 'email',
    ]);
    //Civi::log()->debug('case_civicrm_post', array('$roleEmail' => $roleEmail));

    if (!empty($clientIDs) && !in_array($roleContactId, $clientIDs)) {
      $url = CRM_Utils_System::url(
        'civicrm/contact/view/case',
        "reset=1&action=view&cid={$clientIDs[0]}&id={$caseID}",
        true
      );

      //prepare mail params
      $fromEmailAddress = CRM_Core_OptionGroup::values('from_email_address', NULL, NULL, NULL, ' AND is_default = 1');
      $clientNamesList = implode(', ', $clientNames);
      $mailParams = [
        'toEmail' => $roleEmail,
        'subject' => "Case Role Created/Changed for: {$clientNamesList} (Case ID: {$caseID})",
        'html' => "<p>You have been assigned a case for {$clientNamesList} (Case ID: {$caseID})</p>
          <p><a href='$url' target=_blank>$url</a></p>",
        'from' => reset($fromEmailAddress),
      ];
      //Civi::log()->debug('case_civicrm_post', array('$mailParams' => $mailParams));

      $mailingBackend = Civi::settings()->get('mailing_backend');
      if ($mailingBackend['outBound_option'] != 2) {
        CRM_Utils_Mail::send($mailParams);
      }
      else {
        CRM_Core_Error::debug_var('$mailParams - role', $mailParams);
      }
    }
  }

  //15768 - notify case manager every time case activity is added/updated
  if (in_array($op, ['edit', 'create']) &&
    $objectName == 'Activity' &&
    !empty($objectRef->case_id)
  ) {
    $bbcfg = get_bluebird_instance_config();
    //Civi::log()->debug(__METHOD__, ['$bbcfg' => $bbcfg]);

    if ($bbcfg['case.notify_case_manager']) {
      $action = ($op == 'edit') ? 'Edited' : 'Created';

      $case = \Civi\Api4\CiviCase::get(FALSE)
        ->addSelect('case_type_id:label', 'subject', 'status_id:label', 'id')
        ->addWhere('id', '=', $objectRef->case_id)
        ->execute()
        ->single();
      //Civi::log()->debug(__METHOD__, ['case' => $case]);

      $caseContacts = \Civi\Api4\CaseContact::get()
        ->addSelect('contact_id.display_name', 'contact_id')
        ->addWhere('case_id', '=', $objectRef->case_id)
        ->execute();
      $clients = [];
      foreach ($caseContacts as $caseContact) {
        $firstClientId = (empty($firstClientId)) ? $caseContact['contact_id'] : $firstClientId;
        $clients[] = $caseContact['contact_id.display_name'];
      }
      $clientList = implode(', ', $clients);

      //use the first client ID for the activity URL
      $activityUrl = CRM_Utils_System::url('civicrm/case/activity/view',
        "reset=1&cid={$firstClientId}&caseid={$objectRef->case_id}&aid={$objectRef->id}", TRUE);

      $caseMgrs = \Civi\Api4\Relationship::get(FALSE)
        ->addWhere('case_id', '=', $objectRef->case_id)
        ->addWhere('relationship_type_id:name', '=', 'Case Manager is')
        ->addWhere('is_active', '=', TRUE)
        ->execute();
      //Civi::log()->debug(__METHOD__, ['$caseMgrs' => $caseMgrs]);

      foreach ($caseMgrs as $caseMgr) {
        $contact = \Civi\Api4\Contact::get(FALSE)
          ->addSelect('id', 'display_name', 'email_primary.email')
          ->addWhere('id', '=', $caseMgr['contact_id_b'])
          ->execute()
          ->single();
        //Civi::log()->debug(__METHOD__, ['$contact' => $contact]);

        $msg = "
          <p>A case you are assigned to manage has received updates:</p>
          <ul>
            <li>Case ID: {$objectRef->case_id}</li>
            <li>Case Constituent(s): {$clientList}</li>
            <li>{$action} Activity ID: {$objectRef->id}</li>
            <li>Subject: {$objectRef->subject}</li>
            <li><a href='{$activityUrl}' target='_blank'>View Activity</a></li>
          </ul>
        ";

        $mailParams = [
          'from' => "'{$bbcfg['senator.name.formal']}' <{$bbcfg['senator.email']}>",
          'toName' => $contact['display_name'],
          'toEmail' => $contact['email_primary.email'],
          'subject' => 'Case Manager: Case Updated',
          'html' => $msg,
          'contactId' => $contact['id'],
        ];
        //Civi::log()->debug(__METHOD__, ['$mailParams' => $mailParams]);

        CRM_Utils_Mail::send($mailParams);
      }
    }
  }
}

function case_civicrm_searchColumns($objectName, &$headers, &$rows, &$selector) {
  /*Civi::log()->debug(__FUNCTION__, [
    'objectName' => $objectName,
    'headers' => $headers,
    'selector' => $selector,
  ]);*/

  if ($objectName == 'case' && is_a($selector, 'CRM_Core_Selector_Controller')) {
    foreach ($headers as &$header) {
      if (in_array($header['sort'], ['case_recent_activity_date', 'case_scheduled_activity_date'])) {
        unset($header['sort']);
        unset($header['direction']);
      }
    }
  }
}
