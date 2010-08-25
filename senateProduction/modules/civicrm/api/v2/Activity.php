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
 * File for the CiviCRM APIv2 activity functions
 *
 * @package CiviCRM_APIv2
 * @subpackage API_Activity
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Activity.php 27604 2010-05-12 14:35:23Z sunny $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v2/utils.php';

require_once 'CRM/Activity/BAO/Activity.php';
require_once 'CRM/Core/DAO/OptionGroup.php';

// require these to call new function names from deprecated ones in here
require_once 'api/v2/ActivityType.php';
require_once 'api/v2/ActivityContact.php';

/**
 * Create a new Activity.
 *
 * Creates a new Activity record and returns the newly created
 * activity object (including the contact_id property). Minimum
 * required data values for the various contact_type are:
 *
 * Properties which have administratively assigned sets of values
 * If an unrecognized value is passed, an error
 * will be returned. 
 *
 * Modules may invoke crm_get_contact_values($contactID) to
 * retrieve a list of currently available values for a given
 * property.
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contact.
 * @param string $activity_type Which class of contact is being created.
 *            Valid values = 'SMS', 'Meeting', 'Event', 'PhoneCall'.
 * {@schema Activity/Activity.xml}
 *                            
 * @return CRM_Activity|CRM_Error Newly created Activity object
 * 
 */
function &civicrm_activity_create( &$params ) 
{
    _civicrm_initialize( );
    
    $errors = array( );
    
    // check for various error and required conditions
    $errors = _civicrm_activity_check_params( $params, true ) ;

    if ( !empty( $errors ) ) {
        return $errors;
    }
    
    // processing for custom data
    $values = array();
    _civicrm_custom_format_params( $params, $values, 'Activity' );
    if ( ! empty($values['custom']) ) {
        $params['custom'] = $values['custom'];
    }
    
    if ( ! CRM_Utils_Array::value( 'activity_type_id', $params ) ) {
        $params['activity_type_id'] = CRM_Core_OptionGroup::getValue( 'activity_type', $params['activity_name'] , 'name' );
    }
    
    // create activity
    $activity = CRM_Activity_BAO_Activity::create( $params );
    
    if ( !is_a( $activity, 'CRM_Core_Error' ) && isset( $activity->id ) ) {
        $activityArray = array( 'is_error' => 0 ); 
    } else {
        $activityArray = array( 'is_error' => 1 ); 
    }
    
    _civicrm_object_to_array( $activity, $activityArray);
    
    return $activityArray;
}

/**
 *
 * @param <type> $params
 * @param <type> $returnCustom
 * @return <type>
 */
function civicrm_activity_get( $params, $returnCustom = false ) {
    _civicrm_initialize( );
    
    $activityId = $params['activity_id'];
    if ( empty( $activityId ) ) {
        return civicrm_create_error( ts ("Required parameter not found" ) );
    }
    
    if ( !is_numeric( $activityId ) ) {
        return civicrm_create_error( ts ( "Invalid activity Id" ) );
    }
    
    $activity = _civicrm_activity_get( $activityId, $returnCustom );
    
    if ( $activity ) {
        return civicrm_create_success( $activity );
    } else {
        return civicrm_create_error( ts( 'Invalid Data' ) );
    }
}

/**
 * Wrapper to make this function compatible with the REST API
 *
 * Obsolete now; if no one is using this, it should be removed. -- Wes Morgan
 */
function civicrm_activity_get_contact( $params ) {
    // TODO: Spit out deprecation warning here
    return civicrm_activities_get_contact( $params );
}

/**
 * Retrieve a set of activities, specific to given input params.
 *
 * @param  array  $params (reference ) input parameters.
 *
 * @return array (reference)  array of activities / error message.
 * @access public
 */
function civicrm_activities_get_contact( $params )
{
    // TODO: Spit out deprecation warning here
    return civicrm_activity_contact_get( $params );
}

/**
 * Update a specified activity.
 *
 * Updates activity with the values passed in the 'params' array. An
 * error is returned if an invalid id or activity Name is passed 
 * @param CRM_Activity $activity A valid Activity object
 * @param array       $params  Associative array of property
 *                             name/value pairs to be updated. 
 *  
 * @return CRM_Activity|CRM_Core_Error  Return the updated ActivtyType Object else
 *                                Error Object (if integrity violation)
 *
 * @access public
 *
 */
function &civicrm_activity_update( &$params ) 
{
    $errors = array( );
    //check for various error and required conditions
    $errors = _civicrm_activity_check_params( $params ) ;

    if ( !empty( $errors ) ) {
        return $errors;
    }
    
    $activity = _civicrm_activity_update( $params );
    return $activity;
}

/**
 * Delete a specified Activity.
 * @param CRM_Activity $activity Activity object to be deleted
 *
 * @return void|CRM_Core_Error  An error if 'activityName or ID' is invalid,
 *                         permissions are insufficient, etc.
 *
 * @access public
 *
 */
function civicrm_activity_delete( &$params ) 
{
    _civicrm_initialize( );
    
    $errors = array( );
    
    //check for various error and required conditions
    $errors = _civicrm_activity_check_params( $params ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    if ( CRM_Activity_BAO_Activity::deleteActivity( $params ) ) {
        return civicrm_create_success( );
    } else {
        return civicrm_create_error( ts( 'Could not delete activity' ) );
    }
}

/**
 * Function to update activities
 * @param CRM_Activity $activity Activity object to be deleted
 *
 * @return void|CRM_Core_Error  An error if 'activityName or ID' is invalid,
 *                         permissions are insufficient, etc.
 *
 * @access public
 *
 */
function _civicrm_activity_update( $params ) 
{
    require_once 'CRM/Activity/DAO/Activity.php';
    $dao = new CRM_Activity_BAO_Activity();
    $dao->id = $params['id'];
    if ( $dao->find( true ) ) {
        $dao->copyValues( $params );
        if ( ! isset( $params['activity_date_time'] ) &&
             isset( $dao->activity_date_time ) ) {
            // dont update it
            $dao->activity_date_time = null;
        }

        $dao->save( );
    }
    $activity = array();
    _civicrm_object_to_array( $dao, $activity );
    
    return $activity;
}

/**
 * Retrieve a specific Activity by Id.
 *
 * @param int $activityId
 *
 * @return array (reference)  activity object
 * @access public
 */
function _civicrm_activity_get( $activityId, $returnCustom = false ) {
    $dao = new CRM_Activity_BAO_Activity();
    $dao->id = $activityId;
    if( $dao->find( true ) ) {
        $activity = array();
        _civicrm_object_to_array( $dao, $activity );

        //also return custom data if needed.
        if ( $returnCustom && !empty( $activity ) ) {
            $customdata = civicrm_activity_custom_get( array( 'activity_id'      => $activityId, 
                                                              'activity_type_id' => $activity['activity_type_id']  )  );
            $activity = array_merge( $activity, $customdata );
        }
    
        return $activity;
    } else {
        return false;
    }
}

/**
 * Function to check for required params
 *
 * @param array   $params  associated array of fields
 * @param boolean $addMode true for add mode
 *
 * @return array $error array with errors
 */
function _civicrm_activity_check_params ( &$params, $addMode = false ) 
{
    // return error if we do not get any params
    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'Input Parameters empty' ) );
    }

    // check for activity subject if add mode
    if ( $addMode && ! isset( $params['subject'] ) ) {
        return civicrm_create_error( ts( 'Missing Subject' ) );
    }

    if ( ! $addMode && ! isset( $params['id'] )) {
        return civicrm_create_error( ts( 'Required parameter "id" not found' ) );
    }

    if ( ! $addMode && $params['id'] && ! is_numeric ( $params['id'] )) {
        return civicrm_create_error( ts( 'Invalid activity "id"' ) );
    }
    
    // check if activity type_id is passed in
    if ( ! isset($params['activity_name'] )  && ! isset($params['activity_type_id'] ) ) {
        //when name AND id are both absent
        return civicrm_create_error( ts ( 'Missing Activity' ) );
    } else if ( isset( $params['activity_name'] )  && isset( $params['activity_type_id'] ) ) {
        //when name AND id are both present - check for the match
        $activityTypes  =& CRM_Core_PseudoConstant::activityType( );
        $activityId     = array_search( $params['activity_name'], $activityTypes );
        if ( $activityId != $params['activity_type_id'] ) {
            return civicrm_create_error( ts ( 'Mismatch in Activity' ) );
        }
    } else {
        //either name OR id is present
        if ( isset( $params['activity_name'] ) ) {
            require_once "CRM/Core/PseudoConstant.php";
            $activityTypes  =& CRM_Core_PseudoConstant::activityType( true, false, true );
            $activityId     = array_search( $params['activity_name'], $activityTypes );
            
            if ( ! $activityId ) { 
                return civicrm_create_error( ts ( 'Invalid Activity Name' ) );
            } else {
                $params['activity_type_id'] = $activityId;
            }
        } else {
            if ( !is_numeric( $params['activity_type_id'] ) ) {
                return  civicrm_create_error( ts('Invalid Activity Type ID') );
            } else {
                $activityTypes =& CRM_Core_PseudoConstant::activityType( );
                if ( !array_key_exists( $params['activity_type_id'], $activityTypes ) ) {
                    return  civicrm_create_error( ts('Invalid Activity Type ID') ); 
                }
            }
        }
    }
    
    // check for activity status is passed in
    if ( isset( $params['status_id'] ) && !is_numeric( $params['status_id'] ) ) {
        require_once "CRM/Core/PseudoConstant.php";
        $activityStatus   =& CRM_Core_PseudoConstant::activityStatus( );
        $activityStatusId = array_search( $params['status_id'], $activityStatus );
        if ( ! $activityStatusId ) { 
            return civicrm_create_error( ts('Invalid Activity Status') );
        } else {
            $params['status_id'] = $activityStatusId;
        }
    }
    
    // check for activity duration minutes
    if ( isset( $params['duration_minutes'] ) && !is_numeric( $params['duration_minutes'] ) ) {
        return civicrm_create_error( ts('Invalid Activity Duration (in minutes)') );
        
    }
        
    // check for source contact id
    if ( $addMode && empty( $params['source_contact_id'] ) ) {
        return  civicrm_create_error( ts('Missing Source Contact') );
    } 
    
    if (isset( $params['source_contact_id'] ) && !is_numeric( $params['source_contact_id'] ) ) {
        return  civicrm_create_error( ts('Invalid Source Contact') );
    }
    return null;
}

/**
 * Convert an email file to an activity
 */
function civicrm_activity_processemail( $file, $activityTypeID, $result = array( ) ) {
    // do not parse if result array already passed (towards EmailProcessor..)
    if ( empty($result) ) {
        // might want to check that email is ok here
        if ( ! file_exists( $file ) ||
             ! is_readable( $file ) ) {
            return CRM_Core_Error::createAPIError( ts( 'File %1 does not exist or is not readable',
                                                       array( 1 => $file ) ) );
        }
    }

    require_once 'CRM/Utils/Mail/Incoming.php';
    $result = CRM_Utils_Mail_Incoming::parse( $file );
    if ( $result['is_error'] ) {
        return $result;
    }

    $params = _civicrm_activity_buildmailparams( $result, $activityTypeID );
    return civicrm_activity_create( $params );
}

/**
 *
 * @param <type> $result
 * @param <type> $activityTypeID
 * @return <type>
 */
function _civicrm_activity_buildmailparams( $result, $activityTypeID ) {
    // get ready for collecting data about activity to be created
    $params = array();

    $params['activity_type_id']   = $activityTypeID;
    $params['status_id']          = 2;
    $params['source_contact_id']  = $params['assignee_contact_id'] = $result['from']['id'];
    $params['target_contact_id']  = array( );
    $keys = array( 'to', 'cc', 'bcc' );
    foreach ( $keys as $key ) {
        if ( is_array( $result[$key] ) ) {
            foreach ( $result[$key] as $key => $keyValue ) {
                $params['target_contact_id'][]  = $keyValue['id'];
            }
        }
    }
    $params['subject']            = $result['subject'];
    $params['activity_date_time'] = $result['date'];
    $params['details']            = $result['body'];

    for ( $i = 1; $i <= 5; $i++ ) {
        if ( isset( $result["attachFile_$i"] ) ) {
            $params["attachFile_$i"] = $result["attachFile_$i"];
        }
    }

    return $params;
}

/**
 *
 * @param <type> $file
 * @param <type> $activityTypeID
 * @return <type>
 */
function civicrm_activity_process_email( $file, $activityTypeID ) {
    // TODO: Spit out deprecation warning here
    return civicrm_activity_processemail( $file, $activityTypeID );
}

/**
 *
 * @return <type> 
 */
function civicrm_activity_get_types( ) {
    // TODO: Spit out deprecation warning here
    return civicrm_activity_type_get( );
}

/**
 * Function retrieve actiovity custom data.
 * @param  array  $params key => value array.
 * @return array  $customData activity custom data 
 *
 * @access public
 */
function civicrm_activity_custom_get( $params ) {
    
    $customData = array( );
    if ( !CRM_Utils_Array::value( 'activity_id', $params ) ) {
        return $customData;
    }
    
    require_once 'CRM/Core/BAO/CustomGroup.php';
    $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Activity', 
                                                     CRM_Core_DAO::$_nullObject, 
                                                     $params['activity_id'], 
                                                     null,
                                                     CRM_Utils_Array::value( 'activity_type_id', $params )
                                                     );
    //get the group count.
    $groupCount = 0;
    foreach ( $groupTree as $key => $value ) {
        if ( $key === 'info' ) {
            continue;
        }
        $groupCount++;
    }
    $formattedGroupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 
                                                                     $groupCount, 
                                                                     CRM_Core_DAO::$_nullObject );
    $defaults = array( );
    CRM_Core_BAO_CustomGroup::setDefaults( $formattedGroupTree, $defaults );
    if ( !empty( $defaults ) ) {
        foreach ( $defaults as $key => $val ) {
            $customData[$key] = $val;
        }
    }
    
    return $customData;
}

