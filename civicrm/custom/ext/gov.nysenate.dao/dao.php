<?php

require_once 'dao.civix.php';

use Civi\Core\Event\GenericHookEvent;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function dao_civicrm_config(&$config) {
  _dao_civix_civicrm_config($config);

  // Prevent multiple calls
  if (isset(Civi::$statics[__FUNCTION__])) {
    return;
  }
  Civi::$statics[__FUNCTION__] = 1;

  Civi::dispatcher()->addListener('civi.entity.fields', 'dao_civi_entity_fields');
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function dao_civicrm_install() {
  _dao_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function dao_civicrm_enable() {
  _dao_civix_civicrm_enable();
}

/**
 * Implements civi.entity.fields event.
 *
 * Customizes entity schema definitions and metadata across core entities.
 *
 * @param \Civi\Core\Event\GenericHookEvent $event
 */
function dao_civi_entity_fields(GenericHookEvent $event) {
  switch ($event->entity) {
    case 'Contact':
      // 4766
      $event->fields['do_not_trade']['title'] = ts('Undeliverable: Do Not Mail');

      // Set fields that should not be exportable.
      foreach (['contact_sub_type', 'hash', 'image_URL'] as $field) {
        if (isset($event->fields[$field]['usage'])) {
          $event->fields[$field]['usage'] = array_values(array_diff($event->fields[$field]['usage'], ['export']));
        }
      }

      $event->fields['web_user_id'] = [
        'title' => ts('Website User ID'),
        'sql_type' => 'int',
        'input_type' => 'Text',
        'description' => ts('Public site User ID'),
        'readonly' => TRUE,
        'usage' => [
          'import',
          'export',
        ],
        'input_attrs' => [
          'label' => ts('Website User ID'),
        ],
        'entity_reference' => [
          'entity' => 'Contact',
          'key' => 'id',
        ],
      ];
      break;

    case 'Address':
      // Include parsed address fields in import.
      foreach (['street_number', 'street_name', 'street_unit'] as $field) {
        $event->fields[$field]['usage'] ??= [];
        if (!in_array('import', $event->fields[$field]['usage'], TRUE)) {
          $event->fields[$field]['usage'][] = 'import';
        }
      }

      $event->fields['supplemental_address_1']['title'] = ts('Mailing Address');
      $event->fields['supplemental_address_2']['title'] = ts('Building');

      // Set fields that should not be exportable.
      foreach (['geo_code_1', 'geo_code_2', 'name', 'master_id'] as $field) {
        if (isset($event->fields[$field]['usage'])) {
          $event->fields[$field]['usage'] = array_values(array_diff($event->fields[$field]['usage'], ['export']));
        }
      }
      break;

    case 'WorldRegion':
      if (isset($event->fields['name']['usage'])) {
        $event->fields['name']['usage'] = array_values(array_diff($event->fields['name']['usage'], ['export']));
      }
      break;

    case 'Email':
      // 2729
      $event->fields['is_primary']['title'] = ts('Is Email Primary?');

      foreach (['signature_text', 'signature_html'] as $field) {
        if (isset($event->fields[$field]['usage'])) {
          $event->fields[$field]['usage'] = array_values(array_diff($event->fields[$field]['usage'], ['export']));
        }
      }

      $event->fields['mailing_categories'] = [
        'title' => ts('Mailing Categories'),
        'sql_type' => 'varchar(254)',
        'input_type' => 'Text',
        'description' => ts('Comma-separated list of mailing categories to EXCLUDE'),
        'usage' => [],
        'input_attrs' => [
          'label' => ts('Mailing Categories'),
          'size' => 30,
          'maxlength' => 254,
        ],
      ];
      break;

    case 'OpenID':
      // 2719
      if (isset($event->fields['openid']['usage'])) {
        $event->fields['openid']['usage'] = array_values(array_diff($event->fields['openid']['usage'], ['export']));
      }
      break;
  }
}
