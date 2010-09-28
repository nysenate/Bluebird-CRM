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
 * Definition of CRM API for Membership.
 * More detailed documentation can be found 
 * {@link http://objectledge.org/confluence/display/CRM/CRM+v1.0+Public+APIs
 * here}
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * Files required for this package
 */
require_once 'api/utils.php';

/**
 * Create a Membership Type
 *  
 * This API is used for creating a Membership Type
 * 
 * @param   array  $params  an associative array of name/value property values of civicrm_membership_type
 * 
 * @return array of newly created membership type property values.
 * @access public
 */
function crm_create_membership_type($params) 
{
    _crm_initialize();
    if ( ! is_array($params) ) {
        return _crm_error('Params is not an array.');
    }
    
    if ( ! isset( $params['name'] ) ||
         ! isset( $params['duration_unit'] ) ||
         ! isset( $params['duration_interval'] ) ) {
        return _crm_error('Missing require fileds ( name, duration unit,duration interval)');
    }
    
    $error = _crm_check_required_fields( $params, 'CRM_Member_DAO_MembershipType');
    if ( is_a($error, 'CRM_Core_Error')  ) {
        return $error;
    }
    
    $ids['membershipType']   = CRM_Utils_Array::value( 'id', $params );
    $ids['memberOfContact']  = CRM_Utils_Array::value( 'member_of_contact_id', $params );
    $ids['contributionType'] = CRM_Utils_Array::value( 'contribution_type_id', $params );
    
    require_once 'CRM/Member/BAO/MembershipType.php';
    $membershipTypeBAO = CRM_Member_BAO_MembershipType::add($params, $ids);
    
    $membershipType = array();
    _crm_object_to_array($membershipTypeBAO, $membershipType);
    
    return $membershipType;
}

/**
 * Get a Membership Type.
 * 
 * This api is used for finding an existing membership type.
 * Required parameters : id of membership type
 * 
 * @param  array $params  an associative array of name/value property values of civicrm_membership_type
 * 
 * @return  Array of all found membership type property values.
 * @access public
 */
function crm_get_membership_types($params) 
{
    _crm_initialize();
    if ( ! is_array($params) ) {
        return _crm_error('Params is not an array.');
    }
    
    if ( ! isset($params['id'])) {
        return _crm_error('Required parameters missing.');
    }
    
    require_once 'CRM/Member/BAO/MembershipType.php';
    $membershipTypeBAO = new CRM_Member_BAO_MembershipType();
    
    $properties = array_keys($membershipTypeBAO->fields());
    
    foreach ($properties as $name) {
        if (array_key_exists($name, $params)) {
            $membershipTypeBAO->$name = $params[$name];
        }
    }
    
    if ( $membershipTypeBAO->find() ) {
        $membershipType = array();
        while ( $membershipTypeBAO->fetch() ) {
            _crm_object_to_array( clone($membershipTypeBAO), $membershipType );
            $membershipTypes[$membershipTypeBAO->id] = $membershipType;
        }
    } else {
        return _crm_error('Exact match not found');
    }
    return $membershipTypes;
}

/**
 * Update an existing membership type
 *
 * This api is used for updating an existing membership type.
 * Required parrmeters : id of a membership type
 * 
 * @param  Array   $params  an associative array of name/value property values of civicrm_membership_type
 * 
 * @return array of updated membership type property values
 * @access public
 */
function &crm_update_membership_type( $params ) {
    if ( !is_array( $params ) ) {
        return _crm_error( 'Params is not an array' );
    }
    
    if ( !isset($params['id']) ) {
        return _crm_error( 'Required parameter missing' );
    }
    
    require_once 'CRM/Member/BAO/MembershipType.php';
    $membershipTypeBAO =& new CRM_Member_BAO_MembershipType( );
    $membershipTypeBAO->id = $params['id'];
    if ($membershipTypeBAO->find(true)) {
        $fields = $membershipTypeBAO->fields( );
        foreach ( $fields as $name => $field) {
            if (array_key_exists($name, $params)) {
                $membershipTypeBAO->$name = $params[$name];
            }
        }
        $membershipTypeBAO->save();
    }
    
    $membershipType = array();
    _crm_object_to_array( $membershipTypeBAO, $membershipType );
    $membershipTypeBAO->free( );
    return $membershipType;
}

/**
 * Deletes an existing membership type
 * 
 * This API is used for deleting a membership type
 * 
 * @param  Int  $membershipTypeID    ID of membership type to be deleted
 * 
 * @return null if successfull, object of CRM_Core_Error otherwise
 * @access public
 */
function &crm_delete_membership_type( $membershipTypeID ) {
    if ( ! $membershipTypeID ) {
        return _crm_error( 'Invalid value for membershipTypeID' );
    }
    require_once 'CRM/Member/BAO/MembershipType.php';
    return CRM_Member_BAO_MembershipType::del($membershipTypeID);
}

/**
 * Create a Membership Status
 *  
 * This API is used for creating a Membership Status
 * 
 * @param   array  $params  an associative array of name/value property values of civicrm_membership_status
 * @return array of newly created membership status property values.
 * @access public
 */
function crm_create_membership_status($params) 
{
    _crm_initialize();
    if ( ! is_array($params) ) {
        return _crm_error('Params is not an array.');
    }
    
    if ( empty($params) ) {
        return _crm_error('Params can not be empty.');
    }
    
    if (! $params["name"] ) {
        return _crm_error('Missing require fileds');
    }
    
    require_once 'CRM/Member/BAO/MembershipStatus.php';
    $ids = array();
    $membershipStatusBAO = CRM_Member_BAO_MembershipStatus::add($params, $ids);
    $membershipStatus = array();
    _crm_object_to_array($membershipStatusBAO, $membershipStatus);
    
    return $membershipStatus;
}

/**
 * Get a membership status.
 * 
 * This api is used for finding an existing membership status.
 * Required parrmeters : id of a membership status
 * 
 * @param  array $params  an associative array of name/value property values of civicrm_membership_status
 *
 * @return  Array of all found membership status property values.
 * @access public
 */
function crm_get_membership_statuses($params) 
{
    _crm_initialize();
    if ( ! is_array($params) ) {
        return _crm_error('Params is not an array.');
    }
    
    if ( ! isset($params['id'])) {
        return _crm_error('Required parameters missing.');
    }
    
    require_once 'CRM/Member/BAO/MembershipStatus.php';
    $membershipStatusBAO = new CRM_Member_BAO_MembershipStatus();
    
    $properties = array_keys($membershipStatusBAO->fields());
    
    foreach ($properties as $name) {
        if (array_key_exists($name, $params)) {
            $membershipStatusBAO->$name = $params[$name];
        }
    }
    
    if ( $membershipStatusBAO->find() ) {
        $membershipStatus = array();
        while ( $membershipStatusBAO->fetch() ) {
            _crm_object_to_array( clone($membershipStatusBAO), $membershipStatus );
            $membershipStatuses[$membershipStatusBAO->id] = $membershipStatus;
        }
    } else {
        return _crm_error('Exact match not found');
    }
    return $membershipStatuses;
}

/**
 * Update an existing membership status
 *
 * This api is used for updating an existing membership status.
 * Required parrmeters : id of a membership status
 * 
 * @param  Array   $params  an associative array of name/value property values of civicrm_membership_status
 * 
 * @return array of updated membership status property values
 * @access public
 */
function &crm_update_membership_status( $params ) 
{
    _crm_initialize();
    if ( !is_array( $params ) ) {
        return _crm_error( 'Params is not an array' );
    }
    
    if ( !isset($params['id']) ) {
        return _crm_error( 'Required parameter missing' );
    }
    
    require_once 'CRM/Member/BAO/MembershipStatus.php';
    $membershipStatusBAO =& new CRM_Member_BAO_MembershipStatus( );
    $membershipStatusBAO->id = $params['id'];
    if ($membershipStatusBAO->find(true)) {
        $fields = $membershipStatusBAO->fields( );
        foreach ( $fields as $name => $field) {
            if (array_key_exists($name, $params)) {
                $membershipStatusBAO->$name = $params[$name];
            }
        }
        $membershipStatusBAO->save();
    }
    $membershipStatus = array();
    _crm_object_to_array( clone($membershipStatusBAO), $membershipStatus );
    return $membershipStatus;
}

/**
 * Deletes an existing membership status
 * 
 * This API is used for deleting a membership status
 * 
 * @param  Int  $membershipStatusID   Id of the membership status to be deleted
 * 
 * @return null if successfull, object of CRM_Core_Error otherwise
 * @access public
 */
function &crm_delete_membership_status( $membershipStatusID ) 
{
    _crm_initialize();
    if ( empty($membershipStatusID) ) {
        return _crm_error( 'Invalid value for membershipStatusID' );
    }
    
    require_once 'CRM/Member/BAO/MembershipStatus.php';
    CRM_Member_BAO_MembershipStatus::del($membershipStatusID);
}

/**
 * Create a Contct Membership
 *  
 * This API is used for creating a Membership for a contact.
 * Required parameters : membership_type_id and status_id.
 * 
 * @param   array  $params     an associative array of name/value property values of civicrm_membership
 * @param   int    $contactID  ID of a contact
 * 
 * @return array of newly created membership property values.
 * @access public
 */
function crm_create_contact_membership($params, $contactID)
{
    _crm_initialize();
    if ( !is_array( $params ) ) {
        return _crm_error( 'Params is not an array' );
    }
   
    if ( !isset($params['membership_type_id']) || !isset($params['status_id']) || empty($contactID)) {
        return _crm_error( 'Required parameter missing' );
    }
    
    $values  = array( );   
    $error = _crm_format_membership_params( $params, $values );
    if (is_a($error, 'CRM_Core_Error') ) {
        return $error;
    }
    $params = array_merge($values,$params);
    $params['contact_id'] = $contactID;
    
    require_once 'CRM/Member/BAO/Membership.php';
    $ids = array();
    $membershipBAO = CRM_Member_BAO_Membership::create($params, $ids);
  
    if ( ! is_a( $membershipBAO, 'CRM_Core_Error') ) {
        $relatedContacts = CRM_Member_BAO_Membership::checkMembershipRelationship( 
                                                            $membershipBAO->id,
                                                            $contactID,
                                                            CRM_Core_Action::ADD
                                                            );
    }
    
    foreach ( $relatedContacts as $contactId => $status ) {
        $params['contact_id'         ] = $contactId;
        $params['owner_membership_id'] = $membershipBAO->id;
        unset( $params['id'] );
       
        CRM_Member_BAO_Membership::create( $params, CRM_Core_DAO::$_nullArray );
    }
    
    $membership = array();
    _crm_object_to_array($membershipBAO, $membership);
    return $membership;
}

/**
 * Update an existing contact membership
 *
 * This api is used for updating an existing contact membership.
 * Required parrmeters : id of a membership
 * 
 * @param  Array   $params  an associative array of name/value property values of civicrm_membership
 * 
 * @return array of updated membership property values
 * @access public
 */
function crm_update_contact_membership($params)
{  
    _crm_initialize();
    if ( !is_array( $params ) ) {
        return _crm_error( 'Params is not an array' );
    }
    
    if ( !isset($params['id']) ) {
        return _crm_error( 'Required parameter missing' );
    }
    
    $changeFields = array(
                          'membership_start_date' => 'start_date',
                          'membership_end_date'   => 'end_date',
                          'membership_source'     => 'source'
                          );
    
    foreach ( $changeFields as $field => $requiredField ) {
        if ( array_key_exists( $field, $params ) ) {
            $params[$requiredField] = $params[$field];
            unset($params[$field]);
        }
    }
    
    require_once 'CRM/Member/BAO/Membership.php';
    $membershipBAO     =& new CRM_Member_BAO_Membership( );
    $membershipBAO->id = $params['id'];
    $membershipBAO->find(true);

    $oldStatusID = $membershipBAO->status_id;

    $membershipBAO->copyValues($params);
    
    $datefields = array( 'start_date', 'end_date', 'join_date', 'reminder_date' );
    
    //fix the dates 
    foreach ( $datefields as $value ) {
        $membershipBAO->$value  = CRM_Utils_Date::customFormat($membershipBAO->$value,'%Y%m%d');
        // Handle resetting date to 'null' (which is converted to 00000 by customFormat)
        if ( $membershipBAO->$value == '00000') {
            $membershipBAO->$value = 'null';
        }
        $params[$value] = $membershipBAO->$value;
    }
    
    $membershipBAO->save();
    require_once "CRM/Core/Action.php";
    // Check and add membership for related contacts
    $relatedContacts =
        CRM_Member_BAO_Membership::checkMembershipRelationship( 
                                                               $membershipBAO->id,
                                                               (int) $membershipBAO->contact_id,
                                                               CRM_Core_Action::UPDATE
                                                               );
    
    //delete all the related membership records before creating
    CRM_Member_BAO_Membership::deleteRelatedMemberships( $membershipBAO->id );
    
    $params['membership_type_id'] = $membershipBAO->membership_type_id;
 
    foreach ( $relatedContacts as $contactId => $relationshipStatus ) {
        if ( $relationshipStatus & CRM_Contact_BAO_Relationship::CURRENT ) {
            $params['contact_id'         ] = $contactId;
            $params['owner_membership_id'] = $membershipBAO->id;
            unset( $params['id'] );
           
            CRM_Member_BAO_Membership::create( $params, CRM_Core_DAO::$_nullArray );
        }
    }
   
    // Create activity history record.
    require_once "CRM/Member/PseudoConstant.php";
    $membershipType = CRM_Member_PseudoConstant::membershipType( $membershipBAO->membership_type_id );
    
    if ( ! $membershipType ) {
        $membershipType = ts('Membership');
    }

    $activitySummary = $membershipType;
    
    if ( $membershipBAO->source != 'null' ) {
        $activitySummary .= " - {$membershipBAO->source}";
    }
    
    if ( $membershipBAO->owner_membership_id ) {
        $cid         = CRM_Core_DAO::getFieldValue(
                                                   'CRM_Member_DAO_Membership',
                                                   $membershipBAO->owner_membership_id,
                                                   'contact_id' );
        $displayName = CRM_Core_DAO::getFieldValue(
                                                   'CRM_Contact_DAO_Contact',
                                                   $cid, 'display_name' );
        
        $activitySummary .= " (by $displayName)";
        
    }
    
    // create activity record only if there is change in the statusID (CRM-2521).
    if ( $oldStatusID != $membershipBAO->status_id ) {
        $activityParams = array( 'source_contact_id'  => $membershipBAO->contact_id,
                                 'source_record_id'   => $membershipBAO->id,
                                 'activity_type_id'   => array_search('Membership Signup', CRM_Core_PseudoConstant::activityType()),
                                 'subject'            => $activitySummary,
                                 'activity_date_time' => $params['join_date'],
                                 'is_test'            => $membershipBAO->is_test,
                                 'status_id'          => 2
                                 );
        
        require_once 'api/v2/Activity.php';
        if ( is_a( civicrm_activity_create( $activityParams ), 'CRM_Core_Error' ) ) {
            return false;
        }
    }

    $membership = array();
    _crm_object_to_array( $membershipBAO, $membership );
    $membershipBAO->free( );
    return $membership;
}

/**
 * Get conatct membership record.
 * 
 * This api is used for finding an existing membership record.
 * This api will also return the mebership records for the contacts
 * having mebership based on the relationship with the direct members.
 * 
 * @param  Int  $contactID  ID of a contact
 *
 * @return  Array of all found membership property values.
 * @access public
 */
function crm_get_contact_memberships($contactID)
{
    _crm_initialize();
    if ( empty($contactID) ) {
        return _crm_error( 'Invalid value for ContactID.' );
    }
    
    // get the membership for the given contact ID
    require_once 'CRM/Member/BAO/Membership.php';
    $membership = array('contact_id' => $contactID);
    $membershipValues = array();
    CRM_Member_BAO_Membership::getValues($membership, $membershipValues);
    
    if ( empty( $membershipValues ) ) {
        return _crm_error('No memberships for this contact.');
    }
    
    foreach ($membershipValues as $membershipId => $values) {
        // populate the membership type name for the membership type id
        require_once 'CRM/Member/BAO/MembershipType.php';
        $membershipType = CRM_Member_BAO_MembershipType::getMembershipTypeDetails($values['membership_type_id']);
        
        $membershipValues[$membershipId]['membership_name'] = $membershipType['name'];
        
        $relationships[$membershipType['relationship_type_id']] = $membershipId;
        
        // populating relationship type name.
        require_once 'CRM/Contact/BAO/RelationshipType.php';
        $relationshipType = new CRM_Contact_BAO_RelationshipType();
        $relationshipType->id = $membershipType['relationship_type_id'];
        if ( $relationshipType->find(true) ) {
            $membershipValues[$membershipId]['relationship_name'] = $relationshipType->name_a_b;
        }
    }
    
    $members[$contactID] = $membershipValues;
    
    // populating contacts in members array based on their relationship with direct members.
    require_once 'CRM/Contact/BAO/Relationship.php';
    foreach ($relationships as $relTypeId => $membershipId) {
        // As members are not direct members, there should not be
        // membership id in the result array.
        unset($membershipValues[$membershipId]['id']);
        $relationship = new CRM_Contact_BAO_Relationship();
        $relationship->contact_id_b            = $contactID;
        $relationship->relationship_type_id    = $relTypeId;
        if ($relationship->find()) {
            while ($relationship->fetch()) {
                clone($relationship);
                $membershipValues[$membershipId]['contact_id'] = $relationship->contact_id_a;
                $members[$contactID][$relationship->contact_id_a] = $membershipValues[$membershipId];
            }
        }
    }
    return $members;
    
}

/**
 * Deletes an existing contact membership
 * 
 * This API is used for deleting a contact membership
 * 
 * @param  Int  $membershipID   Id of the contact membership to be deleted
 * 
 * @return null if successfull, object of CRM_Core_Error otherwise
 * @access public
 */
function crm_delete_membership($membershipID)
{
    _crm_initialize();
    
    if (empty($membershipID)) {
        return _crm_error('Invalid value for membershipID');
    }
    
    require_once 'CRM/Member/BAO/Membership.php';
    CRM_Member_BAO_Membership::deleteRelatedMemberships( $membershipID );
    
    $membership = new CRM_Member_BAO_Membership();
    $result = $membership->deleteMembership($membershipID);
    
    return $result ? null : _crm_error('Error while deleting Membership');
}

/**
 * Derives the Membership Status of a given Membership Reocrd
 * 
 * This API is used for deriving Membership Status of a given Membership 
 * record using the rules encoded in the membership_status table.
 * 
 * @param  Int     $membershipID  Id of a membership
 * @param  String  $statusDate    
 * 
 * @return Array  Array of status id and status name 
 * @public
 */
function crm_calc_membership_status( $membershipID )
{
    if ( empty( $membershipID ) ) {
        return _crm_error( 'Invalid value for membershipID' );
    }

    $query = "
SELECT start_date, end_date, join_date
  FROM civicrm_membership
 WHERE id = %1
";
    $params = array( 1 => array( $membershipID, 'Integer' ) );
    $dao =& CRM_Core_DAO::executeQuery( $query, $params );
    if ( $dao->fetch( ) ) {
        require_once 'CRM/Member/BAO/MembershipStatus.php';
        $result =&
            CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( $dao->start_date,
                                                                        $dao->end_date,
                                                                        $dao->join_date );
    } else {
        $result = null;
    }
    $dao->free( );
    return $result;
}


