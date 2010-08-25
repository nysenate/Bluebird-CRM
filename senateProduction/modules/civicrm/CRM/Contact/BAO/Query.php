<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Core/DAO/Address.php'; 
require_once 'CRM/Core/DAO/Phone.php'; 
require_once 'CRM/Core/DAO/Email.php';

/**
 * This class is a heart of search query building mechanism.
 */
class CRM_Contact_BAO_Query 
{
    /**
     * The various search modes
     *
     * @var int
     */
    const
        MODE_CONTACTS   =   1,
        MODE_CONTRIBUTE =   2,
        MODE_QUEST      =   4,
        MODE_MEMBER     =   8,
        MODE_EVENT      =  16,
        MODE_KABISSA    =  64,
        MODE_GRANT      = 128,
        MODE_PLEDGEBANK = 256,
        MODE_PLEDGE     = 512,
        MODE_CASE       = 2048,
        MODE_ALL        = 1023,
        MODE_ACTIVITY   = 4096;
    
    /**
     * the default set of return properties
     *
     * @var array
     * @static
     */
    static $_defaultReturnProperties = null;

    /**
     * the default set of hier return properties
     *
     * @var array
     * @static
     */
    static $_defaultHierReturnProperties;
    
    /** 
     * the set of input params
     * 
     * @var array 
     */ 
    public $_params;

    public $_cfIDs;

    public $_paramLookup;

    /** 
     * the set of output params
     * 
     * @var array 
     */ 
    public $_returnProperties;

    /** 
     * the select clause 
     * 
     * @var array 
     */
    public $_select;

    /** 
     * the name of the elements that are in the select clause 
     * used to extract the values 
     * 
     * @var array 
     */ 
    public $_element;
 
    /**  
     * the tables involved in the query 
     *  
     * @var array  
     */  
    public $_tables;

    /**
     * the table involved in the where clause
     *
     * @var array
     */
    public $_whereTables;

    /**  
     * the where clause  
     *  
     * @var array  
     */  
    public $_where;

    /**   
     * the where string
     *
     * @var string
     *
     */
    public $_whereClause;

    /**    
     * the from string 
     * 
     * @var string 
     * 
     */ 
    public $_fromClause;

    /**
     * the from clause for the simple select and alphabetical
     * select
     *
     * @var string
     */
    public $_simpleFromClause;

    /** 
     * The english language version of the query 
     *   
     * @var array   
     */  
    public $_qill;

    /**
     * All the fields that could potentially be involved in
     * this query
     *
     * @var array
     */
    public    $_fields;

    /** 
     * The cache to translate the option values into labels 
     *    
     * @var array    
     */  
    public    $_options;

    /**
     * are we in search mode
     *
     * @var boolean
     */
    public $_search = true;

    /**
     * should we skip permission checking
     *
     * @var boolean
     */
    public $_skipPermission = false;

    /**
     * are we in strict mode (use equality over LIKE)
     *
     * @var boolean
     */
    public $_strict = false;

    public $_mode = 1;

    /** 
     * Should we only search on primary location
     *    
     * @var boolean
     */  
    public $_primaryLocation = true;

    /**
     * are contact ids part of the query
     *
     * @var boolean
     */
    public $_includeContactIds = false;

    /**
     * Should we use the smart group cache
     *
     * @var boolean
     */
    public $_smartGroupCache = true;

    /**
     * reference to the query object for custom values
     *
     * @var Object
     */
    public $_customQuery;

    /**
     * should we enable the distinct clause, used if we are including
     * more than one group
     *
     * @var boolean
     */
    public $_useDistinct = false;

    /**
     * Should we just display one contact record
     */
    public $_useGroupBy  = false;

    /**
     * the relationship type direction
     *
     * @var array
     * @static
     */
    static $_relType;
    
    /**
     * the activity role
     *
     * @var array
     * @static
     */
    static $_activityRole;

    /**
     * use distinct component clause for component searches
     *
     * @var string
     */
    public $_distinctComponentClause;

  /**
     * use groupBy component clause for component searches
     *
     * @var string
     */
    public $_groupByComponentClause;

    /**
     * The tables which have a dependency on location and/or address
     *
     * @var array
     * @static
     */
    static $_dependencies = array( 'civicrm_state_province' => 1,
                                   'civicrm_country'        => 1,
                                   'civicrm_county'         => 1,
                                   'civicrm_address'        => 1,
                                   'civicrm_location_type'  => 1,
                                   );
    
    /**
     * List of location specific fields
     */
    static $_locationSpecificFields = array ( 'street_address',
                                              'supplemental_address_1',
                                              'supplemental_address_2',
                                              'city',
                                              'postal_code',
                                              'postal_code_suffix',
                                              'geo_code_1',
                                              'geo_code_2',
                                              'state_province',
                                              'country',
                                              'county',
                                              'phone',
                                              'email',
                                              'im',
                                              'address_name' );
    
    /**
     * class constructor which also does all the work
     *
     * @param array   $params
     * @param array   $returnProperties
     * @param array   $fields
     * @param boolean $includeContactIds
     * @param boolean $strict
     * @param boolean $mode - mode the search is operating on
     *
     * @return Object
     * @access public
     */
    function __construct( $params = null, $returnProperties = null, $fields = null,
                          $includeContactIds = false, $strict = false, $mode = 1,
                          $skipPermission = false, $searchDescendentGroups = true,
                          $smartGroupCache = true ) 
    {
        require_once 'CRM/Contact/BAO/Contact.php';

        // CRM_Core_Error::backtrace( );
        // CRM_Core_Error::debug_var( 'params', $params );
         
        // CRM_Core_Error::debug( 'post', $_POST );
        // CRM_Core_Error::debug( 'r', $returnProperties );
        $this->_params =& $params;
                    
        if ( empty( $returnProperties ) ) {
            $this->_returnProperties =& self::defaultReturnProperties( $mode );
        } else {
            $this->_returnProperties =& $returnProperties;
        }
        
        $this->_includeContactIds       = $includeContactIds;
        $this->_strict                  = $strict;
        $this->_mode                    = $mode;
        $this->_skipPermission          = $skipPermission;
        $this->_smartGroupCache         = $smartGroupCache;

        if ( $fields ) {
            $this->_fields =& $fields;
            $this->_search = false;
            $this->_skipPermission = true;
        } else {
            require_once 'CRM/Contact/BAO/Contact.php';
            $this->_fields = CRM_Contact_BAO_Contact::exportableFields( 'All', false, true );
         
            require_once 'CRM/Core/Component.php';
            $fields =& CRM_Core_Component::getQueryFields( );
            unset( $fields['note'] );
            $this->_fields = array_merge( $this->_fields, $fields );
            
            // add activity fields
            require_once 'CRM/Activity/BAO/Activity.php';
            $fields = CRM_Activity_BAO_Activity::exportableFields( );
            $this->_fields = array_merge( $this->_fields, $fields );
        }

        // basically do all the work once, and then reuse it
        $this->initialize( );

        // CRM_Core_Error::debug( $this );
    }

    /**
     * function which actually does all the work for the constructor
     *
     * @return void
     * @access private
     */
    function initialize( ) 
    {
        $this->_select      = array( ); 
        $this->_element     = array( ); 
        $this->_tables      = array( );
        $this->_whereTables = array( );
        $this->_where       = array( ); 
        $this->_qill        = array( ); 
        $this->_options     = array( );
        $this->_cfIDs       = array( );
        $this->_paramLookup = array( );

        $this->_customQuery = null; 
        
        //reset cache, CRM-5803
        self::$_activityRole = null;
        
        $this->_select['contact_id']      = 'contact_a.id as contact_id';
        $this->_element['contact_id']     = 1; 
        $this->_tables['civicrm_contact'] = 1;

        if ( ! empty( $this->_params ) ) {
            $this->buildParamsLookup( );
        }

        $this->_whereTables = $this->_tables;

        $this->selectClause( );
        $this->_whereClause      = $this->whereClause( );
       
        $this->_fromClause       = self::fromClause( $this->_tables     , null, null, $this->_primaryLocation, $this->_mode );
        $this->_simpleFromClause = self::fromClause( $this->_whereTables, null, null, $this->_primaryLocation, $this->_mode );

    }

    function buildParamsLookup( ) 
    {
        foreach ( $this->_params as $value ) {
            $cfID = CRM_Core_BAO_CustomField::getKeyID( $value[0] );
            if ( $cfID ) {
                if ( ! array_key_exists( $cfID, $this->_cfIDs ) ) {
                    $this->_cfIDs[$cfID] = array( );
                }
                $this->_cfIDs[$cfID][] = $value;
            }

            if ( ! array_key_exists( $value[0], $this->_paramLookup ) ) {
                $this->_paramLookup[$value[0]] = array( );
            }
            $this->_paramLookup[$value[0]][] = $value;
        }
    }

    /**
     * Some composite fields do not appear in the fields array
     * hack to make them part of the query
     *
     * @return void 
     * @access public 
     */
    function addSpecialFields( ) 
    {
        static $special = array( 'contact_type', 'contact_sub_type', 'sort_name', 'display_name' );
        foreach ( $special as $name ) {
            if ( CRM_Utils_Array::value( $name, $this->_returnProperties ) ) { 
                $this->_select[$name]  = "contact_a.{$name} as $name";
                $this->_element[$name] = 1;
            }
        }
    }

    /**
     * Given a list of conditions in params and a list of desired
     * return Properties generate the required select and from
     * clauses. Note that since the where clause introduces new
     * tables, the initial attempt also retrieves all variables used
     * in the params list
     *
     * @return void
     * @access public
     */
    function selectClause( ) 
    {
        $properties = array( );

        $this->addSpecialFields( );

        // CRM_Core_Error::debug( 'f', $this->_fields );
        // CRM_Core_Error::debug( 'p', $this->_params );
        // CRM_Core_Error::debug( 'p', $this->_paramLookup );

        foreach ($this->_fields as $name => $field) {

            //skip component fields
            if ( ( substr( $name, 0, 12 ) == 'participant_' ) || 
                 ( substr( $name, 0, 7  ) == 'pledge_' ) || 
                 ( substr( $name, 0, 5  ) == 'case_' ) ) {
                continue;
            }

            // redirect to activity select clause
            if ( substr( $name, 0, 9  ) == 'activity_' ) {
                require_once 'CRM/Activity/BAO/Query.php';
                CRM_Activity_BAO_Query::select( $this );
                continue;
            }

            // if this is a hierarchical name, we ignore it
            $names = explode( '-', $name );
            if ( count( $names > 1 ) && isset( $names[1] ) && is_numeric( $names[1] ) ) {
                continue;
            }

            $cfID = CRM_Core_BAO_CustomField::getKeyID( $name );

            if ( CRM_Utils_Array::value( $name, $this->_paramLookup ) ||
                 CRM_Utils_Array::value( $name, $this->_returnProperties ) ) {

                if ( $cfID ) {
                    // add to cfIDs array if not present
                    if ( ! array_key_exists( $cfID, $this->_cfIDs ) ) {
                        $this->_cfIDs[$cfID] = array( );
                    }
                } else if ( isset( $field['where'] ) ) {
                    list( $tableName, $fieldName ) = explode( '.', $field['where'], 2 );
                    if ( isset( $tableName ) ) { 
                     
                        if (substr( $tableName, 0, 6  ) == 'quest_' ) {
                            $this->_select['ethnicity_id_1']          = 'ethnicity_id_1';
                            $this->_select['gpa_weighted_calc']       = 'gpa_weighted_calc'; 
                            $this->_select['SAT_composite']           = 'SAT_composite';
                            $this->_select['household_income_total']  = 'household_income_total';
                        }

                        if ( CRM_Utils_Array::value( $tableName, self::$_dependencies ) ) {
                            $this->_tables['civicrm_address'] = 1;
                            $this->_select['address_id']      = 'civicrm_address.id as address_id';
                            $this->_element['address_id']     = 1;
                        }
                        
                        if ( $tableName == 'gender' || $tableName == 'individual_prefix' 
                             || $tableName == 'individual_suffix' || $tableName == 'im_provider' 
                             || $tableName == 'email_greeting' || $tableName == 'postal_greeting' 
                             || $tableName == 'addressee' ) {
                            require_once 'CRM/Core/OptionValue.php';
                            CRM_Core_OptionValue::select($this);
                            if ( in_array( $tableName, array( 'email_greeting', 'postal_greeting', 'addressee' ) ) ) {
                                //get display
                                $greetField = "{$name}_display";
                                $this->_select [ $greetField ] = "contact_a.{$greetField} as {$greetField}";
                                $this->_element[ $greetField ] = 1;
                                //get custom
                                $greetField = "{$name}_custom";
                                $this->_select [ $greetField ] = "contact_a.{$greetField} as {$greetField}";
                                $this->_element[ $greetField ] = 1;
                            }
                        } else {
                            $this->_tables[$tableName]         = 1;
                            
                            // also get the id of the tableName
                            $tName = substr($tableName, 8 );
                            
                            if ( $tName != 'contact' ) {
                                $this->_select["{$tName}_id"]  = "{$tableName}.id as {$tName}_id";
                                $this->_element["{$tName}_id"] = 1;
                            }
                            
                            //special case for phone
                            if ($name == 'phone') {
                                $this->_select ['phone_type_id'] = "civicrm_phone.phone_type_id as phone_type_id";
                                $this->_element['phone_type_id'] = 1;
                            }
                            
                            // if IM then select provider_id also 
                            // to get "IM Service Provider" in a file to be exported, CRM-3140
                            if ( $name == 'im' ) {
                              $this->_select ['provider_id'] = "civicrm_im.provider_id as provider_id";
                              $this->_element['provider_id'] = 1;
                            }
                           
                            if ( $name == 'state_province' ) {
                                $this->_select [$name]                 = "civicrm_state_province.abbreviation as `$name`, civicrm_state_province.name as state_province_name";
                                $this->_element['state_province_name'] = 1;
                            } else if ( $tName == 'contact' ) {
                                // special case, when current employer is set for Individual contact
                                if ( $fieldName == 'organization_name' ) {
                                    $this->_select[$name   ] = "IF ( contact_a.contact_type = 'Individual', NULL, contact_a.organization_name ) as organization_name";
                                } else if ( $fieldName != 'id' ) {
                                    $this->_select [$name]          = "contact_a.{$fieldName}  as `$name`";
                                } 
                            } else {
                                $this->_select [$name]              = "{$field['where']} as `$name`";
                            }
                            $this->_element[$name]       = 1;
                        }   
                    }
                } else if ($name === 'tags') {
                    $this->_useGroupBy  = true;
                    $this->_select[$name               ] = "GROUP_CONCAT(DISTINCT(civicrm_tag.name)) as tags";
                    $this->_element[$name              ] = 1;
                    $this->_tables['civicrm_tag'       ] = 1;
                    $this->_tables['civicrm_entity_tag'] = 1;
                } else if ($name === 'groups') {
                    $this->_useGroupBy  = true;
                    $this->_select[$name               ] = "GROUP_CONCAT(DISTINCT(civicrm_group.title)) as groups";
                    $this->_element[$name              ] = 1;
                    $this->_tables['civicrm_group'     ] = 1;
                } else if ($name === 'notes') {
                    $this->_useGroupBy  = true;
                    $this->_select[$name               ] = "GROUP_CONCAT(DISTINCT(civicrm_note.note)) as notes";
                    $this->_element[$name              ] = 1;
                    $this->_tables['civicrm_note'      ] = 1;
                } else if ($name === 'current_employer') {
                    $this->_select[$name   ] = "IF ( contact_a.contact_type = 'Individual', contact_a.organization_name, NULL ) as current_employer";
                    $this->_element[$name]   = 1;
                }
            } 
            
            if ( $cfID &&
                 CRM_Utils_Array::value( 'is_search_range', $field ) ) {
                // this is a custom field with range search enabled, so we better check for two/from values
                if ( CRM_Utils_Array::value( $name . '_from', $this->_paramLookup ) ) {
                    if ( ! array_key_exists( $cfID, $this->_cfIDs ) ) {
                        $this->_cfIDs[$cfID] = array( );
                    }
                    foreach ( $this->_paramLookup[$name . '_from'] as $pID => $p ) {
                        // search in the cdID array for the same grouping
                        $fnd = false;
                        foreach ( $this->_cfIDs[$cfID] as $cID => $c ) {
                            if ( $c[3] == $p[3] ) {
                                $this->_cfIDs[$cfID][$cID][2]['from'] = $p[2];
                                $fnd = true;
                            }
                        }
                        if ( ! $fnd ) {
                            $p[2] = array( 'from' => $p[2] );
                            $this->_cfIDs[$cfID][] = $p;
                        }
                    }
                }
                if ( CRM_Utils_Array::value( $name . '_to', $this->_paramLookup ) ) {
                    if ( ! array_key_exists( $cfID, $this->_cfIDs ) ) {
                        $this->_cfIDs[$cfID] = array( );
                    }
                    foreach ( $this->_paramLookup[$name . '_to'] as $pID => $p ) {
                        // search in the cdID array for the same grouping
                        $fnd = false;
                        foreach ( $this->_cfIDs[$cfID] as $cID => $c ) {
                            if ( $c[4] == $p[4] ) {
                                $this->_cfIDs[$cfID][$cID][2]['to'] = $p[2];
                                $fnd = true;
                            }
                        }
                        if ( ! $fnd ) {
                            $p[2] = array( 'to' => $p[2] );
                            $this->_cfIDs[$cfID][] = $p;
                        }
                    }
                }
            }
        }
        
        // add location as hierarchical elements
        $this->addHierarchicalElements( );

        // add multiple field like website
        $this->addMultipleElements( );
        
        //fix for CRM-951
        require_once 'CRM/Core/Component.php';
        CRM_Core_Component::alterQuery( $this, 'select' );

        if ( ! empty( $this->_cfIDs ) ) {
            require_once 'CRM/Core/BAO/CustomQuery.php';
            $this->_customQuery = new CRM_Core_BAO_CustomQuery( $this->_cfIDs );
            $this->_customQuery->query( );
            $this->_select       = array_merge( $this->_select , $this->_customQuery->_select );
            $this->_element      = array_merge( $this->_element, $this->_customQuery->_element);
            $this->_tables       = array_merge( $this->_tables , $this->_customQuery->_tables );
            $this->_whereTables  = array_merge( $this->_whereTables , $this->_customQuery->_whereTables );
            $this->_options      = $this->_customQuery->_options;
        }
    }

    /**
     * If the return Properties are set in a hierarchy, traverse the hierarchy to get
     * the return values
     *
     * @return void 
     * @access public 
     */
    function addHierarchicalElements( ) 
    {
        if ( ! CRM_Utils_Array::value( 'location', $this->_returnProperties ) ) {
            return;
        }
        if ( ! is_array( $this->_returnProperties['location'] ) ) {
            return;
        }

        $locationTypes = CRM_Core_PseudoConstant::locationType( );
        $processed     = array( );
        $index = 0;

        // CRM_Core_Error::debug( 'd', $this->_fields );
        // CRM_Core_Error::debug( 'r', $this->_returnProperties );
        $addressCustomFields = CRM_Core_BAO_CustomField::getFieldsForImport('Address');
        $addressCustomFieldIds = array( );

        foreach ( $this->_returnProperties['location'] as $name => $elements ) {
            $lCond = self::getPrimaryCondition( $name );

            if ( !$lCond ) {
                $locationTypeId = array_search( $name, $locationTypes );
                if ( $locationTypeId === false ) {
                    continue;
                }
                $lCond = "location_type_id = $locationTypeId";
                $this->_useDistinct = true;
                
                //commented for CRM-3256
                $this->_useGroupBy  = true;
            }
            
            $name = str_replace( ' ', '_', $name );

            $tName  = "$name-location_type";
            $ltName ="`$name-location_type`";
            $this->_select["{$tName}_id" ]  = "`$tName`.id as `{$tName}_id`"; 
            $this->_select["{$tName}"    ]  = "`$tName`.name as `{$tName}`"; 
            $this->_element["{$tName}_id"]  = 1;
            $this->_element["{$tName}"   ]  = 1;  
            
            $locationTypeName = $tName;
            $locationTypeJoin = array( );
            
            $addAddress = false;
            $addWhereCount = 0;
            foreach ( $elements as $elementFullName => $dontCare ) {
                $index++;
                $elementName = $elementCmpName = $elementFullName;
                
                if (substr($elementCmpName, 0, 5) == 'phone') {
                    $elementCmpName = 'phone';
                }
                
                //add address table only once
                if ( in_array( $elementCmpName, self::$_locationSpecificFields ) && ! $addAddress
                     && !in_array( $elementCmpName, array( 'email', 'phone', 'im', 'openid' ) )) {                         
                    $tName = "$name-address";
                    $aName = "`$name-address`";
                    $this->_select["{$tName}_id"]  = "`$tName`.id as `{$tName}_id`"; 
                    $this->_element["{$tName}_id"] = 1; 
                    $addressJoin = "\nLEFT JOIN civicrm_address $aName ON ($aName.contact_id = contact_a.id AND $aName.$lCond)";
                    $this->_tables[ $tName ] = $addressJoin;
                    $locationTypeJoin[$tName] = " ( $aName.location_type_id = $ltName.id ) ";
                    $processed[$aName] = 1;
                    $addAddress = true;
                }
                if ( in_array( $elementCmpName, array_keys( $addressCustomFields ) ) ) {
                    if ( $cfID = CRM_Core_BAO_CustomField::getKeyID( $elementCmpName ) ) {
                        $addressCustomFieldIds[$cfID][$name] = 1;
                    }
                }

                $cond = $elementType = '';
                if ( strpos( $elementName, '-' ) !== false ) {
                    // this is either phone, email or IM
                    list( $elementName, $elementType ) = explode( '-', $elementName );
                                        
                    
                    if( ( $elementName != 'phone' ) && ( $elementName != 'im' ) ) {
                        $cond = self::getPrimaryCondition( $elementType );
                    }
                    if ( ( ! $cond ) && ( $elementName == 'phone') ) {
                        $cond = "phone_type_id = '$elementType'";
                    } else if ( ( ! $cond ) && ( $elementName == 'im' ) ) {
                        // IM service provider id, CRM-3140
                        $cond = "provider_id = '$elementType'";
                    }
                    $elementType = '-' . $elementType;
                }

                $field = CRM_Utils_Array::value( $elementName, $this->_fields ); 

                // hack for profile, add location id
                if ( ! $field ) {
                    if ( $elementType &&
                         ! is_numeric($elementType) ) { //fix for CRM-882( to handle phone types )
                        if ( is_numeric( $name ) ) {
                            $field =& CRM_Utils_Array::value( $elementName . "-Primary$elementType", $this->_fields );
                        } else {
                            $field =& CRM_Utils_Array::value( $elementName . "-$locationTypeId$elementType", $this->_fields );
                        }
                    } else if ( is_numeric( $name ) ) {
                        //this for phone type to work
                        if ( $elementName == "phone" ) {
                            $field =& CRM_Utils_Array::value( $elementName . "-Primary" . $elementType, $this->_fields );
                        } else {
                            $field =& CRM_Utils_Array::value( $elementName . "-Primary", $this->_fields );
                        }
                    } else {
                        //this is for phone type to work for profile edit
                        if ( $elementName == "phone" ) {
                            $field =& CRM_Utils_Array::value( $elementName . "-$locationTypeId$elementType", $this->_fields );
                        } else {
                            $field =& CRM_Utils_Array::value( $elementName . "-$locationTypeId", $this->_fields );
                        }
                    }
                }

                // check if there is a value, if so also add to where Clause
                $addWhere = false;
                if ( $this->_params ) {
                    $nm = $elementName;
                    if ( isset( $locationTypeId ) ) {
                        $nm.= "-$locationTypeId";
                    }
                    if ( !is_numeric($elementType) ) {
                        $nm .= "$elementType";
                    }

                    foreach ( $this->_params as $id => $values ) {
                        if ( $values[0] == $nm ||
                             ( in_array( $elementName, array('phone', 'im') ) 
                               && ( strpos( $values[0], $nm ) !== false ) ) ) {
                            $addWhere = true;
                            $addWhereCount++;
                            break;
                        }
                    }
                }
                
                if ( $field && isset( $field['where'] ) ) {
                    list( $tableName, $fieldName ) = explode( '.', $field['where'], 2 );  
                    $tName = $name . '-' . substr( $tableName, 8 ) . $elementType;
                    $fieldName = $fieldName;
                    if ( isset( $tableName ) ) {
                        $this->_select["{$tName}_id"]                   = "`$tName`.id as `{$tName}_id`";
                        $this->_element["{$tName}_id"]                  = 1;
                        if ( substr( $tName, -15 ) == '-state_province' ) {
                            // FIXME: hack to fix CRM-1900
                            require_once 'CRM/Core/BAO/Preferences.php';
                            $a = CRM_Core_BAO_Preferences::value( 'address_format' );

                            if ( substr_count( $a, 'state_province_name' ) > 0 ) {
                                $this->_select["{$name}-{$elementFullName}"]  = "`$tName`.name as `{$name}-{$elementFullName}`";                            
                            } else {
                                $this->_select["{$name}-{$elementFullName}"]  = "`$tName`.abbreviation as `{$name}-{$elementFullName}`";                            
                            }
                            
                        } else {
                            if ( substr( $elementFullName,0,2) == 'im' ) {
                                $provider = "{$name}-{$elementFullName}-provider_id";
                                $this->_select[$provider]  = "`$tName`.provider_id as `{$name}-{$elementFullName}-provider_id`";
                                $this->_element[$provider] = 1;
                            }
                            
                            $this->_select["{$name}-{$elementFullName}"]  = "`$tName`.$fieldName as `{$name}-{$elementFullName}`";
                        }
                        
                        $this->_element["{$name}-{$elementFullName}"] = 1;
                        if ( ! CRM_Utils_Array::value( "`$tName`", $processed ) ) {
                            $processed["`$tName`"] = 1;
                            $newName = $tableName . '_' . $index;
                            switch ( $tableName ) {
                            case 'civicrm_phone':
                            case 'civicrm_email':
                            case 'civicrm_im':
                            case 'civicrm_openid':

                                $this->_tables[$tName] = "\nLEFT JOIN $tableName `$tName` ON contact_a.id = `$tName`.contact_id AND `$tName`.$lCond";
                                // this special case to add phone type
                                if ( $cond ) {
                                    $this->_tables[$tName] .= " AND `$tName`.$cond ";
                                }

                                //build locationType join
                                $locationTypeJoin[$tName] = " ( `$tName`.location_type_id = $ltName.id )";
                                
                                if ( $addWhere ) {
                                    $this->_whereTables[$tName] = $this->_tables[$tName];
                                }
                                break;

                            case 'civicrm_state_province':
                                $this->_tables[$tName] = "\nLEFT JOIN $tableName `$tName` ON `$tName`.id = $aName.state_province_id";
                                if ( $addWhere ) {
                                    $this->_whereTables[ "{$name}-address" ] = $addressJoin;
                                    $this->_whereTables[$tName] = $this->_tables[$tName];
                                }
                                break;

                            case 'civicrm_country':
                                $this->_tables[$newName] = "\nLEFT JOIN $tableName `$tName` ON `$tName`.id = $aName.country_id";
                                if ( $addWhere ) {
                                    $this->_whereTables[ "{$name}-address" ] = $addressJoin;
                                    $this->_whereTables[$newName] = $this->_tables[$newName];
                                }
                                break;

                            case 'civicrm_county':
                                $this->_tables[$newName] = "\nLEFT JOIN $tableName `$tName` ON `$tName`.id = $aName.county_id";
                                if ( $addWhere ) {
                                    $this->_whereTables[ "{$name}-address" ] = $addressJoin;
                                    $this->_whereTables[$newName] = $this->_tables[$newName];
                                }
                                break;
                                
                            default:
                                if ( $addWhere ) {
                                    $this->_whereTables[ "{$name}-address" ] = $addressJoin;
                                }
                                break;
                            }
                        }
                    }
                }
            }

            // add location type  join
            $ltypeJoin = "\nLEFT JOIN civicrm_location_type $ltName ON ( " . implode( 'OR', $locationTypeJoin ) . " )";
            $this->_tables[ $locationTypeName ] = $ltypeJoin;
            
            // table should be present in $this->_whereTables,
            // to add its condition in location type join, CRM-3939.
            if ( $addWhereCount ) {
                $locClause = array( );
                foreach ( $this->_whereTables as $tableName => $clause ) {
                    if ( CRM_Utils_Array::value( $tableName, $locationTypeJoin ) ) {
                        $locClause[] = $locationTypeJoin[$tableName];  
                    }
                }
                
                if ( !empty( $locClause ) ) {
                    $this->_whereTables[$locationTypeName] = 
                        "\nLEFT JOIN civicrm_location_type $ltName ON ( " . implode( 'OR', $locClause ) . " )";
                }
            }
        }

        if ( ! empty( $addressCustomFieldIds ) ) {
            require_once 'CRM/Core/BAO/CustomQuery.php';
            $cfIDs = $addressCustomFieldIds;
            $customQuery = new CRM_Core_BAO_CustomQuery( $cfIDs );
            foreach ( $addressCustomFieldIds as $cfID => $locTypeName ) {
                foreach ( $locTypeName as $name => $dnc ) {
                    $fieldName = "$name-custom_{$cfID}";
                    $tName     = "$name-address-custom";
                    $aName     = "`$name-address-custom`";
                    $this->_select["{$tName}_id"]  = "`$tName`.id as `{$tName}_id`"; 
                    $this->_element["{$tName}_id"] = 1; 
                    $this->_select[$fieldName]     = 
"`$tName`.{$customQuery->_fields[$cfID]['column_name']} as `{$fieldName}`";
                    $this->_element[$fieldName]    = 1;
                    $this->_tables[ $tName ]       = 
"\nLEFT JOIN {$customQuery->_fields[$cfID]['table_name']} $aName ON ($aName.entity_id = `$name-address`.id)";
                }
            }
        }

    }

    /**
     * If the return Properties are set in a hierarchy, traverse the hierarchy to get
     * the return values
     *
     * @return void 
     * @access public 
     */
    function addMultipleElements( ) {
        if ( ! CRM_Utils_Array::value( 'website', $this->_returnProperties ) ) {
            return;
        }
        if ( ! is_array( $this->_returnProperties['website'] ) ) {
            return;
        }

        foreach ( $this->_returnProperties['website'] as $key => $elements ) {
            foreach ( $elements as $elementFullName => $dontCare ) {
                $tName = "website-{$key}-{$elementFullName}";
                $this->_select["{$tName}_id" ]  = "`$tName`.id as `{$tName}_id`"; 
                $this->_select["{$tName}"    ]  = "`$tName`.url as `{$tName}`";
                $this->_element["{$tName}_id"]  = 1;
                $this->_element["{$tName}"   ]  = 1;
                
                $type = "website-{$key}-website_type_id";
                $this->_select[$type]    = "`$tName`.website_type_id as `{$type}`";
                $this->_element[$type]   = 1;
                $this->_tables[ $tName ] = "\nLEFT JOIN civicrm_website `$tName` ON (`$tName`.contact_id = contact_a.id )";
            }
        }
    }
    
    /** 
     * generate the query based on what type of query we need
     *
     * @param boolean $count
     * @param boolean $sortByChar
     * @param boolean $groupContacts
     * 
     * @return the sql string for that query (this will most likely
     * change soon)
     * @access public 
     */ 
    function query( $count = false, $sortByChar = false, $groupContacts = false ) 
    {
        if ( $count ) {
            if ( isset( $this->_distinctComponentClause ) ) {
                $select = "SELECT count( {$this->_distinctComponentClause} )";
            } else {
                $select = ( $this->_useDistinct ) ?	
                    'SELECT count(DISTINCT contact_a.id)' :
                    'SELECT count(*)';
            }
            $from = $this->_simpleFromClause;
        } else if ( $sortByChar ) {  
            $select = 'SELECT DISTINCT UPPER(LEFT(contact_a.sort_name, 1)) as sort_name';
            $from = $this->_simpleFromClause;
        } else if ( $groupContacts ) { 
//CRM-5954 - changing SELECT DISTINCT( contact_a.id ) -> SELECT ... GROUP BY contact_a.id
// but need to measure performance
            $select = ( $this->_useDistinct ) ?
                'SELECT DISTINCT(contact_a.id) as id' :
                'SELECT contact_a.id as id'; 
//            $select = 'SELECT contact_a.id as id';
//            if ( $this->_useDistinct ) {
//                $this->_useGroupBy = true;
//            }

            $from = $this->_simpleFromClause;
        } else {
            if ( CRM_Utils_Array::value( 'group', $this->_paramLookup ) ) {
                // make sure there is only one element
                // this is used when we are running under smog and need to know
                // how the contact was added (CRM-1203)
                if ( ( count( $this->_paramLookup['group'] ) == 1 ) &&
                     ( count( $this->_paramLookup['group'][0][2] ) == 1 ) ) {
                    $groups = array_keys($this->_paramLookup['group'][0][2]);
                    $groupId = $groups[0];

                    //check if group is saved search
                    $group = new CRM_Contact_BAO_Group(); 
                    $group->id = $groupId;
                    $group->find(true); 
                    
                    if (!isset($group->saved_search_id)) {
                        $tbName = "`civicrm_group_contact-{$groupId}`";
                        $this->_select['group_contact_id']      = "$tbName.id as group_contact_id";
                        $this->_element['group_contact_id']     = 1;
                        $this->_select['status']                = "$tbName.status as status";
                        $this->_element['status']               = 1;
                        $this->_tables[$tbName]                 = 1;
                    }
                }
            }
            if ( $this->_useDistinct && !isset( $this->_distinctComponentClause) ) {
                if ( !( $this->_mode & CRM_Contact_BAO_Query::MODE_ACTIVITY ) ) {
//CRM-5954
                    $this->_select['contact_id'] = 'DISTINCT(contact_a.id) as contact_id';
//                    $this->_useGroupBy = true;
//                    $this->_select['contact_id'] ='contact_a.id as contact_id';
                }
            } 

            $select = "SELECT ";
            if ( isset( $this->_distinctComponentClause)  ) {
                $select .= "{$this->_distinctComponentClause}, ";
            }
            
            $select .= implode( ', ', $this->_select );
            $from = $this->_fromClause;

        }
        
        $where = '';
        if ( ! empty( $this->_whereClause ) ) {
            $where = "WHERE {$this->_whereClause}";
        }

        return array( $select, $from, $where );
    }

    function &getWhereValues( $name, $grouping ) 
    {
        $result = null;
        foreach ( $this->_params as $id => $values ) {
            if ( $values[0] == $name && $values[3] == $grouping ) {
                return $values;
            }
        }
        
        return $result;
    }

    static function convertFormValues( &$formValues, $wildcard = 0, $useEquals = false ) 
    {
        $params = array( );

        if ( empty( $formValues ) ) {
            return $params;
        }

        foreach ( $formValues as $id => $values ) {
            if ( $id == 'privacy' ) {
                if ( is_array($formValues['privacy']) ) { 
                    $op = CRM_Utils_Array::value( 'do_not_toggle', $formValues['privacy'] ) ? '=' : '!=';
                    foreach ($formValues['privacy'] as $key => $value) { 
                        if ($value) {
                            $params[] = array( $key, $op, $value, 0, 0 );
                        }
                    } 
                }
            } else if( $id == 'email_on_hold' ){
                if ( $formValues['email_on_hold']['on_hold'] ){
                    $params[] = array( 'on_hold', '=', $formValues['email_on_hold']['on_hold'], 0, 0 );
                }
            } else {
                $values =& CRM_Contact_BAO_Query::fixWhereValues( $id, $values, $wildcard, $useEquals );
                
                if ( ! $values ) {
                    continue;
                }
                $params[] = $values;
            }
        }
        return $params;
    }

    static function &fixWhereValues( $id, &$values, $wildcard = 0, $useEquals = false ) 
    {
        // skip a few search variables
        static $skipWhere   = null;
        static $arrayValues = null;
        static $likeNames   = null;
        $result = null;

        if ( CRM_Utils_System::isNull( $values ) ) {
            return $result;
        }

        if  ( ! $skipWhere ) {
            $skipWhere   = array( 'task', 'radio_ts', 'uf_group_id' );
        }

        if ( in_array( $id, $skipWhere ) || substr( $id, 0, 4 ) == '_qf_' ) {
            return $result;
        }

        if ( ! $likeNames ) {
            $likeNames = array( 'sort_name', 'email', 'note', 'display_name' );
        }

        if ( ! $useEquals &&
             in_array( $id, $likeNames ) ) {
            $result = array( $id, 'LIKE', $values, 0, 1 );
        } else if ( is_string( $values ) && strpos( $values, '%' ) !== false ) {
            $result = array( $id, 'LIKE', $values, 0, 0 );
        } else if ( $id == 'group' ) {
            if ( is_array( $values ) ) {
                foreach ( $values as $groupIds => $val ) {
                    $matches = array( );
                    if ( preg_match( '/-(\d+)$/', $groupIds, $matches ) ) {
                        if ( strlen( $matches[1] ) > 0 ) {
                            $values[$matches[1]] = 1;
                            unset( $values[$groupIds] );
                        }
                    }
                }
            } else {
                $groupIds = explode( ',', $values );
                unset( $values );
                foreach( $groupIds as $groupId ) {
                    $values[$groupId] = 1;
                }
            }

            $result = array( $id, 'IN', $values, 0, 0 );
        } else if ( $id == 'contact_tags' ) {
            if (! is_array( $values ) ) {
                $tagIds = explode( ',', $values );
                unset( $values );
                foreach( $tagIds as $tagId ) {
                    $values[$tagId] = 1;
                }
            }
            $result = array( $id, 'IN', $values, 0, 0 );
        } else {
            $result = array( $id, '=', $values, 0, $wildcard );
        }

        return $result;
    }

    function whereClauseSingle( &$values ) 
    {
        // do not process custom fields or prefixed contact ids or component params
        if ( CRM_Core_BAO_CustomField::getKeyID( $values[0] ) ||
             ( substr( $values[0], 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) ||
             ( substr( $values[0], 0, 13 ) == 'contribution_' ) ||
             ( substr( $values[0], 0, 6  ) == 'event_' ) ||
             ( substr( $values[0], 0, 12 ) == 'participant_' ) ||
             ( substr( $values[0], 0, 6  ) == 'quest_' ) ||
             ( substr( $values[0], 0, 8  ) == 'kabissa_' ) ||
             ( substr( $values[0], 0, 4  ) == 'tmf_' ) ||
             ( substr( $values[0], 0, 6  ) == 'grant_' ) ||
             ( substr( $values[0], 0, 7  ) == 'pledge_' ) ||
             ( substr( $values[0], 0, 5  ) == 'case_' ) 
             ) {
            return;
            
        }

        switch ( $values[0] ) {
            
        case 'deleted_contacts':
            $this->deletedContacts($values);
            return;

        case 'contact_type':
            $this->contactType( $values );
            return;

        case 'contact_sub_type':
            $this->contactSubType( $values );
            return;

        case 'group':
            list( $name, $op, $value, $grouping, $wildcard ) = $values;
            $this->group( $values );
            return;

            // case tag comes from find contacts
        case 'tag':
        case 'contact_tags':
            $this->tag( $values );
            return;

        case 'note':
            $this->notes( $values );
            return;

        case 'uf_user':
            $this->ufUser( $values );
            return;

        case 'sort_name':
        case 'display_name':
            $this->sortName( $values );
            return;

        case 'email':
            $this->email( $values );
            return;

        case 'street_address':
            $this->street_address( $values );
            return;

        case 'sortByCharacter':
            $this->sortByCharacter( $values );
            return;

        case 'location_type':
            $this->locationType( $values ); 
            return;
            
        case 'state_province':
            $this->stateProvince( $values );
            return;

        case 'postal_code':
        case 'postal_code_low':
        case 'postal_code_high':
            $this->postalCode( $values );
            return;

        case 'activity_date':
        case 'activity_date_low':
        case 'activity_date_high':
        case 'activity_role':
        case 'activity_status':
        case 'activity_subject':
        case 'test_activities':
        case 'activity_type_id':
        case 'activity_tags': 
        case 'activity_test':   
            CRM_Activity_BAO_Query::whereClauseSingle( $values, $this );
            return;

        case 'activity_target_name':
            // since this case is handled with the above
            return;
        case 'birth_date_low':
        case 'birth_date_high': 
        case 'deceased_date_low':
        case 'deceased_date_high':   
            $this->demographics( $values );
            return;
        case 'modified_date_low':
        case 'modified_date_high':
            $this->modifiedDates( $values );
            return;
                        
        case 'changed_by':
            $this->changeLog( $values );
            return;

        case 'do_not_phone':
        case 'do_not_email':
        case 'do_not_mail':
        case 'do_not_sms':
        case 'do_not_trade':
        case 'is_opt_out':
            $this->privacy( $values );
            return;
            
        case 'preferred_communication_method':
            $this->preferredCommunication( $values );
            return;
            
        case 'relation_type_id':
            $this->relationship( $values );
            return;

        case 'relation_target_name':
            // since this case is handled with the above
            return;

        case 'relation_status':
            // since this case is handled with the above
            return;
            
        case 'task_status_id':
            $this->task( $values );
            return;

        case 'task_id':
            // since this case is handled with the above
            return;

        case 'prox_distance':
            require_once 'CRM/Contact/BAO/ProximityQuery.php';
            CRM_Contact_BAO_ProximityQuery::process( $this, $values );
            return;

        case 'prox_street_address':
        case 'prox_city':
        case 'prox_postal_code':
        case 'prox_state_province_id':
        case 'prox_country_id':
            // handled by the proximity_distance clause
            return;

        default:
            $this->restWhere( $values );
            return;
                
        }

    }

    /** 
     * Given a list of conditions in params generate the required
     * where clause
     * 
     * @return void 
     * @access public 
     */ 
    function whereClause( ) 
    {
        $this->_where[0] = array( );
        $this->_qill[0]  = array( );

        $config = CRM_Core_Config::singleton( );

        $this->includeContactIds( );       
        if ( ! empty( $this->_params ) ) {
            $activity = false;

            foreach ( array_keys( $this->_params ) as $id ) {
                // check for both id and contact_id
                if ( $this->_params[$id][0] == 'id' || $this->_params[$id][0] == 'contact_id' ) {
                    if ( $this->_params[$id][1] == 'IS NULL' ||
                         $this->_params[$id][1] == 'IS NOT NULL' ) {
                        $this->_where[0][] = "contact_a.id {$this->_params[$id][1]}";
                    } else {
                        $this->_where[0][] = "contact_a.id {$this->_params[$id][1]} {$this->_params[$id][2]}";
                    }
                } else {
                    $this->whereClauseSingle( $this->_params[$id] );
                }
            
                if ( substr ($this->_params[$id][0], 0 , 9) == 'activity_') {
                    $activity = true;
                }
            }

            require_once 'CRM/Core/Component.php';
            CRM_Core_Component::alterQuery( $this, 'where' );
        }
        
        if ( $this->_customQuery ) {
            // Added following if condition to avoid the wrong value diplay for 'myaccount' / any UF info.
            // Hope it wont affect the other part of civicrm.. if it does please remove it.
            if ( !empty($this->_customQuery->_where) ) {
                $this->_where = CRM_Utils_Array::crmArrayMerge( $this->_where, $this->_customQuery->_where );
            }
            
            $this->_qill  = CRM_Utils_Array::crmArrayMerge( $this->_qill , $this->_customQuery->_qill  );
        }

        $clauses    = array( );
        $andClauses = array( );

        $validClauses = 0;
        if ( ! empty( $this->_where ) ) {
            foreach ( $this->_where as $grouping => $values ) {
                if ( $grouping > 0 && ! empty( $values ) ) {
                    $clauses[$grouping] = ' ( ' . implode( ' AND ', $values ) . ' ) ';
                    $validClauses++;
                }
            }

            if ( ! empty( $this->_where[0] ) ) {
                $andClauses[] = ' ( ' . implode( ' AND ', $this->_where[0] ) . ' ) ';
            }
            if ( ! empty( $clauses ) ) {
                $andClauses[] = ' ( ' . implode( ' OR ', $clauses ) . ' ) ';
            }

            if ( $validClauses > 1 ) {
                $this->_useDistinct = true;
            }
        }
        
        return implode( ' AND ', $andClauses );
    }

    function restWhere( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        if ( ! CRM_Utils_Array::value( $grouping, $this->_where ) ) {
            $this->_where[$grouping] = array( );
        }

        $multipleFields = array( 'url' );
        
        //check if the location type exits for fields
        $lType = '';
        $locType = array( );
        $locType = explode('-', $name);
        
        if ( !in_array( $locType[0], $multipleFields ) ) {
            //add phone type if exists
            if ( isset( $locType[2] ) && $locType[2] ) {
                $locType[2] = CRM_Core_DAO::escapeString( $locType[2] );
            }
        }
        
        $field = CRM_Utils_Array::value( $name, $this->_fields );
        
        if ( ! $field ) {
            $field = CRM_Utils_Array::value( $locType[0], $this->_fields );
            
            if ( ! $field ) {
                return;
            }
        }

        $setTables = true;

        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';

        if ( substr($name,0,14) === 'state_province' ) {
            if ( isset( $locType[1] ) &&
                 is_numeric( $locType[1] ) ) {
                $setTables = false;
                    
                //get the location name 
                $locationType =& CRM_Core_PseudoConstant::locationType();
                list($tName, $fldName ) = self::getLocationTableName( $field['where'], $locType );
                $this->_whereTables[$tName] = $this->_tables[$tName];
                $where = "`$tName`.$fldName";
            } else {
                $where = $field['where'];
            }

            $wc = ( $op != 'LIKE' ) ? "LOWER($where)" : $where;

            if ( is_numeric( $value ) ) {
                $where = str_replace( '.name', '.id', $where );                     
                $this->_where[$grouping][] = self::buildClause( $where, $op, $value, 'Positive' );
                $states =& CRM_Core_PseudoConstant::stateProvince(); 
                $value  =  $states[(int ) $value]; 
            } else {
                $wc = ( $op != 'LIKE' ) ? "LOWER($where)" : $where;
                $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            }
            if (!$lType) {
                $this->_qill[$grouping][] = ts('State') . " $op '$value'";
            } else {
                $this->_qill[$grouping][] = ts('State') . " ($lType) $op '$value'";
            }
        } else if ( substr($name,0,7) === 'country' ) {
            if ( isset( $locType[1] ) &&
                 is_numeric( $locType[1] ) ) {
                $setTables = false;
                    
                //get the location name 
                $locationType =& CRM_Core_PseudoConstant::locationType();
                list($tName, $fldName ) = self::getLocationTableName( $field['where'], $locType );
                $this->_whereTables[$tName] = $this->_tables[$tName];
                $where = "`$tName`.$fldName";
            } else {
                $where = $field['where'];
            }

            if ( is_numeric( $value ) ) {
                $where = str_replace( '.name', '.id', $where );                     
                $this->_where[$grouping][] = self::buildClause( $where, $op, $value, 'Positive' );
                $countries =& CRM_Core_PseudoConstant::country( ); 
                $value     =  $countries[(int ) $value]; 
            } else {
                $wc = ( $op != 'LIKE' ) ? "LOWER($where)" : $where;
                $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            }
            if (!$lType) {
                $this->_qill[$grouping][] = ts('Country') . " $op '$value'";
            } else {
                $this->_qill[$grouping][] = ts('Country') . " ($lType) $op '$value'";
            }
        } else if ( substr($name,0,6) === 'county' ) {
            if ( isset( $locType[1] ) &&
                 is_numeric( $locType[1] ) ) {
                $setTables = false;
                    
                //get the location name 
                $locationType =& CRM_Core_PseudoConstant::locationType();
                list($tName, $fldName ) = self::getLocationTableName( $field['where'], $locType );
                $this->_whereTables[$tName] = $this->_tables[$tName];
                $where = "`$tName`.$fldName";
            } else {
                $where = $field['where'];
            }
            if ( is_numeric( $value ) ) {
                $where = str_replace( '.name', '.id', $where );                     
                $this->_where[$grouping][] = self::buildClause( $where, $op, $value, 'Positive' );
                $counties =& CRM_Core_PseudoConstant::county( ); 
                $value     =  $counties[(int ) $value]; 
            } else {
                $wc = ( $op != 'LIKE' ) ? "LOWER($where)" : $where;
                $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            }

            if (!$lType) {
                $this->_qill[$grouping][] = ts('County') . " $op '$value'";
            } else {
                $this->_qill[$grouping][] = ts('County') . " ($lType) $op '$value'";
            }
        } else if ( $name === 'world_region' ) {
            $worldRegions =& CRM_Core_PseudoConstant::worldRegion( ); 
            if ( is_numeric( $value ) ) { 
                $value     =  $worldRegions[(int ) $value]; 
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            $this->_qill[$grouping][] = ts('World Region') . " $op '$value'";
        } else if ( $name === 'individual_prefix' ) {
            $individualPrefixs =& CRM_Core_PseudoConstant::individualPrefix( ); 
            if ( is_numeric( $value ) ) { 
                $value     =  $individualPrefixs[(int ) $value];  
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            $this->_qill[$grouping][] = ts('Individual Prefix') . " $op '$value'";
        } else if ( $name === 'individual_suffix' ) {
            $individualSuffixs =& CRM_Core_PseudoConstant::individualsuffix( ); 
            if ( is_numeric( $value ) ) { 
                $value     =  $individualSuffixs[(int ) $value];  
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            $this->_qill[$grouping][] = ts('Individual Suffix') . " $op '$value'";
        } else if ( $name === 'gender' ) {
            $genders =& CRM_Core_PseudoConstant::gender( );  
            if ( is_numeric( $value ) ) {  
                $value     =  $genders[(int ) $value];  
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            $this->_qill[$grouping][] = ts('Gender') . " $op '$value'";
        } else if ( $name === 'birth_date' ) {
            $date = CRM_Utils_Date::processDate( $value );
            $this->_where[$grouping][] = self::buildClause( "contact_a.{$name}", $op, $date );
            
            if ( $date ) {
                $date = CRM_Utils_Date::customFormat( $date );
                $this->_qill[$grouping][]  = "$field[title] $op \"$date\"";
            } else {
                $this->_qill[$grouping][]  = "$field[title] $op";
            }

        } else if ( $name === 'deceased_date' ) {
            $date = CRM_Utils_Date::processDate( $value );
            $this->_where[$grouping][] = self::buildClause( "contact_a.{$name}", $op, $date );
            if ( $date ) {
                $date = CRM_Utils_Date::customFormat( $date );
                $this->_qill[$grouping][]  = "$field[title] $op \"$date\"";
            } else {
                $this->_qill[$grouping][]  = "$field[title] $op";
            }
        } else if ( $name === 'is_deceased' ) {
            $this->_where[$grouping][] = self::buildClause( "contact_a.{$name}", $op, $value );
            $this->_qill[$grouping][]  = "$field[title] $op \"$value\"";
        } else if ( $name === 'contact_id' ) {
            if ( is_int( $value ) ) {
                $this->_where[$grouping][] = self::buildClause( $field['where'], $op, $value );
                $this->_qill[$grouping][]  = "$field[title] $op $value";
            }
        } else if ( $name === 'name' ) {
            $value = $strtolower( CRM_Core_DAO::escapeString( $value ) );
            if ( $wildcard ) {
                $value = "%$value%"; 
                $op    = 'LIKE';
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, "'$value'" );
            $this->_qill[$grouping][]  = "$field[title] $op \"$value\"";
        } else if ( $name === 'current_employer' ) {
            $value = $strtolower( CRM_Core_DAO::escapeString( $value ) );
            if ( $wildcard ) {
                $value = "%$value%"; 
                $op    = 'LIKE';
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER(contact_a.organization_name)" : "contact_a.organization_name";
            $this->_where[$grouping][] = self::buildClause( $wc, $op,
                                                            "'$value' AND contact_a.contact_type ='Individual'" );
            $this->_qill[$grouping][]  = "$field[title] $op \"$value\"";
        } else if ( $name === 'email_greeting' ) {
            $filterCondition =  array( 'greeting_type' => 'email_greeting' );
            $emailGreetings =& CRM_Core_PseudoConstant::greeting( $filterCondition );
            if ( is_numeric( $value ) ) { 
                $value     =  $emailGreetings[(int ) $value];  
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            $this->_qill[$grouping][] = ts('Email Greeting') . " $op '$value'";
        } else if ( $name === 'postal_greeting' ) {
            $filterCondition =  array( 'greeting_type' => 'postal_greeting' );
            $postalGreetings =& CRM_Core_PseudoConstant::greeting( $filterCondition ); 
            if ( is_numeric( $value ) ) { 
                $value     =  $postalGreetings[(int ) $value];  
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            $this->_qill[$grouping][] = ts('Postal Greeting') . " $op '$value'";
        } else if ( $name === 'addressee' ) {
            $filterCondition =  array( 'greeting_type' => 'addressee' );
            $addressee =& CRM_Core_PseudoConstant::greeting( $filterCondition ); 
            if ( is_numeric( $value ) ) { 
                $value     =  $addressee[(int ) $value];  
            }
            $wc = ( $op != 'LIKE' ) ? "LOWER({$field['where']})" : "{$field['where']}";
            $this->_where[$grouping][] = self::buildClause( $wc, $op, $value, 'String' );
            $this->_qill[$grouping][] = ts('Addressee') . " $op '$value'";
        } else if ( substr( $name, 0, 4) === 'url-' ) {
            $tName = 'civicrm_website';
            $this->_whereTables[$tName] = $this->_tables[ $tName ] = "\nLEFT JOIN civicrm_website ON ( civicrm_website.contact_id = contact_a.id )";
            $value = $strtolower( CRM_Core_DAO::escapeString( $value ) );
            if ( $wildcard ) {
                $value = "%$value%"; 
                $op    = 'LIKE';
            }
            
            $wc = 'civicrm_website.url';
            $this->_where[$grouping][] = self::buildClause( $wc, $op, "'$value'" );
            $this->_qill[$grouping][]  = "$field[title] $op \"$value\"";
        } else {
            // sometime the value is an array, need to investigate and fix
            if ( is_array( $value ) ) {
                CRM_Core_Error::fatal( );
            }

            if ( ! empty( $field['where'] ) ) {
                if ( $op != 'IN' ) {
                    $value = $strtolower( CRM_Core_DAO::escapeString( $value ) );
                }
                if ( $wildcard ) {
                    $value = "%$value%"; 
                    $op    = 'LIKE';
                }

                if ( $op != 'IN' ) {
                    $value     = "'$value'";
                }

                if ( isset( $locType[1] ) &&
                     is_numeric( $locType[1] ) ) {
                    $setTables = false;
                    
                    //get the location name 
                    $locationType =& CRM_Core_PseudoConstant::locationType();
                    list($tName, $fldName ) = self::getLocationTableName( $field['where'], $locType );

                    $where = "`$tName`.$fldName"; 
                    
                    $this->_where[$grouping][] = self::buildClause( "LOWER($where)",
                                                                    $op,
                                                                    $value );
                    $this->_whereTables[$tName] = $this->_tables[$tName];
                    $this->_qill[$grouping][]  = "$field[title] $op '$value'";
                } else {
                    list( $tableName, $fieldName ) = explode( '.', $field['where'], 2 );  
                    if ( $tableName == 'civicrm_contact' ) {
                        $fieldName = "LOWER(contact_a.{$fieldName})";
                    } else {
                        if ( $op != 'IN' && !is_numeric( $value ) ) {
                            $fieldName = "LOWER({$field['where']})";                           
                        } else {
                            $fieldName = "{$field['where']}";
                        }
                    }
                    
                    $this->_where[$grouping][] = self::buildClause( $fieldName,
                                                                    $op,
                                                                    $value );
                    $this->_qill[$grouping][]  = "$field[title] $op $value";
                }
                
            }
        }

        if ( $setTables ) {
            list( $tableName, $fieldName ) = explode( '.', $field['where'], 2 );  
            if ( isset( $tableName ) ) {
                $this->_tables[$tableName] = 1;  
                $this->_whereTables[$tableName] = 1;  
            }
        }

    }


    static function getLocationTableName( &$where, &$locType ) 
    {
        if (isset( $locType[1] ) && is_numeric( $locType[1] ) ) {
            list($tbName, $fldName) = explode("." , $where);

            //get the location name 
            $locationType =& CRM_Core_PseudoConstant::locationType();
            if ( $locType[0] == 'email' ||
                 $locType[0] == 'im'    ||
                 $locType[0] == 'phone' ||
                 $locType[0] == 'openid' ) {
                if ($locType[2]) {
                    $tName = "{$locationType[$locType[1]]}-{$locType[0]}-{$locType[2]}";
                } else {
                    $tName = "{$locationType[$locType[1]]}-{$locType[0]}";
                }
            } else if ( in_array( $locType[0], 
                                  array( 'address_name', 'street_address', 'supplemental_address_1', 'supplemental_address_2',
                                         'city', 'postal_code', 'postal_code_suffix', 'geo_code_1', 'geo_code_2' ) ) ) {
                //fix for search by profile with address fields.
                $tName = "{$locationType[$locType[1]]}-address";
            } else if ( $locType[0] == 'on_hold' ) {
                $tName = "{$locationType[$locType[1]]}-email";
            } else {
                $tName = "{$locationType[$locType[1]]}-{$locType[0]}";
            }
            $tName = str_replace( ' ', '_', $tName );
            return array( $tName, $fldName );
        }
        CRM_Core_Error::fatal( );
    }

    /**
     * Given a result dao, extract the values and return that array
     *
     * @param Object $dao
     *
     * @return array values for this query
     */
    function store( $dao ) 
    {
        $value = array( );

        foreach ( $this->_element as $key => $dontCare ) {
            if ( isset( $dao->$key ) ) {
                if ( strpos( $key, '-' ) !== false ) {
                    $values = explode( '-', $key );
                    $lastElement = array_pop( $values );
                    $current =& $value;
                    $cnt   = count($values);
                    $count = 1;
                    foreach ( $values as $v ) {
                        if ( ! array_key_exists( $v, $current ) ) {
                            $current[$v] = array( );
                        }
                        //bad hack for im_provider
                        if ( $lastElement == 'provider_id') {
                            if ( $count < $cnt ) {
                                $current =& $current[$v];
                            } else {
                                $lastElement = "{$v}_{$lastElement}"; 
                            }
                        } else {
                            $current =& $current[$v];
                        }
                        $count++;
                    }

                    $current[$lastElement] = $dao->$key;
                } else {
                    $value[$key] = $dao->$key;
                }
            }
        }
        return $value;
    }

    /**
     * getter for tables array
     *
     * @return array
     * @access public
     */
    function tables( ) 
    {
        return $this->_tables;
    }

    function whereTables( ) 
    {
        return $this->_whereTables;
    }

    /**
     * generate the where clause (used in match contacts and permissions)
     *
     * @param array $params
     * @param array $fields
     * @param array $tables
     * @param boolean $strict
     * 
     * @return string
     * @access public
     * @static
     */
    static function getWhereClause( $params, $fields, &$tables, &$whereTables, $strict = false ) 
    {
        $query = new CRM_Contact_BAO_Query( $params, null, $fields,
                                             false, $strict );

        $tables      = array_merge( $query->tables( ), $tables );
        $whereTables = array_merge( $query->whereTables( ), $whereTables );

        return $query->_whereClause;
    }

    /**
     * create the from clause
     *
     * @param array $tables tables that need to be included in this from clause
     *                      if null, return mimimal from clause (i.e. civicrm_contact)
     * @param array $inner  tables that should be inner-joined
     * @param array $right  tables that should be right-joined
     *
     * @return string the from clause
     * @access public
     * @static
     */
    static function fromClause( &$tables , $inner = null, $right = null, $primaryLocation = true, $mode = 1 ) 
    {

        $from = ' FROM civicrm_contact contact_a';
        if ( empty( $tables ) ) {
            return $from;
        }

        if ( CRM_Utils_Array::value( 'civicrm_worldregion', $tables ) ) {
            $tables = array_merge( array( 'civicrm_country' => 1), $tables );
        }

        if ( ( CRM_Utils_Array::value( 'civicrm_state_province', $tables ) ||
               CRM_Utils_Array::value( 'civicrm_country'       , $tables ) ||
               CRM_Utils_Array::value( 'civicrm_county'       , $tables )) &&
             ! CRM_Utils_Array::value( 'civicrm_address'       , $tables ) ) {
            $tables = array_merge( array( 'civicrm_address'  => 1 ),
                                   $tables );
        }

        // add group_contact table if group table is present
        if ( CRM_Utils_Array::value( 'civicrm_group', $tables ) &&
            !CRM_Utils_Array::value('civicrm_group_contact', $tables)) {
            $tables['civicrm_group_contact'] = 1;
        }

        // add group_contact and group table is subscription history is present
        if ( CRM_Utils_Array::value( 'civicrm_subscription_history', $tables )
            && !CRM_Utils_Array::value('civicrm_group', $tables)) {
            $tables = array_merge( array( 'civicrm_group'         => 1,
                                          'civicrm_group_contact' => 1 ),
                                   $tables );
        }
       
        // to handle table dependencies of components
        require_once 'CRM/Core/Component.php';
        CRM_Core_Component::tableNames( $tables );
        
        //format the table list according to the weight
        require_once 'CRM/Core/TableHierarchy.php';
        $info =& CRM_Core_TableHierarchy::info( );

        foreach ($tables as $key => $value) {
            $k = 99;
            if ( strpos( $key, '-' ) !== false ) {
                $keyArray = explode('-', $key);
                $k = CRM_Utils_Array::value( 'civicrm_' . $keyArray[1], $info, 99 );
            } else if ( strpos( $key, '_' ) !== false ) {
                $keyArray = explode( '_', $key );
                if ( is_numeric( array_pop( $keyArray ) ) ) {
                    $k = CRM_Utils_Array::value( implode( '_', $keyArray ), $info, 99 );
                } else {
                    $k = CRM_Utils_Array::value($key, $info, 99 );
                }
            } else {
                $k = CRM_Utils_Array::value($key, $info, 99 );
            }
            $tempTable[$k . ".$key"] = $key;
        }
        ksort( $tempTable );
        $newTables = array ();
        foreach ($tempTable as $key) {
            $newTables[$key] = $tables[$key];
        }

        $tables = $newTables;

        foreach ( $tables as $name => $value ) {
            if ( ! $value ) {
                continue;
            }

            if (CRM_Utils_Array::value($name, $inner)) {
                $side = 'INNER';
            } elseif (CRM_Utils_Array::value($name, $right)) {
                $side = 'RIGHT';
            } else {
                $side = 'LEFT';
            }
            
            if ( $value != 1 ) {
                // if there is already a join statement in value, use value itself
                if ( strpos( $value, 'JOIN' ) ) { 
                    $from .= " $value ";
                } else {
                    $from .= " $side JOIN $name ON ( $value ) ";
                }
                continue;
            }
            switch ( $name ) {

            case 'civicrm_address':
                if ( $primaryLocation ) {
                    $from .= " $side JOIN civicrm_address ON ( contact_a.id = civicrm_address.contact_id AND civicrm_address.is_primary = 1 )";
                } else {
                    $from .= " $side JOIN civicrm_address ON ( contact_a.id = civicrm_address.contact_id ) ";
                }
                continue;

            case 'civicrm_phone':
                $from .= " $side JOIN civicrm_phone ON (contact_a.id = civicrm_phone.contact_id AND civicrm_phone.is_primary = 1) ";
                continue;

            case 'civicrm_email':
                $from .= " $side JOIN civicrm_email ON (contact_a.id = civicrm_email.contact_id AND civicrm_email.is_primary = 1) ";
                continue;

            case 'civicrm_im':
                $from .= " $side JOIN civicrm_im ON (contact_a.id = civicrm_im.contact_id AND civicrm_im.is_primary = 1) ";
                continue;

            case 'im_provider':
                $from .= " $side JOIN civicrm_option_group option_group_imProvider ON option_group_imProvider.name = 'instant_messenger_service'";
                $from .= " $side JOIN civicrm_im_provider im_provider ON (civicrm_im.provider_id = im_provider.id AND option_group_imProvider.id = im_provider.option_group_id)";
                continue;
                
            case 'civicrm_openid':
                $from .= " $side JOIN civicrm_openid ON ( civicrm_openid.contact_id = contact_a.id AND civicrm_openid.is_primary = 1 )";
                continue;
                
            case 'civicrm_state_province':
                $from .= " $side JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id ";
                continue;

            case 'civicrm_country':
                $from .= " $side JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id ";
                continue;

            case 'civicrm_worldregion':
                $from .= " $side JOIN civicrm_worldregion ON civicrm_country.region_id = civicrm_worldregion.id ";
                continue;

            case 'civicrm_county':
                $from .= " $side JOIN civicrm_county ON civicrm_address.county_id = civicrm_county.id ";
                continue;

            case 'civicrm_location_type':
                $from .= " $side JOIN civicrm_location_type ON civicrm_address.location_type_id = civicrm_location_type.id ";
                continue;

            case 'civicrm_group':
                $from .= " $side JOIN civicrm_group ON civicrm_group.id =  civicrm_group_contact.group_id ";
                continue;

            case 'civicrm_group_contact':
                $from .= " $side JOIN civicrm_group_contact ON contact_a.id = civicrm_group_contact.contact_id ";
                continue;

            case 'civicrm_activity':
            case 'civicrm_activity_tag':
            case 'activity_type':
            case 'activity_status':
                require_once 'CRM/Activity/BAO/Query.php';
                $from .= CRM_Activity_BAO_Query::from( $name, $mode, $side );
                continue; 

            case 'civicrm_entity_tag':
                $from .= " $side JOIN civicrm_entity_tag ON ( civicrm_entity_tag.entity_table = 'civicrm_contact' AND
                                                              civicrm_entity_tag.entity_id = contact_a.id ) ";
                continue;

            case 'civicrm_note':
                $from .= " $side JOIN civicrm_note ON ( civicrm_note.entity_table = 'civicrm_contact' AND
                                                        contact_a.id = civicrm_note.entity_id ) "; 
                continue; 
                
            case 'civicrm_subscription_history':
                $from .= " $side JOIN civicrm_subscription_history
                                   ON civicrm_group_contact.contact_id = civicrm_subscription_history.contact_id
                                  AND civicrm_group_contact.group_id   =  civicrm_subscription_history.group_id";
                continue;
                
            case 'individual_prefix':
                $from .= " $side JOIN civicrm_option_group option_group_prefix ON (option_group_prefix.name = 'individual_prefix')";
                $from .= " $side JOIN civicrm_option_value individual_prefix ON (contact_a.prefix_id = individual_prefix.value AND option_group_prefix.id = individual_prefix.option_group_id ) ";
                continue;
                
            case 'individual_suffix':
                $from .= " $side JOIN civicrm_option_group option_group_suffix ON (option_group_suffix.name = 'individual_suffix')";
                $from .= " $side JOIN civicrm_option_value individual_suffix ON (contact_a.suffix_id = individual_suffix.value AND option_group_suffix.id = individual_suffix.option_group_id ) ";
                continue;
                
            case 'gender':
                $from .= " $side JOIN civicrm_option_group option_group_gender ON (option_group_gender.name = 'gender')";
                $from .= " $side JOIN civicrm_option_value gender ON (contact_a.gender_id = gender.value AND option_group_gender.id = gender.option_group_id) ";
                continue;
                
            case 'civicrm_relationship':
                if ( self::$_relType == 'reciprocal' ) {
                    $from .= " $side JOIN civicrm_relationship ON (civicrm_relationship.contact_id_b = contact_a.id OR civicrm_relationship.contact_id_a = contact_a.id)";
                    $from .= " $side JOIN civicrm_contact contact_b ON (civicrm_relationship.contact_id_a = contact_b.id OR civicrm_relationship.contact_id_b = contact_b.id)";
                    
                } else if( self::$_relType == 'b') {
                    $from .= " $side JOIN civicrm_relationship ON (civicrm_relationship.contact_id_b = contact_a.id )";
                    $from .= " $side JOIN civicrm_contact contact_b ON (civicrm_relationship.contact_id_a = contact_b.id )";
                } else {
                    $from .= " $side JOIN civicrm_relationship ON (civicrm_relationship.contact_id_a = contact_a.id )";
                    $from .= " $side JOIN civicrm_contact contact_b ON (civicrm_relationship.contact_id_b = contact_b.id )";
                }
                continue;

            case 'civicrm_log':
                $from .= " $side JOIN civicrm_log ON (civicrm_log.entity_id = contact_a.id AND civicrm_log.entity_table = 'civicrm_contact')";
                $from .= " $side JOIN civicrm_contact contact_b ON (civicrm_log.modified_id = contact_b.id)";
                continue;
                
            case 'civicrm_tag':
                $from .= " $side  JOIN civicrm_tag ON civicrm_entity_tag.tag_id = civicrm_tag.id ";
                continue; 

            case 'civicrm_task_status':
                $from .= " $side JOIN civicrm_task_status ON ( civicrm_task_status.responsible_entity_table = 'civicrm_contact'
                                                          AND contact_a.id = civicrm_task_status.responsible_entity_id )";
                continue;

            case 'civicrm_grant':
                $from .= CRM_Grant_BAO_Query::from( $name, $mode, $side );
                continue;   
            
            //build fromClause for email greeting, postal greeting, addressee CRM-4575    
            case 'email_greeting':
                $from .= " $side JOIN civicrm_option_group option_group_email_greeting ON (option_group_email_greeting.name = 'email_greeting')";
                $from .= " $side JOIN civicrm_option_value email_greeting ON (contact_a.email_greeting_id = email_greeting.value AND option_group_email_greeting.id = email_greeting.option_group_id ) ";
                continue;  
                
            case 'postal_greeting':
                $from .= " $side JOIN civicrm_option_group option_group_postal_greeting ON (option_group_postal_greeting.name = 'postal_greeting')";
                $from .= " $side JOIN civicrm_option_value postal_greeting ON (contact_a.postal_greeting_id = postal_greeting.value AND option_group_postal_greeting.id = postal_greeting.option_group_id ) ";
                continue;  

            case 'addressee':
                $from .= " $side JOIN civicrm_option_group option_group_addressee ON (option_group_addressee.name = 'addressee')";
                $from .= " $side JOIN civicrm_option_value addressee ON (contact_a.addressee_id = addressee.value AND option_group_addressee.id = addressee.option_group_id ) ";
                continue;

            case 'civicrm_website':
                $from .= " $side JOIN civicrm_website ON contact_a.id = civicrm_website.contact_id ";
                continue;   

            default:
                $from .= CRM_Core_Component::from( $name, $mode, $side );
                continue;
            }
        }
        
        return $from;
    }

    /**
     * WHERE / QILL clause for deleted_contacts
     *
     * @return void
     */
    function deletedContacts($values)
    {
        list($_, $_, $value, $grouping, $_) = $values;
        if ($value) {
            // *prepend* to the relevant grouping as this is quite an important factor
            array_unshift($this->_qill[$grouping], ts('Search in Trash'));
        }
    }

    /**
     * where / qill clause for contact_type
     *
     * @return void
     * @access public
     */
    function contactType( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        $subTypes = array( );
        $clause = array( );
        if ( is_array( $value ) ) {
            foreach ( $value as $k => $v) { 
                if ($k) { //fix for CRM-771
                    list( $contactType, $subType ) = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                                              $k, 2 );
                    if ( ! empty( $subType ) ) {
                        $subTypes[$subType] = 1;
                    }
                    $clause[$contactType] = "'" . CRM_Utils_Type::escape( $contactType, 'String' ) . "'";
                }
            }
        } else {
            list( $contactType, $subType ) = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                                      $value, 2 );
            if ( ! empty( $subType ) ) {
                $subTypes[$subType] = 1;
            }
            $clause[$contactType] = "'" . CRM_Utils_Type::escape( $contactType, 'String' ) . "'";
        }
        
        if ( ! empty( $clause ) ) { //fix for CRM-771
            $this->_where[$grouping][] = 'contact_a.contact_type IN (' . implode( ',', $clause ) . ')';
            $this->_qill [$grouping][]  = ts('Contact Type') . ' - ' . implode( ' ' . ts('or') . ' ', $clause );
            
            if ( ! empty( $subTypes ) ) {
                $this->includeContactSubTypes( $subTypes, $grouping );
            }
        }
    }

    /**
     * where / qill clause for contact_sub_type
     *
     * @return void
     * @access public
     */
    function contactSubType( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        $this->includeContactSubTypes( $value, $grouping );
    }

    function includeContactSubTypes( $value, $grouping ) {
        if ( ! is_array( $value ) ) {
            $clause = "'" . CRM_Utils_Type::escape( $value, 'String' ) . "'";
        
            $this->_where[$grouping][] = "contact_a.contact_sub_type = $clause";
            $this->_qill [$grouping][]  = ts('Contact Subtype') . ' - ' . $clause;
        } else {
            $clause = array( );
            foreach ( $value as $k => $v) { 
                if ( ! empty( $k ) ) {
                    $clause[$k] = "'" . CRM_Utils_Type::escape( $k, 'String' ) . "'";
                }
            }
            
            if ( ! empty( $clause ) ) {
                $this->_where[$grouping][] = 'contact_a.contact_sub_type IN (' . implode( ',', $clause ) . ')';
                $this->_qill [$grouping][] = ts('Contact Subtype') . ' - ' . implode( ' ' . ts('or') . ' ', $clause );
            }
        }
    }

    /**
     * where / qill clause for groups
     *
     * @return void
     * @access public
     */
    function group( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        if ( count( $value ) > 1 ) {
            $this->_useDistinct = true;
        }

        $groupNames =& CRM_Core_PseudoConstant::group();
        $groupIds = implode( ',', array_keys($value, 1) );

        $names = array( );
        foreach ( $value as $id => $dontCare ) {
            if ( array_key_exists( $id, $groupNames ) && $dontCare ) {
                $names[] = $groupNames[$id];
            }
        }

        $statii    =  array(); 
        $in        =  false; 
        $gcsValues =& $this->getWhereValues( 'group_contact_status', $grouping );
        if ( $gcsValues &&
             is_array( $gcsValues[2] ) ) {
            foreach ( $gcsValues[2] as $k => $v ) {
                  if ( $v ) {
                    if ( $k == 'Added' ) {
                        $in = true;
                    }
                    $statii[] = "'" . CRM_Utils_Type::escape($k, 'String') . "'";
                }
            }
        } else {
            $statii[] = '"Added"'; 
            $in = true; 
        }

        $skipGroup = false;
        if ( count( $value )  == 1 &&
             count( $statii ) == 1 &&
             $statii[0] == '"Added"' ) {
            // check if smart group, if so we can get rid of that one additional
            // left join
            $groupIDs = array_keys( $value );
            if ( CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group',
                                              $groupIDs[0],
                                              'saved_search_id' ) ) {
                $skipGroup = true;
            }
            
        }

        if ( ! $skipGroup ) {
            $gcTable = "`civicrm_group_contact-{$groupIds}`";
            $this->_tables[$gcTable] = $this->_whereTables[$gcTable] =
                " LEFT JOIN civicrm_group_contact {$gcTable} ON contact_a.id = {$gcTable}.contact_id ";
        }
       
        $qill = ts( 'Contacts %1', array( 1 => $op ) );
        $qill .= ' ' . implode( ' ' . ts('or') . ' ', $names );
        $this->_qill[$grouping][] = $qill;
        
        $groupClause = null;

        if ( ! $skipGroup ) {
            $groupClause = "{$gcTable}.group_id $op ( $groupIds )";
            if ( ! empty( $statii ) ) {
                $groupClause .= " AND {$gcTable}.status IN (" . implode(', ', $statii) . ")";
                $this->_qill[$grouping][] = ts('Group Status') . ' - ' . implode( ' ' . ts('or') . ' ', $statii );
            }
        }

        if ( $in ) {
            $ssClause = $this->savedSearch( $values );
            if ( $ssClause ) {
                if ( $groupClause ) {
                    $groupClause = "( ( $groupClause ) OR ( $ssClause ) )";
                } else {
                    $groupClause = $ssClause;
                }
            }
        }
        
        $this->_where[$grouping][] = $groupClause;
    }
    
    /**
     * where / qill clause for smart groups
     *
     * @return void
     * @access public
     */
    function savedSearch( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        $config = CRM_Core_Config::singleton( );

        // find all the groups that are part of a saved search
        $groupIDs = implode( ',', array_keys( $value ) );
        $sql = "
SELECT id, cache_date, saved_search_id, children
FROM   civicrm_group
WHERE  id IN ( $groupIDs )
  AND  ( saved_search_id != 0
   OR    saved_search_id IS NOT NULL
   OR    children IS NOT NULL )
";
        $group = CRM_Core_DAO::executeQuery( $sql );
        $ssWhere = array(); 
        while ( $group->fetch( ) ) {
            $this->_useDistinct = true;
            
            if ( ! $this->_smartGroupCache || $group->cache_date == null ) {
                require_once 'CRM/Contact/BAO/GroupContactCache.php';
                CRM_Contact_BAO_GroupContactCache::load( $group );
            }
            
            $gcTable = "`civicrm_group_contact_cache_{$group->id}`";
            $this->_tables[$gcTable] = $this->_whereTables[$gcTable] =
                " LEFT JOIN civicrm_group_contact_cache {$gcTable} ON contact_a.id = {$gcTable}.contact_id ";
            $ssWhere[] = "{$gcTable}.group_id = {$group->id}";
        }
        
        if ( ! empty( $ssWhere ) ) {
            return implode(' OR ', $ssWhere);
        }
        return null;
    }

    /**
     * where / qill clause for cms users
     *
     * @return void
     * @access public
     */
    function ufUser( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        if ( $value == 1) {
            $this->_tables['civicrm_uf_match'] = $this->_whereTables['civicrm_uf_match'] =
                ' INNER JOIN civicrm_uf_match ON civicrm_uf_match.contact_id = contact_a.id ';
            
            $this->_qill[$grouping][]         = ts( 'CMS User' );
        } else if ( $value == 0 ) {
            $this->_tables['civicrm_uf_match'] = $this->_whereTables['civicrm_uf_match'] =
                ' LEFT JOIN civicrm_uf_match ON civicrm_uf_match.contact_id = contact_a.id ';
            
            $this->_where[$grouping][] = " civicrm_uf_match.contact_id IS NULL";
            $this->_qill[$grouping][]  = ts( 'Not a CMS User' );
        }
    }

    /**
     * where / qill clause for tag
     *
     * @return void
     * @access public
     */
    function tag( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        if ( count( $value ) > 1 ) {
            $this->_useDistinct = true;
        }

        $etTable = "`civicrm_entity_tag-" .implode( ',', array_keys($value) ) ."`";
        $this->_tables[$etTable] = $this->_whereTables[$etTable] =
            " LEFT JOIN civicrm_entity_tag {$etTable} ON ( {$etTable}.entity_id = contact_a.id  AND 
                        {$etTable}.entity_table = 'civicrm_contact' ) ";
       
        $names = array( );
        $tagNames =& CRM_Core_PseudoConstant::tag( );
        foreach ( $value as $id => $dontCare ) {
            $names[] = $tagNames[$id];
        }

        $this->_where[$grouping][] = "{$etTable}.tag_id $op (". implode( ',', array_keys( $value ) ) . ')';
        $this->_qill[$grouping][]  = ts('Tagged %1', array( 1 => $op ) ) . ' ' . implode( ' ' . ts('or') . ' ', $names ); 
    } 

    /**
     * where/qill clause for notes
     *
     * @return void
     * @access public
     */
    function notes( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
       
        $this->_useDistinct = true;

        $this->_tables['civicrm_note'] = $this->_whereTables['civicrm_note'] =
            " LEFT JOIN civicrm_note ON ( civicrm_note.entity_table = 'civicrm_contact' AND
                                          contact_a.id = civicrm_note.entity_id ) ";

        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $n = trim( $value );
        $value = $strtolower(CRM_Core_DAO::escapeString($n));
        if ( $wildcard || $op == 'LIKE' ) {
            if ( strpos( $value, '%' ) !== false ) {
                // only add wild card if not there
                $value = "'$value'";
            } else {
                $value = "'%$value%'";
            }
            $op    = 'LIKE';
        } else if ( $op == 'IS NULL' || $op == 'IS NOT NULL' ) {
            $value = null;
        } else {
            $value = "'$value'";
        }
        $sub = " ( civicrm_email.email $op $value )";
        $this->_where[$grouping][] = " ( civicrm_note.note $op $value ) ";
        $this->_qill[$grouping][]  = ts( 'Note' ) . " $op - '$n'";
    }

    /**
     * where / qill clause for sort_name
     *
     * @return void
     * @access public
     */
    function sortName( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        $newName = $name;
        $name    = trim( $value ); 
        
        if ( empty( $name ) ) {
            return;
        }

        $config = CRM_Core_Config::singleton( );

        $sub  = array( ); 

		//By default, $sub elements should be joined together with OR statements (don't change this variable).
        $subGlue = ' OR ';

        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        
        if ( substr( $name, 0 , 1 ) == '"' &&
             substr( $name, -1, 1 ) == '"' ) {
			//If name is encased in double quotes, the value should be taken to be the string in entirety and the 
            $value = substr( $name, 1, -1 );
            $value = $strtolower(CRM_Core_DAO::escapeString($value));
            $wc = ( $newName == 'sort_name') ? 'LOWER(contact_a.sort_name)' : 'LOWER(contact_a.display_name)';
            $sub[] = " ( $wc = '$value' ) ";
            if ( $config->includeEmailInName ) {
                $sub[] = " ( civicrm_email.email = '$value' ) ";
            }
        } else if ( strpos( $name, ',' ) !== false ) {
            // if we have a comma in the string, search for the entire string 
            $value = $strtolower(CRM_Core_DAO::escapeString($name));
            if ( $wildcard ) {
                if ( $config->includeWildCardInName ) {
                    $value = "'%$value%'";
                } else {
                    $value = "'$value%'";
                }
                $op    = 'LIKE';
            } else {
                $value = "'$value'";
            }
            if( $newName == 'sort_name') {
                $wc = ( $op != 'LIKE' ) ? "LOWER(contact_a.sort_name)" : "contact_a.sort_name";
            } else {
                $wc = ( $op != 'LIKE' ) ? "LOWER(contact_a.display_name)" : "contact_a.display_name";
            }
            $sub[] = " ( $wc $op $value )";
            if ( $config->includeNickNameInName ) {
                $wc    = ( $op != 'LIKE' ) ? "LOWER(contact_a.nick_name)" : "contact_a.nick_name";
                $sub[] = " ( $wc $op $value )";
            }
            if ( $config->includeEmailInName ) {
                $sub[] = " ( civicrm_email.email $op $value ) ";
            }
        } else {
            //Else, the string should be treated as a series of keywords to be matched with match ANY/ match ALL depending on Civi config settings (see CiviAdmin)
            
            // The Civi configuration setting can be overridden if the string *starts* with the case insenstive strings 'AND:' or 'OR:'  
            // TO THINK ABOUT: what happens when someone searches for the following "AND: 'a string in quotes'"? - probably nothing - it would make the AND OR variable reduntant because there is only one search string?

        	// Check to see if the $subGlue is overridden in the search text
        	if(strtolower(substr( $name,  0,  4 ))=='and:'){
        		$name = substr( $name,  4 );
        		$subGlue = ' AND ';
        	}
        	if(strtolower(substr( $name,  0,  3 ))=='or:'){
        		$name = substr( $name,  3 );
        		$subGlue = ' OR ';
        	}
        	
            $firstChar = substr( $name,  0,  1 );
            $lastChar  = substr( $name, -1, 1 );
            $quotes    = array( "'", '"' );
            if ( in_array( $firstChar, $quotes ) &&
                 in_array( $lastChar , $quotes ) ) {
                $name   = substr( $name,  1 );
                $name   = substr( $name, 0, -1 );
                $pieces = array( $name );
            } else {
                $pieces =  explode( ' ', $name );
            }
            foreach ( $pieces as $piece ) { 
                $value = $strtolower( CRM_Core_DAO::escapeString( trim( $piece ) ) );
                if ( strlen( $value ) ) {
             		// Added If as a sanitization - without it, when you do an OR search, any string with
             		// double spaces (i.e. "  ") or that has a space after the keyword (e.g. "OR: ") will
             		// return all contacts because it will include a condition similar to "OR contact
             		// name LIKE '%'".  It might be better to replace this with array_filter. 
 	            	$fieldsub = array();
                    if ( $wildcard ) {
                        if ( $config->includeWildCardInName ) {
                            $value = "'%$value%'";
                        } else {
                            $value = "'$value%'";
                        }
                        $op    = 'LIKE';
                    } else {
                        $value = "'$value'";
                    }
                    if( $newName == 'sort_name') {
                        $wc = ( $op != 'LIKE' ) ? "LOWER(contact_a.sort_name)" : "contact_a.sort_name";
                    } else {
                        $wc = ( $op != 'LIKE' ) ? "LOWER(contact_a.display_name)" : "contact_a.display_name";
                    }
                    $fieldsub[] = " ( $wc $op $value )";
                    if ( $config->includeNickNameInName ) {
                        $wc    = ( $op != 'LIKE' ) ? "LOWER(contact_a.nick_name)" : "contact_a.nick_name";
                        $fieldsub[] = " ( $wc $op $value )";
                    }
                    if ( $config->includeEmailInName ) {
                        $fieldsub[] = " ( civicrm_email.email $op $value ) ";
                    }
                    $sub[] = ' ( ' . implode( ' OR ', $fieldsub ) . ' ) ';
                    // I seperated the glueing in two.  The first stage should always be OR because we are searching for matches in *ANY* of these fields
                }
            }
        } 

        $sub = ' ( ' . implode( $subGlue, $sub ) . ' ) '; 

        $this->_where[$grouping][] = $sub;
        if ( $config->includeEmailInName ) {
            $this->_tables['civicrm_email'] = $this->_whereTables['civicrm_email'] = 1; 
            $this->_qill[$grouping][]  = ts( 'Name or Email ' ) . "$op - '$name'";
        } else {
            $this->_qill[$grouping][]  = ts( 'Name like' ) . " - '$name'";
        }
    }

    /**
     * where / qill clause for email
     *
     * @return void
     * @access public
     */
    function email( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        $n = trim( $value ); 

        $config = CRM_Core_Config::singleton( );

        if ( substr( $n, 0 , 1 ) == '"' &&
             substr( $n, -1, 1 ) == '"' ) {
            $n     = substr( $n, 1, -1 );
            $value = strtolower(CRM_Core_DAO::escapeString($n));
            $value = "'$value'";
            $op    = '=';
        } else {
            $value = strtolower(CRM_Core_DAO::escapeString($n));
            if ( $wildcard ) {
                if ( strpos( $value, '%' ) !== false ) {
                    $value = "'$value'";
                    // only add wild card if not there
                } else {
                    $value = "'$value%'";
                }
                $op    = 'LIKE';
            } else {
                $value = "'$value'";
            }
        }

        $this->_tables['civicrm_email'] = $this->_whereTables['civicrm_email'] = 1; 
        $this->_where[$grouping][] = " ( civicrm_email.email $op $value )";
        $this->_qill[$grouping][]  = ts( 'Email' ) . " $op '$n'";
    }

    /**
     * where / qill clause for street_address
     *
     * @return void
     * @access public
     */
    function street_address( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        $op = 'LIKE';
        
        $n = trim( $value ); 

        $value = strtolower(CRM_Core_DAO::escapeString($n));
        if ( strpos( $value, '%' ) !== false ) {
            $value = "'$value'";
            // only add wild card if not there
        } else {
            $value = "'$value%'";
        }

        $this->_tables['civicrm_address'] = $this->_whereTables['civicrm_address'] = 1; 
        $this->_where[$grouping][] = " ( LOWER(civicrm_address.street_address) LIKE $value )";
        $this->_qill[$grouping][]  = ts( 'Street' ) . " ILIKE '$n'";
    }

    /**
     * where / qill clause for sorting by character
     *
     * @return void
     * @access public
     */
    function sortByCharacter( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        $name = trim( $value );
        $cond = " contact_a.sort_name LIKE '" . strtolower(CRM_Core_DAO::escapeString($name)) . "%'"; 
        $this->_where[$grouping][] = $cond;
        $this->_qill[$grouping][]  = ts( 'Showing only Contacts starting with: \'%1\'', array( 1 => $name ) );
    }

    /**
     * where / qill clause for including contact ids
     *
     * @return void
     * @access public
     */
    function includeContactIDs( ) 
    {
        if ( ! $this->_includeContactIds || empty( $this->_params ) ) {
            return;
        }

        $contactIds = array( ); 
        foreach ( $this->_params as $id => $values ) { 
            if ( substr( $values[0], 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) { 
                $contactIds[] = substr( $values[0], CRM_Core_Form::CB_PREFIX_LEN ); 
            } 
        } 
        if ( ! empty( $contactIds ) ) { 
            $this->_where[0][] = " ( contact_a.id IN (" . implode( ',', $contactIds ) . " ) ) "; 
        }
    }

    /**
     * where / qill clause for postal code
     *
     * @return void
     * @access public
     */
    function postalCode( &$values ) 
    {
        // skip if the fields dont have anything to do with postal_code
        if ( ! CRM_Utils_Array::value( 'postal_code', $this->_fields ) ) {
            return;
        }

        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        // Handle numeric postal code range searches properly by casting the column as numeric
        if ( is_numeric( $value ) ) {
            $field = 'ROUND(civicrm_address.postal_code)';
            $val   = CRM_Utils_Type::escape( $value, 'Integer' );
        } else {
            $field = 'civicrm_address.postal_code';
            $val   = CRM_Utils_Type::escape( $value, 'String' );
        }

        $this->_tables['civicrm_address' ] = $this->_whereTables['civicrm_address' ] = 1;

        if ( $name == 'postal_code' ) {
            $this->_where[$grouping][] = "{$field} {$op} '$val'"; 
            $this->_qill[$grouping][] = ts('Postal code') . " - '$value'";
        } else if ( $name =='postal_code_low') { 
            $this->_where[$grouping][] = " ( $field >= '$val' ) ";
            $this->_qill[$grouping][] = ts('Postal code greater than or equal to \'%1\'', array( 1 => $value ) );
        } else if ( $name == 'postal_code_high' ) {
            $this->_where[$grouping][] = " ( $field <= '$val' ) ";
            $this->_qill[$grouping][] = ts('Postal code less than or equal to \'%1\'', array( 1 => $value ) );
        }
    }

    /**
     * where / qill clause for location type
     *
     * @return void
     * @access public
     */
    function locationType( &$values, $status = null ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        if (is_array($value)) {
            $this->_where[$grouping][] = 'civicrm_address.location_type_id IN (' .
                implode( ',', array_keys( $value ) ) .
                ')';
            $this->_tables['civicrm_address'] = 1;
            $this->_whereTables['civicrm_address'] = 1;
            
            $locationType =& CRM_Core_PseudoConstant::locationType();
            $names = array( );
            foreach ( array_keys( $value ) as $id ) {
                $names[] = $locationType[$id];
            }
            
            $this->_primaryLocation = false;
            
            if (!$status) {
                $this->_qill[$grouping][] = ts('Location Type') . ' - ' . implode( ' ' . ts('or') . ' ', $names );
            } else {
                return implode( ' ' . ts('or') . ' ', $names );
            }
        }
    }
    
    /**
     * where / qill clause for state/province
     *
     * @return void
     * @access public
     */
    function stateProvince( &$values, $status = null )
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        if (is_array($value)) {
            $this->_where[$grouping][] = 'civicrm_state_province.id IN (' . 
                implode( ',', $value ) .
                ')';
            $this->_tables['civicrm_state_province'] = 1;
            $this->_whereTables['civicrm_state_province'] = 1;
            
            $stateProvince =& CRM_Core_PseudoConstant::stateProvince();
            $names = array( );
            foreach ( $value as $id ) {
                $names[] = $stateProvince[$id];
            }
            
            if (!$status) {
                $this->_qill[$grouping][] = ts('State/Province') . ' - ' . implode( ' ' . ts('or') . ' ', $names );
            } else {
                return implode( ' ' . ts('or') . ' ', $names );
            }
        } else {
            return $this->restWhere( $values );
        }
    }

     /**
     * where / qill clause for change log
     *
     * @return void
     * @access public
     */
    function changeLog ( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        $targetName = $this->getWhereValues( 'changed_by', $grouping );
        if ( ! $targetName ) {
            return;
        }

        $name = trim( $targetName[2] );
        $name = strtolower( CRM_Core_DAO::escapeString( $name ) );
        $name = $targetName[4] ? "%$name%" : $name;
        $this->_where[$grouping][] = "contact_b.sort_name LIKE '%$name%'";
        $this->_tables['civicrm_log'] = $this->_whereTables['civicrm_log'] = 1; 
        $this->_qill[$grouping][] = ts('Changed by') . ": $name";
    }

    function modifiedDates( $values )
    {
        $this->_useDistinct = true;
        $this->dateQueryBuilder( $values,
                                 'civicrm_log', 'modified_date', 'modified_date', 'Modified Date' );
    }

    
    function demographics( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
       
        if ( ($name == 'birth_date_low') ||($name == 'birth_date_high') ) {
          
            $this->dateQueryBuilder( $values,
                                     'contact_a', 'birth_date', 'birth_date', ts('Birth Date'), false );
    
        } else if( ($name == 'deceased_date_low') ||($name == 'deceased_date_high') ) {
          
            $this->dateQueryBuilder( $values,
                                     'contact_a', 'deceased_date', 'deceased_date', ts('Deceased Date'), false );
        }
       
    }

    function privacy( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        //fixed for profile search listing CRM-4633
        if ( strpbrk( $value, "[" ) ) {
            $value = "'{$value}'";
            $op    = "!{$op}";
            $this->_where[$grouping][] = "contact_a.{$name} $op $value";
        } else {
            $this->_where[$grouping][] = "contact_a.{$name} $op $value";
        }
        $field = CRM_Utils_Array::value( $name, $this->_fields );
        $title = $field ? $field['title'] : $name;
        $this->_qill[$grouping][]  = "$title $op $value";
    }

    function preferredCommunication( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
        
        $pref  = array( );
        if ( !is_array($value) ) {
            $v = array( );
            
            if ( strpos( $value, CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) !== false ) {
                $v = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $value );
            } else{
                $v = explode( ",", $value );
            }
            
            foreach ( $v as $item ) {
                if ( $item ) {
                    $pref[] = $item;
                }
            }
        } else {
            foreach ( $value as $key => $checked ) {
                if ( $checked ) {
                    $pref[] = $key;
                }
            }
        }

        $commPref = array( );
        $commPref = CRM_Core_PseudoConstant::pcm();

        $sqlValue = array( ) ;
        $sql = "contact_a.preferred_communication_method";
        foreach ( $pref as $val ) { 
            $sqlValue[] = "( $sql like '%" . CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . $val . CRM_Core_BAO_CustomOption::VALUE_SEPERATOR . "%' ) ";
            $showValue[] =  $commPref[$val];
        }
        $this->_where[$grouping][] = "( ". implode( ' OR ', $sqlValue ). " )"; 
        $this->_qill[$grouping][]  = ts('Preferred Communication Method') . " $op " . implode(' ' . ts('or') . ' ', $showValue);
    }

    /**
     * where / qill clause for task / task status
     *
     * @return void
     * @access public
     */
    function task( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        $targetName = $this->getWhereValues( 'task_id', $grouping );
        if ( ! $targetName ) {
            return;
        }

        $taskID   = CRM_Utils_Type::escape( $targetName[2], 'Integer' );
        $clause   = "civicrm_task_status.task_id = $taskID ";

        $statusID = null;
        if ( $value ) {
            $statusID = CRM_Utils_Type::escape( $value, 'Integer' );
            $clause  .= " AND civicrm_task_status.status_id = $statusID";
        }

        $this->_where[$grouping][] = "civicrm_task_status.task_id = $taskID AND civicrm_task_status.status_id = $statusID";
        $this->_tables['civicrm_task_status'] = $this->_whereTables['civicrm_task_status'] = 1;

        $taskSelect =  CRM_Core_PseudoConstant::tasks( );
        $this->_qill[$grouping][] = ts('Task') . ": $taskSelect[$taskID]";
        if ( $statusID ) {
            require_once 'CRM/Core/OptionGroup.php';
            $statusSelect = CRM_Core_OptionGroup::values( 'task_status' );
            $this->_qill[$grouping][] = ts('Task Status') . ": $statusSelect[$statusID]";
        }
    }

    /**
     * where / qill clause for relationship
     *
     * @return void
     * @access public
     */
    function relationship( &$values ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;
             
        // also get values array for relation_target_name
        // for relatinship search we always do wildcard
        $targetName = $this->getWhereValues( 'relation_target_name', $grouping );
        $relStatus  = $this->getWhereValues( 'relation_status', $grouping );
        
        $nameClause = null;
        if ( $targetName ) {
            $name = trim( $targetName[2] );
            if ( substr( $name, 0 , 1 ) == '"' &&
                 substr( $name, -1, 1 ) == '"' ) {
                $name = substr( $name, 1, -1 );
                $name = strtolower( CRM_Core_DAO::escapeString( $name ) );
                $nameClause = "= '$name'";
            } else {
                $name = strtolower( CRM_Core_DAO::escapeString( $name ) );
                $nameClause = "LIKE '%{$name}%'";
            }
        }

        $rel = explode( '_' , $value );

        self::$_relType = $rel[1];
        if ( $nameClause ) { 
            require_once 'CRM/Contact/BAO/RelationshipType.php';

            $params = array( 'id' => $rel[0] );
            $rTypeValues = array( );

            require_once "CRM/Contact/BAO/RelationshipType.php";
            $rType =& CRM_Contact_BAO_RelationshipType::retrieve( $params, $rTypeValues );
            if ( ! $rType ) {
                return;
            }
            // for relatinship search we always do wildcard
           if ( $rTypeValues['name_a_b'] == $rTypeValues['name_b_a'] ) {
               self::$_relType = 'reciprocal';
           }
           $this->_where[$grouping][] = "( contact_b.sort_name $nameClause AND contact_b.id != contact_a.id )";
        }

        require_once 'CRM/Contact/BAO/Relationship.php';
        $relTypeInd =  CRM_Contact_BAO_Relationship::getContactRelationshipType(null,'null',null,'Individual');
        $relTypeOrg =  CRM_Contact_BAO_Relationship::getContactRelationshipType(null,'null',null,'Organization');
        $relTypeHou =  CRM_Contact_BAO_Relationship::getContactRelationshipType(null,'null',null,'Household');
        $allRelationshipType =array();
        $allRelationshipType = array_merge(  $relTypeInd , $relTypeOrg);
        $allRelationshipType = array_merge( $allRelationshipType, $relTypeHou);
        $this->_qill[$grouping][]  = "$allRelationshipType[$value]  $name";
        
        //check for active, inactive and all relation status
        $today = date( 'Ymd' );
        if ( $relStatus[2] == 0 ) {
            $this->_where[$grouping][] = "civicrm_relationship.is_active = 1 AND ( civicrm_relationship.end_date is NULL OR civicrm_relationship.end_date >= {$today} )";
            $this->_qill[$grouping][]  = ts( 'Relationship - Active');
            
        } else if ( $relStatus[2] == 1 ) {
            $this->_where[$grouping][] = "(civicrm_relationship.is_active = 0 OR civicrm_relationship.end_date < {$today})";
            $this->_qill[$grouping][]  = ts( 'Relationship - Inactive');
        }
        $this->_where[$grouping][] = 'civicrm_relationship.relationship_type_id = '.$rel[0];
        $this->_tables['civicrm_relationship'] = $this->_whereTables['civicrm_relationship'] = 1; 
        $this->_useDistinct = true;
    }

    /**
     * default set of return properties
     *
     * @return void
     * @access public
     */
    static function &defaultReturnProperties( $mode = 1 ) 
    {
        if ( ! isset( self::$_defaultReturnProperties ) ) {
            self::$_defaultReturnProperties = array( );
        }

        if ( ! isset( self::$_defaultReturnProperties[$mode] ) ) {
        	// add activity return properties
        	if ( $mode & CRM_Contact_BAO_Query::MODE_ACTIVITY ) {
        		require_once 'CRM/Activity/BAO/Query.php';
        		self::$_defaultReturnProperties[$mode] = CRM_Activity_BAO_Query::defaultReturnProperties( $mode );
        	} else {
            	require_once 'CRM/Core/Component.php';
            	self::$_defaultReturnProperties[$mode] = CRM_Core_Component::defaultReturnProperties( $mode );
            }

            if ( empty( self::$_defaultReturnProperties[$mode] ) ) {
                self::$_defaultReturnProperties[$mode] = array( 
                                                               'home_URL'               => 1, 
                                                               'image_URL'              => 1, 
                                                               'legal_identifier'       => 1, 
                                                               'external_identifier'    => 1,
                                                               'contact_type'           => 1,
                                                               'contact_sub_type'       => 1,
                                                               'sort_name'              => 1,
                                                               'display_name'           => 1,
                                                               'preferred_mail_format'  => 1,
                                                               'nick_name'              => 1, 
                                                               'first_name'             => 1, 
                                                               'middle_name'            => 1, 
                                                               'last_name'              => 1, 
                                                               'individual_prefix'      => 1, 
                                                               'individual_suffix'      => 1,
                                                               'birth_date'             => 1,
                                                               'gender'                 => 1,
                                                               'street_address'         => 1, 
                                                               'supplemental_address_1' => 1, 
                                                               'supplemental_address_2' => 1, 
                                                               'city'                   => 1, 
                                                               'postal_code'            => 1, 
                                                               'postal_code_suffix'     => 1, 
                                                               'state_province'         => 1, 
                                                               'country'                => 1,
                                                               'world_region'           => 1,
                                                               'geo_code_1'             => 1,
                                                               'geo_code_2'             => 1,
                                                               'email'                  => 1, 
                                                               'on_hold'                => 1, 
                                                               'phone'                  => 1, 
                                                               'im'                     => 1, 
                                                               'household_name'         => 1,
                                                               'organization_name'      => 1,
                                                               'deceased_date'          => 1,
                                                               'is_deceased'            => 1,
                                                               'job_title'              => 1,
                                                               'legal_name'             => 1,
                                                               'sic_code'               => 1,
                                                               'current_employer'       => 1,
                                                               // FIXME: should we use defaultHierReturnProperties() for the below?
                                                               'do_not_email'           => 1,
                                                               'do_not_mail'            => 1,
                                                               'do_not_sms'             => 1,
                                                               'do_not_phone'           => 1,
                                                               'do_not_trade'           => 1,
                                                               'is_opt_out'             => 1,
                                                               ); 
            }
        }
        return self::$_defaultReturnProperties[$mode];
    }

    /**
     * get primary condition for a sql clause
     *
     * @param int $value
     *
     * @return void
     * @access public
     */
    static function getPrimaryCondition( $value ) 
    {
        if ( is_numeric( $value ) ) {
            $value = (int ) $value;
            return ( $value == 1 ) ?'is_primary = 1' : 'is_primary = 0';
        }
        return null;
    }

    /**
     * wrapper for a simple search query
     *
     * @param array $params
     * @param array $returnProperties
     * @param bolean $count
     *
     * @return void 
     * @access public 
     */
    static function getQuery( $params = null, $returnProperties = null, $count = false ) 
    {
        $query = new CRM_Contact_BAO_Query( $params, $returnProperties );
        list( $select, $from, $where ) = $query->query( );
        
        return "$select $from $where";
    }

    /**
     * wrapper for a api search query
     *
     * @param array  $params
     * @param array  $returnProperties
     * @param string $sort
     * @param int    $offset
     * @param int    $row_count
     *
     * @return void 
     * @access public 
     */
    static function apiQuery( $params = null,
                              $returnProperties = null,
                              $fields = null,
                              $sort = null,
                              $offset = 0,
                              $row_count = 25,
                              $smartGroupCache = true )
    {
        $query = new CRM_Contact_BAO_Query( $params, $returnProperties,
                                             null, true, false, 1,
                                             false, true, $smartGroupCache );
 
        list( $select, $from, $where ) = $query->query( );
        $options = $query->_options;
        $sql = "$select $from $where";
        if ( ! empty( $sort ) ) {
            $sql .= " ORDER BY $sort ";
        }
     
        // add group by
        if ( $query->_useGroupBy ) {
            $sql .= ' GROUP BY contact_a.id';
        }

        if ( $row_count > 0 && $offset >= 0 ) {
            $sql .= " LIMIT $offset, $row_count ";
        }
        
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        
        $values = array( );
        while ( $dao->fetch( ) ) {
            $values[$dao->contact_id] = $query->store( $dao );
        }
        $dao->free( );
        return array($values, $options);
    }

    /**
     * create and query the db for an contact search
     *
     * @param int      $offset   the offset for the query
     * @param int      $rowCount the number of rows to return
     * @param string   $sort     the order by string
     * @param boolean  $count    is this a count only query ?
     * @param boolean  $includeContactIds should we include contact ids?
     * @param boolean  $sortByChar if true returns the distinct array of first characters for search results
     * @param boolean  $groupContacts if true, use a single mysql group_concat statement to get the contact ids
     * @param boolean  $returnQuery   should we return the query as a string
     * @param string   $additionalWhereClause if the caller wants to further restrict the search (used for components)
     *
     * @return CRM_Contact_DAO_Contact 
     * @access public
     */
    function searchQuery( $offset = 0, $rowCount = 0, $sort = null, 
                          $count = false, $includeContactIds = false,
                          $sortByChar = false, $groupContacts = false,
                          $returnQuery = false,
                          $additionalWhereClause = null, $sortOrder = null ) 
    {
        require_once 'CRM/Core/Permission.php';

        if ( $includeContactIds ) {
            $this->_includeContactIds = true;
            $this->_whereClause       = $this->whereClause( );
        }

        // hack for now, add permission only if we are in search
        // FIXME: we should actually filter out deleted contacts (unless requested to do the opposite)
        $permission = ' ( 1 ) ';
        $onlyDeleted = in_array(array('deleted_contacts', '=', '1', '0', '0'), $this->_params);

        // if we’re explicitely looking for a certain contact’s contribs, events, etc.
        // and that contact happens to be deleted, set $onlyDeleted to true
        foreach ($this->_params as $values) {
            list($name, $op, $value, $_, $_) = $values;
            if ($name == 'contact_id' and $op == '=') {
                if (CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $value, 'is_deleted')) {
                    $onlyDeleted = true;
                }
                break;
            }
        }

        if ( ! $this->_skipPermission ) {
            require_once 'CRM/ACL/API.php';
            $permission = CRM_ACL_API::whereClause( CRM_Core_Permission::VIEW, $this->_tables, $this->_whereTables, null, $onlyDeleted );
            // CRM_Core_Error::debug( 'p', $permission );
            // CRM_Core_Error::debug( 't', $this->_tables );
            // CRM_Core_Error::debug( 'w', $this->_whereTables );

            // regenerate fromClause since permission might have added tables
            if ( $permission ) {
                //fix for row count in qill (in contribute/membership find)
                if (! $count ) {
                    $this->_useDistinct = true;
                }
                $this->_fromClause       = self::fromClause( $this->_tables     , null, null, $this->_primaryLocation, $this->_mode ); 
                $this->_simpleFromClause = self::fromClause( $this->_whereTables, null, null, $this->_primaryLocation, $this->_mode );
            }
        }
        
        list( $select, $from, $where ) = $this->query( $count, $sortByChar, $groupContacts );
        
        if ( empty( $where ) ) {
            $where = "WHERE $permission";
        } else {
            $where = "$where AND $permission";
        }

        if ( $additionalWhereClause ) {
            $where = $where . ' AND ' . $additionalWhereClause;
        }
        
        $order = $orderBy = $limit = '';

        if ( ! $count ) {
            $config = CRM_Core_Config::singleton( );
            if ( $config->includeOrderByClause ) {
                if ($sort) {
                    if ( is_string( $sort ) ) {
                        $orderBy = $sort;
                    } else {
                        $orderBy = trim( $sort->orderBy() );
                    }
                    if ( ! empty( $orderBy ) ) {
                        // this is special case while searching for
                        // changelog CRM-1718
                        if ( preg_match ( '/sort_name/i', $orderBy) ) {
                            $orderBy = str_replace( 'sort_name', 'contact_a.sort_name', $orderBy );
                        } 

                        $order = " ORDER BY $orderBy";
                        
                        if ( $sortOrder ) {
                            $order .= " $sortOrder";
                        }
                    }
                } else if ($sortByChar) { 
                    $orderBy = " ORDER BY LEFT(contact_a.sort_name, 1) asc";
                } else {
                    $orderBy = " ORDER BY contact_a.sort_name asc";
                }
            }

            if ( $rowCount > 0 && $offset >= 0 ) {
                $limit = " LIMIT $offset, $rowCount ";
                
                // ok here is a first hack at an optimization, lets get all the contact ids
                // that are restricted and we'll then do the final clause with it
                // CRM-5954
                $limitSelect = ( $this->_useDistinct ) ?
                    'SELECT DISTINCT(contact_a.id) as id' :
                    'SELECT contact_a.id as id';

                $doOpt = true;
                // hack for order clause
                if ( $orderBy ) {
                    list( $field, $dir ) = explode( ' ', $orderBy );
                    if ( $field ) {
                        switch ( $field ) {
                        case 'sort_name':
                            break;

                        case 'city':
                        case 'postal_code':
                            $this->_whereTables["civicrm_address"] = 1;
                            $limitSelect .= ", civicrm_address.{$field} as {$field}";
                            break;

                        case 'country':
                        case 'state_province':
                            $this->_whereTables["civicrm_{$field}"] = 1;
                            $limitSelect .= ", civicrm_{$field}.name as {$field}";
                            break;

                        case 'email':
                            $this->_whereTables["civicrm_email"] = 1;
                            $limitSelect .= ", civicrm_email.email as email";
                            break;

                        default:
                            $doOpt = false;
                        }
                    }
                }

                if ( $doOpt ) {
                    $this->_simpleFromClause = self::fromClause( $this->_whereTables, null, null,
                                                                 $this->_primaryLocation, $this->_mode );

                    $limitQuery = "$limitSelect {$this->_simpleFromClause} $where $order $limit";
                    $limitDAO   = CRM_Core_DAO::executeQuery( $limitQuery );
                    $limitIDs   = array( );
                    while ( $limitDAO->fetch( ) ) {
                        $limitIDs[] = $limitDAO->id;
                    }
                    if ( empty( $limitIDs ) ) {
                        $limitClause = ' AND ( 0 ) ';
                    } else {
                        $limitClause = 
                            ' AND contact_a.id IN ( ' .
                            implode( ',', $limitIDs ) .
                            ' ) ';
                    }
                    $where .= $limitClause;
                    // reset limit clause since we already restrict what records we want
                    $limit  = null;
                }
            }
        }

        // building the query string
        $groupBy = null;
        if ( ! $count ) {
			if ( isset( $this->_groupByComponentClause ) ) {
				$groupBy = $this->_groupByComponentClause;
			} else if ( $this->_useGroupBy ) {
				$groupBy = ' GROUP BY contact_a.id';
			}
		}	
        if ( $this->_mode & CRM_Contact_BAO_Query::MODE_ACTIVITY && ( ! $count ) ) {
            $groupBy = 'GROUP BY civicrm_activity.id ';
        }
        $query = "$select $from $where $groupBy $order $limit";
        // CRM_Core_Error::debug('query', $query);

        if ( $returnQuery ) {
            return $query;
        }
        
        if ( $count ) {
            return CRM_Core_DAO::singleValueQuery( $query, CRM_Core_DAO::$_nullArray );
        }

        $dao =& CRM_Core_DAO::executeQuery( $query );
        if ( $groupContacts ) {
            $ids = array( );
            while ( $dao->fetch( ) ) {
                $ids[] = $dao->id;
            }
            return implode( ',', $ids );
        }

        return $dao;
    }

    function setSkipPermission( $val ) 
    {
        $this->_skipPermission = $val;
    }

    function &summaryContribution( )
    {
        list( $select, $from, $where ) = $this->query( true );

        // hack $select
        $select = "
SELECT COUNT( civicrm_contribution.total_amount ) as total_count,
       SUM(   civicrm_contribution.total_amount ) as total_amount,
       AVG(   civicrm_contribution.total_amount ) as total_avg,
       civicrm_contribution.currency              as currency";

        // make sure contribution is completed - CRM-4989
        $additionalWhere = "civicrm_contribution.contribution_status_id = 1";

        if ( ! empty( $where ) ) {
            $newWhere = "$where AND $additionalWhere";
        } else {
            $newWhere = " AND $additionalWhere";
        }

        $summary = array( );
        $summary['total'] = array( );
        $summary['total']['count'] = $summary['total']['amount'] = $summary['total']['avg'] = "n/a";

        $query  = "$select $from $newWhere GROUP BY currency";
        $params = array( );

        $dao =& CRM_Core_DAO::executeQuery( $query, $params );

        require_once 'CRM/Utils/Money.php';
        $summary['total']['count'] = 0;
        $summary['total']['amount'] = $summary['total']['avg'] = array( );
        while ( $dao->fetch( ) ) {
            $summary['total']['count']    += $dao->total_count;
            $summary['total']['amount'][]  = CRM_Utils_Money::format( $dao->total_amount, $dao->currency );
            $summary['total']['avg'][]     = CRM_Utils_Money::format( $dao->total_avg   , $dao->currency );
        }
        if ( ! empty( $summary['total']['amount'] ) ) {
            $summary['total']['amount'] = implode( ',&nbsp;', $summary['total']['amount'] );
            $summary['total']['avg']    = implode( ',&nbsp;', $summary['total']['avg']    );
        } else {
            $summary['total']['amount'] = $summary['total']['avg'] = 0;
        }
        
        // hack $select
        $select = "
SELECT COUNT( civicrm_contribution.total_amount ) as cancel_count,
       SUM(   civicrm_contribution.total_amount ) as cancel_amount,
       AVG(   civicrm_contribution.total_amount ) as cancel_avg,
       civicrm_contribution.currency              as currency";

        $additionalWhere = "civicrm_contribution.cancel_date IS NOT NULL";
        if ( ! empty( $where ) ) {
            $newWhere = "$where AND $additionalWhere";
        } else {
            $newWhere = " AND $additionalWhere";
        }

        $query = "$select $from $newWhere GROUP BY currency";
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );

        if ($dao->N <= 1 ) {
            if ( $dao->fetch( ) ) {
                $summary['cancel']['count']  = $dao->cancel_count;
                $summary['cancel']['amount'] = $dao->cancel_amount;
                $summary['cancel']['avg']    = $dao->cancel_avg;
            }
        } else {
            require_once 'CRM/Utils/Money.php';
            $summary['cancel']['count']  = 0;
            $summary['cancel']['amount'] = $summary['cancel']['avg'] = array( );
            while ( $dao->fetch( ) ) {
                $summary['cancel']['count']    += $dao->cancel_count;
                $summary['cancel']['amount'][]  = CRM_Utils_Money::format( $dao->cancel_amount, $dao->currency );
                $summary['cancel']['avg'][]     = CRM_Utils_Money::format( $dao->cancel_avg   , $dao->currency );
            }
            $summary['cancel']['amount'] = implode( ',&nbsp;', $summary['cancel']['amount'] );
            $summary['cancel']['avg']    = implode( ',&nbsp;', $summary['cancel']['avg']    );
        }

        return $summary;
    }

    /**
     * getter for the qill object
     *
     * @return string
     * @access public
     */
    function qill( ) 
    {
        return $this->_qill;
    }


    /**
     * default set of return default hier return properties
     *
     * @return void
     * @access public
     */
    static function &defaultHierReturnProperties( ) 
    {
        if ( ! isset( self::$_defaultHierReturnProperties ) ) {
            self::$_defaultHierReturnProperties = array(
                                                        'home_URL'               => 1, 
                                                        'image_URL'              => 1, 
                                                        'legal_identifier'       => 1, 
                                                        'external_identifier'    => 1,
                                                        'contact_type'           => 1,
                                                        'contact_sub_type'       => 1,
                                                        'sort_name'              => 1,
                                                        'display_name'           => 1,
                                                        'nick_name'              => 1, 
                                                        'first_name'             => 1, 
                                                        'middle_name'            => 1, 
                                                        'last_name'              => 1, 
                                                        'individual_prefix'      => 1, 
                                                        'individual_suffix'      => 1,
                                                        'email_greeting'         => 1,
                                                        'postal_greeting'        => 1,
                                                        'addressee'              => 1,
                                                        'birth_date'             => 1,
                                                        'gender'                 => 1,
                                                        'preferred_communication_method' => 1,
                                                        'do_not_phone'                   => 1, 
                                                        'do_not_email'                   => 1, 
                                                        'do_not_mail'                    => 1,
                                                        'do_not_sms'                     => 1,
                                                        'do_not_trade'                   => 1, 
                                                        'location'                       => 
                                                        array( '1' => array ( 'location_type'      => 1,
                                                                              'street_address'     => 1,
                                                                              'city'               => 1,
                                                                              'state_province'     => 1,
                                                                              'postal_code'        => 1, 
                                                                              'postal_code_suffix' => 1, 
                                                                              'country'            => 1,
                                                                              'phone-Phone'        => 1,
                                                                              'phone-Mobile'       => 1,
                                                                              'phone-Fax'          => 1,
                                                                              'phone-1'            => 1,
                                                                              'phone-2'            => 1,
                                                                              'phone-3'            => 1,
                                                                              'im-1'               => 1,
                                                                              'im-2'               => 1,
                                                                              'im-3'               => 1,
                                                                              'email-1'            => 1,
                                                                              'email-2'            => 1,
                                                                              'email-3'            => 1,
                                                                              ),
                                                               '2' => array ( 
                                                                             'location_type'      => 1,
                                                                             'street_address'     => 1, 
                                                                             'city'               => 1, 
                                                                             'state_province'     => 1, 
                                                                             'postal_code'        => 1, 
                                                                             'postal_code_suffix' => 1, 
                                                                             'country'            => 1, 
                                                                             'phone-Phone'        => 1,
                                                                             'phone-Mobile'       => 1,
                                                                             'phone-1'            => 1,
                                                                             'phone-2'            => 1,
                                                                             'phone-3'            => 1,
                                                                             'im-1'               => 1,
                                                                             'im-2'               => 1,
                                                                             'im-3'               => 1,
                                                                             'email-1'            => 1,
                                                                             'email-2'            => 1,
                                                                             'email-3'            => 1,
                                                                             ) 
                                                               ),
                                                        );
            
        }
        return self::$_defaultHierReturnProperties;
    }

    function dateQueryBuilder( &$values,
                               $tableName, $fieldName, $dbFieldName, $fieldTitle,
                               $appendTimeStamp = true ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        if ( $name == $fieldName . '_low' ) {
            $op     = '>=';
            $phrase = 'greater than or equal to';
        } else if ( $name == $fieldName . '_high' ) {
            $op     = '<=';
            $phrase = 'less than or equal to';
        } else if ( $name == $fieldName ) {
            $op     = '=';
            $phrase = '=';
        } else {
            return;
        }

        if ( $value ) {
            $date    = $value;
            // add 235959 if its less that or equal to
            if ( $op == '<='      &&
                 $appendTimeStamp &&
                 strlen( $date ) == 10 ) {
                $date .= ' 23:59:59';
            }

            $date = CRM_Utils_Date::processDate( $date );

            if ( !$appendTimeStamp ) {
                $date = substr(  $date, 0, 8 );
            }

            $format  = CRM_Utils_Date::customFormat( $date );
            
            if ( $date ) {
                $this->_where[$grouping][] = "{$tableName}.{$dbFieldName} $op '$date'";
            } else {
                $this->_where[$grouping][] = "{$tableName}.{$dbFieldName} $op";
            }
            $this->_tables[$tableName] = $this->_whereTables[$tableName] = 1;
            $this->_qill[$grouping][]  = "$fieldTitle - $phrase \"$format\"";
        }
    }

    function numberRangeBuilder( &$values,
                                 $tableName, $fieldName, $dbFieldName, $fieldTitle, $options = null ) 
    {
        list( $name, $op, $value, $grouping, $wildcard ) = $values;

        if ( $name == $fieldName . '_low' ) {
            $op     = '>=';
            $phrase = 'greater than';
        } else if ( $name == $fieldName . '_high' ) {
            $op     = '<=';
            $phrase = 'less than';
        } else if ( $name == $fieldName ) {
            $op     = '=';
            $phrase = '=';
        } else {
            return;
        }

        $this->_where[$grouping][] = "{$tableName}.{$dbFieldName} $op {$value}";
        $this->_tables[$tableName] = $this->_whereTables[$tableName] = 1;
        if ( !$options ) { 
            $this->_qill[$grouping][]  = "$fieldTitle - $phrase \"$value\"";
        } else {
            $this->_qill[$grouping][]  = "$fieldTitle - $phrase \"$options[$value]\"";
        }
    }

    /**
     * Given the field name, operator, value & its data type
     * builds the where Clause for the query
     * used for handling 'IS NULL'/'IS NOT NULL' operators
     *
     * @param string  $field       fieldname 
     * @param string  $op          operator
     * @param string  $value       value
     * @param string  $dataType    data type of the field
     *
     * @return where clause for the query
     * @access public
     */
    function buildClause( $field, $op, $value = null, $dataType = null ) 
    {
        $op = trim( $op );
        $clause = "$field $op";
        
        switch ( $op ) {
            
        case 'IS NULL':
        case 'IS NOT NULL':
            return $clause;

        case 'IN':
            if ( isset($dataType) ) {
                $value = CRM_Utils_Type::escape( $value, "String" );
                $values = explode ( ',', CRM_Utils_Array::value( 0, explode(')',CRM_Utils_Array::value( 1, explode('(', $value ) ) ) ) );
                // supporting multiple values in IN clause
                $val = array();
                foreach ( $values as $v ) {
                    $val[] = "'" . CRM_Utils_Type::escape( $v, $dataType ) . "'";
                }
                $value = "(" . implode( $val, "," ) . ")";
            }
            return "$clause $value";
            
        default:
            if ( isset($dataType) ) {
                $value = CRM_Utils_Type::escape( $value, $dataType );
            }
            if ( $dataType == 'String' ) {
                $value = "'" . strtolower( $value ) . "'";
            }
            return "$clause $value";
        }
        
    }

}
