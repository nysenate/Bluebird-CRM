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
 * File for the CiviCRM APIv2 relationship functions
 *
 * @package CiviCRM_APIv2
 * @subpackage API_Relationship
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Relationship.php 28119 2010-06-04 21:19:16Z lobo $
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v2/utils.php';
require_once 'CRM/Contact/BAO/Relationship.php';
require_once 'CRM/Contact/BAO/RelationshipType.php';


/**
 * Add or update a relationship
 *
 * @param  array   $params   (reference ) input parameters
 *
 * @return array (reference) id of created or updated record
 * @static void
 * @access public
 */
function civicrm_relationship_create( &$params ) {
    _civicrm_initialize( );

    if ( empty( $params ) ) { 
        return civicrm_create_error( 'No input parameter present' );
    }
    
    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameter is not an array' ) );
    }
    
    if( ! isset( $params['contact_id_a'] ) &&
        ! isset( $params['contact_id_b'] ) &&
        ! isset( $params['relationship_type_id'] )) { 
        
        return civicrm_create_error( ts('Missing required parameters'));
    }
   
    $values = array( );
    require_once 'CRM/Contact/BAO/Relationship.php';
    $error = _civicrm_relationship_format_params( $params, $values );
    
    if ( civicrm_error( $error ) ) {
        return $error;
    }
    
    $ids = array( );
    require_once 'CRM/Utils/Array.php';
    
    if( CRM_Utils_Array::value( 'id', $params ) ) {
        $ids['relationship']  = $params['id'];
        $ids['contactTarget'] = $params['contact_id_b'];
    }
       
    $values['relationship_type_id'] = $params['relationship_type_id'].'_a_b';
    $values['contact_check']        = array ( $params['contact_id_b'] => $params['contact_id_b'] );
    $ids   ['contact'      ]        = $params['contact_id_a'];
    
    $relationshipBAO = CRM_Contact_BAO_Relationship::create( $values, $ids );

    if ( is_a( $relationshipBAO, 'CRM_Core_Error' ) ) {
        return civicrm_create_error( "Relationship can not be created" );
    } else if ( $relationshipBAO[1] ) {
        return civicrm_create_error( "Relationship is not valid" );
    } else if ( $relationshipBAO[2] ) {
        return civicrm_create_error( "Relationship already exist" );
    }

    return civicrm_create_success( array( 'id' => implode( ",", $relationshipBAO[4] ) ) );
}


/**
 * Delete a relationship 
 *
 * @param  id of relationship  $id
 *
 * @return boolean  true if success, else false
 * @static void
 * @access public
 */

function civicrm_relationship_delete( &$params ) {
     
    if ( empty( $params ) ) { 
        return civicrm_create_error( 'No input parameter present' );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameter is not an array' ) );
    }
        
    if( ! CRM_Utils_Array::value( 'id',$params )  ) {
        return civicrm_create_error( 'Missing required parameter' );
    }
    require_once 'CRM/Utils/Rule.php';
    if( $params['id'] != null && ! CRM_Utils_Rule::integer( $params['id'] ) ) {
        return civicrm_create_error( 'Invalid value for relationship ID' );
    }
    
    $relationBAO = new CRM_Contact_BAO_Relationship( );
    return $relationBAO->del( $params['id'] ) ? civicrm_create_success( ts( 'Deleted relationship successfully' ) ):civicrm_create_error( ts( 'Could not delete relationship' ) );

}

/**
 * Function to update relationship
 *
 * @param  array $params   Associative array of property name/value pairs to update the relationship
 *
 * @return array Array with relationship information
 *
 * @access public
 *
 */
function civicrm_relationship_update( $params ) {
    return civicrm_relationship_create( $params );
}


/**
 * Function to get the relationship
 *
 * @param array   $params          (reference ) input parameters 
         param['contact_id'] is mandatory
 * @return        Array of all relationship.
 *
 * @access  public
 */
function civicrm_relationship_get( $params ) {
    if ( !isset( $params['contact_id'] ) ) {
        return civicrm_create_error( ts( 'Could not find contact_id in input parameters.' ) );
    }

    return civicrm_contact_relationship_get( $params );
}

/**
 * backward compatibility function to match broken naming convention in v2.2.1 and prior
 */
function civicrm_get_relationships( $contact_a, $contact_b = null, $relationshipTypes = null, $sort = null ) {
    return civicrm_contact_relationship_get( $contact_a, $contact_b, $relationshipTypes, $sort );
}

/**
 * Function to get the relationship
 *
 * @param array   $contact_a          (reference ) input parameters.
 * @param array   $contact_b          (reference ) input parameters.
 * @param array   $relationshipTypes  an array of Relationship Type Name.
 * @param string  $sort               sort all relationship by relationshipId (eg asc/desc)
 *
 * @return        Array of all relationship.
 *
 * @access  public
 */
function civicrm_contact_relationship_get( $contact_a, $contact_b = null, $relationshipTypes = null, $sort = null ) 
{
    if ( ! is_array( $contact_a ) ) {
        return civicrm_create_error( ts( 'Input parameter is not an array' ) );
    }
    
    if ( !isset( $contact_a['contact_id'] ) ) {
        return civicrm_create_error( ts( 'Could not find contact_id in input parameters.' ) );
    }
    require_once 'CRM/Contact/BAO/Relationship.php';
    $contactID     = $contact_a['contact_id'];
    $relationships = CRM_Contact_BAO_Relationship::getRelationship($contactID);
    
    if ( !empty( $relationshipTypes ) ) {
        $result = array();
        foreach ( $relationshipTypes as $relationshipName ) {
            foreach( $relationships as $key => $relationship ) {
                if ( $relationship['relation'] ==  $relationshipName ) {
                    $result[$key] = $relationship;
                }
            }
        }
        $relationships = $result;
    }
    
    if( isset( $contact_b['contact_id']) ) {
        $cid = $contact_b['contact_id'];
        $result = array( );
        
        foreach($relationships as $key => $relationship) {
            if ($relationship['cid'] == $cid ) {
                $result[$key] = $relationship;
            }
        }
        $relationships = $result;
    }
    
    //sort by relationship id
    if ( $sort ) {
        if ( strtolower( $sort ) == 'asc' ) {
            ksort( $relationships );
        } 
        else if ( strtolower( $sort ) == 'desc' ) {
            krsort( $relationships );
        }
    }
    
    //handle custom data.
    require_once 'CRM/Core/BAO/CustomGroup.php';

    foreach ( $relationships as $relationshipId => $values ) {
        $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Relationship', CRM_Core_DAO::$_nullObject, $relationshipId, false,
                                                         $values['civicrm_relationship_type_id'] );
        $formatTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, CRM_Core_DAO::$_nullObject );
        
        $defaults = array( );
        CRM_Core_BAO_CustomGroup::setDefaults( $formatTree, $defaults );
        
        if ( !empty( $defaults ) ) {
            foreach ( $defaults as $key => $val ) {
                $relationships[$relationshipId][$key] = $val;
            }
        }
    }
    
    if ( $relationships ) {
        return civicrm_create_success( $relationships );
    } else {
        return civicrm_create_error( ts( 'Invalid Data' ) );
    }
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
function _civicrm_relationship_format_params( &$params, &$values ) {
    // copy all the relationship fields as is
   
    $fields =& CRM_Contact_DAO_Relationship::fields( );
    _civicrm_store_values( $fields, $params, $values );
    
    foreach ($params as $key => $value) {
        // ignore empty values or empty arrays etc
        require_once 'CRM/Utils/System.php';
        if ( CRM_Utils_System::isNull( $value ) ) {
            continue;
        }
        
        switch ($key) {
        case 'contact_id_a':
        case 'contact_id_b':
            require_once 'CRM/Utils/Rule.php';
            if (!CRM_Utils_Rule::integer($value)) {
                return civicrm_create_error("contact_id not valid: $value");
            }
            $dao = new CRM_Core_DAO();
            $qParams = array();
            $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",
                                          $qParams);
            if (!$svq) {
                return civicrm_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
            }
            break;
            
        case 'start_date':
        case 'end_date':
            if (!CRM_Utils_Rule::qfDate($value)) {
                return civicrm_create_error("$key not a valid date: $value");
            }
            break;
            
        case 'relationship_type_id':            
            $relationTypes = CRM_Core_PseudoConstant::relationshipType( );
            if (!array_key_exists($value, $relationTypes)) {
                return civicrm_create_error("$key not a valid: $value");
            } 
            $relation = $relationTypes[$params['relationship_type_id']];
            require_once 'CRM/Contact/BAO/Contact.php';
            if ($relation['contact_type_a'] && 
                $relation['contact_type_a'] != CRM_Contact_BAO_Contact::getContactType($params['contact_id_a'])) {
                return civicrm_create_error("Contact ID :{$params['contact_id_a']} is not of contact type {$relation['contact_type_a']}");
            }
            if ($relation['contact_type_b'] && 
                $relation['contact_type_b'] != CRM_Contact_BAO_Contact::getContactType($params['contact_id_b'])) {
                return civicrm_create_error("Contact ID :{$params['contact_id_b']} is not of contact type {$relation['contact_type_b']}");
            }
            break;
              
        default:
            break;
        }
    }
    
    if ( array_key_exists( 'note', $params ) ) {
        $values['note'] = $params['note'];
    }
    _civicrm_custom_format_params( $params, $values, 'Relationship' );
    
    return array();
}

