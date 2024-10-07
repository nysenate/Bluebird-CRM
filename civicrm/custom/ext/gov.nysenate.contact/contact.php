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
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contact_civicrm_uninstall() {
  _contact_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contact_civicrm_enable() {
  _contact_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function contact_civicrm_disable() {
  _contact_civix_civicrm_disable();
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
function contact_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contact_civix_civicrm_upgrade($op, $queue);
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

    CRM_Core_Resources::singleton()->addVars('NYSS', array('bbContactSummary' => $html));
    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.contact', 'js/ContactSummary.js');

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
  if  ($formName == 'CRM_Note_Form_Note') {
    if ($form->elementExists('note_date')) {
      $ele =& $form->getElement('note_date');
      //Civi::log()->debug(__FUNCTION__, ['ele' => $ele]);

      $existingMaxYear = date('Y', strtotime('-10 year'));
      $newMaxYear = date('Y', strtotime('+10 year'));
      $ele->_attributes['data-crm-datepicker'] = str_replace($existingMaxYear, $newMaxYear, $ele->_attributes['data-crm-datepicker']);
    }
  }
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function contact_civicrm_postInstall() {
  _contact_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function contact_civicrm_entityTypes(&$entityTypes) {
  _contact_civix_civicrm_entityTypes($entityTypes);
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
