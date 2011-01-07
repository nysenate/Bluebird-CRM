<?php 

/*
 * Copyright (C) 2008
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 * Modified and contributed by Click And Pledge, LLC (http://www.clickandpledge.com)
 *
 */

/**
 * @package CRM
 * @author Irfan Ahmed <irfan.ahmed@v-empower.com>
**/

require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_ClickAndPledge extends CRM_Core_Payment {
    const
        CHARSET  = 'iso-8859-1';
    
    protected $_mode = null;

    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;
    
    /** 
     * Constructor 
     * 
     * @param string $mode the mode of operation: live or test
     *
     * @return void 
     */ 
    function __construct( $mode, &$paymentProcessor ) {
        $this->_mode             = $mode;
        $this->_paymentProcessor = $paymentProcessor;
        $this->_processorName    = ts('Click And Pledge');

        if ( $this->_paymentProcessor['payment_processor_type'] == 'ClickAndPledge' ) {
            return;
        }

        if ( ! $this->_paymentProcessor['user_name'] ) {
            CRM_Core_Error::fatal( ts( 'Could not find User ID for payment processor' ) );
        }
    }

    /** 
     * singleton function used to manage this object 
     * 
     * @param string $mode the mode of operation: live or test
     *
     * @return object 
     * @static 
     * 
     */ 
    static function &singleton( $mode, &$paymentProcessor ) {
        $processorName = $paymentProcessor['name'];
        if (self::$_singleton[$processorName] === null ) {
            self::$_singleton[$processorName] = new CRM_Core_Payment_ClickAndPledge( $mode, $paymentProcessor );
        }
        return self::$_singleton[$processorName];
    }
    
    /**
     * This function collects all the information from a web/api form and invokes
     * the relevant payment processor specific functions to perform the transaction
     *
     * @param  array $params assoc array of input parameters for this transaction
     *
     * @return array the result in an nice formatted array (or an error object)
     * @public
     */
    function doDirectPayment( &$params ) {
        $args = array( );

        $this->initialize( $args, 'DoDirectPayment' );

        $args['paymentAction']  = $params['payment_action'];
        $args['amt']            = $params['amount'];
        $args['currencyCode']   = $params['currencyID'];
        $args['invnum']         = $params['invoiceID'];
        $args['ipaddress']      = $params['ip_address'];
        $args['creditCardType'] = $params['credit_card_type'];
        $args['acct']           = $params['credit_card_number'];
        $args['expDate']        = sprintf( '%02d', $params['month'] ) . $params['year'];
        $args['cvv2']           = $params['cvv2'];
        $args['firstName']      = $params['first_name'];
        $args['lastName']       = $params['last_name'];
        $args['email']          = $params['email'];
        $args['street']         = $params['street_address'];
        $args['city']           = $params['city'];
        $args['state']          = $params['state_province'];
        $args['countryCode']    = $params['country'];
        $args['zip']            = $params['postal_code'];

        // Allow further manipulation of the arguments via custom hooks ..
        CRM_Utils_Hook::alterPaymentProcessorParams( $this, $params, $args );

        $result = $this->invokeAPI( $args );

        if ( is_a( $result, 'CRM_Core_Error' ) ) {  
            return $result;  
        }

        /* Success */
        $params['trxn_id']        = $result['transactionid'];
        $params['gross_amount'  ] = $result['amt'];
        return $params;
    }

    /** 
     * This function checks to see if we have the right config values 
     * 
     * @return string the error message if any 
     * @public 
     */ 
    function checkConfig( ) {
        $error = array( );
        if ( $this->_paymentProcessor['payment_processor_type'] == 'ClickAndPledge') {
            if ( empty( $this->_paymentProcessor['user_name'] ) ) {
                $error[] = ts( 'User ID is not set in the Administer CiviCRM &raquo; Payment Processor.' );
            }
        }
    
        if ( ! empty( $error ) ) {
            return implode( '<p>', $error );
        } else {
            return null;
        }
    }

    function doTransferCheckout( &$params, $component = 'contribute' ) {
        $config = CRM_Core_Config::singleton( );

        if ( $component != 'contribute' && $component != 'event' ) {
            CRM_Core_Error::fatal( ts( 'Component is invalid' ) );
        }
        
        $notifyURL = 
            $config->userFrameworkResourceURL . 
            "extern/ipn.php?reset=1&contactID={$params['contactID']}" .
            "&contributionID={$params['contributionID']}" .
            "&module={$component}";

        if ( $component == 'event' ) {
            $notifyURL .= "&eventID={$params['eventID']}&participantID={$params['participantID']}";
        } else {
            $membershipID = CRM_Utils_Array::value( 'membershipID', $params );
            if ( $membershipID ) {
                $notifyURL .= "&membershipID=$membershipID";
            }
            $relatedContactID = CRM_Utils_Array::value( 'related_contact', $params );
            if ( $relatedContactID ) {
                $notifyURL .= "&relatedContactID=$relatedContactID";

                $onBehalfDupeAlert = CRM_Utils_Array::value( 'onbehalf_dupe_alert', $params );
                if ( $onBehalfDupeAlert ) {
                    $notifyURL .= "&onBehalfDupeAlert=$onBehalfDupeAlert";
                }
            }
        }

        $url    = ( $component == 'event' ) ? 'civicrm/event/register' : 'civicrm/contribute/transact';
        $cancel = ( $component == 'event' ) ? '_qf_Register_display'   : '_qf_Main_display';
        $returnURL = CRM_Utils_System::url( $url,
                                            "_qf_ThankYou_display=1&qfKey={$params['qfKey']}",
                                            true, null, false );
        $cancelURL = CRM_Utils_System::url( $url,
                                            "$cancel=1&cancel=1&qfKey={$params['qfKey']}",
                                            true, null, false );

        // ensure that the returnURL is absolute.
        if ( substr( $returnURL, 0, 4 ) != 'http' ) {
            require_once 'CRM/Utils/System.php';
            $fixUrl = CRM_Utils_System::url("civicrm/admin/setting/url", '&reset=1');
            CRM_Core_Error::fatal( ts( 'Sending a relative URL to Click And Pledge is erroneous. Please make your resource URL (in <a href="%1">Administer CiviCRM &raquo; Global Settings &raquo; Resource URLs</a> ) complete.', array( 1 => $fixUrl ) ) );
        }
        
        $ClickAndPledgeParams = array( 'WID' => $this->_paymentProcessor['user_name'],
                                       'R'   => $returnURL,
                                       'D'   => $deductAmount,
                                       'B'   => $this->_paymentProcessor['signature'],
                                       'T'   => $params['amount'],
                                       'RD'  => '1',
                                       'C'   => '1',
                                       'I'   => $params['invoiceID']
                                       );
        
        // add name and address if available, CRM-3130
        $otherVars = array( 'first_name'     => 'first_name',
                            'last_name'      => 'last_name',
                            'street_address' => 'address1',
                            'city'           => 'city',
                            'state_province' => 'state',
                            'postal_code'    => 'zip',
                            'email'          => 'email' 
                            );

        foreach ( array_keys( $params ) as $p ) {
            // get the base name without the location type suffixed to it
            $parts = explode( '-', $p );
            $name  = count( $parts ) > 1 ? $parts[0] : $p;
            if ( isset( $otherVars[$name] ) ) {
                $value = $params[$p];
                if ( $name == 'state_province' ) {
                    $stateName = CRM_Core_PseudoConstant::stateProvinceAbbreviation( $value );
                    $value     = $stateName;
                }
                if ( $value ) {
                    $ClickAndPledgeParams[$otherVars[$name]] = $value;
                }
            }
        }

        // if recurring donations, add a few more items
        if ( ! empty( $params['is_recur'] ) ) {
            if ( $params['contributionRecurID'] ) {
                $notifyURL .= "&contributionRecurID={$params['contributionRecurID']}&contributionPageID={$params['contributionPageID']}";
                $ClickAndPledgeParams['notify_url'] = $notifyURL;
            } else {
                CRM_Core_Error::fatal( ts( 'Recurring contribution, but no database id' ) );
            }
            
            $ClickAndPledgeParams = array( 'WID' => $this->_paymentProcessor['user_name'],
                                           'R'   => $returnURL,
                                           'B'   => $this->_paymentProcessor['signature'],
                                           'T'   => $params['amount'],
                                           'RD'	 => '1',
                                           'C'   => '1',
                                           'I'	 => $params['invoiceID']
                                           );
        } else {
            $ClickAndPledgeParams += array( 'cmd'    => '_xclick',
                                            'amount' => $params['amount'],
                                            );
        }

        // Allow further manipulation of the arguments via custom hooks ..
        CRM_Utils_Hook::alterPaymentProcessorParams( $this, $params, $ClickAndPledgeParams );
        
        $uri = '';
        foreach ( $ClickAndPledgeParams as $key => $value ) {
            if ( $value === null ) {
                continue;
            }

            $value = urlencode( $value );
            if ( $key == 'return' ||
                 $key == 'cancel_return' ||
                 $key == 'notify_url' ) {
                $value = str_replace( '%2F', '/', $value );
            }
            $uri .= "&{$key}={$value}";
        }

        $uri = substr( $uri, 1 );
        $url = $this->_paymentProcessor['url_site'];
        $sub = empty( $params['is_recur'] ) ? 'xclick' : 'subscriptions';
         $clickandpledgeURL = "{$url}?$uri";
		
        CRM_Utils_System::redirect( $clickandpledgeURL );
    }
}