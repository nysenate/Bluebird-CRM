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

/**
 * Files required
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Session.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Core/BAO/Tag.php'; 

require_once 'CRM/Utils/PagerAToZ.php';

require_once 'CRM/Contact/Selector/Controller.php';
require_once 'CRM/Contact/Selector.php';
require_once 'CRM/Contact/Task.php';
require_once 'CRM/Contact/BAO/SavedSearch.php';

/**
 * Base Search / View form for *all* listing of multiple 
 * contacts
 */
class CRM_Contact_Form_Search extends CRM_Core_Form {
    /*
     * list of valid contexts
     *
     * @var array
     * @static
     */
    static $_validContext = null;

    /**
     * The context that we are working on
     *
     * @var string
     * @access protected
     */
    protected $_context;
    
    /**
     * The contextMenu
     *
     * @var array
     * @access protected
     */
    protected $_contextMenu;
    
    /**
     * the groupId retrieved from the GET vars
     *
     * @var int
     * @access public
     */
    public $_groupID;

    /**
     * the Group ID belonging to Add Member to group ID
     * retrieved from the GET vars
     *
     * @var int
     * @access protected
     */
    protected $_amtgID;

    /**
     * the saved search ID retrieved from the GET vars
     *
     * @var int
     * @access protected
     */
    protected $_ssID;

    /**
     * Are we forced to run a search
     *
     * @var int
     * @access protected
     */
    protected $_force;

    /**
     * name of search button
     *
     * @var string
     * @access protected
     */
    protected $_searchButtonName;

    /**
     * name of print button
     *
     * @var string
     * @access protected
     */
    protected $_printButtonName;
    
    /**
     * name of action button
     *
     * @var string
     * @access protected
     */
    protected $_actionButtonName;

    /**
     * the group elements
     *
     * @var array
     * @access public
     */
    public $_group;
    public $_groupElement;
    public $_groupIterator;

    /**
     * the tag elements
     *
     * @var array 
     * @access protected
     */
    public $_tag;
    public $_tagElement;

    /**
     * form values that we will be using
     *
     * @var array
     * @access protected
     */
    protected $_formValues;

    /**
     * The params used for search
     *
     * @var array
     * @access protected
     */
    protected $_params;

    /**
     * The return properties used for search
     *
     * @var array
     * @access protected
     */
    protected $_returnProperties;

    /**
     * The sort by character
     * 
     * @var string
     * @access protected
     */
    protected $_sortByCharacter;

    /**
     * The profile group id used for display
     *
     * @var integer
     * @access protected
     */
    protected $_ufGroupID;

    /*
     * csv - common search values
     *
     * @var array
     * @access protected
     * @static
     */
    static $csv = array('contact_type', 'group', 'tag');

    /**
     * have we already done this search
     *
     * @access protected
     * @var boolean
     */
    protected $_done;

    /**
     * name of the selector to use
     */
    protected $_selectorName      = 'CRM_Contact_Selector';
    protected $_customSearchID    = null;
    protected $_customSearchClass = null;

    /**
     * define the set of valid contexts that the search form operates on
     *
     * @return array the valid context set and the titles
     * @access protected
     * @static
     */
    static function &validContext()
    {
        if (!(self::$_validContext)) {
            self::$_validContext = array( 'smog'     => 'Show members of group',
                                          'amtg'     => 'Add members to group',
                                          'basic'    => 'Basic Search',
                                          'search'   => 'Search',
                                          'builder'  => 'Search Builder',
                                          'advanced' => 'Advanced Search',
                                          'custom'   => 'Custom Search' );
        }
        return self::$_validContext;
    }
    
    static function isSearchContext( $context ) 
    {
        $searchContext = CRM_Utils_Array::value( $context, self::validContext( ) );
        return $searchContext ? true : false;
    }
    
    /**
     * Build the common elements between the search/advanced form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( )
    {
        $permission = CRM_Core_Permission::getPermission( );

        // some tasks.. what do we want to do with the selected contacts ?
        $tasks = array( '' => ts('- actions -') ) + 
            CRM_Contact_Task::permissionedTaskTitles( $permission, 
                                                      CRM_Utils_Array::value( 'deleted_contacts', $this->_formValues ) );
        
        if ( isset( $this->_ssID ) ) {
            if ( $permission == CRM_Core_Permission::EDIT ) {
                $tasks = $tasks + CRM_Contact_Task::optionalTaskTitle();
            }

            $savedSearchValues = array( 'id' => $this->_ssID,
                                        'name' => CRM_Contact_BAO_SavedSearch::getName( $this->_ssID, 'title' ) );
            $this->assign_by_ref( 'savedSearch', $savedSearchValues );
            $this->assign( 'ssID', $this->_ssID );
        }

        if ( $this->_context === 'smog' ) {
            // need to figure out how to freeze a bunch of checkboxes, hack for now
            if ( $this->_action != CRM_Core_Action::ADVANCED ) {
                //Fix ME
                //$this->_groupElement->freeze( );
            }
            
            // also set the group title
            $groupValues = array( 'id' => $this->_groupID, 'title' => $this->_group[$this->_groupID] );
            $this->assign_by_ref( 'group', $groupValues );

            // also set ssID if this is a saved search
            $ssID = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group', $this->_groupID, 'saved_search_id' );
            $this->assign( 'ssID', $ssID );
            
            //get the saved search mapping id
            if ( $ssID ) {
                $ssMappingId = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_SavedSearch', $ssID, 'mapping_id' );
            }
            
            if (isset ( $ssMappingId ) ) {
                $this->assign( 'ssMappingID', $ssMappingId );
            }
            $group_contact_status = array();
            foreach(CRM_Core_SelectValues::groupContactStatus() as $k => $v) {
                if (! empty($k)) {
                    $group_contact_status[] =
                        HTML_QuickForm::createElement('checkbox', $k, null, $v);
                }
            }
            $this->addGroup( $group_contact_status,
                             'group_contact_status', ts( 'Group Status' ) );
            
            /* 
             * commented out to fix CRM-4268
             *
             * $this->addGroupRule( 'group_contact_status',
             *                  ts( 'Please select at least Group Status value.' ), 'required', null, 1 );
            */

            // Set dynamic page title for 'Show Members of Group'
            CRM_Utils_System::setTitle( ts( 'Contacts in Group: %1', array( 1 => $this->_group[$this->_groupID] ) ) );

            // check if user has permission to edit members of this group
            require_once 'CRM/Contact/BAO/Group.php';
            $permission = CRM_Contact_BAO_Group::checkPermission( $this->_groupID, $this->_group[$this->_groupID] );
            if ( $permission && in_array(CRM_Core_Permission::EDIT, $permission) ) {
                $this->assign( 'permissionedForGroup', true );
            } else {
                $this->assign( 'permissionedForGroup', false );
            }
        }
        
        /*
         * add the go button for the action form, note it is of type 'next' rather than of type 'submit'
         *
         */
        if ( $this->_context === 'amtg' ) {
            // Set dynamic page title for 'Add Members Group'
            CRM_Utils_System::setTitle( ts('Add to Group: %1', array(1 => $this->_group[$this->_amtgID])) );
            // also set the group title and freeze the action task with Add Members to Group
            $groupValues = array( 'id' => $this->_amtgID, 'title' => $this->_group[$this->_amtgID] );
            $this->assign_by_ref( 'group', $groupValues );
            $this->add('submit', $this->_actionButtonName, ts('Add Contacts to %1', array(1 => $this->_group[$this->_amtgID])),
                       array( 'class' => 'form-submit',
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 1);" ) );
            $this->add('hidden','task', CRM_Contact_Task::GROUP_CONTACTS );

        } else {
            $this->add('select', 'task'   , ts('Actions:') . ' '    , $tasks    );
            $this->add('submit', $this->_actionButtonName, ts('Go'),
                       array( 'class'   => 'form-submit',
                              'id'      => 'Go',
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 0);" ) );
        }
        
        // need to perform tasks on all or selected items ? using radio_ts(task selection) for it
        $selectedRowsRadio = $this->addElement('radio', 'radio_ts', null, '', 'ts_sel', array( 'checked' => 'checked',
                                                                          'onclick' => 'toggleTaskAction( true );') );
        $this->assign('ts_sel_id', $selectedRowsRadio->_attributes['id']);
        
        $allRowsRadio = $this->addElement('radio', 'radio_ts', null, '', 'ts_all', array( 'onclick' => $this->getName().".toggleSelect.checked = false; toggleCheckboxVals('mark_x_', this);toggleTaskAction( true );" ) );
        $this->assign('ts_all_id', $allRowsRadio->_attributes['id']);

        /*
         * add form checkboxes for each row. This is needed out here to conform to QF protocol
         * of all elements being declared in builQuickForm
         */
        $rows = $this->get( 'rows' );
        if ( is_array( $rows ) ) {
            $this->addElement( 'checkbox', 'toggleSelect', null, null, array( 'onclick' => "toggleTaskAction( true ); return toggleCheckboxVals('mark_x_',this);" ) );
            foreach ($rows as $row) {
                $this->addElement( 'checkbox', $row['checkbox'],
                                   null, null,
                                   array( 'onclick' => "toggleTaskAction( true ); return checkSelectedBox('" . $row['checkbox'] . "', '" . $this->getName() . "');" ) );
            }
        }

        // add buttons
        $this->addButtons( array(
                                 array ( 'type'      => 'refresh',
                                         'name'      => ts('Search') ,
                                         'isDefault' => true     )
                                 )        
                           );

        $this->add('submit', $this->_printButtonName, ts('Print'),
                   array( 'class'   => 'form-submit',
                          'id'      => 'Print',  
                          'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 1);" ) );
        
        $this->setDefaultAction( 'refresh' );

    }
    
    /**
     * processing needed for buildForm and later
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        /**
         * set the varios class variables
         */
        $this->_group           =& CRM_Core_PseudoConstant::group( );
        $this->_groupIterator   =& CRM_Core_PseudoConstant::groupIterator( );
        $this->_tag             =  CRM_Core_BAO_Tag::getTags( );
        $this->_done            =  false;

        /**
         * set the button names
         */
        $this->_searchButtonName = $this->getButtonName( 'refresh' );
        $this->_printButtonName  = $this->getButtonName( 'next'   , 'print' );
        $this->_actionButtonName = $this->getButtonName( 'next'   , 'action' );
        
        /*
         * we allow the controller to set force/reset externally, useful when we are being
         * driven by the wizard framework
         */
        $this->_reset   = CRM_Utils_Request::retrieve( 'reset', 'Boolean',
                                                       CRM_Core_DAO::$_nullObject );

        $this->_force   = CRM_Utils_Request::retrieve( 'force', 'Boolean',
                                                       CRM_Core_DAO::$_nullObject );

        $this->_groupID         = CRM_Utils_Request::retrieve( 'gid'            , 'Positive',
                                                               $this );
        $this->_amtgID          = CRM_Utils_Request::retrieve( 'amtgID'         , 'Positive',
                                                               $this );
        $this->_ssID            = CRM_Utils_Request::retrieve( 'ssID'           , 'Positive',
                                                               $this );
        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter', 'String'  ,
                                                               $this );
        $this->_ufGroupID       = CRM_Utils_Request::retrieve( 'id'             , 'Positive',
                                                               $this );
        
        // reset from session, CRM-3526 
        $session = CRM_Core_Session::singleton();
        if ( $this->_force && $session->get( 'selectedSearchContactIds' ) ) {
            $session->resetScope( 'selectedSearchContactIds' );
        }
        
        // if we dont get this from the url, use default if one exsts
        $config = CRM_Core_Config::singleton( );
        if ( $this->_ufGroupID == null &&
             $config->defaultSearchProfileID != null ) {
            $this->_ufGroupID = $config->defaultSearchProfileID;
        }

        /*
         * assign context to drive the template display, make sure context is valid
         */
        $this->_context = CRM_Utils_Request::retrieve( 'context', 'String', $this, false, 'search' );
        if ( ! CRM_Utils_Array::value( $this->_context, self::validContext( ) ) ) {
            $this->_context = 'search';
        }
        $this->set( 'context', $this->_context );
        $this->assign( 'context', $this->_context );
        
        $this->set( 'selectorName', $this->_selectorName );

        // get user submitted values 
        // get it from controller only if form has been submitted, else preProcess has set this
        // $this->controller->isModal( ) returns true if page is
        // valid, i.e all the validations are true

        if ( ! empty( $_POST ) && !$this->controller->isModal( ) ) {
            $this->_formValues = $this->controller->exportValues($this->_name); 
            $this->normalizeFormValues( );
            $this->_params =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
            $this->_returnProperties =& $this->returnProperties( );

            // also get the uf group id directly from the post value
            $this->_ufGroupID = CRM_Utils_Array::value( 'uf_group_id', $_POST, $this->_ufGroupID );
            $this->_formValues['uf_group_id'] = $this->_ufGroupID;
            $this->set( 'id', $this->_ufGroupID );
        } else {
            $this->_formValues = $this->get( 'formValues' );
            $this->_params =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
            $this->_returnProperties =& $this->returnProperties( );
        }

        if ( empty( $this->_formValues ) ) {
            //check if group is a smart group (fix for CRM-1255)
            if ($this->_groupID) {
                if ($ssId = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group', $this->_groupID, 'saved_search_id' ) ) {
                    $this->_ssID = $ssId;
                }
            }

            // fix for CRM-1907
            if ( isset( $this->_ssID ) && $this->_context != 'smog') {
                // we only retrieve the saved search values if out current values are null
                $this->_formValues = CRM_Contact_BAO_SavedSearch::getFormValues( $this->_ssID );

                //fix for CRM-1505
                if ( CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_SavedSearch', $this->_ssID, 'mapping_id' ) ) {
                    $this->_params =& CRM_Contact_BAO_SavedSearch::getSearchParams( $this->_ssID );
                } else {
                    $this->_params =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
                }
                $this->_returnProperties =& $this->returnProperties( );
            } else if ( isset( $this->_ufGroupID ) ) {
                // also set the uf group id if not already present
                $this->_formValues['uf_group_id'] = $this->_ufGroupID;
            }
        }
        $this->assign( 'id', CRM_Utils_Array::value( 'uf_group_id', $this->_formValues ) );
        
        // show the context menu only when we’re not searching for deleted contacts; CRM-5673
        if ( !CRM_Utils_Array::value( 'deleted_contacts', $this->_formValues ) ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $menuItems = CRM_Contact_BAO_Contact::contextMenu( );
            $primaryActions     = CRM_Utils_Array::value( 'primaryActions', $menuItems, array( ) ); 
            $this->_contextMenu = CRM_Utils_Array::value( 'moreActions',    $menuItems, array( ) );
            $this->assign( 'contextMenu', $primaryActions + $this->_contextMenu );
        }
        
        // CRM_Core_Error::debug( 'f', $this->_formValues );
        // CRM_Core_Error::debug( 'p', $this->_params );
        eval( '$selector = new ' . $this->_selectorName . 
              '( $this->_customSearchClass,
                 $this->_formValues,
                 $this->_params,
                 $this->_returnProperties,
                 $this->_action,
                 false, true,
                 $this->_context );' );
        $controller = new CRM_Contact_Selector_Controller($selector ,
                                                           $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                           $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                           CRM_Core_Action::VIEW,
                                                           $this,
                                                           CRM_Core_Selector_Controller::TRANSFER );
        $controller->setEmbedded( true );

        if ( $this->_force ) {

            $this->postProcess( );
            /*
             * Note that we repeat this, since the search creates and stores
             * values that potentially change the controller behavior. i.e. things
             * like totalCount etc
             */
            $sortID = null;
            if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) {
                $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                       $this->get( CRM_Utils_Sort::SORT_DIRECTION ) );
            }
            $controller = new CRM_Contact_Selector_Controller($selector ,
                                                               $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                               $sortID,
                                                               CRM_Core_Action::VIEW, $this, CRM_Core_Selector_Controller::TRANSFER );
            $controller->setEmbedded( true );
        }
        
        $controller->moveFromSessionToTemplate();
    }

    function &getFormValues( ) {
        return $this->_formValues;
    }

    /**
     * Common post processing
     *
     * @return void
     * @access public
     */
    function postProcess( ) {
        /*
         * sometime we do a postProcess early on, so we dont need to repeat it
         * this will most likely introduce some more bugs :(
         */
        if ( $this->_done ) {
            return;
        }
        $this->_done = true;
        
        //get the button name
        $buttonName = $this->controller->getButtonName( );

        if ( isset( $this->_ufGroupID ) && ! CRM_Utils_Array::value( 'uf_group_id', $this->_formValues ) ) { 
            $this->_formValues['uf_group_id'] = $this->_ufGroupID;
        }

        if (!CRM_Core_Permission::check('access deleted contacts')) {
            unset($this->_formValues['deleted_contacts']);
        }
        
        $this->set( 'type'            , $this->_action );
        $this->set( 'formValues'      , $this->_formValues );
        $this->set( 'queryParams'     , $this->_params );
        $this->set( 'returnProperties', $this->_returnProperties );
        
        if ( $buttonName == $this->_actionButtonName || $buttonName == $this->_printButtonName ) {
            // check actionName and if next, then do not repeat a search, since we are going to the next page

            // hack, make sure we reset the task values
            $stateMachine =& $this->controller->getStateMachine( );
            $formName     =  $stateMachine->getTaskFormName( );
            $this->controller->resetPage( $formName );
            return;
        } else {
            $output = CRM_Core_Selector_Controller::SESSION;
            
            // create the selector, controller and run - store results in session
            $searchChildGroups = true;
            if ( $this->get( 'isAdvanced' ) ) {
                $searchChildGroups = false;
            }
            eval( '$selector = new ' . $this->_selectorName . 
                  '( $this->_customSearchClass,
                     $this->_formValues,
                     $this->_params,
                     $this->_returnProperties,
                     $this->_action,
                     false,
                     $searchChildGroups,
                     $this->_context,
                     $this->_contextMenu );' );

            $selector->setKey( $this->controller->_key );
            
            // added the sorting  character to the form array
            // lets recompute the aToZ bar without the sortByCharacter
            // we need this in most cases except when just pager or sort values change, which
            // we'll ignore for now
            $config = CRM_Core_Config::singleton( );
            if ( $config->includeAlphabeticalPager ) {
                if ($this->_reset || !$this->_sortByCharacter) {
                    $aToZBar = CRM_Utils_PagerAToZ::getAToZBar( $selector, $this->_sortByCharacter );
                    $this->set( 'AToZBar', $aToZBar );
                }
            }

            $sortID = null;
            if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) {
                $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ),
                                                       $this->get( CRM_Utils_Sort::SORT_DIRECTION ) );
            }
            $controller = new CRM_Contact_Selector_Controller($selector ,
                                                               $this->get( CRM_Utils_Pager::PAGE_ID ),
                                                               $sortID,
                                                               CRM_Core_Action::VIEW,
                                                               $this,
                                                               $output );
            $controller->setEmbedded( true );
            $controller->run();
        }
    }

    public function &returnProperties( ) {
        return CRM_Core_DAO::$_nullObject;
    }

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) {
        return ts('Search');
    }

}


