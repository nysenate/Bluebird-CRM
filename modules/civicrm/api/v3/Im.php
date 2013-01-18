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
 * File for the CiviCRM APIv3 IM functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_IM
 *
 * @copyright CiviCRM LLC (c) 2004-2013
 * @version $Id: IM.php 2013-01-15 BrianShaughnessy $
 */

require_once 'CRM/Core/BAO/IM.php';

/**
 *  Add an IM for a contact
 *
 * Allowed @params array keys are:
 * {@getfields im_create}
 *
 * @return array of newly created IM property values.
 * @access public
 * @todo convert to using basic create - BAO function non-std
 */
function civicrm_api3_im_create($params) {
  $imBAO = CRM_Core_BAO_IM::add($params);
    $values = array();
    _civicrm_api3_object_to_array($imBAO, $values[$imBAO->id]);
    return civicrm_api3_create_success($values, $params, 'im', 'get');

}
/*
 * Adjust Metadata for Create action
 *
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_im_create_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

/**
 * Deletes an existing IM
 *
 * @param  array  $params
 * {@getfields im_delete}
 *
 * @return array API result Array
 * @access public
 * @todo convert to using Basic delete - BAO function non standard
 */
function civicrm_api3_im_delete($params) {
  $imID = CRM_Utils_Array::value('id', $params);

  require_once 'CRM/Core/DAO/IM.php';
  $imDAO = new CRM_Core_DAO_IM();
  $imDAO->id = $imID;
  if ($imDAO->find()) {
    while ($imDAO->fetch()) {
      $imDAO->delete();
      return civicrm_api3_create_success(1, $params, 'im', 'delete');
    }
  }
  else {
    return civicrm_api3_create_error('Could not delete IM with id ' . $imID);
  }
}

/**
 * Retrieve one or more IM
 *
 * @param  mixed[]  (reference ) input parameters
 * {@getfields im_get}
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array details of found IM
 *
 * @access public
 */
function civicrm_api3_im_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

