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

require_once 'CRM/Core/Payment/BaseIPN.php';
require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_AuthorizeNetIPN extends CRM_Core_Payment_BaseIPN {

    function __construct( ) {
        parent::__construct( );
    }

    function main( $component = 'contribute' )
    {
        require_once 'CRM/Utils/Request.php';
        
        //we only get invoice num as a key player from payment gateway response.
        //for ARB we get x_subscription_id and x_subscription_paynum
        $x_subscription_id = self::retrieve( 'x_subscription_id', 'String' );

        if ( $x_subscription_id ) {
            //Approved
            CRM_Core_Error::debug_var( '$_POST', $_POST );

            $ids = $objects = array( );
            $input['component'] = $component;
            
            // load post vars in $input
            $this->getInput( $input, $ids );
            
            // load post ids in $ids
            $this->getIDs( $ids, $input );
            
            CRM_Core_Error::debug_var( '$ids', $ids );
            CRM_Core_Error::debug_var( '$input', $input );
            
            if ( ! $this->validateData( $input, $ids, $objects ) ) {
                return false;
            }
            
            if ( $component == 'contribute' && $ids['contributionRecur'] ) {
                // check if first contribution is completed, else complete first contribution
                $first = true;
                if ( $objects['contribution']->contribution_status_id == 1 ) {
                    $first = false;
                }
                return $this->recur( $input, $ids, $objects, $first );
            }
        }
    }
    
    function recur( &$input, &$ids, &$objects, $first ) 
    {
        $recur =& $objects['contributionRecur'];
        
        // do a subscription check
        if ( $recur->processor_id != $input['subscription_id'] ) {
            CRM_Core_Error::debug_log_message( "Unrecognized subscription." );
            echo "Failure: Unrecognized subscription<p>";
            return false;
        }

        // At this point $object has first contribution loaded. 
        // Lets do a check to make sure this payment has the amount same as that of first contribution. 
        if ( $objects['contribution']->total_amount != $input['amount'] ) {
            CRM_Core_Error::debug_log_message( "Subscription amount mismatch." );
            echo "Failure: Subscription amount mismatch<p>";
            return false;
        }

        require_once 'CRM/Contribute/PseudoConstant.php';
        $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        $now = date( 'YmdHis' );

        // fix dates that already exist
        $dates = array( 'create_date', 'start_date', 'end_date', 'cancel_date', 'modified_date' );
        foreach ( $dates as $name ) {
            if ( $recur->$name ) {
                $recur->$name = CRM_Utils_Date::isoToMysql( $recur->$name );
            }
        }

        //load new contribution object if required.
        if ( ! $first ) {
            // create a contribution and then get it processed
            $contribution = new CRM_Contribute_DAO_Contribution( );
            $contribution->contact_id            = $ids['contact'];
            $contribution->contribution_type_id  = $objects['contributionType']->id;
            $contribution->contribution_page_id  = $ids['contributionPage'];
            $contribution->contribution_recur_id = $ids['contributionRecur'];
            $contribution->receive_date          = $now;
            $contribution->currency              = $objects['contribution']->currency;
            $contribution->payment_instrument_id = $objects['contribution']->payment_instrument_id;
            $contribution->amount_level          = $objects['contribution']->amount_level;
            $contribution->address_id            = $objects['contribution']->address_id;
            $objects['contribution'] =& $contribution;
        }
        $objects['contribution']->invoice_id   = md5( uniqid( rand( ), true ) );
        $objects['contribution']->total_amount = $input['amount'];
        $objects['contribution']->trxn_id      = $input['trxn_id'];
        
        // since we have processor loaded for sure at this point, 
        // check and validate gateway MD5 response if present
        $this->checkMD5 ( $ids, $input );

        if ( $input['response_code'] == 1 ) {
            // Approved
            if ( $first ) {
                $recur->start_date    = $now;
                $recur->trxn_id       = $recur->processor_id;
            }             
            $statusName = 'In Progress';
            if ( ( $recur->installments > 0 ) && 
                 ( $input['subscription_paynum'] >= $recur->installments ) ) {
                // this is the last payment
                $statusName      = 'Completed';
                $recur->end_date = $now;
            }
            $recur->modified_date          = $now;
            $recur->contribution_status_id = array_search( $statusName, $contributionStatus );
            $recur->save( );
        } else {
            // Declined
            
            $recur->contribution_status_id = array_search( 'Failed', $contributionStatus ); // failed status
            $recur->cancel_date            = $now;
            $recur->save( );

            CRM_Core_Error::debug_log_message( "Subscription payment failed - '{$input['response_reason_text']}'" );
            return $this->failed( $objects, $transaction );
        }
        
        // check if contribution is already completed, if so we ignore this ipn
        if ( $objects['contribution']->contribution_status_id == 1 ) {
            $transaction->commit( );
            CRM_Core_Error::debug_log_message( "returning since contribution has already been handled" );
            echo "Success: Contribution has already been handled<p>";
            return true;
        }


        $this->completeTransaction( $input, $ids, $objects, $transaction, $recur );
    }

    function getInput( &$input, &$ids ) {
        $input['amount']               = self::retrieve( 'x_amount'       ,        'String'  );
        $input['subscription_id']      = self::retrieve( 'x_subscription_id',      'Integer' );
        $input['response_code']        = self::retrieve( 'x_response_code',        'Integer' );
        $input['MD5_Hash']             = self::retrieve( 'x_MD5_Hash',             'String', false, '' );
        $input['fee_amount']           = self::retrieve( 'x_fee_amount',           'Money',  false, '0.00' );
        $input['net_amount']           = self::retrieve( 'x_net_amount',           'Money',  false, '0.00' );
        $input['response_reason_code'] = self::retrieve( 'x_response_reason_code', 'String', false );
        $input['response_reason_text'] = self::retrieve( 'x_response_reason_text', 'String', false );
        $input['subscription_paynum']  = self::retrieve( 'x_subscription_paynum',  'Integer',false, 0 );
        $input['trxn_id']              = self::retrieve( 'x_trans_id',             'String', false );
        if ( $input['trxn_id'] ) {
            $input['is_test'] = 0;
        } else {
            $input['is_test'] = 1;
            $input['trxn_id'] = md5( uniqid( rand( ), true ) );
        }

        if ( ! $this->getBillingID( $ids ) ) {
            return false;
        }
        $billingID = $ids['billing'];
        $params = array( 'first_name'                  => 'x_first_name',
                         'last_name'                   => 'x_last_name' ,
                         "street_address-{$billingID}" => 'x_address',
                         "city-{$billingID}"           => 'x_city',
                         "state-{$billingID}"          => 'x_state',
                         "postal_code-{$billingID}"    => 'x_zip',
                         "country-{$billingID}"        => 'x_country',
                         "email-{$billingID}"          => 'x_email'  );
        foreach ( $params as $civiName => $resName ) {
            $input[$civiName] = self::retrieve( $resName, 'String', false );
        }
    }
    
    function getIDs( &$ids, &$input ) {
        $ids['contact']       = self::retrieve( 'x_cust_id'    , 'Integer'  );
        $ids['contribution']  = self::retrieve( 'x_invoice_num', 'Integer'  );

        // joining with contribution table for extra checks
        $sql = "
    SELECT cr.id 
      FROM civicrm_contribution_recur cr
INNER JOIN civicrm_contribution co ON co.contribution_recur_id = cr.id
     WHERE cr.processor_id = '{$input['subscription_id']}' AND 
           cr.contact_id = {$ids['contact']} AND co.id = {$ids['contribution']}
     LIMIT 1";
        $ids['contributionRecur'] = CRM_Core_DAO::singleValueQuery( $sql );
        if ( ! $ids['contributionRecur'] ) {
            CRM_Core_Error::debug_log_message( "Could not find contributionRecur id" );
            echo "Failure: Missing Parameter<p>";
            exit( );
        }

        // get page id based on contribution id
        $ids['contributionPage'] = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_Contribution', 
                                                                $ids['contribution'], 
                                                                'contribution_page_id' );

        if ( $input['component'] == 'event' ) {
            // FIXME: figure out fields for event
        } else {
            // get the optional ids
            
            // Get membershipId. Join with membership payment table for additional checks
            $sql = "
    SELECT m.id 
      FROM civicrm_membership m
INNER JOIN civicrm_membership_payment mp ON m.id = mp.membership_id AND mp.contribution_id = {$ids['contribution']}
     WHERE m.contribution_recur_id = {$ids['contributionRecur']}
     LIMIT 1";
            if ( $membershipId = CRM_Core_DAO::singleValueQuery( $sql ) ) {
                $ids['membership'] = $membershipId;
            }

            // FIXME: todo related_contact and onBehalfDupeAlert. Check paypalIPN.
        }
    }

    static function retrieve( $name, $type, $abort = true, $default = null, $location = 'POST' ) 
    {
        static $store = null;
        $value = CRM_Utils_Request::retrieve( $name, $type, $store,
                                              false, $default, $location );
        if ( $abort && $value === null ) {
            CRM_Core_Error::debug_log_message( "Could not find an entry for $name in $location" );
            echo "Failure: Missing Parameter<p>";
            exit( );
        }
        return $value;
    }

    function checkMD5( $ids, $input ) {
        $paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $ids['paymentProcessor'],
                                                                       $input['is_test'] ? 'test' : 'live' );
        $paymentObject    =& CRM_Core_Payment::singleton( $input['is_test'] ? 'test' : 'live', $paymentProcessor );

        if ( ! $paymentObject->checkMD5 ( $input['MD5_Hash'], $input['trxn_id'], $input['amount'], true ) ) {
            CRM_Core_Error::debug_log_message( "MD5 Verification failed." );
            echo "Failure: Security verification failed<p>";
            exit( );
        }
        return true;
    }
}
