<?php

require_once 'contactsummary.civix.php';
use CRM_Contactsummary_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contactsummary_civicrm_config(&$config) {
  _contactsummary_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function contactsummary_civicrm_xmlMenu(&$files) {
  _contactsummary_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contactsummary_civicrm_install() {
  _contactsummary_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function contactsummary_civicrm_postInstall() {
  _contactsummary_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contactsummary_civicrm_uninstall() {
  _contactsummary_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contactsummary_civicrm_enable() {
  _contactsummary_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function contactsummary_civicrm_disable() {
  _contactsummary_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function contactsummary_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contactsummary_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function contactsummary_civicrm_managed(&$entities) {
  _contactsummary_civix_civicrm_managed($entities);
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
function contactsummary_civicrm_angularModules(&$angularModules) {
  _contactsummary_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function contactsummary_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _contactsummary_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function contactsummary_civicrm_entityTypes(&$entityTypes) {
  _contactsummary_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_pageRun().
 *
 * Add layout block data to the contact summary screen.
 */
function contactsummary_civicrm_pageRun(&$page) {
  if (get_class($page) === 'CRM_Contact_Page_View_Summary') {
    $contactID = $page->getVar('_contactId');
    if ($contactID) {
      $layout = CRM_Contactsummary_BAO_ContactSummary::getLayout($contactID);
      if ($layout) {
        $profileBlocks = [];
        foreach ($layout as $column) {
          foreach ($column as $block) {
            if (!empty($block['profile_id'])) {
              $profileBlocks[$block['profile_id']] = CRM_Contactsummary_Page_Inline_ProfileBlock::getProfileBlock($block['profile_id'], $contactID);
            }
          }
        }
        $page->assign('layoutBlocks', $layout);
        $page->assign('profileBlocks', $profileBlocks);
        // Setting these variables will make Summary.tpl replace the contents with SummaryHook.tpl which we override.
        $page->assign('hookContent', 1);
        $page->assign('hookContentPlacement', CRM_Utils_Hook::SUMMARY_REPLACE);
      }
    }
  }
}
