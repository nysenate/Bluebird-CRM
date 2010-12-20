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
require_once 'CRM/Core/OptionGroup.php';
require_once 'CRM/Core/SelectValues.php';
require_once 'CRM/Core/DAO/CustomField.php';
require_once 'CRM/Core/DAO/CustomGroup.php';
require_once 'CRM/Core/BAO/CustomOption.php';
require_once 'CRM/Contact/BAO/ContactType.php';

/**
 * Business objects for managing custom data fields.
 *
 */
class CRM_Core_BAO_CustomField extends CRM_Core_DAO_CustomField 
{
    /**
     * Array for valid combinations of data_type & descriptions
     *
     * @var array
     * @static
     */
    public static $_dataType = null;

    /**
     * Array to hold (formatted) fields for import
     *
     * @var array
     * @static
     */
    public static $_importFields = null;

    /**
     * Build and retrieve the list of data types and descriptions
     *
     * @param NULL
     * @return array        Data type => Description
     * @access public
     * @static
     */
    static function &dataType()
    {
        if ( !(self::$_dataType) ) {
            self::$_dataType = array(
                                     'String'           => ts('Alphanumeric'),
                                     'Int'              => ts('Integer'),
                                     'Float'            => ts('Number'),
                                     'Money'            => ts('Money'),
                                     'Memo'             => ts('Note'),
                                     'Date'             => ts('Date'),
                                     'Boolean'          => ts('Yes or No'),
                                     'StateProvince'    => ts('State/Province'),
                                     'Country'          => ts('Country'),
                                     'File'             => ts('File'),
                                     'Link'             => ts('Link'),
                                     'ContactReference' => ts('Contact Reference')
                                     );
        }
        return self::$_dataType;
    }
    
    /**
     * takes an associative array and creates a custom field object
     *
     * This function is invoked from within the web form layer and also from the api layer
     *
     * @param array $params (reference) an assoc array of name/value pairs
     *
     * @return object CRM_Core_DAO_CustomField object
     * @access public
     * @static
     */
    static function create( &$params )
    {
        if ( !isset($params['id']) && !isset($params['column_name']) ) {
            // if add mode & column_name not present, calculate it.
            require_once 'CRM/Utils/String.php';
            $params['column_name'] = 
                strtolower( CRM_Utils_String::munge( $params['label'], '_', 32 ) );
            
            $params['name'] = CRM_Utils_String::munge($params['label'], '_', 64 );
        } else if ( isset($params['id']) ) {
            $params['column_name'] = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField',
                                                                  $params['id'],
                                                                  'column_name' );
        }
        
        $indexExist = false;
        //as during create if field is_searchable we had created index.
        if ( CRM_Utils_Array::value( 'id', $params ) ) {
            $indexExist = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField', $params['id'], 'is_searchable' );
        }
        
        if ( ( $params['html_type'] == 'CheckBox' ||
               $params['html_type'] == 'AdvMulti-Select' ||
               $params['html_type'] == 'Multi-Select' ) &&
             isset($params['default_checkbox_option'] ) ) {
            $tempArray = array_keys($params['default_checkbox_option']);
            $defaultArray = array();
            foreach ($tempArray as $k => $v) {
                if ( $params['option_value'][$v] ) {
                    $defaultArray[] = $params['option_value'][$v];
                }
            }
            
            if ( ! empty( $defaultArray ) ) {
                // also add the seperator before and after the value per new conventio (CRM-1604)
                $params['default_value'] = 
                    CRM_Core_BAO_CustomOption::VALUE_SEPERATOR .
                    implode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $defaultArray) .
                    CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
            }
        } else {
            if ( CRM_Utils_Array::value( 'default_option', $params ) 
                 && isset($params['option_value'][$params['default_option']] ) ) {
                $params['default_value'] = $params['option_value'][$params['default_option']];
            }
        }
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        // create any option group & values if required
        if ( $params['html_type'] != 'Text' &&
             in_array( $params['data_type'], array('String', 'Int', 'Float', 'Money') ) &&
             ! empty($params['option_value']) && is_array($params['option_value']) ) {

            $tableName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                      $params['custom_group_id'],
                                                      'table_name' );
            
                                                                        
            if ( $params['option_type'] == 1 ) {
                // first create an option group for this custom group
                require_once 'CRM/Core/BAO/OptionGroup.php';
                $optionGroup            = new CRM_Core_DAO_OptionGroup( );
                $optionGroup->name      =  "{$params['column_name']}_". date( 'YmdHis' );
                $optionGroup->label     =  $params['label'];
                $optionGroup->is_active = 1;
                $optionGroup->save( );
                $params['option_group_id'] = $optionGroup->id;
                
                require_once 'CRM/Core/BAO/OptionValue.php';
                
                
                require_once 'CRM/Utils/String.php';
                foreach ($params['option_value'] as $k => $v) {
                    if (strlen(trim($v))) {
                        $optionValue                  =  new CRM_Core_DAO_OptionValue( );
                        $optionValue->option_group_id =  $optionGroup->id;
                        $optionValue->label           =  $params['option_label'][$k];
                        $optionValue->name            =  CRM_Utils_String::titleToVar( $params['option_label'][$k] );
                        switch ( $params['data_type'] ) {
                        case 'Money':
                            require_once 'CRM/Utils/Rule.php';
                            $optionValue->value = number_format(CRM_Utils_Rule::cleanMoney( $v ),2);
                            break;
                        case 'Int':
                            $optionValue->value = intval( $v );
                            break;
                        case 'Float':
                            $optionValue->value = floatval( $v );
                            break;
                        default:
                            $optionValue->value = trim($v);
                        }
                        
                        $optionValue->weight          =  $params['option_weight'][$k];
                        $optionValue->is_active       =  CRM_Utils_Array::value( $k, $params['option_status'], false );
                        $optionValue->save( );
                    }
                }
            }
        }
        
        // check for orphan option groups
        if ( CRM_Utils_Array::value( 'option_group_id', $params ) ) {
            if ( CRM_Utils_Array::value( 'id', $params ) ) {
                self::fixOptionGroups( $params['id'], $params['option_group_id'] ) ;
            }

            // if we dont have a default value
            // retrive it from one of the other custom fields which use this option group
            if ( ! CRM_Utils_Array::value( 'default_value', $params ) ) {
                //don't insert only value separator as default value, CRM-4579
                $defaultValue = self::getOptionGroupDefault( $params['option_group_id'],
                                                             $params['html_type'] );
                
                if ( !CRM_Utils_System::isNull( explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $defaultValue ) ) ) { 
                    $params['default_value'] = $defaultValue;
                }
            }
        }

        // since we need to save option group id :)
        if ( !isset($params['attributes']) && strtolower( $params['html_type'] ) == 'textarea' ) {
            $params['attributes'] = 'rows=4, cols=60';
        }

        $customField = new CRM_Core_DAO_CustomField();
        $customField->copyValues( $params );
        $customField->is_required      = CRM_Utils_Array::value( 'is_required'    , $params, false );
        $customField->is_searchable    = CRM_Utils_Array::value( 'is_searchable'  , $params, false );
        $customField->is_search_range  = CRM_Utils_Array::value( 'is_search_range', $params, false );
        $customField->is_active        = CRM_Utils_Array::value( 'is_active'      , $params, false );
        $customField->is_view          = CRM_Utils_Array::value( 'is_view'        , $params, false );
        $customField->save( );
        
        // make sure all values are present in the object for further processing
        $customField->find(true);

        //create/drop the index when we toggle the is_searchable flag
        if ( CRM_Utils_Array::value( 'id', $params ) ) {
            self::createField( $customField, 'modify', $indexExist );
        } else {
            $customField->column_name .= "_{$customField->id}";
            $customField->save();
            // make sure all values are present in the object
            $customField->find(true);
            
            self::createField( $customField, 'add' );
        }
        
        // complete transaction
        $transaction->commit( );

        // reset the cache
        require_once 'CRM/Core/BAO/Cache.php';
        CRM_Core_BAO_Cache::deleteGroup( 'contact fields' );
    
        // reset various static arrays used here
        require_once 'CRM/Contact/BAO/Contact.php';
        CRM_Contact_BAO_Contact::$_importableFields = CRM_Contact_BAO_Contact::$_exportableFields = self::$_importFields = null;

        return $customField;
    }

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
     * @return object CRM_Core_DAO_CustomField object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults )
    {
        return CRM_Core_DAO::commonRetrieve( 'CRM_Core_DAO_CustomField', $params, $defaults );
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
        require_once 'CRM/Core/BAO/UFField.php';
        //enable-disable UFField 
        CRM_Core_BAO_UFField::setUFField($id, $is_active);
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_CustomField', $id, 'is_active', $is_active );
    }
    
    /**
     * Get the field title.
     *
     * @param int $id id of field.
     * @return string name 
     *
     * @access public
     * @static
     *
     */
    public static function getTitle( $id )
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField', $id, 'label' );
    }
    
    /**
     * Store and return an array of all active custom fields.
     *
     * @param string      $customDataType      type of Custom Data
     * @param boolean     $showAll             If true returns all fields (includes disabled fields)
     * @param boolean     $inline              If true returns all inline fields (includes disabled fields)
     * @param int         $customDataSubType   Custom Data sub type value
	 * @param int         $customDataSubName   Custom Data sub name value
     * @param boolean     $onlyParent          return only top level custom data, for eg, only Participant and ignore subname and subtype  
	 *
     * @return array      $fields - an array of active custom fields.
     *
     * @access public
     * @static
     */
    public static function &getFields( $customDataType = 'Individual',
                                       $showAll = false,
                                       $inline = false,
                                       $customDataSubType = null,
 									   $customDataSubName = null,
 									   $onlyParent = false,
                                       $onlySubType = false ) 
    {
        //$onlySubType = false;
        
        if ( $customDataType && 
             !is_array( $customDataType ) ) {
            
            if ( in_array($customDataType, 
                          CRM_Contact_BAO_ContactType::subTypes()) ) {
                // This is the case when getFieldsForImport() requires fields 
                // limited strictly to a subtype. 
                $customDataSubType = $customDataType;
                $customDataType    = CRM_Contact_BAO_ContactType::getBasicType( $customDataType );
                $onlySubType       = true;
            }

            if ( in_array($customDataType, 
                          array_keys(CRM_Core_SelectValues::customGroupExtends())) ) {
                // this makes the method flexible to support retrieving fields 
                // for multiple extends value.
                $customDataType = array( $customDataType );
            }
        }

        if ( is_array( $customDataType ) ) {
            $cacheKey  = implode( '_', $customDataType );
        } else {
            $cacheKey  = $customDataType;
        }
        $cacheKey .= $customDataSubType ? "{$customDataSubType}_" : "_0";
		$cacheKey .= $customDataSubName ? "{$customDataSubName}_" : "_0";
        $cacheKey .= $showAll ? "_1" : "_0";
        $cacheKey .= $inline  ? "_1_" : "_0_";
		$cacheKey .= $onlyParent  ? "_1_" : "_0_";
		$cacheKey .= $onlySubType  ? "_1_" : "_0_";
        
        $cgTable = CRM_Core_DAO_CustomGroup::getTableName();

        // also get the permission stuff here
        require_once 'CRM/Core/Permission.php';
        $permissionClause = CRM_Core_Permission::customGroupClause( CRM_Core_Permission::VIEW,
                                                                    "{$cgTable}." );

        // lets md5 permission clause and take first 8 characters
        $cacheKey .= substr( md5( $permissionClause ), 0, 8 );

        if ( strlen( $cacheKey ) > 40 ) {
            $cacheKey = md5( $cacheKey );
        }

        if ( ! self::$_importFields ||
             CRM_Utils_Array::value( $cacheKey, self::$_importFields ) === null ) { 
            if ( ! self::$_importFields ) {
                self::$_importFields = array( );
            }

            // check if we can retrieve from database cache
            require_once 'CRM/Core/BAO/Cache.php'; 
            $fields =& CRM_Core_BAO_Cache::getItem( 'contact fields', "custom importableFields $cacheKey" );

            if ( $fields === null ) {
                $cfTable = self::getTableName();

                $extends = '';
                if ( is_array( $customDataType ) ) {
                    $value = null;
                    foreach ( $customDataType as $dataType ) {
                        if ( in_array ( $dataType, 
                                        array_keys(CRM_Core_SelectValues::customGroupExtends()) ) ) {
                            if ( in_array( $dataType, array( 'Individual', 'Household', 'Organization' ) ) ) {
                                $val = "'" . CRM_Utils_Type::escape( $dataType, 'String' ) . "', 'Contact' ";
                            } else {
                                $val = "'" . CRM_Utils_Type::escape($dataType, 'String') . "'";
                            }
                            $value = $value ? $value . ", {$val}" : $val;
                        }
                    }
                    if ( $value ) {
                        $extends = "AND   $cgTable.extends IN ( $value ) ";
                    }
                }

                if ( $onlyParent ) {
                    $extends .= " AND $cgTable.extends_entity_column_value IS NULL AND $cgTable.extends_entity_column_id IS NULL ";
                }
                
                $query ="SELECT $cfTable.id, $cfTable.label,
                            $cgTable.title,
                            $cfTable.data_type, $cfTable.html_type,
                            $cfTable.options_per_line,
                            $cgTable.extends, $cfTable.is_search_range,
                            $cgTable.extends_entity_column_value,
                            $cgTable.extends_entity_column_id,
                            $cfTable.is_view,
                            $cfTable.option_group_id,
                            $cfTable.date_format,
                            $cfTable.time_format,
                            $cgTable.is_multiple
                     FROM $cfTable
                     INNER JOIN $cgTable
                     ON $cfTable.custom_group_id = $cgTable.id
                     WHERE ( 1 ) ";

                if (! $showAll) {
                    $query .= " AND $cfTable.is_active = 1 AND $cgTable.is_active = 1 ";
                }

                if ( $inline ) {
                    $query .= " AND $cgTable.style = 'Inline' ";
                }
                
                //get the custom fields for specific type in
                //combination with fields those support any type.
                if ( $customDataSubType ) {
                    $customDataSubType = 
                        CRM_Core_DAO::VALUE_SEPARATOR . $customDataSubType . CRM_Core_DAO::VALUE_SEPARATOR;
                    $query .= " AND ( $cgTable.extends_entity_column_value LIKE '%$customDataSubType%'";
                    if ( !$onlySubType ) {
                        $query .= " OR $cgTable.extends_entity_column_value IS NULL";
                    }
                    $query .= " )";
                }
                
                if ( $customDataSubName ) {
                    $query .= " AND ( $cgTable.extends_entity_column_id = $customDataSubName ) "; 
                }

                // also get the permission stuff here
                require_once 'CRM/Core/Permission.php';
                $permissionClause = CRM_Core_Permission::customGroupClause( CRM_Core_Permission::VIEW,
                                                                            "{$cgTable}.", true );

                $query .= " $extends AND $permissionClause
                        ORDER BY $cgTable.weight, $cgTable.title,
                                 $cfTable.weight, $cfTable.label";
         
                $dao =& CRM_Core_DAO::executeQuery( $query );

                $fields = array( );
                while ( ( $dao->fetch( ) ) != null) {
                    $fields[$dao->id]['label']                       = $dao->label;
                    $fields[$dao->id]['groupTitle']                  = $dao->title;
                    $fields[$dao->id]['data_type']                   = $dao->data_type;
                    $fields[$dao->id]['html_type']                   = $dao->html_type;
                    $fields[$dao->id]['options_per_line']            = $dao->options_per_line;
                    $fields[$dao->id]['extends']                     = $dao->extends;
                    $fields[$dao->id]['is_search_range']             = $dao->is_search_range;
                    $fields[$dao->id]['extends_entity_column_value'] = $dao->extends_entity_column_value;
                    $fields[$dao->id]['extends_entity_column_id']    = $dao->extends_entity_column_id;
                    $fields[$dao->id]['is_view']                     = $dao->is_view;
                    $fields[$dao->id]['is_multiple']                 = $dao->is_multiple;
                    $fields[$dao->id]['option_group_id']             = $dao->option_group_id;
                    $fields[$dao->id]['date_format']                 = $dao->date_format;
                    $fields[$dao->id]['time_format']                 = $dao->time_format;
                }

                CRM_Core_BAO_Cache::setItem( $fields,
                                             'contact fields',
                                             "custom importableFields $cacheKey" );
            }
            self::$_importFields[$cacheKey] = $fields;
        }
        
        return self::$_importFields[$cacheKey];
    }

    /**
     * Return the field ids and names (with groups) for import purpose.
     *
     * @param int      $contactType   Contact type
     * @param boolean  $showAll       If true returns all fields (includes disabled fields)
     *
     * @return array   $fields - 
     *
     * @access public
     * @static
     */
    public static function &getFieldsForImport( $contactType = 'Individual', $showAll = false, $onlyParent = false ) 
    {
        // Note: there are situations when we want getFieldsForImport() return fields related 
        // ONLY to basic contact types, but NOT subtypes. And thats where $onlyParent is helpful
        $fields =& self::getFields( $contactType, $showAll, false, null, null, $onlyParent );

        $importableFields = array();
        foreach ($fields as $id => $values) {
            // for now we should not allow multiple fields in profile / export etc, hence unsetting
            if ( CRM_Utils_Array::value('is_multiple', $values) ) {
                continue;
            }
            /* generate the key for the fields array */
            $key = "custom_$id";

            $regexp = preg_replace('/[.,;:!?]/', '', CRM_Utils_Array::value( 0, $values ) );
            $importableFields[$key] = array(
                                            'name'             => $key,
                                            'title'            => CRM_Utils_Array::value('label', $values),
                                            'headerPattern'    => '/' . preg_quote($regexp, '/') . '/',
                                            'import'           => 1,
                                            'custom_field_id'  => $id,
                                            'options_per_line' => CRM_Utils_Array::value('options_per_line', $values),
                                            'data_type'        => CRM_Utils_Array::value('data_type', $values),
                                            'html_type'        => CRM_Utils_Array::value('html_type', $values),
                                            'is_search_range'  => CRM_Utils_Array::value('is_search_range', $values),
                                            );

            // CRM-6681, pass date and time format when html_type = Select Date 
            if ( CRM_Utils_Array::value('html_type', $values) == 'Select Date' ) {
                $importableFields[$key]['date_format'] = CRM_Utils_Array::value('date_format', $values);
                $importableFields[$key]['time_format'] = CRM_Utils_Array::value('time_format', $values);
            }
        }
         
        return $importableFields;
    }

    /**
     * Get the field id from an import key
     *
     * @param string $key       The key to parse
     * @return int|null         The id (if exists)
     * @access public
     * @static
     */
    public static function getKeyID($key, $all = false) 
    {
        $match = array( );
        if (preg_match('/^custom_(\d+)_?(-?\d+)?$/', $key, $match)) {
            if ( ! $all ) {
                return $match[1];
            } else {
                return array( $match[1],
                              CRM_Utils_Array::value( 2, $match ) );
            } 
        }
        return null;
    }
    
    
    /**
     * This function for building custom fields
     * 
     * @param object  $qf             form object (reference)
     * @param string  $elementName    name of the custom field
     * @param boolean $inactiveNeeded 
     * @param boolean $userRequired   true if required else false
     * @param boolean $search         true if used for search else false
     * @param string  $label          label for custom field        
     *
     * @access public
     * @static
     */
    public static function addQuickFormElement( &$qf,
                                                $elementName,
                                                $fieldId,
                                                $inactiveNeeded = false,
                                                $useRequired = true,
                                                $search = false,
                                                $label = null ) 
    {
        // we use $_POST directly, since we dont want to use session memory, CRM-4677
        if( isset( $_POST['_qf_Relationship_refresh'] ) && 
            ( $_POST['_qf_Relationship_refresh'] == 'Search' || 
              $_POST['_qf_Relationship_refresh'] == 'Search Again') ) {
            $useRequired = 0;
        }
        
        $field = new CRM_Core_DAO_CustomField();
        
        $field->id = $fieldId;
        if (! $field->find(true)) {
            CRM_Core_Error::fatal( );

        }
        // Fixed for Issue CRM-2183
        if ( $field->html_type == 'TextArea' && $search ){
            $field->html_type = 'Text';
        }
        if (!isset($label)) {
            $label = $field->label;
        }
        /**
         * at some point in time we might want to split the below into small functions
         **/
     
        switch ( $field->html_type ) {
        case 'Text':
            if ($field->is_search_range && $search) {
                $qf->add('text', $elementName.'_from', $label . ' ' . ts('From'), $field->attributes);
                $qf->add('text', $elementName.'_to', ts('To'), $field->attributes);
            } else {
                $element =& $qf->add(strtolower($field->html_type), $elementName, $label,
                                     $field->attributes, (( $useRequired ||( $useRequired && $field->is_required) ) && !$search));
            }
            break;

        case 'TextArea':
            $attributes = '';
            if( $field->note_rows ) {
                $attributes .='rows='.$field->note_rows; 
            } else {
                $attributes .='rows=4';
            }
            
            if( $field->note_columns ) {
                $attributes .=' cols='.$field->note_columns;
            } else {
                $attributes .=' cols=60';
            }
            $element =& $qf->add(strtolower($field->html_type), $elementName, $label,
                                 $attributes, (( $useRequired ||( $useRequired && $field->is_required) ) && !$search));
            break;

        case 'Select Date':
            if ( $field->is_search_range && $search ) {
                $qf->addDate( $elementName.'_from', $label . ' - ' . ts('From'), false, 
                              array( 'format'      =>  $field->date_format,
                                     'timeFormat'  =>  $field->time_format,     
                                     'startOffset' =>  $field->start_date_years,
                                     'endOffset'   =>  $field->end_date_years) );

                $qf->addDate( $elementName.'_to', ts('To'), false, 
                              array( 'format'      =>  $field->date_format,
                                     'timeFormat'  =>  $field->time_format,
                                     'startOffset' =>  $field->start_date_years,
                                     'endOffset'   =>  $field->end_date_years) );
            } else {
                $required = ( ( $useRequired ||( $useRequired && $field->is_required ) ) && !$search );
                
                $qf->addDate( $elementName, $label, $required, array( 'format'      =>  $field->date_format,
                                                                      'timeFormat'  =>  $field->time_format,    
                                                                      'startOffset' =>  $field->start_date_years,
                                                                      'endOffset'   =>  $field->end_date_years) );
            }
            break;

        case 'Radio':
            $choice = array();
            if($field->data_type != 'Boolean') {
                $customOption =& CRM_Core_BAO_CustomOption::valuesByID( $field->id,
                                                                        $field->option_group_id );
                foreach ($customOption as $v => $l ) {
                    $choice[] = $qf->createElement('radio', null, '', $l, (string)$v, $field->attributes);
                }
                $qf->addGroup($choice, $elementName, $label);
            } else {
                $choice[] = $qf->createElement('radio', null, '', ts('Yes'), '1', $field->attributes);
                $choice[] = $qf->createElement('radio', null, '', ts('No') , '0' , $field->attributes);
                $qf->addGroup($choice, $elementName, $label);
            }
            if (( $useRequired ||( $useRequired && $field->is_required) ) && !$search) {
                $qf->addRule($elementName, ts('%1 is a required field.', array(1 => $label)) , 'required');
            }
            break;
            
        case 'Select':
            $selectOption =& CRM_Core_BAO_CustomOption::valuesByID( $field->id,
                                                                    $field->option_group_id );
            $qf->add('select', $elementName, $label,
                     array( '' => ts('- select -')) + $selectOption,
                     ( ( $useRequired || ($useRequired && $field->is_required) ) && !$search));
            break;

            //added for select multiple
        case 'AdvMulti-Select': 
            $selectOption =& CRM_Core_BAO_CustomOption::valuesByID( $field->id,
                                                                    $field->option_group_id );
            $include =& $qf->addElement('advmultiselect', $elementName, 
                                        $label, $selectOption,
                                        array('size' => 5,
                                              'style'=> '',
                                              'class' => 'advmultiselect')
                                        );
            
            $include->setButtonAttributes('add', array('value' => ts('Add >>')));
            $include->setButtonAttributes('remove', array('value' => ts('<< Remove')));     
           
            if (( $useRequired ||( $useRequired && $field->is_required) ) && !$search) {
                $qf->addRule($elementName, ts('%1 is a required field.', array(1 => $label)) , 'required');
            }  
            break;
            
        case 'Multi-Select':
            $selectOption =& CRM_Core_BAO_CustomOption::valuesByID( $field->id,
                                                                    $field->option_group_id );
            if ( $search &&
                 count( $selectOption ) > 1 ) {
                $selectOption['CiviCRM_OP_OR'] = ts( 'Select to match ANY; unselect to match ALL' );
            }
            $qf->addElement('select', $elementName, $label, $selectOption,  array("size"=>"5","multiple"));
            
            if (( $useRequired ||( $useRequired && $field->is_required) ) && !$search) {
                $qf->addRule($elementName, ts('%1 is a required field.', array(1 => $label)) , 'required');
            }
            break;

        case 'CheckBox':
            $customOption = CRM_Core_BAO_CustomOption::valuesByID( $field->id,
                                                                   $field->option_group_id );
            $check = array();
            foreach ($customOption as $v => $l) {
                $check[] =& $qf->addElement('advcheckbox', $v, null, $l); 
            }
            if ( $search &&
                 count( $check ) > 1 ) {
                 $check[] =& $qf->addElement('advcheckbox', 'CiviCRM_OP_OR', null, ts( 'Check to match ANY; uncheck to match ALL' ) ); 
            }
            $qf->addGroup($check, $elementName, $label);
            if (( $useRequired ||( $useRequired && $field->is_required) ) && !$search) {
                $qf->addRule($elementName, ts('%1 is a required field.', array(1 => $label)) , 'required');
            }
            break;
            
        case 'File':
            // we should not build upload file in search mode
            if ( $search ) {
                return;
            }
            $element =& $qf->add( strtolower($field->html_type), $elementName, $label,
                                  $field->attributes,
                                  ( ( $useRequired && $field->is_required ) && ! $search ) );
            $qf->addUploadElement( $elementName );
            break;

        case 'Select State/Province':
            //Add State
            $stateOption = array('' => ts('- select -')) + CRM_Core_PseudoConstant::stateProvince();
            $qf->add('select', $elementName, $label, $stateOption, (($useRequired && $field->is_required) && !$search));
            break;
        case 'Multi-Select State/Province':
            //Add Multi-select State/Province
            $stateOption = CRM_Core_PseudoConstant::stateProvince();
            
            $qf->addElement('select', $elementName, $label, $stateOption, array("size"=>"5","multiple"));
            if (( $useRequired ||( $useRequired && $field->is_required) ) && !$search) {
                $qf->addRule($elementName, ts('%1 is a required field.', array(1 => $label)) , 'required');
            }
            break;
            
        case 'Select Country':
            //Add Country
            $countryOption = array('' => ts('- select -')) + CRM_Core_PseudoConstant::country();
            $qf->add('select', $elementName, $label, $countryOption, (($useRequired && $field->is_required) && !$search));
            break;

        case 'Multi-Select Country':
            //Add Country
            $countryOption = CRM_Core_PseudoConstant::country();
            $qf->addElement('select', $elementName, $label, $countryOption, array("size"=>"5","multiple"));
            if (( $useRequired ||( $useRequired && $field->is_required) ) && !$search) {
                $qf->addRule($elementName, ts('%1 is a required field.', array(1 => $label)) , 'required');
            }
            break;
        
        case 'RichTextEditor':
            $element =& $qf->addWysiwyg( $elementName, $label, array( 'rows' => $field->note_rows ), $search );
            break;
                    
        case 'Autocomplete-Select':
            $qf->add( 'text', $elementName, $label, $field->attributes, 
                    (( $useRequired ||( $useRequired && $field->is_required) ) && !$search));
            $qf->addElement( 'hidden', $elementName . '_id', '', array( 'id' => $elementName. '_id' ) );

            static $customUrls = array( );            
            if ( $field->data_type == 'ContactReference' )  {
                $customUrls[$elementName] = CRM_Utils_System::url( "civicrm/ajax/rest",                                                     "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&reset=1&context=customfield&id={$field->id}",
                                                                   false, null, false );                
               
                $actualElementValue = $qf->_submitValues[ $elementName .'_id'];    
                $qf->addRule($elementName, ts('Select a valid contact for %1.', array(1 => $label)), 'validContact', $actualElementValue );
            } else {
                $customUrls[$elementName] = CRM_Utils_System::url( "civicrm/ajax/auto",
                                                                   "reset=1&ogid={$field->option_group_id}&cfid={$field->id}",
                                                                   false, null, false );                
                $qf->addRule($elementName, ts('Select a valid value for %1.', array(1 => $label)) , 
                                          'autocomplete', array( 'fieldID'       => $field->id,
                                                                 'optionGroupID' => $field->option_group_id ) );
            }
                                                
            $qf->assign( "customUrls", $customUrls );                                          
            break;
        }
        
        switch ( $field->data_type ) {

        case 'Int':
            // integers will have numeric rule applied to them.
            if ( $field->is_search_range && $search) {
                $qf->addRule($elementName.'_from', ts('%1 From must be an integer (whole number).', array(1 => $label)),'integer');
                $qf->addRule($elementName.'_to', ts('%1 To must be an integer (whole number).', array(1 => $label)), 'integer');
            } else {
                $qf->addRule($elementName, ts('%1 must be an integer (whole number).', array(1 => $label)), 'integer');
            }
            break;

        case 'Float':
            if ( $field->is_search_range && $search) {
                $qf->addRule($elementName.'_from', ts('%1 From must be a number (with or without decimal point).', array(1 => $label)), 'numeric');
                $qf->addRule($elementName.'_to', ts('%1 To must be a number (with or without decimal point).', array(1 => $label)), 'numeric');
            } else {
                $qf->addRule($elementName, ts('%1 must be a number (with or without decimal point).', array(1 => $label)), 'numeric');
            }
            break;

        case 'Money':
            if ( $field->is_search_range && $search) {
                $qf->addRule($elementName.'_from', ts('%1 From must in proper money format. (decimal point/comma/space is allowed).', array(1 => $label)), 'money');
                $qf->addRule($elementName.'_to', ts('%1 To must in proper money format. (decimal point/comma/space is allowed).', array(1 => $label)), 'money');
            } else {
                $qf->addRule($elementName, ts('%1 must be in proper money format. (decimal point/comma/space is allowed).', array(1 => $label)), 'money');
            }
            break;

        case 'Link':
            $element =& $qf->add('text',
                                 $elementName,
                                 $label,
                                 array('onfocus' => "if (!this.value) this.value='http://'; else return false",
                                       'onblur'  => "if ( this.value == 'http://') this.value=''; else return false"),
                                 (( $useRequired ||( $useRequired && $field->is_required) ) && !$search));
            $qf->addRule( $elementName, ts('Enter a valid Website.'),'wikiURL');
                    
            break;
    
        }
    }
    
    /**
     * Delete the Custom Field.
     *
     * @param   object $field - the field object
     * 
     * @return  boolean
     *
     * @access public
     * @static
     *
     */
    public static function deleteField( $field )
    { 
        // reset the cache
        require_once 'CRM/Core/BAO/Cache.php';
        CRM_Core_BAO_Cache::deleteGroup( 'contact fields' );

        // reset various static arrays used here
        require_once 'CRM/Contact/BAO/Contact.php';
        CRM_Contact_BAO_Contact::$_importableFields = CRM_Contact_BAO_Contact::$_exportableFields = self::$_importFields = null;

        // first delete the custom option group and values associated with this field
        if ( $field->option_group_id ) {
            //check if option group is related to any other field, if
            //not delete the option group and related option values
            self::checkOptionGroup(  $field->option_group_id );
        }

        // next drop the column from the custom value table
        self::createField( $field, 'delete' );

        $field->delete( );
        return;
    }

    /**
     * Given a custom field value, its id and the set of options
     * find the display value for this field
     *
     * @param mixed  $value     the custom field value
     * @param int    $id        the custom field id
     * @param int    $options   the assoc array of option name/value pairs
     *
     * @return  string   the display value
     * 
     * @static
     * @access public
     */
    static function getDisplayValue( $value, $id, &$options, $contactID = null )
    {
        $option     =& $options[$id];
        $attributes =& $option['attributes'];
        $html_type  =  $attributes['html_type'];
        $data_type  =  $attributes['data_type'];
        $format     =  CRM_Utils_Array::value( 'format', $attributes );

        return self::getDisplayValueCommon( $value,
                                            $option,
                                            $html_type,
                                            $data_type,
                                            $format,
                                            $contactID );
    }

    static function getDisplayValueCommon( $value,
                                           &$option,
                                           $html_type,
                                           $data_type,
                                           $format = null,
                                           $contactID = null )
    {
        $display = $value;
        
        switch ( $html_type ) {

        case "Radio":
            if ( $data_type == 'Boolean' ) {
                $display = $value ? ts('Yes') : ts('No');
            } else {
                $display = CRM_Utils_Array::value( $value, $option );
            }
            break;

        case "Autocomplete-Select":
            if ( $data_type == 'ContactReference' &&
                 $value ) {
                $display = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $value, 'display_name' );
            } else {
                $display = CRM_Utils_Array::value( $value, $option );
            }
            break;

        case "Select":
            $display = CRM_Utils_Array::value( $value, $option );
            break;
        
        case "CheckBox":
        case "AdvMulti-Select":
        case "Multi-Select":
            if ( is_array( $value ) ) {
                $checkedData = $value;
            } else {
                $checkedData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
                if ( $html_type == 'CheckBox' ) {
                    $newData = array( );
                    foreach ( $checkedData as $v) {
                        $newData[$v] =1; 
                    }
                    $checkedData = $newData;
                }
            }

            $v = array( );
            $p = array( );
            foreach ( $checkedData as $key => $val ) {
                if ( $key === 'CiviCRM_OP_OR') {
                    continue;
                }

                if ( $html_type == 'CheckBox' ) {
                    if ( $val ) {
                        $p[] = $key;
                        $v[] = CRM_Utils_Array::value( $key, $option );
                    }
                } else {
                    $p[] = $val;
                    $v[] = CRM_Utils_Array::value( $val, $option );
                }
            }
            if ( ! empty( $v ) ) {
                $display = implode( ', ', $v );
            }
            break;

        case "Select Date":
            // remove time element display if time is not set
            if ( !$option['attributes']['time_format'] ) {
                $value = substr( $value, 0, 10 );
            }
            
            $display = CRM_Utils_Date::customFormat( $value );
            break;

        case 'Select State/Province':
            if ( empty( $value ) ) {
                $display = '';
            } else {
                $display = CRM_Core_PseudoConstant::stateProvince($value);
            }
            break;

        case 'Multi-Select State/Province':
            if ( is_array( $value ) ) {    
                $checkedData = $value;
            } else {
                $checkedData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
            }

            $states = CRM_Core_PseudoConstant::stateProvince( );
            $display = null;
            foreach ( $checkedData as $stateID ) {
                if ( $display ) {
                    $display .= ", ";
                }
                $display .= $states[$stateID];
            }
            break;
            
        case 'Select Country':
            if ( empty( $value ) ) {
                $display = '';
            } else {
                $display = CRM_Core_PseudoConstant::country($value);
            }
            break;
            
        case 'Multi-Select Country':
            if ( is_array( $value ) ) {    
                $checkedData = $value;
            } else {
                $checkedData = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
            }

            $countries = CRM_Core_PseudoConstant::country( );
            $display = null;
            foreach ( $checkedData as $countryID ) {
                if ( $display ) {
                    $display .= ", ";
                }
                $display .= $countries[$countryID];
            }
            break;

        case 'File':
            if ( $contactID ) {
                $url = self::getFileURL( $contactID, $id, $value );
                if ( $url ) {
                    $display = $url['file_url'];
                }
            }
            break;
        
        case 'TextArea':
            if ( empty( $value ) ) {
                $display='';
            } else {
                $display = nl2br($value);
            }
            break;
            
        case 'Link':
            if ( empty( $value ) ) {
                $display='';
            } else {
                $display = $value;
            }  
                
        }
        
        return $display ? $display : $value;
    }
    
    /**
     * Function to set default values for custom data used in profile
     *
     * @params int    $customFieldId custom field id
     * @params string $elementName   custom field name
     * @params array  $defaults      associated array of fields
     * @params int    $contactId     contact id
     * @param  int    $mode          profile mode
     * 
     * @static
     * @access public
     */
    static function setProfileDefaults( $customFieldId,
                                        $elementName,
                                        &$defaults,
                                        $contactId = null,
                                        $mode = null ) 
    {
        //get the type of custom field
        $customField = new CRM_Core_BAO_CustomField();
        $customField->id = $customFieldId;
        $customField->find(true);
        
        require_once "CRM/Profile/Form.php";
        $value = null;
        if ( ! $contactId ) {
            if ($mode == CRM_Profile_Form::MODE_CREATE ) {
                $value = $customField->default_value;
            }
        } else {
            $info   = self::getTableColumnGroup( $customFieldId );
            $query  = "SELECT {$info[0]}.{$info[1]} as value FROM {$info[0]} WHERE {$info[0]}.entity_id = {$contactId}";
            $result = CRM_Core_DAO::executeQuery( $query );
            if ( $result->fetch( ) ) {
                $value = $result->value;
            }
            
            if ( $customField->data_type == 'Country' ) {
                if ( ! $value ) {
                    $config = CRM_Core_Config::singleton();
                    if ( $config->defaultContactCountry ) {
                        $value = $config->defaultContactCountry( );
                    }
                }
            }
        }
        
        //set defaults if mode is registration
        if ( ! trim( $value ) &&
             ( $value !== 0 ) &&
             ( !in_array( $mode, array( CRM_Profile_Form::MODE_EDIT, CRM_Profile_Form::MODE_SEARCH ) ) ) ) {
            $value = $customField->default_value;
        }

        if ( $customField->data_type == 'Money' && isset($value) ) {
            $value = number_format ( $value, 2 );
        }
        switch ($customField->html_type) {
            
        case 'CheckBox':
        case 'AdvMulti-Select':
        case 'Multi-Select':
            $customOption = CRM_Core_BAO_CustomOption::getCustomOption($customFieldId, false);
            $defaults[$elementName] = array();
            $checkedValue = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, substr($value,1,-1));
            foreach($customOption as $val) {
                if ( in_array($val['value'], $checkedValue) ) {
                    if ( $customField->html_type == 'CheckBox' ) {
                        $defaults[$elementName][$val['value']] = 1;
                    } elseif ( $customField->html_type == 'Multi-Select' ||
                               $customField->html_type == 'AdvMulti-Select' ) {
                        $defaults[$elementName][$val['value']] = $val['value'];
                    }
                }        
            }
            break;

        case 'Select Date':
            if ( $value ) {
                list( $defaults[$elementName], $defaults[$elementName . '_time' ] ) = CRM_Utils_Date::setDateDefaults( $value,
                                                                                                                       null,
                                                                                                                       $customField->date_format,
                                                                                                                       $customField->time_format );
            }
            break;
            
        default:
            $defaults[$elementName] = $value;
        }
    }

    static function getFileURL( $contactID, $cfID, $fileID = NULL ) 
    {
        if ( $contactID ) {
            if ( ! $fileID ) {
                $params   = array( 'id' => $cfID );
                $defaults = array();
                CRM_Core_DAO::commonRetrieve( 'CRM_Core_DAO_CustomField', $params, $defaults );
                $columnName = $defaults['column_name'];
            
                //table name of custom data
                $tableName  = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup', 
                                                           $defaults['custom_group_id'], 
                                                           'table_name', 'id' );
                
                //query to fetch id from civicrm_file
                $query = "SELECT {$columnName} FROM {$tableName} where entity_id = {$contactID}";
                $fileID = CRM_Core_DAO::singleValueQuery( $query );
            }
            
            $result = array();
            if ( $fileID ) {
                $fileType = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_File',
                                                         $fileID,
                                                         'mime_type',
                                                         'id' );
                $result['file_id'] = $fileID;
                
                if ( $fileType == "image/jpeg"  ||
                     $fileType == "image/pjpeg" ||
                     $fileType == "image/gif"   ||
                     $fileType == "image/x-png" ||
                     $fileType == "image/png" ) { 
                    $entityId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_EntityFile',
                                                             $fileID,
                                                             'entity_id',
                                                             'id' );
                    require_once 'CRM/Core/BAO/File.php';
                    list( $path ) = CRM_Core_BAO_File::path( $fileID, $entityId, null, null);
                    list( $imageWidth, $imageHeight ) = getimagesize( $path );
                    list( $imageThumbWidth, $imageThumbHeight ) = CRM_Contact_BAO_Contact::getThumbSize( $imageWidth, $imageHeight );
                    $url = CRM_Utils_System::url( 'civicrm/file', "reset=1&id=$fileID&eid=$contactID" );
                    $result['file_url'] = "<a href='javascript:imagePopUp(\"$url\");'><img src=\"$url\" width=$imageThumbWidth height=$imageThumbHeight/></a>";
                } else { // for non image files
                    $uri = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_File',
                                                         $fileID,
                                                         'uri'
                                                        );
                    $url = CRM_Utils_System::url( 'civicrm/file', "reset=1&id=$fileID&eid=$contactID" );
                    $result['file_url'] = "<a href=\"$url\">{$uri}</a>";
                }                                    
            }
            return $result;
        }
    }
    
    /**
     * Format custom fields before inserting
     *
     * @param int    $customFieldId       custom field id
     * @param array  $customFormatted     formatted array
     * @param mix    $value               value of custom field
     * @param string $customFieldExtend   custom field extends
     * @param int    $customValueId custom option value id
     * @param int    $entityId            entity id (contribution, membership...)
     *
     * @return array $customFormatted formatted custom field array
     * @static
     */
    static function formatCustomField( $customFieldId, &$customFormatted, $value, 
                                       $customFieldExtend, $customValueId = null,
                                       $entityId = null, 
                                       $inline = false ) 
    {
        //get the custom fields for the entity
        //subtype and basic type
        $customDataSubType = null;
        if ( in_array($customFieldExtend, 
                      CRM_Contact_BAO_ContactType::subTypes()) ) {
            // This is the case when getFieldsForImport() requires fields 
            // of subtype and its parent.CRM-5143 
            $customDataSubType = $customFieldExtend;
            $customFieldExtend = CRM_Contact_BAO_ContactType::getBasicType( $customDataSubType );
        }
        
        $customFields = CRM_Core_BAO_CustomField::getFields( $customFieldExtend, false, $inline, $customDataSubType );
        
        if ( ! array_key_exists( $customFieldId, $customFields )) {
            return;
        }

        // return if field is a 'code' field
        if ( CRM_Utils_Array::value( 'is_view', $customFields[$customFieldId] ) ) {
            return;
        }

        list( $tableName, $columnName, $groupID ) = self::getTableColumnGroup( $customFieldId );
        
        if ( is_array( $customFieldExtend ) ) {
            $customFieldExtend = $customFieldExtend[0];
        }

        if ( ! $customValueId &&
             ! $customFields[$customFieldId]['is_multiple'] && // we always create new entites for is_multiple unless specified
             $entityId ) {
            //get the entity table for the custom field
            require_once "CRM/Core/BAO/CustomQuery.php";
            $entityTable = CRM_Core_BAO_CustomQuery::$extendsMap[$customFieldExtend];

            $query = "
SELECT id 
  FROM $tableName
 WHERE entity_id={$entityId}";

            $customValueId = CRM_Core_DAO::singleValueQuery( $query );
        }

        //fix checkbox, now check box always submits values
        if ( $customFields[$customFieldId]['html_type'] == 'CheckBox' ) {                
            if ( $value ) {
                // Note that only during merge this is not an array, and you can directly use value
                if ( is_array( $value ) ) { 
                    $selectedValues = null;
                    foreach ( $value as $selId => $val ) {
                        if ( $val ) {
                            $selectedValues .= $selId . CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
                        }
                    }
                    if ( $selectedValues ) {
                        $value = CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . $selectedValues;
                    } else {
                        $value = '';
                    }
                }
            }
        }  
        
        if ( $customFields[$customFieldId]['html_type'] == 'Multi-Select' ||
             $customFields[$customFieldId]['html_type'] == 'AdvMulti-Select' ) {
            if ( $value ) {
                // Note that only during merge this is not an array,
                // and you can directly use value, CRM-4385
                if ( is_array( $value ) ) {
                    $value = 
                        CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . 
                        implode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,
                                 array_values( $value ) ) .
                        CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
                }
            } else {
                $value = '';
            }
        }
        
        $date = null;
        if ( $customFields[$customFieldId]['data_type'] == 'Date' ) {
            if ( ! CRM_Utils_System::isNull( $value ) ) {
                $format = $customFields[$customFieldId]['date_format'];
                
                if ( in_array( $format, array('dd-mm', 'mm/dd' ) ) ) {
                    $dateTimeArray = explode(' ', $value);

                    $separator = '/';
                    if ( $format == 'dd-mm' ) {
                        $separator = '-';
                    }
                    $value = $dateTimeArray[0] . $separator . '1902';
                    
                    if ( array_key_exists( 1, $dateTimeArray) ) {
                        $value .= ' ' . $dateTimeArray[1];
                    }
                } else if ( $format == 'yy' ) {
                    $value = "01-01-{$value}";
                }
                
                $date = CRM_Utils_Date::processDate( $value, null, false, 'YmdHis', $format );
            }
            $value = $date;
        }

        if ( $customFields[$customFieldId]['data_type'] == 'Float' || 
             $customFields[$customFieldId]['data_type'] == 'Money' ) {
            if ( !$value ) {
                $value = 0;  
            }

            if ( $customFields[$customFieldId]['data_type'] == 'Money' ) {
                require_once 'CRM/Utils/Rule.php';
                $value = CRM_Utils_Rule::cleanMoney( $value );
            }
        }
               
        if ( ( $customFields[$customFieldId]['data_type'] == 'StateProvince' || 
               $customFields[$customFieldId]['data_type'] == 'Country') &&
             empty( $value ) ) {
            // CRM-3415
            $value = 0;
        }

        $fileId = null;

        if ( $customFields[$customFieldId]['data_type'] == 'File' ) {
            if ( empty($value) ) {
                return;
            }

            require_once 'CRM/Core/DAO/File.php';
            $config = & CRM_Core_Config::singleton();

            $fName    = $value['name']; 
            $mimeType = $value['type']; 

            $path = explode( '/', $fName );
            $filename = $path[count($path) - 1];
            
            // rename this file to go into the secure directory
            if ( ! rename( $fName, $config->customFileUploadDir . $filename ) ) {
                CRM_Core_Error::statusBounce( ts( 'Could not move custom file to custom upload directory' ) );
                break;
            }

            if ( $customValueId ) {
                $query = "
SELECT $columnName
  FROM $tableName
 WHERE id = %1";
                $params = array( 1 => array( $customValueId, 'Integer' ) );
                $fileId = CRM_Core_DAO::singleValueQuery( $query, $params );
            }
            
            $fileDAO = new CRM_Core_DAO_File();
            
            if ( $fileId ) {
                $fileDAO->id = $fileId;
            }
            
            $fileDAO->uri         = $filename;
            $fileDAO->mime_type   = $mimeType; 
            $fileDAO->upload_date = date('Ymdhis'); 
            $fileDAO->save();
            $fileId = $fileDAO->id;
            $value  =  $filename;
        }

        if ( !is_array( $customFormatted ) ) {
            $customFormatted = array( );
        }

        if ( ! array_key_exists( $customFieldId, $customFormatted ) ) {
            $customFormatted[$customFieldId] = array( );
        }

        $index = -1;
        if ( $customValueId ) {
            $index = $customValueId;
        }

        if ( ! array_key_exists( $index, $customFormatted[$customFieldId] ) ) {
            $customFormatted[$customFieldId][$index] = array( );
        }
        $customFormatted[$customFieldId][$index] = array('id'              => $customValueId > 0 ? $customValueId : null,
                                                         'value'           => $value,
                                                         'type'            => $customFields[$customFieldId]['data_type'],
                                                         'custom_field_id' => $customFieldId, 
                                                         'custom_group_id' => $groupID,
                                                         'table_name'      => $tableName,
                                                         'column_name'     => $columnName,
                                                         'file_id'         => $fileId,
                                                         'is_multiple'     => $customFields[$customFieldId]['is_multiple'],
                                                         );

        //we need to sort so that custom fields are created in the order of entry
        krsort( $customFormatted[$customFieldId] );
        return $customFormatted;
    }

    static function &defaultCustomTableSchema( &$params ) 
    {
        // add the id and extends_id
        $table = array( 'name'        => $params['name'],
                        'is_multiple' => $params['is_multiple'],
                        'attributes'  => "ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci",
                        'fields'      => array(
                                               array( 'name'          => 'id',
                                                      'type'          => 'int unsigned',
                                                      'primary'       => true,
                                                      'required'      => true,
                                                      'attributes'    => 'AUTO_INCREMENT',
                                                      'comment'       => 'Default MySQL primary key' ),
                                               array( 'name'          => 'entity_id',
                                                      'type'          => 'int unsigned',
                                                      'required'      => true,
                                                      'comment'       => 'Table that this extends',
                                                      'fk_table_name' => $params['extends_name'],
                                                      'fk_field_name' => 'id',
                                                      'fk_attributes' => 'ON DELETE CASCADE' )
                                               )
                        );
        
        if ( ! $params['is_multiple'] ) {
            $table['indexes'] = array(
                                      array( 'unique'        => true,
                                             'field_name_1'  => 'entity_id' )
                                      );
        }
        return $table;
    }

    static function createField( $field, $operation, $indexExist = false ) 
    {
        require_once 'CRM/Core/BAO/CustomValueTable.php';
        $tableName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                  $field->custom_group_id,
                                                  'table_name' );
        
        $params = array( 'table_name' => $tableName,
                         'operation'  => $operation,
                         'name'       => $field->column_name,
                         'type'       => CRM_Core_BAO_CustomValueTable::fieldToSQLType( $field->data_type,
                                                                                        $field->text_length ),
                         'required'   => $field->is_required,
                         'searchable' => $field->is_searchable,
                         );
                
        if ( $operation == 'delete') {
            $fkName = "{$tableName}_{$field->column_name}";
            if ( strlen( $fkName ) >= 48) {
                $fkName = substr( $fkName, 0, 32 ) . "_" . substr( md5( $fkName ), 0, 16 );
            } 
            $params['fkName'] = $fkName;
        }
        if ( $field->data_type == 'Country' && $field->html_type == 'Select Country' ) {
            $params['fk_table_name'] = 'civicrm_country';
            $params['fk_field_name'] = 'id';
            $params['fk_attributes'] = 'ON DELETE SET NULL';
        } else if ( $field->data_type == 'Country' && $field->html_type == 'Multi-Select Country' ) {
            $params['type'] ='varchar(255)';
        } else if ( $field->data_type == 'StateProvince' && $field->html_type == 'Select State/Province' ) {
            $params['fk_table_name'] = 'civicrm_state_province';
            $params['fk_field_name'] = 'id';
            $params['fk_attributes'] = 'ON DELETE SET NULL';
        } else if ( $field->data_type == 'StateProvince' && $field->html_type == 'Multi-Select State/Province' ) {
            $params['type'] ='varchar(255)';
        } else if ( $field->data_type == 'File' ) {
            $params['fk_table_name'] = 'civicrm_file';
            $params['fk_field_name'] = 'id';
            $params['fk_attributes'] = 'ON DELETE SET NULL';
        } else if ( $field->data_type == 'ContactReference' ) {
            $params['fk_table_name'] = 'civicrm_contact';
            $params['fk_field_name'] = 'id';
            $params['fk_attributes'] = 'ON DELETE SET NULL';
        }
        if ( $field->default_value ) {
            $params['default'] = "'{$field->default_value}'";
        }
        
        require_once 'CRM/Core/BAO/SchemaHandler.php';
        CRM_Core_BAO_SchemaHandler::alterFieldSQL( $params, $indexExist );
    }

    static function getTableColumnGroup( $fieldID ) 
    {
        static $cache = array( );

        if ( ! array_key_exists( $fieldID, $cache ) ) {
            $query = "
SELECT cg.table_name, cf.column_name, cg.id
FROM   civicrm_custom_group cg,
       civicrm_custom_field cf
WHERE  cf.custom_group_id = cg.id
AND    cf.id = %1";
            $params = array( 1 => array( $fieldID, 'Integer' ) );
            $dao = CRM_Core_DAO::executeQuery( $query, $params );
            
            if ( ! $dao->fetch( ) ) {
                CRM_Core_Error::fatal( );
            }
            $dao->free( );
            $cache[$fieldID] = array( $dao->table_name, $dao->column_name, $dao->id );
        }
        return $cache[$fieldID];
    }

    /**
     * Function to get custom option groups
     * 
     * @params array $includeFieldIds ids of custom fields for which  
     * option groups must be included.
     * 
     * Currently this is required in the cases where option groups are to be included 
     * for inactive fields : CRM-5369
     *
     * @access public
     * @return $customOptionGroup 
     * @static
     */
    public static function &customOptionGroup( $includeFieldIds = null )
    {
        static $customOptionGroup = null;
        
        $cacheKey = (empty($includeFieldIds)) ? 'onlyActive':'force';
        if ( $cacheKey == 'force' ) $customOptionGroup[$cacheKey] = null; 
        
        if ( !CRM_Utils_Array::value( $cacheKey, $customOptionGroup ) ) {
            $whereClause = '( g.is_active = 1 AND f.is_active = 1 )';
            
            //support for single as well as array format.
            if ( !empty( $includeFieldIds )) {
                if ( is_array( $includeFieldIds )) { 
                    $includeFieldIds = implode( ',', $includeFieldIds );
                }
                $whereClause .= "OR f.id IN ( $includeFieldIds )";
            }
            
            $query = "
    SELECT  g.id, f.label
      FROM  civicrm_option_group g
INNER JOIN  civicrm_custom_field f ON ( g.id = f.option_group_id ) 
     WHERE  {$whereClause}";
            
            $dao = CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch( ) ) {
                $customOptionGroup[$cacheKey][$dao->id] = $dao->label;
            }
        }
        
        return $customOptionGroup[$cacheKey];
    }
    
    /**
     * Function to fix orphan groups
     * 
     * @params int $customFieldId custom field id
     * @params int $optionGroupId option group id
     *
     * @access public
     * @return void
     * @static
     */
    static function fixOptionGroups( $customFieldId, $optionGroupId )
    {
        // check if option group belongs to any custom Field else delete
        // get the current option group
        $currentOptionGroupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField',
                                                             $customFieldId,
                                                             'option_group_id' );
        // get the updated option group
        // if both are same return
        if ( $currentOptionGroupId == $optionGroupId ) {
            return;
        }

        // check if option group is related to any other field
        self::checkOptionGroup( $currentOptionGroupId );
    }

    /**
     * Function to check if option group is related to more than one
     * custom field
     *
     * @params int $optionGroupId option group id
     *
     * @return
     * @static
     */
    static function checkOptionGroup( $optionGroupId )
    {
        $query = "
SELECT count(*)
FROM   civicrm_custom_field
WHERE  option_group_id = {$optionGroupId}";
        
        $count = CRM_Core_DAO::singleValueQuery( $query );

        if ( $count < 2 ) {
            //delete the option group
            require_once "CRM/Core/BAO/OptionGroup.php";
            CRM_Core_BAO_OptionGroup::del( $optionGroupId );
        }
    }

    static function getOptionGroupDefault( $optionGroupId, $htmlType ) 
    {
        $query = "
SELECT   default_value, html_type
FROM     civicrm_custom_field
WHERE    option_group_id = {$optionGroupId}
AND      default_value IS NOT NULL
ORDER BY html_type";

        $dao = CRM_Core_DAO::executeQuery( $query );
        $defaultValue    = null;
        $defaultHTMLType = null;
        while ( $dao->fetch( ) ) {
            if ( $dao->html_type == $htmlType ) {
                return $dao->default_value;
            }
            if ( $defaultValue == null ) {
                $defaultValue    = $dao->default_value;
                $defaultHTMLType = $dao->html_type;
            }
        }

        // some conversions are needed if either the old or new has a html type which has potential
        // multiple default values.
        if ( ( $htmlType == 'CheckBox' || $htmlType == 'Multi-Select' ) &&
             ( $defaultHTMLType != 'CheckBox' && $defaultHTMLType != 'Multi-Select' ) ) {
            $defaultValue =
                CRM_Core_BAO_CustomOption::VALUE_SEPERATOR .
                $defaultValue .
                CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
        } else if ( ( $defaultHTMLType == 'CheckBox' || $defaultHTMLType == 'Multi-Select' ) &&
                    ( $htmlType != 'CheckBox' && $htmlType != 'Multi-Select' ) ) {
            $defaultValue = substr( $defaultValue, 1, -1 );
            $values = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR,
                               substr($defaultValue, 1, -1 ) );
            $defaultValue = $values[0];
        }

        return $defaultValue;
    }

    function postProcess( &$params,
                          &$customFields,
                          $entityID,
                          $customFieldExtends,
                          $inline = false ) 
    {
        $customData = array( );
              
        foreach ( $params as $key => $value ) {
            if ( $customFieldInfo = CRM_Core_BAO_CustomField::getKeyID( $key, true ) ) {
                
                // for autocomplete transfer hidden value instead of label
                if ( $params[$key] && isset ( $params[$key. '_id'] ) ) {
                    $value = $params[$key. '_id'];
                }
                
                // we need to append time with date 
                if ( $params[$key] && isset ( $params[$key. '_time'] ) ) {
                    $value .= ' ' . $params[$key. '_time'];
                }
                
                CRM_Core_BAO_CustomField::formatCustomField( $customFieldInfo[0],
                                                             $customData,
                                                             $value,
                                                             $customFieldExtends,
                                                             $customFieldInfo[1],
                                                             $entityID,
                                                             $inline );
            }
        }
        return $customData;
    }

    static function buildOption( $field, &$options ) 
    {
        $options['attributes'] = array( 'label'     => $field['label'],
                                        'data_type' => $field['data_type'],
                                        'html_type' => $field['html_type'] );

        $optionGroupID = null;
        if ( ( $field['html_type'] == 'CheckBox' ||
               $field['html_type'] == 'Radio'    ||
               $field['html_type'] == 'Select'   ||
               $field['html_type'] == 'AdvMulti-Select'   ||
               $field['html_type'] == 'Multi-Select' ||
               ( $field['html_type'] == 'Autocomplete-Select' && $field['data_type'] != 'ContactReference' ) ) ) {
            if ( $field['option_group_id'] ) {
                $optionGroupID = $field['option_group_id'];
            } else if ( $field['data_type'] != 'Boolean' ) {
                CRM_Core_Error::fatal( );
            }
        }
            
        // build the cache for custom values with options (label => value)
        if ( $optionGroupID != null ) {
            $query = "
SELECT label, value
  FROM civicrm_option_value
 WHERE option_group_id = $optionGroupID
";

            $dao =& CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch( ) ) {
                if ( $field['data_type'] == 'Int' || $field['data_type'] == 'Float' ) {
                    $num = round($dao->value, 2);
                    $options["$num"] = $dao->label;
                } else {
                    $options[$dao->value] = $dao->label;
                }
            }

            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::customFieldOptions( $field['id'], $options );
        }
    }

    static function getCustomFieldID( $fieldLabel, $groupTitle = null ) {
        $params = array( 1 => array( $fieldLabel, 'String' ) );
        if ( $groupTitle ) {
            $params[2] = array( $groupTitle, 'String' );
            $sql = "
SELECT     f.id
FROM       civicrm_custom_field f
INNER JOIN civicrm_custom_group g ON f.custom_group_id = g.id
WHERE      ( f.label = %1 OR f.name  = %1 )
AND        ( g.title = %2 OR g.title = %2 )
";
        } else {
            $sql = "
SELECT     f.id
FROM       civicrm_custom_field f
WHERE      ( f.label = %1 OR f.name = %1 )
";
        }

        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        if ( $dao->fetch( ) &&
             $dao->N == 1 ) {
            return $dao->id;
        } else {
            return null;
        }
    }

}
