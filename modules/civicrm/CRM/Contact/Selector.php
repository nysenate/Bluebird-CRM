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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Contact/BAO/Contact.php';
require_once 'CRM/Contact/BAO/Query.php';

/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Contact_Selector extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions- View and Edit.
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    /**
     * Properties of contact we're interested in displaying
     * @var array
     * @static
     */
    static $_properties = array('contact_id', 'contact_type', 'contact_sub_type', 
                                'sort_name', 'street_address',
                                'city', 'state_province', 'postal_code', 'country',
                                'geo_code_1', 'geo_code_2',
                                'email', 'on_hold', 'phone', 'status' );

    /**
     * formValues is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    public $_formValues;

    /**
     * The contextMenu
     *
     * @var array
     * @access protected
     */
    protected $_contextMenu;
    
    /**
     * params is the array in a value used by the search query creator
     *
     * @var array
     * @access protected
     */
    public $_params;

    /**
     * The return properties used for search
     *
     * @var array
     * @access protected
     */
    protected $_returnProperties;

    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;

    protected $_searchContext;

    protected $_query;

    /** 
     * group id 
     * 
     * @var int 
     */ 
    protected $_ufGroupID; 
    
    /**
     * the public visible fields to be shown to the user
     *
     * @var array
     * @access protected
     */
    protected $_fields;

    /**
     * Class constructor
     *
     * @param array $formValues array of form values imported
     * @param array $params     array of parameters for query
     * @param int   $action - action of search basic or advanced.
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct( $customSearchClass,
                          $formValues = null,
                          $params = null,
                          $returnProperties = null,
                          $action = CRM_Core_Action::NONE,
                          $includeContactIds = false,
                          $searchDescendentGroups = true,
                          $searchContext = 'search',
                          $contextMenu = null )
    {
        //don't build query constructor, if form is not submitted
        $force   = CRM_Utils_Request::retrieve( 'force', 'Boolean',
                                                CRM_Core_DAO::$_nullObject );
        if ( empty( $formValues) && !$force ) {
            return;
        }

        // submitted form values
        $this->_formValues       =& $formValues;
        $this->_params           =& $params;
        $this->_returnProperties =& $returnProperties;
        $this->_contextMenu      =& $contextMenu;
        $this->_context          = $searchContext;
        
        // type of selector
        $this->_action = $action;
        
        $this->_searchContext = $searchContext;

        $this->_ufGroupID = CRM_Utils_Array::value( 'uf_group_id', $this->_formValues );

        if ( $this->_ufGroupID ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            $this->_fields = CRM_Core_BAO_UFGroup::getListingFields( CRM_Core_Action::VIEW,
                                                                     CRM_Core_BAO_UFGroup::PUBLIC_VISIBILITY |
                                                                     CRM_Core_BAO_UFGroup::LISTINGS_VISIBILITY,
                                                                     false, $this->_ufGroupID );
            self::$_columnHeaders = null;

            //CRM_Core_Error::debug( 'f', $this->_fields );
            
            $this->_customFields =& CRM_Core_BAO_CustomField::getFieldsForImport( 'Individual' );

            $this->_returnProperties =& CRM_Contact_BAO_Contact::makeHierReturnProperties( $this->_fields );
            $this->_returnProperties['contact_type'] = 1;
            $this->_returnProperties['contact_sub_type'] = 1;
            $this->_returnProperties['sort_name'   ] = 1;
        }

        // rectify params to what proximity search expects if there is a value for prox_distance
        // CRM-7021
        if ( !empty( $this->_params ) ) { 
            foreach ($this->_params as $param){
                if ($param[0] == 'prox_distance'){
                    // add prox_ prefix to these
                    $param_alter = array('street_address', 'city', 'postal_code', 'state_province', 'country');

                    foreach ($this->_params as $key => $_param){
                        if (in_array($_param[0], $param_alter)){
                            $this->_params[$key][0] = 'prox_' . $_param[0];

                            // _id suffix where needed
                            if ($_param[0] == 'country' || $_param[0] == 'state_province'){
                                $this->_params[$key][0] .= '_id';

                                // flatten state_province array
                                if (is_array($this->_params[$key][2])){
                                    $this->_params[$key][2] = $this->_params[$key][2][0];
                                }
                            }
                        }
                    }
                    break;
                }
            }
        }

        $this->_query   = new CRM_Contact_BAO_Query( $this->_params,
                                                     $returnProperties, null, $includeContactIds,
                                                     false, CRM_Contact_BAO_Query::MODE_CONTACTS, false, $searchDescendentGroups );
        $this->_options =& $this->_query->_options;
    }//end of constructor


    /**
     * This method returns the links that are given for each search row.
     * currently the links added for each row are 
     * 
     * - View
     * - Edit
     *
     * @return array
     * @access public
     *
     */
    static function &links( $context = null, $contextMenu = null, $key = null )
    {
        $extraParams   = ( $key ) ? "&key={$key}" : null;
        $searchContext = ( $context ) ? "&context=$context" : null;
        
        if (!(self::$_links)) {
            self::$_links = array(
                                  CRM_Core_Action::VIEW   => array(
                                                                   'name'     => ts('View'),
                                                                   'url'      => 'civicrm/contact/view',
                                                                   'qs'       => "reset=1&cid=%%id%%{$searchContext}{$extraParams}",
                                                                   'title'    => ts('View Contact Details'),
                                                                   'ref'      => 'view-contact'
                                                                  ),
                                  CRM_Core_Action::UPDATE => array(
                                                                   'name'     => ts('Edit'),
                                                                   'url'      => 'civicrm/contact/add',
                                                                   'qs'       => "reset=1&action=update&cid=%%id%%{$searchContext}{$extraParams}",
                                                                   'title'    => ts('Edit Contact Details'),
                                                                   'ref'      => 'edit-contact'
                                                                  ),
                                  );
            
            $config = CRM_Core_Config::singleton( );
            if ( $config->mapAPIKey && $config->mapProvider) {
                self::$_links[CRM_Core_Action::MAP] = array(
                                                            'name'     => ts('Map'),
                                                            'url'      => 'civicrm/contact/map',
                                                            'qs'       => "reset=1&cid=%%id%%{$searchContext}{$extraParams}",
                                                            'title'    => ts('Map Contact'),
                                                            );
            }
            
            // Adding Context Menu Links in more action
            if ( $contextMenu ) {
                $counter = 7000;
                foreach( $contextMenu as $key => $value ) {
                    $contextVal = '&context='.$value['key'];
                    if ( $value['key'] == 'delete' ) $contextVal = $searchContext;
                    
                    $url = "civicrm/contact/view/{$value['key']}";
                    $qs  = "reset=1&action=add&cid=%%id%%{$contextVal}{$extraParams}";
                    if ( $value['key'] == 'activity' ) {
                        $qs = "action=browse&selectedChild=activity&reset=1&cid=%%id%%{$extraParams}";
                    } else if ( $value['key'] == 'email' ) {
                        $url = "civicrm/contact/view/activity";
                        $qs  = "atype=3&action=add&reset=1&cid=%%id%%{$extraParams}";
                    }
                    
                    self::$_links[$counter++]  = array(
                                                       'name'     => $value['title'],
                                                       'url'      => $url,
                                                       'qs'       => $qs,
                                                       'title'    => $value['title'],
                                                       'ref'      => $value['ref']
                                                       );
                }
            }
        }
        return self::$_links;
    } //end of function


    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['status']       = ts('Contact %%StatusMessage%%');
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }//end of function


    function &getColHeads($action = null, $output = null) {
        require_once 'CRM/Contact/BAO/Group.php';
      
        $colHeads = self::_getColumnHeaders();
       
        $colHeads[] = array('desc' => ts('Actions'), 'name' => ts('Action') );
        return $colHeads;
    }
    

    /**
     * returns the column headers as an array of tuples:
     * (name, sortName (key to the sort array))
     *
     * @param string $action the action being performed
     * @param enum   $output what should the result set include (web/email/csv)
     *
     * @return array the column headers that need to be displayed
     * @access public
     */
    function &getColumnHeaders($action = null, $output = null) 
    {

        if ( $output == CRM_Core_Selector_Controller::EXPORT ) {
            $csvHeaders = array( ts('Contact Id'), ts('Contact Type') );
            foreach ( $this->getColHeads($action, $output) as $column ) {
                if ( array_key_exists( 'name', $column ) ) {
                    $csvHeaders[] = $column['name'];
                }
            }
            return $csvHeaders;
        } else if ( $output == CRM_Core_Selector_Controller::SCREEN ) {
            $csvHeaders = array( ts('Name') );
            foreach ( $this->getColHeads($action, $output) as $column ) {
                if ( array_key_exists( 'name', $column ) &&
                     $column['name']                     &&
                     $column['name'] != ts( 'Name' ) ) {
                    $csvHeaders[] = $column['name'];
                }
            }
            return $csvHeaders;
        } else if ( $this->_ufGroupID ) {
            // we dont use the cached value of column headers
            // since it potentially changed because of the profile selected
            static $skipFields = array( 'group', 'tag' );
            $direction = CRM_Utils_Sort::ASCENDING;
            $empty = true;
            if ( ! self::$_columnHeaders ) {
                self::$_columnHeaders = array( array( 'name' => '' ),
                                               array(
                                                     'name'      => ts('Name'),
                                                     'sort'      => 'sort_name',
                                                     'direction' => CRM_Utils_Sort::ASCENDING,
                                                     )
                                               );

                require_once 'CRM/Core/PseudoConstant.php';
                $locationTypes = CRM_Core_PseudoConstant::locationType( );
                
                foreach ( $this->_fields as $name => $field ) { 
                    if ( CRM_Utils_Array::value( 'in_selector', $field ) &&
                         ! in_array( $name, $skipFields ) ) {
                        if ( strpos( $name, '-' ) !== false ) {
                            list( $fieldName, $lType, $type ) = explode( '-', $name );
                            
                            if ( $lType == 'Primary' ) {
                                $locationTypeName = 1;
                            } else {
                                $locationTypeName = $locationTypes[$lType];
                            }

                            if ( in_array( $fieldName, array( 'phone', 'im', 'email' ) ) ) {
                                if ( $type ) {
                                    $name = "`$locationTypeName-$fieldName-$type`";
                                } else {
                                    $name = "`$locationTypeName-$fieldName`";
                                }
                            } else {
                                $name = "`$locationTypeName-$fieldName`";
                            }
                        }
                        //to handle sort key for Internal contactId.CRM-2289
                        if ( $name == 'id' ) {
                            $name = 'contact_id';
                        }

                        self::$_columnHeaders[] = array( 'name'      => $field['title'],
                                                         'sort'      => $name,
                                                         'direction' => $direction );
                        $direction = CRM_Utils_Sort::DONTCARE;
                        $empty = false;
                    }
                }
                    
                // if we dont have any valid columns, dont add the implicit ones
                // this allows the template to check on emptiness of column headers
                if ( $empty ) {
                    self::$_columnHeaders = array( );
                } else {
                    self::$_columnHeaders[] = array('desc' => ts('Actions'));
                }
            }
            return self::$_columnHeaders;
        } else if ( ! empty( $this->_returnProperties ) ) { 

            self::$_columnHeaders = array( array( 'name' => '' ),
                                           array(
                                                 'name'      => ts('Name'),
                                                 'sort'      => 'sort_name',
                                                 'direction' => CRM_Utils_Sort::ASCENDING,
                                                 )
                                           );
            $properties =& self::makeProperties( $this->_returnProperties );

            foreach ( $properties as $prop ) {
                if ( $prop == 'contact_type' || $prop == 'contact_sub_type' || $prop == 'sort_name' ) {
                    continue;
                }

                if ( strpos($prop, '-') ) {
                    list ($loc, $fld, $phoneType) = explode('-', $prop);
                    $title = $this->_query->_fields[$fld]['title'];
                    if (trim($phoneType) && !is_numeric($phoneType) && strtolower($phoneType) != $fld) {
                        $title .= "-{$phoneType}";
                    }
                    $title .= " ($loc)";
                } else {
                    $title = $this->_query->_fields[$prop]['title'];
                }

                self::$_columnHeaders[] = array( 'name' => $title, 'sort' => $prop );
            }
            self::$_columnHeaders[] = array('name' => ts('Actions'));
            return self::$_columnHeaders;
        } else {
            return $this->getColHeads($action, $output);
        }
    }


    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        return $this->_query->searchQuery( 0, 0, null, true );
    }


    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     */

    function &getRows($action, $offset, $rowCount, $sort, $output = null) {
        $config = CRM_Core_Config::singleton( );

        if ( ( $output == CRM_Core_Selector_Controller::EXPORT || 
               $output == CRM_Core_Selector_Controller::SCREEN ) &&
             $this->_formValues['radio_ts'] == 'ts_sel' ) {
            $includeContactIds = true;
        } else {
            $includeContactIds = false;
        }

        // note the formvalues were given by CRM_Contact_Form_Search to us 
        // and contain the search criteria (parameters)
        // note that the default action is basic
        $result = $this->_query->searchQuery($offset, $rowCount, $sort,
                                             false, $includeContactIds );
        
        // process the result of the query
        $rows = array( );
        $permissions = array( CRM_Core_Permission::getPermission( ) );
        if ( CRM_Core_Permission::check( 'delete contacts' ) ) {
            $permissions[] = CRM_Core_Permission::DELETE;
        }
        $mask = CRM_Core_Action::mask( $permissions );

        $mapMask = $mask & 4095; // mask value to hide map link if there are not lat/long
        
        $gc = CRM_Core_SelectValues::groupContactStatus();

        if ( $this->_ufGroupID ) {
            require_once 'CRM/Core/PseudoConstant.php';
            $locationTypes = CRM_Core_PseudoConstant::locationType( );

            $names = array( );
            static $skipFields = array( 'group', 'tag' ); 
            foreach ( $this->_fields as $key => $field ) {
                if ( CRM_Utils_Array::value( 'in_selector', $field ) && 
                     ! in_array( $key, $skipFields ) ) { 
                    if ( strpos( $key, '-' ) !== false ) {
                        list( $fieldName, $id, $type ) = explode( '-', $key );

                        if ($id == 'Primary') {
                            $locationTypeName = 1;
                        } else {
                            $locationTypeName = CRM_Utils_Array::value( $id, $locationTypes );
                            if ( ! $locationTypeName ) {
                                continue;
                            }
                        }                    

                        $locationTypeName = str_replace( ' ', '_', $locationTypeName );
                        if ( in_array( $fieldName, array( 'phone', 'im', 'email' ) ) ) { 
                            if ( $type ) {
                                $names[] = "{$locationTypeName}-{$fieldName}-{$type}";
                            } else {
                                $names[] = "{$locationTypeName}-{$fieldName}";
                            }
                        } else {
                            $names[] = "{$locationTypeName}-{$fieldName}";
                        }
                    } else {
                        $names[] = $field['name'];
                    }
                }
            }
            
            $names[] =  "status";
            
        } else if ( ! empty( $this->_returnProperties ) ) {
            $names =& self::makeProperties( $this->_returnProperties );
        } else {
            $names = self::$_properties;
        }

        //hack for student data (checkboxs)
        $multipleSelectFields = array( 'preferred_communication_method' => 1 );
        if ( CRM_Core_Permission::access( 'Quest' ) ) {
            require_once 'CRM/Quest/BAO/Student.php';
            $multipleSelectFields = CRM_Quest_BAO_Student::$multipleSelectFields;
        }
        
        require_once 'CRM/Core/OptionGroup.php';
        $links =& self::links( $this->_context, $this->_contextMenu, $this->_key );
        
        //check explicitly added contact to a Smart Group.
        $groupID   = CRM_Utils_Array::key( '1', $this->_formValues['group'] );  

        while ($result->fetch()) {
            $row = array( );

            // for CRM-3157 purposes
            require_once 'CRM/Core/PseudoConstant.php';
            if (in_array('country',        $names)) $countries =& CRM_Core_PseudoConstant::country();
            if (in_array('state_province', $names)) $provinces =& CRM_Core_PseudoConstant::stateProvince();
            if (in_array('world_region',   $names)) $regions   =& CRM_Core_PseudoConstant::worldRegions();

            // the columns we are interested in
            foreach ($names as $property) {
                if ( $property == 'status' ) {
                    continue;
                }
                if ( $cfID = CRM_Core_BAO_CustomField::getKeyID($property)) {
                    $row[$property] = CRM_Core_BAO_CustomField::getDisplayValue( $result->$property, $cfID, $this->_options, $result->contact_id );
                }  else if ( $multipleSelectFields &&
                             array_key_exists($property, $multipleSelectFields ) ) {
                    //fix to display student checkboxes
                    $key = $property;
                    $paramsNew = array($key => $result->$property );

                    if ( $key == 'test_tutoring') {
                        $name = array( $key => array('newName' => $key ,'groupName' => 'test' ));
                    }  else if (substr( $key, 0, 4) == 'cmr_') { //for  readers group
                        $name = array( $key => array('newName' => $key, 'groupName' => substr($key, 0, -3) ));
                    } else {
                        $name = array( $key => array('newName' => $key ,'groupName' => $key ));
                    }
                    CRM_Core_OptionGroup::lookupValues( $paramsNew, $name, false );
                    $row[$key] = $paramsNew[$key]; 
                } else if ( isset( $tmfFields ) && $tmfFields && array_key_exists($property, $tmfFields )
                            || substr( $property, 0, 12 ) == 'participant_' ) { 
                    if ( substr($property, -3) == '_id') {
                        $key = substr($property, 0, -3);
                        $paramsNew = array($key => $result->$property );
                        $name = array( $key => array('newName' => $key ,'groupName' => $key ));
                        CRM_Core_OptionGroup::lookupValues( $paramsNew, $name, false );
                        $row[$key] = $paramsNew[$key]; 
                    } else {
                        $row[$property] = $result->$property;
                    }
                } else if ( strpos($property, '-im')) {
                    $row[$property] = $result->$property;
                    if ( !empty($result->$property) ) { 
                        $imProviders    = CRM_Core_PseudoConstant::IMProvider( );
                        $providerId     = $property."-provider_id";
                        $providerName   = $imProviders[$result->$providerId];
                        $row[$property] = $result->$property . " ({$providerName})";
                    }
                } else if ( in_array( $property, array( 'addressee', 'email_greeting', 'postal_greeting' ) ) ) {
                    $greeting = $property.'_display';
                    $row[$property] = $result->$greeting;
                } elseif ($property == 'country') {
                    $row[$property] = CRM_Utils_Array::value( $result->country_id, $countries );
                } elseif ($property == 'state_province') {
                    $row[$property] = CRM_Utils_Array::value( $result->state_province_id, $provinces );
                } elseif ($property == 'world_region') {
                    $row[$property] = $regions[$result->world_region_id];
                } else {
                    $row[$property] = $result->$property;
                }

                if ( ! empty( $result->$property ) ) {
                    $empty = false;
                }
            }

            if ( ! empty ( $result->postal_code_suffix ) ) {
                $row['postal_code'] .= "-" . $result->postal_code_suffix;
            }
            
            if ( $output != CRM_Core_Selector_Controller::EXPORT &&
                 $this->_searchContext == 'smog' ) {
                if ( empty( $result->status ) &&
                     $groupID ) {
                    $contactID = $result->contact_id;
                    if ( $contactID ) {
                        $gcParams = array('contact_id' => $contactID,
                                          'group_id'   => $groupID,
                                          );

                        $gcDefaults = array( );
                        CRM_Core_DAO::commonRetrieve( 'CRM_Contact_DAO_GroupContact', $gcParams, $gcDefaults );
                    
                        if ( empty( $gcDefaults ) ) {
                            $row['status'] = ts('Smart');
                        } else {
                            $row['status'] = $gc[$gcDefaults['status']];
                        }
                    } else {
                        $row['status'] = null;
                    }
                } else {
                    $row['status'] = $gc[$result->status];
                }
            }
            
            if ( $output != CRM_Core_Selector_Controller::EXPORT ) {
                $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->contact_id;

                if ( CRM_Utils_Array::value( 'deleted_contacts', $this->_formValues ) 
                     and CRM_Core_Permission::check('access deleted contacts') ) {
                    $row['is_deleted'] = true;
                    $links = array(
                        array(
                            'name'  => ts('View'),
                            'url'   => 'civicrm/contact/view',
                            'qs'    => 'reset=1&cid=%%id%%',
                            'title' => ts('View Contact Details'),
                        ),
                        array(
                            'name'  => ts('Restore'),
                            'url'   => 'civicrm/contact/view/delete',
                            'qs'    => 'reset=1&cid=%%id%%&restore=1',
                            'title' => ts('Restore Contact'),
                        ),
                        array(
                            'name'  => ts('Delete Permanently'),
                            'url'   => 'civicrm/contact/view/delete',
                            'qs'    => 'reset=1&cid=%%id%%&skip_undelete=1',
                            'title' => ts('Permanently Delete Contact'),
                        ),
                    );
                    $row['action'] = CRM_Core_Action::formLink($links, null, array('id' => $result->contact_id));
                } elseif ( ( is_numeric( CRM_Utils_Array::value( 'geo_code_1', $row ) ) ) ||
                     ( $config->mapGeoCoding &&
                       CRM_Utils_Array::value('city',$row) && $row['state_province'] ) ) {
                    $row['action']   = CRM_Core_Action::formLink( $links, $mask   , array( 'id' => $result->contact_id ) );
                // FIXME: guard with permission check
                } else {
                    $row['action']   = CRM_Core_Action::formLink( $links, $mapMask, array( 'id' => $result->contact_id ) );
                }

                // allow components to add more actions
                CRM_Core_Component::searchAction( $row, $result->contact_id );
                

                require_once( 'CRM/Contact/BAO/Contact/Utils.php' );
                $row['contact_type' ] = 
                    CRM_Contact_BAO_Contact_Utils::getImage( $result->contact_sub_type ? 
                                                             $result->contact_sub_type : $result->contact_type,
                                                             false,
                                                             $result->contact_id );

                $row['contact_id'  ] = $result->contact_id;
                $row['sort_name'   ] = $result->sort_name;
                if ( array_key_exists('id', $row) ) {
                    $row['id'  ] = $result->contact_id;
                }
            }
            // Dedupe contacts        
            if ( ! $empty ) {
                $duplicate = false;
                foreach( $rows as $checkRow ) {
                    if ( $checkRow['contact_id'] == $row['contact_id'] ) {
                        $duplicate = true;
                    }
                }
                if ( ! $duplicate ) {
                    $rows[] = $row;
                }
            }
        }
        //CRM_Core_Error::debug( '$rows', $rows );
        return $rows;
    }
   
    /**
     * Given the current formValues, gets the query in local
     * language
     *
     * @param  array(reference)   $formValues   submitted formValues
     *
     * @return array              $qill         which contains an array of strings
     * @access public
     */
  
    // the current internationalisation is bad, but should more or less work
    // for most of "European" languages
    public function getQILL( )
    {
        return $this->_query->qill( );
    }

    /**
     * name of export file.
     *
     * @param string $output type of output
     * @return string name of the file
     */
    function getExportFileName( $output = 'csv') {
        return ts('CiviCRM Contact Search');
    }

    /**
     * get colunmn headers for search selector
     *
     *
     * @return array $_columnHeaders
     * @access private
     */
    private static function &_getColumnHeaders() 
    {
        if ( ! isset( self::$_columnHeaders ) )
        { self::$_columnHeaders = array(
                                          array('desc' => ts('Contact Type') ),
                                          array(
                                                'name'      => ts('Name'),
                                                'sort'      => 'sort_name',
                                                'direction' => CRM_Utils_Sort::ASCENDING,
                                                ),
                                          array('name' => ts('Address') ),
                                          array(
                                                'name'      => ts('City'),
                                                'sort'      => 'city',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('State'),
                                                'sort'      => 'state_province',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Postal'),
                                                'sort'      => 'postal_code',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Country'),
                                                'sort'      => 'country',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Email'),
                                                'sort'      => 'email',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name' => ts('Phone') )
                );
        }
        return self::$_columnHeaders;
    }
    
    function &getQuery( ) {
        return $this->_query;
    }

    function alphabetQuery( ) {
        return $this->_query->searchQuery( null, null, null, false, false, true );
    }

    function contactIDQuery( $params, $action, $sortID ) {
        $sortOrder =& $this->getSortOrder( $this->_action );
        $sort      = new CRM_Utils_Sort( $sortOrder, $sortID );

        $query = new CRM_Contact_BAO_Query( $params, $this->_returnProperties );
        $value =  $query->searchQuery( 0, 0, $sort,
                                       false, false, false,
                                       false, false );
        return $value;
    }

    function &makeProperties( &$returnProperties ) {
        $properties = array( );
        foreach ( $returnProperties as $name => $value ) {
            if ( $name != 'location' ) {
                $properties[] = $name;
            } else {
                // extract all the location stuff
                foreach ( $value as $n => $v ) {
                    foreach ( $v as $n1 => $v1 ) {
                        if ( ! strpos( '_id', $n1 ) && $n1 != 'location_type' ) {
                            $properties[] = "{$n}-{$n1}";
                        }
                    }
                }
            }
        }
        return $properties;
    }

}//end of class


