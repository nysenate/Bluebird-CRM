<?php

/**
 * @file
 * Add a table of notes from related contacts.
 *
 * Copyright (C) 2013-15, AGH Strategies, LLC <info@aghstrategies.com>
 * Licensed under the GNU Affero Public License 3.0 (see LICENSE.txt)
 */

require_once 'changelogretention.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function changelogretention_civicrm_config(&$config) {
  _changelogretention_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function changelogretention_civicrm_xmlMenu(&$files) {
}

/**
 * Implementation of hook_civicrm_install
 */
function changelogretention_civicrm_install() {
  return _changelogretention_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function changelogretention_civicrm_uninstall() {
  return;
}

/**
 * Implementation of hook_civicrm_enable
 */
function changelogretention_civicrm_enable() {
  return _changelogretention_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function changelogretention_civicrm_disable() {
  return;
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function changelogretention_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return;
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function changelogretention_civicrm_managed(&$entities) {
  return;
}
/**
 * Implementation of hook_civicrm_navigationMenu
 */
function changelogretention_civicrm_navigationMenu(&$navMenu) {
  $pages = [
    'settings_page' => [
      'label' => 'Log Retention Settings',
      'name' => 'Log Retention Settings',
      'url' => 'civicrm/admin/logretention',
      'parent' => ['Administer', 'System Settings'],
      'permission' => 'access CiviCRM',
      'operator' => NULL,
      'separator' => NULL,
      'active' => 1,
    ],
  ];
  foreach ($pages as $item) {
    // Check that our item doesn't already exist.
    $menu_item_search = ['url' => $item['url']];
    $menu_items = [];
    CRM_Core_BAO_Navigation::retrieve($menu_item_search, $menu_items);
    if (empty($menu_items)) {
      $path = implode('/', $item['parent']);
      unset($item['parent']);
      _changelogretention_civix_insert_navigation_menu($navMenu, $path, $item);
    }
  }
}
/**
 * Implements hook_civicrm_alterLogTables().
 *
 * @param array $logTableSpec
 */
function changelogretention_civicrm_alterLogTables(&$logTableSpec) {
  $contactReferences = CRM_Dedupe_Merger::cidRefs();
  foreach (array_keys($logTableSpec) as $tableName) {
    $contactIndexes = [];
    $logTableSpec[$tableName]['engine'] = 'INNODB';
    $logTableSpec[$tableName]['engine_config'] = 'ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4';
    $contactRefsForTable = CRM_Utils_Array::value($tableName, $contactReferences, []);
    foreach ($contactRefsForTable as $fieldName) {
      $contactIndexes['index_' . $fieldName] = $fieldName;
    }
    $indexArray = [
      'index_log_conn_id' => 'log_conn_id',
      'index_log_date' => 'log_date',
    ];
    // Check if current table has an "id" column. If so, index it too
    $dsn = DB::parseDSN(CIVICRM_LOGGING_DSN);
    $dbName = $dsn['database'];
    $dao = CRM_Core_DAO::executeQuery("
      SELECT COLUMN_NAME
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = '{$dbName}'
        AND TABLE_NAME = '{$tableName}'
        AND COLUMN_NAME = 'id'
      ");
    if ($dao->fetch()){
      $indexArray['index_id'] = 'id';
    }
    $logTableSpec[$tableName]['indexes'] = array_merge($indexArray, $contactIndexes);
  }
}
