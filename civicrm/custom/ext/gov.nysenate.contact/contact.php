<?php

require_once 'contact.civix.php';
use CRM_NYSS_Contact_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contact_civicrm_config(&$config) {
  _contact_civix_civicrm_config($config);

  $session = CRM_Core_Session::singleton();

  //NYSS JobID handling
  $jobID = $session->get('jobID');
  if ($jobID) {
    CRM_Core_DAO::executeQuery('SET @jobID = %1',
      [1 => [$jobID, 'String']]
    );
  }

  //prevent multiple calls
  if (isset(Civi::$statics[__FUNCTION__])) { return; }
  Civi::$statics[__FUNCTION__] = 1;

  // NYSS #14192, #17702, #17730
  Civi::dispatcher()->addListener('civi.token.eval', ['CRM_NYSS_Contact_TokenEvalListener', 'evalToken'], -1000);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contact_civicrm_install() {
  _contact_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contact_civicrm_enable() {
  _contact_civix_civicrm_enable();
}

function contact_civicrm_pageRun(&$page) {
  //Civi::log()->debug('contact_civicrm_pageRun', array('page' => $page));

  if (is_a($page, 'CRM_Contact_Page_View_Summary')) {
    $cid = $page->getVar('_contactId');
    $contact = civicrm_api3('contact', 'getsingle', [
      'id' => $cid,
      'return' => [
        'supplemental_address_1',
        'street_address',
        'city',
        'state_province',
        'postal_code',
        'phone',
        'email',
        'id',
        'custom_19'
      ]
    ]);
    //Civi::log()->debug('contact_civicrm_pageRun', array('$contact' => $contact));

    $supp1 = (!empty($contact['supplemental_address_1'])) ? ", {$contact['supplemental_address_1']}" : '';
    $html = "
      <div class='bb-contactsummary'>
        {$contact['street_address']}{$supp1}, {$contact['city']}, {$contact['state_province']} {$contact['postal_code']}<br />
        {$contact['phone']} | {$contact['email']} | Contact ID: {$contact['id']}
      </div>
    ";
    $html = str_replace('| |', '|', $html);
    $html = str_replace(', ,', ',', $html);

    CRM_Core_Resources::singleton()->addVars('NYSS', ['bbContactSummary' => $html]);
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/ContactSummary.js');

    if (!empty($contact['custom_19'])) {
      CRM_Core_Resources::singleton()->addStyle('
        div.crm-actions-ribbon {
          border-bottom: 5px solid #FBCA54 !important;
        }
      ');
    }
  }

  if (is_a($page, 'CRM_Activity_Page_Tab')) {
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.contact', 'js/ActivityTab.js');
  }

  $pagename = $page->getVar('_name');
  //CRM_Core_Error::debug_var('pageRun $pagename', $pagename);

  //5412
  if ($pagename == 'CRM_Contact_Page_Inline_Address') {
    $smarty = CRM_Core_Smarty::singleton();
    if ($smarty->get_template_vars('privacy') === NULL) {
      $cid = $smarty->get_template_vars('contactId');
      $contact = civicrm_api('contact', 'getsingle', array('version' => 3, 'id' => $cid));
      //CRM_Core_Error::debug_var('contact', $contact);

      $privacy = [];
      foreach ($contact as $f => $v) {
        if (strpos($f, 'do_not_') !== FALSE || $f == 'is_opt_out') {
          $privacy[$f] = $v;
        }
      }
      //CRM_Core_Error::debug_var('$privacy', $privacy);
      $page->assign('privacy', $privacy);
    }
  }

  //8547
  if ($pagename == 'CRM_Contact_Page_View_CustomData'
    && $page->_groupId == 9
  ) {
    $cid = $page->_contactId;
    $webId = CRM_NYSS_BAO_Integration_Website::getWebId($cid);
    //CRM_Core_Error::debug_var('pageRun $webId', $webId);

    if ($webId) {
      $bbcfg = get_bluebird_instance_config();
      if (isset($bbcfg['website.url'])) {
        $baseUrl = $bbcfg['website.url'];
      }
      else {
        $baseUrl = 'http://nysenate.gov';
      }
      $webUserUrl = "$baseUrl/user/$webId";
      $page->assign('webUserURL', $webUserUrl);
    }
  }

  //11059
  if ($pagename == 'CRM_Activity_Page_Tab') {
    CRM_Core_Region::instance('page-body')->add([
      'style' => "
        table.contact-activity-selector-activity {
          /*table-layout: fixed;*/
        }
        table.contact-activity-selector-activity td span > a.action-item,
        table.contact-activity-selector-activity span.btn-slide{
          float: left;
        }
        .crm-container .paging_full_numbers {
          width: 400px !important;
        }
      ",
    ]);
  }

  //NYSS 11383 sort activity types
  if (in_array($pagename, [
    'CRM_Contact_Page_View_Summary',
    'CRM_Activity_Page_Tab',
  ])) {
    $activityTypes = $page->get_template_vars('activityTypes');
    uasort($activityTypes, ['CRM_NYSS_Utils_Sort', 'cmpLabel']);
    $page->assign('activityTypes', $activityTypes);
  }

  //NYSS 11335 truncate long file names (attachments panel)
  if (in_array($pagename, [
    'CRM_Contact_Page_View_Summary',
    'CRM_Contact_Page_Inline_CustomData',
  ])) {
    $viewCustomData = $page->get_template_vars('viewCustomData');
    $viewCustomDataInline = $page->get_template_vars('cd_edit');
    //Civi::log()->debug('', array('viewCustomData' => $viewCustomData));

    $modified = FALSE;
    foreach ($viewCustomData as &$set) {
      foreach ($set as &$details) {
        //Civi::log()->debug('', array('$details' => $details));
        if ($details['name'] == 'Attachments') {
          foreach ($details['fields'] as $key => &$field) {
            $doc = phpQuery::newDocument($field['field_value']);
            $text = $doc->find('a')->text();
            $maxLength = 35;
            if (strlen($text) > $maxLength) {
              $text = substr($text, 0, $maxLength).'...';
              $doc->find('a')->text($text);
              $field['field_value'] = $doc->html();

              if (!empty($viewCustomDataInline)) {
                $viewCustomDataInline['fields'][$key]['field_value'] = $doc->html();
              }

              $modified = TRUE;
            }

            // It's good practice to call unloadDocuments() when finished with
            // phpQuery documents. Though, not the case here, big documents
            // can consume a lot of memory. This releases the memory.
            phpQuery::unloadDocuments($doc->getDocumentID());

            /*Civi::log()->debug('', array(
              //'$doc' => $doc,
              'text' => $text,
              '$field[field_value]' => $field['field_value'],
            ));*/
          }
        }
      }
    }

    if ($modified) {
      $page->assign('viewCustomData', $viewCustomData);
      $page->assign('cd_edit', $viewCustomDataInline);
    }
  }

  _contact_fixTitles();

  //4567 make admin breadcrumb unclickable if lacking permission
  if ($pagename == 'CRM_Admin_Page_Tag' ||
    $pagename == 'CRM_Admin_Page_Mapping' ||
    $pagename == 'CRM_Report_Page_TemplateList'
  ) {
    if (CRM_Core_Permission::check('administer CiviCRM')) {
      return;
    }

    $breadCrumb = drupal_get_breadcrumb();
    foreach ($breadCrumb as $key => $crumb) {
      if ($crumb == '<a href="/civicrm/admin?reset=1">Administer Bluebird</a>') {
        $breadCrumb[$key] = 'Administer Bluebird';
      }
    }
    drupal_set_breadcrumb($breadCrumb);
  }

  //NYSS 5149
  if ($pagename == 'CRM_Dashlet_Page_Activity' ||
    $pagename == 'CRM_Activity_Page_Tab') {
    $activityStatusList = CRM_Core_PseudoConstant::activityStatus();
    $page->assign('activityStatusList', $activityStatusList);
  }

  if ($pagename == 'CRM_Group_Page_Group') {
    CRM_Core_Resources::singleton()->addScriptUrl('/sites/all/modules/nyss_civihooks/js/groupsList.js');
  }
}

function contact_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug(__FUNCTION__, [
    'formName' => $formName,
    'form' => $form,
    '_elementIndex' => $form->_elementIndex,
  ]);*/

  //13249
  if ($formName == 'CRM_Contact_Form_Inline_Address' ||
    $formName == 'CRM_Contact_Form_Contact'
  ) {
    foreach ($form->_elementIndex as $ele => $val) {
      if (strpos($ele, 'is_billing') !== FALSE) {
        $form->removeElement($ele);
      }
    }

    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/InlineAddress.js');
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/DistrictInformation.js');
  }

  //14192
  if ($formName == 'CRM_Contact_Form_Task_Label') {
    $form->addElement('checkbox', 'include_title_org', ts('Include Job Title and Organization Name'), NULL);

    CRM_Core_Region::instance('form-body')->add([
      'template' => 'CRM/NYSS/TaskLabels.tpl',
    ]);
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/TaskLabels.js');
  }

  //13832
  if ($formName == 'CRM_Contact_Form_Contact') {
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/DemographicsForm.js');

    if ($form->_action == CRM_Core_Action::ADD) {
      Civi::resources()->addScript("CRM.$('.address-custom-cell').remove();");
    }
    else {
      CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/DistrictInformation.js');
    }

    //3527 add js action to deceased field
    if (isset($form->_elementIndex['is_deceased'])) {
      $deceased =& $form->getElement('is_deceased');
      $js = "showDeceasedDate();processDeceased();";
      $deceased->_attributes['onclick'] = $js;
    }

    //3530 tweak js to place cursor at end of http in website field (IE8)
    if (isset($form->_elementIndex['website[1][url]'])) {
      $website =& $form->getElement('website[1][url]');
      $js = "if(!this.value) {
        this.value='http://';
        if (this.createTextRange) {
          var FieldRange = this.createTextRange();
          FieldRange.moveStart('character', this.value.length);
          FieldRange.collapse();
          FieldRange.select();
        }
      } else { return false; }";
      $website->_attributes['onfocus'] = $js;
    }

    //NYSS 4407 remove bulk email from privacy list as it is a separate element
    if (isset($form->_elementIndex['privacy'])) {
      $privacy =& $form->getElement('privacy');
      foreach ($privacy->_elements as $key=>$option) {
        if ($option->_attributes['name'] == 'is_opt_out') {
          unset($privacy->_elements[$key]);
        }
      }
    }
  }

  //14808
  if ($formName == 'CRM_Contact_Form_Contact' && $form->_contactType == 'Individual') {
    //set personal pronoun custom field for use in tpl
    $cfId = CRM_Core_BAO_CustomField::getCustomFieldID('preferred_pronouns', 'Additional_Constituent_Information', FALSE);
    $form->assign('cf_preferred_pronoun_id', $cfId);
  }

  if (in_array($formName, ['CRM_Contact_Form_Edit_Demographics', 'CRM_Contact_Form_Inline_Demographics'])) {
    CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/DemographicsForm.js');
  }

  //15495
  if ($formName == 'CRM_Contactlayout_Form_Inline_ProfileBlock') {
    if ($form->elementExists('current_employer')) {
      $ele =& $form->getElement('current_employer');
      //Civi::log()->debug(__FUNCTION__, ['ele' => $ele]);

      if (!empty($ele->_attributes['value'])) {
        $ele->_attributes['value'] = $ele->_attributes['value'][0] ?? NULL;
      }
      elseif (is_array($ele->_attributes['value'])) {
        $ele->_attributes['value'] = NULL;
      }
    }
  }

  //16473 - make sure not date extends to future; may be able to revert in future version
  if ($formName == 'CRM_Note_Form_Note') {
    if ($form->elementExists('note_date')) {
      $ele =& $form->getElement('note_date');
      //Civi::log()->debug(__FUNCTION__, ['ele' => $ele]);

      $existingMaxYear = date('Y', strtotime('-10 year'));
      $newMaxYear = date('Y', strtotime('+10 year'));
      $ele->_attributes['data-crm-datepicker'] = str_replace($existingMaxYear, $newMaxYear, $ele->_attributes['data-crm-datepicker']);
    }
  }

  //16734 - suppress public title/description fields
  if ($formName == 'CRM_Group_Form_Edit') {
    if ($form->elementExists('frontend_title')) {
      $form->removeElement('frontend_title');
    }
    if ($form->elementExists('frontend_description')) {
      $form->removeElement('frontend_description');
    }
  }

  //NYSS 11383 sort activity types
  if (in_array($formName, [
    'CRM_Activity_Form_Search',
    'CRM_Contact_Form_Search_Advanced',
    'CRM_Activity_Form_Activity',
  ])) {
    if ($form->elementExists('activity_type_id')) {
      $activityTypeFld =& $form->getElement('activity_type_id');
      uasort($activityTypeFld->_options, ['CRM_NYSS_Utils_Sort', 'cmpText']);
      //CRM_Core_Error::debug_var('buildForm $activityTypeFld', $activityTypeFld);
    }
  }

  //NYSS 11385
  if (in_array($formName, ['CRM_Activity_Form_ActivityFilter', 'CRM_Dashlet_Page_AllActivities'])) {
    $form->setDefaults([
      'status_id' => [],
    ]);
  }

  //11443
  if ($formName == 'CRM_Activity_Form_Search') {
    if ($form->elementExists('activity_test')) {
      CRM_Core_Resources::singleton()->addScript("
        CRM.$(function($) {
          $('input[name=activity_test]').parent('td').text('');
        });
      ");
    }
  }

  //11464
  if ($formName == 'CRM_Activity_Form_Activity') {
    $max_target_msg = $form->get_template_vars('max_target_msg');
    if ($max_target_msg) {
      CRM_Core_Resources::singleton()->addScript("
        $('tr.crm-activity-form-block-target_contact_id td.label').append('<br /><span style=\'font-size:90%; font-style: italic;\'><strong>Note: </strong>You will not be able to edit the target (with) contact list as there are more than 250 contacts already attached to this activity.</span>');
      ");
    }
  }

  _contact_fixTitles();

  //Limit import file size to 1MB
  if ($formName =='CRM_Import_Form_DataSource') {
    $uploadFileSize = 1048576;
    $uploadSize = round(($uploadFileSize / (1024*1024)), 2);
    $form->assign('uploadSize', $uploadSize);
    $form->add('file', 'uploadFile', ts('Import Data File'), 'size=30 maxlength=60', true);

    $form->setMaxFileSize($uploadFileSize);
    $form->addRule('uploadFile', ts('File size should be less than %1 MBytes (%2 bytes)', array(1 => $uploadSize, 2 => $uploadFileSize)), 'maxfilesize', $uploadFileSize);
    $form->addRule('uploadFile', ts('Input file must be in CSV format'), 'utf8File');
    $form->addRule('uploadFile', ts('A valid file must be uploaded.'), 'uploadedfile');
    $form->_rules['uploadFile'][1]['message'] = 'File size should be less than 1 MBytes (1048576 bytes)';
  }

  //set NY as default for state field in proximity search
  if ($formName == 'CRM_Contact_Form_Search_Custom' &&
    $form->getVar('_customSearchClass') == 'CRM_Contact_Form_Search_Custom_Proximity') {
    $defaults['state_province_id'] = 1031;
    $form->setDefaults($defaults);
  }

  //5154
  if ($formName == 'CRM_Contact_Form_Search_Custom') {
    $bC = drupal_get_breadcrumb();
    foreach ($bC as $k => $v) {
      if (strpos($v, 'Custom Searches') !== false) {
        unset($bC[$k]);
      }
    }
    drupal_set_breadcrumb($bC);
  }

  //set bounce reason default on, bounce report
  if ($formName == 'CRM_Report_Form_Mailing_Bounce') {
    $defaults['fields[bounce_reason]'] = 1;
    $form->setDefaults($defaults);
  }

  //3674 add limit submit js to all submit buttons
  _contact_preventDoubleSubmit($formName, $form);

  //add js popup msg to report pdf button
  if ($form->getVar('_instanceForm') && $pdfVar = $form->getVar('_pdfButtonName')) {
    //if the user has selected more than 6 fields for display, warn and proceed
    $form->removeElement($pdfVar);
    $pdfButton =& $form->getElement($pdfVar);

    $jsold = $pdfButton->_attributes['onclick'];
    $jsnew = "var flag = false; $('form').submit(function(e){
      count = $(':input:checkbox:checked[name*=fields]', this);
      if (count.length > 6 && flag == false) {alert('The number of field columns you have selected may exceed what will display in the generated PDF file. Consider selecting fewer fields.'); flag = true;}
    });";
    $pdfButton->setAttribute("onclick", $jsnew.$jsold);

    //3901 if use selects bar or pie chart, hide pdf and print buttons
    $submitVals = $form->_submitValues;
    $paramsVals = $form->getVar('_params');
    $printVar   = $form->getVar('_printButtonName');
    if ((isset($submitVals['charts']) &&
        ($submitVals['charts'] == 'pieChart' || $submitVals['charts'] == 'barChart')) ||
      (isset($paramsVals['charts']) &&
        ($paramsVals['charts'] == 'pieChart' || $paramsVals['charts'] == 'barChart'))) {
      $form->removeElement($pdfVar);
      $form->removeElement($printVar);
      $form->removeElement($printVar); //intentionally duplicated
    }
  }

  //2539 require content in activity email
  if ($formName == 'CRM_Contact_Form_Task_Email') {
    if (empty($form->_submitValues['html_message']) && empty($form->_submitValues['text_message'])) {
      $form->addRule('html_message', ts('Please enter content in either the html or text message fields.'), 'required');
    }
  }

  //4203 disallow setting report nav to root and home parent navigation items
  if ($form->getVar('_instanceForm') && $form->elementExists('parent_id')) {
    $removeList = [
      '-- select --',
      'Home',
      'Administer',
    ];
    $navParent =& $form->getElement('parent_id');
    foreach ($navParent->_options as $key=>$option) {
      if (in_array($option['text'], $removeList)) {
        unset($navParent->_options[$key]);
      }
    }

    //3439 lock permission field
    $permission =& $form->getElement('permission');
    $permission->_values = [0 => 'access CiviReport'];
    $permission->freeze();
  }

  //4339
  if ($formName == 'CRM_Export_Form_Select' && !$form->getVar('_title')) {
    CRM_Utils_System::setTitle(ts('Export All or Selected Fields'));
  }

  //4808 remove various CiviCRM references in page title, etc.
  if (drupal_get_title() == 'CiviCRM') {
    CRM_Utils_System::setTitle(ts('Bluebird'));
  }

  //6655 add to group perms
  if ($formName == 'CRM_Contact_Form_Task_AddToGroup') {
    if (!CRM_Core_Permission::check('edit groups')) {
      $go =& $form->getElement('group_option');
      unset($go->_elements[1]);

      $form->removeElement('title');
    }
  }

  //5444
  if ($form->getVar('_instanceForm')) {
    $instVals = $form->getVar('_instanceValues');
    if ($instVals['report_id'] == 'logging/contact/detail') {
      $instAttrib = $form->getVar('_attributes');
      $instPath = trim(CRM_Utils_Array::value('action', $instAttrib), '/');
      $printerFriendly = CRM_Utils_System::makeURL('snippet', true, false, $instPath).'2';
      $form->assign('printerFriendly', $printerFriendly);
    }
  }

  //4999
  if ($formName == 'CRM_Contact_Form_Task_PickProfile') {
    foreach ($form->_elements[2]->_options as $k => $opt) {
      if ($k != 0 && strpos($opt['text'], 'Batch') === false) {
        unset($form->_elements[2]->_options[$k]);
      }
    }
  }

  if ($formName == 'CRM_Case_Form_ActivityView') {
    CRM_Core_Resources::singleton()->addScript("
      cj('tr.crm-case-activity-view-Client td.label').text('Constituent');
    ");
  }

  if ($formName == 'CRM_Group_Form_Search') {
    Civi::resources()->addScriptFile(E::LONG_NAME, 'js/groupsSearch.js');
  }

  if ($formName == 'CRM_Group_Form_Edit') {
    Civi::resources()->addScriptFile(E::LONG_NAME, 'js/groupsEdit.js');
  }

  if ($formName == 'CRM_Contact_Form_Search_Builder') {
    Civi::resources()->addScriptFile(E::LONG_NAME, 'js/searchBuilder.js');
  }
}

function contact_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  //CRM_Core_Error::debug_var('$formName', $formName);
  //CRM_Core_Error::debug_var('fields', $fields);

  //4272 ensure that target/with contact is set when activity created from contact record
  if ($formName == 'CRM_Activity_Form_Activity' &&
    $form->_context == 'activity' && $form->_action != 8) { //exclude delete
    if (empty($fields['target_contact_id'])) {
      $errors['target_contact_id'] = ts('Please add at least one target contact.');
    }
  }

  if ($formName == 'CRM_Contact_Form_Search_Advanced') {
    $distFlds = [46, 47, 48, 49, 50, 51, 53, 54, 55];
    foreach ($distFlds as $fld) {
      $form->setElementError("custom_$fld", null);
    }
  }

  if ($formName == 'CRM_Note_Form_Note' &&
    $form->_action != CRM_Core_Action::DELETE
  ) {
    $noteVal = trim($fields['note']);
    if (empty($noteVal)) {
      $errors['note'] = ts('Please enter text in the note field.');
    }

    $subjVal = trim($fields['subject']);
    if (empty($subjVal)) {
      $errors['subject'] = ts('Please enter text in the subject field.');
    }
  }

  if ($formName == 'CRM_Contact_Form_GroupContact') {
    if (empty($fields['group_id'])) {
      //5426 temp fix
      $cid = $form->getVar('_contactId');
      $urlString = 'civicrm/contact/view';
      $urlParams = "action=browse&reset=1&cid={$cid}&selectedChild=group";
      $urlBounce = CRM_Utils_System::url($urlString,$urlParams);

      $errors['group_id'] = 'You must select a group.';
      CRM_Core_Error::statusBounce('You must select a group.', $urlBounce);
    }
  }

  //5725/7653
  if ($formName == 'CRM_Profile_Form_Edit') {
    $contactProfiles = CRM_Core_BAO_UFGroup::getProfiles(['Individual']);

    switch ($contactProfiles[$fields['gid']]) {
      case 'New Individual':
        $fName = (isset($fields['first_name'])) ? trim($fields['first_name']) : null;
        $lName = (isset($fields['last_name'])) ? trim($fields['last_name']) : null;
        $email = (isset($fields['email-Primary'])) ? trim($fields['email-Primary']) : null;
        if (empty($fName) && empty($lName) && empty($email)) {
          $errors['first_name'] = 'You must enter a first name, last name, or email address, in order to create a contact.';
        }
        break;

      default:
    }
  }

  //CRM_Core_Error::debug_var('$errors', $errors);
}

function contact_civicrm_postProcess($formName, &$form) {
  /*Civi::log()->debug('', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  if ($formName == 'CRM_Contact_Form_Inline_Demographics') {
    $vals = $form->_submitValues;

    _contact_setCustomData('CRM_Contact_Form_Inline_Demographics', $vals);

    //6803 set comm pref when deceased
    if (isset($vals['is_deceased']) && $vals['is_deceased']) {
      civicrm_api3('contact', 'create', [
        'id' => $vals['cid'],
        'do_not_email' => 1,
        'do_not_phone' => 1,
        'do_not_mail' => 1,
        'do_not_sms' => 1,
        'do_not_trade' => 1,
        'is_opt_out' => 1,
        'preferred_communication_method' => '',
      ]);

      $params = [
        'contact_id_a' => $vals['cid'],
        'is_active' => 1,
      ];
      $rels = civicrm_api3('relationship', 'get', $params);

      foreach ($rels['values'] as $rel) {
        if (($rel['relationship_type_id'] == 6 ||
            $rel['relationship_type_id'] == 7) &&
          (!isset($rel['start_date']) ||
            strtotime($rel['start_date']) < strtotime(date('Y-m-d'))) &&
          (!isset($rel['end_date']) ||
            strtotime($rel['end_date']) > strtotime(date('Y-m-d')) ) ) {

          $msg = "<li>You have marked this contact as deceased and it has a household relationship. Be sure to review the household record and update the postal greeting, email greeting, and addressee values as appropriate.</li>";
        }
      }

      //now register the status message. handle here so we can combine in a nice bullet list
      if ($msg) {
        CRM_Core_Session::setStatus("<ul>$msg</ul>", 'Please note', 'alert', array('expires' => 0));
      }
    }
  }

  if (in_array($formName, [
    'CRM_Contact_Form_Inline_CommunicationPreferences',
    'CRM_Contact_Form_Inline_ContactInfo'
  ])) {
    _contact_setCustomData($formName, $form->_submitValues);
  }

  if ($formName == 'CRM_Contact_Form_Contact') {
    //CRM_Core_Error::debug_var('form',$form);
    if ($form->_contactType == 'Individual') {
      $values = $form->_finalValues;
      $contactID = $form->_contactId;
      $msg = '';

      //CRM_Core_Error::debug_var('values',$values);

      //determine if the contact has a household relationship
      $params = [
        'contact_id_a' => $contactID,
        'is_active' => 1,
      ];
      $rels = civicrm_api3('relationship', 'get', $params);

      //if there are no household relationships, we don't need to continue
      if (!$rels['count']) {
        return;
      }
      //CRM_Core_Error::debug_var('rels',$rels);

      //check if all indivs related to the household are marked either do not email or do not mail
      //if so, mark the household with those values and return a message
      if ($values['privacy']['do_not_email'] || $values['privacy']['do_not_mail']) {
        foreach ($rels['values'] as $rel) {
          if (($rel['relationship_type_id'] == 6 ||
              $rel['relationship_type_id'] == 7) &&
            (!isset($rel['start_date']) ||
              strtotime($rel['start_date']) < strtotime(date('Y-m-d'))) &&
            (!isset($rel['end_date']) ||
              strtotime($rel['end_date']) > strtotime(date('Y-m-d')))) {
            //now determine if there are other contacts attached to this household
            $houseID = $rel['contact_id_b'];

            $params = [
              'contact_id_b' => $houseID,
              'is_active' => 1,
            ];
            $hRels = civicrm_api3('relationship', 'get', $params);

            if (!$rels['count']) {
              continue;
            }

            $flagDNM = $flagDNE = true; //assume true and break when a contact is not flagged
            foreach ($hRels['values'] as $hRel) {
              //CRM_Core_Error::debug_var('hRel',$hRel);
              if (($hRel['relationship_type_id'] == 6 ||
                  $hRel['relationship_type_id'] == 7) &&
                (!isset($hRel['start_date']) ||
                  strtotime($hRel['start_date']) < strtotime(date('Y-m-d'))) &&
                (!isset($hRel['end_date']) ||
                  strtotime($hRel['end_date']) > strtotime(date('Y-m-d')))) {

                //check if related indiv has DNM or DNE
                $params = [
                  'id' => $hRel['contact_id_a'],
                ];
                $iContact = civicrm_api3('contact', 'getsingle', $params);
                //CRM_Core_Error::debug_var('iContact',$iContact);
                if (!$iContact['do_not_mail']) {
                  $flagDNM = false;
                }
                if (!$iContact['do_not_email']) {
                  $flagDNE = false;
                }
              }
            }

            //now process the household
            if ($flagDNM) {
              $params = [
                'id' => $houseID,
                'do_not_mail' => 1,
              ];
              $dnm = civicrm_api3('Contact', 'Update', $params);
              $msg .= '<li>This individual has a related household record which has been marked Do Not Postal Mail. </li>';
            }
            if ($flagDNE) {
              $params = [
                'id' => $houseID,
                'do_not_email' => 1,
              ];
              $dne = civicrm_api3('Contact', 'Update', $params);
              $msg .= '<li>This individual has a related household record which has been marked Do Not Email. </li>';
            }
          }
        }
      }

      //4744 if marked deceased and a household relationship exists, return message
      $isDeceased = civicrm_api3('Contact', 'Getvalue', ['id' => $contactID, 'return' => 'is_deceased']);
      if ($isDeceased) {
        foreach ($rels['values'] as $rel) {
          if (($rel['relationship_type_id'] == 6 ||
              $rel['relationship_type_id'] == 7) &&
            (!isset($rel['start_date']) ||
              strtotime($rel['start_date']) < strtotime(date('Y-m-d'))) &&
            (!isset($rel['end_date']) ||
              strtotime($rel['end_date']) > strtotime(date('Y-m-d')) ) ) {

            $msg .= "<li>You have marked this contact as deceased and it has a household relationship. Be sure to review the household record and update the postal greeting, email greeting, and addressee values as appropriate.</li>";
          }
        }
      }

      //now register the status message. handle here so we can combine in a nice bullet list
      if ($msg) {
        CRM_Core_Session::setStatus("<ul>$msg</ul>", 'Please note', 'alert', ['expires' => 0]);
      }
    }
  }
}

function contact_civicrm_alterEntityRefParams(&$params, $formName) {
  /*Civi::log()->debug(__FUNCTION__, [
    'params' => $params,
    'formName' => $formName,
  ]);*/

  if ($formName == 'CRM_Contactlayout_Form_Inline_ProfileBlock' && $params['entity'] == 'Contact') {
    $params['multiple'] = FALSE;
    $params['select']['multiple'] = FALSE;
  }
}

function contact_civicrm_tabset($tabsetName, &$tabs, $context) {
  //CRM_Core_Error::debug_var('$tabset', $tabsetName);
  //CRM_Core_Error::debug_var('$tabs', $tabs);
  //CRM_Core_Error::debug_var('$context', $context);

  if ($tabsetName == 'civicrm/contact/view') {
    //create new tab for website integration; only attach to individuals
    try {
      $ctype = civicrm_api3('contact', 'getvalue', [
        'id' => $context['contact_id'],
        'return' => 'contact_type'
      ]);

      if ($ctype == 'Individual') {
        $url_tags = CRM_Utils_System::url('civicrm/nyss/web/tags',
          "reset=1&snippet=1&force=1&cid={$context['contact_id']}");
        $tabs[] = [
          'id' => 'nyss_web_tags',
          'url' => $url_tags,
          'title' => 'Website Tags',
          'weight' => 500
        ];

        $url_activity = CRM_Utils_System::url('civicrm/nyss/web/activitystream',
          "reset=1&snippet=1&force=1&cid={$context['contact_id']}");
        $tabs[] = [
          'id' => 'nyss_web_activitystream',
          'url' => $url_activity,
          'title' => 'Website Activity',
          'weight' => 500
        ];
      }

      // NYSS #17217
      _contact_replace_contact_tab_relationships($tabs);

      CRM_Core_Error::debug_var('nyss rel', $tabs['n_y_s_s_contact_summary_relationships']);
    }
    catch (CiviCRM_API3_Exception $e) {}
  }
}

// NYSS #17217
#[CRM_NYSS_Attribute_IssueRef(17217, 'https://dev.nysenate.gov/issues/17217')]
function _contact_replace_contact_tab_relationships (&$tabs) {
  unset($tabs['rel']);
  $tabs['n_y_s_s_contact_tab_relationships']['weight'] = 5;
}

function contact_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  //CRM_Core_Error::debug_var('alterTemplateFile $formName', $formName);
  //CRM_Core_Error::debug_var('alterTemplateFile $context', $context);
  //CRM_Core_Error::debug_var('alterTemplateFile $tplName', $tplName);
  //CRM_Core_Error::debug_var('alterTemplateFile $form', $form);

  //website profile tab
  if ($formName == 'CRM_Contact_Page_View_CustomData' &&
    $form->_groupId == 9
  ) {
    $tplName = 'CRM/NYSS/ContactWebsiteProfileTab.tpl';
  }
}

function contact_civicrm_searchTasks($objectType, &$tasks) {
  //CRM_Core_Error::debug_var('$objectType', $objectType);
  //CRM_Core_Error::debug_var('$tasks', $tasks);

  if ($objectType == 'contact') {
    unset($tasks[19]); //remove pdf letter creation task, #2284
    //unset($tasks[20]); //remove mass email task, #5498
    unset($tasks[7]); //remove send SMS task, #5665
  }

  //7891
  if ($objectType == 'activity') {
    $tasks[100] = [
      'title' => 'Update Activity Status',
      'class' => 'CRM_Activity_Form_Task_UpdateStatus',
      'result' => '',
    ];
  }
}

#[CRM_NYSS_Attribute_IssueRef(17730)]
function contact_civicrm_alterReportVar($varType, &$var, $object) {
  if ($varType === 'columns' && $object instanceof CRM_Report_Form_Contact_Detail) {
    $var['civicrm_contact']['fields']['nick_name'] = [
      'title' => ts('Nick Name'),
    ];
  }
}

#[CRM_NYSS_Attribute_IssueRef(17730)]
function contact_civicrm_searchColumns($objectName, &$headers, &$rows, &$selector) {
  /*Civi::log()->debug('', [
    '$objectName' => $objectName,
    '$headers' => $headers,
    //'$rows' => $rows,
    '$selector' => $selector,
  ]);*/

  //12644 remove sort cols on search builder
  if (_contact_accessProtected(_contact_accessProtected($selector, '_object'), '_searchContext') == 'builder') {
    foreach ($headers as &$header) {
      if (!empty($header['sort']) && $header['sort'] != 'sort_name') {
        unset($header['sort']);
      }
    }
  }

  // NYSS #17730 - show nickname on advanced search (this is dependent on a customization in CRM_Contact_Selector)
  if (get_class($selector) === 'CRM_Contact_Selector_Controller' && sizeof($rows ?? []) && array_key_exists('nick_name',reset($rows))) {
      foreach ($rows as &$row) {
          if (!empty($row['nick_name'])) {
              CRM_NYSS_Contact_Utils::showNickName($row['sort_name'], $row['nick_name'] ?? '');
          }
      }
  }
}

function contact_civicrm_pre($op, $objectName, $objectId, &$objectRef) {
  //CRM_Core_Error::debug_var('op',$op);
  //CRM_Core_Error::debug_var('objectName',$objectName);
  //CRM_Core_Error::debug_var('objectId',$objectId);
  //CRM_Core_Error::debug_var('objectRef',$objectRef);
  //CRM_Core_Error::backtrace('backTrace', true);

  //NYSS #2729 strip line break
  if (in_array($op, ['edit','create'], true) &&
    in_array($objectName, ['Individual','Organization','Household'], true) &&
    !empty($objectRef['email'])
  ) {
    foreach ($objectRef['email'] as $key => $block) {
      if ($block['signature_html'] == '<br />') {
        $objectRef['email'][$key]['signature_html'] = null;
      }
    }
  }

  //4627 allow blank email overrides
  if ($op == 'edit' && $objectName == 'Profile'
    && isset($objectRef['uf_group_id']) && $objectRef['uf_group_id'] == 8) {
    $objectRef['updateBlankLocInfo'] = true;
  }

  //only create a new address if we have key fields
  //8005 - modified CRM_Core_BAO_Address::dataExists so the below is not really necessary.
  //leaving in place as it would be better if we could handle here...
  if ($op == 'create' && $objectName == 'Address') {
    $skipFields = ['location_type_id', 'is_primary', 'state_province_id', 'country_id', 'contact_id'];
    $skipRecord = true;
    foreach ($objectRef as $f => $v) {
      if (!in_array($f, $skipFields) && !empty($v) && $v != 'null') {
        $skipRecord = false;
      }
    }
    if ($skipRecord) {
      foreach ($objectRef as $f => $v) {
        $objectRef[$f] = '';
      }
    }
  }

  //7499/7639 trim space before save
  if (($op == 'edit' || $op == 'create') && $objectName != 'UFMatch') {
    foreach ($objectRef as  $f => $v) {
      if (is_string($v)) {
        if (is_array($objectRef)) {
          $objectRef[$f] = trim($v, ' ');
        }
        elseif (is_object($objectRef)) {
          $objectRef->$f = trim($v, ' ');
        }
      }
    }
    //CRM_Core_Error::debug_var('objectRef after',$objectRef);
  }
}

function contact_civicrm_check(&$messages) {
  //Civi::log()->debug('check', array('messages' => $messages));

  //disable system status messages
  foreach ($messages as $k => $msg) {
    unset($messages[$k]);
  }
}

//10887
function contact_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  if (!CRM_Core_Config::singleton()->debug) {
    $content = _contact_stripSpaces($content);
  }

  /*Civi::log()->debug('nyss_civihooks_civicrm_alterContent', array(
    '$content' => $content,
    //'$context' => $context,
    //'$tplName' => $tplName,
    //'$object' => $object,
  ));*/
}

function _contact_setCustomData($formName, $vals) {
  //CRM_Core_Error::debug_var('$vals',$vals);

  //construct map of form and custom field ids
  $formFlds = [
    'CRM_Contact_Form_Inline_Demographics' => [
      'custom_63' => 'text',
      'custom_45' => 'text',
      'custom_58' => 'multi',
      'custom_62' => 'text',
    ],
    'CRM_Contact_Form_Inline_CommunicationPreferences' => [
      'custom_64' => 'text',
    ],
    'CRM_Contact_Form_Inline_ContactInfo' => [
      'custom_60' => 'select',
      'custom_42' => 'select',
    ],
  ];

  foreach ($formFlds[$formName] as $fld => $type) {
    //CRM_Core_Error::debug_var("vals[{$fld}]", $vals[$fld]);

    //if a multi field and not set, set to empty array
    if ($type == 'multi' && !isset($vals[$fld])) {
      $vals[$fld] = [];
    }

    //set custom data
    if (isset($vals[$fld])) {
      civicrm_api3('contact', 'create', [
        'id' => $vals['cid'],
        $fld => $vals[$fld],
      ]);
    }
  }
}

function _contact_stripSpaces($text) {
  return trim(preg_replace('~>\s*\n\s*<~', '><', $text));
}

//4808/2960 word replacement for titles
function _contact_fixTitles() {
  $currentTitle = drupal_get_title();
  //CRM_Core_Error::debug_var('currentTitle', $currentTitle);

  $stringReplacement = [
    'CiviCRM' => 'Bluebird',
    'CiviMail' => 'BluebirdMail',
    'CiviCase' => 'Case',
    'CiviReport' => 'BluebirdReport',
  ];

  foreach ($stringReplacement as $search => $replace) {
    if (strpos($currentTitle, $search) !== false) {
      CRM_Utils_System::setTitle(str_replace($search, $replace, $currentTitle));
    }
  }
}

/**
 * @param $formName
 * @param $form
 *
 * #3674 - prevent users from submitting forms multiple times
 * see also themes/Bluebird/scripts/civi-header.js
 */
function _contact_preventDoubleSubmit($formName, &$form) {
  if (isset($form->_elementIndex['buttons']) &&
    strpos($formName, 'Inline') === false
  ) {
    $buttons =& $form->getElement('buttons');

    //list of exclusions by name or value
    $btnExcludeName = [
      '_qf_Select_next',
      '_qf_Map_next',
      '_qf_PDF_submit',
      '_qf_PDF_cancel',
      '_qf_Contact_upload_view', //handle separately
      '_qf_Contact_upload_new', //handle separately
      '_qf_Contact_upload_cancel', //handle separately
      '_qf_Email_upload', //7393
      '_qf_Relationship_upload', //4414
      '_qf_Search_refresh',
      '_qf_ProofingReport_next',
      '_qf_ProofingReport_submit',
      '_qf_Label_cancel',
      '_qf_Label_submit',
      '_qf_ExportPermissions_next',
      '_qf_ExportPermissions_submit',
      '_qf_ExportPermissions_cancel',
      '_qf_Website_upload', //7800
      '_qf_Demographics_upload', //7800
      '_qf_CustomData_upload', //7800
      '_qf_ProofingReport_next', //11526
      '_qf_ProofingReport_submit', //11526
    ];
    $btnExcludeValue = [
      'PDF',
      'Print Contact List',
      'File on case'
    ];
    $btnHandleValid = [
      '_qf_Contact_upload_view',
      '_qf_Contact_upload_new',
      '_qf_Contact_upload_cancel',
    ];

    foreach ($buttons->_elements as $key=>$button) {
      $btnType = $buttons->_elements[$key]->_attributes['type'];
      if ($btnType == 'submit') {
        $btnName = $buttons->_elements[$key]->_attributes['name'];
        $btnValue = $buttons->_elements[$key]->_attributes['value'];
        if (!in_array($btnName, $btnExcludeName) &&
          !in_array($btnValue, $btnExcludeValue)) {
          $js = "if(this.value!='Processing...'){this._nyss_oldvalue=this.value;this.value='Processing...';cj('#' + this.id).click();this.value=this._nyss_oldvalue;return true;}else{return false;}";
          $buttons->_elements[$key]->_attributes['onclick'] = $js;
        }
        elseif (in_array($btnName, $btnHandleValid)) {
          $jsValid = $jsCancel = '';
          if (strpos($btnName, '_cancel') === false) {
            $jsValid = '&& cj("#Contact").valid()';
          }
          $js = 'if(this.value!="Processing..." '.$jsValid.' ){this._nyss_oldvalue=this.value;this.value="Processing...";cj("#" + this.id).click();this.value=this._nyss_oldvalue;return true;}else{return false;}';
          $buttons->_elements[$key]->_attributes['onclick'] = $js;
        }
      }
    }
    //CRM_Core_Error::debug($buttons);
  }
}

/**
 * @param $obj
 * @param $prop
 *
 * @return mixed
 * @throws \ReflectionException
 *
 * 12644
 * helper function to access protected object properties
 */
function _contact_accessProtected($obj, $prop) {
  if (!empty($obj)) {
    $reflection = new ReflectionClass($obj);
    if ($reflection->hasProperty($prop)) {
      $property = $reflection->getProperty($prop);
      $property->setAccessible(TRUE);
      return $property->getValue($obj);
    }
  }

  return NULL;
}
