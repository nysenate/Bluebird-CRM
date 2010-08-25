<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/I18n.php';
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Utils/Array.php';
require_once 'CRM/Utils/String.php';
require_once 'CRM/Core/DAO/OptionGroup.php';
require_once 'CRM/Core/DAO/OptionValue.php';

function _crm_error( $message, $code = 8000, $level = 'Fatal', $params = null)
{
    return CRM_Core_Error::createError( $message, $code, $level, $params );
}

function _crm_store_values( &$fields, &$params, &$values ) {
    $valueFound = false;
    
    foreach ($fields as $name => $field) {
        // ignore all ids for now
        if ( $name === 'id' || substr( $name, -1, 3 ) === '_id' ) {
            continue;
        }

        if ( array_key_exists( $name, $params ) ) {
            $values[$name] = $params[$name];
            $valueFound = true;
        }
    }
    return $valueFound;
}

function _crm_update_object(&$object, &$values)
{
    // abort early if the object is an error object: CRM-500 & CRM-559
    // we should trap and return somehow, or not get into this state
    if ( is_a( $object, 'CRM_Core_Error' ) || ! $object ) {
        return;
    }

    $fields =& $object->fields( );
    $valueFound = false;

    foreach ($fields as $name => $field) {
        $key = $field['name'];
        // ignore all ids for now
        if ($key === 'id') {
            continue;
        }

        if (array_key_exists( $name, $values)) {
            $object->$key = $values[$name];
            //if ( substr( $name, -1, 3 ) !== '_id' ) {
            /* only say we've found a value if at least one is not null */
            // why do we check for non-id-ness and not null-ness?
            // we do want to update FKs and be able to null fields, don't we?
#           if (substr($name, -3, 3) !== '_id' && $values[$name] !== null) {
                $valueFound = true;
#           }
        }
    }
    //    print_r($object);
    if ($valueFound) {
        $object->save();
    }
}


function _crm_update_from_object(&$object, &$values, $empty = false, $zeroMoney = false)
{
    $fields =& $object->fields();

    require_once 'CRM/Utils/Type.php';
    foreach ($fields as $name => $field) {
        $key = $field['name'];
        if (($key == 'id') or ($empty and empty($object->$key)) or
            ($zeroMoney and $field['type'] == CRM_Utils_Type::T_MONEY and $object->$key == '0.00')) {
            continue;
        }

        $values[$name] = $object->$key;

        // FIXME? change the dates from YYYY-MM-DD hh:mm:ss format back to YYYYMMDDhhmmss
        // so the $values array is actually importable
        if ($field['type'] & CRM_Utils_Type::T_DATE) {
            $dropArray = array('-' => '', ':' => '', ' ' => '');
            $values[$name] = strtr($values[$name], $dropArray);
        }
    }
}

function _crm_object_to_array( &$dao, &$values )
{
    $tmpFields = $dao->fields();
    $fields = array();
    //rebuild $fields array to fix unique name of the fields
    foreach( $tmpFields as $key => $val ) {
        $fields[$val["name"]]  = $val;
    }
    
    foreach( $fields as $key => $value ) {
        if (array_key_exists($key, $dao)) {
            $values[$key] = $dao->$key;
        }
    }
}

/**
 * This function ensures that we have the right input contribution parameters
 *
 * We also need to make sure we run all the form rules on the params list
 * to ensure that the params are valid
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contribution.
 *
 * @return bool|CRM_Utils_Error
 * @access public
 */
function _crm_check_contrib_params( &$params ) {
    static $required = array( 'contact_id', 'total_amount', 'contribution_type_id' );

    // cannot create a contribution with empty params
    if ( empty( $params ) ) {
        return _crm_error( 'Input Parameters empty' );
    }

    $valid = true;
    $error = '';
    foreach ( $required as $field ) {
        if ( ! CRM_Utils_Array::value( $field, $params ) ) {
            $valid = false;
            $error .= $field;
            break;
        }
    }
    
    if ( ! $valid ) {
        return _crm_error( "Required fields not found for contribution $error" );
    }

    return true;
}

/**
 * take the input parameter list as specified in the data model and 
 * convert it into the same format that we use in QF and BAO object
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contact.
 * @param array  $values       The reformatted properties that we can use internally
 *                            '
 * @return array|CRM_Error
 * @access public
 */
function _crm_format_contrib_params( &$params, &$values, $create=false ) {
    // copy all the contribution fields as is
   
    $fields =& CRM_Contribute_DAO_Contribution::fields( );

    _crm_store_values( $fields, $params, $values );
    
    foreach ($params as $key => $value) {
        // ignore empty values or empty arrays etc
        if ( CRM_Utils_System::isNull( $value ) ) {
            continue;
        }

        switch ($key) {

        case 'contribution_contact_id':
            if (!CRM_Utils_Rule::integer($value)) {
                return _crm_error("contact_id not valid: $value");
            }
            $dao =& new CRM_Core_DAO();
            $qParams = array();
            $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",
                                          $qParams);
            if (!$svq) {
                return _crm_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
            }
            
            $values['contact_id'] = $values['contribution_contact_id'];
            unset ($values['contribution_contact_id']);
            break;

        case 'receive_date':
        case 'cancel_date':
        case 'receipt_date':
        case 'thankyou_date':
            if (!CRM_Utils_Rule::date($value)) {
                return _crm_error("$key not a valid date: $value");
            }
            break;

        case 'non_deductible_amount':
        case 'total_amount':
        case 'fee_amount':
        case 'net_amount':
            if (!CRM_Utils_Rule::money($value)) {
                return _crm_error("$key not a valid amount: $value");
            }
            break;
        case 'currency':
            if (!CRM_Utils_Rule::currencyCode($value)) {
                return _crm_error("currency not a valid code: $value");
            }
            break;
        case 'contribution_type':            
            require_once 'CRM/Contribute/PseudoConstant.php';
            $values['contribution_type_id'] = CRM_Utils_Array::key( ucfirst( $value ),
                                                                    CRM_Contribute_PseudoConstant::contributionType( )
                                                                    );
            break;
        case 'payment_instrument': 
            require_once 'CRM/Core/OptionGroup.php';
            $values['payment_instrument_id'] = CRM_Core_OptionGroup::getValue( 'payment_instrument', $value );
            break;
        case 'contribution_status_id':  
            require_once 'CRM/Core/OptionGroup.php';
            $values['contribution_status_id'] = CRM_Core_OptionGroup::getValue( 'contribution_status', $value );
            break;
        default:
            break;
        }
    }
    if ( array_key_exists( 'note', $params ) ) {
        $values['note'] = $params['note'];
    }
    _crm_format_custom_params( $params, $values, 'Contribution' );
    
    if ( $create ) {
        // CRM_Contribute_BAO_Contribution::add() handles contribution_source
        // So, if $values contains contribution_source, convert it to source
        $changes = array( 'contribution_source' => 'source' );
        
        foreach ($changes as $orgVal => $changeVal) {
            if ( isset($values[$orgVal]) ) {
                $values[$changeVal] = $values[$orgVal];
                unset($values[$orgVal]);
            }
        }
    }
    
    return null;
}

/**
 * take the input parameter list as specified in the data model and 
 * convert it into the same format that we use in QF and BAO object
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contact.
 * @param array  $values       The reformatted properties that we can use internally
 *
 * @param array  $create       Is the formatted Values array going to
 *                             be used for CRM_Member_BAO_Membership:create()
 *
 * @return array|CRM_Error
 * @access public
 */
function _crm_format_membership_params( &$params, &$values, $create=false) 
{
    require_once "CRM/Member/DAO/Membership.php";
    $fields =& CRM_Member_DAO_Membership::fields( );
    _crm_store_values( $fields, $params, $values );
    
    foreach ($params as $key => $value) {
        // ignore empty values or empty arrays etc
        if ( CRM_Utils_System::isNull( $value ) ) {
            continue;
        }
        
        switch ($key) {
        case 'membership_contact_id':
            if (!CRM_Utils_Rule::integer($value)) {
                return _crm_error("contact_id not valid: $value");
            }
            $dao =& new CRM_Core_DAO();
            $qParams = array();
            $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",$qParams);
            if (!$svq) {
                return _crm_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
            }
            $values['contact_id'] = $values['membership_contact_id'];
            unset($values['membership_contact_id']);
            break;
        case 'join_date':
        case 'membership_start_date':
        case 'membership_end_date':
            if (!CRM_Utils_Rule::date($value)) {
                return _crm_error("$key not a valid date: $value");
            }
            break;
        case 'membership_type_id':
            $id = CRM_Core_DAO::getFieldValue( "CRM_Member_DAO_MembershipType", $value, 'id', 'name' );
            $values[$key] = $id;
            break;
        case 'status_id':
            $id = CRM_Core_DAO::getFieldValue( "CRM_Member_DAO_MembershipStatus", $value, 'id', 'name' );
            $values[$key] = $id;
            break;
        default:
            break;
        }
    }
    
    _crm_format_custom_params( $params, $values, 'Membership' );
    
    if ( $create ) {
        // CRM_Member_BAO_Membership::create() handles membership_start_date,
        // membership_end_date and membership_source. So, if $values contains
        // membership_start_date, membership_end_date  or membership_source,
        // convert it to start_date, end_date or source
        $changes = array('membership_start_date' => 'start_date',
                         'membership_end_date'   => 'end_date',
                         'membership_source'     => 'source',
                         );
        
        foreach ($changes as $orgVal => $changeVal) {
            if ( isset($values[$orgVal]) ) {
                $values[$changeVal] = $values[$orgVal];
                unset($values[$orgVal]);
            }
        }
    }
    
    return null;
}

function _crm_format_custom_params( &$params, &$values, $extends )
{
    $values['custom'] = array();
    
    $customFields = CRM_Core_BAO_CustomField::getFields( $extends );

    foreach ($params as $key => $value) {
        if ($customFieldID = CRM_Core_BAO_CustomField::getKeyID($key)) {

            /* check if it's a valid custom field id */
            if ( !array_key_exists($customFieldID, $customFields)) {
                return _crm_error('Invalid custom field ID');
            }
            
            $fieldType = null;
            
            // modified for CRM-1586
            // check data type for importing custom field (labels) with data type Integer/Float/Money
            /* validate the data against the CF type */
            if( ( $customFields[$customFieldID]['data_type'] == "Int")    ||
                ( $customFields[$customFieldID]['data_type'] == "Float" ) ||
                ( $customFields[$customFieldID]['data_type'] == "Money" ) ) { 
                if ( $customFields[$customFieldID]['html_type'] == "Text" ) {
                    $fieldType = $customFields[$customFieldID]['data_type'];
                } else {
                    $customOption = CRM_Core_BAO_CustomOption::getCustomOption($customFieldID);
                    foreach($customOption as $customValue => $customLabel) {
                        //check wether $value is label or value
                        if ( ( strtolower($customValue) == strtolower( trim( $value ) ) ) ) {
                            $fieldType = "String";
                        } else if ( ( strtolower($customValue) == strtolower( trim( $value ) ) ) ) {
                            $fieldType = $customFields[$customFieldID]['data_type'];
                        }
                    }
                }
            } else {
                //set the Field type 
                $fieldType = $customFields[$customFieldID]['data_type'];
            }
            
            $valid = null;
            
            //Validate the datatype of $value
            $valid = CRM_Core_BAO_CustomValue::typecheck( $fieldType, $value);

            //return error, if not valid custom field
            if ( ! $valid ) {
                return _crm_error('Invalid value for custom field ' .
                                  $customFields[$customFieldID][1]);
            }
            
            // fix the date field if so
            if ( $customFields[$customFieldID]['data_type'] == 'Date' ) {
                $value = str_replace( '-', '', $value );
            }
            
            // fixed for checkbox and multiselect
            $newMulValues = array();
            if ( $customFields[$customFieldID]['html_type'] == 'CheckBox' || $customFields[$customFieldID]['html_type'] =='Multi-Select') {
                $value = str_replace(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,',',trim($value,CRM_Core_BAO_CustomOption::VALUE_SEPERATOR));
                $value = str_replace("|",",",$value);
                $mulValues = explode( ',' , $value );
                $custumOption = CRM_Core_BAO_CustomOption::getCustomOption($customFieldID, true);
                foreach( $mulValues as $v1 ) {
                    foreach($customOption as $customValue => $customLabel) {
                        if (( strtolower($customLabel) == strtolower(trim($v1)) )||( strtolower($customValue) == strtolower(trim($v1)) )) {
                            $newMulValues[] = $customValue;
                        }
                    }
                }
                $value = CRM_Core_BAO_CustomOption::VALUE_SEPERATOR.implode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,$newMulValues).CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
                
            } else if ( $customFields[$customFieldID]['html_type'] == 'Select' || $customFields[$customFieldID]['html_type'] == 'Radio' ) {
                $custumOption = CRM_Core_BAO_CustomOption::getCustomOption($customFieldID, true);
                foreach($customOption as $customValue => $customLabel) {
                    if( ( strtolower($customLabel) == strtolower(trim($value)) )||( strtolower($customValue) == strtolower(trim($value)))) {
                        $value = $customValue;
                        break;
                    }
                }
            }
            $values['custom'][$customFieldID] = array(
                                                      'value'   => $value,
                                                      'extends' => $customFields[$customFieldID]['extends'],
                                                      'type'    => $customFields[$customFieldID]['data_type'],
                                                      'custom_field_id' => $customFieldID,
                                                      );
        }
        
    }
}

function _crm_update_contribution($contribution, $values, $overwrite = true)
{
    CRM_Contribute_BAO_Contribution::resolveDefaults($values, true);

    if (!$overwrite) {
        _crm_update_from_object($contribution, $values, true, true);
    }
    _crm_update_object($contribution, $values);
    
    return $contribution;
}


/**
 * This function ensures that we have the right input parameters
 *
 * We also need to make sure we run all the form rules on the params list
 * to ensure that the params are valid
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new history.
 *
 * @return bool|CRM_Utils_Error
 * @access public
 */
function _crm_check_required_fields(&$params, $daoName)
{
    $extends = CRM_Utils_Array::value( 'extends', $params );
    if ( ( $extends == 'Activity' || 
           $extends == 'Phonecall'  || 
           $extends == 'Meeting'    || 
           $extends == 'Group'      || 
           $extends == 'Contribution' 
           ) && 
         ( $params['style'] == 'Tab' ) ) {
        return _crm_error(ts("Can not create Custom Group in Tab for ". $extends));
    }
       
    require_once(str_replace('_', DIRECTORY_SEPARATOR, $daoName) . ".php");
    $dao =& new $daoName();
    $fields = $dao->fields();
    
    //eval('$dao =& new CRM_Core_DAO_' . $type . 'History();');
    
    
    $missing = array();
    foreach ($fields as $k => $v) {
        if ($k == 'id' || $k == 'domain_id') {
            continue;
        }
        
        if (CRM_Utils_Array::value( 'required', $v ) && !(isset($params[$k]))) {
            $missing[] = $k;
        }
    }

    if (!empty($missing)) {
        return _crm_error(ts("Required fields ". implode(',', $missing) . " for $daoName are not found"));
    }

    return true;
}

function _crm_initialize( ) {
    $config =& CRM_Core_Config::singleton( );
}

