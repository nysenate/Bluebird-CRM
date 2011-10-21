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

require_once 'CRM/Contribute/Form/ContributionBase.php';

/**
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Contribute_Form_Contribution_Confirm extends CRM_Contribute_Form_ContributionBase 
{

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        $config = CRM_Core_Config::singleton( );
        parent::preProcess( );
        // lineItem isn't set until Register postProcess
        $this->_lineItem = $this->get( 'lineItem' );
        
        if ( $this->_contributeMode == 'express' ) {
            // rfp == redirect from paypal
            $rfp = CRM_Utils_Request::retrieve( 'rfp', 'Boolean',
                                                CRM_Core_DAO::$_nullObject, false, null, 'GET' );
            if ( $rfp ) {
                require_once 'CRM/Core/Payment.php'; 
                $payment =& CRM_Core_Payment::singleton( $this->_mode, $this->_paymentProcessor, $this );
                $expressParams = $payment->getExpressCheckoutDetails( $this->get( 'token' ) );

                $this->_params['payer'       ] = $expressParams['payer'       ];
                $this->_params['payer_id'    ] = $expressParams['payer_id'    ];
                $this->_params['payer_status'] = $expressParams['payer_status'];

                require_once 'CRM/Core/Payment/Form.php';
                CRM_Core_Payment_Form::mapParams( $this->_bltID, $expressParams, $this->_params, false );

                // fix state and country id if present
                if ( ! empty( $this->_params["billing_state_province_id-{$this->_bltID}"] ) && $this->_params["billing_state_province_id-{$this->_bltID}"] ) {
                    $this->_params["billing_state_province-{$this->_bltID}"] =
                        CRM_Core_PseudoConstant::stateProvinceAbbreviation( $this->_params["billing_state_province_id-{$this->_bltID}"] ); 
                }
                if ( ! empty( $this->_params["billing_country_id-{$this->_bltID}"] ) && $this->_params["billing_country_id-{$this->_bltID}"] ) {
                    $this->_params["billing_country-{$this->_bltID}"]        =
                        CRM_Core_PseudoConstant::countryIsoCode( $this->_params["billing_country_id-{$this->_bltID}"] ); 
                }

                // set a few other parameters for PayPal
                $this->_params['token']          = $this->get( 'token' );

                $this->_params['amount'        ] = $this->get( 'amount' );

                // we use this here to incorporate any changes made by folks in hooks
                $this->_params['currencyID'    ] = $config->defaultCurrency;

                $this->_params['payment_action'] = 'Sale';

                // also merge all the other values from the profile fields
                $values = $this->controller->exportValues( 'Main' );
                $skipFields = array( 'amount', 'amount_other',
                                     "billing_street_address-{$this->_bltID}",
                                     "billing_city-{$this->_bltID}",
                                     "billing_state_province_id-{$this->_bltID}",
                                     "billing_postal_code-{$this->_bltID}",
                                     "billing_country_id-{$this->_bltID}" );
                foreach ( $values as $name => $value ) {
                    // skip amount field
                    if ( ! in_array( $name, $skipFields ) ) {
                        $this->_params[$name] = $value;
                    }
                }
                $this->set( 'getExpressCheckoutDetails', $this->_params );
            } else {
                $this->_params = $this->get( 'getExpressCheckoutDetails' );
            }
        } else {
            $this->_params = $this->controller->exportValues( 'Main' );

            if ( !empty( $this->_params["billing_state_province_id-{$this->_bltID}"] ) ) {
                $this->_params["billing_state_province-{$this->_bltID}"] =
                    CRM_Core_PseudoConstant::stateProvinceAbbreviation( $this->_params["billing_state_province_id-{$this->_bltID}"] ); 
            }
            if ( ! empty( $this->_params["billing_country_id-{$this->_bltID}"] ) ) {
                $this->_params["billing_country-{$this->_bltID}"]        =
                    CRM_Core_PseudoConstant::countryIsoCode( $this->_params["billing_country_id-{$this->_bltID}"] ); 
            }
            
            if ( isset( $this->_params['credit_card_exp_date'] ) ) {
                $this->_params['year'   ]        = $this->_params['credit_card_exp_date']['Y'];  
                $this->_params['month'  ]        = $this->_params['credit_card_exp_date']['M'];  
            }
            $this->_params['ip_address']     = $_SERVER['REMOTE_ADDR']; 
            // hack for safari
            if ( $this->_params['ip_address'] == '::1' ) {
                $this->_params['ip_address'] = '127.0.0.1';
            }
            $this->_params['amount'        ] = $this->get( 'amount' );
            
            if ( $this->_params['amount'] ) { 
                require_once 'CRM/Core/OptionGroup.php';
                $this->_params['amount_level'  ] = CRM_Core_OptionGroup::optionLabel( "civicrm_contribution_page.amount.{$this->_id}",
                                                                                      $this->_params['amount'] );
            }

            $this->_params['currencyID'    ] = $config->defaultCurrency;
            $this->_params['payment_action'] = 'Sale';
        }

        // if onbehalf-of-organization
        if ( CRM_Utils_Array::value( 'hidden_onbehalf_profile', $this->_params ) ) {
            if ( CRM_Utils_Array::value( 'org_option', $this->_params ) && 
                 CRM_Utils_Array::value( 'organization_id', $this->_params ) ) {
                if ( CRM_Utils_Array::value( 'onbehalfof_id', $this->_params ) ) {
                    $this->_params['organization_id'] = $this->_params['onbehalfof_id'];
                }
            }
            
            $this->_params['organization_name'] = $this->_params['onbehalf']['organization_name'];
            $addressBlocks = array( 'street_address', 'city', 
                                    'state_province', 'postal_code', 'country' );

            $blocks = array( 'email', 'phone', 'im', 'url', 'openid' );
            
            foreach ( $this->_params['onbehalf'] as $loc => $value ) {
                list( $field, $locType, $typeId ) = explode( '-', $loc );

                if ( in_array( $field, $addressBlocks ) ) {
                    if ( $field == 'country' ) {
                        $value = CRM_Core_PseudoConstant::countryIsoCode( $value );
                    } else if ( $field == 'state_province' ) {
                        $value = CRM_Core_PseudoConstant::stateProvinceAbbreviation( $value );
                    }
                    $this->_params['onbehalf_location']['address'][$locType][$field]       = $value;
                    $this->_params['onbehalf_location']['address'][$locType]['is_primary'] = 1;
                } else if ( in_array( $field, $blocks ) ) {
                    if ( !$typeId || is_numeric( $typeId ) ) {
                        $blockName     = $fieldName = $field;
                        $locationType  = 'location_type_id';
                        $locationValue = $locType;
                        $locTypeId     = '';
                        
                        if ( $field == 'url' ) {
                            $blockName     = 'website';
                            $locationType  = 'website_type_id';
                            $locationValue = $this->_params['onbehalf']["{$loc}-website_type_id"];
                        } else if ( $field == 'im' ) {
                            $fieldName     = 'name';
                            $locTypeId     = 'provider_id';
                            $typeId        = $this->_params['onbehalf']["{$loc}-provider_id"];
                        } else if ( $field == 'phone' ) {
                            $locTypeId     = 'phone_type_id';
                        }
                        
                        $this->_params['onbehalf_location'][$blockName][$locType][$fieldName]    = $value;
                        $this->_params['onbehalf_location'][$blockName][$locType][$locationType] = $locationValue;
                        $this->_params['onbehalf_location'][$blockName][$locType]['is_primary']  = 1;
                        if ( $locTypeId ) {
                            $this->_params['onbehalf_location'][$blockName][$locType][$locTypeId] = $typeId;
                        }
                    }
                } else if ( strstr( $loc, 'custom' ) ) {
                    if ( $value  && isset($this->_params['onbehalf']["{$loc}_id"]) ) {
                        $value = $this->_params['onbehalf']["{$loc}_id"];
                    }
                    $this->_params['onbehalf_location']["{$loc}"] = $value;
                } else {
                    $this->_params['onbehalf_location'][$field]   = $value;
                }
            }
        } else if ( CRM_Utils_Array::value( 'is_for_organization', $this->_values ) ) {
            // no on behalf of an organization, CRM-5519 
            // so reset loc blocks from main params.
            foreach ( array('phone', 'email', 'address') as $blk ) {
                if ( isset( $this->_params[$blk] ) ) {
                    unset( $this->_params[$blk] );
                }
            }
        }

        // if auto renew checkbox is set, initiate a open-ended recurring membership 
        if ( ( CRM_Utils_Array::value( 'selectMembership', $this->_params ) ||
               CRM_Utils_Array::value ( 'priceSetId', $this->_params ) )            &&
             CRM_Utils_Array::value( 'is_recur',         $this->_paymentProcessor ) &&
             CRM_Utils_Array::value( 'auto_renew',       $this->_params )           &&
             !CRM_Utils_Array::value( 'is_recur',        $this->_params )           &&
             !CRM_Utils_Array::value( 'frequency_interval', $this->_params ) ) {

            $this->_params['is_recur'] = $this->_values['is_recur'] = 1;
            if ( CRM_Utils_Array::value ( 'priceSetId', $this->_params ) ) {
                require_once 'CRM/Price/BAO/Set.php';
                list( $this->_params['frequency_interval'], $this->_params['frequency_unit'] ) =
                CRM_Price_BAO_Set::getRecurDetails( $this->_params['priceSetId'] );            
            } else {
                // FIXME: set interval and unit based on selected membership type
                $this->_params['frequency_interval'] = 
                    CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType',
                                             $this->_params['selectMembership'], 'duration_interval' );
                $this->_params['frequency_unit']     = 
                    CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType',
                                             $this->_params['selectMembership'], 'duration_unit' );
            }    
        }
        
        if ( $this->_pcpId ) { 
            $this->_params['pcp_made_through_id'] = $this-> _pcpInfo['pcp_id'];
            $this->assign( 'pcpBlock', true );
            if ( CRM_Utils_Array::value( 'pcp_display_in_roll', $this->_params ) && 
                 ! CRM_Utils_Array::value( 'pcp_roll_nickname', $this->_params ) ) {
                $this->_params['pcp_roll_nickname'] = ts('Anonymous');
                $this->_params['pcp_is_anonymous'] = 1;
            } else {
                $this->_params['pcp_is_anonymous'] = 0;
            }                
            foreach ( array ( 'pcp_display_in_roll', 'pcp_is_anonymous', 'pcp_roll_nickname', 'pcp_personal_note' ) as $val ) {
                if ( CRM_Utils_Array::value( $val, $this->_params ) ) {
                    $this->assign( $val, $this->_params[$val]);
                }
            }
        }
        $this->_params['invoiceID'] = $this->get( 'invoiceID' );
        
        //carry campaign from profile.
        if ( array_key_exists( 'contribution_campaign_id', $this->_params ) ) {
            $this->_params['campaign_id'] = $this->_params['contribution_campaign_id'];
        }
        
        $this->set( 'params', $this->_params );
    }

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $this->assignToTemplate( );
        require_once 'CRM/Contribute/BAO/Premium.php';
        
        $params = $this->_params;
        $honor_block_is_active = $this->get( 'honor_block_is_active');
        // make sure we have values for it
        if ( $honor_block_is_active &&
             ( ( ! empty( $params['honor_first_name'] ) && ! empty( $params['honor_last_name'] ) ) ||
               ( ! empty( $params['honor_email'] ) ) ) ) {
            $this->assign('honor_block_is_active', $honor_block_is_active );
            $this->assign('honor_block_title', CRM_Utils_Array::value( 'honor_block_title', $this->_values ) );
          
            require_once 'CRM/Core/PseudoConstant.php';
            $prefix = CRM_Core_PseudoConstant::individualPrefix();
            $honor  = CRM_Core_PseudoConstant::honor( );             
            $this->assign('honor_type',       CRM_Utils_Array::value( $params['honor_type_id'], $honor ) );
            $this->assign('honor_prefix',     CRM_Utils_Array::value( $params['honor_prefix_id'], $prefix ) );
            $this->assign('honor_first_name', $params['honor_first_name']);
            $this->assign('honor_last_name',  $params['honor_last_name']);
            $this->assign('honor_email',      $params['honor_email']);
        }
        $this->assign('receiptFromEmail', CRM_Utils_Array::value( 'receipt_from_email', $this->_values ) );
        $amount_block_is_active = $this->get( 'amount_block_is_active');
        $this->assign('amount_block_is_active', $amount_block_is_active );

        if ( CRM_Utils_Array::value( 'selectProduct', $params ) && $params['selectProduct'] != 'no_thanks') {
            $option    = CRM_Utils_Array::value( 'options_'.$params['selectProduct'], $params );
            $productID = $params['selectProduct']; 
            CRM_Contribute_BAO_Premium::buildPremiumBlock( $this , $this->_id, false,
                                                           $productID, $option);
            $this->set('productID',$productID);
            $this->set('option',$option);
        }
        $config = CRM_Core_Config::singleton( );
        if ( in_array('CiviMember', $config->enableComponents) ) {
            if ( isset( $params['selectMembership'] ) &&
                 $params['selectMembership'] != 'no_thanks' ) {
                CRM_Member_BAO_Membership::buildMembershipBlock( $this,
                                                                 $this->_id,
                                                                 false,
                                                                 $params['selectMembership'],
                                                                 false, null,
                                                                 $this->_membershipContactID );
            } else {
                $this->assign('membershipBlock', false);
            }
        }
        $this->buildCustom( $this->_values['custom_pre_id'] , 'customPre' , true );
        $this->buildCustom( $this->_values['custom_post_id'], 'customPost', true );

        if ( CRM_Utils_Array::value( 'hidden_onbehalf_profile', $params ) ) {
            require_once 'CRM/Core/BAO/UFJoin.php'; 
            $ufJoinParams    = array( 'module'       => 'onBehalf',
                                      'entity_table' => 'civicrm_contribution_page',   
                                      'entity_id'    => $this->_id );   
            $OnBehalfProfile = CRM_Core_BAO_UFJoin::getUFGroupIds( $ufJoinParams );
            $profileId       = $OnBehalfProfile[0];

            $fieldTypes = array( 'Contact', 'Organization' );
            if ( is_array( $this->_membershipBlock ) && !empty( $this->_membershipBlock ) ) {
                $fieldTypes = array_merge( $fieldTypes, array( 'Membership' ) );
            } else {
                $fieldTypes = array_merge( $fieldTypes, array( 'Contribution' ) );
            }

            $this->buildCustom( $profileId, 'onbehalfProfile', true, true, $fieldTypes );
        }

        $this->_separateMembershipPayment = $this->get( 'separateMembershipPayment' );
        $this->assign( 'is_separate_payment', $this->_separateMembershipPayment );
        $this->assign( 'lineItem', $this->_lineItem );
        $this->assign( 'priceSetID', $this->_priceSetId );
        

        if ( $this->_paymentProcessor['payment_processor_type'] == 'Google_Checkout' 
             && !$this->_params['is_pay_later'] && ! ( $this->_amount == 0 ) ) {
            $this->_checkoutButtonName = $this->getButtonName( 'next', 'checkout' );
            $this->add('image',
                       $this->_checkoutButtonName,
                       $this->_paymentProcessor['url_button'],
                       array( 'class' => 'form-submit' ) );
            
            $this->addButtons(array(
                                    array ( 'type'      => 'back',
                                            'name'      => ts('<< Go Back')),
                                    )
                              );
        } else {
            if ( $this->_contributeMode == 'notify' || !$this->_values['is_monetary'] || 
                 $this->_amount <= 0.0              || $this->_params['is_pay_later'] ||
                 ( $this->_separateMembershipPayment && $this->_amount <= 0.0 ) ) {
                $contribButton = ts('Continue >>');
                $this->assign( 'button', 'Continue' );
            } else {
                $contribButton = ts('Make Contribution');
                $this->assign( 'button', 'Make Contribution' );
            }
            $this->addButtons(array(
                                    array ( 'type'      => 'next',
                                            'name'      => $contribButton,
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'isDefault' => true,
                                            'js'        => array( 'onclick' => "return submitOnce(this,'" . $this->_name . "','" . ts('Processing') ."');" )
                                            ),
                                    array ( 'type'      => 'back',
                                            'name'      => ts('Go Back')
                                            )
                                    )
                              );
        }
        
        $defaults = array( );
        $options = array( );
        $fields = array( );
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $removeCustomFieldTypes = array ('Contribution');
        foreach ( $this->_fields as $name => $dontCare ) {
            if ( $name == 'onbehalf' ) {
                foreach ( $dontCare as $key => $value ) {
                    $fields['onbehalf'][$key] = 1;
                }
            } else {
                $fields[$name] = 1;
            }
        }
        $fields["billing_state_province-{$this->_bltID}"] =
            $fields["billing_country-{$this->_bltID}"] = $fields["email-{$this->_bltID}"] = 1;

        $contact =  $this->_params;
        foreach ($fields as $name => $dontCare ) {
            if ( $name == 'onbehalf' ) {
                foreach ( $dontCare as $key => $value ) {
                    if ( isset($contact['onbehalf'][$key]) ) {
                        $defaults[$key] = $contact['onbehalf'][$key];
                    }
                    if ( isset($contact['onbehalf']["{$key}_id"]) ) {
                        $defaults["{$key}_id"] = $contact['onbehalf']["{$key}_id"];
                    }
                }       
            } else if ( isset( $contact[$name] ) ) {
                $defaults[$name] = $contact[$name];
                if ( substr( $name, 0, 7 ) == 'custom_' ) {
                    $timeField = "{$name}_time";
                    if ( isset( $contact[ $timeField ] ) ) {
                        $defaults[ $timeField ] = $contact[ $timeField ];
                    }
                    if ( isset( $contact["{$name}_id"] ) ) {
                        $defaults["{$name}_id"] = $contact["{$name}_id"];
                    }
                } else if ( in_array($name, array('addressee', 'email_greeting', 'postal_greeting') )
                         && CRM_Utils_Array::value($name.'_custom', $contact) ) { 
                    $defaults[$name.'_custom'] = $contact[$name.'_custom'];
                } 
            }
        }

        $this->assign('useForMember', $this->get('useForMember'));

        // now fix all state country selectors
        require_once 'CRM/Core/BAO/Address.php';
        CRM_Core_BAO_Address::fixAllStateSelects( $this, $defaults );
        
        $this->setDefaults( $defaults );

        $this->freeze();

    }

    /**
     * overwrite action, since we are only showing elements in frozen mode
     * no help display needed
     * @return int
     * @access public
     */
    function getAction( ) 
    {
        if ( $this->_action & CRM_Core_Action::PREVIEW ) {
            return CRM_Core_Action::VIEW | CRM_Core_Action::PREVIEW;
        } else {
            return CRM_Core_Action::VIEW;
        }
    }

    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return void
     */
    function setDefaultValues()
    {
        
    }

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess( )
    {
        $config = CRM_Core_Config::singleton( );
        require_once 'CRM/Contact/BAO/Contact.php';

        $contactID = $this->_userID;

        // add a description field at the very beginning
        $this->_params['description'] = ts( 'Online Contribution' ) . ': ' . 
            ( ( $this->_pcpInfo['title'] ) ? $this->_pcpInfo['title'] : $this->_values['title'] );
        
        // also add accounting code
        $this->_params['accountingCode'] = CRM_Utils_Array::value( 'accountingCode',
                                                                   $this->_values );

        // fix currency ID
        $this->_params['currencyID'] = $config->defaultCurrency;

        $premiumParams = $membershipParams = $tempParams = $params = $this->_params;
        
        //carry payment processor id.
        if ( $paymentProcessorId = CRM_Utils_Array::value( 'id', $this->_paymentProcessor ) ) {
            $this->_params['payment_processor_id'] = $paymentProcessorId;
            foreach ( array( 'premiumParams', 'membershipParams', 'tempParams', 'params' ) as $p ) {
                ${$p}['payment_processor_id'] = $paymentProcessorId;
            }
        }
        
        $now = date( 'YmdHis' );
        $fields = array( );
        
        if ( CRM_Utils_Array::value( 'image_URL', $params  ) ) {
            CRM_Contact_BAO_Contact::processImageParams( $params ) ;
        }

        // set email for primary location.
        $fields['email-Primary'] = 1;
        
        // don't create primary email address, just add it to billing location
        //$params["email-Primary"] = $params["email-{$this->_bltID}"];
        
        // get the add to groups
        $addToGroups = array( );
        
        // now set the values for the billing location.
        foreach ( $this->_fields as $name => $value ) {
            $fields[$name] = 1;

            // get the add to groups for uf fields
            if ( CRM_Utils_Array::value('add_to_group_id',$value) ) {
                $addToGroups[$value['add_to_group_id']] = $value['add_to_group_id'];
            }
        }
        
        if ( ! array_key_exists( 'first_name', $fields ) ) {
            $nameFields = array( 'first_name', 'middle_name', 'last_name' );
            foreach ( $nameFields as $name ) {
                $fields[$name] = 1;
                if ( array_key_exists( "billing_$name", $params ) ) {
                    $params[$name] = $params["billing_{$name}"];
                    $params['preserveDBName'] = true;
                }
            }
        }
        
        // billing email address
        $fields["email-{$this->_bltID}"] = 1;
        
        //unset the billing parameters if it is pay later mode
        //to avoid creation of billing location
        if ( $params['is_pay_later'] ) {
            $billingFields = array( 'billing_first_name',
                                    'billing_middle_name',
                                    'billing_last_name',
                                    "billing_street_address-{$this->_bltID}",
                                    "billing_city-{$this->_bltID}",
                                    "billing_state_province-{$this->_bltID}",
                                    "billing_state_province_id-{$this->_bltID}",
                                    "billing_postal_code-{$this->_bltID}",
                                    "billing_country-{$this->_bltID}",
                                    "billing_country_id-{$this->_bltID}"
                                    );

            foreach( $billingFields as $value ) {
                unset( $params[$value] );
                unset( $fields[$value] );
            }
        }

        // if onbehalf-of-organization contribution, take out
        // organization params in a separate variable, to make sure
        // normal behavior is continued. And use that variable to
        // process on-behalf-of functionality.
        if ( CRM_Utils_Array::value( 'hidden_onbehalf_profile', $this->_params ) ) {
            $behalfOrganization = array();
            $orgFields = array('organization_name', 'organization_id', 'org_option');
            foreach ( $orgFields as $fld ) {
                if ( array_key_exists( $fld, $params ) ) {
                    $behalfOrganization[$fld] = $params[$fld];
                    unset( $params[$fld] );
                }
            }

            if ( is_array( $params['onbehalf'] ) && !empty( $params['onbehalf'] ) ) {
                foreach ( $params['onbehalf'] as $fld => $values ) {
                    if ( !( strstr( $fld, '-' ) || strstr( $fld, 'custom_' ) ) ) {
                        if ( in_array( $fld, array( 'contribution_campaign_id', 'member_campaign_id' ) ) ) {
                            $fld = 'campaign_id';
                        }
                        $this->_params[$fld] = $values;
                    }
                }
            }
            
            if ( array_key_exists( 'onbehalf_location', $params ) && is_array( $params['onbehalf_location'] ) ) {
                foreach ( $params['onbehalf_location'] as $block => $vals ) {
                    $behalfOrganization[$block] = $vals;
                }
                unset( $params['onbehalf_location'] );
            }
        }
        
        // check for profile double opt-in and get groups to be subscribed
        require_once 'CRM/Core/BAO/UFGroup.php';
        $subscribeGroupIds = CRM_Core_BAO_UFGroup::getDoubleOptInGroupIds( $params, $contactID );

        // since we are directly adding contact to group lets unset it from mailing
        if ( !empty( $addToGroups ) ) {
            foreach( $addToGroups as $groupId ) {
                if ( isset( $subscribeGroupIds[$groupId] ) ) {
                    unset( $subscribeGroupIds[$groupId] );
                }
            }
        }
                        
        foreach ( $addToGroups as $k ) {
            if ( array_key_exists( $k, $subscribeGroupIds ) ) {
                unset( $addToGroups[$k] );
            }
        }

        if ( ! isset( $contactID ) ) {
            require_once 'CRM/Dedupe/Finder.php';
            $dupeParams = $params;
            if ( CRM_Utils_Array::value( 'onbehalf', $dupeParams ) ) {
                unset( $dupeParams['onbehalf'] );
            }

            $dedupeParams = CRM_Dedupe_Finder::formatParams($dupeParams, 'Individual');
            $dedupeParams['check_permission'] = false;
            $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual');

            // if we find more than one contact, use the first one
            $contact_id  = CRM_Utils_Array::value( 0, $ids );
            
            // CRM-8940 Do not reset greeting id's if we already have a contact
            if ( ! $contact_id ) {
                $greetingTypes = array( 'addressee'       => 'addressee_id', 
                                        'email_greeting'  => 'email_greeting_id', 
                                        'postal_greeting' => 'postal_greeting_id'
                                        );
            
                foreach( $greetingTypes  as $key => $value ) {
                    if( !array_key_exists( $key, $params ) ) {
                        $defaultGreetingTypeId = CRM_Core_OptionGroup::values( $key, null, null, null, 
                                                                               'AND is_default =1
                                                                                AND (filter = 1 OR filter = 0 )',
                                                                               'value' 
                                                                               );
                    
                        $params[$key] = key( $defaultGreetingTypeId );
                    }
                }
            }
            
            $contactID =& CRM_Contact_BAO_Contact::createProfileContact( $params,
                                                                         $fields,
                                                                         $contact_id,
                                                                         $addToGroups,
                                                                         null,
                                                                         null,
                                                                         true );
            $this->set( 'contactID', $contactID );
        } else {
            $ctype = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $contactID, 'contact_type');
            $contactID =& CRM_Contact_BAO_Contact::createProfileContact( $params,
                                                                         $fields,
                                                                         $contactID,
                                                                         $addToGroups,
                                                                         null,
                                                                         $ctype,
                                                                         true );
        }

        //get email primary first if exist
        $subscribtionEmail =  array ( 'email' => CRM_Utils_Array::value( 'email-Primary', $params ) ) ;
        if ( !$subscribtionEmail['email'] ) {            
            $subscribtionEmail['email'] = CRM_Utils_Array::value( "email-{$this->_bltID}", $params ) ;
        }
        // subscribing contact to groups
        if ( !empty( $subscribeGroupIds ) && $subscribtionEmail['email'] ) {
            require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
            CRM_Mailing_Event_BAO_Subscribe::commonSubscribe( $subscribeGroupIds, $subscribtionEmail, $contactID );
        }
        
        // If onbehalf-of-organization contribution / signup, add organization
        // and it's location.
        if ( isset( $params['hidden_onbehalf_profile'] ) && isset( $behalfOrganization['organization_name'] ) ) {
            $ufFields = array( );
            foreach ( $this->_fields['onbehalf'] as $name => $value ) {
                $ufFields[$name] = 1;
            }
            self::processOnBehalfOrganization( $behalfOrganization, $contactID, $this->_values, 
                                               $this->_params, $ufFields );
        }

        // lets store the contactID in the session
        // for things like tell a friend
        $session = CRM_Core_Session::singleton( );
        if ( ! $session->get( 'userID' ) ) {
            $session->set( 'transaction.userID', $contactID );
        } else {
            $session->set( 'transaction.userID', null );
        }

        $this->_useForMember = $this->get('useForMember');
        
        // store the fact that this is a membership and membership type is selected
        $processMembership = false;
        if ( ( CRM_Utils_Array::value( 'selectMembership', $membershipParams ) &&
               $membershipParams['selectMembership'] != 'no_thanks' ) ||
             $this->_useForMember ) {
            $processMembership = true;
            
            if ( !$this->_useForMember ) {
                $this->assign( 'membership_assign' , true );
                $this->set('membershipTypeID' , $this->_params['selectMembership']);
            }

            if( $this->_action & CRM_Core_Action::PREVIEW ) {
                $membershipParams['is_test'] = 1;
            }
            if ( $this->_params['is_pay_later'] ) {
                $membershipParams['is_pay_later'] = 1;
            }
        }

        if ( $processMembership ) {
            require_once 'CRM/Core/Payment/Form.php';
            CRM_Core_Payment_Form::mapParams( $this->_bltID, $this->_params, $membershipParams, true );

            // added new parameter for cms user contact id, needed to distinguish behaviour for on behalf of sign-ups
            if ( isset($this->_params['related_contact']) ) {
                $membershipParams['cms_contactID'] = $this->_params['related_contact'];
            } else {
                $membershipParams['cms_contactID'] = $contactID;
            } 
            
            //inherit campaign from contirb page.
            if ( !array_key_exists( 'campaign_id', $membershipParams ) ) {
                $membershipParams['campaign_id'] = CRM_Utils_Array::value( 'campaign_id', $this->_values );
            }
            
            if ( CRM_Utils_Array::value( 'member_campaign_id', $membershipParams['onbehalf'] ) ) {
                $this->_params['campaign_id'] = $membershipParams['onbehalf']['member_campaign_id'];
            }

            $customFieldsFormatted = $fieldTypes = array( );
            require_once 'CRM/Core/BAO/CustomField.php';
            if ( is_array( $membershipParams['onbehalf'] ) && !empty( $membershipParams['onbehalf'] ) ) {
                foreach ( $membershipParams['onbehalf'] as $key => $value ) {
                    if ( strstr( $key, 'custom_' ) ) {
                        $customFieldId = explode( '_', $key );
                        CRM_Core_BAO_CustomField::formatCustomField( $customFieldId[1], $customFieldsFormatted, $value, 
                                                                     'Membership', null, $contactID );
                    }
                }
                $fieldTypes = array( 'Contact', 'Organization', 'Membership' );
            }
            
            $priceFieldIds = $this->get( 'memberPriceFieldIDS' );
            
            if (!empty($priceFieldIds)) {
                $contributionTypeID = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Set', $priceFieldIds['id'], 'contribution_type_id' );
                unset($priceFieldIds['id']);
                   
                foreach ($priceFieldIds as $priceFieldId) {
                    if($id = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_FieldValue', $priceFieldId, 'membership_type_id' )){
                        $membershipTypeIds[] = $id;
                    }                                    
                }
                $membershipParams['selectMembership'] = $membershipTypeIds;
                $membershipParams['contribution_type_id'] = $contributionTypeID;
            }
            require_once 'CRM/Member/BAO/Membership.php';
            CRM_Member_BAO_Membership::postProcessMembership( $membershipParams, $contactID,
                                                              $this, $premiumParams, $customFieldsFormatted, 
                                                              $fieldTypes );  
            
        } else {
            // at this point we've created a contact and stored its address etc
            // all the payment processors expect the name and address to be in the 
            // so we copy stuff over to first_name etc. 
            $paymentParams      = $this->_params;
            $contributionTypeId = $this->_values['contribution_type_id'];

            $fieldTypes = array( );
            require_once 'CRM/Core/BAO/CustomField.php';
            if ( is_array( $paymentParams['onbehalf'] ) && !empty( $paymentParams['onbehalf'] ) ) {
                foreach ( $paymentParams['onbehalf'] as $key => $value ) {
                    if ( strstr( $key, 'custom_' ) ) {
                        $this->_params[$key] = $value;
                    }
                }
                $fieldTypes = array( 'Contact', 'Organization', 'Contribution' );
            }
            
            require_once 'CRM/Contribute/BAO/Contribution/Utils.php';
            CRM_Contribute_BAO_Contribution_Utils::processConfirm( $this, $paymentParams, 
                                                                   $premiumParams, $contactID, 
                                                                   $contributionTypeId, 
                                                                   'contribution',
                                                                   $fieldTypes );
        }
    }
    
    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcessPremium( $premiumParams, $contribution )
    {
        // assigning Premium information to receipt tpl
        $selectProduct = CRM_Utils_Array::value( 'selectProduct', $premiumParams );
        if ( $selectProduct &&
             $selectProduct != 'no_thanks' ) {
            $startDate = $endDate = "";
            $this->assign('selectPremium',  true );
            require_once 'CRM/Contribute/DAO/Product.php';
            $productDAO = new CRM_Contribute_DAO_Product();
            $productDAO->id = $selectProduct;
            $productDAO->find(true);
            $this->assign('product_name',  $productDAO->name );
            $this->assign('price', $productDAO->price);
            $this->assign('sku', $productDAO->sku);
            $this->assign('option', CRM_Utils_Array::value( 'options_'.$premiumParams['selectProduct'], $premiumParams ));
            
            $periodType = $productDAO->period_type;
            
            if ( $periodType ) {
                $fixed_period_start_day = $productDAO->fixed_period_start_day;
                $duration_unit          = $productDAO->duration_unit;
                $duration_interval      = $productDAO->duration_interval;
                if ( $periodType == 'rolling' ) {
                    $startDate = date('Y-m-d');
                } else if ($periodType == 'fixed') {
                    if ( $fixed_period_start_day ) {
                        $date  = explode('-', date('Y-m-d') );
                        $month     = substr( $fixed_period_start_day, 0, strlen($fixed_period_start_day)-2);
                        $day       = substr( $fixed_period_start_day,-2)."<br>";
                        $year      = $date[0];
                        $startDate = $year.'-'.$month.'-'.$day;
                    } else {
                        $startDate = date('Y-m-d');
                    }
                }
                
                $date  = explode('-', $startDate );
                $year  = $date[0];
                $month = $date[1];
                $day   = $date[2];
                
                switch ( $duration_unit ) {
                case 'year' :
                    $year  = $year   + $duration_interval;
                    break;
                case 'month':
                    $month = $month  + $duration_interval;
                    break;
                case 'day':
                    $day   = $day    + $duration_interval;
                    break;
                case 'week':
                    $day   = $day    + ($duration_interval * 7);
                }
                $endDate = date('Y-m-d H:i:s',mktime($hour, $minute, $second, $month, $day, $year));
                $this->assign('start_date',$startDate);
                $this->assign('end_date',$endDate);
            }
            
            require_once 'CRM/Contribute/DAO/Premium.php';
            $dao = new CRM_Contribute_DAO_Premium();
            $dao->entity_table = 'civicrm_contribution_page';
            $dao->entity_id    = $this->_id;
            $dao->find(true);
            $this->assign('contact_phone',$dao->premiums_contact_phone);
            $this->assign('contact_email',$dao->premiums_contact_email);
            
            //create Premium record
            require_once 'CRM/Utils/Date.php';
            $params = array(
                            'product_id'         => $premiumParams['selectProduct'],
                            'contribution_id'    => $contribution->id,
                            'product_option'     => CRM_Utils_Array::value( 'options_'.$premiumParams['selectProduct'], $premiumParams ),
                            'quantity'           => 1,
                            'start_date'         => CRM_Utils_Date::customFormat($startDate,'%Y%m%d'),
                            'end_date'           => CRM_Utils_Date::customFormat($endDate,'%Y%m%d'),
                            );

            //Fixed For CRM-3901
            require_once 'CRM/Contribute/DAO/ContributionProduct.php';
            $daoContrProd = new CRM_Contribute_DAO_ContributionProduct();
            $daoContrProd->contribution_id = $contribution->id;
            if ( $daoContrProd->find(true) ) {
                $params['id'] = $daoContrProd->id;
            }
            
            require_once 'CRM/Contribute/BAO/Contribution.php';
            CRM_Contribute_BAO_Contribution::addPremium($params);
        } else if ( $selectProduct == 'no_thanks' ) {
            //Fixed For CRM-3901
            require_once 'CRM/Contribute/DAO/ContributionProduct.php';
            $daoContrProd = new CRM_Contribute_DAO_ContributionProduct();
            $daoContrProd->contribution_id = $contribution->id;
            if ( $daoContrProd->find(true) ) {
                $daoContrProd->delete();  
            }
        }
    }

    /**
     * Process the contribution
     *
     * @return void
     * @access public
     */
    static function processContribution( &$form, 
                                         $params, 
                                         $result, 
                                         $contactID, 
                                         $contributionType,
                                         $deductibleMode = true, 
                                         $pending = false,
                                         $online = true ) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        $className = get_class( $form );
        $honorCId = $recurringContributionID = null;

        if ( $online && $form->get( 'honor_block_is_active' ) ) {
            $honorCId = $form->createHonorContact( );
        }
        $recurringContributionID = self::processRecurringContribution( $form, $params, $contactID, $online );
        
        if ( ! $online && isset($params['honor_contact_id'] ) ) {
            $honorCId = $params['honor_contact_id'];
        }
        
        $config = CRM_Core_Config::singleton( );
        if ( ! $online && isset($params['non_deductible_amount'] ) ) {
            $nonDeductibleAmount = $params['non_deductible_amount'];
        } else {
            $nonDeductibleAmount = $params['amount'];
        }
        if ( $online && $contributionType->is_deductible && $deductibleMode ) {
            $selectProduct = CRM_Utils_Array::value( 'selectProduct', $premiumParams );
            if ( $selectProduct &&
                 $selectProduct != 'no_thanks' ) {
                require_once 'CRM/Contribute/DAO/Product.php';
                $productDAO = new CRM_Contribute_DAO_Product();
                $productDAO->id = $selectProduct;
                $productDAO->find(true);
                if( $params['amount'] < $productDAO->price ){
                    $nonDeductibleAmount = $params['amount'];
                } else {
                    $nonDeductibleAmount = $productDAO->price;
                }
            } else {
                $nonDeductibleAmount = '0.00';
            }
        }

        $now = date( 'YmdHis' );    
        $receiptDate = CRM_Utils_Array::value( 'receipt_date', $params );
        if ( CRM_Utils_Array::value( 'is_email_receipt', $form->_values ) ) {
            $receiptDate = $now;
        }
        
        //get the contrib page id.
        $campaignId = $contributionPageId = null;
        if ( $online ) {
            $contributionPageId = $form->_id;
            $campaignId = CRM_Utils_Array::value( 'campaign_id', $params );
            if ( !array_key_exists( 'campaign_id', $params ) ) {
                $campaignId = CRM_Utils_Array::value( 'campaign_id', $form->_values );
            }
        } else {
            //also for offline we do support - CRM-7290
            $contributionPageId = CRM_Utils_Array::value( 'contribution_page_id', $params );
            $campaignId = CRM_Utils_Array::value( 'campaign_id', $params  );
        }
        
        // first create the contribution record
        $contribParams = array(
                               'contact_id'            => $contactID,
                               'contribution_type_id'  => $contributionType->id,
                               'contribution_page_id'  => $contributionPageId,
                               'receive_date'          => ( CRM_Utils_Array::value( 'receive_date',  $params ) ) ? CRM_Utils_Date::processDate( $params['receive_date'] ) : date( 'YmdHis' ),
                               'non_deductible_amount' => $nonDeductibleAmount,
                               'total_amount'          => $params['amount'],
                               'amount_level'          => CRM_Utils_Array::value( 'amount_level', $params ),
                               'invoice_id'            => $params['invoiceID'],
                               'currency'              => $params['currencyID'],
                               'source'                => ( ! $online || CRM_Utils_Array::value( 'source', $params ) ) ? 
                               CRM_Utils_Array::value( 'source', $params ) : CRM_Utils_Array::value( 'description', $params ),
                               'is_pay_later'          => CRM_Utils_Array::value( 'is_pay_later', $params, 0 ),
                               //configure cancel reason, cancel date and thankyou date 
                               //from 'contribution' type profile if included
                               'cancel_reason'         => CRM_Utils_Array::value( 'cancel_reason', $params, 0),
                               'cancel_date'           => isset( $params['cancel_date'] ) ? CRM_Utils_Date::format( $params['cancel_date'] ) : null,
                               'thankyou_date'         => isset( $params['thankyou_date'] ) ? CRM_Utils_Date::format( $params['thankyou_date'] ) : null,
                               'campaign_id'           => $campaignId,
                               );
        if ( ! $online && isset($params['thankyou_date'] ) ) {
            $contribParams['thankyou_date'] = $params['thankyou_date'];
        }
        
        if ( ! $online || $form->_values['is_monetary'] ) {
            if ( ! CRM_Utils_Array::value( 'is_pay_later', $params ) ) {
                $contribParams['payment_instrument_id'] = 1;
            }
        }
        
        if ( ! $pending && $result ) {
            $contribParams += array(
                                    'fee_amount'   => CRM_Utils_Array::value( 'fee_amount', $result ),
                                    'net_amount'   => CRM_Utils_Array::value( 'net_amount', $result, $params['amount'] ),
                                    'trxn_id'      => $result['trxn_id'],
                                    'receipt_date' => $receiptDate,
                                    // also add financial_trxn details as part of fix for CRM-4724
                                    'trxn_result_code' => CRM_Utils_Array::value( 'trxn_result_code', $result ),
                                    'payment_processor' => CRM_Utils_Array::value( 'payment_processor', $result )
                                    );
        }
        
        if ( isset($honorCId)  ) {
            $contribParams['honor_contact_id'] = $honorCId;
            $contribParams['honor_type_id']    = $params['honor_type_id'];
        }

        if ( $recurringContributionID ) {
            $contribParams['contribution_recur_id'] = $recurringContributionID;
        }

        $contribParams['contribution_status_id'] = $pending ? 2 : 1;

        $contribParams['is_test'] = 0;
        if ( $form->_mode == 'test' ) {
            $contribParams['is_test'] = 1;
        }
        
        $ids = array( );
        if ( isset( $contribParams['invoice_id'] ) ) {
            $contribID = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_Contribution',
                                                      $contribParams['invoice_id'],
                                                      'id',
                                                      'invoice_id' );
            if ( isset( $contribID ) ) {
                $ids['contribution'] = $contribID;
                $contribParams['id'] = $contribID;
            }
        }
        foreach ( array ('pcp_display_in_roll', 'pcp_roll_nickname', 'pcp_personal_note' ) as $val ) {
            if ( CRM_Utils_Array::value( $val, $params ) ) {
                $contribSoftParams[$val] = $params[$val];
            }
        }
        
        require_once 'CRM/Contribute/BAO/Contribution.php';
        
        //create an contribution address
        if ( $form->_contributeMode != 'notify' && !CRM_Utils_Array::value('is_pay_later', $params) ) {  
            $contribParams['address_id']  = CRM_Contribute_BAO_Contribution::createAddress( $params, $form->_bltID );
        }

        // CRM-4038: for non-en_US locales, CRM_Contribute_BAO_Contribution::add() expects localised amounts
        require_once 'CRM/Utils/Money.php';
        $contribParams['non_deductible_amount'] = trim(CRM_Utils_Money::format($contribParams['non_deductible_amount'], ' '));
        $contribParams['total_amount']          = trim(CRM_Utils_Money::format($contribParams['total_amount'], ' '));

        //add contribution record
        $contribution =& CRM_Contribute_BAO_Contribution::add( $contribParams, $ids );
        
        // process price set, CRM-5095
        if ( $contribution->id && $form->_priceSetId ) {
            require_once 'CRM/Contribute/Form/AdditionalInfo.php';
            CRM_Contribute_Form_AdditionalInfo::processPriceSet( $contribution->id, $form->_lineItem );
        }
        
        //add soft contribution due to pcp or Submit Credit / Debit Card Contribution by admin.
        if ( CRM_Utils_Array::value( 'pcp_made_through_id', $params ) || CRM_Utils_Array::value( 'soft_credit_to', $params ) ) { 
            $contribSoftParams['contribution_id'] = $contribution->id;
                       
            $contribSoftParams['amount']          = $params['amount'];

            //if its due to pcp
            if ( CRM_Utils_Array::value( 'pcp_made_through_id', $params ) ) {
                $contribSoftParams['pcp_id']          = $params['pcp_made_through_id'];
                $contribSoftParams['contact_id']      = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_PCP', 
                                                                                     $params['pcp_made_through_id'], 
                                                                                     'contact_id' );
            } else {
                $contribSoftParams['contact_id'] = CRM_Utils_Array::value( 'soft_credit_to', $params );
            }
            
            $softContribution = CRM_Contribute_BAO_Contribution::addSoftContribution( $contribSoftParams );
        }

        //handle pledge stuff.
        if ( !CRM_Utils_Array::value( 'separate_membership_payment', $form->_params ) &&
             CRM_Utils_Array::value('pledge_block_id', $form->_values ) && 
             ( CRM_Utils_Array::value('is_pledge', $form->_params ) ||
               CRM_Utils_Array::value('pledge_id', $form->_values ) ) ) {
                        
            if ( CRM_Utils_Array::value( 'pledge_id', $form->_values ) ) {
                
                //when user doing pledge payments.
                //update the schedule when payment(s) are made 
                require_once 'CRM/Pledge/BAO/Payment.php';
                foreach ( $form->_params['pledge_amount'] as $paymentId => $dontCare ) {
                    $scheduledAmount  =  CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Payment', 
                                                                       $paymentId,
                                                                       'scheduled_amount', 
                                                                       'id'
                                                                       );
                    
                    $pledgePaymentParams = array('id'              => $paymentId,
                                                 'contribution_id' => $contribution->id,
                                                 'status_id'       => $contribution->contribution_status_id,
                                                 'actual_amount'   => $scheduledAmount
                                                 );
                    
                    
                    CRM_Pledge_BAO_Payment::add( $pledgePaymentParams );
                }
                
                //update pledge status according to the new payment statuses
                CRM_Pledge_BAO_Payment::updatePledgePaymentStatus( $form->_values['pledge_id'] );
                
            } else {
                //when user creating pledge record.
                $pledgeParams                            = array( );
                $pledgeParams['contact_id'             ] = $contribution->contact_id;
                $pledgeParams['installment_amount'     ] = $pledgeParams['actual_amount'] = $contribution->total_amount;
                $pledgeParams['contribution_id'        ] = $contribution->id;
                $pledgeParams['contribution_page_id'   ] = $contribution->contribution_page_id;
                $pledgeParams['contribution_type_id'   ] = $contribution->contribution_type_id;
                $pledgeParams['frequency_interval'     ] = $params['pledge_frequency_interval'];
                $pledgeParams['installments'           ] = $params['pledge_installments'];
                $pledgeParams['frequency_unit'         ] = $params['pledge_frequency_unit'];
                $pledgeParams['frequency_day'          ] = 1;
                $pledgeParams['create_date'            ] = $pledgeParams['start_date'] = $pledgeParams['scheduled_date'] = date( "Ymd" );
                $pledgeParams['status_id'              ] = $contribution->contribution_status_id;
                $pledgeParams['max_reminders'          ] = $form->_values['max_reminders'];
                $pledgeParams['initial_reminder_day'   ] = $form->_values['initial_reminder_day'];
                $pledgeParams['additional_reminder_day'] = $form->_values['additional_reminder_day'];
                $pledgeParams['is_test'                ] = $contribution->is_test;
                $pledgeParams['acknowledge_date'       ] = date( 'Ymd' );
                $pledgeParams['original_installment_amount'] = $pledgeParams['installment_amount'] ;
                
                //inherit campaign from contirb page. 
                $pledgeParams['campaign_id']             = $campaignId;
                
                require_once 'CRM/Pledge/BAO/Pledge.php';
                $pledge = CRM_Pledge_BAO_Pledge::create( $pledgeParams );
                
                $form->_params['pledge_id'] = $pledge->id;  
                
                //send acknowledgment email. only when pledge is created
                if ( $pledge->id ) {
                    
                    //build params to send acknowledgment.
                    $pledgeParams['id'                 ] = $pledge->id;
                    $pledgeParams['receipt_from_name'  ] = $form->_values['receipt_from_name'];
                    $pledgeParams['receipt_from_email' ] = $form->_values['receipt_from_email'];
                    
                    //scheduled amount will be same as installment_amount.
                    $pledgeParams['scheduled_amount'   ] = $pledgeParams['installment_amount'];
                    
                    //get total pledge amount.
                    $pledgeParams['total_pledge_amount'] = $pledge->amount;
                    
                    require_once 'CRM/Pledge/BAO/Pledge.php';
                    CRM_Pledge_BAO_Pledge::sendAcknowledgment( $form, $pledgeParams );
                }
            }
        }
        
        if ( $online ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            CRM_Core_BAO_CustomValueTable::postProcess( $form->_params,
                                                        CRM_Core_DAO::$_nullArray,
                                                        'civicrm_contribution',
                                                        $contribution->id,
                                                        'Contribution' );
        } else {
            //handle custom data.
            $params['contribution_id'] = $contribution->id;
            if ( CRM_Utils_Array::value( 'custom', $params ) &&
                 is_array( $params['custom'] ) &&
                 !is_a( $contribution, 'CRM_Core_Error') ) {
                require_once 'CRM/Core/BAO/CustomValueTable.php';
                CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_contribution', $contribution->id );
            }
        }
        
        require_once 'CRM/Contribute/BAO/Contribution/Utils.php';

        if ( isset($params['related_contact']) ) {
            $contactID = $params['related_contact'];
        } else if ( isset($params['cms_contactID']) ) {
            $contactID = $params['cms_contactID'];
        }
        
        CRM_Contribute_BAO_Contribution_Utils::createCMSUser( $params,
                                                              $contactID, 
                                                              'email-' . $form->_bltID ); 
        
        // return if pending
        if ( $pending ) {
            return $contribution;
        }
        
        // next create the transaction record
        if ( ( ! $online || $form->_values['is_monetary'] ) && $result['trxn_id'] ) {
            $trxnParams = array(
                                'contribution_id'   => $contribution->id,
                                'trxn_date'         => $now,
                                'trxn_type'         => 'Debit',
                                'total_amount'      => $params['amount'],
                                'fee_amount'        => CRM_Utils_Array::value( 'fee_amount', $result ),
                                'net_amount'        => CRM_Utils_Array::value( 'net_amount', $result, $params['amount'] ),
                                'currency'          => $params['currencyID'],
                                'payment_processor' => $form->_paymentProcessor['payment_processor_type'],
                                'trxn_id'           => $result['trxn_id'],
                                'trxn_result_code'  => (isset($result['trxn_result_code']) ? $result['trxn_result_code'] : false),
                                );
            
            require_once 'CRM/Core/BAO/FinancialTrxn.php';
            $trxn =& CRM_Core_BAO_FinancialTrxn::create( $trxnParams );
        }
        
        //create contribution activity w/ individual and target
        //activity w/ organisation contact id when onbelf, CRM-4027 
        $targetContactID = null; 
        if ( CRM_Utils_Array::value( 'hidden_onbehalf_profile', $params ) ) {
            $targetContactID = $contribution->contact_id;  
            $contribution->contact_id = $contactID;
        }
        
        // create an activity record
        require_once 'CRM/Activity/BAO/Activity.php';
        CRM_Activity_BAO_Activity::addActivity( $contribution, null, $targetContactID );
        
        $transaction->commit( ); 

        return $contribution;
    }

    /**
     * Create the recurring contribution record
     *
     */
    function processRecurringContribution( &$form, &$params, $contactID, $online = true ) {
        // return if this page is not set for recurring
        // or the user has not chosen the recurring option
        
        //this is online case validation.
        if ( ( !CRM_Utils_Array::value( 'is_recur', $form->_values ) && $online ) ||
             ! CRM_Utils_Array::value( 'is_recur', $params ) ) {
            return null;
        }
        
        $recurParams = array( );
        $config = CRM_Core_Config::singleton( );
        $recurParams['contact_id']         = $contactID;
        $recurParams['amount']             = $params['amount'];
        $recurParams['frequency_unit']     = $params['frequency_unit'];
        $recurParams['frequency_interval'] = $params['frequency_interval'];
        $recurParams['installments']       = $params['installments'];
        
        $recurParams['is_test'] = 0;
        if ( ( $form->_action & CRM_Core_Action::PREVIEW ) || 
             ( isset( $form->_mode ) && ( $form->_mode == 'test' ) ) ) {
            $recurParams['is_test'] = 1;
        }
        
        $recurParams['start_date'] = $recurParams['create_date'] = $recurParams['modified_date'] = date( 'YmdHis' );
        if( CRM_Utils_Array::value( 'receive_date',$params ) ){
            $recurParams['start_date'] = CRM_Utils_Date::processDate( $params['receive_date'], 
                                                                      $params['receive_date_time'] );
        }
        $recurParams['invoice_id'] = $params['invoiceID'];
        $recurParams['contribution_status_id'] = 2;
        $recurParams['payment_processor_id']   = $params['payment_processor_id'];
    
        // we need to add a unique trxn_id to avoid a unique key error
        // in paypal IPN we reset this when paypal sends us the real trxn id, CRM-2991
        $recurParams['trxn_id'] = CRM_Utils_Array::value( 'trxn_id', $params, $params['invoiceID'] );
        
        $ids = array( ); 
        
        require_once 'CRM/Contribute/BAO/ContributionRecur.php';
        $recurring =& CRM_Contribute_BAO_ContributionRecur::add( $recurParams, $ids );
        if ( is_a( $recurring, 'CRM_Core_Error' ) ) {
            CRM_Core_Error::displaySessionError( $result );
            $urlString = 'civicrm/contribute/transact';
            $urlParams = '_qf_Main_display=true'; 
            if ( $className == 'CRM_Contributet_Form_Contribution' ) {
                $urlString = 'civicrm/contact/view/contribution';
                $urlParams = "action=add&cid={$form->_contactID}";
                if ( $form->_mode ) $urlParams .= "&mode={$form->_mode}"; 
            }
            CRM_Utils_System::redirect( CRM_Utils_System::url( $urlString, $urlParams ) );
        }
        
        return $recurring->id;
    }
    

    /**
     * Create the Honor contact
     *
     * @return void
     * @access public
     */
    function createHonorContact(  ) {
        $params = $this->controller->exportValues( 'Main' );
       
        // return if we dont have enough information
        if ( empty( $params['honor_first_name'] ) &&
             empty( $params['honor_last_name' ] ) &&
             empty( $params['honor_email'] ) ) {
            return null;
        }
        
        //assign to template for email reciept
        $honor_block_is_active = $this->get( 'honor_block_is_active');
        
        $this->assign('honor_block_is_active', $honor_block_is_active );
        $this->assign('honor_block_title', $this->_values['honor_block_title'] );
        
        require_once 'CRM/Core/PseudoConstant.php';
        $prefix = CRM_Core_PseudoConstant::individualPrefix();
        $honorType = CRM_Core_PseudoConstant::honor( );
        $this->assign('honor_type',       $honorType[$params['honor_type_id']]);
        $this->assign('honor_prefix',     $prefix[$params['honor_prefix_id']]);
        $this->assign('honor_first_name', $params['honor_first_name']);
        $this->assign('honor_last_name',  $params['honor_last_name']);
        $this->assign('honor_email',      $params['honor_email']);
        
        //create honoree contact
        require_once 'CRM/Contribute/BAO/Contribution.php';
        return CRM_Contribute_BAO_Contribution::createHonorContact( $params );
    }

    /**
     * Function to add on behalf of organization and it's location  
     *
     * @param $behalfOrganization array  array of organization info
     * @param $values             array  form values array
     * @param $contactID          int    individual contact id. One
     * who is doing the process of signup / contribution. 
     *
     * @return void
     * @access public
     */
    static function processOnBehalfOrganization( &$behalfOrganization, &$contactID, &$values, &$params, $fields = null ) {
        $isCurrentEmployer = false;
        if ( $behalfOrganization['organization_id'] && $behalfOrganization['org_option'] ) {
            $orgID = $behalfOrganization['organization_id'];
            unset($behalfOrganization['organization_id']);
            $isCurrentEmployer = true;
        }

        // formalities for creating / editing organization.
        $behalfOrganization['contact_type'] = 'Organization';
        foreach ( $behalfOrganization as $locFld => $value ) {
            if ( in_array( $locFld, array( 'phone', 'email', 'address' ) ) ) {
                $locTypeId = array_keys( $value );
                if ( $locTypeId[0] == 'Primary' ) {
                    $locTypeId[0] = 1;
                    $behalfOrganization[$locFld][$locTypeId[0]]['email'] = $value['Primary']['email'];
                    unset( $behalfOrganization[$locFld]['Primary'] );
                }
                $behalfOrganization[$locFld][$locTypeId[0]]['is_primary'] = 1;
                $behalfOrganization[$locFld][$locTypeId[0]]['location_type_id'] = $locTypeId[0];
            }
        }
        
        // get the relationship type id
        require_once 'CRM/Contact/DAO/RelationshipType.php';
        $relType = new CRM_Contact_DAO_RelationshipType();
        $relType->name_a_b = 'Employee of';
        $relType->find(true);
        $relTypeId = $relType->id;
        
        // keep relationship params ready
        $relParams['relationship_type_id']    = $relTypeId.'_a_b';
        $relParams['is_permission_a_b'   ]    = 1;
        $relParams['is_active'           ]    = 1;
        
        if ( ! $orgID ) {
            // check if matching organization contact exists
            require_once 'CRM/Dedupe/Finder.php';
            $dedupeParams = CRM_Dedupe_Finder::formatParams($behalfOrganization, 'Organization');
            $dedupeParams['check_permission'] = false;
            $dupeIDs      = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Organization', 'Strict');

            // CRM-6243 says to pick the first org even if more than one match
            if ( count($dupeIDs) >= 1 ) {
                $behalfOrganization['contact_id'] = $dupeIDs[0];
                // don't allow name edit
                unset($behalfOrganization['organization_name']);
            }
        } else {
            // if found permissioned related organization, allow location edit
            $behalfOrganization['contact_id'] = $orgID;
            // don't allow name edit
            unset($behalfOrganization['organization_name']);
        }
        // create organization, add location 
        $orgID = CRM_Contact_BAO_Contact::createProfileContact( $behalfOrganization, $fields, $orgID,
                                                                null, null, 'Organization' );
        // create relationship
        $relParams['contact_check'][$orgID] = 1;
        $cid = array( 'contact' => $contactID );
        $relationship = CRM_Contact_BAO_Relationship::create($relParams, $cid);
       
        // if multiple match - send a duplicate alert
        if ( $dupeIDs && (count($dupeIDs) > 1) ) {
            $values['onbehalf_dupe_alert'] = 1;
            // required for IPN
            $params['onbehalf_dupe_alert'] = 1;
        }
        
        // make sure organization-contact-id is considered for recording
        // contribution/membership etc..
        if ( $contactID != $orgID ) {
            // take a note of contact-id, so we can send the
            // receipt to individual contact as well.

            // required for mailing/template display ..etc 
            $values['related_contact'] = $contactID;
            // required for IPN
            $params['related_contact'] = $contactID;
            
            //make this employee of relationship as current
            //employer / employee relationship,  CRM-3532
            if ( $isCurrentEmployer && 
                 ( $orgID != CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactID, 'employer_id') ) ) {
                $isCurrentEmployer = false;
            }
            
            if ( !$isCurrentEmployer && $orgID ) {
                //build current employer params
                $currentEmpParams[$contactID] = $orgID;
                require_once 'CRM/Contact/BAO/Contact/Utils.php';
                CRM_Contact_BAO_Contact_Utils::setCurrentEmployer( $currentEmpParams );
            }
           
            // contribution / signup will be done using this
            // organization id.
            $contactID = $orgID;
        }
    }
}
