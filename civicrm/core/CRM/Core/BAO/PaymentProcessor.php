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

require_once 'CRM/Core/DAO/PaymentProcessor.php';

/**
 * This class contains payment processor related functions.
 */
class CRM_Core_BAO_PaymentProcessor extends CRM_Core_DAO_PaymentProcessor 
{
    /**
     * static holder for the default payment processor
     */
    static $_defaultPaymentProcessor = null;

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_DAO_PaymentProcessor object on success, null otherwise
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $paymentProcessor = new CRM_Core_DAO_PaymentProcessor( );
        $paymentProcessor->copyValues( $params );
        if ( $paymentProcessor->find( true ) ) {
            CRM_Core_DAO::storeValues( $paymentProcessor, $defaults );
            return $paymentProcessor;
        }
        return null;
    }
    
    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * 
     * @access public
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_PaymentProcessor', $id, 'is_active', $is_active );
    }
    
    /**
     * retrieve the default payment processor
     * 
     * @param NULL
     * 
     * @return object           The default payment processor object on success,
     *                          null otherwise
     * @static
     * @access public
     */
    static function &getDefault( ) 
    {
        if (self::$_defaultPaymentProcessor == null) {
            $params = array( 'is_default' => 1 );
            $defaults = array();
            self::$_defaultPaymentProcessor = self::retrieve($params, $defaults);
        }
        return self::$_defaultPaymentProcessor;
    }
    
    /**
     * Function  to delete payment processor
     * 
     * @param  int  $paymentProcessorId     ID of the processor to be deleted.
     * 
     * @access public
     * @static
     */
    static function del( $paymentProcessorID ) {
        if ( ! $paymentProcessorID ) {
            CRM_Core_Error::fatal( ts( 'Invalid value passed to delete function' ) );
        }

        $dao            = new CRM_Core_DAO_PaymentProcessor( );
        $dao->id        =  $paymentProcessorID;
        if ( ! $dao->find( true ) ) {
            return null;
        }

        $testDAO            = new CRM_Core_DAO_PaymentProcessor( );
        $testDAO->name      =  $dao->name;
        $testDAO->is_test   =  1;
        $testDAO->delete( );

        $dao->delete( );
    }

    /**
     * Function to get the payment processor details
     * 
     * @param  int    $paymentProcessorID payment processor id
     * @param  string $mode               payment mode ie test or live  
     * 
     * @return array  associated array with payment processor related fields
     * @static
     * @access public 
     */
    static function getPayment( $paymentProcessorID, $mode ) 
    {
        if ( ! $paymentProcessorID ) {
            CRM_Core_Error::fatal( ts( 'Invalid value passed to getPayment function' ) );
        }

        $dao            = new CRM_Core_DAO_PaymentProcessor( );
        $dao->id        =  $paymentProcessorID;
        $dao->is_active =  1;
        if ( ! $dao->find( true ) ) {
            return null;
        }

        if ( $mode == 'test' ) {
            $testDAO = new CRM_Core_DAO_PaymentProcessor( );
            $testDAO->name      = $dao->name;
            $testDAO->is_active = 1;
            $testDAO->is_test   = 1;
            if ( ! $testDAO->find( true ) ) {
                CRM_Core_Error::fatal( ts( 'Could not retrieve payment processor details' ) );
            }
            return self::buildPayment( $testDAO );
        } else {
            return self::buildPayment( $dao );
        }
    }

    /**
     * Function to build payment processor details
     *
     * @param object $dao payment processor object
     *
     * @return array  associated array with payment processor related fields
     * @static
     * @access public 
     */
    static function buildPayment( $dao ) 
    {
        $fields = array( 'name', 'payment_processor_type', 'user_name', 'password',
                         'signature', 'url_site', 'url_api', 'url_recur', 'url_button',
                         'subject', 'class_name', 'is_recur', 'billing_mode',
                         'payment_type' );
        $result = array( );
        foreach ( $fields as $name ) {
            $result[$name] = $dao->$name;
        }
        return $result;
    }

}

