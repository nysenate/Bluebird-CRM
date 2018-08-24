<?php

require_once 'recentitems.civix.php';
use CRM_Recentitems_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function recentitems_civicrm_config(&$config) {
  _recentitems_civix_civicrm_config($config);

  //inject recent items js
  CRM_Core_Resources::singleton()->addScriptFile('gov.nysenate.recentitems', 'js/recentitems.js');
  CRM_Core_Resources::singleton()->addStyleFile('gov.nysenate.recentitems', 'css/recentitems.css');

  //build list and store as js var
  $recentItemsList = _recentitems_buildList();
  CRM_Core_Resources::singleton()->addVars('NYSS', array('recentItemsList' => $recentItemsList));
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function recentitems_civicrm_xmlMenu(&$files) {
  _recentitems_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function recentitems_civicrm_install() {
  _recentitems_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function recentitems_civicrm_postInstall() {
  _recentitems_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function recentitems_civicrm_uninstall() {
  _recentitems_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function recentitems_civicrm_enable() {
  _recentitems_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function recentitems_civicrm_disable() {
  _recentitems_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function recentitems_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _recentitems_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function recentitems_civicrm_managed(&$entities) {
  _recentitems_civix_civicrm_managed($entities);
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
function recentitems_civicrm_caseTypes(&$caseTypes) {
  _recentitems_civix_civicrm_caseTypes($caseTypes);
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
function recentitems_civicrm_angularModules(&$angularModules) {
  _recentitems_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function recentitems_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _recentitems_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function recentitems_civicrm_entityTypes(&$entityTypes) {
  _recentitems_civix_civicrm_entityTypes($entityTypes);
}

function recentitems_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  /*Civi::log()->debug('recentitems_civicrm_alterContent', array(
    '$content' => $content,
    '$context' => $context,
  ));*/


}

function _recentitems_buildList() {
  $icons = [
    'Individual' => 'fa-user',
    'Organization' => 'fa-building',
    'Household' => 'fa-home',
    'Relationship' => 'fa-user-circle-o',
  ];
  $recent = CRM_Utils_Recent::get();
  //Civi::log()->debug('', array('recent' => $recent));

  $html = '
    <div id="nyss-recentitems" title="Recent Items">
      <i class="nyss-i fa-th-list"></i>
      <ul id="nyss-recentitems-list" style="display: none;">
  ';

  foreach ($recent as $item) {
    $editUrl = (!empty($item['edit_url'])) ?
      " (<a href='{$item['edit_url']}'><span class='nyss-recentitems-edit'>edit</span></a>)" : '';
    $html .= "
      <li><a href='{$item['url']}'>{$item['title']}</a>{$editUrl}</li>
    ";
  }

  $html .= '</ul></div>';

  return $html;
}
