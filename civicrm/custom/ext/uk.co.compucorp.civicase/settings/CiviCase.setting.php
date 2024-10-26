<?php

/**
 * @file
 * Civicase Settings.
 */

use CRM_Civicase_ExtensionUtil as E;

/**
 * @file
 * CiviCase Setting file.
 */

$xmlProcessor = new CRM_Case_XMLProcessor_Process();
$caseSetting = new CRM_Civicase_Helper_CaseSetting($xmlProcessor);

$setting = [
  'civicaseAllowCaseLocks' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseAllowCaseLocks',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => FALSE,
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts('Allow cases to be locked'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('This will allow cases to be locked for certain contacts.'),
    'help_text' => '',
  ],
  'civicaseShowComingSoonCaseSummaryBlock' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseShowComingSoonCaseSummaryBlock',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => TRUE,
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts('Show "Coming Soon" section on Case Summary'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('This configuration controls the visibility of the coming soon section on the case summary screen which has Next Milestone, Next Activity and the Case Calendar.'),
  ],
  'civicaseAllowLinkedCasesTab' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseAllowLinkedCasesTab',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => FALSE,
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts('Allow linked cases tab'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('This will allow linked cases to be viewed on a separate tab.'),
    'help_text' => '',
  ],
  'civicaseShowWebformsListSeparately' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseShowWebformsListSeparately',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => FALSE,
    'html_attributes' => [
      'data-toggles-visibility-for' => 'civicase__settings__webform-button-label',
      'class' => 'civicase__settings__show-webform',
    ],
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts('Show Webforms list in a separate dropdown'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('This will show the webforms list in a separate dropdown.'),
    'help_text' => '',
  ],
  'civicaseWebformsDropdownButtonLabel' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseWebformsDropdownButtonLabel',
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_attributes' => [
      'class' => 'civicase__settings__webform-button-label',
      'size' => 20,
      'maxlength' => 20,
    ],
    'html_type' => 'text',
    'default' => 'Webforms',
    'add' => '4.7',
    'title' => E::ts('Label for the Webforms dropdown button'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Label for the Webforms dropdown button'),
    'help_text' => '',
  ],
  'showFullContactNameOnActivityFeed' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'showFullContactNameOnActivityFeed',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => TRUE,
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts('Show full name on Case activity feed'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('This configuration determines whether or not the full name of the activity creator is displayed in the case activity feed.'),
    'help_text' => '',
  ],
  'includeActivitiesForInvolvedContact' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'includeActivitiesForInvolvedContact',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => FALSE,
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts("Include activities I'm involved in"),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts("Cases I'm involved in filter will include 'activities created by me' and 'activities assigned to me'."),
    'help_text' => '',
  ],
  'civicaseSingleCaseRolePerType' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseSingleCaseRolePerType',
    'type' => 'Boolean',
    'quick_form_type' => 'Element',
    'default' => 0,
    'html_attributes' => [
      'defaultMultipleCaseClient' => (int) $caseSetting->getDefaultValue('AllowMultipleCaseClients'),
    ],
    'html_type' => 'checkbox',
    'add' => '4.7',
    'title' => E::ts('One active case role'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('This setting will only allow one active instance of each case role for all case types.'),
    'help_text' => '',
  ],
  'civicaseLimitRecipientFields' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseLimitRecipientFields',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => FALSE,
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts('Limit recipients field on bulk email'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('This setting will only allow selected contacts to be added to recipient and Cc/Bcc fields in bulk email.'),
    'help_text' => '',
  ],
  'civicaseRestrictCaseEmailContacts' => [
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'civicaseRestrictCaseEmailContacts',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => FALSE,
    'html_type' => 'radio',
    'add' => '4.7',
    'title' => E::ts('Restrict email recipients to contacts involved with the case'),
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => TRUE,
    'html_attributes' => [
      'data-help-text' => E::ts('When this setting is enabled users will only be able to add existing contacts who are either the case clients or people currently involved with the case as recipients of new emails.'),
    ],
  ],
];

$caseSetting = new CRM_Civicase_Service_CaseCategorySetting();
$caseCategoryWebFormSetting = $caseSetting->getForWebform();

return array_merge($setting, $caseCategoryWebFormSetting);
