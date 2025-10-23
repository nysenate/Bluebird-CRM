<?php

require_once 'nonprimaryquicksearch.civix.php';
// phpcs:disable
use CRM_Nonprimaryquicksearch_ExtensionUtil as E;
// phpcs:enable
use Civi\Api4\Utils\CoreUtil;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function nonprimaryquicksearch_civicrm_config(&$config): void {
  _nonprimaryquicksearch_civix_civicrm_config($config);
  if (isset(Civi::$statics[__FUNCTION__])) {
    return;
  }
  Civi::$statics[__FUNCTION__] = 1;

  // Run this early
  Civi::dispatcher()->addListener('civi.search.defaultDisplay', 'addNonPrimaryContactInfo', 50);
}

function addNonPrimaryContactInfo($e) {
  if ($e->display['type'] !== 'autocomplete' || !CoreUtil::isContact($e->savedSearch['api_entity'])) {
    return;
  }
  $e->display['settings'] = [
    'sort' => [
      ['sort_name', 'ASC'],
    ],
    'columns' => [
      [
        'type' => 'field',
        'key' => 'sort_name',
      ],
      [
        'type' => 'field',
        'key' => 'email.email',
      ]
    ],
  ];
}

function nonprimaryquicksearch_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  // Intercept QuickSearch to return all contact info.
  if ($apiRequest['entity'] === 'Contact' && $apiRequest['action'] === 'autocomplete' && $apiRequest->getFieldName() === 'crm-qsearch-input') {
    $wrappers[] = new CRM_Nonprimaryquicksearch_SearchWrapper();
  }
  // Intercept running the SearchDisplay that powers QuickSearch (to search on non-primary fields)
  if ($apiRequest['entity'] === 'SearchDisplay' && $apiRequest['action'] === 'run') {
    $display = $apiRequest->getDisplay();
    if (($display['type:name'] ?? '') === 'crm-search-display-autocomplete' && $display['label'] === 'Contacts' && $display['type'] === 'autocomplete') {
      $wrappers[] = new CRM_Nonprimaryquicksearch_SearchWrapper();
    }
  }
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function nonprimaryquicksearch_civicrm_install(): void {
  _nonprimaryquicksearch_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function nonprimaryquicksearch_civicrm_enable(): void {
  _nonprimaryquicksearch_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function nonprimaryquicksearch_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function nonprimaryquicksearch_civicrm_navigationMenu(&$menu): void {
//  _nonprimaryquicksearch_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _nonprimaryquicksearch_civix_navigationMenu($menu);
//}
