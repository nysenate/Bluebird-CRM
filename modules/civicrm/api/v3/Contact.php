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
 * new version of civicrm apis. See blog post at
 * http://civicrm.org/node/131
 * @todo Write sth
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Contact
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id: Contact.php 30879 2010-11-22 15:45:55Z shot $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Contact/BAO/Contact.php';
/**
 * @todo - get rid of update & merge into this - wrapper handles update
 *
 * @param  array   $params           (reference ) input parameters
 *
 * Allowed @params array keys are:
 * {@schema Contact/Contact.xml}
 * {@schema Core/Address.xml}}
 * 
 * {@example ContactCreate.php 0}
 * 
 * @return array (reference )        contact_id of created or updated contact
 *
 * @static void
 * @access public
 */
function civicrm_api3_contact_create( $params )
{
    civicrm_api3_verify_mandatory($params,null,array('contact_type'));

    require_once 'CRM/Utils/Array.php';
    $contactID = CRM_Utils_Array::value( 'contact_id', $params );
    if (empty($contactID )){
        $contactID = CRM_Utils_Array::value( 'id', $params );
    }

    $dupeCheck = CRM_Utils_Array::value( 'dupe_check', $params, false );
    $values    = _civicrm_api3_contact_check_params( $params, $dupeCheck );
    if ( $values ) {
        return $values;
    }
    
    if ( empty($contactID ) ) {

        
        // If we get here, we're ready to create a new contact
        if ( ($email = CRM_Utils_Array::value( 'email', $params ) ) && !is_array( $params['email'] ) ) {
            require_once 'CRM/Core/BAO/LocationType.php';
            $defLocType = CRM_Core_BAO_LocationType::getDefault( );
            $params['email'] = array( 1 => array( 'email'            => $email,
                                                  'is_primary'       => 1, 
                                                  'location_type_id' => ($defLocType->id)?$defLocType->id:1
                                                  ),
                                      );
        }
    }

    if ( $homeUrl = CRM_Utils_Array::value( 'home_url', $params ) ) {  
        require_once 'CRM/Core/PseudoConstant.php';
        $websiteTypes = CRM_Core_PseudoConstant::websiteType( );
        $params['website'] = array( 1 => array( 'website_type_id' => key( $websiteTypes ),
                                                'url'             => $homeUrl 
                                                )
                                    );  
    }

    if ( isset( $params['suffix_id'] ) &&
         ! ( is_numeric( $params['suffix_id'] ) ) ) {
        $params['suffix_id'] = array_search( $params['suffix_id'] , CRM_Core_PseudoConstant::individualSuffix() );
    }

    if ( isset( $params['prefix_id'] ) &&
         ! ( is_numeric( $params['prefix_id'] ) ) ) {
        $params['prefix_id'] = array_search( $params['prefix_id'] , CRM_Core_PseudoConstant::individualPrefix() );
    }

         if ( isset( $params['gender_id'] )
              && ! ( is_numeric( $params['gender_id'] ) ) ) {
        $params['gender_id'] = array_search( $params['gender_id'] , CRM_Core_PseudoConstant::gender() );
    }
    
    $error = _civicrm_api3_greeting_format_params( $params );
    if ( civicrm_api3_error( $error ) ) {
        return $error;
    }
    
    $values   = array( );
    $entityId = $contactID;

    if ( ! CRM_Utils_Array::value('contact_type', $params) &&
         $entityId ) {
        $params['contact_type'] = CRM_Contact_BAO_Contact::getContactType( $entityId );
    }
    
    if ( ! ( $csType = CRM_Utils_Array::value('contact_sub_type', $params) ) &&
         $entityId ) {
        require_once 'CRM/Contact/BAO/Contact.php';
        $csType = CRM_Contact_BAO_Contact::getContactSubType( $entityId );
    }
    
    $customValue = _civicrm_api3_contact_check_custom_params( $params, $csType ); 

    if ( $customValue ) {
        return $customValue;
    }
    _civicrm_api3_custom_format_params( $params, $values, $params['contact_type'], $entityId );

    $params = array_merge( $params, $values );

    $contact =& _civicrm_api3_contact_update( $params, $contactID );

    if ( is_a( $contact, 'CRM_Core_Error' ) ) {
        return civicrm_api3_create_error( $contact->_errors[0]['message'] );
    } else {
        $values = array( );
        _civicrm_api3_object_to_array_unique_fields($contact, $values[$contact->id]);
     
    }

    return civicrm_api3_create_success($values,$params,'contact');
    
    return civicrm_api3_contact_update( $params, $create_new );

}


/**
 * Retrieve one or more contacts, given a set of search params
 *
 * @param  mixed[]  (reference ) input parameters
 *
 * @return array (reference )        array of properties, if error an array with an error id and error message
 * @static void
 * @access public
 *
 * {@example ContactGet.php 0}
 * 
 * @todo Erik Hommel 16 dec 2010 Check that all DB fields are returned
 * @todo Erik Hommel 16 dec 2010 fix custom data (CRM-7231)
 * @todo EM 7 Jan 11 - does this return the number of contacts if required (replacement for deprecated contact_search_count function - if so is this tested?
 */

function civicrm_api3_contact_get( $params )
{

    civicrm_api3_verify_mandatory($params);
        // fix for CRM-7384 cater for soft deleted contacts
    $params['contact_is_deleted'] = 0;
    if (isset($params['showAll'])) {
        if (strtolower($params['showAll']) == "active") {
            $params['contact_is_deleted'] = 0;
        }
        if (strtolower($params['showAll']) == "trash") {
            $params['contact_is_deleted'] = 1;
        }
        if (strtolower($params['showAll']) == "all" && isset($params['contact_is_deleted'])) {
            unset($params['contact_is_deleted']);
        }
    }

    $inputParams      = array( );
    $returnProperties = array( );
    $otherVars = array( 'sort', 'offset', 'rowCount', 'smartGroupCache' );

    $sort            = null;
    $offset          = 0;
    $rowCount        = 25;
    $smartGroupCache = false;

    if (array_key_exists ('filter_group_id',$params)) {
      $params['filter.group_id'] = $params['filter_group_id'];
      unset ($params['filter_group_id']);
    }
    if (array_key_exists ('filter.group_id',$params)) { // filter.group_id works both for 1,2,3 and array (1,2,3) 
      if (is_array ($params['filter.group_id']))
        $groups = $params['filter.group_id'];
      else
        $groups = explode (',',$params['filter.group_id']);
      unset ($params['filter.group_id']);
      $groups = array_flip ($groups);
      $groups[key($groups)] = 1;
      $params['group']=$groups;
    }


    if ( array_key_exists ('return',$params)) {// handle the format return =sort_name,display_name...
      $returnProperties = explode (',',$params['return']);
      $returnProperties = array_flip ($returnProperties); 
      $returnProperties[key($returnProperties)] = 1; 
    }
    foreach ( $params as $n => $v ) {
        if ( substr( $n, 0, 6 ) == 'return' ) { // handle the format return.sort_name=1,return.display_name=1
            $returnProperties[ substr( $n, 7 ) ] = $v;
        } elseif ( in_array( $n, $otherVars ) ) {
            $$n = $v;
        } else {
            $inputParams[$n] = $v;
        }
    }

    if ( empty( $returnProperties ) ) {
        $returnProperties = null;
    }

    require_once 'CRM/Contact/BAO/Query.php';
    $newParams =& CRM_Contact_BAO_Query::convertFormValues( $inputParams );
    list( $contacts, $options ) = CRM_Contact_BAO_Query::apiQuery( $newParams,
                                                                   $returnProperties,
                                                                   null,
                                                                   $sort,
                                                                   $offset,
                                                                   $rowCount,
                                                                   $smartGroupCache );
    // CRM-7929 Quick fix by colemanw
    // TODO: Figure out what function is responsible for prepending 'individual_' to these keys
    // and sort it out there rather than going to all this trouble here.
    $returnContacts = array();
    if (is_array($contacts)) {
      foreach ($contacts as $cid => $contact) {
        if (is_array($contact)) {
          $returnContacts[$cid] = array();
          foreach ($contact as $key => $value) {
            $key = str_replace(array('individual_prefix', 'individual_suffix'), array('prefix', 'suffix'), $key);
            $returnContacts[$cid][$key] = $value; 
          }
        }
      }
    }
    return civicrm_api3_create_success($returnContacts, $params,'contact');

}


/**
 * Delete a contact with given contact id
 *
 * @param  array   	  $params (reference ) input parameters, contact_id element required
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 *
 * @example ContactDelete.php
 */
function civicrm_api3_contact_delete( $params )
{


    require_once 'CRM/Contact/BAO/Contact.php';
    civicrm_api3_verify_mandatory($params,null,array('id'));
    $contactID = CRM_Utils_Array::value( 'id', $params );


    $session =& CRM_Core_Session::singleton( );
    if ( $contactID ==  $session->get( 'userID' ) ) {
      return civicrm_api3_create_error(  'This contact record is linked to the currently logged in user account - and cannot be deleted.'  );
    }
    $restore      = CRM_Utils_Array::value( 'restore', $params ) ? $params['restore'] : false;
    $skipUndelete = CRM_Utils_Array::value( 'skip_undelete', $params ) ? $params['skip_undelete'] : false;
    if ( CRM_Contact_BAO_Contact::deleteContact( $contactID , $restore, $skipUndelete) ) {
      return civicrm_api3_create_success( );
    } else {
      return civicrm_api3_create_error(  'Could not delete contact'  );
    }

}



function _civicrm_api3_contact_check_params( &$params, $dupeCheck = true, $dupeErrorArray = false, $requiredCheck = true )
{    if(isset($params['id']) && is_numeric($params['id'])){
       $requiredCheck = false;
    }
    if ( $requiredCheck ) {
        if(isset($params['id'])){
          $required = array('Individual' , 'Household', 'Organization');
        }
        $required = array(
                          'Individual'   => array(
                                                  array( 'first_name', 'last_name' ),
                                                  'email',
                                                  ),
                          'Household'    => array(
                                                  'household_name',
                                                  ),
                          'Organization' => array(
                                                  'organization_name',
                                                  ),
                          );
        

        // contact_type has a limited number of valid values
        $fields = CRM_Utils_Array::value( $params['contact_type'], $required );
        if ( $fields == null ) {
            return civicrm_api3_create_error( "Invalid Contact Type: {$params['contact_type']}" );
        }
        
        if ( $csType = CRM_Utils_Array::value('contact_sub_type', $params) ) {
            if ( !(CRM_Contact_BAO_ContactType::isExtendsContactType($csType, $params['contact_type'])) ) {
                return civicrm_api3_create_error( "Invalid or Mismatched Contact SubType: {$csType}" );
            }
        }

        if ( !CRM_Utils_Array::value( 'contact_id', $params )&& CRM_Utils_Array::value( 'id', $params ) ) { 
            $valid = false;
            $error = '';
            foreach ( $fields as $field ) {
                if ( is_array( $field ) ) {
                    $valid = true;
                    foreach ( $field as $element ) {
                        if ( ! CRM_Utils_Array::value( $element, $params ) ) {
                            $valid = false;
                            $error .= $element; 
                            break;
                        }
                    }
                } else {
                    if ( CRM_Utils_Array::value( $field, $params ) ) {
                        $valid = true;
                    }
                }
                if ( $valid ) {
                    break;
                }
            }
            
            if ( ! $valid ) {
                return civicrm_api3_create_error( "Required fields not found for {$params['contact_type']} : $error" );
            }
        }
    }
    
    if ( $dupeCheck ) {
        // check for record already existing
        require_once 'CRM/Dedupe/Finder.php';
        $dedupeParams = CRM_Dedupe_Finder::formatParams($params, $params['contact_type']);

        // CRM-6431
        // setting 'check_permission' here means that the dedupe checking will be carried out even if the 
        // person does not have permission to carry out de-dupes
        // this is similar to the front end form
        if (isset($params['check_permission'])){
            $dedupeParams['check_permission'] = $params['check_permission'];
        }

        $ids = implode(',', CRM_Dedupe_Finder::dupesByParams($dedupeParams, $params['contact_type']));
        
        if ( $ids != null ) {
            if ( $dupeErrorArray ) {
                $error = CRM_Core_Error::createError( "Found matching contacts: $ids",
                                                      CRM_Core_Error::DUPLICATE_CONTACT, 
                                                      'Fatal', $ids );
                return civicrm_api3_create_error( $error->pop( ) );
            }
            
            return civicrm_api3_create_error( "Found matching contacts: $ids" );
        }
    }

    //check for organisations with same name
    if ( CRM_Utils_Array::value( 'current_employer', $params ) ) {
        $organizationParams = array();
        $organizationParams['organization_name'] = $params['current_employer'];
        
        require_once 'CRM/Dedupe/Finder.php';
        $dedupParams = CRM_Dedupe_Finder::formatParams($organizationParams, 'Organization');
        
        $dedupParams['check_permission'] = false;            
        $dupeIds = CRM_Dedupe_Finder::dupesByParams($dedupParams, 'Organization', 'Fuzzy');
        
        // check for mismatch employer name and id
        if ( CRM_Utils_Array::value( 'employer_id', $params )
             && !in_array( $params['employer_id'] ,$dupeIds ) ) {
            return civicrm_api3_create_error('Employer name and Employer id Mismatch');
        }
        
        // show error if multiple organisation with same name exist
        if ( !CRM_Utils_Array::value( 'employer_id', $params )
             && (count($dupeIds) > 1) ) {
            return civicrm_api3_create_error('Found more than one Organisation with same Name.');
        }
    }
    
    return null;
}


/** 
 * Takes an associative array and creates a contact object and all the associated 
 * derived objects (i.e. individual, location, email, phone etc) 
 * 
 * @param array $params (reference ) an assoc array of name/value pairs 
 * @param  int     $contactID        if present the contact with that ID is updated
 * 
 * @return object CRM_Contact_BAO_Contact object  
 * @access public 
 * @static 
 */ 
function _civicrm_api3_contact_update( $params, $contactID = null )
{
    require_once 'CRM/Core/Transaction.php';
    $transaction = new CRM_Core_Transaction( );

    if ( $contactID ) {
        $params['contact_id'] = $contactID;
    }
    require_once 'CRM/Contact/BAO/Contact.php';
    
    $contact = CRM_Contact_BAO_Contact::create( $params );

    $transaction->commit( );

    return $contact;
}


/**
 * Ensure that we have the right input parameters for custom data
 *
 * @param array   $params          Associative array of property name/value
 *                                 pairs to insert in new contact.
 * @param string  $csType          contact subtype if exists/passed.
 *
 * @return null on success, error message otherwise
 * @access public
 */
function _civicrm_api3_contact_check_custom_params( $params, $csType = null )
{
    empty($csType) ? $onlyParent = true : $onlyParent = false;
    
    require_once 'CRM/Core/BAO/CustomField.php';
    $customFields = CRM_Core_BAO_CustomField::getFields( $params['contact_type'],
                                                         false,
                                                         false,
                                                         $csType,
                                                         null,
                                                         $onlyParent,
                                                         false,
                                                         false );
    
    foreach ($params as $key => $value) {
        if ($customFieldID = CRM_Core_BAO_CustomField::getKeyID($key)) {
            /* check if it's a valid custom field id */
            if ( !array_key_exists($customFieldID, $customFields)) {

                $errorMsg = "Invalid Custom Field Contact Type: {$params['contact_type']}";
                if ( $csType ) {
                    $errorMsg .= " or Mismatched SubType: {$csType}.";  
                }
                return civicrm_api3_create_error( $errorMsg );  
            }
        }
    }
}

/**
 * Validate the addressee or email or postal greetings 
 *
 * @param  $params                   Associative array of property name/value
 *                                   pairs to insert in new contact.
 * 
 * @return array (reference )        null on success, error message otherwise
 *
 * @access public
 */
function _civicrm_api3_greeting_format_params( $params ) 
{
    $greetingParams = array( '', '_id', '_custom' );
    foreach ( array( 'email', 'postal', 'addressee' ) as $key ) {
        $greeting = '_greeting';
        if ( $key == 'addressee' ) {
            $greeting = '';   
        } 

        $formatParams = false;
        // unset display value from params.
        if ( isset( $params["{$key}{$greeting}_display"] ) ) {
            unset( $params["{$key}{$greeting}_display"] );  
        }

        // check if greetings are present in present
        foreach ( $greetingParams as $greetingValues ) {
            if ( array_key_exists( "{$key}{$greeting}{$greetingValues}", $params ) ) {
                $formatParams = true;
                break;
            }
        }

        if ( !$formatParams ) continue;
    
        // format params
        if ( CRM_Utils_Array::value( 'contact_type', $params ) == 'Organization' && $key != 'addressee' ) {
            return civicrm_api3_create_error( ts( 'You cannot use email/postal greetings for contact type %1.', 
                                                  array( 1 => $params['contact_type'] ) ) );
        }
        
        $nullValue      = false; 
        $filter         = array( 'contact_type'  => $params['contact_type'],
                                 'greeting_type' => "{$key}{$greeting}" );
        
        $greetings      = CRM_Core_PseudoConstant::greeting( $filter );
        $greetingId     = CRM_Utils_Array::value( "{$key}{$greeting}_id",     $params );
        $greetingVal    = CRM_Utils_Array::value( "{$key}{$greeting}",        $params );
        $customGreeting = CRM_Utils_Array::value( "{$key}{$greeting}_custom", $params );
        
        if ( !$greetingId && $greetingVal ) {
            $params["{$key}{$greeting}_id"] = CRM_Utils_Array::key( $params["{$key}{$greeting}"], $greetings );
        }
        
        if ( $customGreeting && $greetingId &&
             ( $greetingId != array_search( 'Customized', $greetings ) ) ) {
            return civicrm_api3_create_error( ts( 'Provide either %1 greeting id and/or %1 greeting or custom %1 greeting',
                                                  array( 1 => $key ) ) );
        }
        
        if ( $greetingVal && $greetingId &&
             ( $greetingId != CRM_Utils_Array::key( $greetingVal, $greetings ) ) ) {
            return civicrm_api3_create_error( ts( 'Mismatch in %1 greeting id and %1 greeting',
                                                  array( 1 => $key ) ) );
        } 
        
        if ( $greetingId ) {

            if ( !array_key_exists( $greetingId, $greetings ) ) {
                return civicrm_api3_create_error( ts( 'Invalid %1 greeting Id', array( 1 => $key ) ) );
            }
            
            if ( !$customGreeting && ( $greetingId == array_search( 'Customized', $greetings ) ) ) {
                return civicrm_api3_create_error( ts( 'Please provide a custom value for %1 greeting', 
                                                      array( 1 => $key ) ) );
            }
                        
        } else if ( $greetingVal ) {

            if ( !in_array( $greetingVal, $greetings ) ) {
                return civicrm_api3_create_error( ts( 'Invalid %1 greeting', array( 1 => $key ) ) );
            }

            $greetingId = CRM_Utils_Array::key( $greetingVal, $greetings );
        }
                     
        if ( $customGreeting ) {
            $greetingId = CRM_Utils_Array::key( 'Customized', $greetings );
        }

        $customValue = $params['contact_id'] ? CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', 
                                                                            $params['contact_id'], 
                                                                            "{$key}{$greeting}_custom" ) : false;
                
        if ( array_key_exists( "{$key}{$greeting}_id", $params ) && empty( $params["{$key}{$greeting}_id"] ) ) {
            $nullValue = true;
        } else if ( array_key_exists( "{$key}{$greeting}", $params ) && empty( $params["{$key}{$greeting}"] ) ) {
            $nullValue = true;
        } else if ( $customValue && array_key_exists( "{$key}{$greeting}_custom", $params ) 
                    && empty( $params["{$key}{$greeting}_custom"] ) ) {
            $nullValue = true;
        }

        $params["{$key}{$greeting}_id"] = $greetingId;

        if ( !$customValue && !$customGreeting && array_key_exists( "{$key}{$greeting}_custom", $params ) ) {
            unset( $params["{$key}{$greeting}_custom"] );
        }
        
        if ( $nullValue ) {
            $params["{$key}{$greeting}_id"]     = '';
            $params["{$key}{$greeting}_custom"] = '';
        }
                                
        if ( isset( $params["{$key}{$greeting}"] ) ) {
            unset( $params["{$key}{$greeting}"] );
        }
    }
}
