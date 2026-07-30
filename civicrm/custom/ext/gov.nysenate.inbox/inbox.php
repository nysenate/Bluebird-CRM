<?php

require_once 'inbox.civix.php';

use CRM_NYSS_Inbox_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function inbox_civicrm_config(&$config): void {
  _inbox_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function inbox_civicrm_install(): void {
  _inbox_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function inbox_civicrm_enable(): void {
  _inbox_civix_civicrm_enable();
}

function inbox_civicrm_permission(&$permissions) {
  $permissions['access inbox polling'] = [
    'label' => 'Bluebird: access inbox polling',
    'description' => 'Access inbox polling tools.',
  ];
}

function inbox_civicrm_entityRefFilters(&$filters) {
  //Civi::log()->debug('entityRefFilters', array('filters' => $filters));

  $filters['contact'][] = [
    'key' => 'street_address',
    'value' => 'Street Address',
    'entity' => 'address',
    'type' => 'text',
  ];

  $filters['contact'][] = [
    'key' => 'city',
    'value' => 'City',
    'entity' => 'address',
    'type' => 'text',
  ];

  $filters['contact'][] = [
    'key' => 'postal_code',
    'value' => 'Postal Code',
    'entity' => 'address',
    'type' => 'text',
  ];

  $filters['contact'][] = [
    'key' => 'birth_date',
    'value' => 'Birth Date',
    'entity' => 'contact',
    'type' => 'date',
  ];

  $filters['contact'][] = [
    'key' => 'phone',
    'value' => 'Phone',
    'entity' => 'phone',
    'type' => 'text',
  ];
}//entityRefFilters

#[CRM_NYSS_Attribute_IssueRefs('17369')]
function inbox_civicrm_buildForm($formName, &$form) {
  /*Civi::log()->debug('inbox_civicrm_buildForm', array(
    'formName' => $formName,
    'form' => $form,
  ));*/

  //inject js/css to new indiv form, but only when triggered from inbox matched list
  if ($formName == 'CRM_Profile_Form_Edit') {
    $ufGroup = $form->getVar('_ufGroup');
    $referer = CRM_Utils_Array::value('HTTP_REFERER', $_SERVER);
    if ($ufGroup['name'] == 'new_individual' &&
      (strpos($referer, 'civicrm/nyss/inbox/matched') !== FALSE ||
       strpos($referer, 'civicrm/nyss/inbox/unmatched') !== FALSE)
    ) {

      // NYSS 18205
      _inbox_rename_duplicate_contact_button($form);

      CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/new_individual.js');
      CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.inbox', 'css/new_individual.css');
    }
  }

  // NYSS #17369
  // Set Activity Medium to Email when opening a new case from the Matched
  // Messages EntityRef field, "File On Case"
  if ($formName === 'CRM_Case_Form_Case') {
    $medium_id = CRM_Utils_Request::retrieve('mediumId', 'Integer', NULL, FALSE, NULL);
    $context = CRM_Utils_Request::retrieve('context', 'String', NULL, FALSE, NULL);
    if ($medium_id && $context === 'inbox_entityref') {
      $form->setDefaults(['medium_id' => $medium_id]);
    }
  }

}

function inbox_civicrm_entityTypes(&$entityTypes) {
  if (empty($entityTypes['OAuthSysToken'])) {
    $entityTypes['OAuthSysToken'] = [
      'name' => 'OAuthSysToken',
      'class' => 'CRM_OAuth_DAO_OAuthSysToken',
      'table' => 'civicrm_oauth_systoken',
    ];

    $entityTypes['OAuthClient'] = [
      'name' => 'OAuthClient',
      'class' => 'CRM_OAuth_DAO_OAuthClient',
      'table' => 'civicrm_oauth_client',
    ];

    $entityTypes['OAuthContactToken'] = [
      'name' => 'OAuthContactToken',
      'class' => 'CRM_OAuth_DAO_OAuthContactToken',
      'table' => 'civicrm_oauth_contact_token',
    ];
  }
}

/**
 * @param $formName
 * @param $form
 * @note NYSS #17369
 * @return void
 * @throws \CRM_Core_Exception
 * @throws \Civi\API\Exception\UnauthorizedException
 */
#[CRM_NYSS_Attribute_IssueRefs('17369')]
function inbox_civicrm_postProcess($formName, $form) {
  // NYSS #17369
  // In this specific case, we are creating a case via an entityRef form field.
  // So, we need to give the entityRef field some extra information to allow
  // it to populate the field with the new case.
  if ($formName === 'CRM_Case_Form_Case' && $form->_context === 'inbox_entityref' && isset($form->_caseId)) {
    $case = \Civi\Api4\CiviCase::get(TRUE)
      ->addSelect('id', 'subject')
      ->addWhere('id', '=', $form->_caseId)
      ->setLimit(1)
      ->execute()->single();

    $name = 'Case #' . $case['id'] . ' - ' . $case['subject'];
    $form->ajaxResponse['extra'] = ['display_name'=> $name, 'sort_name'=> $name];
    $form->ajaxResponse['id'] = $form->_caseId;
  }
}

function inbox_civicrm_container(ContainerBuilder $container) {
  $container->register('inbox.parser', '\CRM_NYSS_Inbox_BAO_MessageParserRegex')->setPublic(TRUE);
}

#[CRM_NYSS_Attribute_IssueRefs('18205')]
function inbox_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
    if ($formName === 'CRM_Profile_Form_Edit') {
        // NYSS 18205
        _inbox_add_duplicate_data_to_response($form, $fields);
        // NYSS 18205
        _inbox_change_duplicate_contact_notification_message($form);
    }
}
/** NYSS 18205
 *  adds dependable and clear 'duplicate contact' data for javascript to use in decision-making */
#[CRM_NYSS_Attribute_IssueRefs('18205')]
function _inbox_add_duplicate_data_to_response(&$form, &$fields) {
    $ufGroup = $form->getVar('_ufGroup');
    if (empty($ufGroup['name']) || $ufGroup['name'] !== 'new_individual') {
        return;
    }
    if (CRM_Core_Smarty::singleton()->getTemplateVars('showSaveDuplicateButton')) {
        // NYSS 18205
        // Really don't like running the dupe check again, but CRM_Contact_Form_Contact::checkDuplicateContacts()
        // is really unhelpful in providing clean duplicate contact data. I chose between 2 evils:
        // 1) parse the HTML error message for contact info, or
        // 2) run the dupe check again.
        // duplicate contact IDs are used to show the duplicate users in the UI.
        // TODO: talk to core team about refactoring checkDuplicateContacts()
        $duplicateIds = CRM_Contact_BAO_Contact::getDuplicateContacts(
            $fields, 'Individual', 'Supervised', [], FALSE, NULL
        );
        $form->ajaxResponse['nyssInboxDuplicateIds'] = $duplicateIds ?: [];
        $form->ajaxResponse['nyssInboxIsDuplicate'] = TRUE;
    }
}

/**
 * @see CRM_Contact_Form_Contact::checkDuplicateContacts()
 * NYSS 18205
 */
#[CRM_NYSS_Attribute_IssueRefs('18205')]
function _inbox_change_duplicate_contact_notification_message(&$form) {
    $ufGroup = $form->getVar('_ufGroup');
    if (empty($ufGroup['name']) || $ufGroup['name'] !== 'new_individual') {
        return;
    }
    if (CRM_Core_Smarty::singleton()->getTemplateVars('showSaveDuplicateButton')) {
        // Replacing message originally set in CRM_Contact_Form_Contact::checkDuplicateContacts()
        $form->_errors['_qf_default'] = ts('Duplicate constituent(s) found.');
    }
}

/** NYSS 18205 */
#[CRM_NYSS_Attribute_IssueRefs('18205')]
function _inbox_rename_duplicate_contact_button(&$form) {
    $buttonName = $form->getButtonName('upload', 'duplicate');
    if ($form->elementExists($buttonName)) {
        $el = $form->getElement($buttonName);
        $el->setContent(ts('Create Duplicate Constituent'));
        $existingClass = $el->getAttribute('class');
        $el->updateAttributes(['class' => trim($existingClass . ' nyss-inbox-duplicate-contact-btn')]);
    }
}
