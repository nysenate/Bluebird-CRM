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

require_once 'CRM/Core/Payment/BaseIPN.php';

class CRM_Core_Payment_PayPalProIPN extends CRM_Core_Payment_BaseIPN {
    
    static $_paymentProcessor = null;

    function __construct( ) {
        parent::__construct( );
    }

    function getValue( $name, $abort = true ) {

        if ( !empty( $_POST ) ) { 
            $rpInvoiceArray = array();
            $value          = null;
            $rpInvoiceArray = explode( '&', $_POST['rp_invoice_id'] );
            foreach ( $rpInvoiceArray as $rpInvoiceValue ) {
                $rpValueArray = explode( '=', $rpInvoiceValue );
                if ( $rpValueArray[0] == $name ) {
                    $value = $rpValueArray[1];
                }
            }

            if ( $value == null && $abort ) {
                echo "Failure: Missing Parameter $name<p>";
                exit( );
            } else {
                return $value;
            }
        } else {
            return null;
        }
    }
    
    static function retrieve( $name, $type, $location = 'POST', $abort = true ) 
    {
        static $store = null;
        $value = CRM_Utils_Request::retrieve( $name, $type, $store,
                                              false, null, $location );
        if ( $abort && $value === null ) {
            CRM_Core_Error::debug_log_message( "Could not find an entry for $name in $location" );
            echo "Failure: Missing Parameter<p>";
            exit( );
        }
        return $value;
    }

    function recur( &$input, &$ids, &$objects, $first ) 
    {
        if ( ! isset( $input['txnType'] ) ) {
            CRM_Core_Error::debug_log_message( "Could not find txn_type in input request" );
            echo "Failure: Invalid parameters<p>";
            return false;
        }
        
        if ( $input['txnType']       == 'recurring_payment' &&
             $input['paymentStatus'] != 'Completed' ) {
            CRM_Core_Error::debug_log_message( "Ignore all IPN payments that are not completed" );
            echo "Failure: Invalid parameters<p>";
            return false;
        }
        
        $recur =& $objects['contributionRecur'];
     
        // make sure the invoice ids match
        // make sure the invoice is valid and matches what we have in
        // the contribution record
        if ( $recur->invoice_id != $input['invoice'] ) {
            CRM_Core_Error::debug_log_message( "Invoice values dont match between database and IPN request recur is " . $recur->invoice_id . " input is " . $input['invoice']);
            echo "Failure: Invoice values dont match between database and IPN request recur is " . $recur->invoice_id . " input is " . $input['invoice'];
            return false;
        }
        
        $now = date( 'YmdHis' );
        
        // fix dates that already exist
        $dates = array( 'create', 'start', 'end', 'cancel', 'modified' );
        foreach ( $dates as $date ) {
            $name = "{$date}_date";
            if ( $recur->$name ) {
                $recur->$name = CRM_Utils_Date::isoToMysql( $recur->$name );
            }
        }
        
        $sendNotification = false;
        $subscriptionPaymentStatus = null;
        //List of Transaction Type
        /*
         recurring_payment_profile_created    			RP Profile Created
         recurring_payment 					RP Sucessful Payment
         recurring_payment_failed                               RP Failed Payment
         recurring_payment_profile_cancel     			RP Profile Cancelled
         recurring_payment_expired 				RP Profile Expired
         recurring_payment_skipped				RP Profile Skipped
         recurring_payment_outstanding_payment			RP Sucessful Outstanding Payment
         recurring_payment_outstanding_payment_failed	        RP Failed Outstanding Payment
         recurring_payment_suspended				RP Profile Suspended
         recurring_payment_suspended_due_to_max_failed_payment	RP Profile Suspended due to Max Failed Payment
        */

        //set transaction type
        $txnType = $_POST['txn_type'];
        require_once 'CRM/Core/Payment.php';
        //Changes for paypal pro recurring payment
        
        switch ( $txnType ) {
            
        case 'recurring_payment_profile_created':
            $recur->create_date            = $now;
            $recur->contribution_status_id = 2;
            $recur->processor_id           = $_POST['recurring_payment_id'];
            $recur->trxn_id                = $recur->processor_id;
            $subscriptionPaymentStatus     = CRM_Core_Payment::RECURRING_PAYMENT_START;
            $sendNotification              = true;
            break;
        
        case 'recurring_payment':
            if ( $first ) {
                $recur->start_date    = $now;
            } else {
                $recur->modified_date = $now;
            }
            
            //contribution installment is completed 
            if ( $_POST['profile_status'] == 'Expired' ) {
                $recur->contribution_status_id = 1;
                $recur->end_date               = $now;
                $sendNotification              = true;
                $subscriptionPaymentStatus     = CRM_Core_Payment::RECURRING_PAYMENT_END;
            }
 
            // make sure the contribution status is not done
            // since order of ipn's is unknown
            if ( $recur->contribution_status_id != 1 ) {
                $recur->contribution_status_id = 5;
            }
            
            break;
        }
	
        $recur->save( );

        if ( $sendNotification ) {
            $autoRenewMembership = false;
            if ( $recur->id && 
                 isset( $ids['membership'] ) && $ids['membership'] ) {
                $autoRenewMembership = true;
            }
            //send recurring Notification email for user
            require_once 'CRM/Contribute/BAO/ContributionPage.php';
            CRM_Contribute_BAO_ContributionPage::recurringNofify( $subscriptionPaymentStatus, 
                                                                  $ids['contact'], 
                                                                  $ids['contributionPage'], 
                                                                  $recur,
                                                                  $autoRenewMembership );
        }

        if ( $txnType != 'recurring_payment' ) {
            return;
        }
	
        if ( ! $first ) {
            // create a contribution and then get it processed
            $contribution = new CRM_Contribute_DAO_Contribution( );
            $contribution->contact_id = $ids['contact'];
            $contribution->contribution_type_id  = $objects['contributionType']->id;
            $contribution->contribution_page_id  = $ids['contributionPage'];
            $contribution->contribution_recur_id = $ids['contributionRecur'];
            $contribution->receive_date          = $now;
            $contribution->currency              = $objects['contribution']->currency;
            $contribution->payment_instrument_id = $objects['contribution']->payment_instrument_id;
            $contribution->amount_level          = $objects['contribution']->amount_level;

            $objects['contribution'] =& $contribution;
        }

        $this->single( $input, $ids, $objects,
                       true, $first );
    }
    
    function single( &$input, &$ids, &$objects, $recur = false, $first = false ) 
    {
        $contribution =& $objects['contribution'];
        
        // make sure the invoice is valid and matches what we have in the contribution record
        if ( ( ! $recur ) || ( $recur && $first ) ) {
            if ( $contribution->invoice_id != $input['invoice'] ) {
                CRM_Core_Error::debug_log_message( "Invoice values dont match between database and IPN request" );
                echo "Failure: Invoice values dont match between database and IPN request<p>contribution is" . $contribution->invoice_id  . " and input is " .$input['invoice']  ;
                return false;
            }
        } else {
            $contribution->invoice_id = md5( uniqid( rand( ), true ) );
        }

        if ( ! $recur ) {
            if ( $contribution->total_amount != $input['amount'] ) {
                CRM_Core_Error::debug_log_message( "Amount values dont match between database and IPN request" );
                echo "Failure: Amount values dont match between database and IPN request<p>";
                return false;
            }
        } else {
            $contribution->total_amount = $input['amount'];
        }
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        // fix for CRM-2842
        //  if ( ! $this->createContact( $input, $ids, $objects ) ) {
        //       return false;
        //  }
        
        $participant =& $objects['participant'];
        $membership  =& $objects['membership' ];
        
        $status = $input['paymentStatus'];
        if ( $status == 'Denied' || $status == 'Failed' || $status == 'Voided' ) {
            return $this->failed( $objects, $transaction );
        } else if ( $status == 'Pending' ) {
            return $this->pending( $objects, $transaction );
        } else if ( $status == 'Refunded' || $status == 'Reversed' ) {
            return $this->cancelled( $objects, $transaction );
        } else if ( $status != 'Completed' ) {
            return $this->unhandled( $objects, $transaction );
        }
        
        // check if contribution is already completed, if so we ignore this ipn
        if ( $contribution->contribution_status_id == 1 ) {
            $transaction->commit( );
            CRM_Core_Error::debug_log_message( "returning since contribution has already been handled" );
            echo "Success: Contribution has already been handled<p>";
            return true;
        }
        
        $this->completeTransaction( $input, $ids, $objects, $transaction, $recur );
    }
    
    function main( $component = 'contribute' ) {
        CRM_Core_Error::debug_var('GET' , $_GET , true , true);
        CRM_Core_Error::debug_var('POST', $_POST, true , true);
        
        require_once 'CRM/Utils/Request.php';
        
        $objects = $ids = $input = array( );
        $input['component'] = $component;
        
        // get the contribution and contact ids from the GET params
        $ids['contact']           = self::getValue('c', true);
        $ids['contribution']      = self::getValue('b', true);
        
        $this->getInput( $input, $ids );
        
        if ( $component == 'event' ) {
            $ids['event']               = self::getValue('e', true);
            $ids['participant']         = self::getValue('p', true);
            $ids['contributionRecur']   = self::getValue('r', false);
            
        } else {
            // get the optional ids
            $ids['membership']          = self::retrieve('membershipID', 'Integer', 'GET', false );
            $ids['contributionRecur']   = self::getValue('r', false );
            $ids['contributionPage']    = self::getValue('p', false );
            $ids['related_contact']     = self::retrieve('relatedContactID' , 'Integer', 'GET', false );
            $ids['onbehalf_dupe_alert'] = self::retrieve('onBehalfDupeAlert', 'Integer', 'GET', false );
        }

        if ( !$ids['membership'] && $ids['contributionRecur'] ) {
            $sql = "
    SELECT m.id 
      FROM civicrm_membership m
INNER JOIN civicrm_membership_payment mp ON m.id = mp.membership_id AND mp.contribution_id = %1
     WHERE m.contribution_recur_id = %2
     LIMIT 1";
            $sqlParams = array( 1 => array( $ids['contribution'],      'Integer' ),
                                2 => array( $ids['contributionRecur'], 'Integer' ) );
            if ( $membershipId = CRM_Core_DAO::singleValueQuery( $sql, $sqlParams ) ) {
                $ids['membership'] = $membershipId;
            }
        }

        if ( ! $this->validateData( $input, $ids, $objects ) ) {
            return false;
        }
        
        self::$_paymentProcessor =& $objects['paymentProcessor'];
        if ( $component == 'contribute' || $component == 'event' ) {
            if ( $ids['contributionRecur'] ) {
                // check if first contribution is completed, else complete first contribution
                $first = true;
                if ( $objects['contribution']->contribution_status_id == 1 ) {
                    $first = false;
                }
                return $this->recur( $input, $ids, $objects, $first );
            } else {
                return $this->single( $input, $ids, $objects, false, false );
            }
        } else {
            return $this->single( $input, $ids, $objects, false, false );
        }
    }
    
    function getInput( &$input, &$ids ) {

        if ( ! $this->getBillingID( $ids ) ) {
            return false;
	    }
        
        $input['txnType']       = self::retrieve( 'txn_type'          , 'String' , 'POST', false );      
        $input['paymentStatus'] = self::retrieve( 'payment_status'    , 'String' , 'POST', false  );	
        $input['invoice']       = self::getValue( 'i', true);

        $input['amount']        = self::retrieve( 'mc_gross'          , 'Money'  , 'POST', false );
        $input['reasonCode']    = self::retrieve( 'ReasonCode'        , 'String' , 'POST', false );
        
        $billingID = $ids['billing'];
        $lookup = array( "first_name"                  => 'first_name',
                         "last_name"                   => 'last_name' ,
                         "street_address-{$billingID}" => 'address_street',
                         "city-{$billingID}"           => 'address_city',
                         "state-{$billingID}"          => 'address_state',
                         "postal_code-{$billingID}"    => 'address_zip',
                         "country-{$billingID}"        => 'address_country_code' );
        foreach ( $lookup as $name => $paypalName ) {
            $value = self::retrieve( $paypalName, 'String', 'POST', false );
            $input[$name] = $value ? $value : null;
        }
        
        $input['is_test']    = self::retrieve( 'test_ipn'     , 'Integer', 'POST', false );
        $input['fee_amount'] = self::retrieve( 'mc_fee'       , 'Money'  , 'POST', false );
        $input['net_amount'] = self::retrieve( 'settle_amount', 'Money'  , 'POST', false );
        $input['trxn_id']    = self::retrieve( 'txn_id'       , 'String' , 'POST', false );
    }
}


