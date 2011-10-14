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
 * File for the CiviCRM APIv3 membership type functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Membership
 * 
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: MembershipType.php 30171 2010-10-14 09:11:27Z mover $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Member/BAO/MembershipType.php';

/**
 * API to Create or update a Membership Type
 *  
 * @param   array  $params  an associative array of name/value property values of civicrm_membership_type
 * @return array $result newly created or updated membership type property values.
 * @access public
 */
function civicrm_api3_membership_type_create($params) 
{

    $values = $params;
    civicrm_api3_verify_mandatory($values,'CRM_Member_DAO_MembershipType' ,array('name',  'duration_unit','duration_interval'));
     
    $ids['membershipType']   = CRM_Utils_Array::value( 'id', $values );
    $ids['memberOfContact']  = CRM_Utils_Array::value( 'member_of_contact_id', $values );
    $ids['contributionType'] = CRM_Utils_Array::value( 'contribution_type_id', $values );

    require_once 'CRM/Member/BAO/MembershipType.php';
    $membershipTypeBAO = CRM_Member_BAO_MembershipType::add($values, $ids);
    $membershipType = array();    
    _civicrm_api3_object_to_array( $membershipTypeBAO, $membershipType[$membershipTypeBAO->id] );
    $membershipTypeBAO->free();
    return civicrm_api3_create_success($membershipType,$params);

}

/**
 * Get a Membership Type.
 * 
 * This api is used for finding an existing membership type.
 * 
 * @param  array $params  an associative array of name/value property values of civicrm_membership_type
 * 
 * @return  Array of all found membership type property values.
 * @access public
 */
function civicrm_api3_membership_type_get($params) 
{

    civicrm_api3_verify_mandatory($params);
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Deletes an existing membership type
 * 
 * This API is used for deleting a membership type
 * Required parrmeters : id of a membership type
 * 
 * @param  Array   $params  an associative array of name/value property values of civicrm_membership_type
 * 
 * @return boolean        true if success, else false
 * @access public
 */
function civicrm_api3_membership_type_delete( $params ) {

    civicrm_api3_verify_mandatory($params,null,array('id'));

    require_once 'CRM/Member/BAO/MembershipType.php';
    $memberDelete = CRM_Member_BAO_MembershipType::del( $params['id'] , 1);
    return $memberDelete ?
    civicrm_api3_create_success(  $memberDelete ) :
    civicrm_api3_create_error('Error while deleting membership type. id : ' . $params['id']);

}