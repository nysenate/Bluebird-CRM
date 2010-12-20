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

require_once 'CRM/Activity/Form/Task.php';

/**
 * This class provides the functionality to email a group of contacts
 */
class CRM_Activity_Form_Task_PickOption extends CRM_Activity_Form_Task {

    /**
     * the title of the group
     *
     * @var string
     */
    protected $_title;

    /**
     * maximum Activities that should be allowed to update
     *
     */
    protected $_maxActivities = 100;


    /**
     * variable to store redirect path
     *
     */
    protected $_userContext;

    /**
     * variable to store contact Ids
     *
     */
    public $_contacts;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {   
        /*
         * initialize the task and row fields
         */
        parent::preProcess( );
        $session = CRM_Core_Session::singleton( );
        $this->_userContext = $session->readUserContext( );
       
        CRM_Utils_System::setTitle( ts( 'Send Email to Contacts' ) );
    
        $validate = false;
        //validations
        if ( count( $this->_activityHolderIds ) > $this->_maxActivities ) {
            CRM_Core_Session::setStatus( "The maximum number of Activities you can select to send an email is {$this->_maxActivities}. You have selected ". count($this->_activityHolderIds ). ". Please select fewer Activities from your search results and try again." );
            $validate = true;
        }
        if ( $validate ) { // then redirect
            CRM_Utils_System::redirect( $this->_userContext );
        }
    }
  
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $this->addElement( 'checkbox', 'with_contact', ts('With Contact') );
        $this->addElement( 'checkbox', 'assigned_to',  ts('Assigned to Contact') );
        $this->addElement( 'checkbox', 'created_by',   ts('Created by') );
        $this->setDefaults( array( 'with_contact' => 1 ) );
        $this->addDefaultButtons( ts( 'Continue >>' ) );
    }

    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        $this->addFormRule( array( 'CRM_Activity_Form_Task_PickOption', 'formRule' ) );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $fields ) 
    {
        return true;
    }    

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    
    public function postProcess( ) 
    {
        $params = $this->exportValues( );
        $this->_contacts = array( );
        //get assignee contacts
        if ( $params['assigned_to'] ) {
            require_once 'CRM/Activity/BAO/ActivityAssignment.php';
            foreach ( $this->_activityHolderIds as $key => $id ) {
                $ids = array_keys( CRM_Activity_BAO_ActivityAssignment::getAssigneeNames( $id ) );
                $this->_contacts = array_merge( $this->_contacts, $ids );
            }
        }
        //get target contacts
        if ( $params['with_contact'] ) {
            require_once 'CRM/Activity/BAO/ActivityTarget.php';
            foreach ( $this->_activityHolderIds as $key => $id ) {
                $ids = array_keys( CRM_Activity_BAO_ActivityTarget::getTargetNames( $id ) );
                $this->_contacts = array_merge( $this->_contacts, $ids );
            }
        }
        //get 'Added by' contacts
        if ( $params['created_by'] ) {
            parent::setContactIDs( );  
            if ( ! empty( $this->_contactIds ) ) {
                $this->_contacts =array_merge( $this->_contacts, $this->_contactIds );
            }
        }
        $this->_contacts = array_unique( $this->_contacts );
        $this->set( 'contacts', $this->_contacts );
    }
}
