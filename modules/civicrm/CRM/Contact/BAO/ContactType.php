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

require_once 'CRM/Contact/DAO/ContactType.php';
require_once 'CRM/Core/BAO/Navigation.php';
class CRM_Contact_BAO_ContactType extends CRM_Contact_DAO_ContactType {

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Contact_BAO_ContactType object on success, null otherwise
     * @access public
     * @static
     */  
    static function retrieve( &$params, &$defaults ) {
        $contactType = new CRM_Contact_DAO_ContactType( );
        $contactType->copyValues( $params );
        if ( $contactType->find( true ) ) {
            CRM_Core_DAO::storeValues( $contactType, $defaults );
            return $contactType;
        }
        return null;
    } 

    static function isActive( $contactType ) {
        $contact = self::contactTypeInfo( false );
        $active = array_key_exists( $contactType, $contact ) ? true : false;
        return $active;
    }
    
    /**
     *
     *function to retrieve basic contact type information.
     *
     *@return  array of basic contact types information.
     *@static
     *
     */
    static function &basicTypeInfo( $all = false ) {
        static $_cache = null;
        
        if ( $_cache === null ) {
            $_cache = array( );
        }

        $argString = $all ? 'CRM_CT_BTI_1' : 'CRM_CT_BTI_0';
        if ( ! array_key_exists( $argString, $_cache ) ) {
            $cache =& CRM_Utils_Cache::singleton( );
            $_cache[$argString] = $cache->get( $argString );
            if ( ! $_cache[$argString] ) {
                $sql = "
SELECT *
FROM   civicrm_contact_type
WHERE  parent_id IS NULL
";
                if ( $all === false ) {
                    $sql .= " AND is_active = 1";
                }
                
                $dao = CRM_Core_DAO::executeQuery( $sql,
                                                   CRM_Core_DAO::$_nullArray,
                                                   false,
                                                   'CRM_Contact_DAO_ContactType' );
                while ( $dao->fetch( ) ) {
                    $value = array( );
                    CRM_Core_DAO::storeValues( $dao, $value );
                    $_cache[$argString][$dao->name] = $value;
                }

                $cache->set( $argString, $_cache[$argString] );
            }
        }
        return $_cache[$argString];
    }

    /**
     *
     *function to  retrieve  all basic contact types.
     *
     *@return  array of basic contact types
     *@static
     *
     */ 
    static function basicTypes( $all = false ) {
        return array_keys( self::basicTypeInfo( $all ) );
    }

    static function basicTypePairs( $all = false, $key = 'name' ) {
        $subtypes = self::basicTypeInfo( $all );

        $pairs = array( );
        foreach ( $subtypes as $name => $info ) {
            $index = ($key == 'name') ? $name : $info[$key];
            $pairs[$index] = $info['label'];
        }
        return $pairs;
    }

    /**
     *
     *function to retrieve all subtypes Information.
     *
     *@param array $contactType.
     *@return  array of sub type information
     *@static
     *
     */
    static function &subTypeInfo( $contactType = null, $all = false,  $ignoreCache = false, $reset = false ) {
        static $_cache = null;

        if( $reset === true ) {
            $_cache = null;
        }
        
        if ( $_cache === null ) {
            $_cache = array( );
        }
        if ( $contactType && !is_array( $contactType ) ) {
            $contactType = array( $contactType );
        }

        $argString = $all ? 'CRM_CT_STI_1_' : 'CRM_CT_STI_0_';
        if ( ! empty( $contactType ) ) {
            $argString .= implode( "_" , $contactType );
        }

        if ( (!array_key_exists( $argString, $_cache )) || $ignoreCache ) {
            $cache =& CRM_Utils_Cache::singleton( );
            $_cache[$argString] = $cache->get( $argString );
            if ( ! $_cache[$argString] || $ignoreCache ) {
                $_cache[$argString] = array( );

                $ctWHERE = '';
                if ( ! empty($contactType) ) {
                    $ctWHERE = " AND parent.name IN ('" . implode( "','" , $contactType ) . "')" ;
                }

                $sql = "
SELECT subtype.*, parent.name as parent, parent.label as parent_label
FROM   civicrm_contact_type subtype
INNER JOIN civicrm_contact_type parent ON subtype.parent_id = parent.id
WHERE  subtype.name IS NOT NULL AND subtype.parent_id IS NOT NULL {$ctWHERE} 
";
                if ( $all === false ) {
                    $sql .= " AND subtype.is_active = 1 AND parent.is_active = 1 ORDER BY parent.id";
                }
                $dao = CRM_Core_DAO::executeQuery( $sql, array( ), 
                                                   false, 'CRM_Contact_DAO_ContactType' );
                while ( $dao->fetch( ) ) {
                    $value = array( );
                    CRM_Core_DAO::storeValues( $dao, $value );
                    $value['parent'] = $dao->parent;
                    $value['parent_label'] = $dao->parent_label;
                    $_cache[$argString][$dao->name] = $value;
                }
 
                $cache->set( $argString, $_cache[$argString] );
            }
        }
        return $_cache[$argString];
    }
     
    /**
     *
     *function to  retrieve all subtypes
     *
     *@param array $contactType.
     *@return  list of all subtypes OR list of subtypes associated to
     *a given basic contact type  
     *@static
     *
     */
 
     static function subTypes( $contactType = null, $all = false, $columnName = 'name', $ignoreCache = false ) {
         if ( $columnName == 'name' ) {
             return array_keys( self::subTypeInfo( $contactType, $all, $ignoreCache ) );
         } else {
             return array_values( self::subTypePairs( $contactType, false, null, $ignoreCache ) );
         }
     }

    /**
     *
     *function to retrieve subtype pairs with name as 'subtype-name' and 'label' as value
     *
     *@param array $contactType.
     *@return list of subtypes with name as 'subtype-name' and 'label' as value
     *@static
     *
     */
     static function subTypePairs( $contactType = null, $all = false, $labelPrefix = '- ', $ignoreCache = false ) {
         $subtypes = self::subTypeInfo( $contactType, $all, $ignoreCache );
         
         $pairs = array( );
         foreach ( $subtypes as $name => $info ) {
             $pairs[$name] = $labelPrefix . $info['label'];
         }
         return $pairs;
     }
     
    /**
     *
     *function to retrieve list of all types i.e basic + subtypes.
     *
     *@return  array of basic types + all subtypes.
     *@static
     *
     */
    static function contactTypes( $all = false ) {
        return array_keys( self::contactTypeInfo( $all ) );
    }
    
    /**
     *
     *function to retrieve info array about all types i.e basic + subtypes.
     *
     *@return  array of basic types + all subtypes.
     *@static
     *
     */
    static function contactTypeInfo( $all = false, $reset = false ) {
        static $_cache = null;
        
        if( $reset === true ) {
            $_cache = null;
        }
        
        if ( $_cache === null ) {
            $_cache = array( );
        }

        $argString = $all ? 'CRM_CT_CTI_1' : 'CRM_CT_CTI_0';
        if ( ! array_key_exists( $argString, $_cache ) ) {
            $cache =& CRM_Utils_Cache::singleton( );
            $_cache[$argString] = $cache->get( $argString );
            if ( ! $_cache[$argString] ) {
                $_cache[$argString] = array( );

                $sql = "
SELECT type.*, parent.name as parent, parent.label as parent_label
FROM      civicrm_contact_type type
LEFT JOIN civicrm_contact_type parent ON type.parent_id = parent.id
WHERE  type.name IS NOT NULL 
";
                if ( $all === false ) {
                    $sql .= " AND type.is_active = 1";
                }

                $dao = CRM_Core_DAO::executeQuery( $sql,
                                                   CRM_Core_DAO::$_nullArray,
                                                   false,
                                                   'CRM_Contact_DAO_ContactType' );
                while ( $dao->fetch( ) ) {
                    $value = array( );
                    CRM_Core_DAO::storeValues( $dao, $value );
                    if ( array_key_exists('parent_id', $value) ) {
                        $value['parent'] = $dao->parent;
                        $value['parent_label'] = $dao->parent_label;
                    }
                    $_cache[$argString][$dao->name] = $value;
                }
                
                $cache->set( $argString, $_cache[$argString] );
            }
        }

        return $_cache[$argString];
    }

    /**
     *
     *function to retrieve basic type pairs with name as 'built-in name' and 'label' as value
     *
     *@param array $contactType.
     *@return list of basictypes with name as 'built-in name' and 'label' as value
     *@static
     *
     */
    static function contactTypePairs( $all = false, $typeName = null ) {
        $types = self::contactTypeInfo( $all );

        if ( $typeName ) {
            if ( array_key_exists($typeName, $types) ) {
                return $types[$typeName]['label'];
            }
        } else {
            $pairs = array( );
            foreach ( $types as $name => $info ) {
                $pairs[$name] = $info['label'];
            }
            return $pairs;
        }
        return null;
    }

    static function &getSelectElements( $all         = false, 
                                        $isSeperator = true, 
                                        $seperator   = CRM_Core_DAO::VALUE_SEPARATOR ) {
        static $_cache = null;

        if ( $_cache === null ) {
            $_cache = array( );
        }

        $argString  = $all ? 'CRM_CT_GSE_1' : 'CRM_CT_GSE_0';
        $argString .= $isSeperator ? '_1' : '_0';
        if ( ! array_key_exists( $argString, $_cache ) ) {
            $cache =& CRM_Utils_Cache::singleton( );
            $_cache[$argString] = $cache->get( $argString );
            
            if ( ! $_cache[$argString] ) {
                $_cache[$argString] = array( );

                $sql = "
SELECT    c.name as child_name , c.label as child_label , c.id as child_id,
          p.name as parent_name, p.label as parent_label, p.id as parent_id
FROM      civicrm_contact_type c
LEFT JOIN civicrm_contact_type p ON ( c.parent_id = p.id )
WHERE     ( c.name IS NOT NULL )
";
            
                if ( $all === false ) {
                    $sql .= "
AND   c.is_active = 1
AND   ( p.is_active = 1 OR p.id IS NULL )
";
                }
                $sql .= " ORDER BY c.id";
                
                $values = array( );
                $dao = CRM_Core_DAO::executeQuery( $sql );
                while ( $dao->fetch( ) ) {
                    if ( ! empty( $dao->parent_id ) ) {
                        $key   = $isSeperator ? $dao->parent_name . $seperator . $dao->child_name : $dao->child_name;
                        $label = "-&nbsp;{$dao->child_label}";
                        $pName = $dao->parent_name;
                    } else {
                        $key   = $dao->child_name;
                        $label = $dao->child_label;
                        $pName = $dao->child_name;
                    }
                    
                    if ( ! isset( $values[$pName] ) ) {
                        $values[$pName] = array( );
                    }
                    $values[$pName][] = array( 'key' => $key, 'label' => $label );
                }

                $selectElements = array( );
                foreach ( $values as $pName => $elements ) {
                    foreach ( $elements as $element ) {
                        $selectElements[$element['key']] = $element['label'];
                    }
                }
                $_cache[$argString] = $selectElements;
 
                $cache->set( $argString, $_cache[$argString] );
            }
        }
        return $_cache[$argString];
    }

    /**
     * function to check if a given type is a subtype
     *
     *@param string $subType contact subType.
     *@return  boolean true if subType, false otherwise.
     *@static
     *
     */
    static function isaSubType( $subType, $ignoreCache = false ) {
        return in_array( $subType, self::subTypes( null, true, 'name', $ignoreCache ) );
    }
    
    /**
     *function to retrieve the basic contact type associated with
     *given subType. 
     *
     *@param array/string $subType contact subType.
     *@return array/string of basicTypes.
     *@static
     *
     */
    static function getBasicType( $subType ) { 
        static $_cache = null;
        if ( $_cache === null ) {
            $_cache = array( );
        }
        
        $isArray = true;
        if ( $subType && !is_array( $subType ) ) {
            $subType = array( $subType );
            $isArray = false;
        }
        $argString = implode( "_" , $subType );

        if ( ! array_key_exists( $argString, $_cache ) ) {
            $_cache[$argString] = array( );
            
            $sql = "
SELECT subtype.name as contact_subtype, type.name as contact_type 
FROM   civicrm_contact_type subtype
INNER JOIN civicrm_contact_type type ON ( subtype.parent_id = type.id )
WHERE  subtype.name IN ('".implode("','",$subType)."' )";
            $dao = CRM_Core_DAO::executeQuery( $sql );
            while( $dao->fetch( ) ) {
                if( !$isArray ) { 
                    $_cache[$argString] = $dao->contact_type;
                    break;
                }
                $_cache[$argString][$dao->contact_subtype] = $dao->contact_type;
            }
        } 
        return $_cache[$argString];
    }
    
    /**
     *
     *function to suppress all subtypes present in given array. 
     *
     *@param array $subType contact subType.
     *@return array of suppresssubTypes .
     *@static
     *
     */
    static function suppressSubTypes( &$subTypes, $ignoreCache = false ) {
        $subTypes = array_diff( $subTypes, self::subTypes( null, true, 'name', $ignoreCache ) );
        return $subTypes;
    }
    
    /**
     *
     *function to verify if a given subtype is associated with a given basic contact type.
     *
     *@param  string  $subType contact subType
     *@param  string  $contactType contact Type
     *@return boolean true if contact extends, false otherwise.
     *@static
     *
     */
    static function isExtendsContactType( $subType, $contactType, $ignoreCache = false, $columnName = 'name') {
        return in_array( $subType, self::subTypes( $contactType, true, $columnName, $ignoreCache ) );
    }

    /**
     *
     *function to create shortcuts menu for contactTypes
     *
     *@return array  of contactTypes
     *@static
     *
     */
    static  function getCreateNewList ( ) {
        require_once 'CRM/Core/DAO.php';
        $shortCuts    = array( );
        $contactTypes = self::getSelectElements(  ); 
        foreach( $contactTypes as $key => $value ) {
            if( $key ) {
                $typeValue = explode( CRM_Core_DAO::VALUE_SEPARATOR, $key );
                $typeUrl   = "ct=" . CRM_Utils_Array::value( '0', $typeValue );
                if( $csType = CRM_Utils_Array::value( '1', $typeValue ) ) { 
                    $typeUrl .= "&cst=$csType";
                }
                $shortCuts[]  = array(
                                      'path'  => "civicrm/contact/add",
                                      'query' => "$typeUrl&reset=1",
                                      'ref'   => "new-$value",
                                      'title' => $value,
                                      );          
            }
        }
        return $shortCuts;
    }
    
    /**
     * Function to delete Contact SubTypes 
     * 
     * @param  int  $contactTypeId     ID of the Contact Subtype to be deleted.
     * 
     * @access public
     * @static
     */
    static function del( $contactTypeId ) {
        
        if ( !$contactTypeId  ) {
            return false;
        }
        
        require_once 'CRM/Core/DAO/CustomGroup.php';
        require_once 'CRM/Contact/DAO/Contact.php';
        $params = array( 'id'=> $contactTypeId  );
        self::retrieve( $params, $typeInfo );
        $name   = $typeInfo['name'];
        // check if any custom group
        $custom = new CRM_Core_DAO_CustomGroup ( );
        $custom->whereAdd("extends_entity_column_value LIKE '%" . 
                          CRM_Core_DAO::VALUE_SEPARATOR . 
                          $name . 
                          CRM_Core_DAO::VALUE_SEPARATOR . "%'");  
        if ( $custom->find( ) ) {
            return false;
        }

        // remove subtype for existing contacts
        $sql = "
UPDATE civicrm_contact SET contact_sub_type = NULL 
WHERE contact_sub_type = '$name'";
        CRM_Core_DAO::executeQuery( $sql );

        // remove subtype from contact type table
        $contactType = new CRM_Contact_DAO_ContactType( );
        $contactType->id = $contactTypeId;
        $contactType->delete( );

        // remove navigation entry if any
        if( $name ) {
            $sql = "
DELETE
FROM civicrm_navigation 
WHERE name = %1";
            $params = array( 1 => array( "New $name" , 'String' ) );
            $dao = CRM_Core_DAO::executeQuery( $sql , $params );
            CRM_Core_BAO_Navigation::resetNavigation( );
        }
        return true; 
    }
    /**
     * Function to add or update Contact SubTypes 
     * 
     * @param  array $params  an assoc array of name/value pairs
     * @return object  
     * @access public
     * @static
     */
    static function add( $params ) {

        // null if empty params or doesn't contain parent_id
        if ( !CRM_Utils_Array::value( 'parent_id', $params ) ) {
            return;
        }

        // label or name
        if ( !CRM_Utils_Array::value( 'label', $params ) ) {
            return;
        }        

        // parent_id
        if ( !CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_ContactType', $params['parent_id'] ) ) {
            return;
        }
        
        $contactType = new CRM_Contact_DAO_ContactType( );
        $contactType->copyValues( $params );
        $contactType->id        = CRM_Utils_Array::value( 'id', $params );
        $contactType->is_active = CRM_Utils_Array::value( 'is_active', $params, 0 );


        
        $contactType->save( );
        if( $contactType->find( true ) ) {
            $contactName = $contactType->name;
            $contact     = ucfirst( $contactType->label );
            $active      = $contactType->is_active;
        }
       
        if( CRM_Utils_Array::value( 'id', $params ) ) {
            $params    = array( 'name' => "New $contactName" );
            $newParams = array ( 'label' => "New $contact",
                                 'is_active'=> $active );
            CRM_Core_BAO_Navigation::processUpdate( $params ,$newParams );
        } else if( CRM_Utils_Array::value( 'parent_id', $params ) ) {
            $name = self::getBasicType( $contactName );    
            $value = array( 'name' => "New $name" );
            CRM_Core_BAO_Navigation::retrieve( $value ,$navinfo );
            $navigation = array(
                                'label'   => "New $contact",
                                'name'    => "New $contactName",
                                'url'     => "civicrm/contact/add&ct=$name&cst=$contactName&reset=1",
                                'permission' => "add contacts",
                                'parent_id'  => $navinfo['id'],
                                'is_active'  => $active
                                ); 
            CRM_Core_BAO_Navigation::add( $navigation );
        }
        CRM_Core_BAO_Navigation::resetNavigation( );

        // reset the cache after adding
        self::subTypeInfo( null, false, false, true );
        
        return $contactType;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on success, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) {
        $params = array( 'id'=> $id );
        self::retrieve( $params , $contactinfo );
        $params    = array ( 'name'=> "New $contactinfo[name]" );
        $newParams = array('is_active'=> $is_active );
        CRM_Core_BAO_Navigation::processUpdate( $params ,$newParams );
        CRM_Core_BAO_Navigation::resetNavigation( );
        return CRM_Core_DAO::setFieldValue( 'CRM_Contact_DAO_ContactType', $id, 
                                            'is_active', $is_active );
    }
    
    static function getLabel( $typeName ) {
        $types = self::contactTypeInfo( true );

        if ( array_key_exists($typeName, $types) ) {
            return $types[$typeName]['label'];
        }
        return $typeName;    
    }

    /**
     * Function to check whether allow to change any contact's subtype
     * on the basis of custom data and relationship of specific subtype
     * currently used in contact/edit form amd in import validation
     *
     * @param  int     $contactId    contact id.
     * @param  string  $subType      subtype.  
     *
     * @return boolean true/false.
     * @static
     */
    static function isAllowEdit( $contactId, $subType = null ) {

        if ( !$contactId ) {
            return true;
        }
        
        if ( empty($subType) ) {
            $subType = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                    $contactId,
                                                    'contact_sub_type'
                                                    ); 
        }
        
        if ( self::hasCustomData($subType, $contactId) || self::hasRelationships($contactId, $subType) ) {
            return false;
        }
        
        return true;
    } 
    
    static function hasCustomData( $contactType, $contactId = null ) {
        $subTypeClause = '';

        if ( self::isaSubType( $contactType ) ) {
            $subType      = $contactType;
            $contactType  = self::getBasicType( $subType );
            
            // check for empty custom data which extends subtype
            $subTypeValue  = CRM_Core_DAO::VALUE_SEPARATOR . $subType . CRM_Core_DAO::VALUE_SEPARATOR;
            $subTypeClause = " AND extends_entity_column_value LIKE '%{$subTypeValue}%' ";  
        }
        $query = "SELECT table_name FROM civicrm_custom_group WHERE extends = '{$contactType}' {$subTypeClause}"; 
        
        $dao = CRM_Core_DAO::executeQuery( $query );
        while( $dao->fetch( ) ) {
            $sql = "SELECT count(id) FROM {$dao->table_name}";
            if ( $contactId ) {
                $sql .= " WHERE entity_id = {$contactId}";
            }
            $sql .= " LIMIT 1";

            $customDataCount = CRM_Core_DAO::singleValueQuery( $sql ); 
            if ( !empty($customDataCount) ) {
                $dao->free();
                return true;
            }
        }
        return false; 
    }
    
    static function hasRelationships ( $contactId, $contactType ) {
        $subTypeClause = null;
        if ( self::isaSubType($contactType) ) {
            $subType = $contactType;
            $contactType  = self::getBasicType( $subType );
            $subTypeClause = " AND ( ( crt.contact_type_a = '{$contactType}' AND crt.contact_sub_type_a = '{$subType}') OR 
                                     ( crt.contact_type_b = '{$contactType}' AND crt.contact_sub_type_b = '{$subType}')  ) ";
        } else {
            $subTypeClause = " AND ( crt.contact_type_a = '{$contactType}' OR crt.contact_type_b = '{$contactType}' ) "; 
        }
        
        // check relationships for 
        $relationshipQuery = "
SELECT count(cr.id) FROM civicrm_relationship cr 
INNER JOIN civicrm_relationship_type crt ON 
( cr.relationship_type_id = crt.id {$subTypeClause} ) 
WHERE ( cr.contact_id_a = {$contactId} OR cr.contact_id_b = {$contactId} ) 
LIMIT 1";
        
        $relationshipCount = CRM_Core_DAO::singleValueQuery( $relationshipQuery ); 
        
        if ( !empty($relationshipCount) ) {
            return true;
        }
        
        return false;
    } 
}
