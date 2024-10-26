<?php

require_once 'angularprofiles.civix.php';
use CRM_AngularProfiles_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 * @param $config
 */
function angularprofiles_civicrm_config(&$config) {
  _angularprofiles_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function angularprofiles_civicrm_install() {
  return _angularprofiles_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function angularprofiles_civicrm_enable() {
  return _angularprofiles_civix_civicrm_enable();
}

/**
 * @param $angularModule
 */
function angularprofiles_civicrm_angularModules(&$angularModule) {
  $angularModule['crmProfileUtils'] = [
    'ext' => E::LONG_NAME,
    'js' => ['js/crmProfiles.js'],
    'partials' => ['partials'],
    'settingsFactory' => ['CRM_AngularProfiles_Page_Template', 'getAngularSettings'],
  ];
}
