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

require_once 'CRM/Contribute/Form/ContributionPage.php';
require_once 'CRM/Contribute/PseudoConstant.php';
require_once 'CRM/Member/BAO/Membership.php';

/**
 * form to process actions on Membership
 */
class CRM_Member_Form_MembershipBlock extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * store membership price set id
     */
    protected $_memPriceSetId = null;
    
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
        if ( isset( $this->_id ) ) {
            $defaults = CRM_Member_BAO_Membership::getMembershipBlock( $this->_id );
        }

        // for membership_types
        if ( isset( $defaults['membership_types'] ) ) {
            $membershipType    = explode(',' , $defaults['membership_types'] );
            $newMembershipType = array();  
            foreach( $membershipType as $k => $v ) {
                $newMembershipType[$v] = 1;
                $defaults["auto_renew_$v"] = $defaults['auto_renew'][$v];
            }
            $defaults['membership_type'] = $newMembershipType;
        }

        $defaults['member_is_active'] = $defaults['is_active'];

        // Set Display Minimum Fee default to true if we are adding a new membership block
        if ( ! isset( $defaults['id'] ) ) {
            $defaults['display_min_fee'] = 1;
        } else {
            $this->assign('membershipBlockId', $defaults['id']);
        }
        require_once 'CRM/Price/BAO/Set.php';
        if ( $this->_id &&
           ( $priceSetId = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id, 3 )) ) {
           $defaults['member_price_set_id'] = $priceSetId;
           $this->_memPriceSetId = $priceSetId; 
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
            $this->addElement('checkbox', 'member_is_active', ts('Membership Section Enabled?') );
        
            $this->addElement('text', 'new_title', ts('Title - New Membership'), CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'new_title'));
            
            $this->addWysiwyg( 'new_text', ts('Introductory Message - New Memberships'),CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'new_text'));

            $this->addElement('text', 'renewal_title', ts('Title - Renewals'), CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'renewal_title'));

            $this->addWysiwyg( 'renewal_text', ts('Introductory Message - Renewals'),CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'renewal_text'));
            
            $this->addElement('checkbox', 'is_required', ts('Require Membership Signup') );
            $this->addElement('checkbox', 'display_min_fee', ts('Display Membership Fee') );
            $this->addElement('checkbox', 'is_separate_payment', ts('Separate Membership Payment') );
            
            $paymentProcessor = CRM_Core_PseudoConstant::paymentProcessor( false, false, 'is_recur = 1' );
            
            $paymentProcessorId = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', 
                                                               $this->_id, 'payment_processor_id' );
            $isRecur = false;
            $membership        = array();
            $membershipDefault = array();
            foreach ( $membershipTypes as $k => $v ) {
                $membership[]      = HTML_QuickForm::createElement('advcheckbox', $k , null, $v );
                $membershipDefault[] = HTML_QuickForm::createElement('radio',null ,null,null, $k );
                if ( is_array( $paymentProcessor ) && 
                     CRM_Utils_Array::value( $paymentProcessorId, $paymentProcessor ) ) {
                    $isRecur = true;
                    $autoRenew = CRM_Core_DAO::getFieldValue('CRM_Member_DAO_MembershipType', $k, 'auto_renew' );
                    $autoRenewOptions = array( );
                    if ( $autoRenew ) {
                        $autoRenewOptions = array( ts('Not offered'), ts('Give option'), ts('Required') );
                        $this->addElement('select', "auto_renew_$k", ts('Auto-renew'), $autoRenewOptions );
                        $this->_renewOption[$k] = $autoRenew;
                    } 
                } else {
                    $isRecur = false;
                }
            }
            
            $this->assign( 'is_recur', $isRecur );
            if ( isset( $this->_renewOption ) ) {
                $this->assign( 'auto_renew', $this->_renewOption );
            }
            $this->addGroup($membership, 'membership_type', ts('Membership Types'));
            $this->addGroup($membershipDefault, 'membership_type_default', ts('Membership Types Default'));
            
            $this->addFormRule(array('CRM_Member_Form_MembershipBlock', 'formRule') , $this->_id);
        }
        require_once 'CRM/Price/BAO/Set.php';
        $price = CRM_Price_BAO_Set::getAssoc( false, 'CiviMember');
        if ( CRM_Utils_System::isNull( $price ) ) {
            $this->assign('price', false );
        } else {
            $this->assign('price', true );
        }
        $this->add( 'select', 'member_price_set_id', ts( 'Membership Price Set' ), (array( '' => ts( '- none -' )) + $price) );
        
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
        if ( CRM_Utils_Array::value( 'member_is_active', $params ) ) {
            
            // don't allow price set w/ membership signup, CRM-5095 
            require_once 'CRM/Price/BAO/Set.php';
            if ( $contributionPageId && ( $setID = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $contributionPageId ) ) ) {

                $extends = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Set', $setID, 'extends' );
                if ( $extends != CRM_Core_Component::getComponentID( 'CiviMember' ) ) {
                    $errors['member_is_active'] = ts( 'You cannot enable both Membership Signup and a Contribution Price Set on the same online contribution page.' );  
                    return $errors;
                }
            }
            
            if ( $contributionPageId && CRM_Utils_Array::value('member_price_set_id', $params) && CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_ContributionPage', $contributionPageId, 'amount_block_is_active') ) {
                $errors['member_price_set_id'] = ts( 'You cannot use Membership Price Sets with the Contribution Amounts section. However, a membership price set may include additional fields for non-membership options that requires an additional fee (e.g. magazine subscription) or an additional voluntary contribution.' );
            }
            
            if ( CRM_Utils_Array::value('member_price_set_id', $params) ) {
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
                require_once 'CRM/Contribute/DAO/ContributionPage.php';
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
                        $membershipTypes[$k] = CRM_Utils_Array::value( "auto_renew_$k", $params );
                    }
                }
            }
            
            $params['is_required'] = CRM_Utils_Array::value( 'is_required', $params, false );
            $params['is_active']   = CRM_Utils_Array::value( 'member_is_active', $params, false );

            // check for price set.
            $priceSetID = CRM_Utils_Array::value( 'member_price_set_id', $params );
            if ( $priceSetID ) {
                $params['membership_type_default'] = $params['membership_types']    = 'null';
                $params['display_min_fee']         = $params['is_separate_payment'] = 'null';
            } else {
                $params['membership_type_default'] = CRM_Utils_Array::value( 'membership_type_default', $params, 'null' );
                $params['membership_types']        = serialize( $membershipTypes );
                $params['display_min_fee']         = CRM_Utils_Array::value( 'display_min_fee', $params, false );
                $params['is_separate_payment']     = CRM_Utils_Array::value( 'is_separate_payment', $params, false );
            }
            $params['entity_table'] = 'civicrm_contribution_page';
            $params['entity_id']    = $this->_id;
            
            $dao = new CRM_Member_DAO_MembershipBlock();
            $dao->copyValues($params);
            $dao->save();            
           
            if ( $priceSetID && $params['is_active'] ) {
                CRM_Price_BAO_Set::addTo( 'civicrm_contribution_page', $this->_id, $priceSetID );
            } else {
                if ( $this->_memPriceSetId ) {
                    CRM_Price_BAO_Set::removeFrom( 'civicrm_contribution_page', $this->_id );
                }
            }

        }
        parent::endPostProcess( );
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

