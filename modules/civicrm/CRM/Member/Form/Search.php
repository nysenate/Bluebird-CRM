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
 * Files required
 */
require_once 'CRM/Member/Selector/Search.php';
require_once 'CRM/Core/Selector/Controller.php';


/**
 * This file is for civimember search
 */
class CRM_Member_Form_Search extends CRM_Core_Form 
{
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
     * form values that we will be using 
     * 
     * @var array 
     * @access protected 
     */ 
    protected $_formValues; 

    /**
     * the params that are sent to the query
     * 
     * @var array 
     * @access protected 
     */ 
    protected $_queryParams;

    /** 
     * have we already done this search 
     * 
     * @access protected 
     * @var boolean 
     */ 
    protected $_done; 

    /**
     * are we restricting ourselves to a single contact
     *
     * @access protected  
     * @var boolean  
     */  
    protected $_single = false;

    /** 
     * are we restricting ourselves to a single contact 
     * 
     * @access protected   
     * @var boolean   
     */   
    protected $_limit = null;

    /** 
     * what context are we being invoked from 
     *    
     * @access protected      
     * @var string 
     */      
    protected $_context = null; 

    protected $_defaults;

    /** 
     * prefix for the controller
     * 
     */
    protected $_prefix = "member_";

    /** 
     * processing needed for buildForm and later 
     * 
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    { 
        $this->set( 'searchFormName', 'Search' );

        /** 
         * set the button names 
         */ 
        $this->_searchButtonName = $this->getButtonName( 'refresh' ); 
        $this->_printButtonName  = $this->getButtonName( 'next'   , 'print' ); 
        $this->_actionButtonName = $this->getButtonName( 'next'   , 'action' ); 

        $this->_done = false;

        $this->defaults = array( );

        /* 
         * we allow the controller to set force/reset externally, useful when we are being 
         * driven by the wizard framework 
         */ 
        $this->_reset   = CRM_Utils_Request::retrieve( 'reset', 'Boolean',  CRM_Core_DAO::$_nullObject ); 
        $this->_force   = CRM_Utils_Request::retrieve( 'force', 'Boolean',  $this, false ); 
        $this->_limit   = CRM_Utils_Request::retrieve( 'limit', 'Positive', $this );
        $this->_context = CRM_Utils_Request::retrieve( 'context', 'String', $this, false, 'search' );

        $this->assign( "context", $this->_context );
            
        // get user submitted values  
        // get it from controller only if form has been submitted, else preProcess has set this  
        if ( ! empty( $_POST ) ) { 
            $this->_formValues = $this->controller->exportValues( $this->_name );  
        } else {
            $this->_formValues = $this->get( 'formValues' ); 
        } 

        if ( $this->_force ) { 
            $this->postProcess( );
            $this->set( 'force', 0 );
        }

        $sortID = null; 
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) { 
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ), 
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) ); 
        } 

        require_once 'CRM/Contact/BAO/Query.php';
        $this->_queryParams =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues ); 
        $selector = new CRM_Member_Selector_Search( $this->_queryParams,
                                                    $this->_action,
                                                    null,
                                                    $this->_single,
                                                    $this->_limit,
                                                    $this->_context ); 
        $prefix = null;
        if ( $this->_context == 'basic' ) {
            $prefix = $this->_prefix;
        }

        $this->assign( "{$prefix}limit", $this->_limit );
        $this->assign( "{$prefix}single", $this->_single );

        $controller = new CRM_Core_Selector_Controller($selector ,  
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ),  
                                                        $sortID,  
                                                        CRM_Core_Action::VIEW, 
                                                        $this, 
                                                        CRM_Core_Selector_Controller::TRANSFER,
                                                        $prefix);
        
        $controller->setEmbedded( true ); 
        $controller->moveFromSessionToTemplate(); 
        
        $this->assign( 'summary', $this->get( 'summary' ) );
    }

    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $this->addElement('text', 'sort_name', ts('Member Name or Email'), CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'sort_name') );

        require_once 'CRM/Member/BAO/Query.php';
        CRM_Member_BAO_Query::buildSearchForm( $this );

        /* 
         * add form checkboxes for each row. This is needed out here to conform to QF protocol 
         * of all elements being declared in builQuickForm 
         */ 
        $rows = $this->get( 'rows' ); 
        if ( is_array( $rows ) ) {
            if ( !$this->_single ) {
                $this->addElement( 'checkbox', 'toggleSelect', null, null, array( 'onclick' => "toggleTaskAction( true ); return toggleCheckboxVals('mark_x_',this);" ) ); 
                foreach ($rows as $row) { 
                    $this->addElement( 'checkbox', $row['checkbox'], 
                                       null, null, 
                                       array( 'onclick' => "toggleTaskAction( true ); return checkSelectedBox('" . $row['checkbox'] . "');" )
                                       ); 
                }
            }

            $total = $cancel = 0;

            require_once "CRM/Core/Permission.php";
            $permission = CRM_Core_Permission::getPermission( );
            
            require_once 'CRM/Member/Task.php';
            $tasks = array( '' => ts('- actions -') ) + CRM_Member_Task::permissionedTaskTitles( $permission );
            $this->add('select', 'task'   , ts('Actions:') . ' '    , $tasks    ); 
            $this->add('submit', $this->_actionButtonName, ts('Go'), 
                       array( 'class'   => 'form-submit',
                              'id'      => 'Go',
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 0);" ) ); 

            $this->add('submit', $this->_printButtonName, ts('Print'), 
                       array( 'class' => 'form-submit', 
                              'onclick' => "return checkPerformAction('mark_x', '".$this->getName()."', 1);" ) ); 

            
            // need to perform tasks on all or selected items ? using radio_ts(task selection) for it 
            $this->addElement('radio', 'radio_ts', null, '', 'ts_sel', array( 'checked' => 'checked') ); 
            $this->addElement('radio', 'radio_ts', null, '', 'ts_all', array( 'onclick' => $this->getName().".toggleSelect.checked = false; toggleCheckboxVals('mark_x_',this); toggleTaskAction( true );" ) );
        }
        

        // add buttons 
        $this->addButtons( array( 
                                 array ( 'type'      => 'refresh', 
                                         'name'      => ts('Search') , 
                                         'isDefault' => true     ) 
                                 )    );     
    }

    /**
     * The post processing of the form gets done here.
     *
     * Key things done during post processing are
     *      - check for reset or next request. if present, skip post procesing.
     *      - now check if user requested running a saved search, if so, then
     *        the form values associated with the saved search are used for searching.
     *      - if user has done a submit with new values the regular post submissing is 
     *        done.
     * The processing consists of using a Selector / Controller framework for getting the
     * search results.
     *
     * @param
     *
     * @return void 
     * @access public
     */
    function postProcess() 
    {
        if ( $this->_done ) {
            return;
        }

        $this->_done = true;

        $this->_formValues = $this->controller->exportValues($this->_name);
             
        $this->fixFormValues( );

        // we don't show test memberships in Contact Summary / User Dashboard
        // in Search mode by default we hide test memberships
        if ( ! CRM_Utils_Array::value( 'member_test',
                                       $this->_formValues ) ) {
            $this->_formValues["member_test"] = 0;
        }
        
        require_once 'CRM/Core/BAO/CustomValue.php';
        CRM_Core_BAO_CustomValue::fixFieldValueOfTypeMemo( $this->_formValues );

        require_once 'CRM/Contact/BAO/Query.php';
        $this->_queryParams =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues ); 

        $this->set( 'formValues' , $this->_formValues  );
        $this->set( 'queryParams', $this->_queryParams );

        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->_actionButtonName || $buttonName == $this->_printButtonName ) { 
            // check actionName and if next, then do not repeat a search, since we are going to the next page 
 
            // hack, make sure we reset the task values 
            $stateMachine =& $this->controller->getStateMachine( ); 
            $formName     =  $stateMachine->getTaskFormName( ); 
            $this->controller->resetPage( $formName ); 
            return; 
        }

        $sortID = null; 
        if ( $this->get( CRM_Utils_Sort::SORT_ID  ) ) { 
            $sortID = CRM_Utils_Sort::sortIDValue( $this->get( CRM_Utils_Sort::SORT_ID  ), 
                                                   $this->get( CRM_Utils_Sort::SORT_DIRECTION ) ); 
        } 

        require_once 'CRM/Contact/BAO/Query.php';
        $this->_queryParams =& CRM_Contact_BAO_Query::convertFormValues( $this->_formValues );
        
        require_once "CRM/Member/Selector/Search.php";
        $selector = new CRM_Member_Selector_Search( $this->_queryParams,
                                                    $this->_action,
                                                    null,
                                                    $this->_single,
                                                    $this->_limit,
                                                    $this->_context ); 
        $selector->setKey( $this->controller->_key );
        
        $prefix = null;
        if ( $this->_context == 'basic' ) {
            $prefix = $this->_prefix;
        }

        $controller = new CRM_Core_Selector_Controller($selector , 
                                                        $this->get( CRM_Utils_Pager::PAGE_ID ), 
                                                        $sortID, 
                                                        CRM_Core_Action::VIEW,
                                                        $this,
                                                        CRM_Core_Selector_Controller::SESSION,
                                                        $prefix);
        $controller->setEmbedded( true ); 

        $query   =& $selector->getQuery( );
        $controller->run(); 
    }

    function setDefaultValues( ) 
    { 
        return $this->_defaults; 
    } 
     

    function fixFormValues( ) {
        // if this search has been forced
        // then see if there are any get values, and if so over-ride the post values
        // note that this means that GET over-rides POST :)

        if ( ! $this->_force ) {
            return;
        }

        $status = CRM_Utils_Request::retrieve( 'status', 'String',
                                               CRM_Core_DAO::$_nullObject );
        if ( $status ) {
            $status = explode(',' , $status  );
            $tempStatus = array();
            foreach( $status as $value ) {
                $tempStatus[$value] = 1;
            }
            $this->_formValues['member_status_id'] = $tempStatus;
            $this->_defaults  ['member_status_id'] = $tempStatus;
        }

        $membershipType = CRM_Utils_Request::retrieve( 'type', 'String',
                                                       CRM_Core_DAO::$_nullObject );
        
        if ($membershipType) {
            $this->_formValues['member_membership_type_id'] = array( $membershipType => 1);
            $this->_defaults  ['member_membership_type_id'] = array( $membershipType => 1);
        }
        
        
        $cid = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                            CRM_Core_DAO::$_nullObject );

        if ( $cid ) {
            $cid = CRM_Utils_Type::escape( $cid, 'Integer' );
            if ( $cid > 0 ) {
                $this->_formValues['contact_id'] = $cid;
                require_once 'CRM/Contact/BAO/Contact.php';
                list( $display, $image ) = CRM_Contact_BAO_Contact::getDisplayAndImage( $cid );
                $this->_defaults['sort_name'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $cid,
                                                                             'sort_name' );
                // also assign individual mode to the template
                $this->_single = true;
            }
        }

        $fromDate = CRM_Utils_Request::retrieve( 'start', 'Date',
                                                 CRM_Core_DAO::$_nullObject );
        if ( $fromDate ) {
            list($date) = CRM_Utils_Date::setDateDefaults( $fromDate );
            $this->_formValues['member_start_date_low'] = $this->_defaults['member_start_date_low'] = $date;
        }

        $toDate= CRM_Utils_Request::retrieve( 'end', 'Date',
                                              CRM_Core_DAO::$_nullObject );
        if ( $toDate ) { 
            list($date) = CRM_Utils_Date::setDateDefaults( $toDate );
            $this->_formValues['member_start_date_high'] = $this->_defaults['member_start_date_high'] = $date;
            
        }
        $joinDate= CRM_Utils_Request::retrieve( 'join', 'Date',
                                                CRM_Core_DAO::$_nullObject );
        if ( $joinDate ) {
            list($date) = CRM_Utils_Date::setDateDefaults( $joinDate );
            $this->_formValues['member_join_date_low'] = $this->_defaults  ['member_join_date_low'] = $date;
        }

        $joinEndDate= CRM_Utils_Request::retrieve( 'joinEnd', 'Date',
                                                   CRM_Core_DAO::$_nullObject );
        if ( $joinEndDate ) {
            list($date) = CRM_Utils_Date::setDateDefaults( $joinEndDate );
            $this->_formValues['member_join_date_high'] = $this->_defaults  ['member_join_date_high'] = $date;
        }
        
        $this->_limit = CRM_Utils_Request::retrieve( 'limit', 'Positive',
                                                     $this );
    
		//LCD also allow restrictions to membership owner via GET
		$owner = CRM_Utils_Request::retrieve( 'owner', 'String', CRM_Core_DAO::$_nullObject );
		if ( $owner ) {
			$this->_formValues['member_is_primary'] = $this->_defaults['member_is_primary'] = 2;
		}
	}

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Find Members');
    }
   
}


