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

require_once 'CRM/Core/DAO/Cache.php';

/**
 * BAO object for civicrm_cache table. This is a database cache and is persisted across sessions. Typically we use
 * this to store meta data (like profile fields, custom fields etc).
 *
 * The group_name column is used for grouping together all cache elements that logically belong to the same set.
 * Thus all session cache entries are grouped under 'CiviCRM Session'. This allows us to delete all entries of
 * a specific group if needed.
 *
 * The path column allows us to differentiate between items in that group. Thus for the session cache, the path is
 * the unique form name for each form (per user)
 */

class CRM_Core_BAO_Cache extends CRM_Core_DAO_Cache
{

    /**
     * Retrieve an item from the DB cache
     *
     * @param string $group (required) The group name of the item
     * @param string $path  (required) The path under which this item is stored
     * @param int    $componentID The optional component ID (so componenets can share the same name space)
     *
     * @return object The data if present in cache, else null
     * @static
     * @access public
     */
    static function &getItem( $group, $path, $componentID = null ) {
        $dao = new CRM_Core_DAO_Cache( );

        $dao->group_name = $group;
        $dao->path  = $path;
        $dao->component_id = $componentID;

        $data = null;
        if ( $dao->find( true ) ) {
            $data = unserialize( $dao->data );
        }
        $dao->free( );
        return $data;
    }

    /**
     * Store an item in the DB cache
     *
     * @param object $data  (required) A reference to the data that will be serialized and stored
     * @param string $group (required) The group name of the item
     * @param string $path  (required) The path under which this item is stored
     * @param int    $componentID The optional component ID (so componenets can share the same name space)
     *
     * @return void
     * @static
     * @access public
     */
    static function setItem( &$data,
                             $group, $path, $componentID = null ) {
        $dao = new CRM_Core_DAO_Cache( );

        $dao->group_name = $group;
        $dao->path  = $path;
        $dao->component_id = $componentID;

        $dao->find( true );
        $dao->data         = serialize( $data );
        $dao->created_date = date( 'Ymdhis' );

        $dao->save( );
        
        $dao->free( );
    }

    /**
     * Delete all the cache elements that belong to a group OR
     * delete the entire cache if group is not specified
     *
     * @param string $group The group name of the entries to be deleted
     * 
     * @return void
     * @static
     * @access public
     */
    static function deleteGroup( $group = null ) {
        $dao = new CRM_Core_DAO_Cache( );
        
        if ( ! empty( $group ) ) {
            $dao->group_name = $group;
        }
        $dao->delete( );

        // also reset ACL Cache
        require_once 'CRM/ACL/BAO/Cache.php';
        CRM_ACL_BAO_Cache::resetCache( );

        // also reset memory cache if any
        CRM_Utils_System::flushCache( );
    }

    /**
     * The next two functions are internal functions used to store and retrieve session from
     * the database cache. This keeps the session to a limited size and allows us to
     * create separate session scopes for each form in a tab
     *
     */

    /**
     * This function takes entries from the session array and stores it in the cache.
     * It also deletes the entries from the $_SESSION object (for a smaller session size)
     *
     * @param array $names Array of session values that should be persisted
     *                     This is either a form name + qfKey or just a form name
     *                     (in the case of profile)
     * @param boolean $resetSession Should session state be reset on completion of DB store?
     *
     * @return void
     * @static
     * @access private
     */
    static function storeSessionToCache( $names,
                                         $resetSession = true ) {
        foreach ( $names as $key => $sessionName ) {
            if ( is_array( $sessionName ) ) {
                if ( ! empty( $_SESSION[$sessionName[0]][$sessionName[1]] ) ) {
                    self::setItem( $_SESSION[$sessionName[0]][$sessionName[1]],
                                   'CiviCRM Session',
                                   "{$sessionName[0]}_{$sessionName[1]}" );
                    // $_SESSION[$sessionName[0]][$sessionName[1]] );
                    if ( $resetSession ) {
                        $_SESSION[$sessionName[0]][$sessionName[1]] = null;
                        unset( $_SESSION[$sessionName[0]][$sessionName[1]] );
                    }
                }
            } else {
                if ( ! empty( $_SESSION[$sessionName] ) ) {
                    self::setItem( $_SESSION[$sessionName],
                                   'CiviCRM Session',
                                   $sessionName );
                    // $_SESSION[$sessionName] );
                    if ( $resetSession ) {
                        $_SESSION[$sessionName] = null;
                        unset( $_SESSION[$sessionName] );
                    }
                }
            }
        }

        self::cleanupCache( );
    }

    /* Retrieve the session values from the cache and populate the $_SESSION array
     *
     * @param array $names Array of session values that should be persisted
     *                     This is either a form name + qfKey or just a form name
     *                     (in the case of profile)
     *
     * @return void
     * @static
     * @access private
     */
    static function restoreSessionFromCache( $names ) {
        foreach ( $names as $key => $sessionName ) {
            if ( is_array( $sessionName ) ) {
                $value = self::getItem( 'CiviCRM Session',
                                        "{$sessionName[0]}_{$sessionName[1]}" );
                if ( $value ) {
                    $_SESSION[$sessionName[0]][$sessionName[1]] = $value;
                }
            } else {
                $value = self::getItem( 'CiviCRM Session',
                                        $sessionName );
                if ( $value ) {
                    $_SESSION[$sessionName] = $value;
                }
            }
        }
    }

    /**
     * Do periodic cleanup of the CiviCRM session table. Also delete all session cache entries
     * which are a couple of days old. This keeps the session cache to a manageable size
     *
     * @return void
     * @static
     * @access private
     */
    static function cleanupCache( ) {
        // clean up the session cache every $cacheCleanUpNumber probabilistically
        $cacheCleanUpNumber     = 1396;

        // clean up all sessions older than $cacheTimeIntervalDays days
        $cacheTimeIntervalDays  = 2;

        if ( mt_rand( 1, 100000 ) % 1396 == 0 ) {

            // delete all PrevNext caches
            require_once 'CRM/Core/BAO/PrevNextCache.php';
            CRM_Core_BAO_PrevNextCache::cleanupCache( );

            $sql = "
DELETE FROM civicrm_cache
WHERE       group_name = 'CiviCRM Session'
AND         created_date < date_sub( NOW( ), INTERVAL $cacheTimeIntervalDays day )
";
            CRM_Core_DAO::executeQuery( $sql );

            // also delete all the action temp tables
            // that were created the same interval ago
            $dao = new CRM_Core_DAO( );
            $query = "
SELECT TABLE_NAME as tableName
FROM   INFORMATION_SCHEMA.TABLES
WHERE  TABLE_SCHEMA = %1 
AND    ( TABLE_NAME LIKE 'civicrm_task_action_temp_%' 
 OR      TABLE_NAME LIKE 'civicrm_export_temp_%' )
AND    CREATE_TIME < date_sub( NOW( ), INTERVAL $cacheTimeIntervalDays day )
";

            $params = array( 1 => array( $dao->database(), 'String' ) );
            $tableDAO = CRM_Core_DAO::executeQuery( $query, $params );
            $tables = array();
            while ( $tableDAO->fetch() ) {
                $tables[] = $tableDAO->tableName;
            }
            if ( !empty( $tables ) ) {
                $table = implode(',', $tables);
                // drop leftover temporary tables
                CRM_Core_DAO::executeQuery( "DROP TABLE $table" );
            }

        }
    }
                                         
}