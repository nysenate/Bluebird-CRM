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

require_once 'CRM/Profile/Form.php';

/**
 * This class provides the functionality for batch profile update
 */
class CRM_Contact_Form_Task_PickProfile extends CRM_Contact_Form_Task {

    /**
     * the title of the group
     *
     * @var string
     */
    protected $_title;

    /**
     * maximum contacts that should be allowed to update
     *
     */
    protected $_maxContacts = 100;

    /**
     * maximum profile fields that will be displayed
     *
     */
    protected $_maxFields = 9;


    /**
     * variable to store redirect path
     *
     */
    protected $_userContext;


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
    
        $session = CRM_Core_Session::singleton();
        $this->_userContext = $session->readUserContext( );
    
        $validate = false;
        //validations
        if ( count($this->_contactIds) > $this->_maxContacts) {
            CRM_Core_Session::setStatus("The maximum number of contacts you can select for Batch Update is {$this->_maxContacts}. You have selected ". count($this->_contactIds). ". Please select fewer contacts from your search results and try again." );
            $validate = true;
        }
        
        require_once 'CRM/Contact/BAO/Contact/Utils.php';
        if (CRM_Contact_BAO_Contact_Utils::checkContactType($this->_contactIds)) {
            CRM_Core_Session::setStatus("Batch update requires that all selected contacts be the same type (e.g. all Individuals OR all Organizations...). Please modify your selected contacts and try again.");
            $validate = true;
        }

        if ($validate) { // than redirect
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
        CRM_Utils_System::setTitle( ts('Batch Profile Update for Contact') );
        
        if ( CRM_Core_Permission::access( 'Quest' ) ) {
            $this->_contactTypes['Student'] = 'Student';            
        }
                
        foreach($this->_contactIds as $id) {
            $this->_contactTypes   = CRM_Contact_BAO_Contact::getContactTypes( $id );
        }
        
        //add Contact type profiles
        $this->_contactTypes[] = 'Contact';
        
        require_once "CRM/Core/BAO/UFGroup.php";
        $profiles = CRM_Core_BAO_UFGroup::getProfiles($this->_contactTypes);
        
        if ( empty( $profiles ) ) {
            $types = implode( 'or', $this->_contactTypes );
            CRM_Core_Session::setStatus("The contact type selected for Batch Update do not have corresponding profiles. Please make sure that {$types} has a profile and try again." );
            CRM_Utils_System::redirect( $this->_userContext );
        }
        $ufGroupElement = $this->add('select', 'uf_group_id', ts('Select Profile'), array( '' => ts('- select profile -')) + $profiles, true);
        
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
        $this->addFormRule( array( 'CRM_Contact_Form_Task_PickProfile', 'formRule' ) );
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
        require_once "CRM/Core/BAO/UFField.php";
        if ( CRM_Core_BAO_UFField::checkProfileType( $fields['uf_group_id'] ) ) {
            $errorMsg['uf_group_id'] = "You cannot select mix profile for batch update.";
        }

        if ( !empty($errorMsg) ) {
            return $errorMsg;
        }
        
        return true;
    }    

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params = $this->exportValues( );

        $this->set( 'ufGroupId', $params['uf_group_id'] );

	// also reset the batch page so it gets new values from the db
	$this->controller->resetPage( 'Batch' );
    }//end of function
}

