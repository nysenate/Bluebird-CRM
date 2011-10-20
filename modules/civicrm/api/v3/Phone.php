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
 * File for the CiviCRM APIv3 phone functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Phone
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Phone.php 2011-03-16 ErikHommel $
 */

/**
 * Include utility functions
 */
require_once 'CRM/Core/BAO/Phone.php';

/**
 *  Add an Phone for a contact
 * 
 * Allowed @params array keys are:
 * {@schema Core/Phone.xml}
 * {@example PhoneCreate.php}
 * @return array of newly created phone property values.
 * @access public
 */
function civicrm_api3_phone_create( $params ) 
{

    civicrm_api3_verify_one_mandatory ($params, null, array ('contact_id', 'id'));
	/*
	 * if is_primary is not set in params, set default = 0
	 */
	if ( !CRM_Utils_Array::value('is_primary', $params )) {
		$params['is_primary'] = 0; 
	}
	/*
	 * if phone_type_id in params, it should exist as option value
	 */
	if (CRM_Utils_Array::value('phone_type_id', $params)) {
		$option_group_params = array(
			'version'	=>	'3',
			'name'		=>	'phone_type');
		$option_group = civicrm_api('OptionGroup', 'Get', $option_group_params);
		if ($option_group['count'] == 0) {
			return civicrm_api3_create_error("There is no option group 
				phone_type in CiviCRM, can not create phone.");
		} else {
			$option_value_params = array(
				'version'			=>	'3',
				'option_group_id'	=>	$option_group['id'],
				'value'				=>	$params['phone_type_id']);
			$option_value = civicrm_api('OptionValue', 'Get', $option_value_params);
			if ($option_value['count'] == 0) {
				return civicrm_api3_create_error("Phone_type_id does not
					exist, could not create phone");
			}
		}
	}
	/*
	 * if location_type_id in params, it should exist.
	 */
	if (CRM_Utils_Array::value('location_type_id', $params)) {
		$location_params = array(
			'version'			=>	'3',
			'name'				=>	'locationType');
		$locTypes = civicrm_api('Constant', 'Get', $location_params);
		if (!CRM_Utils_Array::value($params['location_type_id'], 
			$locTypes['values'])) {
			return civicrm_api3_create_error("Location_type_id does not
				exist, could not create phone");
		}
	}
    require_once 'CRM/Core/BAO/Phone.php';
    $phoneBAO = CRM_Core_BAO_Phone::add($params);
    
	 if ( is_a( $phoneBAO, 'CRM_Core_Error' )) {
		 return civicrm_api3_create_error( "Phone is not created or updated ");
	 } else {
		 $values = array( );
		 unset($phoneBAO->location_type_id);
		 CRM_Core_DAO::storeValues($phoneBAO, $values[$phoneBAO->id]);
		 return civicrm_api3_create_success($values, $params,$phoneBAO);
	 }

}
/**
 * Deletes an existing Phone
 *
 * @param  array  $params
 *
 * {@schema Core/Phone.xml}
 * {@example PhoneDelete.php 0}
 * @return boolean | error  true if successfull, error otherwise
 * @access public
 */
function civicrm_api3_phone_delete( $params ) 
{

    civicrm_api3_verify_mandatory ($params,null,array ('id'));
    $phoneID = CRM_Utils_Array::value( 'id', $params );

    require_once 'CRM/Core/DAO/Phone.php';
    $phoneDAO = new CRM_Core_DAO_Phone();
    $phoneDAO->id = $phoneID;
    if ( $phoneDAO->find( ) ) {
		while ( $phoneDAO->fetch() ) {
			$phoneDAO->delete();
			return civicrm_api3_create_success($phoneDAO->id,$params,$phoneDAO);
		}
	} else {
		return civicrm_api3_create_error( 'Could not delete phone with id '.$phoneID);
	}
    

}


/**
 *  civicrm_api('Phone','Get') to retrieve one or more phones is implemented by
 *  function civicrm_api3_phone_get ($params) into the file Phone/Get.php
 *  Could have been implemented here in this file too, but we moved it to illustrate the feature with a real usage.
 *
 */
