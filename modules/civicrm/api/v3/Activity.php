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
 * File for the CiviCRM APIv3 activity functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Activity
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Activity.php 30486 2010-11-02 16:12:09Z shot $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v3/utils.php';

require_once 'CRM/Activity/BAO/Activity.php';
require_once 'CRM/Core/DAO/OptionGroup.php';

/**
 * Creates or updates an Activity. See the example for usage
 * 
 * @param array  $params       Associative array of property name/value
 *                             pairs for the activity.
 * {@getfields activity}
 * @return array Array containing 'is_error' to denote success or failure and details of the created activity
 *
 * @example ActivityCreate.php Standard create example
 * @example Activity/ContactRefCustomField.php Create example including setting a contact reference custom field
 * {@example ActivityCreate.php 0}
 *
 */
function civicrm_api3_activity_create( $params ) {

    if ( !CRM_Utils_Array::value('source_contact_id',$params )){
           $session = CRM_Core_Session::singleton( );
           $params['source_contact_id']  =  $session->get( 'userID' );
    }

    civicrm_api3_verify_mandatory($params,
                                  null,
                                  array('source_contact_id',
                                        array('subject','activity_subject'),
                                        array('activity_name','activity_type_id')));
    $errors = array( );

    // check for various error and required conditions
    $errors = _civicrm_api3_activity_check_params( $params ) ;

    if ( !empty( $errors ) ) {
        return $errors;
    }


    // processing for custom data
    $values = array();
    _civicrm_api3_custom_format_params( $params, $values, 'Activity' );

    if ( ! empty($values['custom']) ) {
        $params['custom'] = $values['custom'];
    }

    $params['skipRecentView'] = true;

    if ( CRM_Utils_Array::value('activity_id', $params) ) {
        $params['id'] = $params['activity_id'];
    }
    
    $deleteActivityAssignment = false;
    if ( isset($params['assignee_contact_id']) ) {
        $deleteActivityAssignment = true;
    }

    $deleteActivityTarget = false;
    if ( isset($params['target_contact_id']) ) {
        $deleteActivityTarget = true;
    }

    $params['deleteActivityAssignment'] = CRM_Utils_Array::value( 'deleteActivityAssignment', $params, $deleteActivityAssignment );
    $params['deleteActivityTarget'] = CRM_Utils_Array::value( 'deleteActivityTarget', $params, $deleteActivityTarget );

    // create activity
    $activityBAO = CRM_Activity_BAO_Activity::create( $params );

    if ( isset( $activityBAO->id ) ) {
      if (array_key_exists ('case_id',$params)) {
        require_once 'CRM/Case/DAO/CaseActivity.php';
        $caseActivityDAO = new CRM_Case_DAO_CaseActivity();
        $caseActivityDAO->activity_id = $activityBAO->id ;
        $caseActivityDAO->case_id = $params['case_id'];
        $caseActivityDAO->find( true );
        $caseActivityDAO->save();
      }
      _civicrm_api3_object_to_array( $activityBAO, $activityArray[$activityBAO->id]);
      return civicrm_api3_create_success($activityArray,$params,'activity','get',$activityBAO);
    }

}

/*
 * Return valid fields for API
 */
function civicrm_api3_activity_getfields( $params ) {
    $fields =  _civicrm_api_get_fields('activity') ;
    //activity_id doesn't appear to work so let's tell them to use 'id' (current focus is ensuring id works)
    $fields['id'] = $fields['activity_id'];
    unset ($fields['activity_id']);
    $fields['assignee_contact_id'] = array('name' => 'assignee_id',
                                           'title' => 'assigned to',
                                           'type' => 1,
                                           'FKClassName' => 'CRM_Activity_DAO_ActivityAssignment');
    $fields['target_contact_id'] = array('name' => 'target_id',
                                           'title' => 'Activity Target',
                                           'type' => 1,
                                           'FKClassName' => 'CRM_Activity_DAO_ActivityTarget');
    $fields['activity_status_id'] = array('name' => 'status_id',
                                           'title' => 'Status Id',
                                           'type' => 1,);

    require_once ('CRM/Core/BAO/CustomField.php');

    return civicrm_api3_create_success($fields );
}



/**
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs for the activity.
 * @return array
 *
 * @todo - if you pass in contact_id do you / can you get custom fields
 *
 * {@example ActivityGet.php 0}
 */

function civicrm_api3_activity_get( $params ) {
        civicrm_api3_verify_mandatory($params);

        if (!empty($params['contact_id'])){
           $activities = CRM_Activity_BAO_Activity::getContactActivity( $params['contact_id'] );
           //BAO function doesn't actually return a contact ID - hack api for now & add to test so when api re-write happens it won't get missed
           foreach ($activities as $key => $activityArray){
              $activities[$key]['id'] = $key ;
          }
        }else{
          $activities = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, FALSE);
        }
        if(CRM_Utils_Array::value('return.assignee_contact_id',$params)){
          foreach ($activities as $key => $activityArray){
              $activities[$key]['assignee_contact_id'] = CRM_Activity_BAO_ActivityAssignment::retrieveAssigneeIdsByActivityId($activityArray['id'] ) ;
          }
        }
        if(CRM_Utils_Array::value('return.target_contact_id',$params)){
          foreach ($activities as $key => $activityArray){
              $activities[$key]['target_contact_id'] = CRM_Activity_BAO_ActivityTarget::retrieveTargetIdsByActivityId($activityArray['id'] ) ;
          }
        }
        foreach ( $params as $n => $v ) {
           if ( substr( $n, 0, 13 ) == 'return.custom' ) { // handle the format return.sort_name=1,return.display_name=1
               $returnProperties[ substr( $n, 7 ) ] = $v;
           }
        }
        if ( !empty( $activities ) && (!empty($returnProperties) || !empty($params['contact_id']))) {
          foreach ($activities as $activityId => $values){

             _civicrm_api3_custom_data_get($activities[$activityId],'Activity',$activityId,null,$values['activity_type_id']);
          }
        }
        //legacy custom data get - so previous formatted response is still returned too
        return civicrm_api3_create_success( $activities ,$params,'activity','get');

}

/**
 * Delete a specified Activity.
 * @param array $params array holding 'id' of activity to be deleted
 *
 * @return void|CRM_Core_Error  An error if 'activityName or ID' is invalid,
 *                         permissions are insufficient, etc. or CiviCRM success array
 *
 * @access public
 *
 * @example ActivityDelete.php
 * {@example ActivityDelete.php 0}
 * 
 */
function civicrm_api3_activity_delete( $params )
{
        civicrm_api3_verify_mandatory($params);
        $errors = array( );

        //check for various error and required conditions
        $errors = _civicrm_api3_activity_check_params( $params ) ;

        if ( !empty( $errors ) ) {
            return $errors;
        }

        if ( CRM_Activity_BAO_Activity::deleteActivity( $params ) ) {
            return civicrm_api3_create_success(1,$params,'activity','delete' );
        } else {
            return civicrm_api3_create_error(  'Could not delete activity'  );
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
function _civicrm_api3_activity_check_params ( & $params)
{

   $contactIDFields = array_intersect_key($params, array('source_contact_id' => 1,'assignee_contact_id' => 1, 'target_contact_id' => 1));
   if(!empty($contactIDFields)){
   $contactIds = array();
   foreach ($contactIDFields as $fieldname => $contactfield) {
     if(empty($contactfield))break;
     if(is_array($contactfield)) {
       foreach ($contactfield as $contactkey => $contactvalue) {
         $contactIds[$contactvalue] = $contactvalue;
       }
     }else{
       $contactIds[$contactfield] = $contactfield;
     }
   }


        $sql = '
SELECT  count(*)
  FROM  civicrm_contact
 WHERE  id IN (' . implode( ', ', $contactIds ) . ' )';
        if ( count( $contactIds ) !=  CRM_Core_DAO::singleValueQuery( $sql ) ) {
            return civicrm_api3_create_error( 'Invalid '. ucfirst($key) .' Contact Id' );
        }

   }
   
   
    $activityIds = array( 'activity' => CRM_Utils_Array::value( 'id', $params ),
                          'parent'   => CRM_Utils_Array::value( 'parent_id', $params ),
                          'original' => CRM_Utils_Array::value( 'original_id', $params )
                          );

    foreach ( $activityIds as $id => $value ) {
        if (  $value &&
              !CRM_Core_DAO::getFieldValue( 'CRM_Activity_DAO_Activity', $value, 'id' ) ) {
            return civicrm_api3_create_error(  'Invalid ' . ucfirst( $id ) . ' Id' );
        }
    }


    require_once 'CRM/Core/PseudoConstant.php';
    $activityTypes = CRM_Core_PseudoConstant::activityType( true, true, false, 'name', true );
    $activityName   = CRM_Utils_Array::value( 'activity_name', $params );
    $activityTypeId = CRM_Utils_Array::value( 'activity_type_id', $params );

    if ( $activityName ) {
        $activityNameId = array_search( ucfirst( $activityName ), $activityTypes );

        if ( !$activityNameId ) {
            return civicrm_api3_create_error(  'Invalid Activity Name'  );
        } else if ( $activityTypeId && ( $activityTypeId != $activityNameId ) ) {
            return civicrm_api3_create_error(  'Mismatch in Activity'  );
        }
        $params['activity_type_id'] = $activityNameId;
    } else if ( $activityTypeId &&
                !array_key_exists( $activityTypeId, $activityTypes ) ) {
        return civicrm_api3_create_error( 'Invalid Activity Type ID' );
    }


    /*
     * @todo unique name for status_id is activity status id - status id won't be supported in v4
     */
    if (!empty($params['status_id'])){
        $params['activity_status_id'] = $params['status_id'];
    }
    // check for activity status is passed in
    if ( isset( $params['activity_status_id'] ) ) {
        require_once "CRM/Core/PseudoConstant.php";
        $activityStatus = CRM_Core_PseudoConstant::activityStatus( );

        if ( is_numeric( $params['activity_status_id'] ) && !array_key_exists( $params['activity_status_id'], $activityStatus ) ) {
            return civicrm_api3_create_error( 'Invalid Activity Status' );
        } elseif ( !is_numeric( $params['activity_status_id'] ) ) {
            $statusId = array_search( $params['activity_status_id'], $activityStatus );

            if ( !is_numeric( $statusId ) ) {
                return civicrm_api3_create_error( 'Invalid Activity Status' );
            }
        }
    }

    if ( isset( $params['priority_id'] ) )  {
        if ( is_numeric( $params['priority_id'] ) ) {
            require_once "CRM/Core/PseudoConstant.php";
            $activityPriority = CRM_Core_PseudoConstant::priority( );
            if ( !array_key_exists( $params['priority_id'], $activityPriority ) ) {
                return civicrm_api3_create_error( 'Invalid Priority' );
            }
        } else {
            return civicrm_api3_create_error( 'Invalid Priority' );
        }
    }

    // check for activity duration minutes
    if ( isset( $params['duration_minutes'] ) && !is_numeric( $params['duration_minutes'] ) ) {
        return civicrm_api3_create_error('Invalid Activity Duration (in minutes)' );
    }


     //if adding a new activity & date_time not set make it now
    if (!CRM_Utils_Array::value( 'id', $params ) &&
         !CRM_Utils_Array::value( 'activity_date_time', $params ) ) {
        $params['activity_date_time'] = CRM_Utils_Date::processDate( date( 'Y-m-d H:i:s' ) );
    }

    return null;
}
