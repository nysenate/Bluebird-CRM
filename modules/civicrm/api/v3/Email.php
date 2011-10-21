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
 * File for the CiviCRM APIv3 email functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Email
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Email.php 2011-02-16 ErikHommel $
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 *  Add an Email for a contact
 * 
 * Allowed @params array keys are:
 * {@schema Core/Email.xml}
 * {@example EmailCreate.php}
 * @return array of newly created email property values.
 * @access public
 */
function civicrm_api3_email_create( $params ) 
{

    civicrm_api3_verify_mandatory ($params, null,array('email', 'contact_id') );
	/*
	 * if is_primary is not set in params, set default = 0
	 */
	if ( !array_key_exists('is_primary', $params )) {
		$params['is_primary'] = 0; 
	}	
	
    require_once 'CRM/Core/BAO/Email.php';
    $emailBAO = CRM_Core_BAO_Email::add($params);
    
	 if ( is_a( $emailBAO, 'CRM_Core_Error' )) {
		 return civicrm_api3_create_error( "Email is not created or updated ");
	 } else {
		 $values = array( );
		 _civicrm_api3_object_to_array($emailBAO, $values[$emailBAO->id]);
		 return civicrm_api3_create_success($values, $params,'email','create',$emailBAO );
	 }

}
/**
 * Deletes an existing Email
 *
 * @param  array  $params
 *
 * {@schema Core/Email.xml}
 * {@example EmailDelete.php 0}
 * @return boolean | error  true if successfull, error otherwise
 * @access public
 */
function civicrm_api3_email_delete( $params ) 
{

    civicrm_api3_verify_mandatory ($params,null,array ('id'));
    $emailID = CRM_Utils_Array::value( 'id', $params );

    require_once 'CRM/Core/DAO/Email.php';
    $emailDAO = new CRM_Core_DAO_Email();
    $emailDAO->id = $emailID;
    if ( $emailDAO->find( ) ) {
		while ( $emailDAO->fetch() ) {
			$emailDAO->delete();
			return civicrm_api3_create_success();
		}
	} else {
		return civicrm_api3_create_error( 'Could not delete email with id '.$emailID);
	}
    

}

/**
 * Retrieve one or more emails 
 *
 * @param  mixed[]  (reference ) input parameters
 * 
 * {@schema Core/Email.xml}
 * {@example EmailDelete.php 0}
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array details of found emails else error
 * @access public
 */

function civicrm_api3_email_get($params) 
{   
    civicrm_api3_verify_one_mandatory($params);

    require_once 'CRM/Core/BAO/Email.php';
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);

}
