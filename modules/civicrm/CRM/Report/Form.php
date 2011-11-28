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

require_once 'CRM/Core/Form.php';

class CRM_Report_Form extends CRM_Core_Form {

    const  
        ROW_COUNT_LIMIT = 50;

    /** 
     * Operator types - used for displaying filter elements
     */
    const
        OP_INT         =  1,
        OP_STRING      =  2,
        OP_DATE        =  4,
        OP_FLOAT       =  8,

        OP_SELECT      =  64,
        OP_MULTISELECT =  65,
        OP_MULTISELECT_SEPARATOR = 66;

    /**
     * The id of the report instance
     *
     * @var integer
     */
    protected $_id;

    /**
     * The id of the report template
     *
     * @var integer;
     */
    protected $_templateID;

    /**
     * The report title
     *
     * @var string
     */
    protected $_title;

    /**
     * The set of all columns in the report. An associative array
     * with column name as the key and attribues as the value
     *
     * @var array
     */
    protected $_columns = array( );

    /**
     * The set of filters in the report
     *
     * @var array
     */
    protected $_filters = array( );

    /**
     * The set of optional columns in the report
     *
     * @var array
     */
    protected $_options = array( );

    protected $_defaults = array( );

    /**
     * Set of statistic fields
     *
     * @var array
     */
    protected $_statFields = array();

    /**
     * Set of statistics data
     *
     * @var array
     */
    protected $_statistics = array();

    /**
     * List of fields not to be repeated during display
     *
     * @var array
     */
    protected $_noRepeats  = array();

    /**
     * List of fields not to be displayed
     *
     * @var array
     */
    protected $_noDisplay  = array();

    /**
     * Object type that a custom group extends
     *
     * @var null
     */
    protected $_customGroupExtends = null;
    protected $_customGroupFilters = true;
    protected $_customGroupGroupBy = false;

    /**
     * build tags filter
     *
     */
    protected $_tagFilter = false;

    /**
     * build groups filter
     *
     */
    protected $_groupFilter = false;
    /**
     * Navigation fields
     *
     * @var array
     */
    public $_navigation = array();
    
    /**
     * An attribute for checkbox/radio form field layout
     *
     * @var array
     */
    protected $_fourColumnAttribute = array('</td><td width="25%">', '</td><td width="25%">', 
                                            '</td><td width="25%">', '</tr><tr><td>');

    protected $_force = 1;

    protected $_params         = null;
    protected $_formValues     = null;
    protected $_instanceValues = null;

    protected $_instanceForm   = false;

    protected $_instanceButtonName = null;
    protected $_printButtonName    = null;
    protected $_pdfButtonName      = null;
    protected $_csvButtonName      = null;
    protected $_groupButtonName    = null;
    protected $_chartButtonName    = null;
    protected $_csvSupported       = true;
    protected $_add2groupSupported = true;
    protected $_groups             = null;
    protected $_having             = null;
    protected $_rowsFound          = null;
    protected $_select             = null;        
    protected $_selectAliases      = array();
    protected $_rollup             = null;
    protected $_limit              = null;
    protected $_orderBy            = null;
    protected $_sections           = null;
    protected $_autoIncludeIndexedFieldsAsOrderBys = 0;
    
    /**
     * To what frequency group-by a date column
     *
     * @var array
     */
    protected $_groupByDateFreq = array( 'MONTH'    => 'Month',
                                         'YEARWEEK' => 'Week',
                                         'QUARTER'  => 'Quarter',
                                         'YEAR'     => 'Year'  );
    
    /**
     * Variables to hold the acl inner join and where clause
     */
    protected $_aclFrom  = null;
    protected $_aclWhere = null;

    /**
     * Array of DAO tables having columns included in SELECT or ORDER BY clause
     * 
     * @var array
     */
    protected $_selectedTables;

    /**
     * 
     */
    function __construct( ) {
        parent::__construct( );
        
        // build tag filter
        if ( $this->_tagFilter ) {
            $this->buildTagFilter( );
        }
        
        if( $this->_groupFilter){
           $this->buildGroupFilter();
        }
        // do not allow custom data for reports if user don't have
        // permission to access custom data.
        if ( !empty( $this->_customGroupExtends ) && !CRM_Core_Permission::check( 'access all custom data' ) ) {
            $this->_customGroupExtends = array( );
        }

        // merge custom data columns to _columns list, if any
        $this->addCustomDataToColumns( );
    }

    function preProcessCommon( ) {
        $this->_force = CRM_Utils_Request::retrieve( 'force',
                                                     'Boolean',
                                                     CRM_Core_DAO::$_nullObject );

        $this->_section = CRM_Utils_Request::retrieve( 'section', 'Integer', CRM_Core_DAO::$_nullObject );
        
        $this->assign( 'section', $this->_section );
                                                 
        $this->_id = $this->get( 'instanceId' );
        if ( !$this->_id ) {
            $this->_id  = CRM_Report_Utils_Report::getInstanceID( );
	     if ( !$this->_id ) {
	         $this->_id  = CRM_Report_Utils_Report::getInstanceIDForPath( );
	     }
        }

        // set qfkey so that pager picks it up and use it in the "Next > Last >>" links.
        // FIXME: Note setting it in $_GET doesn't work, since pager generates link based on QUERY_STRING
        $_SERVER['QUERY_STRING'] .= "&qfKey={$this->controller->_key}"; 

        if ( $this->_id ) {
            $this->assign( 'instanceId', $this->_id );
            $params = array( 'id' => $this->_id );
            $this->_instanceValues = array( );
            CRM_Core_DAO::commonRetrieve( 'CRM_Report_DAO_Instance',
                                          $params,
                                          $this->_instanceValues );
            if ( empty($this->_instanceValues) ) {
                CRM_Core_Error::fatal("Report could not be loaded.");
            }

            if ( !empty($this->_instanceValues['permission']) && 
                 (!(CRM_Core_Permission::check( $this->_instanceValues['permission'] ) ||
                    CRM_Core_Permission::check( 'administer Reports' ))) ) {
                CRM_Utils_System::permissionDenied( );
                CRM_Utils_System::civiExit( );
            }
            $this->_formValues = unserialize( $this->_instanceValues['form_values'] );

            // lets always do a force if reset is found in the url.
            if ( CRM_Utils_Array::value( 'reset', $_GET ) ) {
                $this->_force = 1;
            }

            // set the mode
            $this->assign( 'mode', 'instance' );
        } else {
            list($optionValueID, $optionValue) = CRM_Report_Utils_Report::getValueIDFromUrl( );
            $instanceCount = CRM_Report_Utils_Report::getInstanceCount( $optionValue );
            if ( ($instanceCount > 0) && $optionValueID ) {
                $this->assign( 'instanceUrl', 
                               CRM_Utils_System::url( 'civicrm/report/list', 
                                                      "reset=1&ovid=$optionValueID" ) );
            }
            if ( $optionValueID ) {
                $this->_description = 
                    CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $optionValueID, 'description' );
            }
            
            // set the mode
            $this->assign( 'mode', 'template' );
        }

        // lets display the 
        $this->_instanceForm       = $this->_force || $this->_id || ( ! empty( $_POST ) );

        // do not display instance form if administer Reports permission is absent
        if ( ! CRM_Core_Permission::check( 'administer Reports' ) ) {
            $this->_instanceForm   = false;
        }
    
        $this->assign( 'criteriaForm', false );
        if ( CRM_Core_Permission::check( 'administer Reports' ) ||
             CRM_Core_Permission::check( 'access Report Criteria' ) ) {
            $this->assign( 'criteriaForm', true );
        }

        $this->_instanceButtonName = $this->getButtonName( 'submit', 'save'  );
        $this->_printButtonName    = $this->getButtonName( 'submit', 'print' );
        $this->_pdfButtonName      = $this->getButtonName( 'submit', 'pdf'   );
        $this->_csvButtonName      = $this->getButtonName( 'submit', 'csv'   );
        $this->_groupButtonName    = $this->getButtonName( 'submit', 'group' );
        $this->_chartButtonName    = $this->getButtonName( 'submit', 'chart' );
    }

    function addBreadCrumb() {
        $breadCrumbs = array( array( 'title' => ts('Report Templates'),
                                     'url'   => CRM_Utils_System::url('civicrm/admin/report/template/list','reset=1') ) );
        
        CRM_Utils_System::appendBreadCrumb( $breadCrumbs );
    }    

    function preProcess( ) {

        self::preProcessCommon( );
        if ( !$this->_id ) {
            self::addBreadCrumb();
        }

        foreach ( $this->_columns as $tableName => $table ) {
            // set alias
            if ( ! isset( $table['alias'] ) ) {
                $this->_columns[$tableName]['alias'] = substr( $tableName, 8 ) . '_civireport';
            } else {
                $this->_columns[$tableName]['alias'] = $table['alias'] . '_civireport';
            }

            $this->_aliases[$tableName] = $this->_columns[$tableName]['alias'];

            // higher preference to bao object
            if ( array_key_exists('bao', $table) ) {
                require_once str_replace( '_', DIRECTORY_SEPARATOR, $table['bao'] . '.php' );
                eval( "\$expFields = {$table['bao']}::exportableFields( );");
            } else {
                require_once str_replace( '_', DIRECTORY_SEPARATOR, $table['dao'] . '.php' );
                eval( "\$expFields = {$table['dao']}::export( );");
            }

            $doNotCopy   = array('required');

            $fieldGroups = array('fields', 'filters', 'group_bys', 'order_bys');
            foreach ( $fieldGroups as $fieldGrp ) {
                if ( CRM_Utils_Array::value( $fieldGrp, $table ) && is_array( $table[$fieldGrp] ) ) {
                    foreach ( $table[$fieldGrp] as $fieldName => $field ) {
                        if ( array_key_exists($fieldName, $expFields) ) {
                            foreach ( $doNotCopy as $dnc ) {
                                // unset the values we don't want to be copied.
                                unset($expFields[$fieldName][$dnc]);
                            }
                            if ( empty($field) ) {
                                $this->_columns[$tableName][$fieldGrp][$fieldName] = $expFields[$fieldName];
                            } else {
                                foreach ( $expFields[$fieldName] as $property => $val ) {
                                    if ( ! array_key_exists($property, $field) ) {
                                        $this->_columns[$tableName][$fieldGrp][$fieldName][$property] = $val;
                                    }
                                }
                            }

                            // fill other vars
                            if ( CRM_Utils_Array::value( 'no_repeat', $field ) ) {
                                $this->_noRepeats[] = "{$tableName}_{$fieldName}";
                            }
                            if ( CRM_Utils_Array::value( 'no_display', $field ) ) {
                                $this->_noDisplay[] = "{$tableName}_{$fieldName}";
                            }
                        }

                        // set alias = table-name, unless already set
                        $alias = isset($field['alias']) ? $field['alias'] : 
                            ( isset($this->_columns[$tableName]['alias']) ? 
                              $this->_columns[$tableName]['alias'] : $tableName );
                        $this->_columns[$tableName][$fieldGrp][$fieldName]['alias'] = $alias;

                        // set name = fieldName, unless already set
                        if ( !isset($this->_columns[$tableName][$fieldGrp][$fieldName]['name']) ) {
                            $this->_columns[$tableName][$fieldGrp][$fieldName]['name'] = $fieldName;
                        }

                        // set dbAlias = alias.name, unless already set
                        if ( !isset($this->_columns[$tableName][$fieldGrp][$fieldName]['dbAlias']) ) {
                            $this->_columns[$tableName][$fieldGrp][$fieldName]['dbAlias'] = 
                                $alias . '.' . $this->_columns[$tableName][$fieldGrp][$fieldName]['name'];
                        }

                        if ( CRM_Utils_Array::value('type', $this->_columns[$tableName][$fieldGrp][$fieldName] ) && 
                             !isset($this->_columns[$tableName][$fieldGrp][$fieldName]['operatorType']) ) {
                            if ( in_array( $this->_columns[$tableName][$fieldGrp][$fieldName]['type'],
                                          array( CRM_Utils_Type::T_MONEY, CRM_Utils_Type::T_FLOAT ) ) ) {
                                $this->_columns[$tableName][$fieldGrp][$fieldName]['operatorType'] = 
                                    CRM_Report_Form::OP_FLOAT;
                            } else if ( in_array( $this->_columns[$tableName][$fieldGrp][$fieldName]['type'],
                                               array( CRM_Utils_Type::T_INT ) ) ) {
                                $this->_columns[$tableName][$fieldGrp][$fieldName]['operatorType'] = 
                                    CRM_Report_Form::OP_INT;
                            }
                        }
                    }
                }
            }

            // copy filters to a separate handy variable
            if ( array_key_exists('filters', $table) ) {
                $this->_filters[$tableName] = $this->_columns[$tableName]['filters'];
            }

            if ( array_key_exists('group_bys', $table) ) {
                $groupBys[$tableName] = $this->_columns[$tableName]['group_bys'];
            }

            if ( array_key_exists('fields', $table) ) {
                $reportFields[$tableName] = $this->_columns[$tableName]['fields'];
            }
            
        }

        if ( $this->_force ) {
            $this->setDefaultValues( false );
        }

        require_once 'CRM/Report/Utils/Get.php';
        CRM_Report_Utils_Get::processFilter( $this->_filters,
                                             $this->_defaults );
        CRM_Report_Utils_Get::processGroupBy( $groupBys,
                                              $this->_defaults );
        CRM_Report_Utils_Get::processFields( $reportFields,
                                             $this->_defaults );
        CRM_Report_Utils_Get::processChart( $this->_defaults );
        
        if ( $this->_force ) {
            $this->_formValues = $this->_defaults;
            $this->postProcess( );
        }

    }

    function setDefaultValues( $freeze = true ) {
        $freezeGroup = array();

        // FIXME: generalizing form field naming conventions would reduce 
        // lots of lines below.
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( !array_key_exists('no_display', $field) ) {
                        if ( isset($field['required']) ) {
                            // set default
                            $this->_defaults['fields'][$fieldName] = 1;
                            
                            if ( $freeze ) {
                                // find element object, so that we could use quickform's freeze method 
                                // for required elements
                                $obj = $this->getElementFromGroup("fields",
                                                                  $fieldName);
                                if ( $obj ) {
                                    $freezeGroup[] = $obj;
                                }
                            }
                        } else if ( isset($field['default']) ) {
                            $this->_defaults['fields'][$fieldName] = $field['default'];
                        }
                    }
                }
            }

            if ( array_key_exists('group_bys', $table) ) {
                foreach ( $table['group_bys'] as $fieldName => $field ) {
                    if ( isset($field['default']) ) {
                        if ( CRM_Utils_Array::value('frequency', $field) ) {
                            $this->_defaults['group_bys_freq'][$fieldName] = 'MONTH';
                        }
                        $this->_defaults['group_bys'][$fieldName] = $field['default'];
                    }
                }
            }
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    if ( isset($field['default']) ) {
                        if ( CRM_Utils_Array::value('type', $field ) & CRM_Utils_Type::T_DATE ) {
                            $this->_defaults["{$fieldName}_relative"] = $field['default'];
                        } else {
                            $this->_defaults["{$fieldName}_value"]    = $field['default'];
                        }
                    }
                    //assign default value as "in" for multiselect
                    //operator, To freeze the select element
                    if ( CRM_Utils_Array::value('operatorType', $field ) == CRM_Report_FORM::OP_MULTISELECT ) {
                        $this->_defaults["{$fieldName}_op"] = 'in';
                    } elseif ( CRM_Utils_Array::value('operatorType', $field ) == CRM_Report_FORM::OP_MULTISELECT_SEPARATOR  ) {
                        $this->_defaults["{$fieldName}_op"] = 'mhas'; 
                    } else if ( $op = CRM_Utils_Array::value( 'default_op', $field ) ) {
                        $this->_defaults["{$fieldName}_op"] = $op;
                    }
                }
            }

            if ( array_key_exists( 'order_bys', $table ) && 
                 is_array( $table['order_bys'] ) &&
                 !array_key_exists('order_bys', $this->_defaults) ) {

                $this->_defaults['order_bys'] = array();
                foreach ( $table['order_bys'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'default', $field ) ) {
                        $order_by = array(
                                          'column'  => $fieldName,
                                          'order'   => CRM_Utils_Array::value( 'default_order', $field, 'ASC' ),
                                          'section' => CRM_Utils_Array::value( 'default_is_section', $field, 0 ),
                                          );

                        if ( CRM_Utils_Array::value( 'default_weight', $field ) ) {
                            $this->_defaults['order_bys'][ (int) $field['default_weight']] = $order_by;
                        } else {
                            array_unshift( $this->_defaults['order_bys'], $order_by);
                        }
                    }
                }
            }

            foreach ( $this->_options as $fieldName => $field ) {
                if ( isset($field['default']) ) {
                    $this->_defaults['options'][$fieldName] = $field['default'];
                }
            }
        }

        if ( !empty($this->_submitValues) ) {
            $this->preProcessOrderBy($this->_submitValues);
        } else {
            $this->preProcessOrderBy($this->_defaults);
        }

        // lets finish freezing task here itself
        if ( !empty($freezeGroup) ) {
            foreach ( $freezeGroup as $elem ) {
                $elem->freeze();
            }
        }

        if ( $this->_formValues ) {
            $this->_defaults = array_merge( $this->_defaults, $this->_formValues );
        }

        if ( $this->_instanceValues ) {
            $this->_defaults = array_merge( $this->_defaults, $this->_instanceValues );
        }

        require_once 'CRM/Report/Form/Instance.php';
        CRM_Report_Form_Instance::setDefaultValues( $this, $this->_defaults );
        
        return $this->_defaults;
    }

    function getElementFromGroup( $group, $grpFieldName ) {
        $eleObj = $this->getElement( $group );
        foreach ( $eleObj->_elements as $index => $obj ) {
            if ( $grpFieldName == $obj->_attributes['name']) {
                return $obj;
            }
        }
        return false;
    }

    function addColumns( ) {
        $options = array();
        $colGroups = null;
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( !array_key_exists('no_display', $field) ) {
                        if ( isset($table['grouping']) ) { 
                            $tableName = $table['grouping'];
                        }
                        $colGroups[$tableName]['fields'][$fieldName] = CRM_Utils_Array::value('title',$field);

                        if ( isset($table['group_title']) ) { 
                            $colGroups[$tableName]['group_title'] = $table['group_title'];
                        }

                        $options[$fieldName] = CRM_Utils_Array::value('title',$field);
                    }
                } 
            }
        }
        
        $this->addCheckBox( "fields", ts('Select Columns'), $options, null, 
                            null, null, null, $this->_fourColumnAttribute, true );
        $this->assign( 'colGroups', $colGroups );
    }

    function addFilters( ) {
        require_once 'CRM/Utils/Date.php';
        require_once 'CRM/Core/Form/Date.php';
        $options = $filters = array();
        $count = 1;
        foreach ( $this->_filters as $table => $attributes ) {
            foreach ( $attributes as $fieldName => $field ) {
                // get ready with option value pair
                $operations = $this->getOperationPair( CRM_Utils_Array::value( 'operatorType', $field ), 
                                                       $fieldName );
                
                $filters[$table][$fieldName] = $field;
                
                switch ( CRM_Utils_Array::value( 'operatorType', $field )) {
                case CRM_Report_FORM::OP_MULTISELECT :
                case CRM_Report_FORM::OP_MULTISELECT_SEPARATOR :
                    // assume a multi-select field
                    if ( !empty( $field['options'] ) ) {
                        $element = $this->addElement('select', "{$fieldName}_op", ts( 'Operator:' ), $operations);
                        if ( count($operations) <= 1 ) {
                            $element->freeze();
                        }
                        $select = $this->addElement('select', "{$fieldName}_value", null, 
                                                    $field['options'], array( 'size' => 4, 
                                                                              'style' => 'width:250px'));
                        $select->setMultiple( true );
                    }
                    break;
                    
                case CRM_Report_FORM::OP_SELECT :
                    // assume a select field
                    $this->addElement('select', "{$fieldName}_op", ts( 'Operator:' ), $operations);
                    $this->addElement('select', "{$fieldName}_value", null, $field['options']);
                    break;
                    
                case CRM_Report_FORM::OP_DATE :
                    // build datetime fields
                    CRM_Core_Form_Date::buildDateRange( $this, $fieldName, $count );
                    $count++;
                    break;
                    
                case CRM_Report_FORM::OP_INT:
                case CRM_Report_FORM::OP_FLOAT:   
                    // and a min value input box
                    $this->add( 'text', "{$fieldName}_min", ts('Min') );
                    // and a max value input box
                    $this->add( 'text', "{$fieldName}_max", ts('Max') );
                default:
                    // default type is string
                    $this->addElement('select', "{$fieldName}_op", ts( 'Operator:' ), $operations,
                                      array('onchange' =>"return showHideMaxMinVal( '$fieldName', this.value );"));
                    // we need text box for value input
                    $this->add( 'text', "{$fieldName}_value", null );
                    break;
                }
            }
        }
        $this->assign( 'filters', $filters );
    }

    function addOptions( ) {
        if ( !empty( $this->_options ) ) {
            // FIXME: For now lets build all elements as checkboxes. 
            // Once we clear with the format we can build elements based on type
            
            $options = array();
            foreach ( $this->_options as $fieldName => $field ) {
                if ( $field['type'] == 'select' ) {
                    $this->addElement( 'select', "{$fieldName}", $field['title'], $field['options']);
                } else {
                    $options[$field['title']] = $fieldName;
                }
            }

            $this->addCheckBox( "options", $field['title'], 
                                $options, null, 
                                null, null, null, $this->_fourColumnAttribute );
        }
    }

    function addChartOptions( ) {
        if ( !empty( $this->_charts ) ) {
            $this->addElement( 'select', "charts", ts( 'Chart' ), $this->_charts, array('onchange' => 'disablePrintPDFButtons(this.value);' ) );
            $this->assign( 'charts', $this->_charts );
            $this->addElement('submit', $this->_chartButtonName, ts('View') );
        }
    }
    
    function addGroupBys( ) {
        $options = $freqElements = array( );

        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('group_bys', $table) ) {
                foreach ( $table['group_bys'] as $fieldName => $field ) {
                    if ( !empty($field) ) {
                        $options[$field['title']] = $fieldName;
                        if ( CRM_Utils_Array::value( 'frequency', $field ) ) {
                            $freqElements[$field['title']] = $fieldName;
                        }
                    }
                }
            }
        }
        $this->addCheckBox( "group_bys", ts('Group by columns'), $options, null, 
                            null, null, null, $this->_fourColumnAttribute );
        $this->assign( 'groupByElements', $options );

        foreach ( $freqElements as $name ) {
            $this->addElement( 'select', "group_bys_freq[$name]", 
                               ts( 'Frequency' ), $this->_groupByDateFreq );
        }
    }

    function addOrderBys( ) {
        $options = array();
        foreach ( $this->_columns as $tableName => $table ) {
            
            // Report developer may define any column to order by; include these as order-by options
            if ( array_key_exists('order_bys', $table) ) {
                foreach ( $table['order_bys'] as $fieldName => $field ) {
                    if ( !empty($field) ) {
                        $options[$fieldName] = $field['title'];
                    }
                }
            }

            /* Add searchable custom fields as order-by options, if so requested
             * (These are already indexed, so allowing to order on them is cheap.)
             */
            if ( $this->_autoIncludeIndexedFieldsAsOrderBys && array_key_exists('extends', $table) && ! empty($table['extends']) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( !array_key_exists('no_display', $field) ) {
                        $options[$fieldName] = $field['title'];
                    }
                }
            }
        }

        asort( $options );

        $this->assign( 'orderByOptions', $options );

        if ( ! empty( $options ) ) {
            $options = array( '-' => ' - none - ' ) + $options;
            for ( $i = 1; $i <= 5; $i++ ) {
                $this->addElement( 'select', "order_bys[{$i}][column]", ts('Order by Column'), $options );
                $this->addElement( 'select', "order_bys[{$i}][order]", ts('Order by Order'), array( 'ASC' => 'Ascending', 'DESC' => 'Descending' ) );
                $this->addElement( 'checkbox', "order_bys[{$i}][section]", ts('Order by Section'), false, array( 'id' => "order_by_section_$i") );
            }
        }
    }

    function buildInstanceAndButtons( ) {
        require_once 'CRM/Report/Form/Instance.php';
        CRM_Report_Form_Instance::buildForm( $this );
        
        $label = $this->_id ? ts( 'Update Report' ) : ts( 'Create Report' );
        
        $this->addElement( 'submit', $this->_instanceButtonName, $label );
        $this->addElement('submit', $this->_printButtonName, ts( 'Print Report' ) );
        $this->addElement('submit', $this->_pdfButtonName, ts( 'PDF' ) );
        if ( $this->_instanceForm ){
            $this->assign( 'instanceForm', true );
        }

        $label = $this->_id ? ts( 'Print Report' ) : ts( 'Print Preview' );
        $this->addElement('submit', $this->_printButtonName, $label );

        $label = $this->_id ? ts( 'PDF' ) : ts( 'Preview PDF' );
        $this->addElement('submit', $this->_pdfButtonName, $label );

        $label = $this->_id ? ts( 'Export to CSV' ) : ts( 'Preview CSV' );

        if ( $this->_csvSupported ) {
            $this->addElement('submit', $this->_csvButtonName, $label );
        }

        if ( CRM_Core_Permission::check( 'administer Reports' ) && $this->_add2groupSupported ) {
            $this->addElement( 'select', 'groups', ts( 'Group' ), 
                               array( '' => ts( '- select group -' )) + CRM_Core_PseudoConstant::staticGroup( ) );
            $this->assign( 'group', true );
        }
            
        $label = ts( 'Add these Contacts to Group' );
        $this->addElement('submit', $this->_groupButtonName, $label, array('onclick' => 'return checkGroup();') );
            
        $this->addChartOptions( );
        $this->addButtons( array(
                                 array ( 'type'      => 'submit',
                                         'name'      => ts('Preview Report'),
                                         'isDefault' => true   ),
                                 )
                           );
    }

    function buildQuickForm( ) {
        $this->addColumns( );

        $this->addFilters( );
      
        $this->addOptions( );

        $this->addGroupBys( );

        $this->addOrderBys( );

        $this->buildInstanceAndButtons( );

        //add form rule for report
        if ( is_callable( array( $this, 'formRule' ) ) ) {
            $this->addFormRule( array( get_class($this), 'formRule' ), $this );
        }
    }
       
    // a formrule function to ensure that fields selected in group_by
    // (if any) should only be the ones present in display/select fields criteria;
    // note: works if and only if any custom field selected in group_by.
    function customDataFormRule( $fields, $ignoreFields = array( ) ) {
        $errors = array( );
        if( !empty($this->_customGroupExtends) && $this->_customGroupGroupBy && !empty($fields['group_bys']) ) {
            foreach( $this->_columns as $tableName => $table ) {
                if( (substr($tableName, 0, 13) == 'civicrm_value' || substr($tableName, 0, 12) == 'custom_value') && !empty( $this->_columns[$tableName]['fields']) ) {
                    foreach( $this->_columns[$tableName]['fields'] as $fieldName => $field ) {
                        if ( array_key_exists( $fieldName, $fields['group_bys'] ) && 
                             !array_key_exists( $fieldName, $fields['fields'] ) ) {
                            $errors['fields'] = "Please make sure fields selected in 'Group by Columns' section are also selected in 'Display Columns' section.";
                        } elseif ( array_key_exists( $fieldName, $fields['group_bys'] ) ) {
                            foreach( $fields['fields'] as $fld => $val ) {
                                if( !array_key_exists( $fld, $fields['group_bys'] ) && !in_array($fld, $ignoreFields )) {
                                    $errors['fields'] = "Please ensure that fields selected in 'Display Columns' are also selected in 'Group by Columns' section.";
                                }
                            }
                        }
                    }
                }
            }
        }
        return $errors;
    }

    // Note: $fieldName param allows inheriting class to build operationPairs 
    // specific to a field.
    function getOperationPair( $type = "string", $fieldName = null ) {
        // FIXME: At some point we should move these key-val pairs 
        // to option_group and option_value table.

        switch ( $type ) {
        case CRM_Report_FORM::OP_INT :
        case CRM_Report_FORM::OP_FLOAT :
            return array( 'lte' => ts('Is less than or equal to'), 
                          'gte' => ts('Is greater than or equal to'),
                          'bw'  => ts('Is between'),
                          'eq'  => ts('Is equal to'), 
                          'lt'  => ts('Is less than'), 
                          'gt'  => ts('Is greater than'),
                          'neq' => ts('Is not equal to'), 
                          'nbw' => ts('Is not between'),
                          'nll' => ts('Is empty (Null)'),
                          'nnll' => ts('Is not empty (Null)')
                          );
            break;
        case CRM_Report_FORM::OP_SELECT :
            return array( 'eq'  => ts('Is equal to') );
            break;
        case CRM_Report_FORM::OP_MULTISELECT :
            return array( 'in'  => ts('Is one of'),
                          'notin' => ts('Is not one of') );
            break; 
        case CRM_Report_FORM::OP_DATE :
            return array( 'nll'  => ts('Is empty (Null)'),
                          'nnll' => ts('Is not empty (Null)'));
            break;
        case CRM_Report_FORM::OP_MULTISELECT_SEPARATOR :
            // use this operator for the values, concatenated with separator. For e.g if 
            // multiple options for a column is stored as ^A{val1}^A{val2}^A  
            return array( 'mhas'  => ts('Is one of') );
            break;
        default:
            // type is string
            return array('has'  => ts('Contains'), 
                         'sw'   => ts('Starts with'), 
                         'ew'   => ts('Ends with'),
                         'nhas' => ts('Does not contain'), 
                         'eq'   => ts('Is equal to'), 
                         'neq'  => ts('Is not equal to'),
                         'nll'  => ts('Is empty (Null)'),
                         'nnll'  => ts('Is not empty (Null)')
                         );
        }
    }

    function buildTagFilter( ) {
        require_once 'CRM/Core/BAO/Tag.php';
        $contactTags = CRM_Core_BAO_Tag::getTags( );
        if ( !empty($contactTags) ) {
            $this->_columns['civicrm_tag'] = 
                array( 'dao'     => 'CRM_Core_DAO_Tag',
                       'filters' =>             
                       array( 'tagid' => 
                              array( 'name'         => 'tag_id',
                                     'title'        => ts( 'Tag' ),
                                     'tag'          => true,
                                     'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                     'options'      => $contactTags
                                     ), 
                              ), 
                       );
        }
    }
    
    /*
     * Adds group filters to _columns (called from _Constuct
     */
     function buildGroupFilter( ) {
          $this->_columns['civicrm_group']['filters'] =         
                          array( 'gid' => 
                                 array( 'name'         => 'group_id',
                                        'title'        => ts( 'Group' ),
                                        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                        'group'        => true,
                                        'options'      => CRM_Core_PseudoConstant::group( ) ), 
                   
                           );
       if(empty($this->_columns['civicrm_group']['dao'])){
          $this->_columns['civicrm_group']['dao']  = 'CRM_Contact_DAO_GroupContact';
       }
       if(empty($this->_columns['civicrm_group']['alias'])){
          $this->_columns['civicrm_group']['alias']  = 'cgroup';
       }                   
     }
     
    static function getSQLOperator( $operator = "like" ) {
        switch ( $operator ) {
        case 'eq':
            return '=';
        case 'lt':
            return '<'; 
        case 'lte':
            return '<='; 
        case 'gt':
            return '>'; 
        case 'gte':
            return '>='; 
        case 'ne' :
        case 'neq':
            return '!=';
        case 'nhas':
            return 'NOT LIKE';
        case 'in':
            return 'IN';
        case 'notin':
            return 'NOT IN';
        case 'nll' :
            return 'IS NULL';
        case 'nnll' :
            return 'IS NOT NULL';
        default:
            // type is string
            return 'LIKE';
        }
    }

    function whereClause( &$field, $op,
                          $value, $min, $max ) {

        $type   = CRM_Utils_Type::typeToString( CRM_Utils_Array::value( 'type', $field ) );
        $clause = null;

        switch ( $op ) {
        case 'bw':
        case 'nbw':
            if ( ( $min !== null && strlen( $min ) > 0 ) ||
                 ( $max !== null && strlen( $max ) > 0 ) ) {
                $min = CRM_Utils_Type::escape( $min, $type );
                $max = CRM_Utils_Type::escape( $max, $type );
                $clauses = array( );
                if ( $min ) {
                    if ( $op == 'bw' ) {
                        $clauses[] = "( {$field['dbAlias']} >= $min )";
                    } else {
                        $clauses[] = "( {$field['dbAlias']} < $min )";
                    }
                }
                if ( $max ) {
                    if ( $op == 'bw' ) {
                        $clauses[] = "( {$field['dbAlias']} <= $max )";
                    } else {
                        $clauses[] = "( {$field['dbAlias']} > $max )";
                    }
                }

                if ( ! empty( $clauses ) ) {
                    if ( $op == 'bw' ) {
                        $clause = implode( ' AND ', $clauses );
                    } else {
                        $clause = implode( ' OR ', $clauses );
                    }
                }
            }
            break;

        case 'has':
        case 'nhas': 
            if ( $value !== null && strlen( $value ) > 0 ) {
                $value  = CRM_Utils_Type::escape( $value, $type );
                if ( strpos( $value, '%' ) === false ) {
                    $value = "'%{$value}%'";
                } else {
                    $value = "'{$value}'";
                }
                $sqlOP  = self::getSQLOperator( $op );
                $clause = "( {$field['dbAlias']} $sqlOP $value )";
            }
            break;
                
        case 'in':
        case 'notin':
            if ( $value !== null && is_array( $value ) && count( $value ) > 0 ) {
                $sqlOP  = self::getSQLOperator( $op );
                if ( CRM_Utils_Array::value( 'type', $field ) == CRM_Utils_Type::T_STRING ) {
                    //cycle through selections and esacape values
                    foreach ( $value as $key => $selection ) {
                        $value[$key] = CRM_Utils_Type::escape( $selection, $type );
                    }
                    $clause = "( {$field['dbAlias']} $sqlOP ( '" . implode( "' , '", $value ) . "') )" ;
                } else {
                    // for numerical values
                    $clause = "{$field['dbAlias']} $sqlOP (" . implode( ', ', $value ) . ")";
                }
                if ( $op == 'notin' ) {
                    $clause = "( ". $clause ." OR {$field['dbAlias']} IS NULL )";
                } else {
                    $clause = "( ". $clause ." )";
                }
            }
            break;
            
        case 'mhas': // mhas == multiple has
            if ( $value !== null && count( $value ) > 0 ) {
                $sqlOP   = self::getSQLOperator( $op );
                $clause  = "{$field['dbAlias']} REGEXP '[[:<:]]" . implode( '|', $value) . "[[:>:]]'" ;
            }
            break;

        case 'sw':
        case 'ew':
            if ( $value !== null && strlen( $value ) > 0 ) {
                $value  = CRM_Utils_Type::escape( $value, $type );
                if ( strpos( $value, '%' ) === false ) {
                    if ( $op == 'sw' ) {
                        $value = "'{$value}%'";
                    } else {
                        $value = "'%{$value}'";
                    }
                } else {
                    $value = "'{$value}'";
                }
                $sqlOP  = self::getSQLOperator( $op );
                $clause = "( {$field['dbAlias']} $sqlOP $value )";
            }
            break;

        case 'nll':
        case 'nnll':
            $sqlOP  = self::getSQLOperator( $op );
            $clause = "( {$field['dbAlias']} $sqlOP )";
            break;
                
        default:
            if ( $value !== null && strlen( $value ) > 0 ) {
                if ( isset($field['clause']) ) {
                    // FIXME: we not doing escape here. Better solution is to use two 
                    // different types - data-type and filter-type 
                    eval("\$clause = \"{$field['clause']}\";"); 
                } else {
                    $value  = CRM_Utils_Type::escape( $value, $type );
                    $sqlOP  = self::getSQLOperator( $op );
                    if ( $field['type'] == CRM_Utils_Type::T_STRING ) {
                        $value = "'{$value}'";
                    }
                    $clause = "( {$field['dbAlias']} $sqlOP $value )";
                }
            }
            break;
        }
        
        if ( CRM_Utils_Array::value( 'group', $field ) && $clause ) {
            $clause = $this->whereGroupClause($field, $value, $op);
        } elseif ( CRM_Utils_Array::value( 'tag', $field ) && $clause ) {
            // not using left join in query because if any contact
            // belongs to more than one tag, results duplicate
            // entries.
            $clause = $this->whereTagClause($field, $value, $op);
        }
        
        return $clause;
    }

    function dateClause( $fieldName,
                         $relative, $from, $to ,$type = null ) {
        $clauses         = array( );
        if ( in_array( $relative, array_keys( $this->getOperationPair( CRM_Report_FORM::OP_DATE ) ) ) ) {
            $sqlOP = self::getSQLOperator( $relative );
            return "( {$fieldName} {$sqlOP} )";
        }

        list($from, $to) = self::getFromTo($relative, $from, $to);
        
        if ( $from ) {
            $from = ($type == CRM_Utils_Type::T_DATE)?substr($from,0,8 ):$from;
            $clauses[] = "( {$fieldName} >= $from )";
        }

        if ( $to ) {
            $to   = ($type == CRM_Utils_Type::T_DATE)?substr($to, 0, 8 ):$to;
            $clauses[] = "( {$fieldName} <= {$to} )";
        }

        if ( ! empty( $clauses ) ) {
            return implode( ' AND ', $clauses );
        }

        return null;
    }

    static function dateDisplay( $relative, $from, $to ) {
        list($from, $to) = self::getFromTo($relative, $from, $to);

        if ( $from ) {
            $clauses[] = CRM_Utils_Date::customFormat($from, null, array('m', 'M'));
        } else {
            $clauses[] = 'Past';
        }

        if ( $to ) {
            $clauses[] = CRM_Utils_Date::customFormat($to, null, array('m', 'M'));
        } else {
            $clauses[] = 'Today';
        }

        if ( ! empty( $clauses ) ) {
            return implode( ' - ', $clauses );
        }

        return null;
    }

    static function getFromTo( $relative, $from, $to ) {
        require_once 'CRM/Utils/Date.php';
        //FIX ME not working for relative 
        if ( $relative ) {
            list( $term, $unit ) = explode( '.', $relative );
            $dateRange = CRM_Utils_Date::relativeToAbsolute( $term, $unit );
            $from = $dateRange['from'];
            //Take only Date Part, Sometime Time part is also present in 'to'
            $to   = substr($dateRange['to'], 0, 8);
        }

        $from = CRM_Utils_Date::processDate( $from );
        $to   = CRM_Utils_Date::processDate( $to, '235959' );

        return array($from, $to);
    }

    function alterDisplay( &$rows ) {
        // custom code to alter rows
    }

    function alterCustomDataDisplay( &$rows ) {
        // custom code to alter rows having custom values
        if ( empty($this->_customGroupExtends) ) {
            return;
        }
        
        $customFieldIds  = array( );
        require_once 'CRM/Core/BAO/CustomField.php';
        foreach( $this->_params['fields'] as $fieldAlias => $value ) {
            if ( $fieldId = CRM_Core_BAO_CustomField::getKeyID($fieldAlias) ) {
                $customFieldIds[$fieldAlias] = $fieldId;
            }
        }
        if( empty($customFieldIds) ) {
            return;
        }

        $customFields    = $fieldValueMap = array( );
        $customFieldCols = array( 'column_name', 'data_type', 'html_type', 'option_group_id', 'id' );
        
        // skip for type date and ContactReference since date format is already handled
        $query = " 
SELECT cg.table_name, cf." . implode( ", cf.", $customFieldCols ) . ", ov.value, ov.label
FROM  civicrm_custom_field cf      
INNER JOIN civicrm_custom_group cg ON cg.id = cf.custom_group_id        
LEFT JOIN civicrm_option_value ov ON cf.option_group_id = ov.option_group_id
WHERE cg.extends IN ('" . implode( "','", $this->_customGroupExtends ) . "') AND 
      cg.is_active = 1 AND 
      cf.is_active = 1 AND
      cf.is_searchable = 1 AND
      cf.data_type   NOT IN ('ContactReference', 'Date') AND
      cf.id IN (". implode( ",", $customFieldIds ) .")";

        $dao = CRM_Core_DAO::executeQuery( $query );
        while( $dao->fetch( ) ) {
            foreach( $customFieldCols as $key ) {
                $customFields[$dao->table_name . '_custom_'. $dao->id][$key] = $dao->$key;
            }
            if( $dao->option_group_id ) {
                $fieldValueMap[$dao->option_group_id][$dao->value] = $dao->label;
            }
        }
        $dao->free( );

        $entryFound = false;
        foreach ( $rows as $rowNum => $row ) {
            foreach ( $row as $tableCol => $val ) {
                if ( array_key_exists( $tableCol, $customFields ) ) {
                    $rows[$rowNum][$tableCol] = 
                        $this->formatCustomValues( $val, $customFields[$tableCol], $fieldValueMap );
                    $entryFound = true;
                }
            }
            
            // skip looking further in rows, if first row itself doesn't 
            // have the column we need
            if ( !$entryFound ) {
                break;
            }
        }
    }
    
    function formatCustomValues( $value, $customField, $fieldValueMap ) {
        if ( CRM_Utils_System::isNull( $value ) ) {
            return;
        }

        $htmlType = $customField['html_type'];
        
        switch ( $customField['data_type'] ) {
        case 'Boolean':
            if ( $value == '1' ) {
                $retValue = ts('Yes');
            } else {
                $retValue = ts('No');
            }
            break;
        case 'Link': 
            $retValue = CRM_Utils_System::formatWikiURL( $value );
            break; 
        case 'File':
            $retValue = $value;
            break;  
        case 'Memo': 
            $retValue = $value;
            break;	   
        case 'Float':
            if ( $htmlType == 'Text' ) {
                $retValue = (float)$value;
                break;
            }   
        case 'Money':
            if ( $htmlType == 'Text') {
                require_once 'CRM/Utils/Money.php';
                $retValue = CRM_Utils_Money::format($value, null, '%a');
                break;
            }
        case 'String':
        case 'Int':
            if ( in_array( $htmlType, array( 'Text', 'TextArea' ) ) ) {
                $retValue = $value;
                break;
            }   
        case 'StateProvince':
        case 'Country':
           
            switch ( $htmlType ) {
            case 'Multi-Select Country':
                $value      = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value );
                $customData = array( );
                foreach( $value as $val ) {
                    if( $val ) { 
                        $customData[] = CRM_Core_PseudoConstant::country( $val, false );
                    }
                }
                $retValue = implode( ', ', $customData );
                break;
            case 'Select Country':
                $retValue = CRM_Core_PseudoConstant::country( $value, false );
                break;
            case 'Select State/Province':  
                $retValue =  CRM_Core_PseudoConstant::stateProvince( $value, false );
                break;
            case 'Multi-Select State/Province':
                $value      = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value );
                $customData = array( );
                foreach( $value as $val ) {
                    if ( $val ) {
                        $customData[] = CRM_Core_PseudoConstant::stateProvince( $val, false );
                    }
                }
                $retValue = implode( ', ', $customData );
                break;
            case 'Select':
            case 'Radio':
            case 'Autocomplete-Select':    
                $retValue = $fieldValueMap[$customField['option_group_id']][$value];
                break;
            case 'CheckBox': 
            case 'AdvMulti-Select':
            case 'Multi-Select':
                $value      = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value );
                $customData = array( );
                foreach( $value as $val ) {
                    if( $val ) { 
                        $customData[] = $fieldValueMap[$customField['option_group_id']][$val];
                    }
                }
                $retValue = implode( ', ', $customData );
                break;
            default:
                $retValue = $value;
            } 
        break;

        default:
             $retValue = $value; 
        }

        return $retValue;
    }

    function removeDuplicates( &$rows ) {
        if ( empty($this->_noRepeats) ) {
            return;
        }
        $checkList = array();

        foreach ( $rows as $key => $list ) {
            foreach ( $list as $colName => $colVal ) {
                if ( is_array($checkList[$colName]) && 
                     in_array($colVal, $checkList[$colName]) ) {
                    $rows[$key][$colName] = "";
                }
                if ( in_array($colName, $this->_noRepeats) ) {
                    $checkList[$colName][] = $colVal;
                }
            }
        }
    }

    function fixSubTotalDisplay( &$row, $fields, $subtotal = true ) {
        require_once 'CRM/Utils/Money.php';
        foreach ( $row as $colName => $colVal ) {
            if ( in_array($colName, $fields) ) {
                $row[$colName] = $row[$colName];
            } else if ( isset($this->_columnHeaders[$colName]) ) {
                if ( $subtotal ) {
                    $row[$colName] = "Subtotal";
                    $subtotal = false;
                } else {
                    unset($row[$colName]);
                }
            }
        }
    }

    function grandTotal( &$rows ) {
        if ( !$this->_rollup || ($this->_rollup == '') || 
             ($this->_limit && count($rows) >= self::ROW_COUNT_LIMIT) ) {
            return false;
        }
        $lastRow = array_pop($rows);

        $this->_grandFlag = false;
        foreach ($this->_columnHeaders as $fld => $val) {
            if ( !in_array($fld, $this->_statFields) ) {
                if ( !$this->_grandFlag ) {
                    $lastRow[$fld] = "Grand Total";
                    $this->_grandFlag = true;
                } else{
                    $lastRow[$fld] = "";
                }
            }
        }

        $this->assign( 'grandStat', $lastRow );
        return true;
    }

    function formatDisplay( &$rows, $pager = true ) {
        // set pager based on if any limit was applied in the query. 
        if ( $pager ) {
            $this->setPager( );
        }

        // allow building charts if any
        if ( ! empty($this->_params['charts']) && !empty($rows) ) {
            require_once 'CRM/Utils/OpenFlashChart.php';
            $this->buildChart( $rows );
            $this->assign( 'chartEnabled', true );
            $this->_chartId = "{$this->_params['charts']}_" . ($this->_id ? $this->_id : substr(get_class($this), 16)) . '_' . session_id( );
            $this->assign('chartId',  $this->_chartId);
        }
        
        // unset columns not to be displayed.
        foreach ( $this->_columnHeaders as $key => $value ) {
            if ( is_array($value) && isset($value['no_display']) ) {
                unset($this->_columnHeaders[$key]);
            }
        }

        // unset columns not to be displayed.
        if ( !empty($rows) ) {
            foreach ( $this->_noDisplay as $noDisplayField ) {
                foreach ( $rows as $rowNum => $row ) {
                    unset($this->_columnHeaders[$noDisplayField]);
                }
            }
        }

        // build array of section totals
        $this->sectionTotals( );

        // process grand-total row
        $this->grandTotal( $rows );

        // use this method for formatting rows for display purpose.
        $this->alterDisplay( $rows );

        // use this method for formatting custom rows for display purpose.
        $this->alterCustomDataDisplay( $rows );
    }

    function buildChart( &$rows ) {
        // override this method for building charts.
    }

    // select() method below has been added recently (v3.3), and many of the report templates might 
    // still be having their own select() method. We should fix them as and when encountered and move 
    // towards generalizing the select() method below. 
    function select( ) {
        $select = array( );

        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( $tableName == 'civicrm_address' ) {
                        $this->_addressField = true;
                    }
                    if ( $tableName == 'civicrm_email' ) {
                        $this->_emailField = true;
                    }
                    if ( $tableName == 'civicrm_phone' ) {
                        $this->_phoneField = true;
                    }
                    
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {

                        // 1. In many cases we want select clause to be built in slightly different way 
                        //    for a particular field of a particular type.
                        // 2. This method when used should receive params by reference and modify $this->_columnHeaders
                        //    as needed.
                        $selectClause = $this->selectClause( $tableName, 'fields', $fieldName, $field );
                        if ( $selectClause ) {
                            $select[] = $selectClause;
                            continue;
                        }

                        // include statistics columns only if set
                        if ( CRM_Utils_Array::value('statistics', $field) ) {
                            foreach ( $field['statistics'] as $stat => $label ) {
                                $alias = "{$tableName}_{$fieldName}_{$stat}";
                                switch (strtolower($stat)) {
                                case 'max':
                                case 'sum':
                                    $select[] = "$stat({$field['dbAlias']}) as $alias";
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['type']  = 
                                        $field['type'];
                                    $this->_statFields[] = $alias;
                                    $this->_selectAliases[] = $alias;
                                    break;
                                case 'count':
                                    $select[] = "COUNT({$field['dbAlias']}) as $alias";
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['type']  = 
                                        CRM_Utils_Type::T_INT;
                                    $this->_statFields[] = $alias;
                                    $this->_selectAliases[] = $alias;
                                    break;
                                case 'avg':
                                    $select[] = "ROUND(AVG({$field['dbAlias']}),2) as $alias";
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['type']  =  
                                        $field['type'];
                                    $this->_statFields[] = $alias;
                                    $this->_selectAliases[] = $alias;
                                    break;
                                }
                            }   
                        } else {
                            $alias = "{$tableName}_{$fieldName}";
                            $select[] = "{$field['dbAlias']} as $alias";
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title',$field);
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                            $this->_selectAliases[] = $alias;
                        }
                    }
                }
            }

            // select for group bys
            if ( array_key_exists('group_bys', $table) ) {
                foreach ( $table['group_bys'] as $fieldName => $field ) {

                    if ( $tableName == 'civicrm_address' ) {
                        $this->_addressField = true;
                    }
                    if ( $tableName == 'civicrm_email' ) {
                        $this->_emailField = true;
                    }
                    if ( $tableName == 'civicrm_phone' ) {
                        $this->_phoneField = true;
                    }
                    // 1. In many cases we want select clause to be built in slightly different way 
                    //    for a particular field of a particular type.
                    // 2. This method when used should receive params by reference and modify $this->_columnHeaders
                    //    as needed.
                    $selectClause = $this->selectClause( $tableName, 'group_bys', $fieldName, $field );
                    if ( $selectClause ) {
                        $select[] = $selectClause;
                        continue;
                    }

                    if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                        switch ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys_freq'] ) ) {
                        case 'YEARWEEK' :
                            $select[] = "DATE_SUB({$field['dbAlias']}, INTERVAL WEEKDAY({$field['dbAlias']}) DAY) AS {$tableName}_{$fieldName}_start";
                            $select[] = "YEARWEEK({$field['dbAlias']}) AS {$tableName}_{$fieldName}_subtotal";
                            $select[] = "WEEKOFYEAR({$field['dbAlias']}) AS {$tableName}_{$fieldName}_interval";
                            $field['title'] = 'Week';
                            break;
                            
                        case 'YEAR' :
                            $select[] = "MAKEDATE(YEAR({$field['dbAlias']}), 1)  AS {$tableName}_{$fieldName}_start";
                            $select[] = "YEAR({$field['dbAlias']}) AS {$tableName}_{$fieldName}_subtotal";
                            $select[] = "YEAR({$field['dbAlias']}) AS {$tableName}_{$fieldName}_interval";
                            $field['title'] = 'Year';
                            break;
                            
                        case 'MONTH':
                            $select[] = "DATE_SUB({$field['dbAlias']}, INTERVAL (DAYOFMONTH({$field['dbAlias']})-1) DAY) as {$tableName}_{$fieldName}_start";
                            $select[] = "MONTH({$field['dbAlias']}) AS {$tableName}_{$fieldName}_subtotal";
                            $select[] = "MONTHNAME({$field['dbAlias']}) AS {$tableName}_{$fieldName}_interval";
                            $field['title'] = 'Month';
                            break;
                            
                        case 'QUARTER':
                            $select[] = "STR_TO_DATE(CONCAT( 3 * QUARTER( {$field['dbAlias']} ) -2 , '/', '1', '/', YEAR( {$field['dbAlias']} ) ), '%m/%d/%Y') AS {$tableName}_{$fieldName}_start";
                            $select[] = "QUARTER({$field['dbAlias']}) AS {$tableName}_{$fieldName}_subtotal";
                            $select[] = "QUARTER({$field['dbAlias']}) AS {$tableName}_{$fieldName}_interval";
                            $field['title'] = 'Quarter';
                            break;
                            
                        }
                        // for graphs and charts -
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys_freq'] ) ) {
                            $this->_interval = $field['title'];
                            $this->_columnHeaders["{$tableName}_{$fieldName}_start"]['title'] = 
                                $field['title'] . ' Beginning';
                            $this->_columnHeaders["{$tableName}_{$fieldName}_start"]['type']  = 
                                $field['type'];
                            $this->_columnHeaders["{$tableName}_{$fieldName}_start"]['group_by'] = 
                                $this->_params['group_bys_freq'][$fieldName];

                            // just to make sure these values are transfered to rows.
                            // since we 'll need them for calculation purpose, 
                            // e.g making subtotals look nicer or graphs
                            $this->_columnHeaders["{$tableName}_{$fieldName}_interval"] = array('no_display' => true);
                            $this->_columnHeaders["{$tableName}_{$fieldName}_subtotal"] = array('no_display' => true);
                        }
                    }
                }
            }
        }

        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }

    function selectClause( &$tableName, $tableKey, &$fieldName, &$field ) {
        return false;
    }

    function where( ) {
        $whereClauses = $havingClauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );

                        $clause = $this->dateClause( $field['name'], $relative, $from, $to, $field['type'] );
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op ) {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        if ( CRM_Utils_Array::value( 'having', $field ) ) {
                            $havingClauses[] = $clause;
                        } else {
                            $whereClauses[] = $clause;
                        }
                    }
                }
            }
        }

        if ( empty( $whereClauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
            $this->_having = "";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $whereClauses );
        }
        
        if ( $this->_aclWhere ) {
            $this->_where .= " AND {$this->_aclWhere} ";
        }   

        if ( !empty( $havingClauses ) ) {
            // use this clause to construct group by clause.
            $this->_having = "HAVING " . implode( ' AND ', $havingClauses );
        }
    }

    function processReportMode( ) {
        $buttonName = $this->controller->getButtonName( );

        $output     = CRM_Utils_Request::retrieve( 'output',
                                                   'String', CRM_Core_DAO::$_nullObject );
        $this->_sendmail = CRM_Utils_Request::retrieve( 'sendmail', 
                                                        'Boolean', CRM_Core_DAO::$_nullObject );
        $this->_absoluteUrl = false;
        $printOnly = false;
        $this->assign( 'printOnly', false );

        if ( $this->_printButtonName == $buttonName || $output == 'print' || ($this->_sendmail && !$output) ) {
            $this->assign( 'printOnly', true );
            $printOnly = true;
            $this->assign( 'outputMode', 'print' );
            $this->_outputMode = 'print';
        } else if ( $this->_pdfButtonName   == $buttonName || $output == 'pdf' ) {
            $this->assign( 'printOnly', true );
            $printOnly = true;
            $this->assign( 'outputMode', 'pdf' );
            $this->_outputMode  = 'pdf';
            $this->_absoluteUrl = true;
        } else if ( $this->_csvButtonName   == $buttonName || $output == 'csv' ) {
            $this->assign( 'printOnly', true );
            $printOnly = true;
            $this->assign( 'outputMode', 'csv' );
            $this->_outputMode  = 'csv';
            $this->_absoluteUrl = true;
        } else if ( $this->_groupButtonName   == $buttonName || $output == 'group' ) {
            $this->assign( 'outputMode', 'group' );
            $this->_outputMode  = 'group';
        } else {
            $this->assign( 'outputMode', 'html' );
            $this->_outputMode = 'html';
        }

        // Get today's date to include in printed reports
        if ( $printOnly ) {
            require_once 'CRM/Utils/Date.php';
            $reportDate = CRM_Utils_Date::customFormat( date('Y-m-d H:i') );
            $this->assign( 'reportDate', $reportDate );
        }
    }

    function beginPostProcess( ) {
        $this->_params = $this->controller->exportValues( $this->_name );
        if ( empty( $this->_params ) &&
             $this->_force ) {
            $this->_params = $this->_formValues;
        }
        $this->_formValues = $this->_params ;
        if ( CRM_Core_Permission::check( 'administer Reports' ) &&
             isset( $this->_id ) && 
             ( $this->_instanceButtonName == $this->controller->getButtonName( ) . '_save' ||
               $this->_chartButtonName    == $this->controller->getButtonName( ) ) ) {
            $this->assign( 'updateReportButton', true );
        }
        $this->processReportMode( );
    }

    function buildQuery( $applyLimit = true ) {
        $this->select ( );
        $this->from   ( );
        $this->customDataFrom( );
        $this->where  ( );
        $this->groupBy( );
        $this->orderBy( );

        // order_by columns not selected for display need to be included in SELECT
        $unselectedSectionColumns = $this->unselectedSectionColumns();
        foreach ( $unselectedSectionColumns as $alias => $section ) {
            $this->_select .= ", {$section['dbAlias']} as {$alias}";
        }

        if ( $applyLimit && !CRM_Utils_Array::value( 'charts', $this->_params ) ) {
            $this->limit( );
        }
        $sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_groupBy} {$this->_having} {$this->_orderBy} {$this->_limit}";
        return $sql;
    }

    function groupBy( ) {
        $this->_groupBy = "";
        if ( CRM_Utils_Array::value( 'group_bys', $this->_params ) &&
             is_array($this->_params['group_bys']) &&
             !empty($this->_params['group_bys']) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( array_key_exists('group_bys', $table) ) {
                    foreach ( $table['group_bys'] as $fieldName => $field ) {
								if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                                    $this->_groupBy[] = $field['dbAlias'];
								}
                    }
                }
            }
        } 
    }

    function orderBy( ) {
        $this->_orderBy = "";
        $orderBys = array();
        $this->_sections = array();

        if ( CRM_Utils_Array::value( 'order_bys', $this->_params ) &&
            is_array($this->_params['order_bys']) &&
            !empty($this->_params['order_bys']) ) {

            // Proces order_bys in user-specified order
            foreach( $this->_params['order_bys'] as $orderBy ) {
                $orderByField = array();
                foreach ( $this->_columns as $tableName => $table ) {
                    if ( array_key_exists('order_bys', $table) ) {
                        // For DAO columns defined in $this->_columns
                        $fields = $table['order_bys'];
                    } elseif ( array_key_exists( 'extends', $table ) ) {
                        // For custom fields referenced in $this->_customGroupExtends
                        $fields = $table['fields'];
                    }
                    if ( !empty($fields) && is_array( $fields ) ) {
                        foreach ( $fields as $fieldName => $field ) {
                            if ( $fieldName == $orderBy['column'] ) {
                                $orderByField = $field;
                                $orderByField['tplField'] = "{$tableName}_{$fieldName}";
                                break 2;
                            }
                        }
                    }
                }

                if ( ! empty( $orderByField ) ) {
                    $orderBys[] = "{$orderByField['dbAlias']} {$orderBy['order']}";

                    // Record any section headers for assignment to the template
                    if ( $orderBy['section'] ) {
                        $this->_sections[$orderByField['tplField']] = $orderByField;
                    }
                }
            }
        }

        if ( ! empty( $orderBys ) ) {
            $this->_orderBy = "ORDER BY " . implode( ', ', $orderBys );
        }
        $this->assign('sections', $this->_sections);
    }

    function unselectedSectionColumns( ) {
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {

                        $selectColumns["{$tableName}_{$fieldName}"] = 1;

                    }
                }
            }
        }
        if ( is_array( $this->_sections ) && is_array( $selectColumns ) ) {
          return array_diff_key( $this->_sections, $selectColumns );
        } else {
          return array();
        }

    }

    function buildRows( $sql, &$rows ) {
        $dao  = CRM_Core_DAO::executeQuery( $sql );
        if ( ! is_array($rows) ) {
            $rows = array( );
        }

        // use this method to modify $this->_columnHeaders
        $this->modifyColumnHeaders( );

        $unselectedSectionColumns = $this->unselectedSectionColumns();

        while ( $dao->fetch( ) ) {
            $row = array( );
            foreach ( $this->_columnHeaders as $key => $value ) {
                if ( property_exists( $dao, $key ) ) {
                    $row[$key] = $dao->$key;
                }
            }

            // section headers not selected for display need to be added to row
            foreach ( $unselectedSectionColumns as $key => $values ) {
                if ( property_exists( $dao, $key ) ) {
                    $row[$key] = $dao->$key;
                }
            }

            $rows[] = $row;
        }

    }

    /**
     * When "order by" fields are marked as sections, this assigns to the template 
     * an array of total counts for each section. This data is used by the Smarty
     * plugin {sectionTotal}
     */
    function sectionTotals( ) {

        // Reports using order_bys with sections must populate $this->_selectAliases in select() method.
        if ( empty( $this->_selectAliases ) ) {
          return;
        }

        if ( ! empty( $this->_sections ) ) {
            // build the query with no LIMIT clause
            $select = str_ireplace( 'SELECT SQL_CALC_FOUND_ROWS ', 'SELECT ', $this->_select );
            $sql = "{$select} {$this->_from} {$this->_where} {$this->_groupBy} {$this->_having} {$this->_orderBy}";

            // pull section aliases out of $this->_sections
            $sectionAliases = array_keys($this->_sections);

            $ifnulls = array();
            foreach ( array_merge($sectionAliases, $this->_selectAliases) as $alias ) {
                $ifnulls[] = "ifnull($alias, '') as $alias";
            }

            /* Group (un-limited) report by all aliases and get counts. This might
             * be done more efficiently when the contents of $sql are known, ie. by
             * overriding this method in the report class.
             */
            $query = "select "
            . implode(", ", $ifnulls)
            .", count(*) as ct from ($sql) as subquery group by ".  implode(", ", $sectionAliases);

            // initialize array of total counts
            $totals = array();
            $dao  = CRM_Core_DAO::executeQuery( $query );
            while ($dao->fetch()) {

                // let $this->_alterDisplay translate any integer ids to human-readable values.
                $rows[0] = $dao->toArray();
                $this->alterDisplay($rows);
                $row = $rows[0];

                // add totals for all permutations of section values
                $values = array();
                $i = 1;
                $aliasCount = count($sectionAliases);
                foreach ($sectionAliases as $alias) {
                    $values[] = $row[$alias];
                    $key = implode(CRM_Core_DAO::VALUE_SEPARATOR, $values);
                    if ( $i == $aliasCount ) {
                        // the last alias is the lowest-level section header; use count as-is
                        $totals[$key] = $dao->ct;
                    } else {
                        // other aliases are higher level; roll count into their total
                        $totals[$key] += $dao->ct;
                    }
                }
            }
            $this->assign('sectionTotals', $totals);
        }

    }

    function modifyColumnHeaders( ) {
        // use this method to modify $this->_columnHeaders
    }

    function doTemplateAssignment( &$rows ) {
        $this->assign_by_ref( 'columnHeaders', $this->_columnHeaders );
        $this->assign_by_ref( 'rows', $rows );
        $this->assign( 'statistics',  $this->statistics( $rows ) );
    }

    // override this method to build your own statistics
    function statistics( &$rows ) {
        $statistics = array();

        $count = count($rows);
    
        if ( $this->_rollup && ($this->_rollup != '') && $this->_grandFlag ) {
            $count++;
        }

        $this->countStat  ( $statistics, $count );

        $this->groupByStat( $statistics );

        $this->filterStat ( $statistics );

        return $statistics;
    }

    function countStat( &$statistics, $count ) {
        $statistics['counts']['rowCount'] = array( 'title' => ts('Row(s) Listed'),
                                                   'value' => $count );

        if ( $this->_rowsFound && ($this->_rowsFound > $count) ) {
            $statistics['counts']['rowsFound'] = array( 'title' => ts('Total Row(s)'),
                                                        'value' => $this->_rowsFound );
        }
    }

    function groupByStat( &$statistics ) {
        if ( CRM_Utils_Array::value( 'group_bys', $this->_params ) && 
             is_array($this->_params['group_bys']) && 
             !empty($this->_params['group_bys']) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( array_key_exists('group_bys', $table) ) {
                    foreach ( $table['group_bys'] as $fieldName => $field ) {
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                            $combinations[] = $field['title'];
                        }
                    }
                }
            }
            $statistics['groups'][] = array( 'title' => ts('Grouping(s)'),
                                             'value' => implode( ' & ', $combinations ) );
        }
    }

    function filterStat( &$statistics ) {
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'type', $field ) & CRM_Utils_Type::T_DATE ) {
                        list($from, $to) = 
                            $this->getFromTo( CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params ), 
                                              CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params ),
                                              CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params ) );
                        $from = CRM_Utils_Date::customFormat( $from, null, array('d') );
                        $to   = CRM_Utils_Date::customFormat( $to,   null, array('d') );
                        
                        if ( $from || $to ) {
                            $statistics['filters'][] = 
                                array( 'title' => $field['title'],
                                       'value' => ts( "Between %1 and %2",
                                                      array( 1 => $from,
                                                             2 => $to ) ) );
                        } elseif ( in_array( $rel = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params ), 
                                            array_keys( $this->getOperationPair( CRM_Report_FORM::OP_DATE ) ) ) ) {
                            $pair = $this->getOperationPair( CRM_Report_FORM::OP_DATE );
                            $statistics['filters'][] = 
                                array( 'title' => $field['title'],
                                       'value' => $pair[$rel] );
                        }
                    } else {
                        $op    = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        $value = null;
                        if ( $op ) {
                            $pair  = $this->getOperationPair( CRM_Utils_Array::value( 'operatorType', $field ),
                                                              $fieldName );
                            $min   = CRM_Utils_Array::value( "{$fieldName}_min",  $this->_params );
                            $max   = CRM_Utils_Array::value( "{$fieldName}_max",  $this->_params );
                            $val   = CRM_Utils_Array::value( "{$fieldName}_value",$this->_params );
                            if ( in_array($op, array('bw', 'nbw')) && ($min || $max) ) {
                                $value = "{$pair[$op]} " . $min . ' and ' . $max;
                            } else if ( $op == 'nll' || $op == 'nnll' ) {
                                $value = $pair[$op];
                            } else if ( is_array($val) && (!empty($val)) ) {
                                $options = $field['options'];
                                foreach ( $val as $key => $valIds ) {
                                    if ( isset($options[$valIds]) ) {
                                        $val[$key] = $options[$valIds];
                                    }
                                }
                                $pair[$op] = (count($val) == 1) ? ( ($op == 'notin')? ts('Is Not'): ts('Is') ) : $pair[$op];
                                $val       = implode( ', ', $val );
                                $value     = "{$pair[$op]} " . $val;
                            } else if ( !is_array( $val ) && ( !empty( $val ) || $val == '0' ) && isset($field['options']) &&
                                        is_array( $field['options'] ) && !empty( $field['options'] ) ) { 
                                $value = "{$pair[$op]} " . CRM_Utils_Array::value( $val, $field['options'], $val );
                            } else if ( $val ) {
                                $value = "{$pair[$op]} " . $val;
                            }
                        }
                        if ( $value ) {
                            $statistics['filters'][] = 
                                array( 'title' => CRM_Utils_Array::value( 'title', $field ),
                                       'value' => $value );
                        }
                    }
                }
            }
        }
    }

    function endPostProcess( &$rows = null ) {
        if ( $this->_outputMode == 'print' || 
             $this->_outputMode == 'pdf'   ||
             $this->_sendmail              ) {
            $templateFile = parent::getTemplateFileName( );
            
            $url = CRM_Utils_System::url("civicrm/report/instance/{$this->_id}", 
                                         "reset=1", true);

            $content = $this->_formValues['report_header'] .
                CRM_Core_Form::$_template->fetch( $templateFile ) .      
                $this->_formValues['report_footer'] ;

            if ( $this->_sendmail ) {
                require_once 'CRM/Report/Utils/Report.php';
                $attachments = array();
                if ( $this->_outputMode == 'csv' ) {
                    $content = $this->_formValues['report_header'] .
                        '<p>' . ts('Report URL') . ": {$url}</p>" .
                        '<p>' . ts('The report is attached as a CSV file.') . '</p>' .
                        $this->_formValues['report_footer'] ;

                    require_once 'CRM/Utils/File.php';
                    $config = CRM_Core_Config::singleton();
                    $csvFilename = 'Report.csv';
                    $csvFullFilename = $config->templateCompileDir . CRM_Utils_File::makeFileName( $csvFilename );
                    $csvContent = CRM_Report_Utils_Report::makeCsv( $this, $rows );
                    file_put_contents( $csvFullFilename, $csvContent);
                    $attachments[] = array(
                        'fullPath'  => $csvFullFilename,
                        'mime_type' => 'text/csv',
                        'cleanName' => $csvFilename,
                    );
                }

                if ( CRM_Report_Utils_Report::mailReport( $content, $this->_id,
                                                          $this->_outputMode, $attachments  ) ) {
                    CRM_Core_Session::setStatus( ts("Report mail has been sent.") );
                } else {
                    CRM_Core_Session::setStatus( ts("Report mail could not be sent.") );
                }
                if ( $this->get( 'instanceId' ) ) {
                    CRM_Utils_System::civiExit( );
                } 

                CRM_Utils_System::redirect( CRM_Utils_System::url( CRM_Utils_System::currentPath(), 
                                                                   'reset=1' ) );
         
            } else if ( $this->_outputMode == 'print' ) {
                echo $content;
            } else {
                if( $chartType =  CRM_Utils_Array::value( 'charts', $this->_params ) ) {
                    $config    =& CRM_Core_Config::singleton();
                    //get chart image name
                    $chartImg  = $this->_chartId . '.png';
                    //get image url path
                    $uploadUrl  = str_replace( '/persist/contribute/', '/persist/', $config->imageUploadURL ) . 'openFlashChart/';
                    $uploadUrl .= $chartImg;
                    //get image doc path to overwrite
                    $uploadImg = str_replace( '/persist/contribute/', '/persist/', $config->imageUploadDir ) . 'openFlashChart/' . $chartImg;
                    //Load the image
                    $chart = imagecreatefrompng( $uploadUrl );
                    //convert it into formattd png
                    header('Content-type: image/png');
                    //overwrite with same image
                    imagepng($chart, $uploadImg);
                    //delete the object
                    imagedestroy($chart);
                }
                require_once 'CRM/Utils/PDF/Utils.php';                     
                CRM_Utils_PDF_Utils::html2pdf( $content, "CiviReport.pdf", false, array('orientation' => 'landscape') );
            }
            CRM_Utils_System::civiExit( );
        } else if ( $this->_outputMode == 'csv' ) {
            CRM_Report_Utils_Report::export2csv( $this, $rows );
        } else if ( $this->_outputMode == 'group' ) {
            $group = $this->_params['groups'];
            CRM_Report_Utils_Report::add2group( $this, $group );
        } else if ( $this->_instanceButtonName == $this->controller->getButtonName( ) ) {
            require_once 'CRM/Report/Form/Instance.php';
            CRM_Report_Form_Instance::postProcess( $this );
        }      
    }

    function postProcess( ) {
        // get ready with post process params
        $this->beginPostProcess( );

        // build query
        $sql = $this->buildQuery( );

        // build array of result based on column headers. This method also allows 
        // modifying column headers before using it to build result set i.e $rows.
        $this->buildRows ( $sql, $rows );

        // format result set. 
        $this->formatDisplay( $rows );

        // assign variables to templates
        $this->doTemplateAssignment( $rows );

        // do print / pdf / instance stuff if needed
        $this->endPostProcess( $rows );
    }

    function limit( $rowCount = self::ROW_COUNT_LIMIT ) {
        require_once 'CRM/Utils/Pager.php';
        // lets do the pager if in html mode
        $this->_limit = null;
        if ( $this->_outputMode == 'html' || $this->_outputMode == 'group'  ) {
            $this->_select = str_ireplace( 'SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $this->_select );

            $pageId = CRM_Utils_Request::retrieve( 'crmPID', 'Integer', CRM_Core_DAO::$_nullObject );
           
            if ( !$pageId && !empty($_POST) ) {
                if ( isset($_POST['PagerBottomButton']) && isset($_POST['crmPID_B']) ) {
                    $pageId = max( (int) @$_POST['crmPID_B'], 1 );
                } elseif(  isset($_POST['PagerTopButton']) && isset($_POST['crmPID']) ) {
                    $pageId = max( (int) @$_POST['crmPID'], 1 );
                }   
                unset( $_POST['crmPID_B'] , $_POST['crmPID'] );
            } 
            
            $pageId = $pageId ? $pageId : 1;
            $this->set( CRM_Utils_Pager::PAGE_ID, $pageId );
            $offset = ( $pageId - 1 ) * $rowCount;

            $this->_limit  = " LIMIT $offset, " . $rowCount;
        }
    }

    function setPager( $rowCount = self::ROW_COUNT_LIMIT ) {
        if ( $this->_limit && ($this->_limit != '') ) {
            require_once 'CRM/Utils/Pager.php';
            $sql    = "SELECT FOUND_ROWS();";
            $this->_rowsFound = CRM_Core_DAO::singleValueQuery( $sql );
            $params = array( 'total'        => $this->_rowsFound,
                             'rowCount'     => $rowCount,
                             'status'       => ts( 'Records' ) . ' %%StatusMessage%%',
                             'buttonBottom' => 'PagerBottomButton',
                             'buttonTop'    => 'PagerTopButton',
                             'pageID'       => $this->get( CRM_Utils_Pager::PAGE_ID ) );

            $pager = new CRM_Utils_Pager( $params );
            $this->assign_by_ref( 'pager', $pager );
        }
    }
    
    function whereGroupClause($field, $value, $op) {
         
        $smartGroupQuery = ""; 
        require_once 'CRM/Contact/DAO/Group.php';
        require_once 'CRM/Contact/BAO/SavedSearch.php';
        
        $group = new CRM_Contact_DAO_Group( );
        $group->is_active = 1;
        $group->find();
        while( $group->fetch( ) ) {
             if( in_array( $group->id, $this->_params['gid_value'] ) && $group->saved_search_id ) {
                 $smartGroups[] = $group->id;
             }
        }
        
        require_once 'CRM/Contact/BAO/GroupContactCache.php';
        CRM_Contact_BAO_GroupContactCache::check( $smartGroups );

        $smartGroupQuery = '';
        if( !empty($smartGroups) ) {   
            $smartGroups = implode( ',', $smartGroups );
            $smartGroupQuery =                                                                             
                " UNION DISTINCT 
                  SELECT DISTINCT smartgroup_contact.contact_id                                    
                  FROM civicrm_group_contact_cache smartgroup_contact        
                  WHERE smartgroup_contact.group_id IN ({$smartGroups}) ";
        }
        
        $sqlOp  = self::getSQLOperator( $op );
        if ( !is_array($value) ) {
            $value = array( $value );
        }
        $clause = "{$field['dbAlias']} IN (" . implode(', ', $value) . ")";
        
        return  " {$this->_aliases['civicrm_contact']}.id {$sqlOp} ( 
                          SELECT DISTINCT {$this->_aliases['civicrm_group']}.contact_id 
                          FROM civicrm_group_contact {$this->_aliases['civicrm_group']}
                          WHERE {$clause} AND {$this->_aliases['civicrm_group']}.status = 'Added' 
                          {$smartGroupQuery} ) ";
    }

    function whereTagClause($field, $value, $op) {
        // not using left join in query because if any contact
        // belongs to more than one tag, results duplicate
        // entries.
        $sqlOp  = self::getSQLOperator( $op );
        if ( !is_array($value) ) {
            $value = array( $value );
        }
        $clause = "{$field['dbAlias']} IN (" . implode(', ', $value) . ")";

        return  " {$this->_aliases['civicrm_contact']}.id {$sqlOp} ( 
                          SELECT DISTINCT {$this->_aliases['civicrm_tag']}.entity_id 
                          FROM civicrm_entity_tag {$this->_aliases['civicrm_tag']}
                          WHERE entity_table = 'civicrm_contact' AND {$clause} ) ";
    }

    function buildACLClause( $tableAlias = 'contact_a' ) {
        require_once 'CRM/Contact/BAO/Contact/Permission.php';
        list( $this->_aclFrom, $this->_aclWhere ) = CRM_Contact_BAO_Contact_Permission::cacheClause( $tableAlias );
    }

    function addCustomDataToColumns( $addFields = true ) {
        if ( empty($this->_customGroupExtends) ) {
            return;
        }
        if( !is_array($this->_customGroupExtends) ) {
            $this->_customGroupExtends = array( $this->_customGroupExtends );  
        }

        $sql       = "
SELECT cg.table_name, cg.title, cg.extends, cf.id as cf_id, cf.label, 
       cf.column_name, cf.data_type, cf.html_type, cf.option_group_id, cf.time_format
FROM   civicrm_custom_group cg 
INNER  JOIN civicrm_custom_field cf ON cg.id = cf.custom_group_id
WHERE cg.extends IN ('" . implode( "','", $this->_customGroupExtends ) . "') AND 
      cg.is_active = 1 AND 
      cf.is_active = 1 AND 
      cf.is_searchable = 1
ORDER BY cg.weight, cf.weight";
        $customDAO =& CRM_Core_DAO::executeQuery( $sql );
        
        $curTable  = null;
        while( $customDAO->fetch() ) {
        	if ( $customDAO->table_name != $curTable ) {
                $curTable  = $customDAO->table_name;
                $curFields = $curFilters = array( );
                
                $this->_columns[$curTable]['dao']      = 'CRM_Contact_DAO_Contact'; // dummy dao object
                $this->_columns[$curTable]['extends']  = $customDAO->extends;
                $this->_columns[$curTable]['grouping'] = $customDAO->table_name;
                $this->_columns[$curTable]['group_title'] = $customDAO->title;
            }
            $fieldName = 'custom_' . $customDAO->cf_id;

            if ( $addFields ) {
                $curFields[$fieldName] = 
                    array( 'name'     => $customDAO->column_name, // this makes aliasing work in favor
                           'title'    => $customDAO->label,
                           'dataType' => $customDAO->data_type,
                           'htmlType' => $customDAO->html_type );
            }
            if ( $this->_customGroupFilters ) {
                $curFilters[$fieldName] = 
                    array( 'name'     => $customDAO->column_name, // this makes aliasing work in favor
                           'title'    => $customDAO->label,
                           'dataType' => $customDAO->data_type,
                           'htmlType' => $customDAO->html_type );
            }

        	switch( $customDAO->data_type ) {
            case 'Date':
                // filters
                $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_DATE;
                $curFilters[$fieldName]['type']         = CRM_Utils_Type::T_DATE;
                // CRM-6946, show time part for datetime date fields
                if ( $customDAO->time_format ) {
                    $curFields[$fieldName]['type'] = CRM_Utils_Type::T_TIMESTAMP;
                }
                break;

            case 'Boolean':
                $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_SELECT;
                $curFilters[$fieldName]['options']      = 
                    array('' => ts('- select -'),
                          1  => ts('Yes'),
                          0  => ts('No'), );
                $curFilters[$fieldName]['type']         = CRM_Utils_Type::T_INT;
                break;

            case 'Int':
                $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_INT;
                $curFilters[$fieldName]['type']         = CRM_Utils_Type::T_INT;
                break;

            case 'Money':
                $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_FLOAT;
                $curFilters[$fieldName]['type']         = CRM_Utils_Type::T_MONEY;
                break;

            case 'Float':
                $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_FLOAT;
                $curFilters[$fieldName]['type']         = CRM_Utils_Type::T_FLOAT;
                break;

            case 'String':
                $curFilters[$fieldName]['type']  = CRM_Utils_Type::T_STRING;

                if ( !empty($customDAO->option_group_id) ) {
                    if ( in_array( $customDAO->html_type, array( 'Multi-Select', 'AdvMulti-Select', 'CheckBox') ) ) {
                        $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT_SEPARATOR;
                    } else {
                        $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT;
                    }
                    if( $this->_customGroupFilters ) {
                        $curFilters[$fieldName]['options'] = array( );
                        $ogDAO =& CRM_Core_DAO::executeQuery( "SELECT ov.value, ov.label FROM civicrm_option_value ov WHERE ov.option_group_id = %1 ORDER BY ov.weight", array(1 => array($customDAO->option_group_id, 'Integer')) );
                        while( $ogDAO->fetch() ) {
                            $curFilters[$fieldName]['options'][$ogDAO->value] = $ogDAO->label;
                        }
                    }
                }
                break; 

            case 'StateProvince': 
                if ( in_array( $customDAO->html_type, array( 'Multi-Select State/Province' ) ) ) {
                    $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT_SEPARATOR;
                } else {
                    $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT;
                }
                $curFilters[$fieldName]['options']      = CRM_Core_PseudoConstant::stateProvince();
                break;

            case 'Country':
                if ( in_array( $customDAO->html_type, array( 'Multi-Select Country' ) ) ) {
                    $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT_SEPARATOR;
                } else {
                    $curFilters[$fieldName]['operatorType'] = CRM_Report_Form::OP_MULTISELECT;
                }
                $curFilters[$fieldName]['options']      = CRM_Core_PseudoConstant::country();
                break;

            case 'ContactReference':
                $curFilters[$fieldName]['type']  = CRM_Utils_Type::T_STRING;
                $curFilters[$fieldName]['name']  = 'display_name';
                $curFilters[$fieldName]['alias'] = "contact_{$fieldName}_civireport";

                $curFields[$fieldName]['type']   = CRM_Utils_Type::T_STRING;
                $curFields[$fieldName]['name']   = 'display_name';  
                $curFields[$fieldName]['alias']  = "contact_{$fieldName}_civireport";
                break;

            default:
                $curFields [$fieldName]['type']  = CRM_Utils_Type::T_STRING;
                $curFilters[$fieldName]['type']  = CRM_Utils_Type::T_STRING;
        	}

            if ( ! array_key_exists('type', $curFields[$fieldName]) ) {
                $curFields[$fieldName]['type'] = $curFilters[$fieldName]['type'];
            } 

            if ( $addFields ) {
                $this->_columns[$curTable]['fields']  = $curFields;
            }
            if ( $this->_customGroupFilters ) {
                $this->_columns[$curTable]['filters'] = $curFilters;
            }
            if (  $this->_customGroupGroupBy ) {
                $this->_columns[$curTable]['group_bys'] = $curFields;
            } 
        }
    }

    function customDataFrom( ) {
        if ( empty($this->_customGroupExtends) ) {
            return;
        }
        require_once 'CRM/Core/BAO/CustomQuery.php';
        $mapper = CRM_Core_BAO_CustomQuery::$extendsMap;

        foreach( $this->_columns as $table => $prop ) {
            if (substr($table, 0, 13) == 'civicrm_value' || substr($table, 0, 12) == 'custom_value') {
                $extendsTable = $mapper[$prop['extends']];
                
                // check field is in params
                if( !$this->isFieldSelected( $prop ) ) {
                    continue;
                }
                
                $this->_from .= " 
LEFT JOIN $table {$this->_aliases[$table]} ON {$this->_aliases[$table]}.entity_id = {$this->_aliases[$extendsTable]}.id";
                // handle for ContactReference
                if ( array_key_exists( 'fields', $prop ) ) { 
                    foreach ( $prop['fields'] as $fieldName => $field ) { 
                        if ( CRM_Utils_Array::value( 'dataType', $field ) == 'ContactReference' ) {
                            require_once 'CRM/Core/BAO/CustomField.php';
                            $columnName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField', CRM_Core_BAO_CustomField::getKeyID($fieldName) , 'column_name' );
                            $this->_from .= "
LEFT JOIN civicrm_contact {$field['alias']} ON {$field['alias']}.id = {$this->_aliases[$table]}.{$columnName} ";
                        }
                    }
                }
			}
		}
    }
    
    function isFieldSelected( $prop ) {
        if( empty($prop) ) {
            return false;
        }
        require_once 'CRM/Core/BAO/CustomField.php';
        
        if ( !empty( $this->_params['fields'] ) ) {
            foreach( array_keys($prop['fields']) as $fieldAlias ) {
                $customFieldId = CRM_Core_BAO_CustomField::getKeyID( $fieldAlias );
                if ( $customFieldId ) {
                    if ( array_key_exists( $fieldAlias, $this->_params['fields'] ) ) {
                        return true;
                    }
                    
                    //might be survey response field.
                    if ( CRM_Utils_Array::value( 'survey_response', $this->_params['fields'] ) &&
                         CRM_Utils_Array::value( 'isSurveyResponseField', $prop['fields'][$fieldAlias] ) ) {
                        return true;
                    }
                }
            }
        }
        
        if ( !empty( $this->_params['group_bys'] ) && $this->_customGroupGroupBy ) {
            foreach( array_keys($prop['group_bys']) as $fieldAlias ) {
                if ( array_key_exists( $fieldAlias, $this->_params['group_bys'] ) && CRM_Core_BAO_CustomField::getKeyID($fieldAlias) ) {
                    return true;
                }
            }
        }
        
        if ( !empty($this->_params['order_bys']) ) {
            foreach( array_keys($prop['fields']) as $fieldAlias ) {
                foreach ( $this->_params['order_bys'] as $orderBy ){
                    if ( $fieldAlias == $orderBy['column'] && CRM_Core_BAO_CustomField::getKeyID($fieldAlias) ) {
                        return true;
                    }
                }
            }
        }

        if ( !empty( $prop['filters'] ) && $this->_customGroupFilters ) {
            foreach( $prop['filters'] as $fieldAlias => $val ) {
                foreach( array( 'value', 'min', 'max', 'relative' ,'from', 'to' ) as $attach ) {
                    if ( isset( $this->_params[$fieldAlias.'_'.$attach ] ) &&
                         ( !empty( $this->_params[$fieldAlias.'_'.$attach ] ) || $this->_params[$fieldAlias.'_'.$attach ] == '0' ) ) {
                        return true;
                    } 
                }
                if ( CRM_Utils_Array::value($fieldAlias.'_op', $this->_params) &&
                     in_array($this->_params[$fieldAlias.'_op'], array('nll', 'nnll')) ) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check for empty order_by configurations and remove them; also set 
     * template to hide them.
     */
    function preProcessOrderBy( &$formValues ) {
        // Object to show/hide form elements
        require_once('CRM/Core/ShowHideBlocks.php');
        $_showHide =& new CRM_Core_ShowHideBlocks( '', '' );
        
        $_showHide->addShow( 'optionField_1' );
        
        // Cycle through order_by options; skip any empty ones, and hide them as well        
        $n = 1;

        if ( ! empty($formValues['order_bys']) ) {
            foreach ( $formValues['order_bys'] as $order_by  ) {
                if ( $order_by['column'] && $order_by['column'] != '-' ) {
                    $_showHide->addShow('optionField_'. $n);
                    $orderBys[$n] = $order_by;
                    $n++;
                }
            }
        }
        for ( $i = $n; $i <= 5; $i++ ) {
            if ( $i > 1 ) {
                $_showHide->addHide('optionField_'. $i);
            }
        }
        
        // overwrite order_by options with modified values
        if ( !empty( $orderBys ) ) {
            $formValues['order_bys'] = $orderBys;
        } else {
            $formValues['order_bys'] = array( 1 => array( 'column' => '-' ) );
        }
        
        // assign show/hide data to template
        $_showHide->addToTemplate();
    }

    /**
     * Does table name have columns in SELECT clause?
     * @param string $tableName  Name of table (index of $this->_columns array)
     * @return bool
     */
    function isTableSelected($tableName) {
        return in_array($tableName, $this->selectedTables());
    }


    /**
     * Fetch array of DAO tables having columns included in SELECT or ORDER BY clause
     * (building the array if it's unset)
     *
     * @return Array $this->_selectedTables
     */
    function selectedTables() {
        if ( ! $this->_selectedTables ) {
            $orderByColumns = array();
            if ( is_array( $this->_params['order_bys']) ) {
                foreach( $this->_params['order_bys'] as $orderBy ) {
                    $orderByColumns[] = $orderBy['column'];
                }
            }

            foreach ( $this->_columns as $tableName => $table ) {
                if ( array_key_exists('fields', $table) ) {
                    foreach ( $table['fields'] as $fieldName => $field ) {
                        if ( CRM_Utils_Array::value( 'required', $field ) ||
                             CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {

                            $this->_selectedTables[] = $tableName;
                            break;

                        }
                    }
                }
                if ( array_key_exists('order_bys', $table) ) {
                    foreach ( $table['order_bys'] as $orderByName => $orderBy ) {
                        if ( in_array( $orderByName, $orderByColumns )) {
                            $this->_selectedTables[] = $tableName;
                            break;

                        }
                    }
                }
                if ( array_key_exists('filters', $table) ) {
                    foreach ( $table['filters'] as $filterName => $filter ) {
                        if ( CRM_Utils_Array::value( "{$filterName}_value", $this->_params ) ) {
                            $this->_selectedTables[] = $tableName;
                            break;

                        }
                    }
                }
            }
        }
        return $this->_selectedTables;

    }

    /*
     * function for adding address fields to construct function in reports
     * @param bool $groupBy Add GroupBy? Not appropriate for detail report
     * @param bool $orderBy Add GroupBy? Not appropriate for detail report
     * @return array address fields for construct clause
     */
    function addAddressFields($groupBy = true, $orderBy = false, $filters = true, $defaults = array('country_id' => true ) ) {     
       $addressFields = array('civicrm_address' =>
                              array( 'dao'      => 'CRM_Core_DAO_Address',
                                     'fields'   =>
                                     array( 'street_address'    => 
                                            array( 'title' => ts( 'Street Address' ),
                                                   'default' => CRM_Utils_Array::value('street_address', $defaults, false) ),
                                            'street_number'     => 
                                            array( 'name'    => 'street_number',
                                                   'title'   => ts( 'Street Number' ),
                                                   'type'    => 1,
                                                   'default' => CRM_Utils_Array::value('street_number', $defaults, false) ),
                                            'street_name'       => 
                                            array( 'name'    => 'street_name',
                                                   'title'   => ts( 'Street Name' ),
                                                   'type'    => 1,
                                                   'default' => CRM_Utils_Array::value('street_name', $defaults, false) ),
                                            'street_unit'       => 
                                            array( 'name'    => 'street_unit',
                                                   'title'   => ts( 'Street Unit' ),
                                                   'type'    => 1,
                                                   'default' => CRM_Utils_Array::value('street_unit', $defaults, false) ),
                                            'city'              => 
                                            array( 'title' => ts( 'City' ),
                                                   'default'    => CRM_Utils_Array::value('city', $defaults, false) ),
                                            'postal_code'       => 
                                            array( 'title' => ts( 'Postal Code' ),
                                                   'default'    => CRM_Utils_Array::value('postal_code', $defaults, false) ),
                                            'county_id'  =>
                                            array( 'title'      => ts( 'County' ),  
                                                   'default'    => CRM_Utils_Array::value('county_id', $defaults, false) ),
                                            'state_province_id' => 
                                            array( 'title'      => ts( 'State/Province' ),
                                                   'default'    => CRM_Utils_Array::value('state_province_id', $defaults, false) ),
                                            'country_id'        => 
                                            array( 'title'      => ts( 'Country' ),  
                                                   'default'    => CRM_Utils_Array::value('country_id', $defaults, false) ), 
                                            
                                            ),
                                     'grouping'  => 'location-fields',
                                     ),
                              );
       
       if ( $filters ) {
           $addressFields['civicrm_address' ]['filters'] = 
               array( 
                     'street_number'   => array( 'title'   => ts( 'Street Number' ),
                                                 'type'    => 1,
                                                 'name'    => 'street_number' ),
                     'street_name'     => array( 'title'    => ts( 'Street Name' ),
                                                 'name'     => 'street_name',
                                                 'operator' => 'like' ),
                     'postal_code'     => array( 'title'   => ts( 'Postal Code' ),
                                                 'type'    => 1,
                                                 'name'    => 'postal_code' ),
                     'city'            => array( 'title'   => ts( 'City' ),
                                                 'operator' => 'like',
                                                 'name'    => 'city' ),
                     'county_id' =>  array( 'name'  => 'county_id',
                                            'title' => ts( 'County' ), 
                                            'type'         => CRM_Utils_Type::T_INT,
                                            'operatorType' => 
                                            CRM_Report_Form::OP_MULTISELECT,
                                            'options'       => 
                                            CRM_Core_PseudoConstant::county( ) ) ,
                     'state_province_id' =>  array( 'name'  => 'state_province_id',
                                                    'title' => ts( 'State/Province' ), 
                                                    'type'         => CRM_Utils_Type::T_INT,
                                                    'operatorType' => 
                                                    CRM_Report_Form::OP_MULTISELECT,
                                                    'options'       => 
                                                    CRM_Core_PseudoConstant::stateProvince()), 
                     'country_id'        =>  array( 'name'         => 'country_id',
                                                    'title'        => ts( 'Country' ), 
                                                    'type'         => CRM_Utils_Type::T_INT,
                                                    'operatorType' => 
                                                                                 CRM_Report_Form::OP_MULTISELECT,
                                                    'options'       => 
                                                    CRM_Core_PseudoConstant::country( ) ) );              
       }
       
       if ( $orderBy ) {
           $addressFields['civicrm_address']['order_bys'] =
               array( 'street_name'       => array( 'title'   => ts( 'Street Name' ) ),
                      'street_number'     => array( 'title'   => 'Odd / Even Street Number' ),
                      'street_address'    => null,
                      'city'              => null,
                      'postal_code'       => null,
                      );
       }
       
       if ( $groupBy ) {
           $addressFields['civicrm_address']['group_bys'] =
               array( 'street_address'    => null,
                      'city'              => null,
                      'postal_code'       => null,
                      'state_province_id' => 
                      array( 'title'   => ts( 'State/Province' ), ),
                      'country_id'        => 
                      array( 'title'   => ts( 'Country' ), ),
                      'county_id'        => 
                      array( 'title'      => ts( 'County' ), ),
                      );
       }
       return $addressFields;
    }

    /*
     * Do AlterDisplay processing on Address Fields
     */
    function alterDisplayAddressFields(&$row,&$rows,&$rowNum,$baseUrl,$urltxt){
          $entryFound = false;
           // handle country
            if ( array_key_exists('civicrm_address_country_id', $row) ) {
                if ( $value = $row['civicrm_address_country_id'] ) {
                    $rows[$rowNum]['civicrm_address_country_id'] = 
                        CRM_Core_PseudoConstant::country( $value, false );
                    $url = CRM_Report_Utils_Report::getNextUrl( $baseUrl,
                                                                "reset=1&force=1&" . 
                                                                "country_id_op=in&country_id_value={$value}",
                                                                $this->_absoluteUrl, $this->_id );
                    $rows[$rowNum]['civicrm_address_country_id_link'] = $url;
                    $rows[$rowNum]['civicrm_address_country_id_hover'] = 
                        ts("$urltxt for this country.");
                }
                
             $entryFound = true;
            }
               if ( array_key_exists('civicrm_address_county_id', $row) ) {
                if ( $value = $row['civicrm_address_county_id'] ) {
                    $rows[$rowNum]['civicrm_address_county_id'] = 
                        CRM_Core_PseudoConstant::county( $value, false );
                    $url = CRM_Report_Utils_Report::getNextUrl( $baseUrl,
                                                                "reset=1&force=1&" . 
                                                                "county_id_op=in&county_id_value={$value}",
                                                                $this->_absoluteUrl, $this->_id );
                    $rows[$rowNum]['civicrm_address_county_id_link'] = $url;
                    $rows[$rowNum]['civicrm_address_county_id_hover'] = 
                        ts("$urltxt for this county.");
                }
                $entryFound = true;
            }
             // handle state province
            if ( array_key_exists('civicrm_address_state_province_id', $row) ) {
                if ( $value = $row['civicrm_address_state_province_id'] ) {
                    $rows[$rowNum]['civicrm_address_state_province_id'] = 
                        CRM_Core_PseudoConstant::stateProvince( $value, false );

                    $url = 
                        CRM_Report_Utils_Report::getNextUrl( $baseUrl,
                                                             "reset=1&force=1&state_province_id_op=in&state_province_id_value={$value}", 
                                                             $this->_absoluteUrl, $this->_id );
                    $rows[$rowNum]['civicrm_address_state_province_id_link']  = $url;
                    $rows[$rowNum]['civicrm_address_state_province_id_hover'] = 
                        ts("$urltxt  for this state.");
                }
                $entryFound = true;
            }
            
            return $entryFound;
    }
    /*
     * Add Address into From Table if required
     */

    /*
     *  Adjusts dates passed in to YEAR() for fiscal year.
     */
    function fiscalYearOffset( $fieldName ) {
        $config = CRM_Core_Config::singleton();
        $fy = $config->fiscalYearStart;
        if ( $this->_params['yid_op'] == 'calendar' || ($fy['d'] == 1 && $fy['M'] == 1) ) {
            return "YEAR( $fieldName )";
        }
        return "YEAR( $fieldName - INTERVAL " . ($fy['M'] - 1) . " MONTH" 
        . ($fy['d'] > 1 ? (" - INTERVAL " . ($fy['d'] - 1) . " DAY") : '') . " )";
    }

    function addAddressFromClause(){
       // include address field if address column is to be included
        if ( ( isset( $this->_addressField ) && 
               $this->_addressField ) ||
             $this->isTableSelected('civicrm_address') ) {
            $this->_from .= "
                 LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} 
                           ON ({$this->_aliases['civicrm_contact']}.id = 
                               {$this->_aliases['civicrm_address']}.contact_id) AND
                               {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        }
    }
    
}