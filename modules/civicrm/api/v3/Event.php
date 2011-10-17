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
 * File for the CiviCRM APIv3 event functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Event
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Event.php 30964 2010-11-29 09:41:54Z shot $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';

/**
 * Create a Event
 *
 * This API is used for creating a Event
 *
 * @param  array   $params           (reference ) input parameters
 * Allowed @params array keys are:
 * {@schema Event/Event.xml}
 *
 * @return array of newly created event property values.
 * @access public
*/
function civicrm_api3_event_create( $params )
{

    civicrm_api3_verify_mandatory ($params,'CRM_Event_DAO_Event',array ('start_date','event_type_id','title'));
   
    //format custom fields so they can be added
    $value = array();
    _civicrm_api3_custom_format_params( $params, $values, 'Event' );
    $params = array_merge($values,$params);
    require_once 'CRM/Event/BAO/Event.php';

    $eventBAO = CRM_Event_BAO_Event::create($params);

    if ( is_a( $eventBAO, 'CRM_Core_Error' ) ) {
      return civicrm_api3_create_error( "Event is not created" );
    } else {
      $event = array();
      _civicrm_api3_object_to_array($eventBAO, $event[$eventBAO->id]);
    }

    return civicrm_api3_create_success($event,$params);

}


/**
 * Get Event record.
 *
 *
 * @param  array  $params     an associative array of name/value property values of civicrm_event
 *
 * @return  Array of all found event property values.
 * @access public
 */

function civicrm_api3_event_get( $params )
{

    civicrm_api3_verify_mandatory($params);

    $inputParams            = array( );
    $returnProperties       = array( );
    $returnCustomProperties = array( );
    $otherVars              = array( 'sort', 'offset', 'rowCount', 'isCurrent' );

    $sort     =  array_key_exists( 'return.sort', $params ) ? $params['return.sort'] : false;
    // don't check if empty, more meaningful error for API user instead of silent defaults
    $offset   = array_key_exists( 'return.offset', $params ) ? $params['return.offset'] : 0;
    $rowCount = array_key_exists( 'return.max_results', $params ) ? $params['return.max_results'] : 25;
    $isCurrent = array_key_exists( 'isCurrent', $params ) ? $params['isCurrent'] : 0;
    $isFull  = array_key_exists( 'return.is_full', $params ) ? $params['return.is_full'] : 0;    

    foreach ( $params as $n => $v ) {
      if ( substr( $n, 0, 6 ) == 'return' ) {
        if ( substr( $n, 0, 14 ) == 'return.custom_') {
          //take custom return properties separate
          $returnCustomProperties[] = substr( $n, 7 );
        } elseif( !in_array( substr( $n, 7 ) ,array( 'sort', 'offset', 'max_results', 'isCurrent' , 'is_full') ) ) {
          $returnProperties[] = substr( $n, 7 );
        }
      } elseif ( in_array( $n, $otherVars ) ) {
        $$n = $v;
      } else {
        $inputParams[$n] = $v;
      }
    }

    if ( !empty($returnProperties ) ) {
      $returnProperties[]='id';
      $returnProperties[]='event_type_id';
    }

    require_once 'CRM/Core/BAO/CustomGroup.php';
    require_once 'CRM/Event/BAO/Event.php';
    $eventDAO = new CRM_Event_BAO_Event( );
    $eventDAO->copyValues( $inputParams );

    $event = array();
    if ( !empty( $returnProperties ) ) {
      $eventDAO->selectAdd( );
      $eventDAO->selectAdd( implode( ',' , $returnProperties ) );
    }

    $eventDAO->whereAdd( '( is_template IS NULL ) OR ( is_template = 0 )' );
    
    if ( $isCurrent ) {
        $eventDAO->whereAdd( '(start_date >= CURDATE() || end_date >= CURDATE())' );
    }
    $eventDAO->orderBy( $sort );
    $eventDAO->limit( (int)$offset, (int)$rowCount );
    $eventDAO->find( );
    while ( $eventDAO->fetch( ) ) {
      $event[$eventDAO->id] = array( );
      CRM_Core_DAO::storeValues( $eventDAO, $event[$eventDAO->id] );
      if($isFull){
         _civicrm_api3_event_getisfull($event,$eventDAO->id);
      }
      _civicrm_api3_custom_data_get($event[$eventDAO->id],'Event',$eventDAO->id,null,$eventDAO->event_type_id);

    }//end of the loop

    return civicrm_api3_create_success($event,$params,'event','get',$eventDAO);


}

/**
 * Deletes an existing event
 *
 * This API is used for deleting a event
 *
 * @param  Array  $params    array containing event_id to be deleted
 *
 * @return boolean        true if success, error otherwise
 * @access public
 * 
 * note API has legacy support for 'event_id'
 */
function civicrm_api3_event_delete( $params )
{

    civicrm_api3_verify_one_mandatory($params,null,array('event_id','id'));
    $eventID = 
        CRM_Utils_Array::value( 'event_id', $params ) ?
        CRM_Utils_Array::value( 'event_id', $params ) :
        CRM_Utils_Array::value( 'id', $params );

    require_once 'CRM/Event/BAO/Event.php';
    return 
        CRM_Event_BAO_Event::del( $eventID ) ?
        civicrm_api3_create_success( ) :
        civicrm_api3_create_error( ts( 'Error while deleting event' ) );

}

/*
 * Function to add 'is_full' & 'available_seats' to the return array. (this might be better in the BAO)
 * Default BAO function returns a string if full rather than a Bool - which is more appropriate to a form
 * 
 * @param array $event return array of the event
 * @param int $event_id Id of the event to be updated
 * 
 */
function _civicrm_api3_event_getisfull(&$event,$event_id){
        require_once 'CRM/Event/BAO/Participant.php';
        $eventFullResult = CRM_Event_BAO_Participant::eventFull($event_id,1);
        if(!empty($eventFullResult) && is_int($eventFullResult)){
          $event[$event_id]['available_places'] = $eventFullResult;
        }else{
          $event[$event_id]['available_places'] = 0; 
        }       
        $event[$event_id]['is_full'] = $event[$event_id]['available_places'] == 0  ? 1:0;
  
}
