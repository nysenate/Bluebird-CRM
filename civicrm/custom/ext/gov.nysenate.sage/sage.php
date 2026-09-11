<?php

require_once 'sage.civix.php';
// phpcs:disable
use CRM_SAGE_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function sage_civicrm_config(&$config) {
  _sage_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function sage_civicrm_install() {
  _sage_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function sage_civicrm_enable() {
  _sage_civix_civicrm_enable();
}

function sage_civicrm_pre($op, $objectName, $id, &$params) {
  //Don't do anything unless we are saving an address
  if ($objectName == 'Address' && in_array($op, ['create', 'edit'])) {
    // If the address already exists, fetch it and compare with form values
    // Unless the address is being modified we never overwrite districts
    $old_addr = CRM_Utils_SAGE::retrieveAddress($id);

    // If the new address is different from the old one, or either of the
    // geocodes from the old address are not populated, or any of the 7
    // required district fields from the old address are not populated,
    // then the address will be sent to SAGE and the results will be saved.
    $addr_changed = !CRM_Utils_SAGE::compareAddressComponents($old_addr, $params);
    $geo_missing = empty($old_addr->geo_code_1) || empty($old_addr->geo_code_2);
    $district_info_missing = !CRM_Utils_Sage::districtInfoPopulated($params);

    if ($addr_changed || $geo_missing || $district_info_missing) {
      CRM_Utils_SAGE::lookup($params, $addr_changed, true);
    }
  }
}
