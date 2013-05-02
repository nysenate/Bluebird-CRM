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
 * File for the CiviCRM APIv3 custom value functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_CustomField
 *
 * @copyright CiviCRM LLC (c) 2004-2012
 * @version $Id: CustomField.php 30879 2010-11-22 15:45:55Z shot $
 */

/**
 * Files required for this package
 */

require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
require_once 'CRM/Core/BAO/CustomValueTable.php';

/**
 * Sets custom values for an entity.
 *
 *
 * @param $params  expected keys are in format custom_fieldID:recordID or custom_groupName:fieldName:recordID
 * for example:
 // entity ID. You do not need to specify entity type, we figure it out based on the fields you're using
 * 'entity_id' => 123,
 // (omitting :id) inserts or updates a field in a single-valued group
 * 'custom_6' => 'foo',
 // custom_24 is checkbox or multiselect, so pass items as an array
 * 'custom_24' => array('bar', 'baz'),
 // in this case custom_33 is part of a multi-valued group, and we're updating record id 5
 * 'custom_33:5' => value,
 // inserts new record in multi-valued group
 * 'custom_33:-1' => value,
 // inserts another new record in multi-valued group
 * 'custom_33:-2' => value,
 // you can use group_name:field_name instead of ID
 * 'custom_some_group:my_field => 'myinfo',
 // updates record ID 8 in my_other_field in multi-valued some_big_group
 * 'custom_some_big_group:my_other_field:8 => 'myinfo',
 *
 *
 * @return array('values' => TRUE) or array('is_error' => 1, 'error_message' => 'what went wrong')
 *
 * @access public
 *
 */
function civicrm_api3_custom_value_create($params) {
  civicrm_api3_verify_mandatory($params, NULL, array('entity_id'));
  if (substr($params['entity_table'], 0, 7) == 'civicrm') {
    $params['entity_table'] = substr($params['entity_table'], 8, 7);
  }
  $create = array('entityID' => $params['entity_id']);
  // Translate names and
  //Convert arrays to multi-value strings
  $sp = CRM_Core_DAO::VALUE_SEPARATOR;
  foreach ($params as $id => $param) {
    if (is_array($param)) {
      $param = $sp . implode($sp, $param) . $sp;
    }
    list($c, $id) = explode('_', $id, 2);
    if ($c != 'custom') {
      continue;
    }
    list($i, $n, $x) = explode(':', $id);
    if (is_numeric($i)) {
      $key = $i;
      $x = $n;
    }
    else {
      // Lookup names if ID was not supplied
      $key = CRM_Core_BAO_CustomField::getCustomFieldID($n, $i);
      if (!$key) {
        continue;
      }
    }
    if ($x && is_numeric($x)) {
      $key .= '_' . $x;
    }
    $create['custom_' . $key] = $param;
  }
  $result = CRM_Core_BAO_CustomValueTable::setValues($create);
  if ($result['is_error']) {
    return civicrm_api3_create_error($result['error_message']);
  }
  return civicrm_api3_create_success(TRUE, $params);
}

/**
 * Use this API to get existing custom values for an entity.
 *
 * @param $params  array specifying the entity_id
 * Optionally include entity_type param, i.e. 'entity_type' => 'Activity'
 * If no entity_type is supplied, it will be determined based on the fields you request.
 * If no entity_type is supplied and no fields are specified, 'Contact' will be assumed.
 * Optionally include the desired custom data to be fetched (or else all custom data for this entity will be returned)
 * Example: 'entity_id' => 123, 'return.custom_6' => 1, 'return.custom_33' => 1
 * If you do not know the ID, you may use group name : field name, for example 'return.foo_stuff:my_field' => 1
 *
 * @return array.
 *
 * @access public
 *
 **/
function civicrm_api3_custom_value_get($params) {
  civicrm_api3_verify_mandatory($params, NULL, array('entity_id'));

  $getParams = array(
    'entityID' => $params['entity_id'],
    'entityType' => $params['entity_table'],
  );
  if (strstr($getParams['entityType'], 'civicrm_')) {
    $getParams['entityType'] = ucfirst(substr($getParams['entityType'], 8));
  }
  unset($params['entity_id'], $params['entity_table']);
  foreach ($params as $id => $param) {
    if ($param && substr($id, 0, 6) == 'return') {
      $id = substr($id, 7);
      list($c, $i) = explode('_', $id, 2);
      if ($c == 'custom' && is_numeric($i)) {
        $names['custom_' . $i] = 'custom_' . $i;
        $id = $i;
      }
      else {
        // Lookup names if ID was not supplied
        list($group, $field) = explode(':', $id, 2);
        $id = CRM_Core_BAO_CustomField::getCustomFieldID($field, $group);
        if (!$id) {
          continue;
        }
        $names['custom_' . $id] = 'custom_' . $i;
      }
      $getParams['custom_' . $id] = 1;
    }
  }

  $result = CRM_Core_BAO_CustomValueTable::getValues($getParams);

  if ($result['is_error']) {
    if ($result['error_message'] == "No values found for the specified entity ID and custom field(s).") {
      $values = array();
      return civicrm_api3_create_success($values, $params);
    }
    else {
      return civicrm_api3_create_error($result['error_message']);
    }
  }
  else {
    $entity_id = $result['entityID'];
    unset($result['is_error'], $result['entityID']);
    // Convert multi-value strings to arrays
    $sp = CRM_Core_DAO::VALUE_SEPARATOR;
    foreach ($result as $id => $value) {
      if (strpos($value, $sp) !== FALSE) {
        $value = explode($sp, trim($value, $sp));
      }

      $idArray = explode('_', $id);
      if ($idArray[0] != 'custom') {
        continue;
      }
      $fieldNumber = $idArray[1];
      $info = array_pop(CRM_Core_BAO_CustomField::getNameFromID($fieldNumber));
      // id is the index for returned results

      if (empty($idArray[2])) {
        $n = 0;
        $id = $fieldNumber;
      }
      else{
        $n = $idArray[2];
        $id = $fieldNumber . "." . $idArray[2];
      }
      if (CRM_Utils_Array::value('format.field_names', $params)) {
        $id = $info['field_name'];
      }
      else {
        $id = $fieldNumber;
      }
      $values[$id]['entity_id'] = $getParams['entityID'];
      if (CRM_Utils_Array::value('entityType', $getParams)) {
        $values[$n]['entity_table'] = $getParams['entityType'];
      }
      //set 'latest' -useful for multi fields but set for single for consistency
      $values[$id]['latest'] = $value;
      $values[$id]['id'] = $id;
      $values[$id][$n] = $value;
    }
    return civicrm_api3_create_success($values, $params);
  }
}

