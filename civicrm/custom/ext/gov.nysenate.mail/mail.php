<?php

require_once 'mail.civix.php';
use CRM_NYSS_Mail_ExtensionUtil as E;

defined('FILTER_ALL') or define('FILTER_ALL', 0);
defined('FILTER_IN_SD_ONLY') or define('FILTER_IN_SD_ONLY', 1);
defined('FILTER_IN_SD_OR_NO_SD') or define('FILTER_IN_SD_OR_NO_SD', 2);
define('BB_MAIL_LOG', FALSE);
define('BASE_SUBSCRIPTION_GROUP', 'Bluebird_Mail_Subscription');
define('DEFAULT_REPLYTO', 'bluebird.admin@nysenate.gov');

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mail_civicrm_config(&$config) {
  _mail_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mail_civicrm_xmlMenu(&$files) {
  _mail_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mail_civicrm_install() {
  _mail_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mail_civicrm_uninstall() {
  _mail_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mail_civicrm_enable() {
  _mail_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mail_civicrm_disable() {
  _mail_civix_civicrm_disable();
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
function mail_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mail_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mail_civicrm_managed(&$entities) {
  _mail_civix_civicrm_managed($entities);
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
function mail_civicrm_caseTypes(&$caseTypes) {
  _mail_civix_civicrm_caseTypes($caseTypes);
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
function mail_civicrm_angularModules(&$angularModules) {
_mail_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mail_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mail_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function mail_civicrm_alterAngular(\Civi\Angular\Manager $angular) {
  //inject mailing form options
  $changeSet = \Civi\Angular\ChangeSet::create('inject_options')
    ->alterHtml('~/crmMailing/BlockMailing.html', '_mail_alterMailingBlock');
  $angular->add($changeSet);

  //inject wizard
  $changeSet = \Civi\Angular\ChangeSet::create('inject_wizard')
    ->alterHtml('~/crmMailing/EditMailingCtrl/workflow.html', '_mail_alterMailingWizard');
  $angular->add($changeSet);

  //11041 adjust mailing summary
  $changeSet = \Civi\Angular\ChangeSet::create('modify_review')
    ->alterHtml('~/crmMailing/BlockReview.html', '_mail_alterMailingReview');
  $angular->add($changeSet);

  //12136 mailing test group
  $changeSet = \Civi\Angular\ChangeSet::create('modify_preview')
    ->alterHtml('~/crmMailing/BlockPreview.html', '_mail_alterMailingPreview');
  $angular->add($changeSet);
}

function mail_civicrm_pageRun(&$page) {
  /*Civi::log()->debug(__FUNCTION__, [
    'page' => $page,
    '$_GET' => $_GET,
  ]);*/

  //11038
  if (is_a($page, 'Civi\Angular\Page\Main')) {
    CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.mail', 'css/mail.css');
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.mail', 'js/mail.js');
  }

  //expose url/open tracking to mailing report
  //need to do manually since we are not using native tracking tools
  if ($page->getVar('_name') == 'CRM_Mailing_Page_Report') {
    $smarty =& CRM_Core_Smarty::singleton();
    $rpt =& $smarty->get_template_vars('report');
    //CRM_Core_Error::debug('rpt', $rpt);

    $rpt['mailing']['url_tracking'] = 1;
    $rpt['mailing']['open_tracking'] = 1;

    $smarty->assign_by_ref('report', $rpt);

    // NYSS 7860 - include mailing category on report page
    $mailingID = $page->_mailing_id;
    if ( $mailingID ) {
      $category = CRM_Core_DAO::singleValueQuery("
        SELECT ov.label
        FROM civicrm_mailing m
        JOIN civicrm_option_value ov
          ON m.category = ov.value
          AND ov.option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'mailing_categories')
        WHERE m.id = {$mailingID}
      ");

      if ( $category ) {
        $page->assign('mailing_category', $category);
      }
    }
  }

  // NYSS 5567 - fix title
  if ($page->getVar('_name') == 'CRM_Mailing_Page_Event') {
    $event = CRM_Utils_Array::value('event', $_GET);
    if ($event == 'unsubscribe') {
      CRM_Utils_System::setTitle(ts('Opt-out Requests'));
    }
  }

  // NYSS 5581
  if ($page->getVar('_name') == 'CRM_Profile_Page_View') {
    $gid = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_uf_group
      WHERE name = 'Mass_Email_Subscriptions'
    ");

    if ($page->getVar('_gid') == $gid) {
      CRM_Utils_System::setTitle('Mass Email Subscriptions');

      //get all emails
      $cid = $page->getVar('_id');

      //get contact display name
      $displayName = civicrm_api('contact', 'getvalue', array('version'=>3, 'id'=>$cid, 'return'=>'display_name'));
      $page->assign('display_name', $displayName);

      //get senator name
      $bbconfig = get_bluebird_instance_config();
      $page->assign('senatorFormal', $bbconfig['senator.name.formal']);

      $contactEmails = array();
      $sql = "
        SELECT *
        FROM civicrm_email
        WHERE contact_id = {$cid}
      ";
      $email = CRM_Core_DAO::executeQuery($sql);

      $locTypes = civicrm_api('location_type', 'get', array('version' => 3));
      //CRM_Core_Error::debug_var('$locTypes', $locTypes);
      $lt = array();
      foreach ($locTypes['values'] as $lt_id => $lt_val) {
        if ($lt_val['is_active']) {
          $lt[$lt_id] = $lt_val['display_name'];
        }
      }
      //CRM_Core_Error::debug_var('$lt', $lt);

      $holdOptions = array(
        1 => ts('On Hold Bounce'),
        2 => ts('On Hold Opt Out'),
      );

      //get category options
      $mCats = array();
      $opts = CRM_Core_DAO::executeQuery("
        SELECT ov.label, ov.value
        FROM civicrm_option_value ov
        JOIN civicrm_option_group og
          ON ov.option_group_id = og.id
          AND og.name = 'mailing_categories'
        ORDER BY ov.label
      ");
      while ($opts->fetch()) {
        $mCats[$opts->value] = $opts->label;
      }

      while ($email->fetch()) {
        $contactEmails[$email->id] = array(
          'location_type_id' => $lt[$email->location_type_id],
          'email' => $email->email,
          'is_primary' => $email->is_primary,
          'on_hold' => CRM_Utils_Array::value($email->on_hold, $holdOptions, ''),
          'hold_date' => $email->hold_date,
        );
        $cats = explode(',', $email->mailing_categories);
        $catsLabel = array();
        foreach ($cats as $cat) {
          $catsLabel[] = $mCats[$cat];
        }
        $contactEmails[$email->id]['mailing_categories'] = implode(', ', $catsLabel);
      }
      $page->assign('emails', $contactEmails);
    }
  } // NYSS 5581
}

function mail_civicrm_entityTypes(&$entityTypes) {
  //Civi::log()->debug('mail_civicrm_entityTypes', array('entityTypes' => $entityTypes));

  //formally declare our additions to the mailing table as entity fields
  $entityTypes['CRM_Mailing_DAO_Mailing']['fields_callback'][] = function($class, &$fields) {
    //Civi::log()->debug('mail_civicrm_entityTypes', array('$class' => $class, 'fields' => $fields));

    $fields['all_emails'] = array(
      'name' => 'all_emails',
      'type' => CRM_Utils_Type::T_INT,
      'title' => 'All Emails',
    );

    $fields['exclude_ood'] = array(
      'name' => 'exclude_ood',
      'type' => CRM_Utils_Type::T_INT,
      'title' => 'Exclude Out of District Emails',
    );

    $fields['category'] = array(
      'name' => 'category',
      'type' => CRM_Utils_Type::T_STRING,
      'title' => 'Category',
      'maxlength' => 255,
    );
  };
}

function mail_civicrm_alterMailingRecipients(&$mailing, &$params, $context) {
  /*Civi::log()->debug('mail_civicrm_alterMailingRecipients', array(
    '$mailing' => $mailing,
    '$params' => $params,
    '$context' => $context,
  ));*/

  if ($context == 'pre') {
    unset($params['filters']['on_hold']);
  }

  //only trigger these at the end of the recipient construction process and only when
  //the mailing has been scheduled
  if ($context == 'post' && !empty($mailing->scheduled_date)) {
    _mail_logRecipients('pre-filters', $mailing->id);

    // NYSS 4628, 4879
    if ($mailing->all_emails) {
      _mail_addAllEmails($mailing->id, $mailing->exclude_ood);
      _mail_logRecipients('_mail_addAllEmails', $mailing->id);
    }

    if ($mailing->exclude_ood != FILTER_ALL) {
      _mail_excludeOOD($mailing->id, $mailing->exclude_ood);
      _mail_logRecipients('_mail_excludeOOD', $mailing->id);
    }

    // NYSS 5581
    if ($mailing->category) {
      _mail_excludeCategoryOptOut($mailing->id, $mailing->category);
      _mail_logRecipients('_mail_excludeCategoryOptOut', $mailing->id);
    }

    //add email seed group
    _mail_addEmailSeeds($mailing->id);
    _mail_logRecipients('_mail_addEmailSeeds', $mailing->id);

    //dedupe emails as final step
    if ($mailing->dedupe_email) {
      _mail_dedupeEmail($mailing->id);
      _mail_logRecipients('_mail_dedupeEmail', $mailing->id);
    }

    //remove on_hold as we didn't do it earlier
    _mail_removeOnHold($mailing->id);
    _mail_logRecipients('_mail_removeOnHold', $mailing->id);
  }

  _mail_dedupeContacts($mailing->id);
}

function mail_civicrm_pre($op, $objectName, $id, &$params) {
  /*Civi::log()->debug('mail_civicrm_pre', array(
    '$op' => $op,
    '$objectName' => $objectName,
    '$id' => $id,
    '$params' => $params,
  ));*/

  //set exclude_ood and other fixed default values
  if ($objectName == 'Mailing') {
    //exclude_ood is set from config file
    $bbconfig = get_bluebird_instance_config();
    $excludeOOD = FILTER_ALL;
    if (isset($bbconfig['email.filter.district'])) {
      $filter_district = $bbconfig['email.filter.district'];
      switch ($filter_district) {
        case "1": case "strict": case "in_sd":
          $excludeOOD = FILTER_IN_SD_ONLY;
          break;
        case "2": case "fuzzy": case "in_sd_or_no_sd":
          $excludeOOD = FILTER_IN_SD_OR_NO_SD;
          break;
        default:
          $excludeOOD = FILTER_ALL;
      }
    }
    $params['exclude_ood'] = $excludeOOD;

    $params['url_tracking'] = 0;
    $params['forward_replies'] = 0;
    $params['auto_responder'] = 0;
    $params['open_tracking'] = 0;
    $params['visibility'] = 'Public Pages';

    $doc = phpQuery::newDocument($params['body_html']);
    $style = $doc->find('figure')->attr('style');
    if (!empty($params['body_html']) && strpos($style, 'margin-inline-start') === FALSE) {
      $doc->find('figure')
        ->attr('style', "{$style}; margin-inline-start: 5px; margin-inline-end: 5px;");
      $params['body_html'] = $doc->html();
    }
    //Civi::log()->debug('mail_civicrm_pre AFTER', ['$style' => $style, '$params[body_html]' => $params['body_html']]);
  }

  //10925 set click/open values to 0
  if ($op == 'create' && $objectName == 'Mailing') {
    $params['open_tracking'] = $params['url_tracking'] = FALSE;
  }
}

function mail_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  /*Civi::log()->debug('mail_civicrm_post', array(
    '$op' => $op,
    '$objectName' => $objectName,
    '$objectId' => $objectId,
    '$objectRef' => $objectRef,
  ));*/

  if ($objectName == 'MailingJob') {
    //check if existing non-test parent job exists for same mailing
    if ($op == 'create' && !$objectRef->is_test && empty($objectRef->parent_id)) {
      $jobId = CRM_Core_DAO::singleValueQuery("
        SELECT id
        FROM civicrm_mailing_job
        WHERE mailing_id = {$objectRef->mailing_id}
          AND is_test = 0
          AND parent_id IS NULL
          AND id != {$objectId}
        LIMIT 1
      ");

      if ($jobId) {
        //if exists, delete the newly created parent job
        CRM_Core_DAO::executeQuery("
          DELETE FROM civicrm_mailing_job
          WHERE id = {$objectId}
        ");
      }
    }
  }
}

function mail_civicrm_links($op, $objectName, $objectId, &$links, &$mask, &$values) {
  /*Civi::log()->debug('mail_civicrm_links', array(
    '$op' => $op,
    '$objectName' => $objectName,
    '$objectId' => $objectId,
    '$links' => $links,
    '$mask' => $mask,
    '$values' => $values,
  ));*/

  //11500
  if (strpos($op, 'view.mailing.browse') !== FALSE &&
    $objectName == 'Mailing'
  ) {
    foreach ($links as $key => $link) {
      if ($link['name'] == 'Public View') {
        unset($links[$key]);
      }
    }
  }

  if ($op == 'mailing.contact.action' && $objectName == 'Mailing') {
    $viewPerm = FALSE;
    $allowedPerms = [
      'view mass email',
      'access CiviMail',
      'create mailings',
      'approve mailings',
      'schedule mailings',
    ];

    foreach ($allowedPerms as $perm) {
      if (CRM_Core_Permission::check($perm)) {
        $viewPerm = TRUE;
      }
    }

    //13174 if user does not have a mailing perm, hide mailing report link
    if (!$viewPerm) {
      foreach ($links as $k => $link) {
        if ($link['name'] == 'Mailing Report') {
          unset($links[$k]);
        }
      }
    }
  }
}

function mail_civicrm_mosaicoBaseTemplates(&$templates) {
  //Civi::log()->debug('', array('templates' => $templates));
  unset($templates['tedc15']);
  unset($templates['tutorial']);
}

function mail_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  /*Civi::log()->debug('', [
    'wrappers' => $wrappers,
    'apiRequest' => $apiRequest,
  ]);*/

  //we do our best to target the specific API called on the preview popup grouplist
  //this is tricky, as we don't have a clear way to target that page view this hook
  if ($apiRequest['entity'] == 'Group' &&
    $apiRequest['action'] == 'getlist' &&
    isset($apiRequest['params']['params']['is_hidden']) &&
    isset($apiRequest['params']['params']['is_active']) &&
    empty($apiRequest['params']['params']['group_type'])
  ) {
    //TODO this isn't working but would be great if it did...
    $nmp = CRM_Core_Session::singleton()->get('nyss-mailing-preview');

    /*Civi::log()->debug('', [
      //'$wrappers' => $wrappers,
      '$apiRequest' => $apiRequest,
      '$_REQUEST' => $_REQUEST,
      'session nmp' => $nmp,
    ]);*/

    $wrappers[] = new CRM_NYSS_Mail_APIWrapper();
  }
}

function mail_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Mailing_Form_Group' && $form->_searchBasedMailing) {
    //get base mailing group, add to option list, set as default, freeze field
    $params = ['name' => BASE_SUBSCRIPTION_GROUP];
    $groupObjects = CRM_Contact_BAO_Group::getGroups($params);
    $groupID = $groupObjects[0]->id;
    $groupTitle = $groupObjects[0]->title;
    $baseGroup =& $form->getElement('baseGroup');
    $baseGroup->addOption($groupTitle, $groupID);
    $defaults['baseGroup'] = $groupID;
    $form->setDefaults($defaults);
    $baseGroup->freeze();
  }

  if ($formName == 'CRM_Mailing_Form_Group') {
    $mailingID = CRM_Utils_Request::retrieve('mid', 'Integer', $form, false, null );

    // NYSS 4628
    $form->addElement('checkbox', 'all_emails', ts('Send to all contact emails?'));

    // NYSS 4879
    $form->add('select', 'exclude_ood', ts('Send only to emails matched with in-district postal addresses'),
      array(
        FILTER_ALL => 'No District Filtering',
        FILTER_IN_SD_ONLY => 'In-District Only',
        FILTER_IN_SD_OR_NO_SD => 'In-District and Unknowns'),
      false);

    //NYSS 5581 - mailing category options
    $mCats = array('' => '- select -');
    $opts = CRM_Core_DAO::executeQuery("
      SELECT ov.label, ov.value
      FROM civicrm_option_value ov
      JOIN civicrm_option_group og
        ON ov.option_group_id = og.id
        AND og.name = 'mailing_categories'
      ORDER BY ov.label
    ");
    while ($opts->fetch()) {
      $mCats[$opts->value] = $opts->label;
    }
    $form->add('select', 'category', 'Mailing Category', $mCats, false);

    if ($mailingID) {
      $m = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_mailing WHERE id = {$mailingID}");
      while ($m->fetch()) {
        $defaults = array(
          'all_emails' => $m->all_emails,
          'dedupe_email' => $m->dedupe_email,
          'exclude_ood' => $m->exclude_ood,
          'category' => $m->category,
        );
      }
    }
    else {
      $defaults['dedupe_email'] = true;
    }

    //CRM_Core_Error::debug_var('defaults', $defaults);
    $form->setDefaults($defaults);
  }

  if ($formName == 'CRM_Mailing_Form_Test') {
    //change button text
    $buttons =& $form->getElement('buttons');
    foreach ($buttons->_elements as $key => $button) {
      if ($button->_attributes['value'] == 'Inform Scheduler') {
        $buttons->_elements[$key]->_attributes['value'] = 'Submit for Scheduling';
      }
    }
  }

  if ($formName == 'CRM_Mailing_Form_Schedule') {
    //change button text
    $buttons =& $form->getElement('buttons');
    foreach ($buttons->_elements as $key => $button) {
      if ($button->_attributes['value'] == 'Submit Mailing') {
        $buttons->_elements[$key]->_attributes['value'] = 'Submit for Approval';
      }
    }
  }

  if ($formName == 'CRM_Mailing_Form_Group' ||
    $formName == 'CRM_Mailing_Form_Upload' ||
    $formName == 'CRM_Mailing_Form_Test' ||
    $formName == 'CRM_Mailing_Form_Schedule'
  ) {
    CRM_Utils_System::setTitle('New Mass Email');

    // NYSS 4557
    //CRM_Core_Error::debug_var('form', $form);
    $session =& CRM_Core_Session::singleton();
    if (!empty($form->_finalValues['name'])) {
      $form->assign('mailingName', $form->_finalValues['name']);
      $session->set('mailingName', $form->_finalValues['name']);
    }
    elseif ($session->get('mailingName')) {
      $form->assign('mailingName', $session->get('mailingName'));
    }
  }

  if ($formName == 'CRM_Mailing_Form_Search') {
    $parent = $form->controller->getParent();
    $title  = $parent->getVar('_title');

    if ($title == 'Draft and Unscheduled Mailings') {
      CRM_Utils_System::setTitle('Draft and Unscheduled Email');
    }
    elseif ($title == 'Scheduled and Sent Mailings') {
      CRM_Utils_System::setTitle('Scheduled and Sent Email');
    }
    elseif ($title == 'Archived Mailings') {
      CRM_Utils_System::setTitle('Archived Email');
    }
    //CRM_Core_Error::debug($parent);
  }

  // NYSS 5581 - optimized opt out
  if ($formName == 'CRM_Profile_Form_Edit' &&
    $form->getVar('_ufGroupName') == 'Mass_Email_Subscriptions'
  ) {
    $cid = $form->getVar('_id');

    //get contact display name
    $displayName = civicrm_api('contact', 'getvalue', array('version'=>3, 'id'=>$cid, 'return'=>'display_name'));
    $form->assign('display_name', $displayName);

    //get senator name
    $bbconfig = get_bluebird_instance_config();
    $form->assign('senatorFormal', $bbconfig['senator.name.formal']);

    $contactEmails = array();
    $sql = "
      SELECT *
      FROM civicrm_email
      WHERE contact_id = {$cid}
    ";
    $email = CRM_Core_DAO::executeQuery($sql);

    $locTypes = civicrm_api('location_type', 'get', array('version' => 3));
    //CRM_Core_Error::debug_var('$locTypes', $locTypes);
    $lt = array();
    foreach ($locTypes['values'] as $lt_id => $lt_val) {
      if ($lt_val['is_active']) {
        $lt[$lt_id] = $lt_val['display_name'];
      }
    }
    //CRM_Core_Error::debug_var('$lt', $lt);

    $holdOptions = array(
      1 => ts('On Hold Bounce'),
      2 => ts('On Hold Opt Out'),
    );
    $blockId = 0;

    //get category options
    $mCats = array();
    $opts = CRM_Core_DAO::executeQuery("
      SELECT ov.label, ov.value
      FROM civicrm_option_value ov
      JOIN civicrm_option_group og
        ON ov.option_group_id = og.id
        AND og.name = 'mailing_categories'
      ORDER BY ov.label
    ");
    while ($opts->fetch()) {
      $mCats[$opts->value] = $opts->label;
    }

    $defaults = array();
    while ($email->fetch()) {
      $contactEmails[$email->id] = array(
        'location_type_id' => $lt[$email->location_type_id],
        'email' => $email->email,
        'is_primary' => $email->is_primary,
        'on_hold' => CRM_Utils_Array::value($email->on_hold, $holdOptions, ''),
        'hold_date' => $email->hold_date,
        'mailing_categories' => $email->mailing_categories,
      );

      /*$form->addElement('text', "email[$blockId][email]", ts('Email'),
        CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email'));
      $form->addElement('select', "email[$blockId][location_type_id]", '',
        CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id'));
      $form->addElement('select', "email[$blockId][on_hold]", '', $holdOptions);*/

      $form->add(
        'select',
        "email[{$email->id}][mailing_categories]",
        ts('Subscription Opt-Outs'),
        $mCats,
        false,
        array(
          'id' => 'subscription-optout-'.$email->id,
          'multiple' => 'multiple',
          'title' => ts('- select -')
        )
      );

      //set defaults
      $defaults["email[{$email->id}][mailing_categories]"] = $email->mailing_categories;
    }
    //CRM_Core_Error::debug_var('$contactEmails', $contactEmails);
    $form->assign('emails', $contactEmails);
    $form->setDefaults($defaults);
  }

  if ($formName == 'CRM_Mailing_Form_Approve') {
    $recipContacts = [];

    //get total count
    $count = 0;
    try {
      $count = civicrm_api3('MailingRecipients', 'getcount', [
        'mailing_id' => $form->_mailingID,
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug(__FUNCTION__, ['$e' => $e]);
    }

    $reviewUrl = CRM_Utils_System::url('civicrm/mailing/recipientreview', "id={$form->_mailingID}&count={$count}&reset=1");
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/MailingApproval.js');
    CRM_Core_Resources::singleton()->addStyleFile(E::LONG_NAME, 'css/MailingApproval.css');
    CRM_Core_Resources::singleton()->addVars('NYSS', ['mailingCount' => $count, 'reviewUrl' => $reviewUrl]);
  }

  //CRM_Core_Error::debug_var('formName', $formName);
  //CRM_Core_Error::debug_var('form', $form);
} // nyss_mail_civicrm_buildForm()

function mail_civicrm_postProcess($formName, &$form) {
  // NYSS 5581
  if ($formName == 'CRM_Profile_Form_Edit' &&
    $form->getVar('_ufGroupName') == 'Mass_Email_Subscriptions'
  ) {
    $vals = $form->_submitValues;
    $cid = $form->getVar('_id');
    //CRM_Core_Error::debug_var('vals', $vals);

    $allEmails = CRM_Core_DAO::executeQuery("
      SELECT id
      FROM civicrm_email
      WHERE contact_id = {$cid}
    ");

    while ($allEmails->fetch()) {
      $mCatsList = '';
      if (isset($vals['email'][$allEmails->id])) {
        $mCatsList = implode(',', $vals['email'][$allEmails->id]['mailing_categories']);
      }
      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_email
        SET mailing_categories = '{$mCatsList}'
        WHERE id = {$allEmails->id}
      ");
    }

    if (!empty($vals['note'])) {
      $form->assign('noteText', $vals['note']);
    }
  }
  //CRM_Core_Error::debug($form); exit();
}

function mail_civicrm_alterMailParams(&$params, $context) {
  //CRM_Core_Error::debug_var('params', $params);

  $path = CRM_Core_Resources::singleton()->getPath('gov.nysenate.mail');
  require_once $path.'/libs/SmtpApiHeader.php';

  $contentTypes = ['text', 'html'];

  // Rewrite the public URLs to use pubfiles.nysenate.gov
  foreach ($contentTypes as $ctype) {
    if (isset($params[$ctype])) {
      $params[$ctype] = _mail_rewrite_public_urls($params[$ctype]);
    }
  }

  // Confirm that <html>, <head>, <body> elements are present, and add them
  // if necessary.
  $params['html'] = _mail_fixup_html_message($params['html']);

  $hdr = new SmtpApiHeader();
  $bbconfig = get_bluebird_instance_config();

  if (isset($bbconfig['senator.email'])) {
    $senator_email = $bbconfig['senator.email'];
  }
  else {
    $senator_email = '';
  }

  if (!empty($bbconfig['senator.email.replyto'])) {
    $replyto = $bbconfig['senator.email.replyto'];
  }
  elseif ($senator_email != '') {
    $replyto = $senator_email;
  }
  else {
    $replyto = DEFAULT_REPLYTO;
  }

  // A context of "civimail" indicates a mass email job, which requires
  // much more setup than a non-civimail message.
  if ($context == 'civimail') {
    $eventQueueID = $contactID = 0;
    $jobInfo = null;
    $extraContent = array_fill_keys($contentTypes, array());

    if (isset($params['event_queue_id'])) {
      $eventQueueID = $params['event_queue_id'];
      unset($params['event_queue_id']);
    }
    else if (empty($params['is_test'])) {
      CRM_Core_Error::debug_var('params: event_queue_id not found', $params);
    }

    // NYSS 5354 - set the "X-clientid" header
    if (isset($params['contact_id'])) {
      $contactID = $params['contact_id'];
      $params['X-clientid'] = $contactID;
      unset($params['contact_id']);
    }

    $params['Return-Path'] = '';
    $params['List-Unsubscribe'] = '';
    $params['Reply-To'] = $replyto;

    if (isset($params['job_id'])) {
      $jobInfo = _mail_get_job_info($params['job_id']);
      unset($params['job_id']);
    }

    // NYSS 5579 - Construct the whitelisting language and add to e-mail body.
    // NYSS 7423 - Allow location of whitelisting blurb to be configurable.
    // NYSS 7804 - suppress whitelisting blurb if viewing html
    // If contactID is set, then this is a real email (either job or preview).
    // If contactID is not set, then this is an HTML view of the email.
    if (!empty($contactID)) {
      if (!empty($bbconfig['email.extras.include_whitelist'])) {
        if (isset($bbconfig['email.extras.whitelist_location']) &&
          $bbconfig['email.extras.whitelist_location'] == 'bottom') {
          $locidx = 'post_body';
        }
        else {
          $locidx = 'pre_body';
        }
        $s = _mail_get_whitelist_clause($bbconfig);
        $extraContent['text'][$locidx][] = $s['text'];
        $extraContent['html'][$locidx][] = $s['html'];
      }
    }
    else {
      // NYSS 7803 - if viewing HTML, insert FB share image
      $s = _mail_get_opengraph_clause($bbconfig, $params['Subject']);
      $extraContent['text']['head'][] = $s['text'];
      $extraContent['html']['head'][] = $s['html'];
    }

    // NYSS 7701 - append link for hosted email if this is part of a mailing
    // NYSS 4864 - optionally include a "Share on Facebook" link
    if ($jobInfo) {
      if (!empty($jobInfo['mailing_hash'])) {
        $view_id = $jobInfo['mailing_hash'];
      }
      else {
        $view_id = $jobInfo['mailing_id'];
      }

      $view_url = _mail_get_view_url($bbconfig, $view_id);

      // If a VIEWIN_BROWSER_URL token appears in the HTML content, then
      // suppress the auto-appending of the "View in Browser" link.
      $is_viewin_token = strpos($params['html'], '%VIEWIN_BROWSER_URL%');
      if ($is_viewin_token === false &&
        !empty($bbconfig['email.extras.include_browserview'])) {
        $s = _mail_get_browserview_clause($bbconfig);
        $extraContent['text']['post_body'][] = $s['text'];
        $extraContent['html']['post_body'][] = $s['html'];
      }

      // Always auto-append the subscription management/optout link, unless
      // it has been disabled for this CRM instance.
      if (!empty($bbconfig['email.extras.include_optout'])) {
        // NYSS 5581 - opt-out/subscription management link
        $s = _mail_get_optout_clause($bbconfig, $contactID, $eventQueueID);
        $extraContent['text']['post_body'][] = $s['text'];
        $extraContent['html']['post_body'][] = $s['html'];
        // Disable SendGrid Opt-Out as we are handling thru subscription page.
        $hdr->addFilterSetting('subscriptiontrack', 'enable', 0);
      }

      // If a SHAREON_FACEBOOK_URL token appears in the HTML content, then
      // suppress the auto-appending of the "Share on Facebook" link.
      $is_shareon_token = strpos($params['html'], '%SHAREON_FACEBOOK_URL%');
      if ($is_shareon_token === false &&
        !empty($bbconfig['email.extras.include_shareon'])) {
        $s = _mail_get_shareon_clause($bbconfig);
        $extraContent['text']['post_body'][] = $s['text'];
        $extraContent['html']['post_body'][] = $s['html'];
      }
    }
    else {
      // In this case, we are viewing in a browser, so there is no job info
      // available.  However, the URL has the view ID in it.
      $view_id = $_GET['id'];
      $view_url = _mail_get_view_url($bbconfig, $view_id);
    }

    $token_replacements = array(
      '%SENATOR_EMAIL%' => $senator_email,
      '%SHAREON_FACEBOOK_URL%' => "https://www.facebook.com/sharer/sharer.php?u=$view_url",
      '%SHAREON_TWITTER_URL%' => "https://twitter.com/intent/tweet?url=$view_url&text=New York State Senate",
      '%SHAREON_REDDIT_URL%' => "https://www.reddit.com/submit?url=$view_url",
      '%VIEWIN_BROWSER_URL%' => $view_url,
      '%MANAGE_SUBSCRIPTIONS_URL%' => ''
    );

    // Add extra content (OpenGraph, whitelist, browser-view, opt-out, share-on)
    // and replace any tokens.
    foreach ($contentTypes as $ctype) {
      if (isset($params[$ctype])) {
        $params[$ctype] = _mail_add_extra_content($params[$ctype], $extraContent[$ctype], $ctype);
        $params[$ctype] = _mail_replace_tokens($params[$ctype], $token_replacements);
      }
    }

    //Sendgrid headers
    $hdr->setCategory("BluebirdMail: {$jobInfo['mailing_name']} (ID: {$jobInfo['mailing_id']})");
    $hdr->setUniqueArgs(array(
      'instance' => $bbconfig['shortname'],
      'install_class' => $bbconfig['install_class'],
      'servername' => $bbconfig['servername'],
      'mailing_id' => $jobInfo['mailing_id'],
      'job_id' => $jobInfo['job_id'],
      'queue_id' => $eventQueueID,
      'is_test' => $jobInfo['is_test']
    ));
  }
  else {
    // For non-Civimail messages, disable subscription/click/open tracking
    // Sendgrid SMTP-API
    $hdr->setCategory('Bluebird Activity');
    $hdr->setUniqueArgs(array(
      'instance' => $bbconfig['shortname'],
      'install_class' => $bbconfig['install_class'],
      'servername' => $bbconfig['servername']
    ));
    $hdr->addFilterSetting('subscriptiontrack', 'enable', 0);
    $hdr->addFilterSetting('clicktrack', 'enable', 0);
    $hdr->addFilterSetting('opentrack', 'enable', 0);
    //$params['replyTo'] = $replyto;
  }

  // Prevent Sendgrid from dropping any of our messages.
  $hdr->addFilterSetting('bypass_list_management', 'enable', 1);

  $params['headers']['X-SMTPAPI'] = $hdr->asJSON();

  //CRM_Core_Error::debug('session', $_SESSION);
  //CRM_Core_Error::debug_var('params', $params);
}

function mail_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  //CRM_Core_Error::debug_var('$formName', $formName);
  //CRM_Core_Error::debug_var('$form', $form);
  //CRM_Core_Error::debug_var('$context', $context);
  //CRM_Core_Error::debug_var('$tplName', $tplName);

  if ($formName == 'CRM_Profile_Form_Edit' &&
    $form->getVar('_ufGroupName') == 'Mass_Email_Subscriptions') {
    $tplName = 'CRM/NYSS/Subscription.tpl';
  }

  // NYSS 5581
  if ($formName == 'CRM_Profile_Page_View') {
    $gid = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_uf_group
      WHERE name = 'Mass_Email_Subscriptions'
    ");

    if ($form->getVar('_gid') == $gid) {
      $tplName = 'CRM/NYSS/SubscriptionView.tpl';
    }
  }
} // nyss_mail_civicrm_alterTemplateFile()

function mail_civicrm_permission_check($permission, &$granted) {
  /*Civi::log()->debug('mail_civicrm_permission_check', [
    '$permission' => $permission,
    '$granted' => $granted,
    'current_path' => current_path(),
  ]);*/

  //current_path() is not available via the CLI; we don't need the permission checks
  //in that context anyway, so simply return early
  if (!function_exists('current_path') || !function_exists('user_access')) {
    return;
  }

  //13174 grant access to mailing tab if user has any of the mailing perms
  if ($permission == 'access CiviMail') {
    if (current_path() == 'civicrm/contact/view' &&
      user_access("view all contacts")
    ) {
      $granted = TRUE;
    }
  }

  //13174 view email content
  if ($permission == 'view public CiviMail content') {
    if (current_path() == 'civicrm/mailing/view' &&
      user_access("view all contacts")
    ) {
      $granted = TRUE;
    }
  }
}

//NYSS 4870
function _mail_removeOnHold($mailingID) {
  $sql = "
    DELETE FROM civicrm_mailing_recipients
    USING civicrm_mailing_recipients
    JOIN civicrm_email
      ON civicrm_mailing_recipients.email_id = civicrm_email.id
      AND civicrm_email.on_hold > 0
    WHERE civicrm_mailing_recipients.mailing_id = %1
 ";
  $params = array(1 => array($mailingID, 'Integer'));

  CRM_Core_DAO::executeQuery($sql, $params);
}

/**
 * @param phpQueryObject $doc
 *
 * construct custom wizard html
 */
function _mail_alterMailingWizard(phpQueryObject $doc) {
  $extDir = CRM_Core_Resources::singleton()->getPath('gov.nysenate.mail');
  $html = file_get_contents($extDir.'/html/workflow.html');
  $doc->find('div[ng-form=crmMailingSubform]')->html($html);
}

/**
 * @param phpQueryObject $doc
 *
 * inject custom fields
 */
function _mail_alterMailingBlock(phpQueryObject $doc) {
  //NYSS 5581 - mailing category options
  $catOptions = "<option value=''>- select -</option>";
  $opts = CRM_Core_DAO::executeQuery("
    SELECT ov.label, ov.value
    FROM civicrm_option_value ov
    JOIN civicrm_option_group og
      ON ov.option_group_id = og.id
      AND og.name = 'mailing_categories'
    ORDER BY ov.label
  ");
  while ($opts->fetch()) {
    $catOptions .= "<option value='{$opts->value}'>{$opts->label}</option>";
  }

  $doc->find('.crm-group')->append('
    <div crm-ui-field="{name: \'subform.nyss\', title: \'Mailing Category\', help: hs(\'category\')}">
      <select 
        crm-ui-id="subform.nyss" 
        crm-ui-select="{dropdownAutoWidth : true, allowClear: true, placeholder: ts(\'Category\')}"
        name="category" 
        ng-model="mailing.category"
      >'.$catOptions.'</select>
    </div>
    <div crm-ui-field="{name: \'subform.nyss\', title: \'Send to all contact emails?\', help: hs(\'all-emails\')}">
      <input
        type="checkbox"
        crm-ui-id="subform.nyss"
        name="all_emails" 
        ng-model="mailing.all_emails"
        ng-true-value="\'1\'"
        ng-false-value="\'0\'"
      >
    </div>
  ');
}

function _mail_alterMailingReview(phpQueryObject $doc) {
  $extDir = CRM_Core_Resources::singleton()->getPath('gov.nysenate.mail');
  $html = file_get_contents($extDir.'/html/BlockReview.html');
  $doc->find('.crm-group')->html($html);
}

function _mail_alterMailingPreview(phpQueryObject $doc) {
  //12136 set var so we can manipulate in apiWrappers
  CRM_Core_Session::singleton()->set('nyss-mailing-preview', TRUE);
}

// NYSS 4628
function _mail_addAllEmails($mailingID, $excludeOOD = FILTER_ALL) {
  $sql = "
    INSERT IGNORE INTO civicrm_mailing_recipients (mailing_id, email_id, contact_id)
    SELECT DISTINCT %1, e.id, e.contact_id
    FROM civicrm_email e
    JOIN civicrm_mailing_recipients mr
      ON e.contact_id = mr.contact_id
      AND mr.mailing_id = %1
      AND e.on_hold = 0
    WHERE e.id NOT IN (
      SELECT email_id
      FROM civicrm_mailing_recipients mr
      WHERE mailing_id = %1
    )
  ";
  $params = array(1 => array($mailingID, 'Integer'));
  CRM_Core_DAO::executeQuery($sql, $params);
} // _addAllEmails()

// NYSS 4879
function _mail_excludeOOD($mailingID, $excludeOOD) {
  //determine what SD we are in
  $bbconfig = get_bluebird_instance_config();
  $district = $bbconfig['district'];

  if (empty($district)) {
    return;
  }

  //create temp table to store contacts confirmed to be in district
  $tempTbl = "nyss_temp_excludeOOD_$mailingID";
  $sql = "CREATE TEMPORARY TABLE $tempTbl(contact_id INT NOT NULL, PRIMARY KEY(contact_id)) ENGINE=MyISAM;";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "
    INSERT INTO $tempTbl
    SELECT DISTINCT mr.contact_id
    FROM civicrm_mailing_recipients mr
    JOIN civicrm_address a
      ON mr.contact_id = a.contact_id
    JOIN civicrm_value_district_information_7 di
      ON a.id = di.entity_id
    WHERE mailing_id = $mailingID
      AND ny_senate_district_47 = $district;
  ";
  CRM_Core_DAO::executeQuery($sql);

  //also include unknowns if option enabled
  if ($excludeOOD == FILTER_IN_SD_OR_NO_SD) {
    //include where no district is known or no address is present
    $sql = "
      INSERT INTO $tempTbl
      SELECT mr.contact_id
      FROM civicrm_mailing_recipients mr
      LEFT JOIN civicrm_address a
        ON mr.contact_id = a.contact_id
      LEFT JOIN civicrm_value_district_information_7 di
        ON a.id = di.entity_id
      WHERE mr.mailing_id = $mailingID
      GROUP BY mr.contact_id
      HAVING COUNT(di.ny_senate_district_47) = 0
    ";
    CRM_Core_DAO::executeQuery($sql);
  }

  $sql = "
    DELETE FROM civicrm_mailing_recipients
    USING civicrm_mailing_recipients
    LEFT JOIN $tempTbl
      ON civicrm_mailing_recipients.contact_id = $tempTbl.contact_id
    WHERE civicrm_mailing_recipients.mailing_id = $mailingID
      AND $tempTbl.contact_id IS NULL;
  ";
  CRM_Core_DAO::executeQuery($sql);

  //cleanup
  CRM_Core_DAO::executeQuery("DROP TABLE $tempTbl");
} // _excludeOOD()

// NYSS 5581
function _mail_excludeCategoryOptOut($mailingID, $mailingCat) {
  $sql = "
    DELETE FROM civicrm_mailing_recipients
    USING civicrm_mailing_recipients
    JOIN civicrm_email
      ON civicrm_mailing_recipients.email_id = civicrm_email.id
    WHERE FIND_IN_SET({$mailingCat}, civicrm_email.mailing_categories)
      AND civicrm_mailing_recipients.mailing_id = $mailingID
  ";
  //CRM_Core_Error::debug_var('sql', $sql);
  CRM_Core_DAO::executeQuery($sql);
} // _excludeCategoryOptOut()

function _mail_addEmailSeeds($mailingID) {
  $gid = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_group WHERE name LIKE 'Email_Seeds';");

  if (!$gid) {
    return;
  }

  $sql = "
    INSERT INTO civicrm_mailing_recipients ( mailing_id, contact_id, email_id )
    SELECT $mailingID, e.contact_id, e.id
    FROM civicrm_group_contact gc
    JOIN civicrm_email e
      ON gc.contact_id = e.contact_id
      AND gc.group_id = $gid
      AND gc.status = 'Added'
      AND e.on_hold = 0
      AND ( e.is_primary = 1 OR e.is_bulkmail = 1 )
    JOIN civicrm_contact c
      ON gc.contact_id = c.id
    LEFT JOIN civicrm_mailing_recipients mr
      ON gc.contact_id = mr.contact_id
      AND mr.mailing_id = $mailingID
    WHERE mr.id IS NULL
      AND c.is_deleted = 0;
  ";
  CRM_Core_DAO::executeQuery($sql);
} // _addEmailSeeds()

function _mail_dedupeEmail($mailingID) {
  //if dedupeEmails, handle that now, as it was skipped earlier in the process
  $tempTbl = "nyss_temp_dedupe_emails_{$mailingID}";
  $sql = "CREATE TEMPORARY TABLE $tempTbl (email_id INT NOT NULL, PRIMARY KEY(email_id)) ENGINE=MyISAM;";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "
    INSERT INTO $tempTbl
    SELECT ANY_VALUE(mr.email_id) email_id
    FROM civicrm_mailing_recipients mr
    JOIN civicrm_email e
      ON mr.email_id = e.id
    WHERE mailing_id = %1
    GROUP BY e.email;
  ";
  CRM_Core_DAO::executeQuery($sql, array(1 => array($mailingID, 'Positive')));

  //now remove contacts from the recipients table that are not found in the deduped table
  $sql = "
    DELETE FROM civicrm_mailing_recipients
    USING civicrm_mailing_recipients
    LEFT JOIN $tempTbl
      ON civicrm_mailing_recipients.email_id = $tempTbl.email_id
    WHERE civicrm_mailing_recipients.mailing_id = %1
      AND $tempTbl.email_id IS NULL;
  ";
  CRM_Core_DAO::executeQuery($sql, array(1 => array($mailingID, 'Positive')));

  //cleanup
  CRM_Core_DAO::executeQuery("DROP TABLE $tempTbl");
}

/**
 * @param $mailingId
 *
 * the mailing recipients should already be deduped by contact/email
 * this is a failsafe to ensure it is properly deduped
 */
function _mail_dedupeContacts($mailingId) {
  CRM_Core_DAO::executeQuery("
    DELETE a
    FROM civicrm_mailing_recipients AS a, civicrm_mailing_recipients AS b
    WHERE a.id < b.id
      AND a.mailing_id <=> b.mailing_id
      AND a.contact_id <=> b.contact_id
      AND a.email_id <=> b.email_id
      AND a.mailing_id = %1
  ", array(
    1 => array($mailingId, 'Positive')
  ));
}

/**
 * @param $mailingID
 *
 * small helper function to output mailing recipients list to log
 * for debugging purposes
 */
function _mail_logRecipients($note, $mailingID) {
  if (BB_MAIL_LOG) {
    $dao = CRM_Core_DAO::executeQuery("
      SELECT mr.email_id, mr.contact_id, e.email
      FROM civicrm_mailing_recipients mr
      JOIN civicrm_email e 
        ON mr.email_id = e.id
      WHERE mr.mailing_id = {$mailingID}
      ORDER BY mr.email_id
    ");

    $rows = array();
    while ($dao->fetch()) {
      $rows[] = "EID: {$dao->email_id} | CID: {$dao->contact_id} | Email: {$dao->email}";
    }

    Civi::log()->debug('_mail_logRecipients: '.$note, $rows);
  }
}

function _mail_fixup_html_message($m) {
  $added_tags = '';

  // The <body> tag is typically in the header template, while the </body>
  // tag is in the footer template.  So check for both separately.
  if (stripos($m, '<body') === false) {
    $m = '<body style="font-family:arial; font-size:14px; color:#505050; background-color:#ffffff;" leftmargin="0" topmargin="0" marginheight="0" marginwidth="0" offset="0">'."\n$m";
    $added_tags .= ' BODY';
  }
  if (stripos($m, '</body>') === false) {
    $m .= "\n</body>";
    $added_tags .= ' /BODY';
  }

  // The <head> and </head> tags are typically both in the header template.
  if (stripos($m, '<head') === false) {
    $m = "<head>\n<title>New York State Senate</title>\n</head>\n$m";
    $added_tags .= ' HEAD TITLE /TITLE /HEAD';
  }
  else if (stripos($m, '</head>') === false) {
    $m = str_ireplace('<body', "</head>\n<body", $m);
    $added_tags .= ' /HEAD';
  }

  // The <html> and </html> tags are separated in the header & footer templates.
  if (stripos($m, '<html>') === false) {
    $m = "<html>\n$m";
    $added_tags .= ' HTML';
  }
  if (stripos($m, '</html>') === false) {
    $m .= "\n</html>";
    $added_tags .= ' /HTML';
  }

  if (!empty($added_tags)) {
    $m .= "\n<!-- AutoInserted Tags: $added_tags -->";
  }
  return $m;
} // _mail_fixup_html_message()


/* Re-write any URLs in the message body of the form:
 *   <sitename>/sites/<sitename>/pubfiles [old format]
 * or
 *   <sitename>/data/<shortname>/pubfiles [new format]
 *   (where <shortname> is typically the senator's last name and
 *          <envname> is "crm", "crmdev", "crmtest", etc. and
 *          <sitename> is <shortname>.<envname>.nysenate.gov)
 * into:
 *   pubfiles.nysenate.gov/<envname>/<shortname>/
*/
function _mail_rewrite_public_urls($s) {
  $patterns = array(
    // Legacy "/sites/" URLs
    '#[\w-]+\.(crm[\w]*)\.nysenate\.gov/sites/([\w-]+)\.crm[\w]*\.nysenate\.gov/pubfiles/#i',
    // Standard "/data/" URLs
    '#[\w-]+\.(crm[\w]*)\.nysenate\.gov/data/([\w-]+)/pubfiles/#i',
  );
  $replacement = 'pubfiles.nysenate.gov/$1/$2/';

  // Two patterns.  One replacement.  One call to preg_replace().
  return preg_replace($patterns, $replacement, $s);
} // _mail_rewrite_public_urls()


function _mail_get_job_info($jid) {
  $mJob = CRM_Core_DAO::executeQuery("SELECT mailing_id, is_test FROM civicrm_mailing_job WHERE id = $jid;");
  while ($mJob->fetch()) {
    $mid = $mJob->mailing_id;
    $test = $mJob->is_test;
  }
  $mJob->free();

  $m = CRM_Core_DAO::executeQuery("SELECT name, hash FROM civicrm_mailing WHERE id = $mid;");
  while ($m->fetch()) {
    $mname = $m->name;
    $hash = $m->hash;
  }

  return array('job_id'=>$jid, 'mailing_id'=>$mid, 'is_test'=>$test,
    'mailing_name'=>$mname, 'mailing_hash'=>$hash);
} // _mail_get_job_info()


function _mail_get_whitelist_clause($bbcfg) {
  if (!empty($bbcfg['email.extras.whitelist_html'])) {
    $html = $bbcfg['email.extras.whitelist_html'];
  }
  else {
    $html = 'To ensure delivery of emails to your inbox, please add <a href="mailto:%SENATOR_EMAIL%">%SENATOR_EMAIL%</a> to your email address book.';
  }

  if (!empty($bbcfg['email.extras.whitelist_text'])) {
    $text = $bbcfg['email.extras.whitelist_text'];
  }
  else {
    $text = 'To ensure delivery of emails to your inbox, please add %SENATOR_EMAIL% to your email address book.';
  }

  return array('text' => $text, 'html' => $html);
} // _mail_get_whitelist_clause()


function _mail_get_view_url($bbcfg, $viewId) {
  $url = "http://pubfiles.nysenate.gov/{$bbcfg['envname']}/{$bbcfg['shortname']}/view/$viewId";
  //$url = CRM_Utils_System::url('civicrm/mailing/view', 'reset=1&id='.$viewId, true);
  return $url;
} // _mail_get_view_url()


function _mail_get_browserview_clause($bbcfg) {
  $text = 'To view this email in your browser, go to %VIEWIN_BROWSER_URL%';
  $html = '<a href="%VIEWIN_BROWSER_URL%" target="_blank">Click here</a> to view this email in your browser.';
  return array('text' => $text, 'html' => $html);
} // _mail_get_browserview_clause()


function _mail_get_optout_clause($bbcfg, $cid, $qid) {
  $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($cid);
  $url = "http://pubfiles.nysenate.gov/{$bbcfg['envname']}/{$bbcfg['shortname']}/subscription/manage/$qid/$cs";

  $text = "To manage your email subscription settings or to unsubscribe, go to $url";
  $html = '<a href="'.$url.'" target="_blank">Click here</a> to manage your email subscription settings or to unsubscribe.';

  return array('text' => $text, 'html' => $html);
} // _mail_get_optout_clause()


function _mail_get_shareon_clause($bbcfg) {
  $fbimg = "http://pubfiles.nysenate.gov/{$bbcfg['envname']}/{$bbcfg['shortname']}/common/images/social_media/facebook_share_68x25.png";

  $text = 'To share this on Facebook, go to %SHAREON_FACEBOOK_URL%';
  $html = '<a style="color:#386eff; text-decoration:underline;" href="%SHAREON_FACEBOOK_URL%" target="_blank">Share&nbsp;on&nbsp;Facebook.</a>';
  return array('text' => $text, 'html' => $html);
} // _mail_get_shareon_clause()


function _mail_get_opengraph_clause($bbcfg, $subj) {
  $senator_name = $bbcfg['senator.name.formal'];
  $url = "http://pubfiles.nysenate.gov/{$bbcfg['envname']}/{$bbcfg['shortname']}/common/images/nysenate_logo_200.png";
  $text = '';
  $metas = array(
    '<meta property="og:type" content="article" />',
    '<meta property="og:title" content="'.$subj.'" />',
    '<meta property="og:description" content="From the desk of '.$senator_name.'" />',
    '<meta property="og:image" content="'.$url.'" />',
    '<meta name="twitter:title" content="'.$subj.'" />',
    '<meta name="twitter:description" content="From the desk of '.$senator_name.'" />',
    '<meta name="twitter:image" content="'.$url.'" />',
    '<link rel="image_src" type="image/png" href="'.$url.'" />'
  );
  $html = implode("\n", $metas);
  return array('text' => $text, 'html' => $html);
} // _mail_get_opengraph_clause()


function _mail_add_extra_content($msg, $extra, $ctype) {
  $sep = ($ctype == 'text') ? "\n" : "\n<br/>\n";

  // Each of the three "extra" variables is an array of items.
  $extraHead = implode($sep, $extra['head']);
  $extraPreBody = implode($sep, $extra['pre_body']);
  $extraPostBody = implode($sep, $extra['post_body']);

  if ($ctype == 'text') {
    $msg = "$extraHead\n$extraPreBody\n$msg\n$extraPostBody";
  }
  else {
    $patterns = array(
      '#(\s*</head>)#',
      '/(<body( [^>]*)?>\s*)/',
      '#(\s*</body>)#'
    );
    $attr = 'style="text-align:center; font:10px/12px Helvetica, Arial, sans-serif; color:#3f3f3f; padding:0 10px 30px;"';
    $replacements = array(
      "\n<!-- Extra HEAD content -->\n$extraHead\$1",
      "\$1<div id=\"extra_prebody_content\" $attr>\n$extraPreBody\n</div>\n",
      "\n<div id=\"extra_postbody_content\" $attr>\n$extraPostBody\n</div>\$1"
    );
    $msg = preg_replace($patterns, $replacements, $msg);
  }
  return $msg;
} // _mail_add_extra_content()


function _mail_replace_tokens($msg, $token_map) {
  $patterns = array_keys($token_map);
  $replacements = array_values($token_map);
  return str_replace($patterns, $replacements, $msg);
} // _mail_replace_tokens()
