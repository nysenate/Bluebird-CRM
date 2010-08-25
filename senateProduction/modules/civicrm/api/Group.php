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
 * Definition of the Group part of the CRM API. 
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

require_once 'CRM/Contact/BAO/Group.php';
require_once 'CRM/Contact/BAO/GroupContact.php';

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
 * subscribe contacts to a group 
 * 
 * @param CRM_Contact $group       A valid group object (passed by reference).
 * @param array       $contacts    An array of one or more valid Contact objects (passed by reference).
 *
 *  
 * @return null if success or CRM_Error (db error or contacts were not valid)
 *
 * @access public
 */

function crm_subscribe_group_contacts(&$group, $contacts)
{
    _crm_initialize( );

    if(!is_array($contacts)) {
        return _crm_error( '$contacts is not  Array ' );
    }
   
    if( ! is_a( $group,'CRM_Contact_BAO_Group') && ! is_a( $group,'CRM_Contact_DAO_Group')) {
        return _crm_error( 'Invalid group object passed in' );
    }

    foreach($contacts as $contact){
        if ( ! isset( $contact->id )) {
            return _crm_error( 'Invalid contact object passed in' );
        }
        $contactID[] = $contact->id;
    }

    $status = 'Pending';
    $method = 'Email';

    CRM_Contact_BAO_GroupContact::addContactsToGroup( $contactID, $group->id, $method, $status);
    return null;

}

/**
 * confirm membership to a group  
 *
 * @param CRM_Contact $group       A valid group object (passed by reference).
 * @param array       $contacts    An array of one or more valid Contact objects (passed by reference).
 *
 *  
 * @return null if success or CRM_Error (db error or contact was not valid)
 *
 * @access public
 */
function crm_confirm_group_contacts(&$group, $contacts)
{
    _crm_initialize( );

    if( ! is_a( $group,'CRM_Contact_BAO_Group') && ! is_a( $group,'CRM_Contact_DAO_Group')) {
        return _crm_error( 'Invalid group object passed in' );
    }

    if(!is_array($contacts)) {
        return _crm_error( '$contacts is not  Array ' );
    }
    
    foreach($contacts as $contact){
        if ( ! isset( $contact->id )) {
            return _crm_error( 'Invalid contact object passed in' );
        }
        $member =& CRM_Contact_BAO_GroupContact::getMembershipDetail($contact->id,$group->id);
        if ( ! $member ) {
            continue;
        }
        
        if($member->status != 'Pending') {
            return _crm_error( 'Can not confirm subscription. Current group status is NOT Pending.' );
        }
        CRM_Contact_BAO_GroupContact::updateGroupMembershipStatus($contact->id,$group->id);
    }

    return null;    
}


