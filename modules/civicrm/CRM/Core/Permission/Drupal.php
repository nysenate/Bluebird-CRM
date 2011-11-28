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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 *
 */
class CRM_Core_Permission_Drupal {
    /**
     * is this user someone with access for the entire system
     *
     * @var boolean
     */
    static protected $_viewAdminUser = false;
    static protected $_editAdminUser = false;

    /**
     * am in in view permission or edit permission?
     * @var boolean
     */
    static protected $_viewPermission = false;
    static protected $_editPermission = false;

    /**
     * the current set of permissioned groups for the user
     *
     * @var array
     */
    static protected $_viewPermissionedGroups;
    static protected $_editPermissionedGroups;

    /**
     * Get all groups from database, filtered by permissions
     * for this user
     *
     * @param string $groupType     type of group(Access/Mailing) 
     * @param boolen $excludeHidden exclude hidden groups.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all groups.
     *
     */
    public static function &group( $groupType = null, $excludeHidden = true ) {
        if ( ! isset( self::$_viewPermissionedGroups ) ) {
            self::$_viewPermissionedGroups = self::$_editPermissionedGroups = array( );

            $groups =& CRM_Core_PseudoConstant::allGroup( $groupType, $excludeHidden );

            if ( self::check( 'edit all contacts' ) ) {
                // this is the most powerful permission, so we return
                // immediately rather than dilute it further
                self::$_editAdminUser          = self::$_viewAdminUser  = true;
                self::$_editPermission         = self::$_viewPermission = true;
                self::$_editPermissionedGroups = $groups;
                self::$_viewPermissionedGroups = $groups;
                return self::$_viewPermissionedGroups;
            } else if ( self::check( 'view all contacts' ) ) {
                self::$_viewAdminUser          = true;
                self::$_viewPermission         = true;
                self::$_viewPermissionedGroups = $groups;
            }

            require_once 'CRM/ACL/API.php';

            $ids = CRM_ACL_API::group( CRM_Core_Permission::VIEW, null, 'civicrm_saved_search', $groups );
            foreach ( array_values( $ids ) as $id ) {
                $title = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group', $id, 'title' );
                self::$_viewPermissionedGroups[$id] = $title;
                self::$_viewPermission              = true; 
            }

            $ids = CRM_ACL_API::group( CRM_Core_Permission::EDIT, null, 'civicrm_saved_search', $groups );
            foreach ( array_values( $ids ) as $id ) {
                $title = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group', $id, 'title' );
                self::$_editPermissionedGroups[$id] = $title;
                self::$_viewPermissionedGroups[$id] = $title;
                self::$_editPermission              = true; 
                self::$_viewPermission              = true; 
            }
        }

        return self::$_viewPermissionedGroups;
    }

    /**
     * Get group clause for this user
     *
     * @param int $type the type of permission needed
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     * @param  array $whereTables (reference ) add the tables that are needed for the where clause
     *
     * @return string the group where clause for this user
     * @access public
     */
    public static function groupClause( $type, &$tables, &$whereTables ) {
        if (! isset( self::$_viewPermissionedGroups ) ) {
            self::group( );
        }

        if ( $type == CRM_Core_Permission::EDIT ) {
            if ( self::$_editAdminUser ) {
                $clause = ' ( 1 ) ';
            } else if ( empty( self::$_editPermissionedGroups ) ) {
                $clause = ' ( 0 ) ';
            } else {
                $clauses = array( );
                $groups = implode( ', ', self::$_editPermissionedGroups );
                $clauses[] = ' ( civicrm_group_contact.group_id IN ( ' . implode( ', ', array_keys( self::$_editPermissionedGroups ) ) .
                    " ) AND civicrm_group_contact.status = 'Added' ) ";
                $tables['civicrm_group_contact'] = 1;
                $whereTables['civicrm_group_contact'] = 1;
                
                // foreach group that is potentially a saved search, add the saved search clause
                foreach ( array_keys( self::$_editPermissionedGroups ) as $id ) {
                    $group     = new CRM_Contact_DAO_Group( );
                    $group->id = $id;
                    if ( $group->find( true ) && $group->saved_search_id ) {
                        require_once 'CRM/Contact/BAO/SavedSearch.php';
                        $clause    = CRM_Contact_BAO_SavedSearch::whereClause( $group->saved_search_id,
                                                                               $tables,
                                                                               $whereTables );
                        if ( trim( $clause ) ) {
                            $clauses[] = $clause;
                        }
                    }
                }
                $clause = ' ( ' . implode( ' OR ', $clauses ) . ' ) ';
            }
        } else {
            if ( self::$_viewAdminUser ) {
                $clause = ' ( 1 ) ';
            } else if ( empty( self::$_viewPermissionedGroups ) ) {
                $clause = ' ( 0 ) ';
            } else {
                $clauses = array( );
                $groups = implode( ', ', self::$_viewPermissionedGroups );
                $clauses[] = ' ( civicrm_group_contact.group_id IN (' . implode( ', ', array_keys( self::$_viewPermissionedGroups ) ) .
                    " ) AND civicrm_group_contact.status = 'Added' ) ";
                $tables['civicrm_group_contact'] = 1;
                $whereTables['civicrm_group_contact'] = 1;

        
                // foreach group that is potentially a saved search, add the saved search clause
                foreach ( array_keys( self::$_viewPermissionedGroups ) as $id ) {
                    $group     = new CRM_Contact_DAO_Group( );
                    $group->id = $id;
                    if ( $group->find( true ) && $group->saved_search_id ) {
                        require_once 'CRM/Contact/BAO/SavedSearch.php';
                        $clause    = CRM_Contact_BAO_SavedSearch::whereClause( $group->saved_search_id,
                                                                               $tables,
                                                                               $whereTables );
                        if ( trim( $clause ) ) {
                            $clauses[] = $clause;
                        }
                    }
                }

                $clause = ' ( ' . implode( ' OR ', $clauses ) . ' ) ';
            }
        }
        
        return $clause;
    }

    /**
     * get the current permission of this user
     *
     * @return string the permission of the user (edit or view or null)
     */
    public static function getPermission( ) {
        self::group( );

        if ( self::$_editPermission ) {
            return CRM_Core_Permission::EDIT;
        } else if ( self::$_viewPermission ) {
            return CRM_Core_Permission::VIEW;
        }
        return null;
    }
    
    /**
     * Get the permissioned where clause for the user
     *
     * @param int $type the type of permission needed
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     * @param  array $whereTables (reference ) add the tables that are needed for the where clause
     *
     * @return string the group where clause for this user
     * @access public
     */
    public static function whereClause( $type, &$tables, &$whereTables ) {
        self::group( );

        return self::groupClause( $type, $tables, $whereTables );
    }

    /**
     * given a permission string, check for access requirements
     *
     * @param string $str the permission to check
     *
     * @return boolean true if yes, else false
     * @static
     * @access public
     */
    static function check( $str, $contactID = null ) {
        if ( function_exists( 'user_access' ) ) {
            return user_access( $str ) ? true : false;
        }
        return true;
        /**
         * lets introduce acl in 2.1
        static $isAdmin = null;
        if ( $isAdmin === null ) {
            $session = CRM_Core_Session::singleton( );
            $isAdmin = $session->get( 'ufID' ) == 1 ? true : false;
        }
        require_once 'CRM/ACL/API.php';
        return ( $isAdmin) ? true : CRM_ACL_API::check( $str, $contactID );
        */
    }

    /**
     * Get all the contact emails for users that have a specific permission
     *
     * @param string $permissionName name of the permission we are interested in
     *
     * @return string a comma separated list of email addresses
     */
    public static function permissionEmails( $permissionName ) {
        static $_cache = array( );
        
        if ( isset( $_cache[$permissionName] ) ) {
            return $_cache[$permissionName];
        }
        
        $uids = array( );
        $sql = "
SELECT     {users}.uid, {permission}.perm
FROM       {users}
LEFT  JOIN {users_roles} ON {users}.uid = {users_roles}.uid
INNER JOIN {permission}  ON ( {permission}.rid = {users_roles}.rid OR {permission}.rid = 2 )
WHERE      {permission}.perm LIKE '%%{$permissionName}%%'
AND        {users}.status = 1
";

        $query = db_query( $sql );
        while ( $result = db_fetch_object( $query ) ) {
            $uids[] = $result->uid;
        }

        $_cache[$permissionName] = self::getContactEmails( $uids );
        return $_cache[$permissionName];
    }

    /**
     * Get all the contact emails for users that have a specific role
     *
     * @param string $roleName name of the role we are interested in
     *
     * @return string a comma separated list of email addresses
     */
    public static function roleEmails( $roleName ) {
        static $_cache = array( );
        
        if ( isset( $_cache[$roleName] ) ) {
            return $_cache[$roleName];
        }

        $uids = array( );
        $sql = "
SELECT     {users}.uid, {permission}.perm
FROM       {users}
LEFT  JOIN {users_roles} ON {users}.uid = {users_roles}.uid
INNER JOIN {role}        ON ( {role}.rid = {users_roles}.rid OR {role}.rid = 2 )
WHERE      {role}. name LIKE '%%{$roleName}%%'
AND        {users}.status = 1
";

        $query = db_query( $sql );
        while ( $result = db_fetch_object( $query ) ) {
            $uids[] = $result->uid;
        }

        $_cache[$roleName] = self::getContactEmails( $uids );
        return $_cache[$roleName];
    }
    
    static function getContactEmails( $uids ) {
        if ( empty( $uids ) ) {
            return '';
        }
        $uidString = implode( ',', $uids );
        $sql = "
SELECT     e.email
FROM       civicrm_contact c
INNER JOIN civicrm_email e     ON ( c.id = e.contact_id AND e.is_primary = 1 )
INNER JOIN civicrm_uf_match uf ON ( c.id = uf.contact_id )
WHERE      c.is_deceased = 0
AND        c.is_deleted  = 0
AND        uf.uf_id IN ( $uidString )
";

        $dao = CRM_Core_DAO::executeQuery( $sql );

        $emails = array( );
        while ( $dao->fetch( ) ) {
            $emails[] = $dao->email;
        }

        return implode( ', ', $emails );
    }

}
