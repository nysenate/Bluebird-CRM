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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * This class contains all the function that are called using AJAX
 */
class CRM_Event_Page_AJAX
{
    /**
     * Function for building Event combo box
     */
    function event( )
    {
        require_once 'CRM/Utils/Type.php';
        $name = trim( CRM_Utils_Type::escape( $_GET['s'], 'String' ) );
        if ( ! $name ){
            $name = '%';
        }
        $whereClause = " title LIKE '$name%' ";
        
        $query = "
SELECT title, id
FROM civicrm_event
WHERE {$whereClause}
ORDER BY title
";
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            echo $elements = "$dao->title|$dao->id\n";
        }
        CRM_Utils_System::civiExit( );
    }

    /**
     * Function for building Event Type combo box
     */
    function eventType( )
    {
        require_once 'CRM/Utils/Type.php';
        $name = trim( CRM_Utils_Type::escape( $_GET['s'], 'String' ) );
        if( !$name ) {
            $name = '%';
        }
        $whereClause = " v.label LIKE '$name%' ";
        
        $query ="
SELECT v.label ,v.value
FROM   civicrm_option_value v,
       civicrm_option_group g
WHERE  v.option_group_id = g.id 
AND g.name = 'event_type'
AND v.is_active = 1
AND {$whereClause}
ORDER by v.weight";

        $dao = CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch( ) ) {
                echo $elements = "$dao->label|$dao->value\n";
            }
            CRM_Utils_System::civiExit( );
    }

    /**
     * Function for building EventFee combo box
     */
    function eventFee( )
    {
        require_once 'CRM/Utils/Type.php';
        $name = trim( CRM_Utils_Type::escape( $_GET['s'], 'String' ) );
        if( !$name ) {
            $name = '%';
        }
        
        $whereClause = "cv.label LIKE '$name%' ";
        
        $query = "
SELECT distinct(cv.label), cv.id
FROM civicrm_option_value cv, civicrm_option_group cg
WHERE cg.name LIKE 'civicrm_event.amount%'
   AND cg.id = cv.option_group_id AND {$whereClause}
   GROUP BY cv.label
";
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            echo $elements = "$dao->label|$dao->id\n";
        }
        CRM_Utils_System::civiExit( );
    }
    
    function eventList(  ) {
        require_once "CRM/Event/BAO/Event.php";
        $events = CRM_Event_BAO_Event::getEvents( true );

        $elements = array( array( 'name'  => ts('- select -'),
                                  'value' => '' ) );
        foreach ( $events as $id => $name ) {
            $elements[] = array( 'name'  => $name,
                                 'value' => $id );
        }

        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    } 
}
