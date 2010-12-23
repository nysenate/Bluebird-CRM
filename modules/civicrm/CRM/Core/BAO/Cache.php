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

require_once 'CRM/Core/DAO/Cache.php';

/**
 * BAO object for crm_log table
 */

class CRM_Core_BAO_Cache extends CRM_Core_DAO_Cache
{
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

    static function setItem( &$data,
                             $group, $path, $componentID = null ) {
        $dao = new CRM_Core_DAO_Cache( );

        $dao->group_name = $group;
        $dao->path  = $path;
        $dao->component_id = $componentID;

        $dao->find( true );
        $dao->data         = serialize( $data );
        $dao->created_date = date( 'Ymdhis' );

        // CRM_Core_Error::debug_var( "Saving $group, $path on {$dao->created_date}", $data );
        $dao->save( );
        
        $dao->free( );
    }

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

    static function storeSessionToCache( $names,
                                         $resetSession = true ) {
        // CRM_Core_Error::debug_var( 'names in store', $names );
        foreach ( $names as $key => $sessionName ) {
            if ( is_array( $sessionName ) ) {
                if ( ! empty( $_SESSION[$sessionName[0]][$sessionName[1]] ) ) {
                    self::setItem( $_SESSION[$sessionName[0]][$sessionName[1]],
                                   'CiviCRM Session',
                                   "{$sessionName[0]}_{$sessionName[1]}" );
                    // CRM_Core_Error::debug_var( "session value for: {$sessionName[0]}_{$sessionName[1]}",
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
                    // CRM_Core_Error::debug_var( "session value for: {$sessionName}",
                    // $_SESSION[$sessionName] );
                    if ( $resetSession ) {
                        $_SESSION[$sessionName] = null;
                        unset( $_SESSION[$sessionName] );
                    }
                }
            }
        }

        // CRM_Core_Error::debug_var( 'SESSION STATE STORE', $_SESSION );
        self::cleanupCache( );
    }

    static function restoreSessionFromCache( $names ) {
        // CRM_Core_Error::debug_var( 'names in restore', $names );
        foreach ( $names as $key => $sessionName ) {
            if ( is_array( $sessionName ) ) {
                $value = self::getItem( 'CiviCRM Session',
                                        "{$sessionName[0]}_{$sessionName[1]}" );
                if ( $value ) {
                    // CRM_Core_Error::debug( "session value for: {$sessionName[0]}_{$sessionName[1]}", $value ); 
                    $_SESSION[$sessionName[0]][$sessionName[1]] = $value;
                } else {
                    // CRM_Core_Error::debug_var( "session value for: {$sessionName[0]}_{$sessionName[1]} is null", $value );
                }
            } else {
                $value = self::getItem( 'CiviCRM Session',
                                        $sessionName );
                if ( $value ) {
                    // CRM_Core_Error::debug( "session value for: {$sessionName}", $value );
                    $_SESSION[$sessionName] = $value;
                } else {
                    // CRM_Core_Error::debug_var( "session value for: {$sessionName} is null", $value );
                }
            }
        }

        // CRM_Core_Error::debug_var( 'SESSION STATE RESTORE', $_SESSION );
        // CRM_Core_Error::debug_var( 'REQUEST', $_REQUEST );
    }

    static function cleanupCache( ) {
        // clean up the session cache every $cacheCleanUpNumber probabilistically
        $cacheCleanUpNumber     = 1396;

        // clean up all sessions older than $cacheTimeIntervalDays days
        $cacheTimeIntervalDays  = 2;

        if ( mt_rand( 1, 100000 ) % 1396 == 0 ) {
            $sql = "
DELETE FROM civicrm_cache
WHERE       group_name = 'CiviCRM Session'
AND         created_date < date_sub( NOW( ), INTERVAL $cacheTimeIntervalDays day )
";
            CRM_Core_DAO::executeQuery( $sql );
        }
    }
                                         
}