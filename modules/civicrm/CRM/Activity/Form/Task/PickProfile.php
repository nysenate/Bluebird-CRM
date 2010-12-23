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
require_once 'CRM/Activity/Form/Task.php';

/**
 * This class provides the functionality for batch profile update for Activity
 */
class CRM_Activity_Form_Task_PickProfile extends CRM_Activity_Form_Task {

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
       
        CRM_Utils_System::setTitle( ts( 'Batch Profile Update for Activities' ) );
    
        $validate = false;
        //validations
        if ( count( $this->_activityHolderIds ) >$this->_maxActivities ) {
            CRM_Core_Session::setStatus( "The maximum number of Activities you can select for Batch Update is {$this->_maxActivities}. You have selected ". count($this->_activityHolderIds ). ". Please select fewer Activities from your search results and try again." );
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
        require_once "CRM/Core/BAO/UFGroup.php";
        $types = array( 'Activity' );
        $profiles = CRM_Core_BAO_UFGroup::getProfiles( $types, true );
        
        $activityTypeIds = array_flip( CRM_Core_PseudoConstant::activityType( true, false, false, 'name' ) );
        $nonEditableActivityTypeIds =  array (                                         
                                              $activityTypeIds['Email'],
                                              $activityTypeIds['Bulk Email'],
                                              $activityTypeIds['Contribution'],
                                              $activityTypeIds['Inbound Email'],
                                              $activityTypeIds['Pledge Reminder'],
                                              $activityTypeIds['Membership Signup'], 
                                              $activityTypeIds['Membership Renewal'],
                                              $activityTypeIds['Event Registration'],
                                              $activityTypeIds['Pledge Acknowledgment']
                                             );
        
        foreach ( $this->_activityHolderIds as $activityId ) {
            $typeId = CRM_Core_DAO::getFieldValue( "CRM_Activity_DAO_Activity", $activityId, 'activity_type_id' );
            if ( in_array ( $typeId, $nonEditableActivityTypeIds ) ) {
                $notEditable = true;
                break;
            }
        }
        
        if ( empty( $profiles ) ) {
            CRM_Core_Session::setStatus( "You will need to create a Profile containing the {$types[0]} fields you want to edit before you can use Batch Update via Profile. Navigate to Administer Civicrm >> CiviCRM Profile to configure a Profile. Consult the online Administrator documentation for more information." );
            CRM_Utils_System::redirect( $this->_userContext );
        } else if ( $notEditable ) {
            CRM_Core_Session::setStatus( "Some of the selected activities are not editable." );
            CRM_Utils_System::redirect( $this->_userContext );
        }
        
        $ufGroupElement = $this->add( 'select', 'uf_group_id', ts('Select Profile' ), 
                                      array( '' => ts( '- select profile -') ) + $profiles, true );
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
        $this->addFormRule( array( 'CRM_Activity_Form_Task_PickProfile', 'formRule' ) );
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
    public function postProcess() 
    {
        $params = $this->exportValues( );
        
        $this->set( 'ufGroupId', $params['uf_group_id'] );
        
	// also reset the batch page so it gets new values from the db
	$this->controller->resetPage( 'Batch' );
       
    }//end of function
}

