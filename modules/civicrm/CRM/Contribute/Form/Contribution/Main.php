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

require_once 'CRM/Contribute/Form/ContributionBase.php';
require_once 'CRM/Core/Payment.php';

/**
 * This class generates form components for processing a ontribution 
 * 
 */
class CRM_Contribute_Form_Contribution_Main extends CRM_Contribute_Form_ContributionBase 
{
    /**
     *Define default MembershipType Id
     *
     */
    public $_defaultMemTypeId;

    protected $_defaults;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess()  
    {  
        parent::preProcess( );

        // make sure we have right permission to edit this user
        $csContactID = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this, false, $this->_userID );

        require_once 'CRM/Contact/BAO/Contact.php';
        if ( $csContactID != $this->_userID ) {
            require_once 'CRM/Contact/BAO/Contact/Permission.php';
            if ( CRM_Contact_BAO_Contact_Permission::validateChecksumContact( $csContactID, $this ) ) {
                $session = CRM_Core_Session::singleton( );
                $session->set( 'userID', $csContactID ) ;
                $this->_userID = $csContactID;
            }
        }

        if (  CRM_Utils_Array::value( 'id', $this->_pcpInfo )  && 
              CRM_Utils_Array::value( 'intro_text', $this->_pcpInfo ) ) {
            $this->assign( 'intro_text' , $this->_pcpInfo['intro_text'] );
        } else if ( CRM_Utils_Array::value( 'intro_text', $this->_values ) ) {
            $this->assign( 'intro_text' , $this->_values['intro_text'] );
        }
        
        if ( CRM_Utils_Array::value( 'footer_text', $this->_values ) ) {
            $this->assign( 'footer_text', $this->_values['footer_text'] );
        }

        //CRM-5001
        if ( $this->_values['is_for_organization'] ) {
            $msg = ts('Mixed profile not allowed for on behalf of registration/sign up.');
            require_once 'CRM/Core/BAO/UFGroup.php';
            if ( $preID = CRM_Utils_Array::value( 'custom_pre_id', $this->_values ) ) {
                $preProfile = CRM_Core_BAO_UFGroup::profileGroups( $preID );
                foreach ( array( 'Individual', 'Organization', 'Household' ) as $contactType ) {
                    if ( in_array( $contactType, $preProfile ) &&
                         ( in_array( 'Membership', $preProfile ) || 
                           in_array( 'Contribution', $preProfile ) ) ) {
                        CRM_Core_Error::fatal( $msg );   
                    }
                }
            }

            if ( $postID = CRM_Utils_Array::value( 'custom_post_id', $this->_values ) ) {
                $postProfile = CRM_Core_BAO_UFGroup::profileGroups( $postID );
                foreach ( array( 'Individual', 'Organization', 'Household' ) as $contactType ) {
                    if ( in_array( $contactType, $postProfile ) &&
                         ( in_array( 'Membership', $postProfile ) ||
                           in_array( 'Contribution', $postProfile ) ) ) {
                        CRM_Core_Error::fatal( $msg );
                    }
                }
            }
        }

    }

    function setDefaultValues( ) 
    {
        // process defaults only once
        if ( ! empty( $this->_defaults ) ) {
            // return $this->_defaults;
        }

        // check if the user is registered and we have a contact ID
        $session = CRM_Core_Session::singleton( );
        $contactID = $this->_userID;
        
        if ( $contactID ) {
            $options = array( );
            $fields = array( );
            require_once "CRM/Core/BAO/CustomGroup.php";
            $removeCustomFieldTypes = array ('Contribution', 'Membership');
            require_once 'CRM/Contribute/BAO/Contribution.php';
            $contribFields =& CRM_Contribute_BAO_Contribution::getContributionFields();
            
            // remove component related fields
            foreach ( $this->_fields as $name => $dontCare ) {
                //don't set custom data Used for Contribution (CRM-1344)
                if ( substr( $name, 0, 7 ) == 'custom_' ) {  
                    $id = substr( $name, 7 );
                    if ( ! CRM_Core_BAO_CustomGroup::checkCustomField( $id, $removeCustomFieldTypes )) {
                        continue;
                    }
                } else if ( array_key_exists( $name, $contribFields ) || (substr( $name, 0, 11 ) == 'membership_' ) ) { //ignore component fields
                    continue;
                }
                $fields[$name] = 1;
            }

            $names = array( "first_name", "middle_name", "last_name","street_address-{$this->_bltID}","city-{$this->_bltID}",
                            "postal_code-{$this->_bltID}","country_id-{$this->_bltID}","state_province_id-{$this->_bltID}"
                            );
            foreach ($names as $name) {
                $fields[$name] = 1;
            }
            $fields["state_province-{$this->_bltID}"] = 1;
            $fields["country-{$this->_bltID}"       ] = 1;
            $fields["email-{$this->_bltID}"         ] = 1;
            $fields["email-Primary"                 ] = 1;

            require_once "CRM/Core/BAO/UFGroup.php";
            CRM_Core_BAO_UFGroup::setProfileDefaults( $contactID, $fields, $this->_defaults );

            // use primary email address if billing email address is empty
            if ( empty( $this->_defaults["email-{$this->_bltID}"] ) &&
                 ! empty( $this->_defaults["email-Primary"] ) ) {
                $this->_defaults["email-{$this->_bltID}"] = $this->_defaults["email-Primary"];
            }

            foreach ($names as $name) {
                if ( ! empty( $this->_defaults[$name] ) ) {
                    $this->_defaults["billing_" . $name] = $this->_defaults[$name];
                }
            }
        } 
        
        //set custom field defaults set by admin if value is not set
        if ( ! empty( $this->_fields ) ) {
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
        }

        //set default membership for membershipship block
        require_once 'CRM/Member/BAO/Membership.php';
        if ( $this->_membershipBlock ) {
            $this->_defaults['selectMembership'] = 
                $this->_defaultMemTypeId ? $this->_defaultMemTypeId : 
                CRM_Utils_Array::value( 'membership_type_default', $this->_membershipBlock );
        }

        if ( $this->_membershipContactID ) {
            $this->_defaults['is_for_organization'] = 1;
            $this->_defaults['org_option'] = 1;
        } elseif ( $this->_values['is_for_organization'] ) {
            $this->_defaults['org_option'] = 0;
        }

        if ( $this->_values['is_for_organization'] && 
             ! isset($this->_defaults['location'][1]['email'][1]['email']) ) {
            $this->_defaults['location'][1]['email'][1]['email'] = 
                CRM_Utils_Array::value( "email-{$this->_bltID}",
                                        $this->_defaults );
        }

        //if contribution pay later is enabled and payment
        //processor is not available then freeze the pay later checkbox with
        //default check
        if ( CRM_Utils_Array::value( 'is_pay_later' , $this->_values ) &&
             empty ( $this->_paymentProcessor ) ) {
            $this->_defaults['is_pay_later'] = 1;
        }

//         // hack to simplify credit card entry for testing
//         $this->_defaults['credit_card_type']     = 'Visa';
//         $this->_defaults['amount']               = 168;
//         $this->_defaults['credit_card_number']   = '4807731747657838';
//         $this->_defaults['cvv2']                 = '000';
//         $this->_defaults['credit_card_exp_date'] = array( 'Y' => '2012', 'M' => '05' );

//         // hack to simplify direct debit entry for testing
//         $this->_defaults['account_holder'] = 'Max Müller';
//         $this->_defaults['bank_account_number'] = '12345678';
//         $this->_defaults['bank_identification_number'] = '12030000';
//         $this->_defaults['bank_name'] = 'Bankname';

        //build set default for pledge overdue payment.
        if ( CRM_Utils_Array::value( 'pledge_id', $this->_values ) ) {
            //get all payment statuses.
            $statuses = array( );
            $returnProperties = array( 'status_id' );
            CRM_Core_DAO::commonRetrieveAll( 'CRM_Pledge_DAO_Payment', 'pledge_id', $this->_values['pledge_id'],
                                             $statuses, $returnProperties );
            
            require_once 'CRM/Contribute/PseudoConstant.php';
            $paymentStatusTypes = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
            $duePayment = false;
            foreach ( $statuses as $payId => $value ) {
                if ( $paymentStatusTypes[$value['status_id']] == 'Overdue' ) {
                    $this->_defaults['pledge_amount'][$payId] = 1;
                } else if ( !$duePayment && $paymentStatusTypes[$value['status_id']] == 'Pending' ) {
                    $this->_defaults['pledge_amount'][$payId] = 1;
                    $duePayment = true;
                }
            }
        } else if ( CRM_Utils_Array::value( 'pledge_block_id', $this->_values ) ) {
            //set default to one time contribution.
            $this->_defaults['is_pledge'] = 0;  
        }

        // to process Custom data that are appended to URL
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $getDefaults = CRM_Core_BAO_CustomGroup::extractGetParams( $this, "'Contact', 'Individual', 'Contribution'" );
        if ( ! empty( $getDefaults ) ) {
            $this->_defaults = array_merge( $this->_defaults, $getDefaults );
        }

        $config = CRM_Core_Config::singleton( );
        // set default country from config if no country set
        if ( !CRM_Utils_Array::value("billing_country_id-{$this->_bltID}", $this->_defaults ) ) { 
            $this->_defaults["billing_country_id-{$this->_bltID}"] = $config->defaultContactCountry;
        }
        
        // now fix all state country selectors
        require_once 'CRM/Core/BAO/Address.php';
        CRM_Core_BAO_Address::fixAllStateSelects( $this, $this->_defaults );

        if ( $this->_priceSetId ) {
            foreach( $this->_priceSet['fields'] as $key => $val ) {
                foreach ( $val['options'] as $keys => $values ) {
                    if ( $values['is_default'] ) {
                        if ( $val['html_type'] == 'CheckBox') {
                            $this->_defaults["price_{$key}"][$keys] = 1;
                        } else {
                            $this->_defaults["price_{$key}"] = $keys;
                        }
                    }
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
        $config = CRM_Core_Config::singleton( );

        $this->applyFilter('__ALL__', 'trim');
        $this->add( 'text', "email-{$this->_bltID}",
                    ts( 'Email Address' ), array( 'size' => 30, 'maxlength' => 60 ), true );
        $this->addRule( "email-{$this->_bltID}", ts('Email is not valid.'), 'email' );
        
         //build pledge block.

        //don't build membership block when pledge_id is passed
        if ( ! CRM_Utils_Array::value( 'pledge_id', $this->_values ) ) {
            $this->_separateMembershipPayment = false;
            if ( in_array("CiviMember", $config->enableComponents) ) {
                $isTest = 0;
                if ( $this->_action & CRM_Core_Action::PREVIEW ) {
                    $isTest = 1;
                }
            
                require_once 'CRM/Member/BAO/Membership.php';
                $this->_separateMembershipPayment = 
                    CRM_Member_BAO_Membership::buildMembershipBlock( $this , 
                                                                     $this->_id , 
                                                                     true, null, false, 
                                                                     $isTest, $this->_membershipContactID );
            }
            $this->set( 'separateMembershipPayment', $this->_separateMembershipPayment );
        }
        
        // If we configured price set for contribution page
        // we are not allow membership signup as well as any
        // other contribution amount field, CRM-5095
        if ( isset($this->_priceSetId) && $this->_priceSetId ) {
            $this->add( 'hidden', 'priceSetId', $this->_priceSetId );
            // build price set form.
            $this->set( 'priceSetId', $this->_priceSetId );
            require_once 'CRM/Price/BAO/Set.php';
            CRM_Price_BAO_Set::buildPriceSet( $this );
        } else if ( CRM_Utils_Array::value( 'amount_block_is_active', $this->_values ) 
                    && ! CRM_Utils_Array::value( 'pledge_id', $this->_values ) ) {
            $this->buildAmount( $this->_separateMembershipPayment );
            
            if ( $this->_values['is_monetary'] &&
                 $this->_values['is_recur']    &&
                 $this->_paymentProcessor['is_recur'] ) {
                $this->buildRecur( );
            }
        }

        if ( CRM_Utils_Array::value( 'is_pay_later', $this->_values ) ) {
            $this->buildPayLater( );
        }

        if ( $this->_values['is_for_organization'] ) {
            $this->buildOnBehalfOrganization( );
        }
        
        //we allow premium for pledge during pledge creation only.
        if ( ! CRM_Utils_Array::value( 'pledge_id', $this->_values ) ) {
            require_once 'CRM/Contribute/BAO/Premium.php';
            CRM_Contribute_BAO_Premium::buildPremiumBlock( $this , $this->_id ,true );
        }
        
        if ( $this->_values['honor_block_is_active'] ) {
            $this->buildHonorBlock( );
        }
        
        //don't build pledge block when mid is passed
        if ( ! $this->_mid ) {  
            $config = CRM_Core_Config::singleton( );
            if ( in_array('CiviPledge', $config->enableComponents ) 
                && CRM_Utils_Array::value( 'pledge_block_id', $this->_values ) ) {
                require_once 'CRM/Pledge/BAO/PledgeBlock.php';
                CRM_Pledge_BAO_PledgeBlock::buildPledgeBlock( $this );
            }
        }

        $this->buildCustom( $this->_values['custom_pre_id'] , 'customPre'  );
        $this->buildCustom( $this->_values['custom_post_id'], 'customPost' );

        // doing this later since the express button type depends if there is an upload or not
        if ( $this->_values['is_monetary'] ) {
            require_once 'CRM/Core/Payment/Form.php';
            if (  $this->_paymentProcessor['payment_type'] & CRM_Core_Payment::PAYMENT_TYPE_DIRECT_DEBIT ) {
                CRM_Core_Payment_Form::buildDirectDebit( $this );
            } else {
                CRM_Core_Payment_Form::buildCreditCard( $this );
            }
        }

        //to create an cms user 
        if ( ! $this->_userID ) {
            $createCMSUser = false;
            if ( $this->_values['custom_pre_id'] ) {
                $profileID = $this->_values['custom_pre_id'];
                $createCMSUser = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup',  $profileID,'is_cms_user' );
            }
            if ( ! $createCMSUser &&
                 $this->_values['custom_post_id'] ) {
                $profileID = $this->_values['custom_post_id'];
                $createCMSUser = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', $profileID, 'is_cms_user' );
            }

            if ( $createCMSUser ) {
                require_once 'CRM/Core/BAO/CMSUser.php';
                CRM_Core_BAO_CMSUser::buildForm( $this, $profileID , true );
            }
        }
        if ( $this->_pcpId ) {
            require_once 'CRM/Contribute/BAO/PCP.php';
            if ( $pcpSupporter = CRM_Contribute_BAO_PCP::displayName( $this->_pcpId ) ) {
                $this->assign( 'pcpSupporterText' , ts('This contribution is being made thanks to effort of <strong>%1</strong>, who supports our campaign. You can support it as well - once you complete the donation, you will be able to create your own Personal Campaign Page!', array(1 => $pcpSupporter ) ) );
            }
            $this->assign( 'pcp', true );
            $this->add( 'checkbox', 'pcp_display_in_roll', ts('Show my contribution in the public honor roll'), null, null,
                        array('onclick' => "showHideByValue('pcp_display_in_roll','','nameID|nickID|personalNoteID','block','radio',false); pcpAnonymous( );")
                        );
            $extraOption = array('onclick' =>"return pcpAnonymous( );");
            $elements = array( );
            $elements[] =& $this->createElement('radio', null, '', ts( 'Include my name and message'), 0, $extraOption );
            $elements[] =& $this->createElement('radio', null, '', ts( 'List my contribution anonymously'), 1, $extraOption );
            $this->addGroup( $elements, 'pcp_is_anonymous', null, '&nbsp;&nbsp;&nbsp;' );
            $this->_defaults['pcp_is_anonymous'] = 0;
            
            $this->add( 'text', 'pcp_roll_nickname', ts('Name'), array( 'maxlength' => 30 ) );
            $this->add( 'textarea', "pcp_personal_note", ts( 'Personal Note' ), array( 'style' => 'height: 3em; width: 40em;' ) );
        }
        
        if ( !( $this->_paymentProcessor['billing_mode'] == CRM_Core_Payment::BILLING_MODE_BUTTON &&
                !$this->_values['is_pay_later'] ) ) {
            $this->addButtons(array( 
                                    array ( 'type'      => 'upload',
                                            'name'      => ts('Confirm Contribution'), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    ) 
                              );
        }
        
        $this->addFormRule( array( 'CRM_Contribute_Form_Contribution_Main', 'formRule' ), $this );
    }

    /**
     * build the radio/text form elements for the amount field
     *
     * @return void
     * @access private
     */
    function buildAmount( $separateMembershipPayment = false ) 
    {
        $elements = array( );
        if ( ! empty( $this->_values['amount'] ) ) {
            // first build the radio boxes
            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::buildAmount( 'contribution', $this, $this->_values['amount'] );
            
            require_once 'CRM/Utils/Money.php';
            foreach ( $this->_values['amount'] as $amount ) {
                $elements[] =& $this->createElement('radio', null, '',
                                                    CRM_Utils_Money::format( $amount['value']) . ' ' . $amount['label'],
                                                    $amount['amount_id'],
                                                    array('onclick'=>'clearAmountOther();'));
            }
        }

        if ( $separateMembershipPayment ) {
            $elements[''] = $this->createElement('radio',null,null,ts('No thank you'),'no_thanks', array('onclick'=>'clearAmountOther();') );
            $this->assign('is_separate_payment', true); 
        }

        if ( isset( $this->_values['default_amount_id'] ) ) {
            $this->_defaults['amount'] = $this->_values['default_amount_id'];
        }
        $title = ts('Contribution Amount');
        if ( $this->_values['is_allow_other_amount'] ) {
            if ( ! empty($this->_values['amount'] ) ) {
                $elements[] =& $this->createElement('radio', null, '',
                                                    ts('Other Amount'), 'amount_other_radio');

                $this->addGroup( $elements, 'amount', $title, '<br />' );

                if ( ! $separateMembershipPayment ) {
                    $this->addRule( 'amount', ts('%1 is a required field.', array(1 => ts('Amount'))), 'required' );
                }
                $this->add('text', 'amount_other', ts( 'Other Amount' ), array( 'size' => 10, 'maxlength' => 10, 'onfocus'=>'useAmountOther();') );
            } else {
                if ( $separateMembershipPayment ) {
                    $title = ts('Additional Contribution');
                }
                $this->add('text', 'amount_other', $title, array( 'size' => 10, 'maxlength' => 10, 'onfocus'=>'useAmountOther();'));
                if ( ! $separateMembershipPayment ) {
                    $this->addRule( 'amount_other', ts('%1 is a required field.', array(1 => $title)), 'required' );
                }
            }

            $this->assign( 'is_allow_other_amount', true );

            $this->addRule( 'amount_other', ts( 'Please enter a valid amount (numbers and decimal point only).' ), 'money' );
        } else {
            if ( ! empty($this->_values['amount'] ) ) {
                if ( $separateMembershipPayment ) {
                    $title = ts('Additional Contribution');
                }
                $this->addGroup( $elements, 'amount', $title, '<br />' );
            
                if ( ! $separateMembershipPayment ) {
                    $this->addRule( 'amount', ts('%1 is a required field.', array(1 => ts('Amount'))), 'required' );
                }
            }
            $this->assign( 'is_allow_other_amount', false );
        }
    }
    

    /**  
     * Function to add the honor block
     *  
     * @return None  
     * @access public  
     */ 
    function buildHonorBlock(  ) {
        $this->assign("honor_block_is_active",true);
        $this->set("honor_block_is_active",true);

        $this->assign("honor_block_title", CRM_Utils_Array::value( 'honor_block_title', $this->_values ));
        $this->assign("honor_block_text", CRM_Utils_Array::value( 'honor_block_text', $this->_values ) );

        $attributes  = CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact');
        $extraOption = array('onclick' =>"enableHonorType();");
        // radio button for Honor Type
        $honorOptions = array( );
        $honor = CRM_Core_PseudoConstant::honor( ); 
        foreach ($honor as $key => $var) {
            $honorTypes[$key] = HTML_QuickForm::createElement('radio', null, null, $var, $key, $extraOption );
        }
        $this->addGroup($honorTypes, 'honor_type_id', null);
        
        // prefix
        $this->addElement('select', 'honor_prefix_id', ts('Prefix'), array('' => ts('- prefix -')) + CRM_Core_PseudoConstant::individualPrefix());
        // first_name
        $this->addElement('text', 'honor_first_name', ts('First Name'), $attributes['first_name'] );
        
        //last_name
        $this->addElement('text', 'honor_last_name', ts('Last Name'), $attributes['last_name'] );
        
        //email
        $this->addElement('text', 'honor_email', ts('Email Address'));
        $this->addRule( "honor_email", ts('Honoree Email is not valid.'), 'email' );
    }

    /**
     * build elements to enable pay on behalf of an organization.
     *
     * @access public
     */
    function buildOnBehalfOrganization( ) 
    {
        if ( $this->_membershipContactID ) {
            require_once 'CRM/Core/BAO/Location.php';
            $entityBlock = array( 'contact_id' => $this->_membershipContactID );
            CRM_Core_BAO_Location::getValues( $entityBlock, $this->_defaults );
        }

        require_once 'CRM/Contact/BAO/Contact/Utils.php';
        if ( $this->_values['is_for_organization'] != 2 ) {
            $attributes = array('onclick' => 
                                "return showHideByValue('is_for_organization','true','for_organization','block','radio',false);");
            $this->addElement( 'checkbox', 'is_for_organization', 
                               $this->_values['for_organization'], 
                               null, $attributes );
        } else {
            $this->addElement( 'hidden', 'is_for_organization', true );
        }
        $this->assign( 'is_for_organization', true);
        CRM_Contact_BAO_Contact_Utils::buildOnBehalfForm( $this, 'Organization', null, 
                                                          null, 'Organization Details' );
    }

    /**
     * build elements to enable pay later functionality
     *
     * @access public
     */
    function buildPayLater( ) 
    {

        $attributes = null;
        $this->assign( 'hidePaymentInformation', false );
                   
        if ( !in_array( $this->_paymentProcessor['billing_mode'], array( 2, 4 ) ) && 
             $this->_values['is_monetary'] && is_array( $this->_paymentProcessor ) ) {
            $attributes = array('onclick' => "return showHideByValue('is_pay_later','','payment_information',
                                                     'block','radio',true);");
            
            $this->assign( 'hidePaymentInformation', true );
        }
        //hide the paypal exress button and show continue button
        if ( $this->_paymentProcessor['payment_processor_type'] == 'PayPal_Express' ) {
            $attributes = array('onclick' => "showHidePayPalExpressOption();" );
        }
        
        $element = $this->addElement( 'checkbox', 'is_pay_later', 
                                      $this->_values['pay_later_text'], null, $attributes );
        //if payment processor is not available then freeze
        //the paylater checkbox with default checked.
        if ( empty ( $this->_paymentProcessor ) ) {
            $element->freeze();
        }
    }

    /** 
     * build elements to collect information for recurring contributions
     *
     * @access public
     */
    function buildRecur( ) {
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_ContributionRecur' );
        $extraOption = array('onclick' =>"enablePeriod();");
        $elements = array( );
      	$elements[] =& $this->createElement('radio', null, '', ts( 'I want to make a one-time contribution.'), 0, $extraOption );
      	$elements[] =& $this->createElement('radio', null, '', ts( 'I want to contribute this amount'), 1, $extraOption );
        $this->addGroup( $elements, 'is_recur', null, '<br />' );
        $this->_defaults['is_recur'] = 0;
        
        if ( $this->_values['is_recur_interval'] ) {
            $this->add( 'text', 'frequency_interval', ts( 'Every' ),
                        $attributes['frequency_interval'] );
            $this->addRule( 'frequency_interval', ts( 'Frequency must be a whole number (EXAMPLE: Every 3 months).' ), 'integer' );
        } else {
            // make sure frequency_interval is submitted as 1 if given
            // no choice to user.
            $this->add( 'hidden', 'frequency_interval', 1 );
        }
        
        $units    = array( );
        $unitVals = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $this->_values['recur_frequency_unit'] );
        $frequencyUnits = CRM_Core_OptionGroup::values( 'recur_frequency_units' );
        foreach ( $unitVals as $key => $val ) {
            if ( array_key_exists( $val, $frequencyUnits ) ) {
                $units[$val] = $this->_values['is_recur_interval'] ? "{$frequencyUnits[$val]}(s)" : $frequencyUnits[$val];
            }
        }

        $frequencyUnit =& $this->add( 'select', 'frequency_unit', null, $units );
        
        // FIXME: Ideally we should freeze select box if there is only
        // one option but looks there is some problem /w QF freeze.
        //if ( count( $units ) == 1 ) {
        //$frequencyUnit->freeze( );
        //}
        
        $this->add( 'text', 'installments', ts( 'installments' ),
                    $attributes['installments'] );
        $this->addRule( 'installments', ts( 'Number of installments must be a whole number.' ), 'integer' );
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
        $amount = self::computeAmount( $fields, $self );
        
        //check for atleast one pricefields should be selected
        if ( CRM_Utils_Array::value( 'priceSetId', $fields ) ) {
            $priceField = new CRM_Price_DAO_Field( );
            $priceField->price_set_id = $fields['priceSetId'];
            $priceField->find( );
            
            $check = array( );
            
            while ( $priceField->fetch( ) ) {
                if ( ! empty( $fields["price_{$priceField->id}"] ) ) {
                    $check[] = $priceField->id; 
                }
            }
            
            if ( empty( $check ) ) {
                $errors['_qf_default'] = ts( "Select at least one option from Contribution(s)." );
            }
            
            require_once 'CRM/Price/BAO/Set.php';
            CRM_Price_BAO_Set::processAmount( $self->_values['fee'], 
                                              $fields, $lineItem );
            if ($fields['amount'] < 0) {
                $errors['_qf_default'] = ts( "Contribution can not be less than zero. Please select the options accordingly" );
            }
            $amount = $fields['amount'];
        }
        
        if ( isset( $fields['selectProduct'] ) &&
             $fields['selectProduct'] != 'no_thanks' &&
             $self->_values['amount_block_is_active'] ) {
            require_once 'CRM/Contribute/DAO/Product.php';
            require_once 'CRM/Utils/Money.php';
            $productDAO = new CRM_Contribute_DAO_Product();
            $productDAO->id = $fields['selectProduct'];
            $productDAO->find(true);
            $min_amount = $productDAO->min_contribution;
            
            if ( $amount < $min_amount ) {
                $errors['selectProduct'] = ts('The premium you have selected requires a minimum contribution of %1', array(1 => CRM_Utils_Money::format($min_amount)));
            }
        }

        if ( $self->_values["honor_block_is_active"] && CRM_Utils_Array::value( 'honor_type_id', $fields ) ) {
            // make sure there is a first name and last name if email is not there
            if ( ! CRM_Utils_Array::value( 'honor_email' , $fields ) ) {
                if ( !  CRM_Utils_Array::value( 'honor_first_name', $fields ) ||
                     !  CRM_Utils_Array::value( 'honor_last_name' , $fields ) ) {
                    $errors['honor_last_name'] = ts('In Honor Of - First Name and Last Name, OR an Email Address is required.');
                }
            }
        }

        if ( isset( $fields['is_recur'] ) && $fields['is_recur'] ) {
            if ( $fields['frequency_interval'] <= 0 ) {
                $errors['frequency_interval'] = ts('Please enter a number for how often you want to make this recurring contribution (EXAMPLE: Every 3 months).'); 
            }
            if ( $fields['frequency_unit'] == '0' ) {
                $errors['frequency_unit'] = ts('Please select a period (e.g. months, years ...) for how often you want to make this recurring contribution (EXAMPLE: Every 3 MONTHS).'); 
            }
        }

        if ( CRM_Utils_Array::value( 'is_recur', $fields ) && $fields['is_pay_later'] ) {
            $errors['is_pay_later'] = ' ';
            $errors['_qf_default'] = ts('You cannot set up a recurring contribution if you are not paying online by credit card.'); 
        }

        if ( CRM_Utils_Array::value( 'is_for_organization', $fields ) ) {
            if ( CRM_Utils_Array::value( 'org_option',$fields ) && ! $fields['onbehalfof_id'] ) {
                $errors['organization_id'] = ts('Please select an organization or enter a new one.'); 
            }
            if ( !CRM_Utils_Array::value( 'org_option',$fields ) && ! $fields['organization_name'] ) {
                $errors['organization_name'] = ts('Please enter the organization name.'); 
            }
            if ( ! $fields['email'][1]['email'] ) {
                $errors["email[1][email]"] = ts('Organization email is required.'); 
            }
        }

        if ( CRM_Utils_Array::value('selectMembership', $fields) && 
             $fields['selectMembership'] != 'no_thanks') {
            require_once 'CRM/Member/BAO/Membership.php';
            require_once 'CRM/Member/BAO/MembershipType.php';
            $memTypeDetails = CRM_Member_BAO_MembershipType::getMembershipTypeDetails( $fields['selectMembership']);
            if ( $self->_values['amount_block_is_active'] &&
                 ! CRM_Utils_Array::value( 'is_separate_payment', $self->_membershipBlock ) ) {
                require_once 'CRM/Utils/Money.php';
                if ( $amount < CRM_Utils_Array::value('minimum_fee',$memTypeDetails) ) {
                    $errors['selectMembership'] =
                        ts('The Membership you have selected requires a minimum contribution of %1',
                           array( 1 => CRM_Utils_Money::format($memTypeDetails['minimum_fee'] ) ) );
                }
            } else if( CRM_Utils_Array::value( 'minimum_fee', $memTypeDetails ) ) {
                // we dont have an amount, so lets get an amount for cc checks
                $amount = $memTypeDetails['minimum_fee'];
            }
        }
        
        if ( $self->_values['is_monetary'] ) {
            //validate other amount.
            $checkOtherAmount = false;
            if ( CRM_Utils_Array::value('amount', $fields ) == 'amount_other_radio' || CRM_Utils_Array::value( 'amount_other', $fields ) ) {
                $checkOtherAmount = true;
            }
            $otherAmountVal = CRM_Utils_Array::value( 'amount_other', $fields );
            if ( $checkOtherAmount || $otherAmountVal ) {
                if ( !$otherAmountVal ) {
                    $errors['amount_other'] = ts('Amount is required field.');
                }
                //validate for min and max.
                if ( $otherAmountVal ) {
                    $min = CRM_Utils_Array::value( 'min_amount', $self->_values );
                    $max = CRM_Utils_Array::value('max_amount',  $self->_values );
                    if ( $min && $otherAmountVal < $min ) {
                        $errors['amount_other'] = ts( 'Contribution amount must be at least %1', 
                                                      array ( 1 => $min ) );
                    }
                    if ( $max && $otherAmountVal > $max ) {
                        $errors['amount_other'] = ts( 'Contribution amount cannot be more than %1.',
                                                      array ( 1 => $max ) );
                    }
                }
            }
        }
        
        // validate PCP fields - if not anonymous, we need a nick name value
        if ( $self->_pcpId && CRM_Utils_Array::value('pcp_display_in_roll',$fields) &&
             ( CRM_Utils_Array::value('pcp_is_anonymous',$fields) == 0 ) &&
               CRM_Utils_Array::value('pcp_roll_nickname',$fields) == '') {
             $errors['pcp_roll_nickname'] = ts( 'Please enter a name to include in the Honor Roll, or select \'contribute anonymously\'.');
        }
        
        // return if this is express mode
        $config = CRM_Core_Config::singleton( );
        if ( $self->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_BUTTON ) {
            if ( CRM_Utils_Array::value( $self->_expressButtonName . '_x', $fields ) ||
                 CRM_Utils_Array::value( $self->_expressButtonName . '_y', $fields ) ||
                 CRM_Utils_Array::value( $self->_expressButtonName       , $fields ) ) {
                return $errors;
            }
        }
        
        //validate the pledge fields.
        if ( CRM_Utils_Array::value( 'pledge_block_id', $self->_values ) ) {
            //validation for pledge payment.
            if (  CRM_Utils_Array::value( 'pledge_id', $self->_values ) ) {
                if ( empty( $fields['pledge_amount'] ) ) {
                    $errors['pledge_amount'] = ts( 'At least one payment option needs to be checked.' );
                }
            } else if ( CRM_Utils_Array::value( 'is_pledge', $fields ) ) { 
                if ( CRM_Utils_Rule::positiveInteger( CRM_Utils_Array::value( 'pledge_installments', $fields ) ) == false ) {
                    $errors['pledge_installments'] = ts('Please enter a valid pledge installment.');
                } else {
                    if ( CRM_Utils_Array::value( 'pledge_installments', $fields ) == null) {
                        $errors['pledge_installments'] = ts( 'Pledge Installments is required field.' ); 
                    } else if (  CRM_Utils_array::value( 'pledge_installments', $fields ) == 1 ) {
                        $errors['pledge_installments'] = ts('Pledges consist of multiple scheduled payments. Select one-time contribution if you want to make your gift in a single payment.');
                    } else if (  CRM_Utils_array::value( 'pledge_installments', $fields ) == 0 ) {
                        $errors['pledge_installments'] = ts('Pledge Installments field must be > 1.');
                    }
                }
                
                //validation for Pledge Frequency Interval.
                if ( CRM_Utils_Rule::positiveInteger( CRM_Utils_Array::value( 'pledge_frequency_interval', $fields ) ) == false ) {
                    $errors['pledge_frequency_interval'] = ts('Please enter a valid Pledge Frequency Interval.');
                } else {
                    if ( CRM_Utils_Array::value( 'pledge_frequency_interval', $fields ) == null) {
                        $errors['pledge_frequency_interval'] = ts( 'Pledge Frequency Interval. is required field.' ); 
                    } else if ( CRM_Utils_array::value( 'pledge_frequency_interval', $fields ) == 0 )  {
                        $errors['pledge_frequency_interval'] = ts( 'Pledge frequency interval field must be > 0' );
                    }
                }
            }
        }
        
        // also return if paylater mode
        if ( CRM_Utils_Array::value( 'is_pay_later', $fields ) ) {
            return empty( $errors ) ? true : $errors;
        }
        
        // if the user has chosen a free membership or the amount is less than zero
        // i.e. we skip calling the payment processor and hence dont need credit card
        // info
        if ( (float ) $amount <= 0.0 ) {
            return $errors;
        }

        foreach ( $self->_fields as $name => $fld ) {
            if ( $fld['is_required'] &&
                 CRM_Utils_System::isNull( CRM_Utils_Array::value( $name, $fields ) ) ) {
                $errors[$name] = ts( '%1 is a required field.', array( 1 => $fld['title'] ) );
            }
        }

        // make sure that credit card number and cvv are valid
        require_once 'CRM/Utils/Rule.php';
        if ( CRM_Utils_Array::value( 'credit_card_type', $fields ) ) {
            if ( CRM_Utils_Array::value( 'credit_card_number', $fields ) &&
                 ! CRM_Utils_Rule::creditCardNumber( $fields['credit_card_number'], $fields['credit_card_type'] ) ) {
                $errors['credit_card_number'] = ts( "Please enter a valid Credit Card Number" );
            }
            
            if ( CRM_Utils_Array::value( 'cvv2', $fields ) &&
                 ! CRM_Utils_Rule::cvv( $fields['cvv2'], $fields['credit_card_type'] ) ) {
                $errors['cvv2'] =  ts( "Please enter a valid Credit Card Verification Number" );
            }
        }

        $elements = array( 'email_greeting'  => 'email_greeting_custom', 
                           'postal_greeting' => 'postal_greeting_custom',
                           'addressee'       => 'addressee_custom' ); 
        foreach ( $elements as $greeting => $customizedGreeting ) {
            if( $greetingType = CRM_Utils_Array::value($greeting, $fields) ) {
                $customizedValue = CRM_Core_OptionGroup::getValue( $greeting, 'Customized', 'name' ); 
                if( $customizedValue  == $greetingType && 
                    ! CRM_Utils_Array::value( $customizedGreeting, $fields ) ) {
                    $errors[$customizedGreeting] = ts( 'Custom %1 is a required field if %1 is of type Customized.', 
                                                       array( 1 => ucwords(str_replace('_'," ", $greeting) ) ) );
                }
            }
        }
        
        return empty( $errors ) ? true : $errors;
    }

    public function computeAmount( &$params, &$form ) 
    {
        $amount = null;

        // first clean up the other amount field if present
        if ( isset( $params['amount_other'] ) ) {
            $params['amount_other'] = CRM_Utils_Rule::cleanMoney( $params['amount_other'] );
        }
        
        if ( CRM_Utils_Array::value('amount',$params) == 'amount_other_radio' ||
             CRM_Utils_Array::value( 'amount_other', $params )  ) {
            $amount = $params['amount_other'];
        } else if  ( !empty( $params['pledge_amount'] ) ) {
            $amount = 0;
            foreach ( $params['pledge_amount'] as $paymentId => $dontCare ) {
                $amount += CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Payment', $paymentId, 'scheduled_amount' );
            } 
        } else {
            if ( CRM_Utils_Array::value( 'amount', $form->_values ) ) {
                $amountID = CRM_Utils_Array::value( 'amount', $params );
                
                if ( $amountID ) {
                    $params['amount_level'] =
                        $form->_values['amount'][$amountID]['label'];
                    $amount = 
                        $form->_values['amount'][$amountID]['value'];
                }
            }
        }
        return $amount;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {   
        $config = CRM_Core_Config::singleton( );
    
        // we first reset the confirm page so it accepts new values
        $this->controller->resetPage( 'Confirm' );

        // get the submitted form values. 
        $params = $this->controller->exportValues( $this->_name );

        if ( CRM_Utils_Array::value( 'onbehalfof_id', $params ) ) {
            $params['organization_id'] = $params['onbehalfof_id'];
        }
        
        $params['currencyID']     = $config->defaultCurrency;

        $params['amount'] = self::computeAmount( $params, $this );
        $memFee = null;
        if ( CRM_Utils_Array::value( 'selectMembership', $params ) ) {
            $membershipTypeValues = CRM_Member_BAO_Membership::buildMembershipTypeValues( $this,
                                                                                          $params['selectMembership'] );
            $memFee = $membershipTypeValues['minimum_fee'];
            if ( !$params['amount'] && !$this->_separateMembershipPayment ) {
                $params['amount'] = $memFee ? $memFee : 0;
            }
        }

        if ( ! isset( $params['amount_other'] ) ) {
            $this->set( 'amount_level',  CRM_Utils_Array::value( 'amount_level', $params ) ); 
        }

        if ( $priceSetId = CRM_Utils_Array::value( 'priceSetId', $params ) ) {
            $lineItem = array( );
            require_once 'CRM/Price/BAO/Set.php';
            CRM_Price_BAO_Set::processAmount( $this->_values['fee'], $params, $lineItem[$priceSetId] );
            $this->set( 'lineItem', $lineItem );
        }
        $this->set( 'amount', $params['amount'] ); 
        
        // generate and set an invoiceID for this transaction
        $invoiceID = md5(uniqid(rand(), true));
        $this->set( 'invoiceID', $invoiceID );

        // required only if is_monetary and valid postive amount 
        if ( $this->_values['is_monetary'] &&
             is_array( $this->_paymentProcessor ) &&
             ( (float ) $params['amount'] > 0.0 || $memFee > 0.0 ) ) {
            
            // default mode is direct
            $this->set( 'contributeMode', 'direct' ); 
            
            if ( $this->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_BUTTON ) {
                //get the button name  
                $buttonName = $this->controller->getButtonName( );  
                if ( in_array( $buttonName, 
                               array( $this->_expressButtonName, $this->_expressButtonName. '_x', $this->_expressButtonName. '_y' ) ) && 
                     ! isset( $params['is_pay_later'] )) { 
                    $this->set( 'contributeMode', 'express' ); 
                    
                    $donateURL = CRM_Utils_System::url( 'civicrm/contribute', '_qf_Contribute_display=1' ); 
                    $params['cancelURL' ] = CRM_Utils_System::url( 'civicrm/contribute/transact', "_qf_Main_display=1&qfKey={$params['qfKey']}", true, null, false ); 
                    $params['returnURL' ] = CRM_Utils_System::url( 'civicrm/contribute/transact', "_qf_Confirm_display=1&rfp=1&qfKey={$params['qfKey']}", true, null, false ); 
                    $params['invoiceID' ] = $invoiceID;

                    //default action is Sale
                    $params['payment_action'] = 'Sale';
                    
                    $payment =& CRM_Core_Payment::singleton( $this->_mode, $this->_paymentProcessor, $this ); 
                    $token = $payment->setExpressCheckout( $params ); 
                    if ( is_a( $token, 'CRM_Core_Error' ) ) { 
                        CRM_Core_Error::displaySessionError( $token ); 
                        CRM_Utils_System::redirect( $params['cancelURL' ] );
                    } 
                    
                    $this->set( 'token', $token ); 
                    
                    $paymentURL = $this->_paymentProcessor['url_site'] . "/cgi-bin/webscr?cmd=_express-checkout&token=$token"; 
                    CRM_Utils_System::redirect( $paymentURL ); 
                }
            } else if ( $this->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_NOTIFY ) {
                $this->set( 'contributeMode', 'notify' );
            }
        }      
    }
    
}


