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
 *
 * File for the CiviCRM APIv3 membership contact functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Membership
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: MembershipContact.php 30171 2010-10-14 09:11:27Z mover $
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Utils/Rule.php';
require_once 'CRM/Utils/Array.php';

/**
 * Deletes an existing contact membership
 *
 * This API is used for deleting a contact membership
 *
 * @param  $params array  array holding membership_id - Id of the contact membership to be deleted
 * @todo should this really return null if successful - should be array
 * @return null if successfull, object of CRM_Core_Error otherwise
 * @access public
 */
function civicrm_api3_membership_delete($params)
{
   
    civicrm_api3_verify_one_mandatory($params,null,array('id','membership_id'));
    $membershipID = empty($params['id']) ?$params['membership_id'] :$params['id'];


    // membershipID should be numeric
    if ( ! is_numeric( $membershipID) ) {
      return civicrm_api3_create_error( 'Input parameter should be numeric' );
    }

    require_once 'CRM/Member/BAO/Membership.php';
    CRM_Member_BAO_Membership::deleteRelatedMemberships( $membershipID );

    $membership = new CRM_Member_BAO_Membership();
    $result = $membership->deleteMembership($membershipID);

    return $result ? civicrm_api3_create_success( ) : civicrm_api3_create_error('Error while deleting Membership');

}



/**
 * Create a Contact Membership
 *
 * This API is used for creating a Membership for a contact.
 * Required parameters : membership_type_id and status_id.
 *
 * @param   array  $params     an associative array of name/value property values of civicrm_membership
 *
 * @return array of newly created membership property values.
 * @access public
 */
function civicrm_api3_membership_create($params)
{



    $error = _civicrm_api3_membership_check_params( $params );
    if ( civicrm_api3_error( $error ) ) {
      return $error;
    }

    $values  = array( );
    $error = _civicrm_api3_membership_format_params( $params, $values );
    if ( civicrm_api3_error( $error ) ) {
      return $error;
    }

    $params = array_merge( $params, $values );

    require_once 'CRM/Core/Action.php';
    $action = CRM_Core_Action::ADD;
    // we need user id during add mode
    $ids = array ( 'userId' => $params['contact_id'] );

    //for edit membership id should be present
    if ( CRM_Utils_Array::value( 'id', $params ) ) {
      $ids = array( 'membership' => $params['id'],
                      'userId'     => $params['contact_id'] );
      $action = CRM_Core_Action::UPDATE;
    }

    //need to pass action to handle related memberships.
    $params['action'] = $action;

    require_once 'CRM/Member/BAO/Membership.php';
    $membershipBAO = CRM_Member_BAO_Membership::create($params, $ids, true);
    
    if ( array_key_exists( 'is_error', $membershipBAO ) ) {
      // In case of no valid status for given dates, $membershipBAO
      // is going to contain 'is_error' => "Error Message"
      return civicrm_api3_create_error( ts( 'The membership can not be saved, no valid membership status for given dates' ) );
    }

    $membership = array();
    _civicrm_api3_object_to_array($membershipBAO, $membership[$membershipBAO->id]);

    return civicrm_api3_create_success($membership , $params,'membership','create', $membershipBAO);

}

/**
 * Get contact membership record.
 *
 * This api will return the membership records for the contacts
 * having membership based on the relationship with the direct members.
 *
 * @param  Array $params key/value pairs for contact_id and some
 *          options affecting the desired results; has legacy support
 *          for just passing the contact_id itself as the argument
 *
 * @return  Array of all found membership property values.
 * @access public
 * @todo needs some love - basically only a get for a given contact right now
 */
function civicrm_api3_membership_get($params)
{

    civicrm_api3_verify_mandatory($params);

    $contactID = $activeOnly = $membershipTypeId = $membershipType = null;
   
      $contactID        = CRM_Utils_Array::value( 'contact_id', $params );
      if(!empty($params['filters']) && is_array($params['filters'])){
        $activeOnly       = CRM_Utils_Array::value( 'is_current', $params['filters'], false );
      }
      $activeOnly       = CRM_Utils_Array::value( 'active_only', $params, $activeOnly );

      $membershipTypeId = CRM_Utils_Array::value( 'membership_type_id', $params );
      if ( !$membershipTypeId ) {
        $membershipType = CRM_Utils_Array::value( 'membership_type', $params );
        if ( $membershipType ) {
          require_once 'CRM/Member/DAO/MembershipType.php';
          $membershipTypeId =
          CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType',
          $membershipType, 'id', 'name' );
        }
      }


    // get the membership for the given contact ID
    require_once 'CRM/Member/BAO/Membership.php';
    $membershipParams = array( 'contact_id' => $contactID );
    if ( $membershipTypeId ) {
      $membershipParams['membership_type_id'] = $membershipTypeId;
    }
    $membershipValues = array();
    CRM_Member_BAO_Membership::getValues( $membershipParams, $membershipValues, $activeOnly );
    if(empty($params['contact_id'])){
      //added this as contact_id was the only acceptable field so this was a quick way to improve
        $membershipValues = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, FALSE);
    }
    

    if ( empty( $membershipValues ) ) {
      # No results is NOT an error!
      return civicrm_api3_create_success($membershipValues,$params);
    }

    $relationships       = array();
    foreach ($membershipValues as $membershipId => $values) {
      // populate the membership type name for the membership type id
      require_once 'CRM/Member/BAO/MembershipType.php';
      $membershipType = CRM_Member_BAO_MembershipType::getMembershipTypeDetails($values['membership_type_id']);

      $membershipValues[$membershipId]['membership_name'] = $membershipType['name'];

      if ( CRM_Utils_Array::value( 'relationship_type_id', $membershipType ) ) {
        $relationships[$membershipType['relationship_type_id']] = $membershipId;
      }

      // populating relationship type name.
      require_once 'CRM/Contact/BAO/RelationshipType.php';
      $relationshipType = new CRM_Contact_BAO_RelationshipType();
      $relationshipType->id = CRM_Utils_Array::value( 'relationship_type_id', $membershipType );
      if ( $relationshipType->find(true) ) {
        $membershipValues[$membershipId]['relationship_name'] = $relationshipType->name_a_b;
      }
      
      _civicrm_api3_custom_data_get($membershipValues[$membershipId],'Membership',$membershipId,null,$values['membership_type_id']);

    }

    $members = $membershipValues;

    // populating contacts in members array based on their relationship with direct members.
    require_once 'CRM/Contact/BAO/Relationship.php';
    if ( !empty( $relationships ) ) {
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
            $membershipValues[$membershipId]['contact_id']    = $relationship->contact_id_a;
            $members[$membershipId]['related_contact_id'] = $relationship->contact_id_a;
          }
        }

      }
    }
    
    return civicrm_api3_create_success($members,$params, 'membership','get');

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
 * @return array|error
 * @access public
 */
function _civicrm_api3_membership_format_params( $params, &$values, $create=false)
{
  require_once "CRM/Member/DAO/Membership.php";
  require_once "CRM/Member/PseudoConstant.php";
  $fields =& CRM_Member_DAO_Membership::fields( );
  _civicrm_api3_store_values( $fields, $params, $values );

  foreach ($params as $key => $value) {
    // ignore empty values or empty arrays etc
    if ( CRM_Utils_System::isNull( $value ) ) {
      continue;
    }
     
    switch ($key) {
      case 'membership_contact_id':
        if (!CRM_Utils_Rule::integer($value)) {
          return civicrm_api3_create_error("contact_id not valid: $value");
        }
        $dao = new CRM_Core_DAO();
        $qParams = array();
        $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",
        $qParams);
        if (!$svq) {
          return civicrm_api3_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
        }
        $values['contact_id'] = $values['membership_contact_id'];
        unset($values['membership_contact_id']);
        break;

      case 'membership_type_id':
        if ( !CRM_Utils_Array::value( $value, CRM_Member_PseudoConstant::membershipType( ) ) ) {
          return civicrm_api3_create_error( 'Invalid Membership Type Id' );
        }
        $values[$key] = $value;
        break;
      case 'membership_type':
        $membershipTypeId = CRM_Utils_Array::key( ucfirst( $value ),
        CRM_Member_PseudoConstant::membershipType( ) );
        if ( $membershipTypeId ) {
          if ( CRM_Utils_Array::value( 'membership_type_id', $values ) &&
          $membershipTypeId != $values['membership_type_id'] ) {
            return civicrm_api3_create_error( 'Mismatched membership Type and Membership Type Id' );
          }
        } else {
          return civicrm_api3_create_error( 'Invalid Membership Type' );
        }
        $values['membership_type_id'] = $membershipTypeId;
        break;
      case 'status_id':
        if ( !CRM_Utils_Array::value( $value, CRM_Member_PseudoConstant::membershipStatus( ) ) ) {
          return civicrm_api3_create_error( 'Invalid Membership Status Id' );
        }
        $values[$key] = $value;
        break;
      default:
        break;
    }
  }

  _civicrm_api3_custom_format_params( $params, $values, 'Membership' );


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

/**
 * This function ensures that we have the right input membership parameters
 *
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new membership.
 *
 * @return bool|CRM_Utils_Error
 * @access private
 */
function _civicrm_api3_membership_check_params( &$params ) {

  civicrm_api3_verify_mandatory($params,null,array('contact_id',array('membership_type_id','membership_type')));

  $valid = true;
  $error = '';

  // check params for membership id during update
  if ( CRM_Utils_Array::value( 'id', $params ) ) {
    //don't calculate dates on exisiting membership - expect API use to pass them in
    // or leave unchanged
    $params['skipStatusCal'] = 1;
    require_once 'CRM/Member/BAO/Membership.php';
    $membership     = new CRM_Member_BAO_Membership();
    $membership->id = $params['id'];
    if ( !$membership->find( true ) ) {
      return civicrm_api3_create_error( ts( 'Membership id is not valid' ));
    }
  } 

  // also check for status id if override is set (during add/update)
  if ( isset( $params['is_override'] ) &&
  !CRM_Utils_Array::value( 'status_id', $params ) ) {
    $valid  = false;
    $error .= ' status_id';
  }

  if ( ! $valid ) {
    return civicrm_api3_create_error( "Required fields not found for membership $error" );
  }

  return array();

}


