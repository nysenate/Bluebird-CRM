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
 * Implements hook_civicrm_recent().
 *
 * Flush menu cache when adding recent item.
 */
function recentmenu_civicrm_recent() {
  CRM_Core_BAO_Navigation::resetNavigation(CRM_Core_Session::getLoggedInContactID());
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function recentmenu_civicrm_navigationMenu(&$menu) {
  $icons = [
    'Individual' => 'fa-user',
    'Organization' => 'fa-building',
    'Household' => 'fa-home',
    'Relationship' => 'fa-user-circle-o',
  ];
  $recent = CRM_Utils_Recent::get();
  _recentmenu_civix_insert_navigation_menu($menu, NULL, [
    'label' => E::ts('Recent Items (%1)', [1 => count($recent)]),
    'name' => 'recent_items',
    'class' => 'crm-recent-items-menu',
    'permission' => 'access CiviCRM',
  ]);
  foreach ($recent as $i => $item) {
    $icon = NULL;
    if (!empty($item['type'])) {
      $icon = 'crm-i ' . CRM_Utils_Array::value($item['type'], $icons, 'fa-gear');
    }
    _recentmenu_civix_insert_navigation_menu($menu, 'recent_items', [
      'label' => $item['title'],
      'url' => $item['url'],
      'name' => 'recent_items_' . $i,
      'permission' => 'access CiviCRM',
      'icon' => $icon,
    ]);
    _recentmenu_civix_insert_navigation_menu($menu, 'recent_items/recent_items_' . $i, [
      'label' => E::ts('View'),
      'url' => $item['url'],
      'name' => 'recent_items_' . $i . '_view',
      'permission' => 'access CiviCRM',
    ]);
    if (!empty($item['edit_url'])) {
      _recentmenu_civix_insert_navigation_menu($menu, 'recent_items/recent_items_' . $i, [
        'label' => E::ts('Edit'),
        'url' => $item['edit_url'],
        'name' => 'recent_items_' . $i . '_edit',
        'permission' => 'access CiviCRM',
      ]);
    }
    if (!empty($item['delete_url'])) {
      _recentmenu_civix_insert_navigation_menu($menu, 'recent_items/recent_items_' . $i, [
        'label' => E::ts('Delete'),
        'url' => $item['delete_url'],
        'name' => 'recent_items_' . $i . '_delete',
        'permission' => 'access CiviCRM',
      ]);
    }
  }
  _recentmenu_civix_navigationMenu($menu);
}
