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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

class CRM_Contact_BAO_Contact_Permission {

    const
        NUM_CONTACTS_TO_INSERT = 200;

    /**
     * check if the logged in user has permissions for the operation type
     *
     * @param int    $id   contact id
     * @param string $type the type of operation (view|edit)
     *
     * @return boolean true if the user has permission, false otherwise
     * @access public
     * @static
     */
    static function allow( $id, $type = CRM_Core_Permission::VIEW ) 
    {
        $tables     = array( );
        $whereTables       = array( );
       
        # FIXME: push this somewhere below, to not give this permission so many rights
        $isDeleted = (bool) CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $id, 'is_deleted');
        if (CRM_Core_Permission::check('access deleted contacts') and $isDeleted) {
            return true;
        }

        //check permission based on relationship, CRM-2963
        if ( self::relationship( $id ) ) {
            return true;
        }

        require_once 'CRM/ACL/API.php';
        $permission = CRM_ACL_API::whereClause( $type, $tables, $whereTables );

        require_once "CRM/Contact/BAO/Query.php";
        $from       = CRM_Contact_BAO_Query::fromClause( $whereTables );

        $query = "
SELECT count(DISTINCT contact_a.id) 
       $from
WHERE contact_a.id = %1 AND $permission";
        $params = array( 1 => array( $id, 'Integer' ) );

        return ( CRM_Core_DAO::singleValueQuery( $query, $params ) > 0 ) ? true : false;
    }

    /**
     * fill the acl contact cache for this contact id if empty
     *
     * @param int     $id     contact id
     * @param string  $type   the type of operation (view|edit)
     * @param boolean $force  should we force a recompute
     *
     * @return void
     * @access public
     * @static
     */
    static function cache( $userID, $type = CRM_Core_Permission::VIEW, $force = false )
    {
        static $_processed = array( );

        if ( $type = CRM_Core_Permission::VIEW ) {
            $operationClause = " operation IN ( 'Edit', 'View' ) ";
            $operation       = 'View';
        } else {
            $operationClause = " operation = 'Edit' ";
            $operation       = 'Edit';
        }

        if ( ! $force ) {
            if ( CRM_Utils_Array::value( $userID, $_processed ) ) {
                return;
            }

            // run a query to see if the cache is filled
            $sql = "
SELECT count(id)
FROM   civicrm_acl_contact_cache
WHERE  user_id = %1
AND    $operationClause
";
            $params = array( 1 => array( $userID, 'Integer' ) );
            $count = CRM_Core_DAO::singleValueQuery( $sql, $params );
            if ( $count > 0 ) {
                $_processed[$userID] = 1;
                return;
            }
        }

        $tables      = array( );
        $whereTables = array( );

        require_once 'CRM/ACL/API.php';
        $permission = CRM_ACL_API::whereClause( $type, $tables, $whereTables, $userID );

        require_once "CRM/Contact/BAO/Query.php";
        $from       = CRM_Contact_BAO_Query::fromClause( $whereTables );

        $query = "
SELECT DISTINCT(contact_a.id) as id
       $from
WHERE $permission
";

        $values = array( );
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            $values[] = "( {$userID}, {$dao->id}, '{$operation}' )";
        }

        // now store this in the table
        while ( ! empty( $values ) ) {
            $processed = true;
            $input = array_splice( $values, 0, self::NUM_CONTACTS_TO_INSERT );
            $str   = implode( ',', $input );
            $sql = "REPLACE INTO civicrm_acl_contact_cache ( user_id, contact_id, operation ) VALUES $str;";
            CRM_Core_DAO::executeQuery( $sql );
        }
        CRM_Core_DAO::executeQuery('DELETE FROM civicrm_acl_contact_cache WHERE contact_id IN (SELECT id FROM civicrm_contact WHERE is_deleted = 1)');

        $_processed[$userID] = 1;
        return;
    }

    static function cacheClause( $contactAlias = 'contact_a', $contactID = null ) {
        if ( CRM_Core_Permission::check( 'view all contacts' ) ) {
            if (is_array($contactAlias)) {
                $wheres = array();
                foreach ($contactAlias as $alias) {
                    // CRM-6181
                    $wheres[] = "$alias.is_deleted = 0";
                }
                return array(null, '(' . implode(' AND ', $wheres) . ')');
            } else {
                // CRM-6181
                return array(null, "$contactAlias.is_deleted = 0");
            }
        }

        $session = CRM_Core_Session::singleton( );
        $contactID =  $session->get( 'userID' );
        if ( ! $contactID ) {
            $contactID = 0;
        }
        $contactID = CRM_Utils_Type::escape( $contactID, 'Integer' );

        self::cache( $contactID );
        
        if( is_array($contactAlias) && !empty($contactAlias) ) {
            //More than one contact alias
            $clauses = array();
            foreach( $contactAlias as $k => $alias ) {
                $clauses[] = " INNER JOIN civicrm_acl_contact_cache aclContactCache_{$k} ON {$alias}.id = aclContactCache_{$k}.contact_id AND aclContactCache_{$k}.user_id = $contactID ";  
            }
            
            $fromClause = implode(" ", $clauses );
            $whereClase = null;
            
        } else {
            $fromClause = " INNER JOIN civicrm_acl_contact_cache aclContactCache ON {$contactAlias}.id = aclContactCache.contact_id ";
            $whereClase = " aclContactCache.user_id = $contactID ";
        }

        return array( $fromClause , $whereClase );
    }


    /**
      * Function to get the permission base on its relationship
      * 
      * @param int $selectedContactId contact id of selected contact
      * @param int $contactId contact id of the current contact 
      *
      * @return booleab true if logged in user has permission to view
      * selected contact record else false
      * @static
      */
    static function relationship( $selectedContactID, $contactID = null ) 
    {
        $session   = CRM_Core_Session::singleton( );
        if ( ! $contactID ) {
            $contactID =  $session->get( 'userID' );
            if ( ! $contactID ) {
                return false;
            }
        }
        if (  $contactID == $selectedContactID ) {
            return true;
        } else {
            $query = "
SELECT id
FROM   civicrm_relationship
WHERE  (( contact_id_a = %1 AND contact_id_b = %2 AND is_permission_a_b = 1 ) OR
        ( contact_id_a = %2 AND contact_id_b = %1 AND is_permission_b_a = 1 )) AND
       (id NOT IN (SELECT id FROM civicrm_contact WHERE is_deleted = 1))
";
            $params = array( 1 => array( $contactID        , 'Integer' ),
                             2 => array( $selectedContactID, 'Integer' ) );
            return CRM_Core_DAO::singleValueQuery( $query, $params );
        }
    }


    static function validateOnlyChecksum( $contactID, &$form ) {
        // check if this is of the format cs=XXX
        require_once 'CRM/Contact/BAO/Contact/Utils.php';
        if ( !  CRM_Contact_BAO_Contact_Utils::validChecksum( $contactID,
                                                              CRM_Utils_Request::retrieve( 'cs', 'String' , $form, false ) ) ) {
            $config = CRM_Core_Config::singleton( );
            CRM_Core_Error::statusBounce( ts( 'You do not have permission to edit this contact record. Contact the site administrator if you need assistance.' ),
                                          $config->userFrameworkBaseURL );
            // does not come here, we redirect in the above statement
        }
        return true;
    }

    static function validateChecksumContact( $contactID, &$form ) {
        if ( ! self::allow( $contactID, CRM_Core_Permission::EDIT ) ) {
            // check if this is of the format cs=XXX
            return self::validateOnlyChecksum( $contactID, $form );
        }
        return true;
    }

}
