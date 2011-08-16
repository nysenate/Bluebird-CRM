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
 * @package CiviCRM_Hook
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id: $
 *
 */
 
require_once 'CRM/Utils/Hook.php';

class CRM_Utils_Hook_Soap extends CRM_Utils_Hook {

    /** 
     * This hook will be called on any operation on some core CiviCRM 
     * objects. We will extend the functionality over a period of time 
     * to make it similar to Drupal's user hook, where the external module 
     * can inject and collect form elements and form information into a 
     * Drupal form (specifically the registration page and the account 
     * information page) 
     * 
     * @param string $op         the type of operation being performed 
     * @param string $objectName the BAO class name of the object 
     * @param object $id         the object id if available
     * @param array  $params     the parameters used for object creation / editing
     *  
     * @return mixed             based on op. pre-hooks return a boolean and/or
     *                           an error message which aborts the operation
     * @access public 
     */ 
    static function pre( $op, $objectName, $id, &$params ) {
        return;
    }

    /** 
     * This hook will be called on any operation on some core CiviCRM 
     * objects. We will extend the functionality over a period of time 
     * to make it similar to Drupal's user hook, where the external module 
     * can inject and collect form elements and form information into a 
     * Drupal form (specifically the registration page and the account 
     * information page) 
     * 
     * @param string $op         the type of operation being performed 
     * @param string $objectName the BAO class name of the object 
     * @param int    $objectId   the unique identifier for the object 
     * @param object $objectRef  the reference to the object if available 
     *  
     * @return mixed             based on op. pre-hooks return a boolean and/or
     *                           an error message which aborts the operation
     * @access public 
     */ 
    static function post( $op, $objectName, $objectId, &$objectRef ) {
        return;
    }

    /**
     * This hook retrieves links from other modules and injects it into
     * CiviCRM forms
     *
     * @param string $op         the type of operation being performed
     * @param string $objectName the name of the object
     * @param int    $objectId   the unique identifier for the object 
     *
     * @return array|null        an array of arrays, each element is a tuple consisting of url, img, title
     *
     * @access public
     */
    static function links( $op, $objectName, $objectId ) {
        return null;
    }

}
