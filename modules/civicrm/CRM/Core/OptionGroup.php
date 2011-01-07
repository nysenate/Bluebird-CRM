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

class CRM_Core_OptionGroup 
{
    static $_values = array( );

    /*
     * $_domainIDGroups array maintains the list of option groups for whom 
     * domainID is to be considered.
     *
     */
    static $_domainIDGroups = array( 'from_email_address', 
                                     'grant_type' );

    static function &valuesCommon( $dao, $flip = false, $grouping = false,
                                   $localize = false, $valueColumnName = 'label' ) 
    {
        self::$_values = array( );

        while ( $dao->fetch( ) ) {
            if ( $flip ) {
                if ( $grouping ) {
                    self::$_values[$dao->value] = $dao->grouping;
                } else {
                    self::$_values[$dao->{$valueColumnName}] = $dao->value;
                }
            } else {
                if ( $grouping ) {
                    self::$_values[$dao->{$valueColumnName}] = $dao->grouping;
                } else {
                    self::$_values[$dao->value] = $dao->{$valueColumnName};
                }
            }
        }
        if ($localize) {
            $i18n =& CRM_Core_I18n::singleton();
            $i18n->localizeArray(self::$_values);
        }
        return self::$_values;
    }

    static function &values( $name, $flip = false, $grouping = false,
                             $localize = false, $condition = null,
                             $valueColumnName = 'label', $onlyActive = true ) 
    {
        $cacheKey = "CRM_OG_{$name}_{$flip}_{$grouping}_{$localize}_{$condition}_{$valueColumnName}_{$onlyActive}";
        $cache =& CRM_Utils_Cache::singleton( );
        $var = $cache->get( $cacheKey );
        if ( $var ) {
            return $var;
        }
        
        $query = "
SELECT  v.{$valueColumnName} as {$valueColumnName} ,v.value as value, v.grouping as grouping
FROM   civicrm_option_value v,
       civicrm_option_group g
WHERE  v.option_group_id = g.id
  AND  g.name            = %1
  AND  g.is_active       = 1 ";
        
        if ( $onlyActive ) {
            $query .= " AND  v.is_active = 1 ";
        }
        if ( in_array( $name, self::$_domainIDGroups ) ) {
            $query .= " AND v.domain_id = " . CRM_Core_Config::domainID( );
        }

        if ( $condition ) {
            $query .= $condition;
        } 
        
        $query .= "  ORDER BY v.weight";

        $p = array( 1 => array( $name, 'String' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
        
        $var =& self::valuesCommon( $dao, $flip, $grouping, $localize, $valueColumnName );
        $cache->set( $cacheKey, $var );

        // call option value hook
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::optionValues( $var, $name );

        return $var;
    }

    static function &valuesByID( $id, $flip = false, $grouping = false, $localize = false, $valueColumnName = 'label' ) 
    {
        $cacheKey = "CRM_OG_ID_{$id}_{$flip}_{$grouping}_{$localize}_{$valueColumnName}";

        $cache =& CRM_Utils_Cache::singleton( );
        $var = $cache->get( $cacheKey );
        if ( $var ) {
            return $var;
        }
        

        $query = "
SELECT  v.{$valueColumnName} as {$valueColumnName} ,v.value as value, v.grouping as grouping
FROM   civicrm_option_value v,
       civicrm_option_group g
WHERE  v.option_group_id = g.id
  AND  g.id              = %1
  AND  v.is_active       = 1 
  AND  g.is_active       = 1 
  ORDER BY v.weight, v.label; 
";
        $p = array( 1 => array( $id, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
           
        $var =& self::valuesCommon( $dao, $flip, $grouping, $localize, $valueColumnName );
        $cache->set( $cacheKey, $var );

        return $var;
    }
    
    /**
     * Function to lookup titles OR ids for a set of option_value populated fields. The retrieved value
     * is assigned a new fieldname by id or id's by title  
     * (each within a specificied option_group)
     *
     * @param  array   $params   Reference array of values submitted by the form. Based on
     *                           $flip, creates new elements in $params for each field in
     *                           the $names array.
     *                           If $flip = false, adds     root field name     => title
     *                           If $flip = true, adds      actual field name   => id                                                                     
     * 
     * @param  array   $names    Reference array of fieldnames we want transformed.
     *                           Array key = 'postName' (field name submitted by form in $params).
     *                           Array value = array('newName' => $newName, 'groupName' => $groupName).
     *                           
     *
     * @param  boolean $flip
     *
     * @return void     
     * 
     * @access public
     * @static
     */
    static function lookupValues( &$params, &$names, $flip = false ) 
    {
        require_once "CRM/Core/BAO/CustomOption.php";
        foreach ($names as $postName => $value) {
            // See if $params field is in $names array (i.e. is a value that we need to lookup)
            if ( CRM_Utils_Array::value( $postName, $params ) ) {
                // params[$postName] may be a Ctrl+A separated value list
                if ( strpos( $params[$postName], CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) ) {
                    // eliminate the ^A frm the beginning and end if present
                    if ( substr( $params[$postName], 0, 1 ) == CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) {
                        $params[$postName] = substr( $params[$postName], 1, -1 );
                    }
                }
                $postValues = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $params[$postName]);
                $newValue = array( );
                foreach ($postValues as $postValue) {
                    if ( ! $postValue ) {
                        continue;
                    }

                    if ( $flip ) {
                        $p = array( 1 => array( $postValue, 'String' ) );
                        $lookupBy = 'v.label= %1';
                        $select   = "v.value";
                    } else {
                        $p = array( 1 => array( $postValue, 'Integer' ) );
                        $lookupBy = 'v.value = %1';
                        $select   = "v.label";
                    }
                    
                    $p[2] = array( $value['groupName'], 'String' );
                    $query = "
                        SELECT $select
                        FROM   civicrm_option_value v,
                               civicrm_option_group g
                        WHERE  v.option_group_id = g.id
                        AND    g.name            = %2
                        AND    $lookupBy";

                    $newValue[] = CRM_Core_DAO::singleValueQuery( $query, $p );
                    $newValue = str_replace( ',', '_', $newValue );
                }
                $params[$value['newName']] = implode(', ', $newValue);
            }
        }
    }

    static function getLabel( $groupName, $value, $onlyActiveValue = true ) 
    {
        if ( empty( $groupName ) ||
             empty( $value ) ) {
            return null;
        }

        $query = "
SELECT  v.label as label ,v.value as value
FROM   civicrm_option_value v, 
       civicrm_option_group g 
WHERE  v.option_group_id = g.id 
  AND  g.name            = %1 
  AND  g.is_active       = 1  
  AND  v.value           = %2
";
        if ( $onlyActiveValue ) {
            $query .= " AND  v.is_active = 1 ";
        }
        $p = array( 1 => array( $groupName , 'String' ),
                    2 => array( $value, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
        if ( $dao->fetch( ) ) {
            return $dao->label;
        }
        return null;
    }

    static function getValue( $groupName,
                              $label,
                              $labelField = 'label',
                              $labelType  = 'String',
                              $valueField = 'value' ) 
    {
        if ( empty( $label ) ) {
            return null;
        }

        $query = "
SELECT  v.label as label ,v.{$valueField} as value
FROM   civicrm_option_value v, 
       civicrm_option_group g 
WHERE  v.option_group_id = g.id 
  AND  g.name            = %1 
  AND  v.is_active       = 1  
  AND  g.is_active       = 1  
  AND  v.$labelField     = %2
";

        $p = array( 1 => array( $groupName , 'String' ),
                    2 => array( $label     , $labelType ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
        if ( $dao->fetch( ) ) {
            return $dao->value;
        }
        return null;
    }

    static function createAssoc( $groupName, &$values, &$defaultID, $groupLabel = null ) 
    {
        self::deleteAssoc( $groupName );
        if ( ! empty( $values ) ) {
            require_once 'CRM/Core/DAO/OptionGroup.php';
            $group = new CRM_Core_DAO_OptionGroup( );
            $group->name        = $groupName;
            $group->label       = $groupLabel;
            $group->is_reserved = 1;
            $group->is_active   = 1;
            $group->save( );
            
            require_once 'CRM/Core/DAO/OptionValue.php';
            foreach ( $values as $v ) {
                $value = new CRM_Core_DAO_OptionValue( );
                $value->option_group_id = $group->id;
                $value->label           = $v['label'];
                $value->value           = $v['value'];
                $value->name            = CRM_Utils_Array::value( 'name',        $v );
                $value->description     = CRM_Utils_Array::value( 'description', $v );
                $value->weight          = CRM_Utils_Array::value( 'weight',      $v );
                $value->is_default      = CRM_Utils_Array::value( 'is_default',  $v );
                $value->is_active       = CRM_Utils_Array::value( 'is_active',   $v );
                $value->save( );
                
                if ( $value->is_default ) {
                    $defaultID = $value->id;
                }
            }
        } else {
            return $defaultID = 'null';   
        }
        
        return $group->id;
    }
    
    static function getAssoc( $groupName, &$values, $flip = false, $field = 'name' ) 
    {
        $query = "
SELECT v.id as amount_id, v.value, v.label, v.name, v.description, v.weight
  FROM civicrm_option_group g,
       civicrm_option_value v
 WHERE g.id = v.option_group_id
   AND g.$field = %1
ORDER BY v.weight
";
        $params = array( 1 => array( $groupName, 'String' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        $fields = array( 'value', 'label', 'name', 'description', 'amount_id', 'weight' );
        if ( $flip ) {
            $values = array( );
        } else {
            foreach ( $fields as $field ) {
                $values[$field] = array( );
            }
        }
        $index  = 1; 
         
        while ( $dao->fetch( ) ) { 
            if ( $flip ) {
                $value = array( );
                foreach ( $fields as $field ) {
                    $value[$field] = $dao->$field;
                }
                $values[$dao->amount_id] = $value;
            } else {
                foreach ( $fields as $field ) {
                    $values[$field][$index] = $dao->$field;
                }
                $index++; 
            }
        } 
    }

    static function deleteAssoc( $groupName , $operator = "=" ) 
    {        
        $query = "
DELETE g, v
  FROM civicrm_option_group g,
       civicrm_option_value v
 WHERE g.id = v.option_group_id
   AND g.name {$operator} %1";

        $params = array( 1 => array( $groupName, 'String' ) );

        $dao = CRM_Core_DAO::executeQuery( $query, $params );
    }

    static function optionLabel( $groupName, $value ) 
    {
        $query = "
SELECT v.label
  FROM civicrm_option_group g,
       civicrm_option_value v
 WHERE g.id = v.option_group_id
   AND g.name  = %1
   AND v.value = %2";
        $params = array( 1 => array( $groupName, 'String' ),
                         2 => array( $value    , 'String' ) );
        return CRM_Core_DAO::singleValueQuery( $query, $params );

    }

    static function getRowValues( $groupName, $fieldValue, $field = 'name', 
                                  $fieldType  = 'String', $active = true ) 
    {
        $query = "
SELECT v.id, v.label, v.value, v.name, v.weight, v.description 
FROM   civicrm_option_value v, 
       civicrm_option_group g 
WHERE  v.option_group_id = g.id 
  AND  g.name            = %1
  AND  g.is_active       = 1  
  AND  v.$field          = %2
";

        if ( $active ) {
            $query .= " AND  v.is_active = 1";
        }

        $p = array( 1 => array( $groupName , 'String' ),
                    2 => array( $fieldValue, $fieldType ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
        $row = array( );

        if ( $dao->fetch( ) ) {
            foreach ( array('id','name','value','label','weight','description') as $fld ) {
                $row[$fld]  = $dao->$fld;
            }
        }
        return $row;
    }
}
