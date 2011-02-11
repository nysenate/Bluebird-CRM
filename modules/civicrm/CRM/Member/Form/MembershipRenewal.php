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

require_once 'CRM/Member/Form.php';
require_once 'CRM/Member/PseudoConstant.php';

/**
 * This class generates form components for Membership Renewal
 * 
 */
class CRM_Member_Form_MembershipRenewal extends CRM_Member_Form
{

    public function preProcess()  
    {  
        // check for edit permission
        if ( ! CRM_Core_Permission::check( 'edit memberships' ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );
        }

        // action
        $this->_action    = CRM_Utils_Request::retrieve( 'action', 'String',
                                                         $this, false, 'add' );
        $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Positive',
                                                         $this );
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive',
                                                         $this );
        if ( $this->_id ) {
            $this->_memType = CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership",$this->_id,"membership_type_id");
        }
       
        $this->assign( "endDate", CRM_Utils_Date::customFormat( CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership",
                                                                                            $this->_id,"end_date")
                                                                ) );
        $this->assign( "membershipStatus", 
                       CRM_Core_DAO::getFieldValue("CRM_Member_DAO_MembershipStatus",
                                                   CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership",
                                                                               $this->_id, "status_id" ),
                                                   "name") );
        
        $orgId   = CRM_Core_DAO::getFieldValue("CRM_Member_DAO_MembershipType",$this->_memType,"member_of_contact_id");
        
        $this->assign( "memType",  CRM_Core_DAO::getFieldValue("CRM_Member_DAO_MembershipType",$this->_memType,"name") );
        $this->assign( "orgName",  CRM_Core_DAO::getFieldValue("CRM_Contact_DAO_Contact", $orgId,"display_name") );
        
        //using credit card :: CRM-2759
        $this->_mode      = CRM_Utils_Request::retrieve( 'mode', 'String', $this );
        if ( $this->_mode ) {
            $membershipFee = CRM_Core_DAO::getFieldValue( "CRM_Member_DAO_MembershipType", $this->_memType, 'minimum_fee' );
            if ( ! $membershipFee ) {
                $statusMsg = ts('Membership Renewal using credit card required Membership fee, since this memebrship type have no fee, you can use normal renew mode');
                CRM_Core_Session::setStatus($statusMsg);
                CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contact/view/membership',
                                                                   "reset=1&action=renew&cid={$this->_contactID}&id={$this->_id}&context=membership" ) );
            }
            $this->assign( 'membershipMode', $this->_mode );
            
            $this->_paymentProcessor = array( 'billing_mode' => 1 );
            $validProcessors = array( );
            $processors = CRM_Core_PseudoConstant::paymentProcessor( false, false, "billing_mode IN ( 1, 3 )" );
            
            foreach ( $processors as $ppID => $label ) {
                require_once 'CRM/Core/BAO/PaymentProcessor.php';
                require_once 'CRM/Core/Payment.php';
                $paymentProcessor =& CRM_Core_BAO_PaymentProcessor::getPayment( $ppID, $this->_mode );
                if ( $paymentProcessor['payment_processor_type'] == 'PayPal' && !$paymentProcessor['user_name'] ) {
                    continue;
                } else if ( $paymentProcessor['payment_processor_type'] == 'Dummy' && $this->_mode == 'live' ) {
                    continue;
                } else {
                    $paymentObject =& CRM_Core_Payment::singleton( $this->_mode, $paymentProcessor, $this );
                    $error = $paymentObject->checkConfig( );
                    if ( empty( $error ) ) {
                        $validProcessors[$ppID] = $label;
                    }
                    $paymentObject = null;
                }
            }
            if ( empty( $validProcessors )  ) {
                CRM_Core_Error::fatal( ts( 'Could not find valid payment processor for this page' ) );
            } else {
                $this->_processors = $validProcessors;  
            }
            // also check for billing information
            // get the billing location type
            $locationTypes =& CRM_Core_PseudoConstant::locationType( );
            $this->_bltID = array_search( 'Billing',  $locationTypes );
            if ( ! $this->_bltID ) {
                CRM_Core_Error::fatal( ts( 'Please set a location type of %1', array( 1 => 'Billing' ) ) );
            }
            $this->set   ( 'bltID', $this->_bltID );
            $this->assign( 'bltID', $this->_bltID );
            
            $this->_fields = array( );
            
            require_once 'CRM/Core/Payment/Form.php';
            CRM_Core_Payment_Form::setCreditCardFields( $this );
            
            // this required to show billing block    
            $this->assign_by_ref( 'paymentProcessor', $paymentProcessor );
            $this->assign( 'hidePayPalExpress', true );
        } else {
            $this->assign( 'membershipMode', false );
        }

        require_once "CRM/Core/BAO/Email.php";
        $this->_fromEmails = CRM_Core_BAO_Email::getFromEmail( );
        parent::preProcess( );
    }

    /**
     * This function sets the default values for the form.
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    public function setDefaultValues( ) 
    {
        $defaults = array( );
        $defaults =& parent::setDefaultValues( );
        $this->_memType = $defaults["membership_type_id"] ;
        $defaults['renewal_date'] = CRM_Utils_Date::getToday( CRM_Utils_Array::value( 'renewal_date', $defaults ),
                                                              'm/d/Y' );

        if ($defaults['id']) {
            $defaults['record_contribution'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipPayment', 
                                                                            $defaults['id'], 
                                                                            'contribution_id', 
                                                                            'membership_id' );
        }
        
        $defaults['contribution_type_id'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                                         $this->_memType, 
                                                                         'contribution_type_id' );
        
        $defaults['total_amount'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                                 $this->_memType, 
                                                                 'minimum_fee' );
        
        $defaults['record_contribution'] = 0;
        if ( $defaults['record_contribution'] ) {
            $contributionParams   = array( 'id' => $defaults['record_contribution'] );
            $contributionIds      = array( );
            
            require_once "CRM/Contribute/BAO/Contribution.php";
            CRM_Contribute_BAO_Contribution::getValues( $contributionParams, $defaults, $contributionIds );
        }
        
        $defaults['send_receipt'] = 0; 
        
        if ( $defaults['membership_type_id'] ) {
            $defaults['receipt_text_renewal'] =  CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                                              $defaults['membership_type_id'],
                                                                              'receipt_text_renewal' );
        }

        $renewalDate = CRM_Utils_Date::processDate( CRM_Utils_Array::value( 'renewal_date', $defaults ), 
                                                    null, null, 'Y-m-d' );
        $this->assign( 'renewalDate', $renewalDate );
        $this->assign( "member_is_test", CRM_Utils_Array::value('member_is_test',$defaults) );

        if ( $this->_mode ) {
            $fields = array( );
            
            foreach ( $this->_fields as $name => $dontCare ) {
                $fields[$name] = 1;
            }

            $names = array( "first_name", "middle_name", "last_name","street_address-{$this->_bltID}",
                            "city-{$this->_bltID}", "postal_code-{$this->_bltID}","country_id-{$this->_bltID}",
                            "state_province_id-{$this->_bltID}"
                            );
            foreach ($names as $name) {
                $fields[$name] = 1;
            }
            
            $fields["state_province-{$this->_bltID}"] = 1;
            $fields["country-{$this->_bltID}"       ] = 1;
            $fields["email-{$this->_bltID}"         ] = 1;
            $fields["email-Primary"                 ] = 1;
            
            require_once "CRM/Core/BAO/UFGroup.php";
            CRM_Core_BAO_UFGroup::setProfileDefaults( $this->_contactID, $fields, $this->_defaults );

            // use primary email address if billing email address is empty
            if ( empty( $this->_defaults["email-{$this->_bltID}"] ) &&
                 ! empty( $this->_defaults["email-Primary"] ) ) {
                $defaults["email-{$this->_bltID}"] = $this->_defaults["email-Primary"];
            }

            foreach ( $names as $name) {
                if ( ! empty( $this->_defaults[$name] ) ) {
                    $defaults["billing_" . $name] = $this->_defaults[$name];
                }
            }
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
        parent::buildQuickForm( );

        $this->applyFilter('__ALL__', 'trim');
        
        $this->addDate( 'renewal_date', ts('Date Renewal Entered'), false, array( 'formatType' => 'activityDate') );    
        if( ! $this->_mode ) {
            $this->addElement('checkbox', 'record_contribution', ts('Record Renewal Payment?'), null, array('onclick' =>"checkPayment();"));
            require_once 'CRM/Contribute/PseudoConstant.php';
            $this->add('select', 'contribution_type_id', ts( 'Contribution Type' ), 
                       array(''=>ts( '- select -' )) + CRM_Contribute_PseudoConstant::contributionType( )
                       );
            
            $this->add('text', 'total_amount', ts('Amount'));
            $this->addRule('total_amount', ts('Please enter a valid amount.'), 'money');
            
            $this->add('select', 'payment_instrument_id', ts( 'Paid By' ), 
                       array(''=>ts( '- select -' )) + CRM_Contribute_PseudoConstant::paymentInstrument( ),
                       false, array( 'onChange' => "return showHideByValue('payment_instrument_id','4','checkNumber','table-row','select',false);"));
            
            $this->add('text', 'trxn_id', ts('Transaction ID'));
            $this->addRule( 'trxn_id', ts('Transaction ID already exists in Database.'),
                            'objectExists', array( 'CRM_Contribute_DAO_Contribution', $this->_id, 'trxn_id' ) );
            
            $this->add('select', 'contribution_status_id', ts('Payment Status'), 
                       CRM_Contribute_PseudoConstant::contributionStatus( )
                       );
            
            $this->add( 'text', 'check_number', ts('Check Number'), 
                        CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Contribution', 'check_number' ) );
        }
        $this->addElement( 'checkbox', 'send_receipt', ts('Send Confirmation and Receipt?'), null, 
                           array( 'onclick' => "showHideByValue( 'send_receipt', '', 'notice', 'table-row', 'radio', false ); showHideByValue( 'send_receipt', '', 'fromEmail', 'table-row', 'radio',false);" ) );

        $this->add( 'select', 'from_email_address', ts('Receipt From'), $this->_fromEmails );

        $this->add('textarea', 'receipt_text_renewal', ts('Renewal Message') );

        if ( $this->_mode ) {
            $this->add( 'select', 'payment_processor_id', ts( 'Payment Processor' ),$this->_processors, true );
            require_once 'CRM/Core/Payment/Form.php';
            CRM_Core_Payment_Form::buildCreditCard( $this, true );
        }
        
        require_once 'CRM/Contact/BAO/Contact/Location.php';
        // Retrieve the name and email of the contact - this will be the TO for receipt email
        list( $this->_contributorDisplayName, 
              $this->_contributorEmail ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $this->_contactID );
        $this->assign( 'email', $this->_contributorEmail );

        require_once "CRM/Core/BAO/Preferences.php";
        $mailingInfo =& CRM_Core_BAO_Preferences::mailingPreferences();
        $this->assign( 'outBound_option', $mailingInfo['outBound_option'] );

        if ( CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', $this->_id, 'contribution_recur_id' ) ) {
            require_once 'CRM/Member/BAO/Membership.php'; 
            if ( CRM_Member_BAO_Membership::isCancelSubscriptionSupported( $this->_id ) ) {
                $this->assign( 'cancelAutoRenew', 
                               CRM_Utils_System::url( 'civicrm/contribute/unsubscribe', "reset=1&mid={$this->_id}" ) );
            }
        }
        
        $this->addFormRule(array('CRM_Member_Form_MembershipRenewal', 'formRule'));
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
    static function formRule( $params ) 
    {
        $errors = array( );
        //total amount condition arise when membership type having no
        //minimum fee
        if ( isset( $params['record_contribution'] ) ) { 
            if ( ! $params['contribution_type_id'] ) {
                $errors['contribution_type_id'] = ts('Please enter the contribution Type.');
            } 
            if ( !$params['total_amount'] ) {
                $errors['total_amount'] = ts('Please enter the contribution.'); 
            }
        }
     
        return empty($errors) ? true : $errors;
    }
       
    /**
     * Function to process the renewal form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        require_once 'CRM/Member/BAO/Membership.php';
        require_once 'CRM/Member/BAO/MembershipType.php';
        require_once 'CRM/Member/BAO/MembershipStatus.php'; 
    
        // get the submitted form values.  
        $this->_params = $formValues = $this->controller->exportValues( $this->_name );
        
        $params = array( );
        $ids    = array( );
        $config = CRM_Core_Config::singleton();
        $params['contact_id']  = $this->_contactID;
        if ( $this->_mode ) {
            $formValues['total_amount']         = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                                               $this->_memType,'minimum_fee' );
            $formValues['contribution_type_id'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', 
                                                                               $this->_memType,'contribution_type_id' );
            require_once 'CRM/Core/BAO/PaymentProcessor.php';
            $this->_paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $formValues['payment_processor_id'],
                                                                                  $this->_mode );
            require_once "CRM/Contact/BAO/Contact.php";
            $now = CRM_Utils_Date::getToday( $now, 'YmdHis' );
            $fields = array( );
            
            // set email for primary location.
            $fields["email-Primary"]     = 1;
            $formValues["email-5"]       = $formValues["email-Primary"] = $this->_contributorEmail;
            $formValues['register_date'] = $now;
            
            // now set the values for the billing location.
            foreach ( $this->_fields as $name => $dontCare ) {
                $fields[$name] = 1;
            }
            
            // also add location name to the array
            $formValues["address_name-{$this->_bltID}"] =
                CRM_Utils_Array::value( 'billing_first_name' , $formValues ) . ' ' .
                CRM_Utils_Array::value( 'billing_middle_name', $formValues ) . ' ' .
                CRM_Utils_Array::value( 'billing_last_name'  , $formValues );
            
            $formValues["address_name-{$this->_bltID}"] = trim( $formValues["address_name-{$this->_bltID}"] );
        
            $fields["address_name-{$this->_bltID}"] = 1;
            
            $fields["email-{$this->_bltID}"] = 1;
            
            $ctype = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $this->_contactID, 'contact_type' );
            
            $nameFields = array( 'first_name', 'middle_name', 'last_name' );
            
            foreach ( $nameFields as $name ) {
                $fields[$name] = 1;
                if ( array_key_exists( "billing_$name", $formValues ) ) {
                    $formValues[$name]            = $formValues["billing_{$name}"];
                    $formValues['preserveDBName'] = true;
                }
            }
            
            $contactID = CRM_Contact_BAO_Contact::createProfileContact( $formValues, $fields, $this->_contactID, null, null, $ctype );
            
            // add all the additioanl payment params we need
            $this->_params["state_province-{$this->_bltID}"] = $this->_params["billing_state_province-{$this->_bltID}"] =
                CRM_Core_PseudoConstant::stateProvinceAbbreviation( $this->_params["billing_state_province_id-{$this->_bltID}"] );
            $this->_params["country-{$this->_bltID}"] = $this->_params["billing_country-{$this->_bltID}"] =
                CRM_Core_PseudoConstant::countryIsoCode( $this->_params["billing_country_id-{$this->_bltID}"] );
            
            $this->_params['year'      ]     = $this->_params['credit_card_exp_date']['Y'];
            $this->_params['month'     ]     = $this->_params['credit_card_exp_date']['M'];
            $this->_params['ip_address']     = CRM_Utils_System::ipAddress( );
            $this->_params['amount'        ] = $formValues['total_amount'];
            $this->_params['currencyID'    ] = $config->defaultCurrency;
            $this->_params['payment_action'] = 'Sale';
            $this->_params['invoiceID']      = md5( uniqid( rand( ), true ) );
            
            // at this point we've created a contact and stored its address etc
            // all the payment processors expect the name and address to be in the 
            // so we copy stuff over to first_name etc. 
            $paymentParams = $this->_params;
            if ( CRM_Utils_Array::value( 'send_receipt', $this->_params ) ) {
                $paymentParams['email'] = $this->_contributorEmail;
            }
            
            require_once 'CRM/Core/Payment/Form.php';
            CRM_Core_Payment_Form::mapParams( $this->_bltID, $this->_params, $paymentParams, true );
            
            $payment =& CRM_Core_Payment::singleton( $this->_mode, $this->_paymentProcessor, $this );
            
            $result =& $payment->doDirectPayment( $paymentParams );
            
            if ( is_a( $result, 'CRM_Core_Error' ) ) {
                CRM_Core_Error::displaySessionError( $result );
                CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contact/view/membership',
                                                                   "reset=1&action=renew&cid={$this->_contactID}&id={$this->_id}&context=membership&mode={$this->_mode}" ) );
            }
            
            if ( $result ) {
                $this->_params = array_merge( $this->_params, $result );
            }
            $formValues['contribution_status_id'] = 1;
            $formValues['receive_date']           = $now;
            $formValues['invoice_id']             = $this->_params['invoiceID'];
            $formValues['trxn_id']                = $result['trxn_id'];
            $formValues['payment_instrument_id']  = 1;
            $formValues['is_test']                = ( $this->_mode == 'live' ) ? 0 : 1 ; 
            if ( CRM_Utils_Array::value( 'send_receipt', $this->_params ) ) {
                $formValues['receipt_date'] = $now;
            } else {
                $formValues['receipt_date'] = null;
            }
            
            $this->set( 'params', $this->_params );
            $this->assign( 'trxn_id', $result['trxn_id'] );
            $this->assign( 'receive_date',CRM_Utils_Date::mysqlToIso( $formValues['receive_date']) );
        }

        $renewalDate = null;

        if ( $formValues['renewal_date'] ) {
            $this->set( 'renewDate', CRM_Utils_Date::processDate( $formValues['renewal_date'] ) );
        }
        $this->_membershipId = $this->_id;
        
        // check for test membership.
        $isTestMembership = CRM_Core_DAO::getFieldValue('CRM_Member_DAO_Membership', $this->_membershipId, 'is_test' );
        $renewMembership = CRM_Member_BAO_Membership::renewMembership( $this->_contactID, 
                                                                       $this->_memType,
                                                                       $isTestMembership, $this, null );
        
        $endDate = CRM_Utils_Date::processDate( $renewMembership->end_date );
        
        require_once 'CRM/Contact/BAO/Contact/Location.php';
        // Retrieve the name and email of the current user - this will be the FROM for the receipt email
        $session = CRM_Core_Session::singleton( );
        $userID  = $session->get( 'userID' );
        list( $userName, $userEmail ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $userID );

        $memType = CRM_Core_DAO::getFieldValue('CRM_Member_DAO_MembershipType', $renewMembership->membership_type_id, 'name');

        if ( CRM_Utils_Array::value( 'record_contribution', $formValues ) || $this->_mode ) {
            //building contribution params 
            $contributionParams = array( );
            $config = CRM_Core_Config::singleton();
            $contributionParams['currency'             ] = $config->defaultCurrency;
            $contributionParams['contact_id'           ] = $params['contact_id'];
            $contributionParams['source'               ] = "{$memType} Membership: Offline membership renewal (by {$userName})";
            $contributionParams['non_deductible_amount'] = 'null';
            $contributionParams['receive_date'         ] = date( 'Y-m-d H:i:s' );
            $contributionParams['receipt_date'         ] = CRM_Utils_Array::value( 'send_receipt', $formValues ) ? 
                                                           $contributionParams['receive_date'] : 'null';
                       
            $recordContribution = array( 'total_amount', 'contribution_type_id', 'payment_instrument_id','trxn_id', 'contribution_status_id', 'invoice_id', 'check_number', 'is_test' );
            foreach ( $recordContribution as $f ) {
                $contributionParams[$f] = CRM_Utils_Array::value( $f, $formValues );
            }   

            require_once 'CRM/Contribute/BAO/Contribution.php';
            $contribution =& CRM_Contribute_BAO_Contribution::create( $contributionParams, $ids );
           
            require_once 'CRM/Member/DAO/MembershipPayment.php';
            require_once 'CRM/Utils/Hook.php';
            $mpDAO = new CRM_Member_DAO_MembershipPayment();    
            $mpDAO->membership_id   = $renewMembership->id;
            $mpDAO->contribution_id = $contribution->id;
            
            CRM_Utils_Hook::pre( 'create', 'MembershipPayment', null, $mpDAO );
            $mpDAO->save();
            CRM_Utils_Hook::post( 'create', 'MembershipPayment', $mpDAO->id, $mpDAO );

            if ($this->_mode ) {
                $trxnParams = array(
                                    'contribution_id'   => $contribution->id,
                                    'trxn_date'         => $now,
                                    'trxn_type'         => 'Debit',
                                    'total_amount'      => $formValues['total_amount'],
                                    'fee_amount'        => CRM_Utils_Array::value( 'fee_amount', $result ),
                                    'net_amount'        => CRM_Utils_Array::value( 'net_amount', $result, $formValues['total_amount'] ),
                                    'currency'          => $config->defaultCurrency,
                                    'payment_processor' => $this->_paymentProcessor['payment_processor_type'],
                                    'trxn_id'           => $result['trxn_id'],
                                    );
            
                require_once 'CRM/Core/BAO/FinancialTrxn.php';
                $trxn =& CRM_Core_BAO_FinancialTrxn::create( $trxnParams );
            }
        }

        if ( CRM_Utils_Array::value( 'send_receipt', $formValues ) ) {
            require_once 'CRM/Core/DAO.php';
            CRM_Core_DAO::setFieldValue( 'CRM_Member_DAO_MembershipType', 
                                         CRM_Utils_Array::value( 'membership_type_id', $params ),
                                         'receipt_text_renewal',
                                         $formValues['receipt_text_renewal'] );
        }
        
        $receiptSend = false;
        if ( CRM_Utils_Array::value( 'send_receipt', $formValues ) ) {
            $receiptSend = true;
            // Retrieve the name and email of the contact - this will be the TO for receipt email
            list( $this->_contributorDisplayName, 
                  $this->_contributorEmail ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $this->_contactID );
            $receiptFrom = $formValues['from_email_address'];
            
            $paymentInstrument = CRM_Contribute_PseudoConstant::paymentInstrument();
            $formValues['paidBy'] = $paymentInstrument[$formValues['payment_instrument_id']];
            
            //get the group Tree
            $this->_groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Membership', $this, $this->_id, false,$this->_memType);
            
            // retrieve custom data
            require_once "CRM/Core/BAO/UFGroup.php";
            $customFields = $customValues = $fo = array( );
            foreach ( $this->_groupTree as $groupID => $group ) {
                if ( $groupID == 'info' ) {
                    continue;
                }
                foreach ( $group['fields'] as $k => $field ) {
                    $field['title'] = $field['label'];
                    $customFields["custom_{$k}"] = $field;
                }
            }

            CRM_Core_BAO_UFGroup::getValues( $this->_contactID, $customFields, $customValues , false, 
                                             array( array( 'member_id', '=', $renewMembership->id, 0, 0 ) ) );
            
            $this->assign_by_ref( 'formValues', $formValues );
            $this->assign( 'receive_date', $renewalDate );
            $this->assign( 'module', 'Membership' );
            $this->assign('receiptType', 'membership renewal');
            $this->assign( 'mem_start_date', CRM_Utils_Date::customFormat( $renewMembership->start_date  ) );
            $this->assign( 'mem_end_date', CRM_Utils_Date::customFormat( $renewMembership->end_date ) );
            $this->assign( 'membership_name', CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType',
                                                                           $renewMembership->membership_type_id ) );
            $this->assign( 'customValues', $customValues );
            if( $this->_mode ) {
                if ( CRM_Utils_Array::value( 'billing_first_name', $this->_params ) ) {
                    $name = $this->_params['billing_first_name'];
                    
                }
                
                if ( CRM_Utils_Array::value( 'billing_middle_name', $this->_params ) ) {
                    $name .= " {$this->_params['billing_middle_name']}";
                }
                
                if ( CRM_Utils_Array::value( 'billing_last_name', $this->_params ) ) {
                    $name .= " {$this->_params['billing_last_name']}";
                }
                $this->assign( 'billingName', $name );
                
                // assign the address formatted up for display
                $addressParts  = array( "street_address-{$this->_bltID}",
                                        "city-{$this->_bltID}",
                                        "postal_code-{$this->_bltID}",
                                        "state_province-{$this->_bltID}",
                                        "country-{$this->_bltID}");
                $addressFields = array( );
                foreach ($addressParts as $part) {
                    list( $n, $id ) = explode( '-', $part );
                    if ( isset ( $this->_params['billing_' . $part] ) ) {
                        $addressFields[$n] = $this->_params['billing_' . $part];
                    }
                }
                require_once 'CRM/Utils/Address.php';
                $this->assign('address', CRM_Utils_Address::format( $addressFields ) );
                $date = CRM_Utils_Date::format( $this->_params['credit_card_exp_date'] );
                $date = CRM_Utils_Date::mysqlToIso( $date );
                $this->assign( 'credit_card_exp_date', $date );
                $this->assign( 'credit_card_number',
                               CRM_Utils_System::mungeCreditCard( $this->_params['credit_card_number'] ) );
                $this->assign( 'credit_card_type', $this->_params['credit_card_type'] );
                $this->assign( 'contributeMode', 'direct');
                $this->assign( 'isAmountzero' , 0);
                $this->assign( 'is_pay_later',0);
                $this->assign( 'isPrimary', 1 );
                if( $this->_mode == 'test' ) {
                    $this->assign( 'action', '1024') ;
                }
            }

            require_once 'CRM/Core/BAO/MessageTemplates.php';
            list ($mailSend, $subject, $message, $html) = CRM_Core_BAO_MessageTemplates::sendTemplate(
                array(
                    'groupName' => 'msg_tpl_workflow_membership',
                    'valueName' => 'membership_offline_receipt',
                    'contactId' => $this->_contactID,
                    'from'      => $receiptFrom,
                    'toName'    => $this->_contributorDisplayName,
                    'toEmail'   => $this->_contributorEmail,
                    'isTest'    => $this->_mode == 'test',
                )
            );
        }
       
        $statusMsg = ts('%1 membership for %2 has been renewed.', array(1 => $memType, 2 => $this->_contributorDisplayName));
       
        $endDate = CRM_Utils_Date::customFormat( CRM_Core_DAO::getFieldValue( "CRM_Member_DAO_Membership", 
                                                                              $this->_id, 
                                                                              "end_date" ) );
        if ( $endDate ) {
            $statusMsg .= ' ' . ts('The new membership End Date is %1.', array(1 => $endDate));
        }
        
        if ( $receiptSend && $mailSend ) {
            $statusMsg .= ' ' . ts('A renewal confirmation and receipt has been sent to %1.', array(1 => $this->_contributorEmail));
        }
        
        CRM_Core_Session::setStatus($statusMsg);
    }
}

