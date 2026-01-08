<?php

require_once 'inlinehelp.civix.php';
use CRM_Inlinehelp_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function inlinehelp_civicrm_config(&$config) {
  _inlinehelp_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function inlinehelp_civicrm_install() {
  _inlinehelp_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function inlinehelp_civicrm_enable() {
  _inlinehelp_civix_civicrm_enable();
}
