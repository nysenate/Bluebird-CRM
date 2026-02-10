<?php

require_once 'changelogproofing.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function changelogproofing_civicrm_config(&$config) {
  _changelogproofing_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function changelogproofing_civicrm_install() {
  _changelogproofing_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function changelogproofing_civicrm_enable() {
  _changelogproofing_civix_civicrm_enable();
}
