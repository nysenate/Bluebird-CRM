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

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function sage_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function sage_civicrm_navigationMenu(&$menu) {
//  _sage_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _sage_civix_navigationMenu($menu);
//}

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

    /*Civi::log()->debug(__FUNCTION__, [
      'op' => $op,
      'id' => $id,
      'params' => $params,
      '$addr_changed' => $addr_changed,
      '$geo_missing' => $geo_missing,
      '$district_info_missing' => $district_info_missing,
    ]);*/
  }
}
