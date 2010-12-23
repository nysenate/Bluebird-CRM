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


/**
 * Business objects for managing custom data options.
 *
 */
class CRM_Core_BAO_CustomOption {

    const VALUE_SEPERATOR = "";

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
     * @return object CRM_Core_BAO_CustomOption object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults )
    {
        require_once 'CRM/Core/DAO/OptionValue.php';
        $customOption = new CRM_Core_DAO_OptionValue( );
        $customOption->copyValues( $params );
        if ( $customOption->find( true ) ) {
            CRM_Core_DAO::storeValues( $customOption, $defaults );
            return $customOption;
        }
        return null;
    }

    /**
     * Returns all active options ordered by weight for a given field
     *
     * @param  int      $fieldId         field whose options are needed
     * @param  boolean  $inactiveNeeded  do we need inactive options ?
     *
     * @return array $customOption all active options for fieldId
     * @static
     */
    static function getCustomOption( $fieldID,
                                     $inactiveNeeded = false )
    {       
        $options = array();
        if ( ! $fieldID ) {
            return $options;
        }

        // get the option group id
        $optionGroupID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField',
                                                      $fieldID,
                                                      'option_group_id' );
        if ( ! $optionGroupID ) {
            return $options;
        }

        require_once 'CRM/Core/DAO/OptionValue.php';
        $dao = new CRM_Core_DAO_OptionValue();
        $dao->option_group_id = $optionGroupID;
        if ( ! $inactiveNeeded ) {
            $dao->is_active = 1;
        }
        $dao->orderBy('weight ASC, label ASC');
        $dao->find( );
        
        while ( $dao->fetch( ) ) {
            $options[$dao->id] = array();
            $options[$dao->id]['id']    = $dao->id;
            $options[$dao->id]['label'] = $dao->label;
            $options[$dao->id]['value'] = $dao->value;
        }

        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::customFieldOptions( $fieldID, $options, true );
        
        return $options;
    }

    static function getOptionLabel($fieldId, $value, $htmlType = null, $dataType = null )
    {
        if ( ! $fieldId ) {
            return null;
        }

        if ( ! $htmlType || ! $dataType ) {
            $sql = "
SELECT html_type, data_type
FROM   civicrm_custom_field
WHERE  id = %1
";
            $params = array( 1 => array( $fieldId, 'Integer' ) );
            $dao = CRM_Core_DAO::executeQuery( $sql, $params );
            if ( $dao->fetch( ) ) {
                $htmlType = $dao->html_type;
                $dataType  = $dao->data_type;
            } else {
                CRM_Core_Error::fatal( );
            }
        }

        $options = null;
        switch ($htmlType) {
        case 'CheckBox':
        case 'Multi-Select':
        case 'AdvMulti-Select':
        case 'Select':
        case 'Radio':
        case 'Autocomplete-Select':
            if ( !in_array( $dataType, array( 'Boolean', 'ContactReference' ) ) ) {
                $options =& self::valuesByID( $fieldId );
            }
        }

        require_once 'CRM/Core/BAO/CustomField.php';
        return CRM_Core_BAO_CustomField::getDisplayValueCommon( $value,
                                                                $options,
                                                                $htmlType,
                                                                $dataType );
    }

    /**
     * Function to delete Option
     *
     * param $optionId integer option id
     *
     * @static
     * @access public
     */
    static function del( $optionId ) 
    {
        // get the customFieldID
        $query = "
SELECT f.id as id, f.data_type as dataType
FROM   civicrm_option_value v,
       civicrm_option_group g,
       civicrm_custom_field f
WHERE  v.id    = %1
AND    g.id    = f.option_group_id
AND    g.id    = v.option_group_id";
        $params = array( 1 => array( $optionId, 'Integer' ) );
        $dao    = CRM_Core_DAO::executeQuery( $query, $params );
        if ( $dao->fetch( ) ) {
            if ( in_array( $dao->dataType,
                           array( 'Int', 'Float', 'Money', 'Boolean' ) ) ) {
                $value = 0;
            } else {
                $value = '';
            }
            $params = array( 'optionId' => $optionId,
                             'fieldId'  => $dao->id,
                             'value'    => $value );
            // delete this value from the tables
            self::updateCustomValues( $params );

            // also delete this option value
            $query = "
DELETE
FROM   civicrm_option_value
WHERE  id = %1";
            $params = array( 1 => array( $optionId, 'Integer' ) );
            CRM_Core_DAO::executeQuery( $query, $params );
        }
    }

    static function updateCustomValues($params) 
    {
        $optionDAO = new CRM_Core_DAO_OptionValue();
        $optionDAO->id = $params['optionId'];
        $optionDAO->find( true );
        $oldValue = $optionDAO->value;

        // get the table, column, html_type and data type for this field
        $query = "
SELECT g.table_name  as tableName ,
       f.column_name as columnName,
       f.data_type   as dataType,
       f.html_type   as htmlType
FROM   civicrm_custom_group g,
       civicrm_custom_field f
WHERE  f.custom_group_id = g.id
  AND  f.id = %1";
        $queryParams = array( 1 => array( $params['fieldId'], 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $queryParams );
        if ( $dao->fetch( ) ) {
            if ( $dao->dataType == 'Money' ) { 
                require_once 'CRM/Utils/Rule.php';
                $params['value'] = CRM_Utils_Rule::cleanMoney( $params['value'] );
            }
            switch ( $dao->htmlType ) {
            case 'Autocomplete-Select':    
            case 'Select':
            case 'Radio':
                $query = "
UPDATE {$dao->tableName}
SET    {$dao->columnName} = %1
WHERE  {$dao->columnName} = %2";
                if ( $dao->dataType == 'Auto-complete' ) {
                    $dataType = "String";
                } else {
                    $dataType = $dao->dataType;
                }
                $queryParams = array( 1 => array( $params['value'],
                                                  $dataType ),
                                      2 => array( $oldValue,
                                                  $dataType ) );
                break;

            case 'AdvMulti-Select':
            case 'Multi-Select':
            case 'CheckBox':
                $oldString =
                    CRM_Core_DAO::VALUE_SEPARATOR . $oldValue . CRM_Core_DAO::VALUE_SEPARATOR;
                $newString = 
                    CRM_Core_DAO::VALUE_SEPARATOR . $params['value'] . CRM_Core_DAO::VALUE_SEPARATOR;
                $query = "
UPDATE {$dao->tableName}
SET    {$dao->columnName} = REPLACE( {$dao->columnName}, %1, %2 )";
                $queryParams = array( 1 => array( $oldString, 'String' ),
                                      2 => array( $newString, 'String' ) );
                break;

            default:
                CRM_Core_Error::fatal( );
            }
            $dao = CRM_Core_DAO::executeQuery( $query, $queryParams );
        }
    }

    static function &valuesByID( $customFieldID, $optionGroupID = null ) {
        if ( ! $optionGroupID ) {
            $optionGroupID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField',
                                                          $customFieldID,
                                                          'option_group_id' );
        }
        
        require_once 'CRM/Core/OptionGroup.php';
        $options =& CRM_Core_OptionGroup::valuesByID( $optionGroupID );

        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::customFieldOptions( $customFieldID, $options, false );

        return $options;
    }

}

