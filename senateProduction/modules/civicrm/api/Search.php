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
 * Definition of the Contact part of the CRM API.  
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

require_once 'CRM/Contact/BAO/Query.php';

/** 
 * Most API functions take in associative arrays ( name => value pairs 
 * as parameters. Some of the most commonly used parameters are 
 * described below 
 * 
 * @param array $params           an associative array used in construction 
                                  / retrieval of the object 
 * @param array $returnProperties the limited set of object properties that 
 *                                need to be returned to the caller 
 * 
 */ 


/** 
 * Returns the number of Contact objects which match the search criteria specified in $params.
 *
 * @param array  $params
 *
 * @return int
 * @access public
 */
function crm_contact_search_count( &$params ) {
    // convert the params to new format
    require_once 'CRM/Contact/Form/Search.php';
    $newP =& CRM_Contact_BAO_Query::convertFormValues( $params );
    $query =& new CRM_Contact_BAO_Query( $newP );
    return $query->searchQuery( 0, 0, null, true );
}

/**  
 * returns a number of contacts from the offset that match the criteria
 * specified in $params. return_properties are the values that are returned
 * to the calling function
 * 
 * @param array  $params
 * @param array  $returnProperties
 * @param object|array  $sort      object or array describing sort order for sql query.
 * @param int    $offset   the row number to start from
 * @param int    $rowCount the number of rows to return
 * 
 * @return int 
 * @access public 
 */ 
function crm_contact_search( &$params, $return_properties = null, $sort = null, $offset = 0, $row_count = 25) {
    $sortString = CRM_Core_DAO::getSortString( $sort );
    require_once 'CRM/Contact/BAO/Query.php';
    $newP =& CRM_Contact_BAO_Query::convertFormValues( $params );
    return CRM_Contact_BAO_Query::apiQuery( $newP, $return_properties, null, $sortString, $offset, $row_count );
} 

/** 
 * Returns the number of Contact objects which match the search criteria specified in $params.
 * This matches the new search format
 *
 * @param array  $params
 *
 * @return int
 * @access public
 */
function crm_search_count( &$params ) {
    require_once 'CRM/Contact/Form/Search.php';
    $query =& new CRM_Contact_BAO_Query( $params );
    return $query->searchQuery( 0, 0, null, true );
}

/**  
 * returns a number of contacts from the offset that match the criteria
 * specified in $params. return_properties are the values that are returned
 * to the calling function. This matches the new search format
 * 
 * @param array  $params
 * @param array  $returnProperties
 * @param object|array  $sort      object or array describing sort order for sql query.
 * @param int    $offset   the row number to start from
 * @param int    $rowCount the number of rows to return
 * 
 * @return int 
 * @access public 
 */ 
function crm_search( &$params, $return_properties = null, $sort = null, $offset = 0, $row_count = 25) {
    $sortString = CRM_Core_DAO::getSortString( $sort );
    require_once 'CRM/Contact/Form/Search.php';
    return CRM_Contact_BAO_Query::apiQuery( $params, $return_properties, null, $sortString, $offset, $row_count );
}



