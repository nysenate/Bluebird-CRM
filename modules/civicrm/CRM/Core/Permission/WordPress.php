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
class CRM_Core_Permission_WordPress {
    /**
     * get the current permission of this user
     *
     * @return string the permission of the user (edit or view or null)
     */
    public static function getPermission( ) {
        return CRM_Core_Permission::EDIT;
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
        return '( 1 )';
    }

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
        return CRM_Core_PseudoConstant::allGroup( $groupType, $excludeHidden );
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
    static function check( $str ) {
        $config = CRM_Core_Config::singleton( );
        $adminPerm = array( 'administer users',
                            'edit all contacts',
                            'view all contacts' );
        
        if ( $config->userFrameworkFrontend && in_array( $str, $adminPerm ) ) {
            return false;
        }
        return true;
    }

    /**
     * Get all the contact emails for users that have a specific permission
     *
     * @param string $permissionName name of the permission we are interested in
     *
     * @return string a comma separated list of email addresses
     */
    public static function permissionEmails( $permissionName ) {
        return '';
    }

    /**
     * Get all the contact emails for users that have a specific role
     *
     * @param string $roleName name of the role we are interested in
     *
     * @return string a comma separated list of email addresses
     */
    public static function roleEmails( $roleName ) {
        return '';
    }
    
}


