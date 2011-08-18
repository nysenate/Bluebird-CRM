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
 * This class holds all the Pseudo constants that are specific to the civimember component. This avoids
 * polluting the core class and isolates the mass mailer class
 */
class CRM_Member_PseudoConstant extends CRM_Core_PseudoConstant {

    /**
     * membership types
     * @var array
     * @static
     */
    private static $membershipType;

    /**
     * membership types
     * @var array
     * @static
     */
    private static $membershipStatus;

    /**
     * Get all the membership types
     *
     * @access public
     * @return array - array reference of all membership types if any
     * @static
     */
    public static function &membershipType($id = null, $force = false)
    {
        if ( ! self::$membershipType || $force ) {
            CRM_Core_PseudoConstant::populate( self::$membershipType,
                                               'CRM_Member_DAO_MembershipType',
                                               false, 'name', 'is_active', null, 'weight');
        }
        if ($id) {
            if (array_key_exists($id, self::$membershipType)) {
                return self::$membershipType[$id];
            } else {
                $result = null;
                return $result;
            }
        }
        return self::$membershipType;
    }

    /**
     * Get all the membership statuss
     *
     * @access public
     * @return array - array reference of all membership statuss if any
     * @static
     */
    public static function &membershipStatus($id = null, $cond = null, $column = 'name', $force = false)
    {
        if ( self::$membershipStatus === null ) {
            self::$membershipStatus = array( );
        }
        
        $cacheKey = $column;
        if ( $cond ) $cacheKey .= "_{$cond}"; 
        if ( !isset( self::$membershipStatus[$cacheKey] ) || $force ) {
            CRM_Core_PseudoConstant::populate( self::$membershipStatus[$cacheKey],
                                               'CRM_Member_DAO_MembershipStatus',
                                               false, $column, 'is_active', $cond, 'weight');
            
        }
        
        $value = null;
        if ( $id ) {
            $value = CRM_Utils_Array::value( $id, self::$membershipStatus[$cacheKey] );
        } else {
            $value = self::$membershipStatus[$cacheKey];
        }
        
        return $value;
    }
    
      /**
     * Flush given pseudoconstant so it can be reread from db
     * next time it's requested.
     *
     * @access public
     * @static
     *
     * @param boolean $name pseudoconstant to be flushed
     *
     */
    public static function flush( $name )
    {
        self::$$name = null;
    }  
}


