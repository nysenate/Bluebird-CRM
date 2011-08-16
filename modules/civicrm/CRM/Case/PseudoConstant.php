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
 * This class holds all the Pseudo constants that are specific for CiviCase.
 *
 */
class CRM_Case_PseudoConstant extends CRM_Core_PseudoConstant 
{

    /**
     * case statues
     * @var array
     * @static
     */
    static $caseStatus = array( );

    /**
     * redaction rules
     * @var array
     * @static
     */
    static $redactionRule;

    /**
     * case type
     * @var array
     * @static
     */
    static $caseType = array( );
    
    /**
     * Encounter Medium
     * @var array
     * @static
     */
    static $encounterMedium = array( );
    
    /**
     * activity type
     * @var array
     * @static
     */
    static $activityTypeList = array( );

    /**
     * case type
     * @var array
     * @static
     */
    static $caseTypePair = array( );

    /**
     * Get all the case statues
     *
     * @access public
     * @return array - array reference of all case statues
     * @static
     */
    public static function caseStatus( $column = 'label', $onlyActive = true )
    {
        $cacheKey = "{$column}_".(int)$onlyActive;
        if ( !isset( self::$caseStatus[$cacheKey] ) ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$caseStatus[$cacheKey] = CRM_Core_OptionGroup::values( 'case_status', 
                                                                         false, false, false, null, 
                                                                         $column, $onlyActive );
        }
        
        return self::$caseStatus[$cacheKey];
    }
    
    /**
     * Get all the redaction rules
     *
     * @access public
     * @return array - array reference of all redaction rules
     * @static
     */

    public static function redactionRule( $filter = null )
    {
        // if ( ! self::$redactionRule ) {
            self::$redactionRule = array( );
                        
            if( $filter === 0) {
                $condition = "  AND (v.filter = 0 OR v.filter IS NULL)";
                
            } elseif ( $filter === 1) {
                $condition = "  AND  v.filter = 1";
            } elseif ( $filter === null) {
                $condition = null;
            } 
            
            require_once 'CRM/Core/OptionGroup.php';
            self::$redactionRule = CRM_Core_OptionGroup::values('redaction_rule', true, false, false, $condition);
            // }
        return self::$redactionRule;
    }

    /**
     * Get all the case type
     *
     * @access public
     * @return array - array reference of all case type
     * @static
     */
    public static function caseType( $column = 'label', $onlyActive = true )
    {
        $cacheKey = "{$column}_".(int)$onlyActive;
        if ( !isset( self::$caseType[$cacheKey] ) ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$caseType[$cacheKey] =  CRM_Core_OptionGroup::values( 'case_type', 
                                                                        false, false, false, null, 
                                                                        $column, $onlyActive );
        }
        
        return self::$caseType[$cacheKey];
    }
    
    /**
     * Get all the Encounter Medium 
     *
     * @access public
     * @return array - array reference of all Encounter Medium.
     * @static
     */
    public static function encounterMedium( $column = 'label', $onlyActive = true )
    {
        $cacheKey = "{$column}_".(int)$onlyActive;
        if ( !isset( self::$encounterMedium[$cacheKey] ) ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$encounterMedium[$cacheKey] =  CRM_Core_OptionGroup::values( 'encounter_medium', 
                                                                               false, false, false, null, 
                                                                               $column, $onlyActive );
        }
        
        return self::$encounterMedium[$cacheKey];
    }
    
    /**
     * Get all Activty types for the CiviCase component
     *
     * The static array activityType is returned
     * @param boolean $indexName - true return activity name in array
     * key else activity id as array key.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all activty types.
     */
    public static function activityType( $indexName = true, $all = false )
    {
        $cache = (int) $indexName . '_' . (int) $all;
        
        if ( ! array_key_exists($cache, self::$activityTypeList) ) {
            self::$activityTypeList[$cache] = array( );

            $query = "
              SELECT  v.label as label ,v.value as value, v.name as name, v.description as description
              FROM   civicrm_option_value v,
                     civicrm_option_group g
              WHERE  v.option_group_id = g.id
                     AND  g.name         = 'activity_type'
                     AND  v.is_active    = 1 
                     AND  g.is_active    = 1";
            
            if ( ! $all ) {
                $componentId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Component',
                                                            'CiviCase',
                                                            'id', 'name' );
                $query      .= " AND  v.component_id = {$componentId} ";
            }

            $query .= "  ORDER BY v.weight";
            
            $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );

            $activityTypes = array();
            while( $dao->fetch() ) {
                if ( $indexName ) {
                    $index = $dao->name;
                } else {
                    $index = $dao->value;
                }
                $activityTypes[$index] = array();
                $activityTypes[$index]['id']          = $dao->value; 
                $activityTypes[$index]['label']       = $dao->label; 
                $activityTypes[$index]['name']        = $dao->name;
                $activityTypes[$index]['description'] = $dao->description;
            }
            self::$activityTypeList[$cache] = $activityTypes;
        }
        return self::$activityTypeList[$cache];
    }

    /**
     * Get the associated case type name/id, given a case Id
     *
     * @access public
     * @return array - array reference of all case type name/id
     * @static
     */
    public static function caseTypeName( $caseId , $column = 'name')
    {
        if ( !$caseId ) {
            return false;
        }

        require_once('CRM/Case/BAO/Case.php');
        if ( ! array_key_exists($caseId, self::$caseTypePair) || empty(self::$caseTypePair[$caseId][$column]) ) {
            $caseTypes   = self::caseType( $column );
            $caseTypeIds = CRM_Core_DAO::getFieldValue( 'CRM_Case_DAO_Case',
                                                        $caseId,
                                                        'case_type_id' );
            $caseTypeId  = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                    trim($caseTypeIds, 
                                         CRM_Core_DAO::VALUE_SEPARATOR ) );
            $caseTypeId  = $caseTypeId[0];
            
            self::$caseTypePair[$caseId][$column] = array( 'id'   => $caseTypeId,
                                                  'name' => $caseTypes[$caseTypeId] );
        }

        return self::$caseTypePair[$caseId][$column];
    }

}
