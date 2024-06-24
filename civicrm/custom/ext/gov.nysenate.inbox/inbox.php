<?php

require_once 'inbox.civix.php';

use CRM_NYSS_Inbox_ExtensionUtil as E;

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
    'Bluebird: access inbox polling',
    'Access inbox polling tools.',
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
      CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.inbox', 'js/new_individual.js');
      CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.inbox', 'css/new_individual.css');
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
