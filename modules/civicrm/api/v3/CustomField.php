<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * File for the CiviCRM APIv3 custom group functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_CustomField
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: CustomField.php 30879 2010-11-22 15:45:55Z shot $
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Core/BAO/CustomField.php';

/**
 * Most API functions take in associative arrays ( name => value pairs
 * as parameters. Some of the most commonly used parameters are
 * described below
 *
 * @param array $params           an associative array used in construction
 * retrieval of the object
 *
 */


/**
 * Defines 'custom field' within a group.
 *
 *
 * @param $params       array  Associative array of property name/value pairs to create new custom field.
 *
 * @return Newly created custom_field id array
 *
 * @access public
 * 
 * @example CustomFieldCreate.php
 * 
 * {@example CustomFieldCreate.php 0}
 *
 */

function civicrm_api3_custom_field_create( $params )
{

        civicrm_api3_verify_mandatory($params,null,array('custom_group_id','label'));
        
        if ( !( CRM_Utils_Array::value('option_type', $params ) ) ) {
            if( CRM_Utils_Array::value('id', $params ) ){
                $params['option_type'] = 2;
            } else {
                $params['option_type'] = 1;
            }
        }
        
        if (is_a($error, 'CRM_Core_Error')) {
            return civicrm_api3_create_error( $error->_errors[0]['message'] );
        }
        
        // Array created for passing options in params
        if ( isset( $params['option_values'] ) && is_array( $params['option_values'] ) ) {
            foreach ( $params['option_values'] as $key => $value ){
                $params['option_label'][$key] = $value['label'];
                $params['option_value'][$key] = $value['value'];
                $params['option_status'][$key] = $value['is_active'];
                $params['option_weight'][$key] = $value['weight']; 
            }
        }
        
        $customField = CRM_Core_BAO_CustomField::create($params);
        _civicrm_api3_object_to_array_unique_fields($customField , $values[$customField->id]);
        return civicrm_api3_create_success($values,$params, 'custom_field',$customField);

}

/**
 * Use this API to delete an existing custom group field.
 *
 * @param $params     Array id of the field to be deleted
 * @example CustomFieldDelete.php
 * 
 * {@example CustomFieldDelete.php 0}
 * @access public
 **/
function civicrm_api3_custom_field_delete( $params )
{
        civicrm_api3_verify_mandatory($params,null,array('id'));
        
        $field = new CRM_Core_BAO_CustomField( );
        $field->id = $params['id'];
        $field->find(true);
        
        
        $customFieldDelete = CRM_Core_BAO_CustomField::deleteField( $field );
        return $customFieldDelete ?
            civicrm_api3_create_error('Error while deleting custom field') :
            civicrm_api3_create_success( );


}

/**
 * Use this API to get existing custom fields.
 *
 * @param array $params Array to search on
 *
* @access public
 * 
 **/
function civicrm_api3_custom_field_get($params)
{

        civicrm_api3_verify_mandatory($params);
        return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);

}

/*
 * Helper function to validate custom field values 
 * 
 * @params Array   $params             Custom fields with values
 * @params Array   $errors             Reference fields to be check with 
 * @params Boolean $checkForDisallowed Check for disallowed elements 
 *                                     in params
 * @params Boolean $checkForRequired   Check for non present required elements
 *                                     in params
 * @return Array  Validation errors
 */
function _civicrm_api3_custom_field_validate_fields($params, $fields, $checkForDisallowed = false, $checkForRequired = false) {
    $checkFields = $errors = $disallowedFields = $requiredFields = array( );
    foreach ( $params as $fieldName => $value ) {
        if (substr($fieldName, 0, 6) === 'custom') {
            $customFieldID = CRM_Core_BAO_CustomField::getKeyID($fieldName);
            if ( $customFieldID ) {
                $checkFields[$customFieldID] = $fieldName;
                if ( !in_array($customFieldID, array_keys($fields) ) ) {
                    $disallowedFields[$customFieldID] = $fieldName;
                } else if( CRM_Utils_Array::value('is_required', $fields[$customFieldID] ) ) {
                    $requiredFields[$customFieldID] = $fieldName;
                }
            }
        }
    }
    
    if ( empty($checkFields) ) {
        return $errors;
    }
    
    if ( $checkForDisallowed && !empty($disallowedFields) ) {
        $errors[] = "Can't use custom field(s) : ". implode(', ', $disallowedFields);
        return $errors; 
    }
    
    if ( $checkForRequired && !empty($missingRequired) ) {
        $errors[] = 'Missing required field(s) : '. implode(', ', $missingRequired);
        return $errors;
    }
    
    foreach( $checkFields as $fieldId => $fieldName ) {
        _civicrm_api3_custom_field_validate_field($fieldName, $params[$fieldName], $fields[$fieldId], $errors );   
    }
    
    return $errors;
}

/*
 * Helper function to validate custom field value
 * 
 * @params String $fieldName    Custom field name (eg: custom_8 )
 * @params Mixed  $value        Field value to be validate
 * @params Array  $fieldDetails Field Details
 * @params Array  $errors       Collect validation  errors
 *
 * @return Array  Validation errors
 */
function _civicrm_api3_custom_field_validate_field( $fieldName, $value, $fieldDetails, &$errors = array( ) ) {
    if ( !$value ) {
        return $errors;
    }

    $dataType = $fieldDetails['data_type'];
    $htmlType = $fieldDetails['html_type'];
    
    switch ( $dataType ) {
        
    case 'Int':
        if ( ! CRM_Utils_Rule::integer($value) ) {
            $errors[$fieldName] = 'Invalid integer value for '. $fieldName;
        }
        break;
        
    case 'Float':
        if ( ! CRM_Utils_Rule::numeric($value) ) {
            $errors[$fieldName] = 'Invalid numeric value for '. $fieldName;
        }
        break;
        
    case 'Money':
        if ( ! CRM_Utils_Rule::money($value) ) {
            $errors[$fieldName] = 'Invalid numeric value for '. $fieldName;
        }
        break;
        
    case 'Link':
        if ( ! CRM_Utils_Rule::url($value) ) {
            $errors[$fieldName] = 'Invalid link for '. $fieldName;
        }
        break;
        
        
    case 'Boolean':
        if ( $value != '1' && $value != '0' ) {
            $errors[$fieldName] = 'Invalid boolean (use 1 or 0) value for '. $fieldName;
            }
        break;
            
    case 'Country':
        if( empty($value) ) {
            break;
        }
        if ( $htmlType != 'Multi-Select Country' && is_array($value) ) {
            $errors[$fieldName] = 'Invalid country for '. $fieldName;
            break;
        }

        if ( !is_array($value) ) {
            $value = array($value);
        }
        
        $query  = "SELECT count(*) FROM civicrm_country WHERE id IN (". implode(',', $value) .")";
        if ( CRM_Core_DAO::singleValueQuery( $query ) < count($value) ) {
            $errors[$fieldName] = 'Invalid country(s) for '. $fieldName;
        }
        break;
        
    case 'StateProvince':
        if( empty($value) ) {
            break;
        }

        if ( $htmlType != 'Multi-Select State/Province' && is_array($value) ) {
            $errors[$fieldName] = 'Invalid State/Province for '. $fieldName;
            break;
        }
        
        if ( !is_array($value) ) {
            $value = array($value);
        }

        $query = "
SELECT count(*) 
  FROM civicrm_state_province
 WHERE id IN ('". implode("','", $value ) ."')";
        if ( CRM_Core_DAO::singleValueQuery( $query ) < count($value) ) {
            $errors[$fieldName] = 'Invalid State/Province for '. $fieldName;
        }
        break;
        
    case 'ContactReference':
        //FIX ME
        break;
    }
    
    if ( in_array($htmlType, array('Select', 'Multi-Select', 'CheckBox', 'Radio', 'AdvMulti-Select')) &&
         !isset($errors[$fieldName]) ) {
        require_once 'CRM/Core/OptionGroup.php';
        $options = CRM_Core_OptionGroup::valuesByID( $fieldDetails['option_group_id'] );
        if ( !is_array($value) ) {
            $value = array( $value );
        }
        
        $invalidOptions = array_diff( $value, array_keys($options) );
        if (!empty($invalidOptions) ) {
            $errors[$fieldName] = "Invalid option(s) for field '{$fieldName}': ". implode(',', $invalidOptions); 
        }
    }

    return $errors;
}