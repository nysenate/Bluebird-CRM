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

require_once 'CRM/Price/DAO/FieldValue.php';

/**
 * Business objects for managing price fields values.
 *
 */
class CRM_Price_BAO_FieldValue extends CRM_Price_DAO_FieldValue 
{

    /**
     * insert/update a new entry in the database.
     *
     * @param array $params (reference), array $ids
     *
     * @return object CRM_Price_DAO_FieldValue object
     * @access public
     * @static
     */
    static function &add( &$params, $ids ) {

        $fieldValueBAO = new CRM_Price_BAO_FieldValue( );
        $fieldValueBAO->copyValues( $params );
        
        if ( $id = CRM_Utils_Array::value( 'id', $ids ) ) {
            $fieldValueBAO->id = $id;
        }
        
        $fieldValueBAO->save( );
        return $fieldValueBAO;
    }
    

    /**
     * Creates a new entry in the database.
     *
     * @param array $params (reference), array $ids
     *
     * @return object CRM_Price_DAO_FieldValue object
     * @access public
     * @static
     */
    static function create( &$params, $ids ) {
        
        if ( !is_array($params) || empty($params) ) {
            return;
        }
        
        if ( $id = CRM_Utils_Array::value( 'id', $ids ) ) {
            if ( isset($params['name']) ) unset($params['name']);

            $oldWeight = null;
            if ( $id ) {
                $oldWeight = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_FieldValue', $id, 'weight', 'id' );
            }
            
            $fieldValues = array( 'price_field_id' => CRM_Utils_Array::value('price_field_id', $params, 0) );
            $params['weight'] =
                CRM_Utils_Weight::updateOtherWeights('CRM_Price_DAO_FieldValue', $oldWeight, $params['weight'], $fieldValues);
            
        } else {
            if ( !CRM_Utils_Array::value('name', $params) ) {
                $params['name'] =  CRM_Utils_String::munge( CRM_Utils_Array::value('label', $params), '_', 64 );
            }
            $params['weight'] = 1;  
        }
        
        $params['is_active'] = CRM_Utils_Array::value('is_active', $params, 0);

        return self::add( $params, $ids );
    }
    
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects.  
     *
     * @param array $params   (reference ) an assoc array 
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Price_DAO_FieldValue object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults )
    {
        return CRM_Core_DAO::commonRetrieve( 'CRM_Price_DAO_FieldValue', $params, $defaults );
    }


    /**
     * Retrive the all values for given field id
     * 
     * @param int $fieldId price_field_id
     * @param array $values (reference ) to hold the values
     * @param string $orderBy for order by, default weight
     * @param int $isActive is_active, default false
     *
     * @return array $values
     *
     * @access public
     * @static
     */
    static function getValues( $fieldId, &$values, $orderBy = 'weight', $isActive = false )
    {
        $fieldValueDAO = new CRM_Price_DAO_FieldValue( );
        $fieldValueDAO->price_field_id = $fieldId;
        if ( $isActive ) {
            $fieldValueDAO->is_active = 1;
        }
        $fieldValueDAO->find( );
        
        while ( $fieldValueDAO->fetch() ) {
            CRM_Core_DAO::storeValues($fieldValueDAO, $values[$fieldValueDAO->id]);
        }
        
        return $values;  
    }
    
    /**
     * update the is_active flag in the db
     *
     * @param int      $id         Id of the database record
     * @param boolean  $is_active  Value we want to set the is_active field
     *
     * @return   Object            DAO object on sucess, null otherwise
     * 
     * @access public
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Price_DAO_FieldValue', $id, 'is_active', $is_active );
    }
    

    /**
     * delete all values of the given field id 
     *
     * @param  int    $fieldId    Price field id
     *
     * @return boolean
     * 
     * @access public
     * @static
     */
    static function deleteValues( $fieldId ) 
    {
        if ( !$fieldId ) return false;

        $fieldValueDAO = new CRM_Price_DAO_FieldValue( );
        $fieldValueDAO->price_field_id = $fieldId;
        $fieldValueDAO->delete( );
    } 
    
    /**
     * Delete the value.
     *
     * @param   int   $id  Id 
     * 
     * @return  boolean
     *
     * @access public
     * @static
     */ 
    static function del( $id )
    {
        if ( !$id ) return false;

        $fieldValueDAO = new CRM_Price_DAO_FieldValue( );
        $fieldValueDAO->id = $id;
        return $fieldValueDAO->delete( );
    }
}