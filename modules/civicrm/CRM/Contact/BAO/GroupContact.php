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

require_once 'CRM/Contact/DAO/GroupContact.php';
require_once 'CRM/Contact/BAO/SubscriptionHistory.php';

class CRM_Contact_BAO_GroupContact extends CRM_Contact_DAO_GroupContact {
    
    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }

    /**
     * takes an associative array and creates a groupContact object
     *
     * the function extract all the params it needs to initialize the create a
     * group object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return object CRM_Contact_BAO_Group object
     * @access public
     * @static
     */
    static function add( &$params ) 
    {
       
        $dataExists = self::dataExists( $params );
        if ( ! $dataExists ) {
            return null;
        }

        $groupContact = new CRM_Contact_BAO_GroupContact( );
        $groupContact->copyValues( $params );
        CRM_Contact_BAO_SubscriptionHistory::create($params);
        $groupContact->save( );
        return $groupContact;
    }

    /**
     * Check if there is data to create the object
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return boolean
     * @access public
     * @static
     */
    static function dataExists( &$params ) 
    {
        // return if no data present
        if ($params['group_id'] == 0) {
            return false;
        }

        return true;
     }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $params        input parameters to find object
     * @param array $values        output values of the object
     * @param array $ids           the array that holds all the db ids
     *
     * @return array (reference)   the values that could be potentially assigned to smarty
     * @access public
     * @static
     */
    static function getValues( &$params, &$values ) 
    {
        if ( empty( $params ) ) {
            return null;
        }
        $values['group']['data']       =& CRM_Contact_BAO_GroupContact::getContactGroup($params['contact_id'],
                                                                                        'Added' ,
                                                                                        3 );

        // get the total count of groups
        $values['group']['totalCount'] =  CRM_Contact_BAO_GroupContact::getContactGroup($params['contact_id'],
                                                                                        'Added' ,
                                                                                        null,
                                                                                        true );

        return null;
    }

    /**
     * Given an array of contact ids, add all the contacts to the group 
     *
     * @param array  $contactIds (reference ) the array of contact ids to be added
     * @param int    $groupId    the id of the group
     *
     * @return array             (total, added, notAdded) count of contacts added to group
     * @access public
     * @static
     */
    static function addContactsToGroup( &$contactIds, $groupId,
                                        $method = 'Admin',
                                        $status = 'Added',
                                        $tracking = null)  {

        require_once 'CRM/Utils/Hook.php';
        
        CRM_Utils_Hook::pre( 'create', 'GroupContact', $groupId, $contactIds ); 
        
        $date = date('YmdHis');
        $numContactsAdded    = 0;
        $numContactsNotAdded = 0;
       
        foreach ( $contactIds as $contactId ) {
            
            $groupContact = new CRM_Contact_DAO_GroupContact( );
            $groupContact->group_id   = $groupId;
            $groupContact->contact_id = $contactId;
            // check if the selected contact id already a member
            // if not a member add to groupContact else keep the count of contacts that are not added
            if (  ! $groupContact->find( true )) {
                // add the contact to group
                $historyParams = array(
                    'contact_id' => $contactId, 
                    'group_id' => $groupId, 
                    'method' => $method,
                    'status' => $status,
                    'date' => $date,
                    'tracking' => $tracking,
                );
                CRM_Contact_BAO_SubscriptionHistory::create($historyParams);
                $groupContact->status    = $status;
                $groupContact->save( );
                $numContactsAdded++;
            } else {
                if ($groupContact->status == 'Added') {
                    $numContactsNotAdded++;
                } else {
                    $historyParams = array(
                        'contact_id' => $contactId, 
                        'group_id' => $groupId, 
                        'method' => $method,
                        'status' => $status,
                        'date' => $date,
                        'tracking' => $tracking,
                    );
                    CRM_Contact_BAO_SubscriptionHistory::create($historyParams);
                    $groupContact->status    = $status;
                    $groupContact->save( );
                    $numContactsAdded++;
                }
            }
        }

        // also reset the acl cache
        $config = CRM_Core_Config::singleton( );
        if ( ! $config->doNotResetCache ) {
            require_once 'CRM/ACL/BAO/Cache.php';
            CRM_ACL_BAO_Cache::resetCache( );
        }

        // reset the group contact cache for all group(s)
        // if this group is being used as a smart group
        require_once 'CRM/Contact/BAO/GroupContactCache.php';
        CRM_Contact_BAO_GroupContactCache::remove( );

        CRM_Utils_Hook::post( 'create', 'GroupContact', $groupId, $contactIds );

        return array( count($contactIds), $numContactsAdded, $numContactsNotAdded );
    }


    /**
     * Given an array of contact ids, remove all the contacts from the group 
     *
     * @param array  $contactIds (reference ) the array of contact ids to be removed
     * @param int    $groupId    the id of the group
     *
     * @return array             (total, removed, notRemoved) count of contacts removed to group
     * @access public
     * @static
     */
    static function removeContactsFromGroup( &$contactIds, $groupId ,$method = 'Admin',$status = 'Removed',$tracking = null) {
        if ( ! is_array( $contactIds ) ) {
            return array( 0, 0, 0 );
        }
        
        require_once 'CRM/Utils/Hook.php';
        
        if ($status == 'Removed') {
            $op = 'delete';
        } else {
            $op = 'edit';
        }
        
        CRM_Utils_Hook::pre( $op, 'GroupContact', $groupId, $contactIds );
        
        $date = date('YmdHis');
        $numContactsRemoved    = 0;
        $numContactsNotRemoved = 0;
        
        require_once "CRM/Contact/DAO/Group.php";
        $group = new CRM_Contact_DAO_Group();
        $group->id = $groupId;
        $group->find(true);
        
        foreach ( $contactIds as $contactId ) {
            $groupContact = new CRM_Contact_DAO_GroupContact( );
            $groupContact->group_id   = $groupId;
            $groupContact->contact_id = $contactId;
            // check if the selected contact id already a member, or if this is
            // an opt-out of a smart group.
            // if not a member remove to groupContact else keep the count of contacts that are not removed
            if ( $groupContact->find( true ) || $group->saved_search_id ) {
                // remove the contact from the group
                $numContactsRemoved++;
            } else {
                $numContactsNotRemoved++;
            }
            
            //now we grant the negative membership to contact if not member. CRM-3711
            $historyParams = array( 'group_id' => $groupId,
                                    'contact_id' => $contactId,
                                    'status' => $status,
                                    'method' => $method,
                                    'date' => $date,
                                    'tracking' => $tracking);
            CRM_Contact_BAO_SubscriptionHistory::create($historyParams);
            $groupContact->status = $status;
            $groupContact->save( );
        }
        
        // also reset the acl cache
        $config = CRM_Core_Config::singleton( );
        if ( ! $config->doNotResetCache ) {
            require_once 'CRM/ACL/BAO/Cache.php';
            CRM_ACL_BAO_Cache::resetCache( );
        }

        // reset the group contact cache for all group(s)
        // if this group is being used as a smart group
        require_once 'CRM/Contact/BAO/GroupContactCache.php';
        CRM_Contact_BAO_GroupContactCache::remove( );

        CRM_Utils_Hook::post( $op, 'GroupContact', $groupId, $contactIds );
        
        return array( count($contactIds), $numContactsRemoved, $numContactsNotRemoved );
    }


    /**
     * Function to get list of all the groups and groups for a contact
     *
     * @param  int $contactId contact id
     *
     * @access public
     * @return array $values this array has key-> group id and value group title
     * @static
     */
    static function getGroupList( $contactId = 0, $visibility = false ) {
        require_once 'CRM/Contact/DAO/Group.php';
        $group = new CRM_Contact_DAO_Group( );

        $select = $from = $where = '';
        
        $select = 'SELECT DISTINCT civicrm_group.id, civicrm_group.title ';
        $from   = ' FROM civicrm_group ';
        $where  = " WHERE civicrm_group.is_active = 1 ";
        if ( $contactId ) {
            $from  .= ' , civicrm_group_contact ';
            $where .= " AND civicrm_group.id = civicrm_group_contact.group_id 
                        AND civicrm_group_contact.contact_id = " 
                    . CRM_Utils_Type::escape($contactId, 'Integer');
        }

        if ( $visibility ) {
            $where .= " AND civicrm_group.visibility != 'User and User Admin Only'";
        }

        $orderby = " ORDER BY civicrm_group.name";
        $sql     = $select . $from . $where . $orderby;

        $group->query($sql);

        $values = array( );
        while($group->fetch()) {
            $values[$group->id] = $group->title;
        }
        
        return $values;
    }

   /**
     * function to get the list of groups for contact based on status of membership
     *
     * @param int     $contactId         contact id 
     * @param string  $status            state of membership
     * @param int     $numGroupContact   number of groups for a contact that should be shown
     * @param boolean $count             true if we are interested only in the count
     * @param boolean $ignorePermission  true if we should ignore permissions for the current user
     *                                   useful in profile where permissions are limited for the user
     * @param boolean $onlyPublicGroups  true if we want to hide system groups
     *
     * @return array (reference )|int $values the relevant data object values for the contact or
     *                                 the total count when $count is true
     *
     * $access public
     */
    static function &getContactGroup( $contactId, $status = null,
                                      $numGroupContact = null,
                                      $count = false,
                                      $ignorePermission = false,
                                      $onlyPublicGroups = false ) {
        if ( $count ) {
            $select = 'SELECT count(DISTINCT civicrm_group_contact.id)';
        } else {
            $select = 'SELECT 
                    civicrm_group_contact.id as civicrm_group_contact_id, 
                    civicrm_group.title as group_title,
                    civicrm_group.visibility as visibility,
                    civicrm_group_contact.status as status, 
                    civicrm_group.id as group_id,
                    civicrm_subscription_history.date as date,
                    civicrm_subscription_history.method as method';
        }

        $where  = " WHERE contact_a.id = %1 AND civicrm_group.is_active = 1 ";

        $params = array( 1 => array( $contactId, 'Integer' ) );
        if ( ! empty( $status ) ) {
            $where .= ' AND civicrm_group_contact.status = %2';
            $params[2] = array( $status, 'String' );
        }
        $tables     = array( 'civicrm_group_contact'        => 1,
                             'civicrm_group'                => 1,
                             'civicrm_subscription_history' => 1 );
        $whereTables = array( );
        if ( $ignorePermission ) {
            $permission = ' ( 1 ) ';
        } else {
            $permission = CRM_Core_Permission::whereClause( CRM_Core_Permission::VIEW, $tables, $whereTables ); 
        }
        
        require_once 'CRM/Contact/BAO/Query.php';
        $from = CRM_Contact_BAO_Query::fromClause( $tables );
        
        $where .= " AND $permission ";

        if ( $onlyPublicGroups ) {
            $where .= " AND civicrm_group.visibility != 'User and User Admin Only' ";
        }

        $order = $limit = '';
        if (! $count ) {
            $order = ' ORDER BY civicrm_group.title, civicrm_subscription_history.date ASC';

            if ( $numGroupContact ) {
                $limit = " LIMIT 0, $numGroupContact";
            }
        }

        $sql = $select . $from . $where . $order . $limit;

        if ( $count ) {
            $result = CRM_Core_DAO::singleValueQuery( $sql, $params ); 
            return $result;
        } else {
            $dao =& CRM_Core_DAO::executeQuery( $sql, $params );
            $values = array( );
            while ( $dao->fetch() ) {
                $id                            = $dao->civicrm_group_contact_id;
                $values[$id]['id']             = $id;
                $values[$id]['group_id']       = $dao->group_id;
                $values[$id]['title']          = $dao->group_title;
                $values[$id]['visibility']     = $dao->visibility;
                switch($dao->status) { 
                case 'Added': 
                    $prefix = 'in_'; 
                    break; 
                case 'Removed': 
                    $prefix = 'out_'; 
                    break; 
                default: 
                    $prefix = 'pending_'; 
                } 
                $values[$id][$prefix . 'date']            = $dao->date; 
                $values[$id][$prefix . 'method']          = $dao->method; 
                if ( $status == "Removed" ) {
                    $query = "SELECT `date` as `date_added` FROM civicrm_subscription_history WHERE id = (SELECT max(id) FROM civicrm_subscription_history WHERE contact_id = %1 AND status = \"Added\" AND group_id = $dao->group_id )";
                    $dateDAO =& CRM_Core_DAO::executeQuery( $query, $params );
                    if ($dateDAO->fetch() ) {
                        $values[$id]["date_added"]          = $dateDAO->date_added; 
                    }
                    
                }
            }
            return $values;
        }
    }

    /**
     * Returns array of contacts who are members of the specified group.
     *
     * @param CRM_Contact $group                A valid group object (passed by reference)
     * @param array       $returnProperties     Which properties
     *                    should be included in the returned Contact object(s). If NULL,
     *                    the default set of contact properties will be
     *                    included. group_contact properties (such as 'status',
     * '                  in_date', etc.) are included automatically.Note:Do not inclue
     *                    Id releted properties.  
     * @param text        $status               A valid status value ('Added', 'Pending', 'Removed').
     * @param text        $sort                 Associative array of
     *                    one or more "property_name"=>"sort direction"
     *                    pairs which will control order of Contact objects returned.
     * @param Int         $offset               Starting row index.
     * @param Int         $row_count            Maximum number of rows to returns.
     *
     *
     * @return            $contactArray         Array of contacts who are members of the specified group
     *
     * @access public
     */
    static function getGroupContacts(&$group,
                                     $returnProperties = null,
                                     $status = 'Added',
                                     $sort = null,
                                     $offset = null,
                                     $row_count= null,
                                     $includeChildGroups = false )
    {
        $groupDAO = new CRM_Contact_DAO_Group();
        $groupDAO->id = $group->id;
        if ( ! $groupDAO->find( true ) ) {
            return CRM_Core_Error::createError( "Could not locate group with id: $id" );
        }
        
        // make sure user has got permission to view this group
        require_once 'CRM/Contact/BAO/Group.php';
        if ( ! CRM_Contact_BAO_Group::checkPermission( $groupDAO->id, $groupDAO->title ) ) {
            return CRM_Core_Error::createError( "You do not have permission to access group with id: $id" );
        }
        
        $query = '';
        if ( empty($returnProperties) ) {
            $query = "SELECT contact_a.id as contact_id,
                      civicrm_email.email as email";
        } else {
            $query  = "SELECT contact_a.id as contact_id , $grpStatus as status,";
            $query .= implode( ',', $returnProperties );
        }
        
        $params = array( );
        if ( $includeChildGroups ) {
            require_once 'CRM/Contact/BAO/GroupNesting.php';
            $groupIds = CRM_Contact_BAO_GroupNesting::getDescendentGroupIds( array( $group->id ) );
        } else {
            $groupIds = array( $group->id );
        }
        foreach ( $groupIds as $groupId ) {
            $params[] = array( 'group', 'IN', array($group->id => true), 0, 0 );
        }

        require_once 'CRM/Core/BAO/Email.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        $tables = array(
            CRM_Core_BAO_Email::getTableName() => true,
            CRM_Contact_BAO_Contact::getTableName() => true, 
        );

        $inner = array( );

        $whereTables = array( );
        $where = CRM_Contact_BAO_Query::getWhereClause($params, null, $tables, $whereTables);
        $permission = CRM_Core_Permission::whereClause( CRM_Core_Permission::VIEW, $tables, $whereTables);
        $from = CRM_Contact_BAO_Query::fromClause($tables, $inner);
        $query .= " $from WHERE $permission AND $where ";
        
        if ( $sort != null ) {
            $order = array( );
            foreach($sort as $key=> $direction) {
                $order[] = " $key $direction ";
            }
            $query .= " ORDER BY " . implode( ',', $order );
        }
        
        if ( !is_null($offset) && !is_null($row_count) ) {
            $query .= " LIMIT $offset, $row_count";        
        }

        $dao = new CRM_Contact_DAO_Contact( );
        $dao->query( $query );
        
        // this is quite inefficient, we need to change the return
        // values in docs
        $contactArray = array();
        while($dao->fetch()) { 
            $contactArray[] = clone($dao);
        }
        return $contactArray;
    }

    
    /**
     * Returns membership details of a contact for a group
     *
     * @param  int  $contactId id of the contact
     *
     * @param  int  $groupID   Id of a perticuler group
     *
     * @return object of group contact
     * @access public
     * @static
     */

    function &getMembershipDetail($contactId,$groupID)
    {
        $query = "SELECT * 
FROM civicrm_group_contact 
LEFT JOIN civicrm_subscription_history ON (civicrm_group_contact.contact_id = civicrm_subscription_history.contact_id) 
WHERE civicrm_group_contact.contact_id = %1
AND civicrm_group_contact.group_id = %2
AND civicrm_subscription_history.method ='Email' "  ;

        $params = array( 1 => array( $contactId, 'Integer' ),
                         2 => array( $groupID  , 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        $dao->fetch( );
        return $dao;
    }


    /**
     * Method to update the Status of Group member form 'Pending' to 'Added'
     *
     * @param  int  $contactId id of the contact
     *
     * @param  int  $groupID   Id of a perticuler group
     *
     * @param mixed $tracking   tracking information for history
     *
     * @return null If success
     * @access public
     * @static
     */

    function updateGroupMembershipStatus($contactId,$groupID,$method = 'Email',$tracking = null)
    {
        if(! isset($contactId) && ! isset($groupID)) {
            return CRM_Core_Error::fatal( "$contactId or $groupID should not empty" );
        } 

        $query = "UPDATE civicrm_group_contact 
SET civicrm_group_contact.status = 'Added'
WHERE civicrm_group_contact.contact_id = %1
AND civicrm_group_contact.group_id = %2";
        $params = array( 1 => array( $contactId, 'Integer' ),
                         2 => array( $groupID  , 'Integer' ) );
      
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );

        $params = array('contact_id' => $contactId,
                        'group_id'  => $groupID,
                        'status'    => 'Added',
                        'method'    => $method,
                        'tracking'  => $tracking
        );

        CRM_Contact_BAO_SubscriptionHistory::create($params);
        return null;    
        
    }
    
    /**
     * Method to get Group Id 
     *
     * @param  int  $groupContactID   Id of a perticuler group
     *
     *
     * @return groupID
     * @access public
     * @static
     */
    public static function getGroupId($groupContactID){
        $dao = new CRM_Contact_DAO_GroupContact();
        $dao->id = $groupContactID;
        $dao->find(true);
        return $dao->group_id; 
        
    }

    /**
     * takes an associative array and creates / removes
     * contacts from the groups
     *
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     * @param array $contactId    contact id
     *
     * @return none
     * @access public
     * @static
     */
    static function create( &$params, $contactId, $visibility = false, $method = 'Admin' ) 
    {
        $contactIds = array();
        $contactIds[] = $contactId;
        //if $visibility is true we are coming in via profile mean $method = 'Web'
        $ignorePermission = false; 
        if ( $visibility ) {
            $ignorePermission = true; 
        }
        if ($contactId) {
            $contactGroupList =& CRM_Contact_BAO_GroupContact::getContactGroup( $contactId, 'Added',
                                                                                null, false, $ignorePermission );
            if (is_array($contactGroupList)) {
                foreach ($contactGroupList as $key) {
                    $groupId = $key['group_id'];
                    $contactGroup[$groupId] = $groupId;
                }
            }
         }

        // get the list of all the groups
        $allGroup =& CRM_Contact_BAO_GroupContact::getGroupList( 0, $visibility );
        
        // this fix is done to prevent warning generated by array_key_exits incase of empty array is given as input
        if (!is_array($params)) {
            $params = array( );
        }
        
        // this fix is done to prevent warning generated by array_key_exits incase of empty array is given as input
        if ( ! isset( $contactGroup ) || ! is_array( $contactGroup ) ) {
            $contactGroup = array( );
        }

        // check which values has to be add/remove contact from group
        foreach ($allGroup as $key => $varValue) {
            if ( CRM_Utils_Array::value( $key, $params) && !array_key_exists($key, $contactGroup) ) {
                // add contact to group
                CRM_Contact_BAO_GroupContact::addContactsToGroup($contactIds, $key, $method);
            } else if ( !CRM_Utils_Array::value( $key, $params) && array_key_exists($key, $contactGroup) ) {
                // remove contact from group
                CRM_Contact_BAO_GroupContact::removeContactsFromGroup($contactIds, $key, $method);
            }
        }
    }

    static function isContactInGroup( $contactID, $groupID ) {
        require_once 'CRM/Utils/Rule.php';
        if ( ! CRM_Utils_Rule::positiveInteger( $contactID ) ||
             ! CRM_Utils_Rule::positiveInteger( $groupID ) ) {
            return false;
        }

        $params = array( 'group'      => array( $groupID => 1 ),
                         'contact_id' => $contactID,
                         'return.contact_id' => 1 );
        require_once 'api/v2/Contact.php';
        $contacts = civicrm_contact_search( $params );

        if ( ! empty( $contacts ) ) {
            return true;
        }
        return false;
    }

}




