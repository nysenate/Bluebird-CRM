<?php
// $Id$

define(API_V3_EXTENSION_DELIMITER, ',');

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
 *                          - keys: mixed; array of string, eg array("com.example.myextension1", "com.example.myextension2") or string with comma-delimited list
 *                            using 'keys' should be more performant than making multiple API calls with 'key'
 *
 * @return array API result
 * @static void
 * @access public
 * @example ExtensionInstall.php
 *
 */
function civicrm_api3_extension_install($params) {
  $keys = _civicrm_api3_getKeys($params);
  if (count($keys) == 0) {
    return civicrm_api3_create_success();
  }

  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  foreach ($keys as $key) {
    if (!$ext->isEnabled()) {
      return civicrm_api3_create_error('Extension support is not enabled');
    } elseif (!$ext->isExtensionKey($key) || !array_key_exists($key, $exts)) {
      return civicrm_api3_create_error('Invalid extension key: ' . $key);
    } elseif ($exts[$key]->status == 'installed' && $exts[$key]->is_active == TRUE) {
      // already installed
    } elseif (!in_array($exts[$key]->status, array('remote', 'local'))) {
      return civicrm_api3_create_error('Can only install extensions with status "Available (Local)" or "Available (Remote)"');
    } else {
      // pre-condition: not installed
      $ext->install(NULL, $key); // FIXME: only rebuild cache one time
    }
  }
  return civicrm_api3_create_success();
}

/**
 * Enable an extension
 *
 * @param  array   	  $params input parameters
 *                          - key: string, eg "com.example.myextension"
 *                          - keys: mixed; array of string, eg array("com.example.myextension1", "com.example.myextension2") or string with comma-delimited list
 *                            using 'keys' should be more performant than making multiple API calls with 'key'
 *
 * @return array API result
 * @static void
 * @access public
 * @example ExtensionEnable.php
 *
 */
function civicrm_api3_extension_enable($params) {
  $keys = _civicrm_api3_getKeys($params);
  if (count($keys) == 0) {
    return civicrm_api3_create_success();
  }

  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  foreach ($keys as $key) {
    if (!$ext->isEnabled()) {
      return civicrm_api3_create_error('Extension support is not enabled');
    } elseif (!$ext->isExtensionKey($key) || !array_key_exists($key, $exts)) {
      return civicrm_api3_create_error('Invalid extension key: ' . $key);
    } elseif ($exts[$key]->status == 'installed' && $exts[$key]->is_active == TRUE) {
      // already enabled
    } elseif ($exts[$key]->status != 'installed') {
      return civicrm_api3_create_error('Can only enable extensions which have been previously installed');
    } elseif ($exts[$key]->is_active == TRUE) {
      return civicrm_api3_create_error('Can only enable extensions which are currently inactive');
    } else {
      // pre-condition: installed and inactive
      $ext->enable(NULL, $key); // FIXME: only rebuild cache one time
    }
  }
  return civicrm_api3_create_success();
}

/**
 * Disable an extension
 *
 * @param  array   	  $params input parameters
 *                          - key: string, eg "com.example.myextension"
 *                          - keys: mixed; array of string, eg array("com.example.myextension1", "com.example.myextension2") or string with comma-delimited list
 *                            using 'keys' should be more performant than making multiple API calls with 'key'
 *
 * @return array API result
 * @static void
 * @access public
 * @example ExtensionDisable.php
 *
 */
function civicrm_api3_extension_disable($params) {
  $keys = _civicrm_api3_getKeys($params);
  if (count($keys) == 0) {
    return civicrm_api3_create_success();
  }

  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  foreach ($keys as $key) {
    if (!$ext->isEnabled()) {
      return civicrm_api3_create_error('Extension support is not enabled');
    } elseif (!$ext->isExtensionKey($key) || !array_key_exists($key, $exts)) {
      return civicrm_api3_create_error('Invalid extension key: ' . $key);
    } elseif ($exts[$key]->status == 'installed' && $exts[$key]->is_active != TRUE) {
      // already disabled
    } elseif ($exts[$key]->status != 'installed') {
      return civicrm_api3_create_error('Can only disable extensions which have been previously installed');
    } elseif ($exts[$key]->is_active != TRUE) {
      return civicrm_api3_create_error('Can only disable extensions which are active');
    } else {
      // pre-condition: installed and active
      $ext->disable(NULL, $key); // FIXME: only rebuild cache one time
    }
  }
  return civicrm_api3_create_success();
}

/**
 * Uninstall an extension
 *
 * @param  array   	  $params input parameters
 *                          - key: string, eg "com.example.myextension"
 *                          - keys: array of string, eg array("com.example.myextension1", "com.example.myextension2")
 *                            using 'keys' should be more performant than making multiple API calls with 'key'
 *                          - removeFiles: bool, whether to remove source tree; default: FALSE
 *
 * @return array API result
 * @static void
 * @access public
 * @example ExtensionUninstall.php
 *
 */
function civicrm_api3_extension_uninstall($params) {
  $keys = _civicrm_api3_getKeys($params);
  if (count($keys) == 0) {
    return civicrm_api3_create_success();
  }

  $ext = new CRM_Core_Extensions();
  $exts = $ext->getExtensions();
  foreach ($keys as $key) {
    if (!$ext->isEnabled()) {
      return civicrm_api3_create_error('Extension support is not enabled');
    } elseif (!$ext->isExtensionKey($key) || !array_key_exists($key, $exts)) {
      // FIXME: is this necesary? if $key is not in $exts, can't we assume it's uninstalled
      return civicrm_api3_create_error('Invalid extension key: ' . $key);
    } elseif ($exts[$key]->status != 'installed') {
      return civicrm_api3_create_error('Can only uninstall extensions which have been previously installed');
    } elseif ($exts[$key]->is_active == TRUE) {
      return civicrm_api3_create_error('Extension must be disabled before uninstalling');
    } else {
      // pre-condition: installed and inactive
      $removeFiles = CRM_Utils_Array::value('removeFiles', $params, FALSE);
      $ext->uninstall(NULL, $key, $removeFiles); // FIXME: only rebuild cache one time
    }
  }
  return civicrm_api3_create_success();
}

/**
 * Determine the list of extension keys
 *
 * @param array $params API request params with 'key' or 'keys'
 * @return array of extension keys
 * @throws API_Exception
 */
function _civicrm_api3_getKeys($params) {
  if (array_key_exists('keys', $params) && is_array($params['keys'])) {
    return $params['keys'];
  } elseif (array_key_exists('keys', $params) && is_string($params['keys'])) {
    if ($params['keys'] == '') {
      return array();
    } else {
      return explode(API_V3_EXTENSION_DELIMITER, $params['keys']);
    }
  } elseif (array_key_exists('key', $params)) {
    return array($params['key']);
  } else {
    throw new API_Exception('Missing required parameter: key or keys');
  }
}
