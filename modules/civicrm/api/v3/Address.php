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
 * File for the CiviCRM APIv3 address functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Address
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Address.php 2011-02-16 ErikHommel $
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 *  Add an Address for a contact
 * 
 * Allowed @params array keys are:
 * {@schema Core/Address.xml}
 * {@example AddressCreate.php}
 * @return array of newly created tag property values.
 * @access public
 */
function civicrm_api3_address_create( &$params ) 
{

    civicrm_api3_verify_one_mandatory ($params, null, 
    array ('contact_id', 'id'));
    civicrm_api3_verify_mandatory ($params, null, array('location_type_id'));
    
    require_once 'CRM/Core/BAO/Address.php';

	/*
	 * if is_primary is not set in params, set default = 0
	 */
	if ( !array_key_exists('is_primary', $params )) {
		$params['is_primary'] = 0; 
	}	
	
	/*
	 * if street_parsing, street_address has to be parsed into
	 * separate parts
	 */
	 if ( array_key_exists('street_parsing', $params)) {
		 if ( $params['street_parsing'] == 1 ) {
			 if ( array_key_exists('street_address', $params)) {
				 if (!empty($params['street_address'])) {
					 $parsedItems = CRM_Core_BAO_Address::parseStreetAddress(
						$params['street_address']);
					 if ( array_key_exists('street_name', $parsedItems)) {
						 $params['street_name'] = $parsedItems['street_name'];
					 }
					 if ( array_key_exists('street_unit', $parsedItems)) {
						 $params['street_unit'] = $parsedItems['street_unit'];
					 }
					 if ( array_key_exists('street_number', $parsedItems)) {
						 $params['street_number'] = $parsedItems['street_number'];
					 }
					 if ( array_key_exists('street_number_suffix', $parsedItems)) {
						 $params['street_number_suffix'] = $parsedItems['street_number_suffix'];
					 }
				 }
			 }
		 }
	 }
	 /*
	  * create array for BAO (expects address params in as an
	  * element in array 'address'
	  */
	 $paramsBAO = array( );
	 $paramsBAO['contact_id'] = $params['contact_id'];
	 unset ($params['contact_id']);
	 $paramsBAO['address'][0] = $params;
	 $addressBAO = CRM_Core_BAO_Address::create($paramsBAO, true);
	 if ( is_a( $addressBAO, 'CRM_Core_Error' )) {
		 return civicrm_api3_create_error( "Address is not created or updated ");
	 } else {
		 $values = array( );
		 CRM_Core_DAO::storeValues($addressBAO[0], $values);
		 return civicrm_api3_create_success($values, $params,'address',$addressBAO);
	 }

}
/**
 * Deletes an existing Address
 *
 * @param  array  $params
 * 
 * {@schema Core/Address.xml}
 * {@example AddressDelete.php 0}
 * @return boolean | error  true if successfull, error otherwise
 * @access public
 */
function civicrm_api3_address_delete( &$params ) 
{
    civicrm_api3_verify_mandatory ($params,null,array ('id'));
    $addressID = CRM_Utils_Array::value( 'id', $params );

    require_once 'CRM/Core/DAO/Address.php';
    $addressDAO = new CRM_Core_DAO_Address();
    $addressDAO->id = $addressID;
    if ( $addressDAO->find( ) ) {
		while ( $addressDAO->fetch() ) {
			$addressDAO->delete();
			return civicrm_api3_create_success(1,$params,'activity',$addressDAO);
		}
	} else {
		return civicrm_api3_create_error( 'Could not delete address with id '.$addressID);
	}
    
}

/**
 * Retrieve one or more addresses on address_id, contact_id, street_name, city
 * or a combination of those
 *
 * @param  mixed[]  (reference ) input parameters
 * 
 * {@example AddressGet.php 0}
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array details of found addresses else error
 * @access public
 */

function civicrm_api3_address_get(&$params) 
{   
    civicrm_api3_verify_one_mandatory($params, null, 
		array('id', 'contact_id', 'location_type_id'));
	
    require_once 'CRM/Core/BAO/Address.php';
    $addressBAO = new CRM_Core_BAO_Address();
    $fields = array_keys($addressBAO->fields());

    foreach ( $fields as $name) {
        if (array_key_exists($name, $params)) {
            $addressBAO->$name = $params[$name];
        }
    }
    
    if ( $addressBAO->find() ) {
      $addresses = array();
      while ( $addressBAO->fetch() ) {
        CRM_Core_DAO::storeValues( $addressBAO, $address );
        $addresses[$addressBAO->id] = $address;
      }
      return civicrm_api3_create_success($addresses,$params,'activity','get',$addressBAO);
    } else {
      return civicrm_api3_create_success(array(),$params,'activity','get',$addressBAO);
    }
				
}

