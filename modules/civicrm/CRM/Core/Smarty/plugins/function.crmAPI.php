<?php
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
 *
 * @package CRM
 * @copyright TTTP
 * $Id$
 *
 */

/**
 */
function smarty_function_crmAPI($params, &$smarty) {
  if (array_key_exists('action', $params) && !array_key_exists('action', $params)) {
    $params['action'] = $params['method'];
  }
  if (empty($params['action'])) {
    $params['action'] = "get";
  }
  if (empty($params['sequential'])) {
    $params['sequential'] = 1;
  }
  if (empty($params['entity'])) {
    $smarty->trigger_error("assign: missing 'entity' parameter");
    return "crmAPI: missing 'entity' parameter";
  }
  CRM_Core_Error::setCallback(array('CRM_Utils_REST', 'fatal'));
  $action = $params['action'];
  $entity = $params['entity'];
  unset($params['entity']);
  unset($params['method']);
  unset($params['assign']);
  $params['version'] = 3;
  require_once 'api/api.php';
  $result = civicrm_api($entity, $action, $params);
  CRM_Core_Error::setCallback();
  if ($result === FALSE) {
    $smarty->trigger_error("Unkown error");
    return;
  }

  if (empty($params['var'])) {
    return json_encode($result);
  }
  if (!empty($params['json'])) {
    $smarty->assign($params["var"], json_encode($result));
  }
  else {
    $smarty->assign($params["var"], $result);
  }
}




