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

require_once "CRM/Core/Form.php";
require_once "CRM/Activity/BAO/Activity.php";
require_once 'CRM/Custom/Form/CustomData.php';

/**
 * This class generates form components for case activity
 * 
 */
class CRM_Case_Form_Case extends CRM_Core_Form
{
    /**
     * The context
     *
     * @var string
     */
    public $_context = 'case';

    /**
     * Case Id
     */
    public $_caseId  = null;

    /**
     * Client Id
     */
    public $_currentlyViewedContactId = null;

    /**
     * Activity Type File
     */
    public $_activityTypeFile = null;
    
    /**
     * logged in contact Id
     */
    public $_currentUserId    = null;

    /**
     * activity type Id
     */
    public $_activityTypeId   = null;

    /**
     * activity type Id
     */
    public $_activityId       = null;

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    function preProcess( ) 
    {        
        $this->_caseId                   = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );

        $this->_currentlyViewedContactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );

        if ( $this->_action & CRM_Core_Action::ADD && ! $this->_currentlyViewedContactId ) {
            // check for add contacts permissions
            require_once 'CRM/Core/Permission.php';
            if ( ! CRM_Core_Permission::check( 'add contacts' ) ) {
                CRM_Utils_System::permissionDenied( );
                return;
            }
        }
        
        //CRM-4418
        if ( !CRM_Core_Permission::checkActionPermission( 'CiviCase', $this->_action ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );
        }
        
        if ( $this->_action & CRM_Core_Action::DELETE || $this->_action & CRM_Core_Action::RENEW ) {
            return true;
        }
        
        if ( $this->_action & CRM_Core_Action::ADD ) {
            require_once 'CRM/Core/OptionGroup.php';
            $this->_activityTypeId = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                                     'Open Case',
                                                                     'name' );  
        }
        
        //check for case permissions.
        if ( !CRM_Case_BAO_Case::accessCiviCase( ) ) {
            CRM_Core_Error::fatal( ts( 'You are not authorized to access this page.' ) );
        }
        if ( ($this->_action & CRM_Core_Action::ADD) && 
             !CRM_Core_Permission::check( 'access all cases and activities' ) ) {
            CRM_Core_Error::fatal( ts( 'You are not authorized to access this page.' ) );
        }
        
        if ( $this->_activityTypeFile = 
             CRM_Activity_BAO_Activity::getFileForActivityTypeId($this->_activityTypeId,
                                                                 'Case') ) {
            require_once "CRM/Case/Form/Activity/{$this->_activityTypeFile}.php";
            $this->assign( 'activityTypeFile', $this->_activityTypeFile );
        }

        $details  = CRM_Case_PseudoConstant::activityType( false );
       
        CRM_Utils_System::setTitle($details[$this->_activityTypeId]['label']);
        $this->assign('activityType', $details[$this->_activityTypeId]['label']);
       
        if ( isset($this->_currentlyViewedContactId) ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $contact = new CRM_Contact_DAO_Contact( );
            $contact->id = $this->_currentlyViewedContactId;
            if ( ! $contact->find( true ) ) {
                CRM_Core_Error::statusBounce( ts('Client contact does not exist: %1', array(1 => $this->_currentlyViewedContactId)) );
            }
            $this->assign( 'clientName', $contact->display_name );
        }
        
        
        $session              = CRM_Core_Session::singleton();
        $this->_currentUserId = $session->get('userID');
        
        //when custom data is included in this page
        CRM_Custom_Form_Customdata::preProcess( $this, null, $this->_activityTypeId, 1, 'Activity' );
        eval("CRM_Case_Form_Activity_{$this->_activityTypeFile}::preProcess( \$this );");
    }
    
    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        if ( $this->_action & CRM_Core_Action::DELETE || $this->_action & CRM_Core_Action::RENEW ) {
            return true;
        }
        eval('$defaults = CRM_Case_Form_Activity_'. $this->_activityTypeFile. '::setDefaultValues($this);');
        $defaults = array_merge( $defaults, CRM_Custom_Form_Customdata::setDefaultValues( $this ) );
        return $defaults;
    }

    public function buildQuickForm( ) 
    {

        require_once 'CRM/Case/XMLProcessor/Process.php';
        $xmlProcessorProcess = new CRM_Case_XMLProcessor_Process( );
        $isMultiClient = $xmlProcessorProcess->getAllowMultipleCaseClients( );
        $this->assign( 'multiClient', $isMultiClient );

        if ( $this->_action & CRM_Core_Action::DELETE || $this->_action & CRM_Core_Action::RENEW ) {
            $title = 'Delete';
            if ( $this->_action & CRM_Core_Action::RENEW ) {
                $title = 'Restore';
            }
            $this->addButtons(array( 
                                    array ( 'type'      => 'next', 
                                            'name'      => $title,
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                     ) 
                              );
            return;
        }
        
        CRM_Custom_Form_Customdata::buildQuickForm( $this );
        // we don't want to show button on top of custom form
        $this->assign('noPreCustomButton', true);

        $s = CRM_Core_DAO::getAttribute( 'CRM_Activity_DAO_Activity', 'subject' );
        if( ! is_array( $s ) ) {
                $s = array();
        }
        $this->add( 'text', 'activity_subject', ts('Subject'), 
                   array_merge( $s, array('maxlength' => '128') ), true);

        require_once 'CRM/Core/BAO/Tag.php';
        $tags = CRM_Core_BAO_Tag::getTags( 'civicrm_case' );
        if ( !empty($tags) ) { 
            $this->add('select', 'tag',  ts( 'Select Tags' ), $tags, false, 
                       array( 'id' => 'tags',  'multiple'=> 'multiple', 'title' => ts('- select -') ));
        }
        
        // build tag widget
        require_once 'CRM/Core/Form/Tag.php';
        $parentNames = CRM_Core_BAO_Tag::getTagSet( 'civicrm_case' );
        CRM_Core_Form_Tag::buildQuickForm( $this, $parentNames, 'civicrm_case', null, false, true );
        
        $this->addButtons(array( 
                                array ( 'type'      => 'next',
                                        'name'      => ts('Save'), 
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Cancel') ), 
                                ) 
                          );

        eval("CRM_Case_Form_Activity_{$this->_activityTypeFile}::buildQuickForm( \$this );");
    }

    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        if ( $this->_action & CRM_Core_Action::DELETE || $this->_action & CRM_Core_Action::RENEW ) {
            return true;
        }
        eval('$this->addFormRule' . "(array('CRM_Case_Form_Activity_{$this->_activityTypeFile}', 'formrule'), \$this);");
        $this->addFormRule( array( 'CRM_Case_Form_Case', 'formRule'), $this );
    }

    /**
     * global validation rules for the form
     *
     * @param array $values posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values, $files, $form ) 
    {
        return true;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        // check if dedupe button, if so return.
        $buttonName = $this->controller->getButtonName( );
        if ( $buttonName == $this->_dedupeButtonName ) {
            return;
        }

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            $statusMsg = null;
            require_once 'CRM/Case/BAO/Case.php';
            $caseDelete = CRM_Case_BAO_Case::deleteCase( $this->_caseId, true );
            if ( $caseDelete ) {
                $statusMsg = ts('The selected case has been moved to the Trash. You can view and / or restore deleted cases by checking the "Deleted Cases" option under Find Cases.<br />');
            }
            CRM_Core_Session::setStatus( $statusMsg );
            return;
        }

        if ( $this->_action & CRM_Core_Action::RENEW ) {
            $statusMsg = null;
            require_once 'CRM/Case/BAO/Case.php';
            $caseRestore = CRM_Case_BAO_Case::restoreCase( $this->_caseId );
            if ( $caseRestore ) {
                $statusMsg = ts('The selected case has been restored.<br />');
            }
            CRM_Core_Session::setStatus( $statusMsg );
            return;
        }
        // store the submitted values in an array
        $params = $this->controller->exportValues( $this->_name );
        $params['now'] = date("Ymd");

        require_once 'CRM/Case/XMLProcessor/Process.php';

        // 1. call begin post process
        if ( $this->_activityTypeFile ) {
            eval("CRM_Case_Form_Activity_{$this->_activityTypeFile}" . "::beginPostProcess( \$this, \$params );");
        }

        // 2. create/edit case
        require_once 'CRM/Case/BAO/Case.php';
        if ( CRM_Utils_Array::value('case_type_id', $params ) ) {
            $caseType = CRM_Core_OptionGroup::values('case_type', false, false, false, null, 'name');
            $params['case_type']    = $caseType[$params['case_type_id']];
            $params['case_type_id'] = CRM_Case_BAO_Case::VALUE_SEPERATOR . 
                $params['case_type_id'] . CRM_Case_BAO_Case::VALUE_SEPERATOR;
        }
        $caseObj = CRM_Case_BAO_Case::create( $params );
        $params['case_id'] = $caseObj->id;
        // unset any ids, custom data
        unset($params['id'], $params['custom']);

        // add tags if exists
        $tagParams = array( );
        if ( !empty($params['tag']) ) {
            require_once 'CRM/Core/BAO/EntityTag.php';
            $tagParams = array( );
            foreach( $params['tag'] as $tag ) {
                $tagParams[$tag] = 1;
            }
        }
        require_once 'CRM/Core/BAO/EntityTag.php';
        CRM_Core_BAO_EntityTag::create( $tagParams, 'civicrm_case', $caseObj->id );
        
        //save free tags
        if ( isset( $params['taglist'] ) && !empty( $params['taglist'] ) ) {
            require_once 'CRM/Core/Form/Tag.php';
            CRM_Core_Form_Tag::postProcess( $params['taglist'], $caseObj->id, 'civicrm_case', $this );
        }
        
        // user context
        $url = CRM_Utils_System::url( 'civicrm/contact/view/case',
                                      "reset=1&action=view&cid={$this->_currentlyViewedContactId}&id={$caseObj->id}" );
        $session = CRM_Core_Session::singleton( ); 
        $session->pushUserContext( $url );

        // 3. format activity custom data
        if ( CRM_Utils_Array::value( 'hidden_custom', $params ) ) {
			$customFields = CRM_Core_BAO_CustomField::getFields( 'Activity', false, false, $this->_activityTypeId );
			$customFields = 
                CRM_Utils_Array::crmArrayMerge( $customFields, 
                                                CRM_Core_BAO_CustomField::getFields( 'Activity', false, false, 
                                                                                     null, null, true ) );
	        $params['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
	                                                                   $customFields,
	                                                                   $this->_activityId,
	                                                                   'Activity' );
        }

        // 4. call end post process
        if ( $this->_activityTypeFile ) {
            eval("CRM_Case_Form_Activity_{$this->_activityTypeFile}" . "::endPostProcess( \$this, \$params );");
        }

        // 5. auto populate activites

        // 6. set status
        CRM_Core_Session::setStatus( "{$params['statusMsg']}" );
    }
}
