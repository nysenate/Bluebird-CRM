<?php

require_once 'contact.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contact_civicrm_config(&$config) {
  _contact_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function contact_civicrm_xmlMenu(&$files) {
  _contact_civix_civicrm_xmlMenu($files);
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

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function contact_civicrm_managed(&$entities) {
  _contact_civix_civicrm_managed($entities);
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
function contact_civicrm_caseTypes(&$caseTypes) {
  _contact_civix_civicrm_caseTypes($caseTypes);
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
function contact_civicrm_angularModules(&$angularModules) {
_contact_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function contact_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _contact_civix_civicrm_alterSettingsFolders($metaDataFolders);
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

    CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.contact', 'js/InlineAddress.js');
  }
}
