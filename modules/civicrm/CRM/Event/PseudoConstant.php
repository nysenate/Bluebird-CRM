<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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

require_once 'CRM/Core/PseudoConstant.php';

/**
 * This class holds all the Pseudo constants that are specific to Event. This avoids
 * polluting the core class and isolates the Event
 */
class CRM_Event_PseudoConstant extends CRM_Core_PseudoConstant 
{
    /**
     * Event
     *
     * @var array
     * @static
     */
    private static $event; 
    
    /**
     * Participant Status 
     *
     * @var array
     * @static
     */
    private static $participantStatus; 
    
    /**
     * Participant Role
     *
     * @var array
     * @static
     */
    private static $participantRole; 
    
    /**
     * Participant Listing
     *
     * @var array
     * @static
     */
    private static $participantListing; 
    
    /**
     * Event Type.
     *
     * @var array
     * @static
     */
    private static $eventType; 
    
    /**
     * event template titles
     * @var array
     */
    private static $eventTemplates;

    /**
     * Get all the n events
     *
     * @access public
     * @return array - array reference of all events if any
     * @static
     */
    public static function &event( $id = null, $all = false, $condition = null ) 
    {
        $key = "{$id}_{$all}_{$condition}";

        if ( !isset( self::$event[$key] ) ) {
            self::$event[$key] = array( );
        }

        if ( ! self::$event[$key] ) {
            CRM_Core_PseudoConstant::populate( self::$event[$key],
                                               'CRM_Event_DAO_Event',
                                               $all, 'title', 'is_active', $condition , null);
        }
                        
        if ($id) {
            if (array_key_exists($id, self::$event[$key])) {
                return self::$event[$key][$id];
            } else {
                return null;
            }
        }
        return self::$event[$key];
    }
    
    /**
     * Get all the n participant statuses
     *
     * @access public
     * @param  string - $retColumn  tells populate() whether to return 'name' (default) or 'label' values
     * @return array  - array reference of all participant statuses if any
     * @static
     */
    public static function &participantStatus( $id = null, $cond = null, $retColumn = 'name' ) 
    { 
        if ( self::$participantStatus === null ) {
            self::$participantStatus = array( );
        }

        $index = $cond ? $cond : 'No Condition';
        $index = "{$index}_{$retColumn}";
        if ( ! CRM_Utils_Array::value( $index, self::$participantStatus ) ) {
            self::$participantStatus[$index] = array( );
            CRM_Core_PseudoConstant::populate( self::$participantStatus[$index],
                                               'CRM_Event_DAO_ParticipantStatusType',
                                               false, $retColumn, 'is_active', $cond, 'weight' );
        }
        
        if ( $id ) {
            return self::$participantStatus[$index][$id];
        }
        
        return self::$participantStatus[$index];
    }

    /**
     * Return a status-type-keyed array of status classes
     *
     * @return array  of status classes, keyed by status type
     */
    static function &participantStatusClass()
    {
        static $statusClasses = null;

        if ($statusClasses === null) {
            self::populate($statusClasses, 'CRM_Event_DAO_ParticipantStatusType', true, 'class');
        }

        return $statusClasses;
    }
    
    /**
     * Get all the n participant roles
     *
     * @access public
     * @return array - array reference of all participant roles if any
     * @static
     */
    public static function &participantRole( $id = null, $cond = null )
    {
        $index = $cond ? $cond : 'No Condition';
        if ( ! CRM_Utils_Array::value( $index, self::$participantRole ) ) {
            self::$participantRole[$index] = array( );
            require_once "CRM/Core/OptionGroup.php";
            $condition = null;
            
            if ( $cond ) {
                $condition = "AND $cond";
            }
            
            self::$participantRole[$index] = CRM_Core_OptionGroup::values( "participant_role",  false, false, 
                                                                           false, $condition );
        }
        
        if ( $id ) {
            return self::$participantRole[$index][$id];
        }        
        return self::$participantRole[$index];
    }

    /**
     * Get all the participant listings
     *
     * @access public
     * @return array - array reference of all participant listings if any
     * @static
     */
    public static function &participantListing( $id = null )
    {
        if ( ! self::$participantListing ) {
            self::$participantListing = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$participantListing = CRM_Core_OptionGroup::values("participant_listing");
        }
        
        if( $id ) {
            return self::$participantListing[$id];
        }
        
        return self::$participantListing;
    }
    
    /**
     * Get all  event types.
     *
     * @access public
     * @return array - array reference of all event types.
     * @static
     */
    public static function &eventType( $id = null )
    {
        if ( ! self::$eventType ) {
            self::$eventType = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$eventType = CRM_Core_OptionGroup::values("event_type");
        }
        
        if( $id ) {
            return self::$eventType[$id];
        }
        
        return self::$eventType;
    }

    /**
     * get event template titles
     *
     * @return array  of event id → template title pairs
     */
    public static function &eventTemplates($id = null)
    {
        if (!self::$eventTemplates) {
            CRM_Core_PseudoConstant::populate(self::$eventTemplates, 
                                              'CRM_Event_DAO_Event', 
                                              false, 
                                              'template_title', 
                                              'is_active', 
                                              "is_template = 1"
                                              );
        }
        if ($id) {
            return self::$eventTemplates[$id];
        }
        return self::$eventTemplates;
    }
    
}

