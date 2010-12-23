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

class CRM_Core_Lock {

    // lets have a 1 second timeout for now
    const TIMEOUT = 1;

    protected $_hasLock = false;

    protected $_name;

    function __construct( $name, $timeout = null ) {
        $config         = CRM_Core_Config::singleton( );
        $dsnArray       = DB::parseDSN($config->dsn);
        $database       = $dsnArray['database'];
        $domainID       = CRM_Core_Config::domainID( );
        $this->_name    = $database . '.' . $domainID . '.' . $name;
        $this->_timeout = $timeout ? $timeout : self::TIMEOUT;

        $this->acquire( );
    }

    function __destruct( ) {
        $this->release( );
    }

    function acquire( ) {
        if ( ! $this->_hasLock ) {
            $query  = "SELECT GET_LOCK( %1, %2 )";
            $params = array( 1 => array( $this->_name   , 'String'  ),
                             2 => array( $this->_timeout, 'Integer' ) );
            $res = CRM_Core_DAO::singleValueQuery( $query, $params );
            if ( $res ) {
                $this->_hasLock = true;
            }
        }
        return $this->_hasLock;
    }

    function release( ) {
        if ( $this->_hasLock ) {
            $this->_hasLock = false;

            $query = "SELECT RELEASE_LOCK( %1 )";
            $params = array( 1 => array( $this->_name, 'String' ) );
            return CRM_Core_DAO::singleValueQuery( $query, $params );
        }
    }

    function isFree( ) {
        $query = "SELECT IS_FREE_LOCK( %1 )";
        $params = array( 1 => array( $this->_name, 'String' ) );
        return CRM_Core_DAO::singleValueQuery( $query, $params );
    }

    function isAcquired( ) {
        return $this->_hasLock;
    }

}


