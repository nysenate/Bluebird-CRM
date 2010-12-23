<?php

/*
 * Copyright (C) 2007
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 * Written and contributed by Ideal Solution, LLC (http://www.idealso.com)
 *
 */

/**
 *
 * @package CRM
 * @author Marshal Newrock <marshal@idealso.com>
 * $Id: Dummy.php 30063 2010-10-06 10:33:02Z ashwini $
 */

/* NOTE:
 * When looking up response codes in the Authorize.Net API, they
 * begin at one, so always delete one from the "Position in Response"
 */

require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_Dummy extends CRM_Core_Payment {
    const
        CHARSET = 'iso-8859-1';

    static protected $_mode = null;

    static protected $_params = array();

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
        $this->_processorName    = ts('Dummy Processor');
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
            self::$_singleton[$processorName] = new CRM_Core_Payment_Dummy( $mode, $paymentProcessor );
        }
        return self::$_singleton[$processorName];
    }

    /**
     * Submit a payment using Advanced Integration Method
     *
     * @param  array $params assoc array of input parameters for this transaction
     * @return array the result in a nice formatted array (or an error object)
     * @public
     */
    function doDirectPayment ( &$params ) {
        // Invoke hook_civicrm_paymentProcessor
        // In Dummy's case, there is no translation of parameters into
        // the back-end's canonical set of parameters.  But if a processor
        // does this, it needs to invoke this hook after it has done translation,
        // but before it actually starts talking to its proprietary back-end.
        
        $cookedParams = $params; // no translation in Dummy processor
        CRM_Utils_Hook::alterPaymentProcessorParams( $this,
                                                     $params,
                                                     $cookedParams );
        //end of hook invokation

        if ( $this->_mode == 'test' ) {
            $query = "SELECT MAX(trxn_id) FROM civicrm_contribution WHERE trxn_id LIKE 'test\\_%'";
            $p = array( );
            $trxn_id = strval( CRM_Core_Dao::singleValueQuery( $query, $p ) );
            $trxn_id = str_replace( 'test_', '', $trxn_id );
            $trxn_id = intval($trxn_id) + 1;
            $params['trxn_id'] = sprintf('test_%08d', $trxn_id);
        } else {
           $query = "SELECT MAX(trxn_id) FROM civicrm_contribution WHERE trxn_id LIKE 'live_%'";
            $p = array( );
            $trxn_id = strval( CRM_Core_Dao::singleValueQuery( $query, $p ) );
            $trxn_id = str_replace( 'live_', '', $trxn_id );
            $trxn_id = intval($trxn_id) + 1;
            $params['trxn_id'] = sprintf('live_%08d', $trxn_id);
        }
        $params['gross_amount'] = $params['amount'];
        return $params;
    }

    function &error ( $errorCode = null, $errorMessage = null ) {
        $e =& CRM_Core_Error::singleton( );
        if ( $errorCode ) {
            $e->push( $errorCode, 0, null, $errorMessage );
        }
        else {
            $e->push( 9001, 0, null, 'Unknown System Error.' );
        }
        return $e;
    }

    /**
     * This function checks to see if we have the right config values 
     *
     * @return string the error message if any
     * @public
     */
    function checkConfig( ) {
        return null;
    }

}         
