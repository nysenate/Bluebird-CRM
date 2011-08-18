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
 * File for the CiviCRM APIv3 group contact functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Group
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: GroupContact.php 30171 2010-10-14 09:11:27Z mover $
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 * This API will give list of the groups for particular contact
 * Particualr status can be sent in params array
 * If no status mentioned in params, by default 'added' will be used
 * to fetch the records
 *
 * @param  array $params  name value pair of contact information
 *
 * @return  array  list of groups, given contact subsribed to
 */
function civicrm_api3_group_contact_get($params) {

		
		civicrm_api3_verify_mandatory ( $params, null );
		require_once 'CRM/Contact/BAO/GroupContact.php';
		if(empty($params['contact_id'])){
		  if(empty($params['status'] )){
		    //default to 'Added'
		    $params['status'] ='Added';
		  }
		  //ie. id passed in so we have to return something
		  return _civicrm_api3_basic_get('CRM_Contact_BAO_GroupContact', $params);
		}
		$status = CRM_Utils_Array::value ( 'status', $params, 'Added' );

		$values = & CRM_Contact_BAO_GroupContact::getContactGroup ( $params ['contact_id'], $status, null, false, true );
		return civicrm_api3_create_success ( $values, $params );

}

/**
 * Add contact(s) to group(s)
 *
 * @access public
 * @param  array $params Input parameters
 *
 * Allowed @params array keys are:<br>
 * "contact_id" (required) : first contact to add<br>
 * "group_id" (required): first group to add contact(s) to<br>
 * "contact_id.1" etc. (optional) : another contact to add<br>
 * "group_id.1" etc. (optional) : additional group to add contact(s) to<br>
 * "status" (optional) : one of "Added", "Pending" or "Removed" (default is "Added")
 * {@example GroupContactCreate.php 0}
 * 
 * @return array Information about operation results
 *
 * On success, the return array will be structured as follows:
 * <code>array(
 *   "is_error" => 0,
 *   "version"  => 3,
 *   "count"    => 3,
 *   "values" => array(
 *     "not_added"   => integer,
 *     "added"       => integer,
 *     "total_count" => integer
 *   )
 * )</code>
 * 
 * On failure, the return array will be structured as follows:
 * <code>array(
 *   'is_error' => 1,
 *   'error_message' = string,
 *   'error_data' = mixed or undefined
 * )</code>
 * 
 */
function civicrm_api3_group_contact_create($params) {

		civicrm_api3_verify_mandatory ( $params, 'CRM_Contact_BAO_GroupContact' );
		$action = CRM_Utils_Array::value('status',$params,'Added');
		return _civicrm_api3_group_contact_common ( $params, $action );

}

/**
 *
 * @param <type> $params
 * @return <type>
 * @deprecated
 */

function civicrm_api3_group_contact_delete($params) {
		$params['status'] = 'Removed';
		return civicrm_api ( 'GroupContact','Create',$params);

}


/**
 *
 * @param <type> $params
 * @return <type>
 * @deprecated
 */
function civicrm_api3_group_contact_pending($params) {
		$params['status'] = 'Pending';
		return civicrm_api ( 'GroupContact','Create',$params);
}

/**
 *
 * @param <type> $params
 * @param <type> $op
 * @return <type>
 */
function _civicrm_api3_group_contact_common($params, $op = 'Added') {
	
	$contactIDs = array ();
	$groupIDs = array ();
	foreach ( $params as $n => $v ) {
		if (substr ( $n, 0, 10 ) == 'contact_id') {
			$contactIDs [] = $v;
		} else if (substr ( $n, 0, 8 ) == 'group_id') {
			$groupIDs [] = $v;
		}
	}
	
	if (empty ( $contactIDs )) {
		return civicrm_api3_create_error ( 'contact_id is a required field' );
	}
	
	if (empty ( $groupIDs )) {
		return civicrm_api3_create_error ( 'group_id is a required field' );
	}
	
	$method = CRM_Utils_Array::value ( 'method', $params, 'API' );
	if ($op == 'Added') {
		$status = CRM_Utils_Array::value ( 'status', $params, 'Added' );
	} elseif ($op == 'Pending') {
		$status = CRM_Utils_Array::value ( 'status', $params, 'Pending' );
	} else {
		$status = CRM_Utils_Array::value ( 'status', $params, 'Removed' );
	}
	$tracking = CRM_Utils_Array::value ( 'tracking', $params );
	
	require_once 'CRM/Contact/BAO/GroupContact.php';
	$values = array ();
	if ($op == 'Added' || $op == 'Pending') {
		$values ['total_count'] = $values ['added'] = $values ['not_added'] = 0;
		foreach ( $groupIDs as $groupID ) {
			list ( $tc, $a, $na ) = CRM_Contact_BAO_GroupContact::addContactsToGroup ( $contactIDs, $groupID, $method, $status, $tracking );
			$values ['total_count'] += $tc;
			$values ['added'] += $a;
			$values ['not_added'] += $na;
		}
	} else {
		$values ['total_count'] = $values ['removed'] = $values ['not_removed'] = 0;
		foreach ( $groupIDs as $groupID ) {
			list ( $tc, $r, $nr ) = CRM_Contact_BAO_GroupContact::removeContactsFromGroup ( $contactIDs, $groupID, $method, $status, $tracking );
			$values ['total_count'] += $tc;
			$values ['removed'] += $r;
			$values ['not_removed'] += $nr;
		}
	}
	return civicrm_api3_create_success ( $values );
}
/*
 * @deprecated - this should be part of create but need to know we aren't missing something
 */
function civicrm_api3_group_contact_update_status($params) {

		civicrm_api3_verify_mandatory ( $params, null, array ('contact_id', 'group_id' ) );
		
		$method = CRM_Utils_Array::value ( 'method', $params, 'API' );
		$tracking = CRM_Utils_Array::value ( 'tracking', $params );
		
		require_once 'CRM/Contact/BAO/GroupContact.php';
		
		CRM_Contact_BAO_GroupContact::updateGroupMembershipStatus ( $params ['contact_id'], $params ['group_id'], $method, $tracking );
		
		return TRUE;

}
