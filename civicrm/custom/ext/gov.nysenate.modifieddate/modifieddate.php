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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function modifieddate_civicrm_install() {
  _modifieddate_civix_civicrm_install();
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
