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
 * Cache is an empty base object, we'll modify the scheme when we have different caching schemes
 *
 */

class CRM_Utils_Cache {

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;

    /**
     * Constructor
     *
     * @return void
     */
    function __construct( ) {
    }

    /**
     * singleton function used to manage this object
     *
     * @return object
     * @static
     *
     */
    static function &singleton( ) {
        if (self::$_singleton === null ) {
            if ( defined( 'CIVICRM_USE_MEMCACHE' ) &&
                 CIVICRM_USE_MEMCACHE ) {
                require_once 'CRM/Utils/Cache/Memcache.php';
                $settings = self::getCacheSettings( );
                self::$_singleton = new CRM_Utils_Cache_Memcache( $settings['host'],
                                                                  $settings['port'],
                                                                  $settings['timeout'],
                                                                  $settings['prefix'] );
            } else if ( defined( 'CIVICRM_USE_ARRAYCACHE' ) && 
                        CIVICRM_USE_ARRAYCACHE ) {
                require_once 'CRM/Utils/Cache/ArrayCache.php';
                self::$_singleton = new CRM_Utils_Cache_ArrayCache();
            } else {
                self::$_singleton = new CRM_Utils_Cache( );
            }
        }
        return self::$_singleton;
    }

    /**
     * Get cache relevant settings
     *
     * @return array
     *   associative array of settings for the cache
     * @static
     */
    static function getCacheSettings( ) {
        if ( !defined( 'CIVICRM_USE_MEMCACHE' ) or !CIVICRM_USE_MEMCACHE ) {
          return array();
        }
        $defaults =
            array (
            'host'    =>  'localhost',
            'port'    =>  11211,
            'timeout' =>  3600,
            'prefix'  =>  ''
            );

        if ( defined(  'CIVICRM_MEMCACHE_HOST' ) ) {
            $defaults['host'] = CIVICRM_MEMCACHE_HOST;
        }

        if ( defined(  'CIVICRM_MEMCACHE_PORT' ) ) {
            $defaults['port'] = CIVICRM_MEMCACHE_PORT;
        }

        if ( defined(  'CIVICRM_MEMCACHE_TIMEOUT' ) ) {
            $defaults['timeout'] = CIVICRM_MEMCACHE_TIMEOUT;
        }

        if ( defined(  'CIVICRM_MEMCACHE_PREFIX' ) ) {
            $defaults['prefix'] = CIVICRM_MEMCACHE_PREFIX;
        }

        return $defaults;
    }

    function set( $key, &$value ) {
        return false;
    }

    function get( $key ) {
        return null;
    }


    function delete( $key ) {
        return false;
    }

    function flush( ) {
        return false;
    }

}
