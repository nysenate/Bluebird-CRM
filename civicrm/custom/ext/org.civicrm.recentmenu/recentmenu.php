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
    $recentMenuItems = _get_recentmenu_items();
    if ($recentMenuItems) {
      Civi::resources()
        ->addScriptFile('org.civicrm.recentmenu', 'js/recentmenu.js', 0, 'html-header')
        ->addVars('recentmenu', $recentMenuItems);
    }
  }
}

/**
 * @return array|NULL
 */
function _get_recentmenu_items() {
  // Lookup existing menu item to get the possibly user-defined label and icon
  $navigation = \Civi\Api4\Navigation::get(FALSE)
    ->addWhere('name', '=', 'recent_items')
    ->addSelect('label', 'icon')
    ->addWhere('domain_id', '=', 'current_domain')
    ->execute()->first();
  if (!$navigation) {
    // Maybe the managed navigation entity hasn't been reconciled yet, e.g. mid-upgrade
    return NULL;
  }
  try {
    $recent = \Civi\Api4\RecentItem::get()->execute();
  }
  catch (API_Exception $e) {
    // No logged-in user?
    return NULL;
  }
  $menu = [
    'label' => $navigation['label'] . ' (' . $recent->count() . ')',
    'name' => 'recent_items',
    'icon' => $navigation['icon'],
    'child' => [],
  ];
  $entityTitles = \Civi\Api4\Entity::get(FALSE)
    ->addSelect('name', 'title')
    ->execute()
    ->indexBy('name')->column('title');
  foreach ($recent as $i => $item) {
    $entityTitle = $entityTitles[$item['entity_type']] ?? '';
    $node = [
      'label' => $item['title'],
      'url' => $item['view_url'],
      'name' => 'recent_items_' . $i,
      'attr' => ['title' => E::ts('View %1', [1 => $entityTitle])],
      'icon' => 'crm-i fa-fw ' . ($item['icon'] ?? 'fa-gear'),
      'child' => [
        [
          'label' => E::ts('View %1', [1 => $entityTitle]),
          'url' => $item['view_url'],
          'name' => 'recent_items_' . $i . '_view',
        ],
      ],
    ];
    if (!empty($item['edit_url'])) {
      $node['child'][] = [
        'label' => E::ts('Edit %1', [1 => $entityTitle]),
        'url' => $item['edit_url'],
        'name' => 'recent_items_' . $i . '_edit',
      ];
    }
    $menu['child'][] = $node;
  }
  return $menu;
}
