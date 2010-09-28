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

require_once "CRM/Contact/Form/AddContact.php";
require_once 'CRM/Core/Form.php';
require_once "CRM/Contact/Form/Task.php";

/**
 * This class generates form components for processing a case
 * 
 */
class CRM_Case_Form_Case extends CRM_Contact_Form_Task
{
    /**
     * the id of the case that we are proceessing
     *
     * @var int
     * @protected
     */
    protected $_id;


    /**
     * the id of the contact associated with this contribution
     *
     * @var int
     * @protected
     */
    protected $_contactID;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess()  
    {  
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        if ( $this->_contactID ) {
            $currentlyViewedContact = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                            $this->_contactID,
                                                            'sort_name',
                                                            'id' );
            $this->assign('currentlyViewedContact',$currentlyViewedContact);
        }
        $this->_activityID = CRM_Utils_Request::retrieve('activity_id','Integer',$this);
        $this->_context = CRM_Utils_Request::retrieve('context','String',$this);
        if ( $this->_context != 'search' ) {
            $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Integer', $this );
        }
        $this->assign('context', $this->_context);
        $this->_caseid = CRM_Utils_Request::retrieve('caseid','Integer',$this);
        $this->assign('enableCase', true );
        $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this );
        $this->assign( 'action', $this->_action);

        $this->_addCaseContact = CRM_Utils_Array::value( 'case_contact', $_GET );
        
        $this->assign('addCaseContact', false);
        if ( $this->_addCaseContact ) {
            $this->assign('addCaseContact', true);
        }
        if ( $this->_context != 'search' ) { 
            $this->assign('search', false);
        } else {
            //set the appropriate action
            $advanced = null;
            $builder  = null;
            
            $session = CRM_Core_Session::singleton();
            $advanced = $session->get('isAdvanced');
            $builder  = $session->get('isSearchBuilder');
            
            if ( $advanced == 1 ) {
                $this->_action = CRM_Core_Action::ADVANCED;
            } else if ( $advanced == 2 && $builder = 1) {
                $this->_action = CRM_Core_Action::PROFILE;
            }
            
            parent::preProcess( );
            $this->assign('search', true);
        }
        
        $this->assign( 'contactUrlPath', 'civicrm/contact/view/case' );
        
        // build case contact combo
        if ( CRM_Utils_Array::value( 'case_contact', $_POST ) ) {
            foreach ( $_POST['case_contact'] as $key => $value ) {
                CRM_Contact_Form_AddContact::buildQuickForm( $this, "case_contact[{$key}]" );
            }
            $this->assign( 'caseContactCount', count( $_POST['case_contact'] ) );
        }
    }

    function setDefaultValues( ) 
    {
        $defaults = array( );
        $contactNames = array();
        require_once 'CRM/Case/BAO/Case.php' ;
        if ( isset( $this->_id ) ) { 
            $params = array( 'id' => $this->_id );
            CRM_Case_BAO_Case::retrieve($params, $defaults, $ids);
            
            $defaults['case_contact'] = CRM_Case_BAO_Case::retrieveContactIdsByCaseId( $this->_id, $this->_contactID );
            $contactNames =  CRM_Case_BAO_Case::getContactNames( $this->_id );
            foreach( $contactNames as $key => $name ){
                $defaults['contact_names'] .=  $defaults['contact_names']?",\"$name\"":"\"$name\"";
            }

        }    
        $this->assign('contactNames',CRM_Utils_Array::value( 'contact_names', $defaults ) );
        $defaults['case_type_id'] = explode( CRM_Case_BAO_Case::VALUE_SEPERATOR, CRM_Utils_Array::value( 'case_type_id' , $defaults ) );
        $config = CRM_Core_Config::singleton( );
        if ($config->civiHRD){
            $defaults['casetag2_id'] = explode( CRM_Case_BAO_Case::VALUE_SEPERATOR, CRM_Utils_Array::value( 'casetag2_id' , $defaults ) );
            $defaults['casetag3_id'] = explode( CRM_Case_BAO_Case::VALUE_SEPERATOR, CRM_Utils_Array::value( 'casetag3_id' , $defaults ) );
        }
        if ( $this->_action & CRM_Core_Action::ADD || $this->_context == 'search') {
            $defaults['start_date'] = array( );
            CRM_Utils_Date::getAllDefaultValues( $defaults['start_date'] );
        }
        
        //set the assigneed contact count to template
        if ( !empty( $defaults['case_contact'] ) ) {
            $this->assign( 'caseContactCount', count( $defaults['case_contact'] ) );
        }
        return $defaults;
    }
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )
    {
        if ( ! empty($this->_contactIds) && is_array($this->_contactIds)) {
            $contactIds = implode(',',$this->_contactIds);
            $query = "SELECT id, sort_name 
                      FROM civicrm_contact
                      WHERE id IN ({$contactIds})";
            $queryParam = array();
            $dao = CRM_Core_DAO::executeQuery( $query, $queryParam );
            while ( $dao->fetch() ) {
                $caseContacts .= $caseContacts?",\"$dao->sort_name\"":"\"$dao->sort_name\"";
            }
            $this->assign('caseContacts', $caseContacts);
        } 

        if ( $this->_action & CRM_Core_Action::DELETE ) {
            $this->addButtons(array( 
                                    array ( 'type'      => 'next', 
                                            'name'      => ts('Delete'), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                    ) 
                              );
            return;
        }

        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Case_DAO_Case' );
        $this->add( 'text', 'subject', ts('Subject'), array_merge( $attributes['subject'], array('maxlength' => '128') ), true);
        $this->addRule( 'subject', ts('A case with this subject already exists.'),     
                        'objectExists', array('CRM_Case_DAO_Case', $this->_id, 'subject') );

        require_once 'CRM/Core/OptionGroup.php';        
        $caseStatus  = CRM_Core_OptionGroup::values('case_status');
        $this->add('select', 'status_id',  ts( 'Case Status' ),  
                    $caseStatus , true  );

        $caseType = CRM_Core_OptionGroup::values('case_type');
        $this->add('select', 'case_type_id',  ts( 'Case Type' ),  
                   $caseType , true, array("size"=>"5",  "multiple"));
        $config = CRM_Core_Config::singleton( );
        if ($config->civiHRD){
            $caseSubType = CRM_Core_OptionGroup::values('f1_case_sub_type');
            $this->add('select', 'casetag2_id',  ts( 'Case Sub Type' ),  
                       $caseSubType , false, array("size"=>"5","multiple"));
            
            $caseViolation = CRM_Core_OptionGroup::values('f1_case_violation');
            $this->add('select', 'casetag3_id',  ts( 'Violation' ),  
                       $caseViolation , false, array("size"=>"5",  "multiple"));
        }

        // add a dojo facility for searching contacts
        $this->assign( 'dojoIncludes', " dojo.require('dojox.data.QueryReadStore'); dojo.require('dojo.parser');" );
        
        $attributes = array( 'dojoType'       => 'civicrm.FilteringSelect',
                             'mode'           => 'remote',
                             'store'          => 'contactStore',
                             'pageSize'       => 10  );
        
        $dataUrl = CRM_Utils_System::url( "civicrm/ajax/search",
                                          "reset=1",
                                          true, null, false );
        $this->assign('dataUrl',$dataUrl );

        if ( $this->_addCaseContact ) {
            $contactCount = CRM_Utils_Array::value( 'count', $_GET );
            $nextContactCount = $contactCount + 1;
            $this->assign('contactCount', $contactCount );
            $this->assign('nextContactCount', $nextContactCount );
            $this->assign('contactFieldName', 'case_contact' );
            return CRM_Contact_Form_AddContact::buildQuickForm( $this, "case_contact[{$contactCount}]" );
        }

        $this->add( 'date', 'start_date', ts('Start Date'),
                    CRM_Core_SelectValues::date('activityDate' ),
                    true);   
        $this->addRule('start_date', ts('Select a valid date.'), 'qfDate');
        
        $this->add( 'date', 'end_date', ts('End Date'),
                    CRM_Core_SelectValues::date('activityDate' ),
                    false); 
        $this->addRule('end_date', ts('Select a valid date.'), 'qfDate');
        $this->add('textarea', 'details', ts('Notes'), CRM_Core_DAO::getAttribute( 'CRM_Case_DAO_Case', 'details' ));

        $this->addFormRule( array( 'CRM_Case_Form_Case', 'formRule' ) );
        
        if ( $this->_action & CRM_Core_Action::VIEW ) {
            $this->freeze( );
            $this->addButtons(array(  
                                    array ( 'type'      => 'cancel',  
                                            'name'      => ts('Done'),  
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                            
                                            'isDefault' => true   )
                                    )
                              );
        } else {
            $this->addButtons(array( 
                                    array ( 'type'      => 'next',
                                            'name'      => ts('Save'), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                    ) 
                              );
        }
    }
    
    /**  
     * global form rule  
     *  
     * @param array $fields  the input form values  
     * @param array $files   the uploaded files if any  
     * @param array $options additional user data  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * @static  s
     */  
    static function formRule( $values ) {

        $errors = array( ); 

        $start = CRM_Utils_Date::format( $values['start_date'] );
        $end   = CRM_Utils_Date::format( $values['end_date'  ] );
        if ( ($end < $start) && ($end != 0) ) {
            $errors['end_date'] = ts( 'End date should be later than Start date' );
            return $errors;
        }
        
        return true;
    }
    
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess( )
    {
        if ( $this->_action & CRM_Core_Action::DELETE ) { 
            require_once 'CRM/Case/BAO/Case.php';
            CRM_Case_BAO_Case::deleteCase( $this->_id );
            CRM_Core_Session::setStatus( ts("Selected Case has been deleted."));
            return;
        }
        
        // get the submitted form values.  
        $params = $this->controller->exportValues( $this->_name );
        
        if ( $this->_action & CRM_Core_Action::UPDATE ) {
            $params['id'] = $this->_id ;
        }
        
        $params['contact_id'  ] = $this->_contactID;
        $params['start_date'  ] = CRM_Utils_Date::format( $params['start_date'] );
        $params['end_date'    ] = CRM_Utils_Date::format( $params['end_date'] );
        $params['case_type_id'] = CRM_Case_BAO_Case::VALUE_SEPERATOR.implode(CRM_Case_BAO_Case::VALUE_SEPERATOR, $params['case_type_id'] ).CRM_Case_BAO_Case::VALUE_SEPERATOR;
        
        $config = CRM_Core_Config::singleton( );
        if ($config->civiHRD){
            $params['casetag2_id'] = CRM_Case_BAO_Case::VALUE_SEPERATOR.implode(CRM_Case_BAO_Case::VALUE_SEPERATOR, $params['casetag2_id'] ).CRM_Case_BAO_Case::VALUE_SEPERATOR;
            $params['casetag3_id'] = CRM_Case_BAO_Case::VALUE_SEPERATOR.implode(CRM_Case_BAO_Case::VALUE_SEPERATOR, $params['casetag3_id'] ).CRM_Case_BAO_Case::VALUE_SEPERATOR;
        }
        
        require_once 'CRM/Case/BAO/Case.php';
        $case = CRM_Case_BAO_Case::create( $params );
        CRM_Case_BAO_Case::deleteCaseContact($case->id);
        if ( ! empty($this->_contactIds) && is_array($this->_contactIds)) {
            foreach ( $this->_contactIds as $key => $id ) {
                if ($id) {
                    $contactParams = array(
                                           'case_id'    => $case->id,
                                           'contact_id' => $id
                                           );
                    CRM_Case_BAO_Case::addCaseToContact( $contactParams );
                }
            }
        } else {
            $contactParams = array(
                                   'case_id'    => $case->id,
                                   'contact_id' => $this->_contactID
                                   );
            CRM_Case_BAO_Case::addCaseToContact( $contactParams );
            foreach ( $params['case_contact'] as $key => $id ) {
                if ($id) {
                    $contactParams = array(
                                           'case_id'    => $case->id,
                                           'contact_id' => $id
                                           );
                    CRM_Case_BAO_Case::addCaseToContact( $contactParams );
                }
            }
        }
        
        // set status message
        CRM_Core_Session::setStatus( ts('Case \'%1\' has been saved.', array( 1 => $params['subject'] ) ) );
    }
}


