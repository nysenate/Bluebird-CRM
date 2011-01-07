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

class CRM_Core_Payment_PayPalIPN extends CRM_Core_Payment_BaseIPN {

    static $_paymentProcessor = null;

    function __construct( ) {
        parent::__construct( );
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

        if ( $input['txnType']       == 'subscr_payment' &&
             $input['paymentStatus'] != 'Completed' ) {
            CRM_Core_Error::debug_log_message( "Ignore all IPN payments that are not completed" );
            echo "Failure: Invalid parameters<p>";
            return false;
        }

        $recur =& $objects['contributionRecur'];
        
        // make sure the invoice ids match
        // make sure the invoice is valid and matches what we have in the contribution record
        if ( $recur->invoice_id != $input['invoice'] ) {
            CRM_Core_Error::debug_log_message( "Invoice values dont match between database and IPN request" );
            echo "Failure: Invoice values dont match between database and IPN request<p>";
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
        $sendNotification          = false;
        $subscriptionPaymentStatus = null;
        require_once 'CRM/Core/Payment.php';
        //set transaction type
        $txnType = $_POST['txn_type'];
        switch ( $txnType ) {

        case 'subscr_signup':
            $recur->create_date            = $now;
            //some times subscr_signup response come after the
            //subscr_payment and set to pending mode.
            $statusID                      = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionRecur',
                                                                          $recur->id, 'contribution_status_id' );
            if ($statusID != 5 ) {
                $recur->contribution_status_id = 2;
            }
            $recur->processor_id           = $_POST['subscr_id'];
            $recur->trxn_id                = $recur->processor_id;
            $sendNotification              = true;
            $subscriptionPaymentStatus     = CRM_Core_Payment::RECURRING_PAYMENT_START;
            break;
            
        case 'subscr_eot':
            $recur->contribution_status_id = 1;
            $recur->end_date               = $now;
            $sendNotification              = true;
            $subscriptionPaymentStatus     = CRM_Core_Payment::RECURRING_PAYMENT_END;
            break;

        case 'subscr_cancel':
            $recur->contribution_status_id = 3;
            $recur->cancel_date            = $now;
            break;

        case 'subscr_failed':
            $recur->contribution_status_id = 4;
            $recur->cancel_date            = $now;
            break;

        case 'subscr_modify':
            CRM_Core_Error::debug_log_message( "We do not handle modifications to subscriptions right now" );
            echo "Failure: We do not handle modifications to subscriptions right now<p>";
            return false;

        case 'subscr_payment':
            if ( $first ) {
                $recur->start_date    = $now;
            } else {
                $recur->modified_date = $now;
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
            //send recurring Notification email for user
            require_once 'CRM/Contribute/BAO/ContributionPage.php';
            CRM_Contribute_BAO_ContributionPage::recurringNofify( $subscriptionPaymentStatus, $ids['contact'],
                                                                  $ids['contributionPage'], $recur );
        }

        if ( $txnType != 'subscr_payment' ) {
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

    function single( &$input, &$ids, &$objects,
                     $recur = false,
                     $first = false ) 
    {
        $contribution =& $objects['contribution'];

        // make sure the invoice is valid and matches what we have in the contribution record
        if ( ( ! $recur ) || ( $recur && $first ) ) {
            if ( $contribution->invoice_id != $input['invoice'] ) {
                CRM_Core_Error::debug_log_message( "Invoice values dont match between database and IPN request" );
                echo "Failure: Invoice values dont match between database and IPN request<p>";
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

    function main( $component = 'contribute' ) 
    {
        // CRM_Core_Error::debug_var( 'GET' , $_GET , true, true );
        // CRM_Core_Error::debug_var( 'POST', $_POST, true, true );

        require_once 'CRM/Utils/Request.php';
        
        $objects = $ids = $input = array( );
        $input['component'] = $component;

        // get the contribution and contact ids from the GET params
        $ids['contact']           = self::retrieve( 'contactID'         , 'Integer', 'GET' , true  );
        $ids['contribution']      = self::retrieve( 'contributionID'    , 'Integer', 'GET' , true  );
        
        $this->getInput( $input, $ids );

        if ( $component == 'event' ) {
            $ids['event']       = self::retrieve( 'eventID'      , 'Integer', 'GET', true );
            $ids['participant'] = self::retrieve( 'participantID', 'Integer', 'GET', true );
        } else {
            // get the optional ids
            $ids['membership']          = self::retrieve( 'membershipID'       , 'Integer', 'GET', false );
            $ids['contributionRecur']   = self::retrieve( 'contributionRecurID', 'Integer', 'GET', false );
            $ids['contributionPage']    = self::retrieve( 'contributionPageID' , 'Integer', 'GET', false );
            $ids['related_contact']     = self::retrieve( 'relatedContactID'   , 'Integer', 'GET', false );
            $ids['onbehalf_dupe_alert'] = self::retrieve( 'onBehalfDupeAlert'  , 'Integer', 'GET', false );
        }

        if ( ! $this->validateData( $input, $ids, $objects ) ) {
            return false;
        }

        self::$_paymentProcessor =& $objects['paymentProcessor'];
        if ( $component == 'contribute' ) {
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
        $input['invoice']       = self::retrieve( 'invoice'           , 'String' , 'POST', true  );
        $input['amount']        = self::retrieve( 'mc_gross'          , 'Money'  , 'POST', false  );
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


