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
require_once 'CRM/Core/Form.php';
/**
 * This class generates form components for processing a ontribution 
 * 
 */
class CRM_Contribute_Form_PCP_PCPAccount extends CRM_Core_Form
{
    /**
     *Variable defined for Contribution Page Id
     *
     */

    public  $_pageId = null;
    public  $_id     = null;

    /** 
     * are we in single form mode or wizard mode?
     * 
     * @var boolean
     * @access protected 
     */ 
    public $_single;

    public function preProcess()  
    {
        $session = CRM_Core_Session::singleton( );
        $this->_action = CRM_Utils_Request::retrieve( 'action', 'String', $this, false );
        $this->_pageId = CRM_Utils_Request::retrieve( 'pageId', 'Positive', $this );
        $this->_id     = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
        if ( $this->_id ){
            $contactID = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_PCP', $this->_id, 'contact_id' );   
        }

        $this->_contactID = isset( $contactID ) ? $contactID : $session->get( 'userID' );     
        if ( ! $this->_pageId ) {
            if ( ! $this->_id ) {
                $msg = ts( 'We can\'t load the requested web page due to an incomplete link. This can be caused by using your browser\'s Back button or by using an incomplete or invalid link.' );
                CRM_Core_Error::fatal( $msg );
            } else {
                $this->_pageId = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_PCP', $this->_id, 'contribution_page_id' );
            }
        }
        $config = CRM_Core_Config::singleton( );
        //redirect back to online Contribution page, we allow only logged in
        //user to configure the PCP account and Page in standalone installation.
        if ( $config->userFramework == 'Standalone' && !$this->_contactID ) {
            CRM_Core_Error::statusBounce( ts("You must login with your OpenID provider before you can create a Personal Campaign Page."),
                                          CRM_Utils_System::url( 'civicrm/contribute/transact',
                                                                 "reset=1&id={$this->_pageId}",
                                                                 false, null, false, true ) );
        }
        $this->_single = $this->get( 'single' );
        
        if ( !$this->_single ) {
            $this->_single = $session->get('singleForm');
        }

        $this->set( 'action'              , $this->_action );
        $this->set( 'page_id'             , $this->_id );
        $this->set( 'contribution_page_id', $this->_pageId );

        // we do not want to display recently viewed items, so turn off
        $this->assign('displayRecent' , false );

        if( $this->_single ) {
            CRM_Utils_System::setTitle(ts('Update Contact Information'));
        }
    }

    function setDefaultValues( ) 
    {   
        if (!$this->_contactID) {
            return;
        }
        foreach ( $this->_fields as $name => $dontcare) {
            $fields[$name] = 1;
        }
        
        require_once "CRM/Core/BAO/UFGroup.php";
        CRM_Core_BAO_UFGroup::setProfileDefaults( $this->_contactID, $fields, $this->_defaults );
        
        //set custom field defaults
        require_once "CRM/Core/BAO/CustomField.php";
        foreach ( $this->_fields as $name => $field ) {
            if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID($name) ) {
                if ( !isset( $this->_defaults[$name] ) ) {
                    CRM_Core_BAO_CustomField::setProfileDefaults( $customFieldID, $name, $this->_defaults,
                                                                  null, CRM_Profile_Form::MODE_REGISTER );
                }
            }
        }
        
        return $this->_defaults;
    }
    
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    {
        require_once 'CRM/Contribute/BAO/PCP.php';
        $id = CRM_Contribute_BAO_PCP::getSupporterProfileId( $this->_pageId );
        if ( CRM_Contribute_BAO_PCP::checkEmailProfile( $id ) ){
            $this->assign('profileDisplay', true);
        }
        $fields = null;
        require_once "CRM/Core/BAO/UFGroup.php";
        if ( $this->_contactID ) {
            if ( CRM_Core_BAO_UFGroup::filterUFGroups($id, $this->_contactID)  ) {
                $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD );
            }
            $this->addFormRule( array( 'CRM_Contribute_Form_PCP_PCPAccount', 'formRule' ), $this ); 
        } else {
            require_once 'CRM/Core/BAO/CMSUser.php';
            CRM_Core_BAO_CMSUser::buildForm( $this, $id , true );

            $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD );
        }
        
        if ( $fields ) {
            $this->assign( 'fields', $fields );
            $addCaptcha = false;
            foreach($fields as $key => $field) {
                if ( isset( $field['data_type'] ) && $field['data_type'] == 'File' ) {
                    // ignore file upload fields
                    continue;
                }
                require_once "CRM/Core/BAO/UFGroup.php";
                require_once "CRM/Profile/Form.php";
                CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE);
                $this->_fields[$key] = $field;
                if ( $field['add_captcha'] ) {
                    $addCaptcha = true;
                }
            }
            
            if ( $addCaptcha ) {
                require_once 'CRM/Utils/ReCAPTCHA.php';
                $captcha =& CRM_Utils_ReCAPTCHA::singleton( );
                $captcha->add( $this );
                $this->assign( "isCaptcha" , true );
            }
        }

        require_once "CRM/Contribute/PseudoConstant.php";
        $this->assign( 'campaignName', CRM_Contribute_PseudoConstant::contributionPage( $this->_pageId ) );
        
        if ( $this->_single ) {
            $button = array ( array ( 'type'      => 'next',
                                      'name'      => ts('Save'), 
                                      'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                      'isDefault' => true   ),
                              array ( 'type' => 'cancel',
                                      'name' => ts('Cancel'))
                              );
        }else {
            $button[] = array ( 'type'      => 'next',
                                'name'      => ts('Continue >>'), 
                                'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                'isDefault' => true   );
        }
        $this->addFormRule( array( 'CRM_Contribute_Form_PCP_PCPAccount', 'formRule' ), $this );
        $this->addButtons( $button );
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
     * @static  
     */  
    static function formRule( $fields, $files, $self ) 
    {
        $errors = array( );
        require_once "CRM/Utils/Rule.php";
        foreach( $fields as $key => $value ) {
            if ( strpos($key, 'email-') !== false && !empty($value) ) {
                $ufContactId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFMatch', $value, 'contact_id', 'uf_name' );
                if ( $ufContactId && $ufContactId != $self->_contactID ) {
                    $errors[$key] = ts( 'There is already an user associated with this email address. Please enter different email address.' );   
                }
            }
        }             
        return empty($errors) ? true : $errors;
    }
    
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess( )  
    {
        $params  = $this->controller->exportValues( $this->getName() );
       
        if ( ! $this->_contactID && isset( $params['cms_create_account'] ) ) {
            foreach( $params as $key => $value ) {
                if ( substr( $key , 0,5 ) == 'email' && !empty( $value ) )  {
                    list($fieldName, $locTypeId) = CRM_Utils_System::explode('-', $key, 2);
                    $isPrimary = 0;
                   if ( $locTypeId == 'Primary') {
                       require_once "CRM/Core/BAO/LocationType.php";
                       $locTypeId = & CRM_Core_BAO_LocationType::getDefault();
                       $isPrimary = 1;
                   }

                   $params['email'] = array( );
                   $params['email'][1]['email']            = $value;
                   $params['email'][1]['location_type_id'] = $locTypeId;
                   $params['email'][1]['is_primary']       = $isPrimary;
                }
            }
        }
        
        require_once 'CRM/Dedupe/Finder.php';
        $dedupeParams = CRM_Dedupe_Finder::formatParams( $params, 'Individual');
        $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual', 'Strict' );
        if ( $ids ) {
            $this->_contactID = $ids['0'];
        }
        $contactID =& CRM_Contact_BAO_Contact::createProfileContact( $params, $this->_fields, $this->_contactID );
        $this->set('contactID', $contactID);
        
        if ( !empty($params['email']) ) {
            $params['email'] = $params['email'][1]['email']; 
        }

        require_once "CRM/Contribute/BAO/Contribution/Utils.php";
        CRM_Contribute_BAO_Contribution_Utils::createCMSUser( $params, $contactID, 'email' );
    }
}

