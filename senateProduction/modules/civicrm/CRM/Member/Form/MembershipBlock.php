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

require_once 'CRM/Contribute/Form/ContributionPage.php';
require_once 'CRM/Contribute/PseudoConstant.php';

/**
 * form to process actions on Membership
 */
class CRM_Member_Form_MembershipBlock extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return void
     */
    function setDefaultValues()
    {
        //parent::setDefaultValues();
        $defaults = array();
        if ( isset($this->_id ) ) {
            require_once 'CRM/Member/DAO/MembershipBlock.php';
            $dao = new CRM_Member_DAO_MembershipBlock();
            $dao->entity_table = 'civicrm_contribution_page';
            $dao->entity_id = $this->_id; 
            $dao->find(true);
            CRM_Core_DAO::storeValues( $dao, $defaults );
        }

        // for membership_types
        if ( isset( $defaults['membership_types'] ) ) {
            $membershipType    = explode(',' , $defaults['membership_types'] );
            $newMembershipType = array();  
            foreach( $membershipType as $k => $v ) {
                $newMembershipType[$v] = 1;
            }
            $defaults['membership_type'] = $newMembershipType;
        }

        // Set Display Minimum Fee default to true if we are adding a new membership block
        if ( ! isset( $defaults['id'] ) ) {
            $defaults['display_min_fee'] = 1;
        } else {
            $this->assign('membershipBlockId', $defaults['id']);
        }
        return $defaults;
    }
    

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
             
        require_once 'CRM/Member/BAO/MembershipType.php';
        $membershipTypes = CRM_Member_BAO_MembershipType::getMembershipTypes(); 

        if (! empty( $membershipTypes ) ) {
            $this->addElement('checkbox', 'is_active', ts('Membership Section Enabled?') , null, array( 'onclick' => "memberBlock(this);" ));
        
            $this->addElement('text', 'new_title', ts('Title - New Membership'), CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'new_title'));
            
            $this->addWysiwyg( 'new_text', ts('Introductory Message - New Memberships'),CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'new_text'));

            $this->addElement('text', 'renewal_title', ts('Title - Renewals'), CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'renewal_title'));

            $this->addWysiwyg( 'renewal_text', ts('Introductory Message - Renewals'),CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'renewal_text'));
            
            $this->addElement('checkbox', 'is_required', ts('Require Membership Signup') );
            $this->addElement('checkbox', 'display_min_fee', ts('Display Membership Fee') );
            $this->addElement('checkbox', 'is_separate_payment', ts('Separate Membership Payment') );
            
            $membership        = array();
            $membershipDefault = array();
            foreach ( $membershipTypes as $k => $v ) {
                $membership[]      = HTML_QuickForm::createElement('advcheckbox', $k , null, $v );
                $membershipDefault[] = HTML_QuickForm::createElement('radio',null ,null,null, $k );
            }
            
            $this->addGroup($membership, 'membership_type', ts('Membership Types'));
            $this->addGroup($membershipDefault, 'membership_type_default', ts('Membership Types Default'));
            
            $this->addFormRule(array('CRM_Member_Form_MembershipBlock', 'formRule') , $this->_id);
        }

        $session = CRM_Core_Session::singleton();
        $single = $session->get('singleForm');
        if ( $single ) {
            $this->addButtons(array(
                                    array ( 'type'      => 'next',
                                            'name'      => ts('Save'),
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'isDefault' => true   ),
                                    array ( 'type'      => 'cancel',
                                            'name'      => ts('Cancel') ),
                                    )
                              );
        } else {
            parent::buildQuickForm( );
        }
        //$session->set('single', false );
    }

    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    static function formRule( $params, $files, $contributionPageId = null ) 
    {
        $errors = array( );
        if ( CRM_Utils_Array::value( 'is_active', $params ) ) {
            
            // don't allow price set w/ membership signup, CRM-5095 
            require_once 'CRM/Price/BAO/Set.php';
            if ( $contributionPageId && CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $contributionPageId ) ) {
                $errors['is_active'] = ts( 'You cannot enable both Membership Signup and Price Set on the same online contribution page.' );  
                return $errors;
            }
            
            if ( !  isset ( $params['membership_type'] ) ||
                 ( ! is_array( $params['membership_type'] ) ) ) {
                $errors['membership_type'] = ts( 'Please select at least one Membership Type to include in the Membership section of this page.' );
            } else {
                $membershipType = array_values($params['membership_type']);
                if ( array_sum($membershipType) == 0 ) {
                    $errors['membership_type'] = ts( 'Please select at least one Membership Type to include in the Membership section of this page.' );
                }
            }

            //for CRM-1302
            //if Membership status is not present, then display an error message
            require_once 'CRM/Member/BAO/MembershipStatus.php';
            $dao = new CRM_Member_BAO_MembershipStatus();
            if ( ! $dao->find( ) ) {
                $errors['_qf_default'] = ts( 'Add status rules, before configuring membership' );
            }    
            
            //give error if default is selected for an unchecked membership type
            if ( isset($params['membership_type_default']) && !$params['membership_type'][$params['membership_type_default']] ) {
                $errors['membership_type_default'] = ts( 'Can\'t set default option for an unchecked membership type.' );
            }

            if ( $contributionPageId ) {
                require_once "CRM/Contribute/DAO/ContributionPage.php";
                $amountBlock = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $contributionPageId, 'amount_block_is_active' );
                
                if ( !$amountBlock &&  CRM_Utils_Array::value( 'is_separate_payment', $params ) ) {
                    $errors['is_separate_payment'] = ts( 'Please enable the contribution amount section to use this option.' );
                }
            }
        }

        return empty($errors) ? true : $errors;
    }
    
    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // get the submitted form values.
        $params = $this->controller->exportValues( $this->_name );

        if ( $params['membership_type'] ) {
            // we do this in case the user has hit the forward/back button
            require_once 'CRM/Member/DAO/MembershipBlock.php';
            $dao = new CRM_Member_DAO_MembershipBlock();
            $dao->entity_table = 'civicrm_contribution_page';
            $dao->entity_id = $this->_id; 
            $dao->find(true);
            $membershipID = $dao->id;
            if ( $membershipID ) {
                $params['id'] = $membershipID;
            }
            
            $membershipTypes = array();
            if ( is_array($params['membership_type']) ) {
                foreach( $params['membership_type'] as $k => $v) {
                    if ( $v ) {
                        $membershipTypes[] = $k;
                    }
                }
            }
            
            $params['membership_type_default']       =  CRM_Utils_Array::value( 'membership_type_default', $params, 'null' );
            $params['membership_types']              =  implode(',', $membershipTypes);
            $params['is_required']                   =  CRM_Utils_Array::value( 'is_required', $params, false );
            $params['is_active']                     =  CRM_Utils_Array::value( 'is_active', $params, false );
            $params['display_min_fee']               =  CRM_Utils_Array::value( 'display_min_fee', $params, false );
            $params['is_separate_payment']           =  CRM_Utils_Array::value( 'is_separate_payment', $params, false );
            $params['entity_table']                  = 'civicrm_contribution_page';
            $params['entity_id']                     =  $this->_id;
            
            $dao = new CRM_Member_DAO_MembershipBlock();
            $dao->copyValues($params);
            $dao->save();
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
        return ts( 'Memberships' );
    }
}

