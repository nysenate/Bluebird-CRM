<?php

require_once 'recentmenu.civix.php';
use CRM_Recentmenu_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function recentmenu_civicrm_config(&$config) {
  _recentmenu_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function recentmenu_civicrm_xmlMenu(&$files) {
  _recentmenu_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function recentmenu_civicrm_install() {
  _recentmenu_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function recentmenu_civicrm_postInstall() {
  _recentmenu_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function recentmenu_civicrm_uninstall() {
  _recentmenu_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function recentmenu_civicrm_enable() {
  _recentmenu_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function recentmenu_civicrm_disable() {
  _recentmenu_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function recentmenu_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _recentmenu_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function recentmenu_civicrm_managed(&$entities) {
  _recentmenu_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function recentmenu_civicrm_caseTypes(&$caseTypes) {
  _recentmenu_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function recentmenu_civicrm_angularModules(&$angularModules) {
  _recentmenu_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function recentmenu_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _recentmenu_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function recentmenu_civicrm_entityTypes(&$entityTypes) {
  _recentmenu_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_pageRun().
 */
function recentmenu_civicrm_pageRun(&$page) {
  if (!empty($_REQUEST['snippet']) && in_array($_REQUEST['snippet'], ['json', 6])) {
    $page->ajaxResponse['recentmenu_items'] = _get_recentmenu_items();
  }
}

/**
 * Implements hook_civicrm_preProcess().
 */
function recentmenu_civicrm_preProcess($formName, &$form) {
  if (!empty($_REQUEST['snippet']) && in_array($_REQUEST['snippet'], ['json', 6])) {
    $form->ajaxResponse['recentmenu_items'] = _get_recentmenu_items();
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function recentmenu_civicrm_postProcess($formName, &$form) {
  if (!empty($_REQUEST['snippet']) && in_array($_REQUEST['snippet'], ['json', 6])) {
    $form->ajaxResponse['recentmenu_items'] = _get_recentmenu_items();
  }
}

/**
 * Implements hook_civicrm_coreResourceList().
 */
function recentmenu_civicrm_coreResourceList(&$list, $region) {
  if ($region == 'html-header' && CRM_Core_Permission::check('access CiviCRM')) {
    Civi::resources()
      ->addScriptFile('org.civicrm.recentmenu', 'js/recentmenu.js', 0, 'html-header')
      ->addVars('recentmenu', _get_recentmenu_items());
  }
}

function _get_recentmenu_items() {
  $icons = [
    'Individual' => 'fa-user',
    'Household' => 'fa-home',
    'Organization' => 'fa-building',
    'Activity' => 'fa-tasks',
    'Case' => 'fa-folder-open',
    'Contribution' => 'fa-credit-card',
    'Grant' => 'fa-money',
    'Group' => 'fa-users',
    'Membership' => 'fa-id-badge',
    'Note' => 'fa-sticky-note',
    'Participant' => 'fa-ticket',
    'Pledge' => 'fa-paper-plane',
    'Relationship' => 'fa-handshake-o',
  ];
  $recent = CRM_Utils_Recent::get();
  $menu = [
    'label' => E::ts('Recent (%1)', [1 => count($recent)]),
    'name' => 'recent_items',
    'icon' => 'crm-i fa-history',
    'child' => [],
  ];
  foreach ($recent as $i => $item) {
    $node = [
      'label' => $item['title'],
      'url' => $item['url'],
      'name' => 'recent_items_' . $i,
      'attr' => ['title' => $item['type']],
      'icon' => 'crm-i fa-fw ' . CRM_Utils_Array::value($item['type'], $icons, 'fa-gear'),
      'child' => [[
        'label' => E::ts('View'),
        'attr' => ['title' => E::ts('View %1', [1 => $item['type']])],
        'url' => $item['url'],
        'name' => 'recent_items_' . $i . '_view',
      ]]
    ];
    if (!empty($item['edit_url'])) {
      $node['child'][] = [
        'label' => E::ts('Edit'),
        'attr' => ['title' => E::ts('Edit %1', [1 => $item['type']])],
        'url' => $item['edit_url'],
        'name' => 'recent_items_' . $i . '_edit',
      ];
    }
    if (!empty($item['delete_url'])) {
      $node['child'][] = [
        'label' => E::ts('Delete'),
        'attr' => ['title' => E::ts('Delete %1', [1 => $item['type']])],
        'url' => $item['delete_url'],
        'name' => 'recent_items_' . $i . '_delete',
      ];
    }
    $menu['child'][] = $node;
  }
  return $menu;
}
