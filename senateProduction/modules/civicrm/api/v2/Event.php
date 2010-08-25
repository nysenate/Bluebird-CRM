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
 * File for the CiviCRM APIv2 event functions
 *
 * @package CiviCRM_APIv2
 * @subpackage API_Event
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Event.php 27242 2010-04-27 04:48:36Z deepak $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v2/utils.php';

/**
 * Create a Event
 *  
 * This API is used for creating a Event
 * 
 * @param   array  $params  an associative array of title/value property values of civicrm_event
 * 
 * @return array of newly created event property values.
 * @access public
 */
function civicrm_event_create( &$params ) 
{
    _civicrm_initialize();
    if ( ! is_array($params) ) {
        return civicrm_create_error('Params is not an array');
    }
    
    if (! isset( $params['title'] ) || ! isset( $params['event_type_id'] ) || ! isset( $params['start_date'] ) ) {
        return civicrm_create_error('Missing require fields ( title, event type id,start date)');
    }
    
    $error = _civicrm_check_required_fields( $params, 'CRM_Event_DAO_Event' );
    if ($error['is_error']) {
        return civicrm_create_error( $error['error_message'] );
    }
    
    // Do we really want $params[id], even if we have
    // $params[event_id]? if yes then please uncomment the below line 
    
    //$ids['event'      ] = $params['id'];
    
    $ids['eventTypeId'] = $params['event_type_id'];
    $ids['startDate'  ] = $params['start_date'];
    $ids['event_id']    = CRM_Utils_Array::value( 'event_id', $params );
    
    require_once 'CRM/Event/BAO/Event.php';
    $eventBAO = CRM_Event_BAO_Event::create($params, $ids);
    
    if ( is_a( $eventBAO, 'CRM_Core_Error' ) ) {
        return civicrm_create_error( "Event is not created" );
    } else {
        $event = array();
        _civicrm_object_to_array($eventBAO, $event);
        $values = array( );
        $values['event_id'] = $event['id'];
        $values['is_error']   = 0;
    }
    
    return $values;
}

/**
 * Get an Event.
 * 
 * This api is used to retrieve all data for an existing Event.
 * Required parameters : id of event
 * 
 * @param  array $params  an associative array of title/value property values of civicrm_event
 * 
 * @return  If successful array of event data; otherwise object of CRM_Core_Error.
 * @access public
 */
function civicrm_event_get( &$params ) 
{
    _civicrm_initialize();

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( 'Input parameters is not an array.' );
    }
    
    if ( empty( $params ) ) {
        return civicrm_create_error('Params cannot be empty.');
    }
    
    $event  =& civicrm_event_search( $params );
    
    if ( count( $event ) != 1 &&
         ! CRM_Utils_Array::value( 'returnFirst', $params ) ) {
        return civicrm_create_error( ts( '%1 events matching input params', array( 1 => count( $event ) ) ) );
    }
    
    if ( civicrm_error( $event ) ) {
        return $event;
    }
    
    $event = array_values( $event );
    $event[0]['is_error'] = 0;
    return $event[0];
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

function civicrm_event_search( &$params ) 
{

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameters is not an array.' ) );
    }

    $inputParams            = array( );
    $returnProperties       = array( );
    $returnCustomProperties = array( );
    $otherVars              = array( 'sort', 'offset', 'rowCount' );

    $sort     = false;
    // don't check if empty, more meaningful error for API user instead of siletn defaults
    $offset   = array_key_exists( 'return.offset', $params ) ? $params['return.offset'] : 0;
    $rowCount = array_key_exists( 'return.max_results', $params ) ? $params['return.max_results'] : 25;
    
    foreach ( $params as $n => $v ) {
        if ( substr( $n, 0, 7 ) == 'return.' ) {
            if ( substr( $n, 0, 14 ) == 'return.custom_') {
                //take custom return properties separate
                $returnCustomProperties[] = substr( $n, 7 );
            } elseif( !in_array( substr( $n, 7 ) ,array( 'offset', 'max_results' ) ) ) {
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
    
    $eventDAO->orderBy( $sort );
    $eventDAO->limit( (int)$offset, (int)$rowCount );
    $eventDAO->find( );
    while ( $eventDAO->fetch( ) ) {
        $event[$eventDAO->id] = array( );
        CRM_Core_DAO::storeValues( $eventDAO, $event[$eventDAO->id] );
        $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Event', CRM_Core_DAO::$_nullObject, $eventDAO->id, false, $eventDAO->event_type_id );
        $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, CRM_Core_DAO::$_nullObject );
        $defaults  = array( );
        CRM_Core_BAO_CustomGroup::setDefaults( $groupTree, $defaults );
            
        if ( !empty( $defaults ) ) {
            foreach ( $defaults as $key => $val ) {
                if (! empty($returnCustomProperties ) ) {
                    $customKey  = explode('_', $key );
                    //show only return properties
                    if ( in_array( 'custom_'.$customKey['1'], $returnCustomProperties ) ) {
                        $event[$eventDAO->id][$key] = $val;
                    }
                } else {
                    $event[$eventDAO->id][$key] = $val;
                }
            }
        }
    }//end of the loop
    $eventDAO->free( );
    return $event; 
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
 */
function civicrm_event_delete( &$params ) 
{
    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }
    
    $eventID = null;
    
    $eventID = CRM_Utils_Array::value( 'event_id', $params );
    
    if ( ! isset( $eventID ) ) {
        return civicrm_create_error( ts( 'Invalid value for eventID' ) );
    }
    
    require_once 'CRM/Event/BAO/Event.php';
    
    return CRM_Event_BAO_Event::del( $eventID ) ?  civicrm_create_success( ) : civicrm_create_error( ts( 'Error while deleting event' ) );
}

