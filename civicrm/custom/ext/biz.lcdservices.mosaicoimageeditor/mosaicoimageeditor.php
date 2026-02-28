<?php

/**
 * Resources:
 * https://github.com/voidlabs/mosaico/wiki
 * https://github.com/voidlabs/mosaico/issues/95
 * https://github.com/scaleflex/filerobot-image-editor/blob/master/examples/js/src/filerobot-init-example.js
 * https://github.com/veda-consulting-company/uk.co.vedaconsulting.mosaico/issues/347#issuecomment-555785659
 * https://scaleflex.github.io/filerobot-image-editor/
 * https://github.com/scaleflex/filerobot-image-editor#installation
 */

require_once 'mosaicoimageeditor.civix.php';
use CRM_Mosaicoimageeditor_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mosaicoimageeditor_civicrm_config(&$config) {
  _mosaicoimageeditor_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mosaicoimageeditor_civicrm_install() {
  _mosaicoimageeditor_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mosaicoimageeditor_civicrm_enable() {
  _mosaicoimageeditor_civix_civicrm_enable();
}

function mosaicoimageeditor_civicrm_mosaicoConfig(&$config) {
  //Civi::log()->debug(__FUNCTION__, ['config' => $config]);
}

//https://github.com/veda-consulting-company/uk.co.vedaconsulting.mosaico/issues/347#issuecomment-555785659
function mosaicoimageeditor_civicrm_mosaicoScripts(&$scripts) {
  $extUrl = CRM_Core_Resources::singleton()->getUrl(E::LONG_NAME);
  $scripts[] = $extUrl.'js/filerobot-image-editor.min.js';
  $scripts[] = $extUrl.'js/FileRobotPlugin.js';
}

function mosaicoimageeditor_civicrm_mosaicoPlugins(&$plugins) {
  $plugins[] = 'function(viewModel) { frie(viewModel); }';
}

function mosaicoimageeditor_civicrm_idsException(&$skip) {
  //Civi::log()->debug(__FUNCTION__, ['skip' => $skip]);

  $skip[] = 'civicrm/mosaico/iframe';
  $skip[] = 'civicrm/mosaicoimageditor/storefrie';
}
