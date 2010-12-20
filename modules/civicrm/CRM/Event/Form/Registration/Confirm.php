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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Event/Form/Registration.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_Registration_Confirm extends CRM_Event_Form_Registration
{
    /**
     * the values for the contribution db object
     *
     * @var array
     * @protected
     */
    public $_values;

    /**
     * the total amount
     *
     * @var float
     * @public
     */
    public $_totalAmount;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        parent::preProcess( );
        
        // lineItem isn't set until Register postProcess
        $this->_lineItem = $this->get( 'lineItem' );
        $this->_params = $this->get( 'params' );

        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::eventDiscount( $this, $this->_params );

        if ( CRM_Utils_Array::value( 'discount', $this->_params[0] ) &&
             CRM_Utils_Array::value( 'applied', $this->_params[0]['discount'] ) ) {
            $this->set( 'hookDiscount', $this->_params[0]['discount'] );
            $this->assign( 'hookDiscount', $this->_params[0]['discount'] );
        }

        $config = CRM_Core_Config::singleton( );
        if ( $this->_contributeMode == 'express' ) {
            $params = array(); 
            // rfp == redirect from paypal
            $rfp = CRM_Utils_Request::retrieve( 'rfp', 'Boolean',
                                                CRM_Core_DAO::$_nullObject, false, null, 'GET' );
           
            //we lost rfp in case of additional participant. So set it explicitly.
            if ( $rfp || CRM_Utils_Array::value( 'additional_participants', $this->_params[0], false ) ) {
                require_once 'CRM/Core/Payment.php'; 
                $payment =& CRM_Core_Payment::singleton( $this->_mode, $this->_paymentProcessor, $this );
                $expressParams = $payment->getExpressCheckoutDetails( $this->get( 'token' ) );
                             
                $params['payer'       ] = $expressParams['payer'       ];
                $params['payer_id'    ] = $expressParams['payer_id'    ];
                $params['payer_status'] = $expressParams['payer_status'];

                require_once 'CRM/Core/Payment/Form.php';
                CRM_Core_Payment_Form::mapParams( $this->_bltID, $expressParams, $params, false );
                
                // fix state and country id if present
                if ( isset( $params["billing_state_province_id-{$this->_bltID}"] ) ) {
                    $params["billing_state_province-{$this->_bltID}"] =
                        CRM_Core_PseudoConstant::stateProvinceAbbreviation( $params["billing_state_province_id-{$this->_bltID}"] ); 
                }
                if ( isset( $params['billing_country_id'] ) ) {
                    $params["billing_country-{$this->_bltID}"] =
                        CRM_Core_PseudoConstant::countryIsoCode( $params["billing_country_id-{$this->_bltID}"] ); 
                }

                // set a few other parameters for PayPal
                $params['token']           = $this->get( 'token' );
                $params['amount'        ]  = $this->_params[0]['amount'];
                if ( CRM_Utils_Array::value( 'discount', $this->_params[0] ) ) {
                    $params['discount'      ]  = $this->_params[0]['discount'];
                    $params['discountAmount']  = $this->_params[0]['discountAmount'];
                    $params['discountMessage'] = $this->_params[0]['discountMessage'];
                }
                $params['amount_level'  ]  = $this->_params[0]['amount_level'];
                $params['currencyID'    ]  = $this->_params[0]['currencyID'];
                $params['payment_action']  = 'Sale';
                
                // also merge all the other values from the profile fields
                $values = $this->controller->exportValues( 'Register' );
                $skipFields = array( 'amount',
                                     "street_address-{$this->_bltID}",
                                     "city-{$this->_bltID}",
                                     "state_province_id-{$this->_bltID}",
                                     "postal_code-{$this->_bltID}",
                                     "country_id-{$this->_bltID}" );

                foreach ( $values as $name => $value ) {
                    // skip amount field
                    if ( ! in_array( $name, $skipFields ) ) {
                        $params[$name] = $value;
                    }
                }
                $this->set( 'getExpressCheckoutDetails', $params );
            } else {
                $params = $this->get( 'getExpressCheckoutDetails' );
            }
            $this->_params[0] = $params;
            $this->_params[0]['is_primary'] = 1; 
        } else {
            //process only primary participant params.
            $registerParams = $this->_params[0];
            if ( isset( $registerParams["billing_state_province_id-{$this->_bltID}"] ) 
                 && $registerParams["billing_state_province_id-{$this->_bltID}"] ) {
                $registerParams["billing_state_province-{$this->_bltID}"] =
                    CRM_Core_PseudoConstant::stateProvinceAbbreviation( $registerParams["billing_state_province_id-{$this->_bltID}"] ); 
            }
            
            if ( isset( $registerParams["billing_country_id-{$this->_bltID}"] ) && $registerParams["billing_country_id-{$this->_bltID}"] ) {
                $registerParams["billing_country-{$this->_bltID}"] =
                    CRM_Core_PseudoConstant::countryIsoCode( $registerParams["billing_country_id-{$this->_bltID}"] ); 
            }
            if ( isset( $registerParams['credit_card_exp_date'] ) ) {
                $registerParams['year'   ]        = $registerParams['credit_card_exp_date']['Y'];  
                $registerParams['month'  ]        = $registerParams['credit_card_exp_date']['M'];  
            }
            if ( $this->_values['event']['is_monetary'] ) {
                $registerParams['ip_address']     = CRM_Utils_System::ipAddress( );
                $registerParams['currencyID'    ] = $this->_params[0]['currencyID'];
                $registerParams['payment_action'] = 'Sale';
            }
            //assign back primary participant params.
            $this->_params[0] = $registerParams;
        }
        
        if ( $this->_values['event']['is_monetary'] ) {
            $this->_params[0]['invoiceID'] = $this->get( 'invoiceID' );
        }
        $this->assign('defaultRole', false );
        if ( CRM_Utils_Array::value( 'defaultRole', $this->_params[0] ) == 1 ) {
            $this->assign( 'defaultRole', true );
        }
        
        if ( ! CRM_Utils_Array::value( 'participant_role_id', $this->_params[0] ) &&
             $this->_values['event']['default_role_id'] ) {
            $this->_params[0]['participant_role_id'] = $this->_values['event']['default_role_id'];
        }
        
        if ( isset ($this->_values['event']['confirm_title'] ) ) {
            CRM_Utils_System::setTitle($this->_values['event']['confirm_title']);
        }
        $this->set( 'params', $this->_params );
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
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    { 
        $this->assignToTemplate( );
        if( $this->_params[0]['amount'] || $this->_params[0]['amount'] == 0 ) {
            $this->_amount = array();

            foreach( $this->_params as $k => $v ) {
                if ( is_array( $v ) ) {
                    foreach (array ('first_name', 'last_name') as $name) {
                        if(isset($v['billing_'.$name])) {
                            $v[$name] = $v['billing_'.$name];
                            
                        }
                    }
                    if ( CRM_Utils_Array::value( "email-{$this->_bltID}", $v ) ) {
                        $append = $v["email-{$this->_bltID}"];
                    } else {
                        $append = $v['first_name'] .' ' . $v['last_name'];  
                    }
                    $this->_amount[$k]['amount'] = $v['amount'];
                    if ( CRM_Utils_Array::value( 'discountAmount', $v ) ) {
                        $this->_amount[$k]['amount'] -= $v['discountAmount'];
                    }
                        
                    $this->_amount[$k]['label'] = $v['amount_level'].'  -  '. $append;
                    $this->_part[$k]['info'] = CRM_Utils_Array::value( 'first_name', $v ) . ' ' . CRM_Utils_Array::value( 'last_name', $v );
                    if ( !CRM_Utils_Array::value( "first_name", $v ) ) {
                        $this->_part[$k]['info'] = $append;
                    }
                    $this->_totalAmount = $this->_totalAmount + $this->_amount[$k]['amount'];
                    if ( CRM_Utils_Array::value( 'is_primary', $v ) ) {
                        $this->set( 'primaryParticipantAmount', $this->_amount[$k]['amount'] );
                    }
                }
            }
            
            $this->assign('part', $this->_part);
            $this->set( 'part', $this->_part );
            $this->assign('amount', $this->_amount);
            $this->assign('totalAmount', $this->_totalAmount);
            $this->set( 'totalAmount', $this->_totalAmount );
        }

        $config = CRM_Core_Config::singleton( );
        
        $this->buildCustom( $this->_values['custom_pre_id'] , 'customPre' , true );
        $this->buildCustom( $this->_values['custom_post_id'], 'customPost', true );
        
        $this->assign( 'lineItem', $this->_lineItem );
        //display additional participants profile.
        require_once 'CRM/Event/BAO/Event.php';
        $participantParams = $this->_params;
        $formattedValues = array( );
        $count = 1;
        foreach ( $participantParams as $participantNum => $participantValue ) {
            if ( $participantNum && $participantValue != 'skip') {
                //get the customPre profile info
                if ( CRM_Utils_Array::value( 'additional_custom_pre_id', $this->_values ) ) {
                    $values = array( );
                    $groupName = array( );
                    CRM_Event_BAO_Event::displayProfile( $participantValue, 
                                                         $this->_values['additional_custom_pre_id'], 
                                                         $groupName, 
                                                         $values );
                    if ( count( $values ) ) {
                        $formattedValues[$count]['additionalCustomPre'] = $values;
                    }
                    $formattedValues[$count]['additionalCustomPreGroupTitle'] = CRM_Utils_Array::value( 'groupTitle', $groupName );
                }
                //get the customPost profile info
                if ( CRM_Utils_Array::value( 'additional_custom_post_id', $this->_values ) ) {
                    $values = array( );
                    $groupName = array( );
                    CRM_Event_BAO_Event::displayProfile( $participantValue, 
                                                         $this->_values['additional_custom_post_id'], 
                                                         $groupName, 
                                                         $values );
                    if ( count( $values ) ) {
                        $formattedValues[$count]['additionalCustomPost'] = $values;
                    }
                    $formattedValues[$count]['additionalCustomPost'] = array_diff_assoc( $formattedValues[$count]['additionalCustomPost'], $formattedValues[$count]['additionalCustomPre'] );
                    $formattedValues[$count]['additionalCustomPostGroupTitle'] = CRM_Utils_Array::value( 'groupTitle', $groupName );
                }
                $count++; 
            }
        }
        
        if ( ! empty( $formattedValues ) && $count > 1 ) {
            $this->assign( 'addParticipantProfile' , $formattedValues );
        }
        
        if( $this->_params[0]['amount'] == 0 ) {
            $this->assign( 'isAmountzero', 1 );
        }

        if ( $this->_paymentProcessor['payment_processor_type'] == 'Google_Checkout' && 
             ! CRM_Utils_Array::value( 'is_pay_later', $this->_params[0] ) && ! ( $this->_params[0]['amount'] == 0 ) &&
             !$this->_allowWaitlist && !$this->_requireApproval ) {
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
            $contribButton = ts('Continue >>');
            $this->addButtons(array(
                                    array ( 'type'      => 'back',
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'name'      => ts('<< Go Back'),
                                           ),
                                    array ( 'type'      => 'next',
                                            'name'      => $contribButton,
                                            'isDefault' => true,
                                            'js'        => array( 'onclick' => "return submitOnce(this,'" . $this->_name . "','" . ts('Processing') ."');" ),
                                           ),
                                    )
                                );
        }
        
        $defaults = array( );
        $fields = array( );
        if( ! empty( $this->_fields ) ) {
            foreach ( $this->_fields as $name => $dontCare ) {
                $fields[$name] = 1;
            }
        }
        $fields["billing_state_province-{$this->_bltID}"] =
            $fields["billing_country-{$this->_bltID}"] = $fields["email-{$this->_bltID}"] = 1;
      
        foreach ($fields as $name => $dontCare ) {
            if ( isset($this->_params[0][$name]) ) {
                $defaults[$name] = $this->_params[0][$name];
                if ( substr( $name, 0, 7 ) == 'custom_' ) {
                    $timeField = "{$name}_time";
                    if ( isset( $this->_params[0][ $timeField ] ) ) {
                        $defaults[ $timeField ] = $this->_params[0][ $timeField ];
                    }
                    if ( isset( $this->_params[0]["{$name}_id"] ) ) {
                        $defaults["{$name}_id"] = $this->_params[0]["{$name}_id"];
                    }
                } else if ( in_array($name, array('addressee', 'email_greeting', 'postal_greeting'))
                            && CRM_Utils_Array::value($name.'_custom', $this->_params[0]) ) { 
                    $defaults[$name.'_custom'] = $this->_params[0][$name.'_custom'];
                }
            }
        }
       
        // now fix all state country selectors
        require_once 'CRM/Core/BAO/Address.php';
        CRM_Core_BAO_Address::fixAllStateSelects( $this, $defaults );
        
        $this->setDefaults( $defaults );
        $this->freeze();
        
        //lets give meaningful status message, CRM-4320.
        $this->assign( 'isOnWaitlist', $this->_allowWaitlist );
        $this->assign( 'isRequireApproval', $this->_requireApproval );
        
        // Assign Participant Count to Lineitem Table
        require_once "CRM/Price/BAO/Set.php";
        $this->assign( 'pricesetFieldsCount', CRM_Price_BAO_Set::getPricesetCount( $this->_priceSetId ) );
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess( ) 
    {
        require_once 'CRM/Event/BAO/Participant.php';

        $now           = date( 'YmdHis' );
        $config        = CRM_Core_Config::singleton( );
        $session       = CRM_Core_Session::singleton( );
        $contactID     = parent::getContactID( );
        $this->_params = $this->get( 'params' );
        
        // if a discount has been applied, lets now deduct it from the amount
        // and fix the fee level
        if ( CRM_Utils_Array::value( 'discount', $this->_params[0] ) &&
             CRM_Utils_Array::value( 'applied', $this->_params[0]['discount'] ) ) {
            foreach( $this->_params as $k => $v ) {
                if ( CRM_Utils_Array::value( 'amount'        , $this->_params[$k] ) > 0 &&
                     CRM_Utils_Array::value( 'discountAmount', $this->_params[$k] ) ) {
                    $this->_params[$k]['amount'] -= $this->_params[$k]['discountAmount'];
                    $this->_params[$k]['amount_level'] .=
                        CRM_Utils_Array::value( 'discountMessage', $this->_params[$k] );
                }
            }
            $this->set( 'params', $this->_params );
        }
        
        // CRM-4320, lets build array of cancelled additional participant ids 
        // those are drop or skip by primary at the time of confirmation.
        // get all in and then unset those we want to process.
        $cancelledIds = $this->_additionalParticipantIds;
        
        $params = $this->_params;
        $this->set( 'finalAmount' ,$this->_amount );
        $participantCount = array( );
        
        //unset the skip participant from params.
        //build the $participantCount array.
        //maintain record for all participants.
        foreach ( $params as $participantNum => $record ) {
            if ( $record == 'skip' ) {
                unset( $params[$participantNum] );
                $participantCount[$participantNum] = 'skip';
            } else if ( $participantNum ) {
                $participantCount[$participantNum] = 'participant';
            }
            
            //lets get additional participant id to cancel.
            if ( $this->_allowConfirmation && is_array( $cancelledIds ) ) {
                $additonalId = CRM_Utils_Array::value( 'participant_id', $record );
                if ( $additonalId && $key = array_search( $additonalId, $cancelledIds ) ) {
                    unset( $cancelledIds[$key] ); 
                }
            }
        }
                
        $payment = $registerByID = $primaryCurrencyID = $contribution = null;
        foreach ( $params as $key => $value ) {
            $this->fixLocationFields( $value, $fields );
            //unset the billing parameters if it is pay later mode
            //to avoid creation of billing location
            if ( $this->_allowWaitlist || $this->_requireApproval || 
                 CRM_Utils_Array::value( 'is_pay_later', $value ) || !CRM_Utils_Array::value( 'is_primary', $value ) ) {
                $billingFields = array( "email-{$this->_bltID}",
                                        "billing_first_name",
                                        "billing_middle_name",
                                        "billing_last_name",
                                        "billing_street_address-{$this->_bltID}",
                                        "billing_city-{$this->_bltID}",
                                        "billing_state_province-{$this->_bltID}",
                                        "billing_state_province_id-{$this->_bltID}",
                                        "billing_postal_code-{$this->_bltID}",
                                        "billing_country-{$this->_bltID}",
                                        "billing_country_id-{$this->_bltID}",
                                        "address_name-{$this->_bltID}"
                                        );
                foreach( $billingFields as $field ) {
                    unset( $value[$field] );
                }
                if ( CRM_Utils_Array::value( 'is_pay_later', $value ) ) {
                    $this->_values['params']['is_pay_later'] = true;                 
                }
            }
            
            //Unset ContactID for additional participants and set RegisterBy Id.
            if ( !CRM_Utils_Array::value( 'is_primary', $value ) ) {
                $contactID = CRM_Utils_Array::value( 'contact_id', $value );
                $registerByID = $this->get( 'registerByID' );
                if ( $registerByID ) {
                    $value['registered_by_id'] = $registerByID;
                }
            } else {
                $value['amount'] = $this->_totalAmount;
            }
            
            $contactID =& $this->updateContactFields( $contactID, $value, $fields );
            
            // lets store the contactID in the session
            // we dont store in userID in case the user is doing multiple
            // transactions etc
            // for things like tell a friend
            if ( ! parent::getContactID( ) && CRM_Utils_Array::value( 'is_primary', $value ) ) {
                $session->set( 'transaction.userID', $contactID );
            }
            
            $value['description'] =
                ts( 'Online Event Registration' ) . ': ' . $this->_values['event']['title'];
            $value['accountingCode'] =
                CRM_Utils_Array::value( 'accountingCode',
                                        $this->_values['event'] );
            
            // required only if paid event
            if ( $this->_values['event']['is_monetary'] ) {
                require_once 'CRM/Core/Payment.php';
                if ( is_array( $this->_paymentProcessor ) ) {
                    $payment =& CRM_Core_Payment::singleton( $this->_mode, $this->_paymentProcessor, $this );
                }
                $pending = false;
                $result  = null;
                
                require_once 'CRM/Event/PseudoConstant.php';
                if ( $this->_allowWaitlist || $this->_requireApproval ) {
                    //get the participant statuses.
                    $waitingStatuses = CRM_Event_PseudoConstant::participantStatus( null, "class = 'Waiting'" );
                    if ( $this->_allowWaitlist ) {
                        $value['participant_status_id'] = array_search( 'On waitlist', $waitingStatuses );
                    } else {
                        $value['participant_status_id'] = array_search( 'Awaiting approval', $waitingStatuses );  
                    }
                    
                    //there might be case user seleted pay later and
                    //now becomes part of run time waiting list.
                    $value['is_pay_later'] = false;
                } else if ( CRM_Utils_Array::value( 'is_pay_later', $value ) ||
                            $value['amount']         == 0                    ||
                            $this->_contributeMode   == 'checkout'           ||
                            $this->_contributeMode   == 'notify' ) {
                    if ( $value['amount'] != 0 ) {
                        $pending = true;
                        //get the participant statuses.
                        require_once 'CRM/Event/PseudoConstant.php';
                        $pendingStatuses = CRM_Event_PseudoConstant::participantStatus( null, "class = 'Pending'" );
                        $status = CRM_Utils_Array::value('is_pay_later', $value) ? 'Pending from pay later' : 'Pending from incomplete transaction';
                        $value['participant_status_id'] = array_search($status, $pendingStatuses);
                    }
                } else if ( $this->_contributeMode == 'express' && CRM_Utils_Array::value( 'is_primary', $value ) ) {
                    $result =& $payment->doExpressCheckout( $value );
                } else if ( CRM_Utils_Array::value( 'is_primary', $value ) ) {
                    require_once 'CRM/Core/Payment/Form.php';
                    CRM_Core_Payment_Form::mapParams( $this->_bltID, $value, $value, true );
                    $result =& $payment->doDirectPayment( $value );
                }
                
                if ( is_a( $result, 'CRM_Core_Error' ) ) {
                    CRM_Core_Error::displaySessionError( $result );
                    CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/event/register', "id={$this->_eventId}" ) );
                }
                
                if ( $result ) {
                    $value = array_merge( $value, $result );
                }
                
                $value['receive_date'] = $now;
                if ( $this->_allowConfirmation ) {
                    $value['participant_register_date'] = $this->_values['participant']['register_date'];
                }
                
                $createContrib = ( $value['amount'] != 0 ) ? true : false;
                // force to create zero amount contribution, CRM-5095
                if ( ! $createContrib && ($value['amount'] == 0) 
                     && $this->_priceSetId && $this->_lineItem ) {
                    $createContrib = true;
                }
                
                if ( $createContrib && CRM_Utils_Array::value( 'is_primary', $value ) &&
                     !$this->_allowWaitlist && !$this->_requireApproval ) {
                    // if paid event add a contribution record
                    //if primary participant contributing additional amount
                    //append (multiple participants) to its fee level. CRM-4196.
                    $isAdditionalAmount = false;
                    if ( count($params) > 1 ) {
                        $isAdditionalAmount = true;
                    }
                    
                    //passing contribution id is already registered.
                    $contribution =& self::processContribution( $this, $value, $result, $contactID, 
                                                                $pending, $isAdditionalAmount );
                    
                    $value['contributionID'    ] = $contribution->id;
                    $value['contributionTypeID'] = $contribution->contribution_type_id; 
                    $value['receive_date']       = $contribution->receive_date;
                    $value['trxn_id']            = $contribution->trxn_id;
                    $value['contributionID'    ] = $contribution->id;
                    $value['contributionTypeID'] = $contribution->contribution_type_id;
                }
                $value['contactID'] = $contactID;
                $value['eventID']   = $this->_eventId;
                $value['item_name'] = $value['description'];
            }
            
            //CRM-4453.
            if ( CRM_Utils_Array::value( 'is_primary', $value ) ) {
                $primaryCurrencyID = CRM_Utils_Array::value( 'currencyID', $value );
            }
            if ( !CRM_Utils_Array::value( 'currencyID', $value ) ) {
                $value['currencyID'] = $primaryCurrencyID;
            }
            
            if ( !$pending && CRM_Utils_Array::value( 'is_primary', $value ) &&
                 !$this->_allowWaitlist && !$this->_requireApproval ) {
                // transactionID & receive date required while building email template
                $this->assign( 'trxn_id', $value['trxn_id'] );
                $this->assign( 'receive_date', CRM_Utils_Date::mysqlToIso( $value['receive_date']) );
                $this->set( 'receiveDate', CRM_Utils_Date::mysqlToIso( $value['receive_date']) );
                $this->set( 'trxnId', CRM_Utils_Array::value( 'trxn_id', $value ) );
            }
            
            $value['fee_amount'] =  $value['amount'];
            $this->set( 'value', $value );
            
            // handle register date CRM-4320
            if ( $this->_allowConfirmation ) {
                $registerDate = $params['participant_register_date'];
            } else if ( is_array( $params['participant_register_date'] ) && !empty( $params['participant_register_date'] ) ) {
                $registerDate = CRM_Utils_Date::format( $params['participant_register_date'] ); 
            } else {
                $registerDate =  date( 'YmdHis' );
            }
            $this->assign( 'register_date', $registerDate );
            
            $this->confirmPostProcess( $contactID, $contribution, $payment );
        }
        //handle if no additional participant.
        if ( !$registerByID ) {
            $registerByID = $this->get('registerByID');
        }
        
        // create line items, CRM-5313 
        if ( $this->_priceSetId && !empty( $this->_lineItem ) ) {
            require_once 'CRM/Price/BAO/LineItem.php';
            
            // take all processed participant ids.
            $allParticipantIds = $this->_participantIDS;
            
            // when participant re-walk wizard.
            if ( $this->_allowConfirmation && !empty( $this->_additionalParticipantIds ) ) {
                $allParticipantIds = array_merge( array( $registerByID ), $this->_additionalParticipantIds );
            }

            $entityTable = 'civicrm_participant';
            foreach ( $this->_lineItem as $key => $value ) {
                if ( ( $value != 'skip' ) &&
                     ( $entityId = CRM_Utils_Array::value( $key, $allParticipantIds ) ) ) {
                    
                    // do cleanup line  items if participant re-walking wizard.
                    if ( $this->_allowConfirmation ) {
                        CRM_Price_BAO_LineItem::deleteLineItems( $entityId, $entityTable );
                    }
                    
                    // create line.
                    foreach( $value as $line ) {
                        $line['entity_id']    = $entityId;
                        $line['entity_table'] = $entityTable;
                        CRM_Price_BAO_LineItem::create( $line );
                    }
                }
            }
        }
        
        //update status and send mail to cancelled additonal participants, CRM-4320
        if ( $this->_allowConfirmation && is_array( $cancelledIds ) && !empty( $cancelledIds ) ) {
            require_once 'CRM/Event/BAO/Participant.php';
            require_once 'CRM/Event/PseudoConstant.php';
            $cancelledId = array_search( 'Cancelled', 
                                         CRM_Event_PseudoConstant::participantStatus( null, "class = 'Negative'" ) );
            CRM_Event_BAO_Participant::transitionParticipants( $cancelledIds, $cancelledId );
        }
        
        $isTest = false;
        if ( $this->_action & CRM_Core_Action::PREVIEW ) {
            $isTest = true;
        }
                
        // for Transfer checkout.
        require_once "CRM/Event/BAO/Event.php";
        if ( ( $this->_contributeMode == 'checkout' ||
               $this->_contributeMode == 'notify'   ) && 
             ! CRM_Utils_Array::value( 'is_pay_later', $params[0] ) && 
             ! $this->_allowWaitlist && !$this->_requireApproval &&
             $this->_totalAmount > 0 ) {

            $primaryParticipant = $this->get ( 'primaryParticipant' );

            if ( !CRM_Utils_Array::value( 'participantID', $primaryParticipant ) ) {
                $primaryParticipant['participantID'] = $registerByID;
            } 
            
            //build an array of custom profile and assigning it to template
            $customProfile = CRM_Event_BAO_Event::buildCustomProfile( $registerByID, $this->_values, null, $isTest );
            
            if ( count($customProfile) ) {
                $this->assign( 'customProfile', $customProfile );
                $this->set   ( 'customProfile', $customProfile );
            }
            
            // do a transfer only if a monetary payment greater than 0
            if ( $this->_values['event']['is_monetary'] && $primaryParticipant && $payment ) {
                $payment->doTransferCheckout( $primaryParticipant, 'event' );
            }
            
        } else {
            //otherwise send mail Confirmation/Receipt
            $primaryContactId = $this->get('primaryContactId');
            
            //build an array of cId/pId of participants
            require_once "CRM/Event/BAO/Event.php";
            $additionalIDs = CRM_Event_BAO_Event::buildCustomProfile( $registerByID,
                                                                      null, $primaryContactId, $isTest,
                                                                      true );
            //lets send  mails to all with meaningful text, CRM-4320.
            $this->assign( 'isOnWaitlist', $this->_allowWaitlist );
            $this->assign( 'isRequireApproval', $this->_requireApproval );
            
            //need to copy, since we are unsetting on the way.
            $copyParticipantCount = $participantCount;
            
            //lets carry all paticipant params w/ values.
            foreach ( $additionalIDs as $participantID => $contactId ) {
                $participantNum = null;
                if ( $participantID == $registerByID ) {
                    $participantNum = 0;
                } else {
                    if ( $participantNum = array_search( 'participant', $copyParticipantCount ) ) {
                        unset( $copyParticipantCount[$participantNum] );
                    }
                }
                if ( $participantNum === null ) break;
                
                //carry the participant submitted values.
                $this->_values['params'][$participantID] = $params[$participantNum];
            }
            
            foreach( $additionalIDs as $participantID => $contactId ) {
                $participantNum = 0;
                if ( $participantID == $registerByID ) {
                    //set as Primary Participant
                    $this->assign ( 'isPrimary' , 1 );
                    //build an array of custom profile and assigning it to template.
                    $customProfile = CRM_Event_BAO_Event::buildCustomProfile( $participantID, $this->_values, null, $isTest );
                    
                    if ( count($customProfile) ) {
                        $this->assign( 'customProfile', $customProfile );
                        $this->set   ( 'customProfile', $customProfile );
                    }
                    $this->_values['params']['additionalParticipant'] = false;
                } else {
                    //take the Additional participant number. 
                    if ( $participantNum = array_search( 'participant', $participantCount ) ) {
                        unset( $participantCount[$participantNum] );
                    }
                    $this->assign ( 'isPrimary' , 0 );
                    $this->assign( 'customProfile', null );
                    //Additional Participant should get only it's payment information
                    if ( $this->_amount ) {
                        $amount = array();
                        $params = $this->get( 'params' );
                        $amount[$participantNum]['label']  = $params[$participantNum]['amount_level'];
                        $amount[$participantNum]['amount'] = $params[$participantNum]['amount'];
                        $this->assign( 'amount', $amount );
                    }
                    if ( $this->_lineItem ) {
                        $lineItems = $this->_lineItem;
                        $lineItem = array();
                        $lineItem[] = CRM_Utils_Array::value( $participantNum, $lineItems );
                        $this->assign( 'lineItem',$lineItem );
                    }
                    $this->_values['params']['additionalParticipant'] = true;
                }
                
                //pass these variables since these are run time calculated.
                $this->_values['params']['isOnWaitlist']  = $this->_allowWaitlist;
                $this->_values['params']['isRequireApproval'] = $this->_requireApproval;
                
                //send mail to primary as well as additional participants.
                $this->assign( 'contactID', $contactId );
                $this->assign( 'participantID', $participantID );
                CRM_Event_BAO_Event::sendMail( $contactId, $this->_values, $participantID, $isTest );
            }
                
        }
        
    } //end of function
    
    /**
     * Process the contribution
     *
     * @return void
     * @access public
     */
    static function processContribution( &$form, $params, $result, $contactID, 
                                         $pending = false, $isAdditionalAmount = false ) 
    {   require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $config = CRM_Core_Config::singleton( );
        $now         = date( 'YmdHis' );
        $receiptDate = null;
        
        if ( $form->_values['event']['is_email_confirm'] ) {
            $receiptDate = $now ;
        }
        //CRM-4196        
        if ( $isAdditionalAmount ) {
            $params['amount_level'] = $params['amount_level'].ts(' (multiple participants)'). CRM_Core_BAO_CustomOption::VALUE_SEPERATOR;
        }

        $contribParams = array(
                               'contact_id'            => $contactID,
                               'contribution_type_id'  => $form->_values['event']['contribution_type_id'] ?
                               $form->_values['event']['contribution_type_id'] : $params['contribution_type_id'],
                               'receive_date'          => $now,
                               'total_amount'          => $params['amount'],
                               'amount_level'          => $params['amount_level'],
                               'invoice_id'            => $params['invoiceID'],
                               'currency'              => $params['currencyID'],
                               'source'                => $params['description'],
                               'is_pay_later'          => CRM_Utils_Array::value( 'is_pay_later', $params, 0 ),
                               );
        
        if ( ! CRM_Utils_Array::value( 'is_pay_later', $params ) ) {
            $contribParams['payment_instrument_id'] = 1;
        }

        if ( ! $pending && $result ) {
            $contribParams += array(
                                    'fee_amount'   => CRM_Utils_Array::value( 'fee_amount', $result ),
                                    'net_amount'   => CRM_Utils_Array::value( 'net_amount', $result, $params['amount'] ),
                                    'trxn_id'      => $result['trxn_id'],
                                    'receipt_date' => $receiptDate,
                                    );
        }
        
        require_once 'CRM/Contribute/PseudoConstant.php';
        $allStatuses = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        $contribParams["contribution_status_id"] = array_search( 'Completed', $allStatuses );
        if ( $pending ) {
            $contribParams["contribution_status_id"] = array_search( 'Pending', $allStatuses );
        }
        
        $contribParams["is_test"] = 0;
        if( $form->_action & CRM_Core_Action::PREVIEW || CRM_Utils_Array::value( 'mode', $params ) == 'test' ) {
            $contribParams["is_test"] = 1;
        }
        
        $contribID = null; 
        if ( CRM_Utils_Array::value( 'invoice_id', $contribParams ) ) {
            $contribID = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_Contribution',
                                                      $contribParams['invoice_id'],
                                                      'id',
                                                      'invoice_id' );
        }
        
        $ids = array( );
        if ( $contribID ) {
            $ids['contribution'] = $contribID;
            $contribParams['id'] = $contribID;
        }
        
        require_once 'CRM/Contribute/BAO/Contribution.php';
        //create an contribution address
        if ( $form->_contributeMode != 'notify' && !CRM_Utils_Array::value('is_pay_later', $params) ) {  
            $contribParams['address_id']  = CRM_Contribute_BAO_Contribution::createAddress( $params, $form->_bltID );
        }
        
		// create contribution record
        $contribution =& CRM_Contribute_BAO_Contribution::add( $contribParams, $ids );
        
        // return if pending
        if ( $pending || ($contribution->total_amount == 0) ) {
            $transaction->commit( );
            return $contribution;
        }
        
        // next create the transaction record
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
                            );
        
        require_once 'CRM/Core/BAO/FinancialTrxn.php';
        $trxn =& CRM_Core_BAO_FinancialTrxn::create( $trxnParams );

        $transaction->commit( );
        
        return $contribution;
    }
    
    /**
     * Fix the Location Fields
     *
     * @return void
     * @access public
     */
    public function fixLocationFields( &$params, &$fields ) 
    {
        if( ! empty($this->_fields) ) {
            foreach ( $this->_fields as $name => $dontCare ) {
                $fields[$name] = 1;
            }
        }

        if ( is_array($fields) ) {
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
        }

        // also add location name to the array
        if ($this->_values['event']['is_monetary']){
            $params["address_name-{$this->_bltID}"] = 
                CRM_Utils_Array::value( "billing_first_name", $params ) . ' ' . 
                CRM_Utils_Array::value( "billing_middle_name", $params ) . ' ' . 
                CRM_Utils_Array::value( "billing_last_name", $params );
            $fields["address_name-{$this->_bltID}"] = 1;
        }
        $fields["email-{$this->_bltID}"] = 1;
        $fields["email-Primary"] = 1;

        //if its pay later or additional participant set email address as primary.
        if( ( CRM_Utils_Array::value( 'is_pay_later', $params) || 
              !CRM_Utils_Array::value('is_primary', $params)   || 
              !$this->_values['event']['is_monetary']          ||
              $this->_allowWaitlist                            || 
              $this->_requireApproval                          ) && 
            CRM_Utils_Array::value("email-{$this->_bltID}", $params) ) {
            $params["email-Primary"] = $params["email-{$this->_bltID}"];
        }
    }
    
    /**
     * function to update contact fields
     *
     * @return void
     * @access public
     */
    public function updateContactFields( $contactID, $params, $fields ) 
    {
        //add the contact to group, if add to group is selected for a
        //particular uf group
 
        // get the add to groups
        $addToGroups = array( );

        if ( !empty($this->_fields) ) {
            foreach ( $this->_fields as $key => $value) {
                if ( CRM_Utils_Array::value( 'add_to_group_id', $value ) ) {
                    $addToGroups[$value['add_to_group_id']] = $value['add_to_group_id'];
                }
            } 
        }

        // check for profile double opt-in and get groups to be subscribed
        require_once 'CRM/Core/BAO/UFGroup.php';
        $subscribeGroupIds = CRM_Core_BAO_UFGroup::getDoubleOptInGroupIds( $params, $contactID );
                
        foreach ( $addToGroups as $k ) {
            if ( array_key_exists( $k, $subscribeGroupIds ) ) {
                unset( $addToGroups[$k] );
            }
        }
        
        // since we are directly adding contact to group lets unset it from mailing
        if ( !empty( $addToGroups ) ) {
            foreach( $addToGroups as $groupId ) {
                if ( isset( $subscribeGroupIds[$groupId] ) ) {
                    unset( $subscribeGroupIds[$groupId] );
                }
            }
        }        
        
        require_once "CRM/Contact/BAO/Contact.php";
        if ($contactID) {
            $ctype = CRM_Core_DAO::getFieldValue( "CRM_Contact_DAO_Contact",
                                                  $contactID,
                                                  "contact_type" );
            $contactID =& CRM_Contact_BAO_Contact::createProfileContact( $params,
                                                                         $fields, 
                                                                         $contactID, 
                                                                         $addToGroups, 
                                                                         null,
                                                                         $ctype );
        } else {
            // when we have allow_same_participant_emails = 1
            // don't take email address in dedupe params - CRM-4886
            // here we are making dedupe weak - so to make dedupe
            // more effective please update individual 'Strict' rule.
            $allowSameEmailAddress = CRM_Utils_Array::value( 'allow_same_participant_emails', $this->_values['event'] );
            require_once 'CRM/Dedupe/Finder.php';
            //suppress "email-Primary" when allow_same_participant_emails = 1
            if ( $allowSameEmailAddress && 
                 ( $email = CRM_Utils_Array::value( 'email-Primary', $params ) ) &&
                 ( CRM_Utils_Array::value( 'registered_by_id', $params ) ) ) {
                //skip dedupe check only for additional participants
                unset($params['email-Primary']);
            }
            $dedupeParams = CRM_Dedupe_Finder::formatParams($params, 'Individual');
            // disable permission based on cache since event registration is public page/feature.
            $dedupeParams['check_permission'] = false;
            $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual');
            
            // if we find more than one contact, use the first one
            $contact_id  = $ids[0];
            if ( isset( $email ) ) {
                $params['email-Primary'] = $email;
            }
            
            $contactID =& CRM_Contact_BAO_Contact::createProfileContact( $params, $fields, $contact_id, $addToGroups );
            $this->set( 'contactID', $contactID );
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

        return $contactID;
    }

}

