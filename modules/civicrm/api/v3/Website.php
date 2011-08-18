
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
 * File for the CiviCRM APIv3 website functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Website
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Website.php 2011-03-16 ErikHommel $
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 *  Add an Website for a contact
 * 
 * Allowed @params array keys are:
 * {@schema Core/Website.xml}
 * {@example WebsiteCreate.php}
 * @return array of newly created website property values.
 * @access public
 */
function civicrm_api3_website_create( $params ) 
{
    civicrm_api3_verify_one_mandatory ($params, null, array ('contact_id', 'id'));
    
    require_once 'CRM/Core/BAO/Website.php';

    $websiteBAO = CRM_Core_BAO_Website::add($params);
    
	 if ( is_a( $websiteBAO, 'CRM_Core_Error' )) {
		 return civicrm_api3_create_error( "Website is not created or updated ");
	 } else {
		 $values = array( );
		 _civicrm_api3_object_to_array($websiteBAO, $values[$websiteBAO->id]);
		 return civicrm_api3_create_success($values, $params,'website','get');
	 }

}
/**
 * Deletes an existing Website
 *
 * @param  array  $params
 *
 * {@schema Core/Website.xml}
 * {@example WebsiteDelete.php 0}
 * @return boolean | error  true if successfull, error otherwise
 * @access public
 */
function civicrm_api3_website_delete( $params ) 
{

    civicrm_api3_verify_mandatory ($params,null,array ('id'));
    $websiteID = CRM_Utils_Array::value( 'id', $params );

    require_once 'CRM/Core/DAO/Website.php';
    $websiteDAO = new CRM_Core_DAO_Website();
    $websiteDAO->id = $websiteID;
    if ( $websiteDAO->find( ) ) {
		while ( $websiteDAO->fetch() ) {
			$websiteDAO->delete();
			return civicrm_api3_create_success(1,$params,'website','delete');
		}
	} else {
		return civicrm_api3_create_error( 'Could not delete website with id '.$websiteID);
	}

}

/**
 * Retrieve one or more websites 
 *
 * @param  mixed[]  (reference ) input parameters
 * 
 * {@schema Core/Website.xml}
 * {@example WebsiteDelete.php 0}
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array details of found websites else error
 * @access public
 */

function civicrm_api3_website_get( $params ) 
{   

    civicrm_api3_verify_mandatory($params );
	
    require_once 'CRM/Core/BAO/Website.php';
    $websiteBAO = new CRM_Core_BAO_Website();
    $fields = array_keys($websiteBAO->fields());

    foreach ( $fields as $name) {
        if (array_key_exists($name, $params)) {
            $websiteBAO->$name = $params[$name];
        }
    }
    
    if ( $websiteBAO->find() ) {
        $websites = array();
        while ( $websiteBAO->fetch() ) {
            CRM_Core_DAO::storeValues( $websiteBAO, $website );
            $websites[$websiteBAO->id] = $website;
        }
        return civicrm_api3_create_success($websites,$params,$websiteBAO);
    } else {
        return civicrm_api3_create_success(array(),$params,'website','get',$websiteBAO);
    }

}

