<?php
// $Id$

require_once 'CRM/Core/BAO/OptionValue.php';

/**
 * Retrieve one or more OptionValues
 *
 * @param  array  $ params input parameters
 *
 * {@example OptionValueGet.php 0}
 * @example OptionValueGet.php
 *
 * @return  array details of found Option Values
 * {@getfields OptionValue_get}
 * @access public
 */
function civicrm_api3_option_value_get($params) {

  if (empty($params['option_group_id']) && !empty($params['option_group_name'])) {
    $opt = array('version' => 3, 'name' => $params['option_group_name']);
    $optionGroup = civicrm_api('OptionGroup', 'Get', $opt);
    if (empty($optionGroup['id'])) {
      return civicrm_api3_create_error("option group name does not correlate to a single option group");
    }
    $params['option_group_id'] = $optionGroup['id'];
  }

  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 *  Add a OptionValue. OptionValues are used to classify CRM entities (including Contacts, Groups and Actions).
 *
 * Allowed @params array keys are:
 *
 * {@example OptionValueCreate.php}
 *
 * @return array of newly created option_value property values.
 * {@getfields OptionValue_create}
 * @access public
 */
function civicrm_api3_option_value_create($params) {

  $weight = 0;
  if (!array_key_exists('label', $params) && array_key_exists('name', $params)) {
    // no idea why that's a "mandatory" field
    $params['label'] = $params['name'];
  }
  if (!CRM_Utils_Array::value('value', $params) && array_key_exists('option_group_id', $params)) {
    require_once 'CRM/Utils/Weight.php';
    $fieldValues = array('option_group_id' => $params['option_group_id']);
    // use the next available value
    /* CONVERT(value, DECIMAL) is used to convert varchar
       field 'value' to decimal->integer                    */


    $params['value'] = (int) CRM_Utils_Weight::getDefaultWeight('CRM_Core_DAO_OptionValue',
      $fieldValues,
      'CONVERT(value, DECIMAL)'
    );
    $weight = $params['value'];
  }
  if (!array_key_exists('weight', $params) && array_key_exists('value', $params)) {
    // no idea why that's a "mandatory" field
    $params['weight'] = $params['value'];
  } elseif (array_key_exists('weight', $params) && $params['weight'] == 'next') {
    // weight is numeric, so it's safe-ish to treat symbol 'next' as magical value
    $params['weight'] = CRM_Utils_Weight::getDefaultWeight('CRM_Core_DAO_OptionValue',
      array('option_group_id' => $params['option_group_id'])
    );
  }

  if (array_key_exists('component', $params)) {
    if (empty($params['component'])) {
      $params['component_id'] = '';
    } else {
      $params['component_id'] = array_search($params['component'], CRM_Core_PseudoConstant::component());
    }
    unset($params['component']);
  }

  if (CRM_Utils_Array::value('id', $params)) {
    $ids = array('optionValue' => $params['id']);
  }
  $optionValueBAO = CRM_Core_BAO_OptionValue::add($params, $ids);
  civicrm_api('option_value', 'getfields', array('version' => 3, 'cache_clear' => 1));
  $values = array();
  _civicrm_api3_object_to_array($optionValueBAO, $values[$optionValueBAO->id]);
  return civicrm_api3_create_success($values, $params);
}

/*
 * Adjust Metadata for Create action
 * 
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_option_value_create_spec(&$params) {
  $params['is_active']['api.default'] = 1;
  $params['component']['type'] = CRM_Utils_Type::T_STRING;
  $params['component']['options'] = array_values(CRM_Core_PseudoConstant::component());
  // $params['component_id']['pseudoconstant'] = 'component';
}

/**
 * Deletes an existing OptionValue
 *
 * @param  array  $params
 *
 * {@example OptionValueDelete.php 0}
 *
 * @return array Api result
 * {@getfields OptionValue_create}
 * @access public
 */
function civicrm_api3_option_value_delete($params) {
  return CRM_Core_BAO_OptionValue::del((int) $params['id']) ? civicrm_api3_create_success() : civicrm_api3_create_error('Could not delete OptionValue ' . $params['id']);
}

