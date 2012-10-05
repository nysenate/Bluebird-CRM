<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 * File for the CiviCRM APIv3 extension functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Extension
 *
 * @copyright CiviCRM LLC (c) 2004-2012
 * @version $Id$
 *
 */

/**
 * Install an extension
 *
 * @param  array   	  $params input parameters
 *                          - key: string, eg "com.example.myextension"
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 * @example ExtensionInstall.php
 *
 */
function civicrm_api3_extension_install($params) {
  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  if (!$ext->isEnabled()) {
    return civicrm_api3_create_error('Extension support is not enabled');
  } elseif (!isset($params['key'])) {
    return civicrm_api3_create_error('Missing required parameter: key');
  } elseif (!$ext->isExtensionKey($params['key']) || !array_key_exists($params['key'], $exts)) {
    return civicrm_api3_create_error('Unknown extension key');
  } elseif ($exts[$params['key']]->status == 'installed' && $exts[$params['key']]->is_active == TRUE) {
    return civicrm_api3_create_success(); // already installed
  } elseif (!in_array($exts[$params['key']]->status, array('remote', 'local'))) {
    return civicrm_api3_create_error('Can only install extensions with status "Available (Local)" or "Available (Remote)"');
  } else {
    // pre-condition not installed
    $ext->install(NULL, $params['key']);
    return civicrm_api3_create_success();
  }
}

/**
 * Enable an extension
 *
 * @param  array   	  $params input parameters
 *                          - key: string, eg "com.example.myextension"
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 * @example ExtensionEnable.php
 *
 */
function civicrm_api3_extension_enable($params) {
  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  if (!$ext->isEnabled()) {
    return civicrm_api3_create_error('Extension support is not enabled');
  } elseif (!array_key_exists('key', $params)) {
    return civicrm_api3_create_error('Missing required parameter: key');
  } elseif (!$ext->isExtensionKey($params['key']) || !array_key_exists($params['key'], $exts)) {
    return civicrm_api3_create_error('Unknown extension key');
  } elseif ($exts[$params['key']]->status == 'installed' && $exts[$params['key']]->is_active == TRUE) {
    return civicrm_api3_create_success(); // already enabled
  } elseif ($exts[$params['key']]->status != 'installed') {
    return civicrm_api3_create_error('Can only enable extensions which have been previously installed');
  } elseif ($exts[$params['key']]->is_active == TRUE) {
    return civicrm_api3_create_error('Can only enable extensions which are currently inactive');
  } else {
    // pre-condition: installed and inactive
    $ext->enable(NULL, $params['key']);
    return civicrm_api3_create_success();
  }
}

/**
 * Disable an extension
 *
 * @param  array   	  $params input parameters
 *                          - key: string, eg "com.example.myextension"
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 * @example ExtensionDisable.php
 *
 */
function civicrm_api3_extension_disable($params) {
  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  if (!$ext->isEnabled()) {
    return civicrm_api3_create_error('Extension support is not enabled');
  } elseif (!isset($params['key'])) {
    return civicrm_api3_create_error('Missing required parameter: key');
  } elseif (!$ext->isExtensionKey($params['key']) || !array_key_exists($params['key'], $exts)) {
    return civicrm_api3_create_error('Unknown extension key');
  } elseif ($exts[$params['key']]->status == 'installed' && $exts[$params['key']]->is_active != TRUE) {
    return civicrm_api3_create_success(); // already disabled
  } elseif ($exts[$params['key']]->status != 'installed') {
    return civicrm_api3_create_error('Can only disable extensions which have been previously installed');
  } elseif ($exts[$params['key']]->is_active != TRUE) {
    return civicrm_api3_create_error('Can only disable extensions which are active');
  } else {
    // pre-condition: installed and active
    $ext->disable(NULL, $params['key']);
    return civicrm_api3_create_success();
  }
}

/**
 * Uninstall an extension
 *
 * @param  array   	  $params input parameters
 *                          - key: string, eg "com.example.myextension"
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 * @example ExtensionUninstall.php
 *
 */
function civicrm_api3_extension_uninstall($params) {
  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  if (!$ext->isEnabled()) {
    return civicrm_api3_create_error('Extension support is not enabled');
  } elseif (!isset($params['key'])) {
    return civicrm_api3_create_error('Missing required parameter: key');
  } elseif (!$ext->isExtensionKey($params['key']) || !array_key_exists($params['key'], $exts)) {
    return civicrm_api3_create_error('Unknown extension key');
  } elseif ($exts[$params['key']]->status != 'installed') {
    return civicrm_api3_create_error('Can only uninstall extensions which have been previously installed');
  } elseif ($exts[$params['key']]->is_active == TRUE) {
    return civicrm_api3_create_error('Extension must be disabled before uninstalling');
  } else {
    // pre-condition: installed and inactive
    $ext->uninstall(NULL, $params['key']);
    return civicrm_api3_create_success();
  }
}
