<?php

require_once 'modifieddate.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function modifieddate_civicrm_config(&$config) {
  _modifieddate_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function modifieddate_civicrm_xmlMenu(&$files) {
  _modifieddate_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function modifieddate_civicrm_install() {
  _modifieddate_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function modifieddate_civicrm_uninstall() {
  _modifieddate_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function modifieddate_civicrm_enable() {
  _modifieddate_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function modifieddate_civicrm_disable() {
  _modifieddate_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function modifieddate_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _modifieddate_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function modifieddate_civicrm_managed(&$entities) {
  _modifieddate_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function modifieddate_civicrm_caseTypes(&$caseTypes) {
  _modifieddate_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function modifieddate_civicrm_angularModules(&$angularModules) {
_modifieddate_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function modifieddate_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _modifieddate_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function modifieddate_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function modifieddate_civicrm_navigationMenu(&$menu) {
  _modifieddate_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'gov.nysenate.modifieddate')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _modifieddate_civix_navigationMenu($menu);
} // */

/**
 * @param $info
 * @param $tableName
 *
 * implement triggerInfo hook to store modified date for additional related tables:
 * - notes
 * - tags
 * - group contacts
 * - activities
 * - relationships
 * - cases
 */
function modifieddate_civicrm_triggerInfo(&$info, $tableName) {
  $info = array_merge($info,
    _modifieddate_note(),
    _modifieddate_tag(),
    _modifieddate_groupcontact(),
    _modifieddate_activity(),
    _modifieddate_relationship(),
    _modifieddate_case()
  );

  /*Civi::log()->debug('modifieddate_civicrm_triggerInfo', array(
    'info' => $info,
    //'_modifieddate_note()' => _modifieddate_note(),
  ));*/
}

function _modifieddate_note() {
  $triggers = array(
    array(
      'table' => 'civicrm_note',
      'when' => 'AFTER',
      'event' => array('INSERT', 'UPDATE'),
      'sql' => "
        UPDATE civicrm_contact 
        SET modified_date = CURRENT_TIMESTAMP 
        WHERE id = NEW.entity_id
          AND NEW.entity_table IN ('civicrm_contact', 'nyss_directmsg', 'nyss_contextmsg');
      ",
    ),
    array(
      'table' => 'civicrm_note',
      'when' => 'AFTER',
      'event' => array('DELETE'),
      'sql' => "
        UPDATE civicrm_contact
        SET modified_date = CURRENT_TIMESTAMP
        WHERE id = OLD.entity_id
          AND OLD.entity_table IN ('civicrm_contact', 'nyss_directmsg', 'nyss_contextmsg');
      ",
    ),
  );

  return $triggers;
}

function _modifieddate_tag() {
  $triggers = array(
    array(
      'table' => 'civicrm_entity_tag',
      'when' => 'AFTER',
      'event' => array('INSERT', 'UPDATE'),
      'sql' => "
        UPDATE civicrm_contact 
        SET modified_date = CURRENT_TIMESTAMP 
        WHERE id = NEW.entity_id
          AND NEW.entity_table IN ('civicrm_contact');
      ",
    ),
    array(
      'table' => 'civicrm_entity_tag',
      'when' => 'AFTER',
      'event' => array('DELETE'),
      'sql' => "
        UPDATE civicrm_contact
        SET modified_date = CURRENT_TIMESTAMP
        WHERE id = OLD.entity_id
          AND OLD.entity_table IN ('civicrm_contact');
      ",
    ),
  );

  return $triggers;
}

function _modifieddate_groupcontact() {
  $triggers = array(
    array(
      'table' => 'civicrm_group_contact',
      'when' => 'AFTER',
      'event' => array('INSERT', 'UPDATE'),
      'sql' => "
        UPDATE civicrm_contact 
        SET modified_date = CURRENT_TIMESTAMP 
        WHERE id = NEW.contact_id;
      ",
    ),
    array(
      'table' => 'civicrm_group_contact',
      'when' => 'AFTER',
      'event' => array('DELETE'),
      'sql' => "
        UPDATE civicrm_contact
        SET modified_date = CURRENT_TIMESTAMP
        WHERE id = OLD.contact_id;
      ",
    ),
  );

  return $triggers;
}

function _modifieddate_activity() {
  $triggers = array(
    array(
      'table' => 'civicrm_activity_contact',
      'when' => 'AFTER',
      'event' => array('INSERT', 'UPDATE'),
      'sql' => "
        UPDATE civicrm_contact 
        SET modified_date = CURRENT_TIMESTAMP 
        WHERE id = NEW.contact_id;
      ",
    ),
    array(
      'table' => 'civicrm_activity_contact',
      'when' => 'AFTER',
      'event' => array('DELETE'),
      'sql' => "
        UPDATE civicrm_contact
        SET modified_date = CURRENT_TIMESTAMP
        WHERE id = OLD.contact_id;
      ",
    ),
  );

  return $triggers;
}

function _modifieddate_relationship() {
  $triggers = array(
    array(
      'table' => 'civicrm_relationship',
      'when' => 'AFTER',
      'event' => array('INSERT', 'UPDATE'),
      'sql' => "
        UPDATE civicrm_contact 
        SET modified_date = CURRENT_TIMESTAMP 
        WHERE id = NEW.contact_id_a
          OR id = NEW.contact_id_b;
      ",
    ),
    array(
      'table' => 'civicrm_relationship',
      'when' => 'AFTER',
      'event' => array('DELETE'),
      'sql' => "
        UPDATE civicrm_contact
        SET modified_date = CURRENT_TIMESTAMP
        WHERE id = OLD.contact_id_a
          OR id = OLD.contact_id_b;
      ",
    ),
  );

  return $triggers;
}

function _modifieddate_case() {
  $triggers = array(
    array(
      'table' => 'civicrm_case_contact',
      'when' => 'AFTER',
      'event' => array('INSERT', 'UPDATE'),
      'sql' => "
        UPDATE civicrm_contact 
        SET modified_date = CURRENT_TIMESTAMP 
        WHERE id = NEW.contact_id;
      ",
    ),
    array(
      'table' => 'civicrm_case_contact',
      'when' => 'AFTER',
      'event' => array('DELETE'),
      'sql' => "
        UPDATE civicrm_contact
        SET modified_date = CURRENT_TIMESTAMP
        WHERE id = OLD.contact_id;
      ",
    ),
  );

  return $triggers;
}
