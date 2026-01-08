<?php

require_once 'deceased.civix.php';
use CRM_Deceased_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function deceased_civicrm_config(&$config) {
  _deceased_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function deceased_civicrm_install() {
  _deceased_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function deceased_civicrm_enable() {
  _deceased_civix_civicrm_enable();
}

function deceased_civicrm_pre($op, $objectName, $id, &$params) {
  if ($objectName === 'Individual' && !empty($params['is_deceased'])) {
    $params['is_opt_out'] = 1;
    $params['do_not_email'] = 1;
    $params['do_not_phone'] = 1;
    $params['do_not_mail'] = 1;
    $params['do_not_sms'] = 1;
    $params['do_not_trade'] = 1;
  }
}
