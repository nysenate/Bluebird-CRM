<?php
/**
 * THIS CODE WAS WRITTEN FOR CIVICRM v1.7 AND HAS NOT BEEN UPGRADED SINCE THEN
 * IF YOU NEED RECURRING SUPPORT, YOU WILL NEED TO UPGRADE THIS FILE
 *
 * IF YOU DO SO PLEASE SUBMIT YOUR CHANGES BACK TO US AS AN ISSUE AND A PATCH
 * THE ERRORS BELOW ARE INTENTIONAL 
 *
 * 
 */

/**
 *  Copyright (C) 2007
 *  Licensed to CiviCRM under the Academic Free License version 3.0.
 *
 *  Written and contributed by Advomatic, LLC (http://advomatic.com)
 *  and Ideal Solution, LLC (http://idealso.com)
 *
 *  authors:
 *  Dave Hansen-Lange <dave@advomatic.com>
 *  Marshal Newrock <marshal@idealso.com>
 */

/**
 *  This file connects to the specified INBOX via IMAP.  If there are any
 *  Automated-Recurring-Billing reports they will be processed.  These emails
 *  will contain a Successful.csv and Failed.csv.  These CSV files will be
 *  parsed and CiviContribute will be updated accordingly.  After the email is
 *  processed a summary will be sent to the specified address.  The report
 *  email will then be moved or deleted.
 *
 *  It takes the first argument as the domain-id if specified, otherwise
 *  assumes that the domain-id as 1.
 *
 *
 *  This script uses the PHP IMAP functions.  You'll need to either have the
 *  mod_imap apache module installed, or see:
 *    http://ca.php.net/manual/en/ref.imap.php
 */

/**
 * USAGE:
 *
 * Copy this file to ProcessAuthorizeReport.php and set the configuration
 * settings below.
 *
 * This should be added to crontab to run at regular intervals.  In most
 * cases, hourly or daily should suffice.
 *
 * NOTE: this script must be run as the web user, otherwise it may have
 * difficulty reporting errors.
 */

/**
 *  SET PARAMETERS HERE
 */

//  Connection paramaters
define('_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_HOST', 'mail.example.com');
define('_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PORT', null);   //optional, set null to use default
define('_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_USER', 'username');
define('_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PASS', 'password');
define('_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_INBOX', 'INBOX');
define('_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PROCESSED_MAILBOX', 'Trash');
define('_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_SECURITY', 'ssl'); // options are '', 'ssl', 'tls' (which means tls, if available), or 'notls'

// the results of this script will be sent to th following email address
define('_CRM_PROCESS_AUTHORIZE_REPORT_SUMMARY_TO_EMAIL', 'authnet@example.com');

//  debugging output
define('_CRM_PROCESS_AUTHORIZE_REPORT_DEBUG', false);

/**
 *  END OF PARAMETERS
 */


/**
 * PROGRAM FLOW
 *
 * Check specified mailbox for ARB notification emails.
 * Get info from csv files.
 * If this is the first payment:
 * * Load the first contribution using SubscriptionId as trxn_id.
 * * Set recurring contribution parameters based on csv and set contribution
 * *   trxn_id to TransactionId.
 * Load contribution page.
 * Verify that this is a valid transaction, using contact_id, recur total, 
 *   recur installments, if contribution is complete or cancelled, etc.
 * Update recurring contribution entry.
 * If this is the first payment, update first contribution.
 * Otherwise, add a new contribution.  Use settings from first contribution
 *   where necessary.
 * Create a transaction record for this contribution.
 * Add an Activity History record.
 * Move or delete processed messages
 *
 * Always assume that this is for a contribution, as events do not have
 * recurring payments
 */

require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';
require_once 'api/utils.php';
require_once 'CRM/Core/Payment/AuthorizeNet.php';

require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Date.php';
require_once 'CRM/Contribute/BAO/Contribution.php';
require_once 'CRM/Contribute/BAO/ContributionRecur.php';
require_once 'CRM/Contribute/BAO/ContributionPage.php';

define ('_CRM_PROCESS_AUTHORIZE_REPORT_STATUS_COMPLETE',  1);
define ('_CRM_PROCESS_AUTHORIZE_REPORT_STATUS_PENDING',   2);
define ('_CRM_PROCESS_AUTHORIZE_REPORT_STATUS_CANCELED',  3);
define ('_CRM_PROCESS_AUTHORIZE_REPORT_STATUS_FAILED',    4);
define ('_CRM_PROCESS_AUTHORIZE_REPORT_STATUS_CURRENT',   5);

class CRM_ProcessAuthorizeReport {

    /**
     *  array that holds log messages.  Log is then displayed to screen
     *  and also emailed to _CRM_PROCESS_AUTHORIZE_REPORT_SUMMARY_TO_EMAIL
     */
    var $summary = array();

    /**
     *  array holds processed $msg_id
     */
    var $msg_ids = array();

    /**
     *  holds attachments as arrays in the form
     *    $attachment[success.csv] = 'attachment data as a very long string';
     *  After processing is complete the attachments
     *  are emailed to _CRM_PROCESS_AUTHORIZE_REPORT_SUMMARY_TO_EMAIL
     */
    var $attachments = array();

    /**
     * Store the number of emails processed
     */
    var $emails_processed = 0;

    /**
     * The IMAP connection
     */
    var $email_conn = false;

    var $_debug = false;

    function CRM_ProcessAuthorizeReport( ) {
        _crm_initialize( );

        $config = CRM_Core_Config::singleton( );
        
        //load bootstrap to call hooks
        require_once 'CRM/Utils/System.php';
        CRM_Utils_System::loadBootStrap(  );
        
        $config->userFramework          = 'Soap';
        $config->userFrameworkClass     = 'CRM_Utils_System_Soap';
        $config->userHookClass          = 'CRM_Utils_Hook_Soap';

        if ( !function_exists( 'imap_headers' ) ) {
            die('PHP IMAP extension required to use this script');
        }

        if ( defined( '_CRM_PROCESS_AUTHORIZE_REPORT_DEBUG' ) ) {
            $this->_debug = _CRM_PROCESS_AUTHORIZE_REPORT_DEBUG;
            error_reporting(E_ALL);
            ini_set('display_errors', true);
        }
        else {
            $this->_debug = false;
            ini_set('display_errors', false);
        }
    }

    function processAuthorizeReport() {

        /**
         * Check specified mailbox for ARB notifications
         */

        if ( !defined( '_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_HOST' ) || _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_HOST == '' ) {
            $this->_criticalError('No IMAP server defined');
        }

        if ( !defined( '_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_USER' ) || _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_USER == '' ) {
            $this->_criticalError('No IMAP username provided');
        }

        if ( !defined( '_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PASS') || _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PASS == '' ) {
            $this->_criticalError('No IMAP password provided');
        }

        if ( defined( '_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_SECURITY' ) ) {
            $imap_security = '/' . _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_SECURITY . '/novalidate-cert';
        }
        else {
            $imap_security = '';
        }

        if ( defined( '_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PORT' ) && _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PORT != '' ) {
            $imap_port = ':' . _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PORT;
        }
        elseif ( $imap_security ) {
            $imap_port = ':993';
        }
        else {
            $imap_port = '';
        }

        if ( defined( '_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_INBOX' ) && _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_INBOX != '' ) {
            $imap_inbox = _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_INBOX;
        }
        else {
            $imap_inbox = 'INBOX';
        }

        // create connection string and connect
        $conn_str = '{' . _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_HOST . $imap_port . '/imap' . $imap_security . '}' . $imap_inbox;

        $this->email_conn = @imap_open($conn_str, _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_USER, _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PASS) or
            $this->_criticalError('Cannot connect to '. _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_HOST .' as '. _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_USER);

        // Get list of Automated Recurring Billing messages
        $msgList = imap_search( $this->email_conn, 'UNDELETED SUBJECT "Summary of Automated Recurring Billing"' );

        if ( !$msgList ) {
            // no mesasges.  nothing to do.
            exit;
        }

        /**
         * Get info from CSV files
         */

        foreach ( $msgList as $msg_id ) {
            $mailHeader = imap_headerinfo( $this->email_conn, $msg_id );
            $this->_addToSummary( null ); // insert blank line.
            $this->_addToSummary( 'Processing an ARB email' );

            $mail_date = date( 'YmdHis', strtotime( $mailHeader->date ) );
            $mail_date = CRM_Utils_Date::isoToMysql( $mail_date );

            // extract the CSV files
            $attachments = $this->_getAttachmentsData( $msg_id );
            foreach ( $attachments as $key => $attachment ) {
                $this->_addAttachment( $attachment, $msg_id, $key );
                $attachment = $this->_csv_to_array( $attachment );
                $attachments[$key] = $attachment;
            }

            // process successful.csv
            if ( isset( $attachments['successful.csv'] ) ) {
                $this->_process_csv( $attachments['successful.csv'], 'successful.csv', $mail_date );
                $process_successful = true;
            }
            else {
                $process_successful = false;
            }

            // process failed.csv
            if ( isset( $attachments['failed.csv'] ) ) {
                $this->_process_csv( $attachments['failed.csv'], 'failed.csv', $mail_date );
                $process_failed = true;
            }
            else {
                $process_failed = false;
            }

            // mark message as processed
            if ( $process_successful && $process_failed ) {
                $this->_addMsgID( $msg_id );
                $this->emails_processed++;
            }
        }

        $this->_addToSummary( 'Total Authorize.net emails processed: '. $this->emails_processed );

        // send summary
        $this->_sendSummaryEmail( );

        // get rid of processed messages
        if ( defined( '_CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PROCESSED_MAILBOX' ) && _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PROCESSED_MAILBOX != '' ) {
            $msg_id_list = implode( ',', $this->_getMsgIDs() );
            imap_mail_move( $this->email_conn, $msg_id_list, _CRM_PROCESS_AUTHORIZE_REPORT_IMAP_PROCESSED_MAILBOX );
        }
        else {
            foreach( $this->_getMsgIDs( ) as $id ) {
                imap_delete( $this->email_conn, $id );
            }
        }

        if ( $this->_debug ) {
            print_r( imap_alerts( ) );
            print_r( imap_errors( ) );
        }
        imap_close( $this->email_conn );

      }

      /**
       *  Returns an array of file attachments for the given message
       *  @param stream     $conn         An IMAP stream returned by imap_open().
       *  @param integer    $msg_id       The message number
       *
       *  @return array                   An array containing the attachments
       */
      function _getAttachmentsData( $msg_id ) {
          $attachments = array();
          $msg_structure = imap_fetchstructure( $this->email_conn, $msg_id );
          $part_numbers[] = $this->_getAttachmentPartNumbers( $msg_structure );
          foreach ( array_shift( $part_numbers ) as $part_number => $file_name ) {
              $body = preg_replace( "/=(\r?)\n/", '', imap_fetchbody( $this->email_conn, $msg_id, $part_number ) );
              $attachments[$file_name] = $body;
          }
          return $attachments;
    }

    /**
     *  Returns an array of message part numbers of attachments attachments for the given message structure
     *  @param array    $message_part   message structure as derived from imap_fetchstructure()
     *  @param integer  $part_number    Used for recursive calls to process sub-parts
     *
     *  @return array                   An array associative containing the part numbers and attachment names
     */
    function _getAttachmentPartNumbers( $message_part, $part_number = 0 ) {
        $attachment_parts = array( );
        $sub_part_number = 1;
        foreach ( $message_part->parts as $part ) {
            $new_part_number = ( $part_number != 0 ? $part_number .'.' : '' ) . $sub_part_number;
            if ( isset( $part->parts ) ) {
                $s_part = $part->parts;   // in two steps because of php bug
                // the message has another message embedded within (a forward perhaps), so process recursively
                foreach( $s_part as $sub_part ) {
                    $sub_attachment_parts = $this->_getAttachmentPartNumbers( $sub_part, $new_part_number );
                    $attachment_parts = array_merge( $attachment_parts, $sub_attachment_parts );
                }
            }
            else {
                $name = '';
                if ( $part->ifdparameters && !empty( $part->dparameters ) ) {
                    foreach ( $part->dparameters as $child ) {
                        if ( strtolower( $child->attribute ) == 'name' || strtolower ( $child->attribute ) == 'filename' ) {
                            $attachment_parts[$new_part_number] = strtolower( $child->value );
                        }
                    }
                }
                if ( empty( $attachment_parts[$new_part_number] ) ) {
                    if ( $part->ifparameters && !empty( $part->parameters ) )  {
                        foreach ($part->parameters as $child) {
                            if ( strtolower( $child->attribute ) == 'name' || strtolower( $child->attribute) == 'filename' ) {
                                $name = strtolower( $child->value );
                                $attachment_parts[$new_part_number] = strtolower( $child->value );
                            }
                        }
                    }
                }
            }
            $sub_part_number ++;
        }
        return $attachment_parts;
    }
    
    // see http://php.net/manual/en/function.fgetcsv.php#62524
    // NOTE: consider using this for CRM_Core_Payment_AuthorizeNet::explode_csv
    function _csv_to_array( $string ) {
        if ( strpos( $string, "\r\n" ) !== false ) {
            $lines = explode ( "\r\n", $string );
        }
        elseif ( strpos( $string, "\r" ) !== false ) {
            $lines = explode ( "\r", $string );
        }
        elseif ( strpos( $string, "\n" ) !== false ) {
            $lines = explode ( "\n", $string );
        }

        $csv_array = array( );
        foreach ( $lines as $line ) {
            // check for header line and skip
            if ( strpos( $line, 'SubscriptionID' ) !== FALSE ) {
                continue;
            }
            $expr = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
            $results = preg_split( $expr, trim( $line ) );
            $csv_array[] =  preg_replace( "/^\"(.*)\"$/", "$1", $results );
        }
        return $csv_array;
    }

    function _addAttachment( $attachment, $msg_id, $filename ) {
        $this->attachments[$msg_id .'-'. $filename] = $attachment;
    }

    function _getAttachments( ) {
        return $this->attachments;
    }

    function _addMsgID( $msg_id ) {
        $this->msg_ids[] = $msg_id;
    }

    function _getMsgIDs( ) {
        return $this->msg_ids;
    }

    function _addToSummary( $addition ) {
        $this->summary[] = $addition;
        if ( $this->_debug ) {
            echo $addition . "\n";
        }
    }

    function _criticalError( $addition ) {
        $this->_addToSummary('CRITICAL ERROR: '. $addition);
        //$this->_sendSummaryEmail($conn);

        if ( $this->email_conn ) {
            @imap_close( $this->email_conn );
        }
        die();
    }

    function _summaryToString( ) {
        $string = '';
        foreach( $this->summary as $line) {
            $string .= $line ."\n";
        }
        return $string;
    }

    function _sendSummaryEmail( ) {
        if ( count( $this->_getMsgIDs( ) ) < 1 ) {
            $this->_addToSummary('No ARB emails to process');
            return;
        }

        $summary = $this->_summaryToString( );

        $envelope["from"]= ini_get( 'sendmail_from' );

        $body[] = array (
            "type"    => TYPEMULTIPART,
            "subtype" => "mixed",
        );

        $body[] = array (
            'type'                  => 0,
            'encoding'              => 0,
            'subtype'               => "PLAIN",
            'contents.data'         => $summary,
        );

        foreach ( $this->_getAttachments( ) as $filename => $attachment ) {
            $body[] = array (
                'type'                  => 0,
                'encoding'              => 0,
                'subtype'               => "X-COMMA-SEPARATED-VALUES",
                'description'           => $filename,
                'disposition.type'      => 'attachment',
                'disposition'           => array ('filename' => $filename),
                'dparameters.filename'  => $filename,
                'parameters.name'       => $filename,
                'contents.data'         => $attachment,
            );
        }

        $msg = imap_mail_compose( $envelope, $body );

        list( $t_header, $t_body ) = preg_split( "/\r\n\r\n/", $msg, 2 );
        $t_header = str_replace( "\r", '', $t_header );

        $success = imap_mail( _CRM_PROCESS_AUTHORIZE_REPORT_SUMMARY_TO_EMAIL, 'Authorize.net Report Processesing Summary', $t_body, $t_header );
    }

    function _get_contribution_status( $arb_resp_code ) {
        switch ( $arb_resp_code ) {
            case CRM_Core_Payment_AuthorizeNet::AUTH_APPROVED:
                return _CRM_PROCESS_AUTHORIZE_REPORT_STATUS_COMPLETE;
                break;
            case CRM_Core_Payment_AuthorizeNet::AUTH_DECLINED:
                return _CRM_PROCESS_AUTHORIZE_REPORT_STATUS_FAILED;
                break;
            case CRM_Core_Payment_AuthorizeNet::AUTH_ERROR:
                $this->_addToSummary( 'AUTHORIZE.NET EXPERIENCED AN ERROR TRYING TO PROCESS THIS TRANSACTION.  PLEASE REVIEW' );
                return _CRM_PROCESS_AUTHORIZE_REPORT_STATUS_FAILED;
                break;
            default:
                $this->_addToSummary( "AN UNKNOWN RESPONSE CODE: $arb_resp_code WAS RECEIVED FOR THIS TRANSACTION. PLEASE REVIEW." );
                return _CRM_PROCESS_AUTHORIZE_REPORT_STATUS_FAILED;
                break;
        }
    }

    function _process_csv( $csv_array, $csv_name, $mail_date ) {
        $this->_addToSummary( "Processing $csv_name" );

        foreach ( $csv_array as $row ) {
            $this->_addToSummary( null ); // insert blank line.
            // check if this is a blank line
            if ( count($row) <= 1 ) {
                continue;
            }

            $subscriptionId       = $row[0];
            $subscriptionStatus   = $row[1];
            $paymentNum           = $row[2];
            $totalRecurrences     = $row[3];
            $transactionId        = $row[4];
            $amount               = $row[5];
            $currency             = $row[6];
            $custFirstName        = $row[8];
            $custLastName         = $row[9];
            $contributionStatus   = $row[10];

            $recur = new CRM_Contribute_DAO_ContributionRecur( );
            
            $first_contribution = new CRM_Contribute_DAO_Contribution( );
            
            // If this is the first payment, load recurring contribution and update
            if ( $paymentNum == 1 ) {
                // Load contribution using SubscriptionID as trxn_id
                $first_contribution->trxn_id = $subscriptionId;

                if ( !$first_contribution->find( true ) ) {
                    $this->_addToSummary("THE RECURRING TRANSACTION FOR SUBSCRIPTION $subscriptionId COULD NOT BE FOUND. A TRANSACTION HAS OCCURED THAT WAS NOT EXPECTED.  PLEASE REVIEW $csv_name." );
                    continue;
                }

                // Load recurring contribution from contribution
                $recur->id = $first_contribution->contribution_recur_id;
                if ( !$recur->find( true ) ) {
                    $this->_addToSummary("INITIAL RECURRING CONTRIBUTION NOT FOUND FOR $subscriptionId. PLEASE REVIEW $csv_name");
                    continue;
                }

                $recur->start_date = $mail_date;
                $recur->processor_id = $subscriptionId;
                $recur->trxn_id = $subscriptionId;
                $recur->contribution_status_id = _CRM_PROCESS_AUTHORIZE_REPORT_STATUS_CURRENT;

                // update transaction id for contribution
                $first_contribution->trxn_id = $transactionId;
                $first_contribution->receive_date = $mail_date;
                $first_contribution->contribution_status_id = $this->_get_contribution_status( $contributionStatus );

                // load contribution page
                $contribution_page = new CRM_Contribute_DAO_ContributionPage( );
                $contribution_page->id = $first_contribution->contribution_page_id;
                if ( !$contribution_page->find( true) ) {
                    $this->_addToSummary("COULD NOT FIND CONTRIBUTION PAGE FOR $subscriptionId. PLEASE REVIEW $csv_name");
                    continue;
                }

                // is there an email receipt
                if ( $contribution_page->is_email_receipt ) {
                    $first_contribution->receipt_date = date( 'YmdHis' );
                }
            }
            else {
                $recur->processor_id = $subscriptionId;
                if ( !$recur->find( true ) ) {
                    $this->_addToSummary("THE RECURRING TRANSACTION FOR SUBSCRIPTION $subscriptionId COULD NOT BE FOUND. A TRANSACTION HAS OCCURED THAT WAS NOT EXPECTED.  PLEASE REVIEW $csv_name." );
                    continue;
                }
                $recur->modified_date = $mail_date;

                // load first contribution
                $first_contribution->contribution_recur_id = $recur->id;
                $first_contribution->orderBy( 'receive_date' );
                $first_contribution->limit( 1 );
                if ( !$first_contribution->find( true ) ) {
                    $this->_addToSummary("CONTRIBUTION RECORD FOR SUBSCRIPTION $subscriptonId COULD NOT BE FOUND.  PLEASE REVIEW $csv_name");
                    continue;
                }
                
                // load contribution page
                $contribution_page = new CRM_Contribute_DAO_ContributionPage( );
                $contribution_page->id = $first_contribution->contribution_page_id;
                if ( !$contribution_page->find( true) ) {
                    $this->_addToSummary("COULD NOT FIND CONTRIBUTION PAGE FOR $subscriptionId. PLEASE REVIEW $csv_name");
                    continue;
                }
            }

            // is this valid for failed transactions also?
            if ( $amount != $recur->amount ) {
                $this->_addToSummary("AN UNEXPECTED AMOUNT WAS RECEIVED FOR SUBSCRIPTION $subscriptionId. SKIPPING THIS TRANSACTION. PLEASE REVIEW $csv_name");
                continue;
            }

            // Verify contact exists
            if ( !$recur->contact_id ) {
                // assuming if contact_id is set, contact exists
                $this->_addToSummary("NO USER IS ASSOCIATED WITH THE CONTRIBUTION FOR SUBSCRIPTION $subscrptionId, EXPECTED '$custFirstName $custLastName'. PLEASE REVIEW $csv_name");
                continue;
            }
            
            // Verify number of recurrences
            if ( $recur->installments != $totalRecurrences ) {
                $this->_addToSummary("SUBSCRIPTION $subscriptionId EXPECTS {$recur->installments}, OFFERED $totalRecurrences. PLEASE REVIEW $csv_name");
                continue;
            }

            // Check if this contribution is complete
            if ( !empty( $recur->end_date ) && $recur->end_date != '0000-00-00 00:00:00' ) {
                $this->_addToSummary("SUBSCRIPTION $subscriptionId IS MARKED AS COMPLETE. PLEASE REVIEW $csv_name");
                continue;
            }

            if ( !empty( $recur->cancel_date ) && $recur->cancel_date != '0000-00-00 00:00:00' ) {
                $this->_addToSummary("SUBSCRIPTION $subscriptionId IS MARKED AS CANCELLED. PLEASE REVIEW $csv_name");
                continue;
            }

            if ( $paymentNum == $totalRecurrences ) {
                $recur->end_date = $mail_date;
                $recur->contribution_status_id = _CRM_PROCESS_AUTHORIZE_REPORT_STATUS_COMPLETE;
            }

            if ( $contributionStatus != CRM_Core_Payment_AuthorizeNet::AUTH_APPROVED ) {
                $recur->failure_count++;
            }

            CRM_Core_DAO::transaction( 'BEGIN' );

            if ( !$recur->save( ) ) {
                $this->_addToSummary("THE RECURRING CONTRIBUTION COULD NOT BE UPDATED. PLEASE REVIEW $csv_name FOR subscription_id=$subscription_id");
                CRM_Core_DAO::transaction( 'ROLLBACK' );
                continue;
            }
            $this->_addToSummary("The recurring transaction has been updated." );

            if ( $paymentNum == 1 ) {
                // update first contribution
                if ( !$first_contribution->save( ) ) {
                     $this->_addToSummary("THE CONTRIBUTION COULD NOT BE UPDATED. PLEASE REVIEW $csv_name FOR subscription_id=$subscription_id");
                     CRM_Core_DAO::transaction( 'ROLLBACK' );
                     continue;
                }
                // copy $first_contribution to $contribution for use later
                $contribution = $first_contribution;
            }
            else {
                // create a contribution and then get it processed
                $contribution = new CRM_Contribute_DAO_Contribution( );

                // make sure that the transaction doesn't already exist
                $contribution->trxn_id = $transactionId;
                if ( $contribution->find( ) ) {
                    $this->_addToSummary("THE TRANSACTION $transaction_id ALREADY EXISTS IN CIVICRM. PLEASE REVIEW $csv_name FOR subscription_id=$subscription_id" );
                    CRM_Core_DAO::transaction( 'ROLLBACK' );
                    continue;
                }

                $contribution->contribution_recur_id = $recur->id;
                
                $contribution->receive_date = $mail_date;
                $contribution->total_amount = $amount;
                $contribution->net_amount = $amount;
                $contribution->trxn_id = $transactionId;
                $contribution->currency = $currency;
                $contribution->contribution_status_id = $this->_get_contribution_status( $contributionStatus );

                $contribution->contact_id = $first_contribution->contact_id;
                $contribution->contribution_type_id = $first_contribution->contribution_type_id;
                $contribution->contribution_page_id = $first_contribution->contribution_page_id;
                $contribution->payment_instrument_id = $first_contribution->payment_instrument_id;
                $contribution->is_test = $first_contribution->is_test;
                $contribution->invoice_id = md5( uniqid( rand(), true ) );

                if ( $contribution_page->is_email_receipt ) {
                    $contribution->receipt_date = date( 'YmdHis' );
                }

                if ( !$contribution->save( ) ) {
                    $this->_addToSummary("THE CONTRIBUTION COULD NOT BE SAVED. PLEASE REVIEW $csv_name FOR subscription_id=$subscription_id");
                    CRM_Core_DAO::transaction( 'ROLLBACK' );
                    continue;
                }
            }

            $this->_addToSummary('Contribution saved');

            // create the transaction record
            $trxnParams = array(
                'entity_table'      => 'civicrm_contribution',
                'entity_id'         => $contribution->id,
                'trxn_date'         => $mail_date,
                'trxn_type'         => 'Debit',
                'total_amount'      => $amount,
                'fee_amount'        => $contribution->fee_amount,
                'net_amount'        => $contribution->net_amount,
                'currency'          => $contribution->currency,
                'payment_processor' => 'AuthNet_AIM',
                'trxn_id'           => $contribution->trxn_id,
            );

            require_once 'CRM/Contribute/BAO/FinancialTrxn.php';
            $trxn =& CRM_Contribute_BAO_FinancialTrxn::create($trxnParams);
            if (is_a($trxn,  'CRM_Core_Error')) {
                $this->_addToSummary("A TRANSACTION RECORD COULD NOT BE CREATED. PLEASE REVIEW $csv_name FOR subscription_id=$subscription_id");
                CRM_Core_DAO::transaction('ROLLBACK');
                continue;
            }
            else {
                $this->_addToSummary("Transaction record created." );
            }

            // get the title of the contribution page
            $title = $contribution_page->title;

            // format the money
            require_once 'CRM/Utils/Money.php';
            $formattedAmount = CRM_Utils_Money::format($amount, $contribution->currency);

            CRM_Core_DAO::transaction('COMMIT');

            // get the contribution type
            require_once 'CRM/Contribute/BAO/ContributionType.php';
            $contribution_type_name = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionType',
                                                  $contribution->contribution_type_id,
                                                  'name' );

            // create an activity history record
            $ahParams = array(
                'entity_table'     => 'civicrm_contact',
                'entity_id'        => $recur->contact_id,
                'activity_type'    => $contribution_type_name,
                'module'           => 'CiviContribute',
                'callback'         => 'CRM_Contribute_Page_Contribution::details',
                'activity_id'      => $contribution->id,
                'activity_summary' => "$formattedAmount - $title (online)",
                'activity_date'    => $mail_date,
            );

            require_once 'api/History.php';
            if (is_a(crm_create_activity_history($ahParams), 'CRM_Core_Error')) {
                $this->_addToSummary("AN ACTIVITY HISTORY RECORD COULD NOT BE CREATED.");
            }
            else {
                $this->_addToSummary("Activity History record created." );
            }

            $this->_addToSummary("Transaction $transactionId has been processed.");
            $first_contribution->free();
            $contribution_page->free();
            $contribution->free();
            $recur->free();
            $trxn->free();
        }

        $this->_addToSummary("Done processing $csv_name");
        $this->_addToSummary('');
    }

}

$domainId = isset( $argv[1] ) ? intval($argv[1]) : 1;
//echo "\n Processing <br /> \n";
$obj = new CRM_ProcessAuthorizeReport($domainId);
$obj->processAuthorizeReport();
//echo "\n\n All reports processed. (Done) \n";

