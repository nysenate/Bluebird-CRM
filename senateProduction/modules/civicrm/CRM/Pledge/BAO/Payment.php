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

require_once 'CRM/Pledge/DAO/Payment.php';

class CRM_Pledge_BAO_Payment extends CRM_Pledge_DAO_Payment
{

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }
    
    /**
     * Function to get pledge payment details
     *  
     * @param int $pledgeId pledge id
     *
     * @return array associated array of pledge payment details
     * @static
     */
    static function getPledgePayments( $pledgeId )
    {
        $query = "
SELECT civicrm_pledge_payment.id id, scheduled_amount, scheduled_date, reminder_date, reminder_count,
        total_amount, receive_date, civicrm_option_value.name as status, civicrm_contribution.id as contribution_id
FROM civicrm_pledge_payment
LEFT JOIN civicrm_contribution ON civicrm_pledge_payment.contribution_id = civicrm_contribution.id
LEFT JOIN civicrm_option_group ON ( civicrm_option_group.name = 'contribution_status' )
LEFT JOIN civicrm_option_value ON ( civicrm_pledge_payment.status_id = civicrm_option_value.value
AND civicrm_option_group.id = civicrm_option_value.option_group_id )
WHERE pledge_id = %1
";

        $params[1] = array( $pledgeId, 'Integer' );
        $payment = CRM_Core_DAO::executeQuery( $query, $params );

        $paymentDetails = array( );
        while ( $payment->fetch( ) ) {
            $paymentDetails[$payment->id]['scheduled_amount'] = $payment->scheduled_amount;
            $paymentDetails[$payment->id]['scheduled_date'  ] = $payment->scheduled_date;
            $paymentDetails[$payment->id]['reminder_date'   ] = $payment->reminder_date;
            $paymentDetails[$payment->id]['reminder_count'  ] = $payment->reminder_count;
            $paymentDetails[$payment->id]['total_amount'    ] = $payment->total_amount;
            $paymentDetails[$payment->id]['receive_date'    ] = $payment->receive_date;
            $paymentDetails[$payment->id]['status'          ] = $payment->status;
            $paymentDetails[$payment->id]['id'              ] = $payment->id;
            $paymentDetails[$payment->id]['contribution_id' ] = $payment->contribution_id;
        }
        
        return $paymentDetails;
    }

    static function create( $params )
    {
        require_once 'CRM/Contribute/PseudoConstant.php';
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        $date = array();
        $scheduled_date =  CRM_Utils_Date::processDate( $params['scheduled_date']);
        
        $date['year']   = (int) substr($scheduled_date,  0, 4);
        $date['month']  = (int) substr($scheduled_date,  4, 2);
        $date['day']    = (int) substr($scheduled_date,  6, 2);
        
        $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus( );
        //calculation of schedule date according to frequency day of period
        //frequency day is not applicable for daily installments
        if ( $params['frequency_unit'] != 'day' ) {
            if ( $params['frequency_unit'] != 'week' ) {
                
                //for month use day of next month & for year use day of month Jan of next year as next payment date 
                $date['day'] = $params['frequency_day'];
                if ( $params['frequency_unit'] == 'year' ) {
                    $date['month'] = '1';
                }   
            } else if ( $params['frequency_unit'] == 'week' ) {
                
                //for week calculate day of week ie. Sunday,Monday etc. as next payment date
                $dayOfWeek = date('w',mktime(0, 0, 0, $date['month'], $date['day'], $date['year'] ));
                $frequencyDay =   $params['frequency_day'] - $dayOfWeek;
                
                $scheduleDate =  explode ( "-", date( 'n-j-Y', mktime ( 0, 0, 0, $date['month'], 
                                                                        $date['day'] + $frequencyDay, $date['year'] )) );
                $date['month'] = $scheduleDate[0];
                $date['day']   = $scheduleDate[1];
                $date['year']  = $scheduleDate[2];
            }
        }
        //calculate the scheduled date for every installment
        $now = date('Ymd') . '000000';
        $statues = $prevScheduledDate = array ( );         
        $prevScheduledDate[1] = $scheduled_date;

        if ( CRM_Utils_Date::overdue( $prevScheduledDate[1], $now ) ) {
            $statues[1] = array_search( 'Overdue', $contributionStatus); 
        } else {
            $statues[1] = array_search( 'Pending', $contributionStatus);            
        }
        
        $newDate = date( 'YmdHis', mktime ( 0, 0, 0, $date['month'], $date['day'], $date['year'] ));
        
        for ( $i = 1; $i < $params['installments']; $i++ ) {
            $prevScheduledDate[$i+1] = CRM_Utils_Date::format(CRM_Utils_Date::intervalAdd( $params['frequency_unit'], 
                                                                                           $i * ($params['frequency_interval']) , $newDate ));
            if ( CRM_Utils_Date::overdue( $prevScheduledDate[$i+1], $now ) ) {
                $statues[$i+1] = array_search( 'Overdue', $contributionStatus);
            } else {
                $statues[$i+1] = array_search( 'Pending', $contributionStatus);
            }
        }
        
        if ( $params['installment_amount'] ) {
            $params['scheduled_amount'] = $params['installment_amount'];
        } else {
            $params['scheduled_amount'] = round( ( $params['amount'] / $params['installments'] ), 2 );
        }

        for ( $i = 1; $i <= $params['installments']; $i++ ) {
            //calculate the scheduled amount for every installment.
            if ( $i == $params['installments'] ) {
                $params['scheduled_amount'] = $params['amount'] - ($i-1) * $params['scheduled_amount'];
            }
            if (  ! isset( $params['contribution_id'] ) && $params['installments'] > 1 ) {
                $params['status_id'] = $statues[$i];
            }
 
            $params['scheduled_date'] = $prevScheduledDate[$i];
            $payment = self::add( $params );
            if ( is_a( $payment, 'CRM_Core_Error') ) {
                $transaction->rollback( );
                return $payment;
            }
            
             // we should add contribution id to only first payment record
            if ( isset( $params['contribution_id'] ) ){
                unset( $params['contribution_id'] );
            }
        }
        
        //update pledge status
        self::updatePledgePaymentStatus( $params['pledge_id'] );

        $transaction->commit( );
        return $payment;
    }

    /**
     * Add pledge payment
     *
     * @param array $params associate array of field
     *
     * @return pledge payment id 
     * @static
     */
    static function add( $params )
    {
        require_once 'CRM/Pledge/DAO/Payment.php';
        $payment = new CRM_Pledge_DAO_Payment( );
        $payment->copyValues( $params );
        
        // set currency for CRM-1496
        if ( ! isset( $payment->currency ) ) {
            $config =& CRM_Core_Config::singleton( );
            $payment->currency = $config->defaultCurrency;
        }
        
        $result = $payment->save( );
        
        return $result;
    }

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * pledge id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Pledge_BAO_Payment object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $payment = new CRM_Pledge_DAO_Payment;
        $payment->copyValues( $params );
        if ( $payment->find( true ) ) {
            CRM_Core_DAO::storeValues( $payment, $defaults );
            return $payment;
        }
        return null;
    }
    
    /**
     * Function to delete all pledge payments
     *
     * @param int $id  pledge id
     *
     * @access public
     * @static
     *
     */
    static function deletePayments( $id )
    { 
        require_once 'CRM/Utils/Rule.php';
        if ( ! CRM_Utils_Rule::positiveInteger( $id ) ) {
            return false;
        }
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $payment = new CRM_Pledge_DAO_Payment( );
        $payment->pledge_id = $id;
        
        if ( $payment->find( ) ) {
            while ( $payment->fetch( ) ) {
                //also delete associated contribution.
                if ( $payment->contribution_id ) {
                    require_once 'CRM/Contribute/BAO/Contribution.php';
                    CRM_Contribute_BAO_Contribution::deleteContribution( $payment->contribution_id );
                }
                $payment->delete( );
            }
        }
        
        $transaction->commit( );
        
        return true;
    }
    
    /**
     * On delete contribution record update associated pledge payment and pledge.
     *
     * @param int $contributionID  contribution id
     *
     * @access public
     * @static
     */
    static function resetPledgePayment( $contributionID )
    { 
        //get all status
        require_once 'CRM/Contribute/PseudoConstant.php';
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( );
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $payment = new CRM_Pledge_DAO_Payment( );
        $payment->contribution_id = $contributionID;
        if ( $payment->find( true ) ) {
            $payment->contribution_id = 'null'; 
            $payment->status_id = array_search( 'Pending', $allStatus );
            $payment->scheduled_date = NULL;
            $payment->reminder_date  = NULL;
            $payment->save( );
            
            //update pledge status.
            $pledgeID = $payment->pledge_id;
            $pledgeStatusID = self::calculatePledgeStatus( $pledgeID );
            CRM_Core_DAO::setFieldValue( 'CRM_Pledge_DAO_Pledge', $pledgeID, 'status_id', $pledgeStatusID );
            
            $payment->free( );
        }
        
        $transaction->commit( );
        return true;
    }
    
    /**
     * update Pledge Payment Status
     *
     * @param int   $pledgeID, id of pledge
     * @param array $paymentIDs, ids of pledge payment
     * @param int   $paymentStatus, payment status
     * @param int   $pledgeStatus, pledge status
     *
     * @return int $newStatus, updated status id (or 0)
     */
    function updatePledgePaymentStatus( $pledgeID, $paymentIDs = null, $paymentStatusID = null, $pledgeStatusID = null )
    {
        //get all status
        require_once 'CRM/Contribute/PseudoConstant.php';
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( );

        // if payment ids are passed, we update payment table first, since payments statuses are not dependent on pledge status
        if ( !empty( $paymentIDs ) || $pledgeStatusID == array_search( 'Cancelled', $allStatus ) ) {
            if ( $pledgeStatusID == array_search( 'Cancelled', $allStatus ) ) {
                $paymentStatusID = $pledgeStatusID ;
            }
            self::updatePledgePayments( $pledgeID, $paymentStatusID, $paymentIDs );
        }
        
        $cancelDateClause = $endDateClause  = null;
        //update pledge and payment status if status is Completed/Cancelled.
        if ( $pledgeStatusID && $pledgeStatusID == array_search( 'Cancelled', $allStatus ) ) {
            $paymentStatusID  = $pledgeStatusID;
            $cancelDateClause = ", civicrm_pledge.cancel_date = CURRENT_TIMESTAMP ";
        } else { 
            // get pledge status
            $pledgeStatusID = self::calculatePledgeStatus( $pledgeID );
        }
        
        if ( $pledgeStatusID == array_search( 'Completed', $allStatus ) ) {
            $endDateClause = ", civicrm_pledge.end_date = CURRENT_TIMESTAMP ";
        } 

        //update pledge status
        $query = "
UPDATE civicrm_pledge
 SET   civicrm_pledge.status_id = %1
       {$cancelDateClause} {$endDateClause}
WHERE  civicrm_pledge.id = %2
"; 
        $params = array( 1 => array( $pledgeStatusID, 'Integer' ),
                         2 => array( $pledgeID, 'Integer' ) );
        
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        
        return $pledgeStatusID;
    }

    /**
     * Calculate the pledge status
     *
     * @param int $pledgeId pledge id 
     *
     * @return int $statusId calculated status id of pledge 
     * @static
     */
     static function calculatePledgeStatus( $pledgeId )
     {
         require_once 'CRM/Contribute/PseudoConstant.php';
         $paymentStatusTypes = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
         
         //retrieve all pledge payments for this particular pledge
         $allPledgePayments = array( );
         $returnProperties  = array( 'status_id' );
         CRM_Core_DAO::commonRetrieveAll( 'CRM_Pledge_DAO_Payment', 'pledge_id', $pledgeId, $allPledgePayments, $returnProperties );

         // build pledge payment statuses
         foreach ( $allPledgePayments as $key => $value ) {
             $allStatus[$value['id']] = $paymentStatusTypes[$value['status_id']];
         }

         if ( array_search( 'Overdue', $allStatus ) ) {
             $statusId = array_search( 'Overdue', $paymentStatusTypes );
         } else if ( array_search( 'Completed', $allStatus ) ) {
             if ( count( array_count_values( $allStatus) ) == 1 ) {
                 $statusId = array_search( 'Completed', $paymentStatusTypes );
             } else {
                 $statusId = array_search( 'In Progress', $paymentStatusTypes );
             }
         } else {
             $statusId = array_search( 'Pending', $paymentStatusTypes );
         }
         
         return $statusId;
     }


    /**
     * Function to update pledge payment table
     *
     * @param int   $pledgeId pledge id
     * @param array $paymentIds payment ids 
     * @param int   $paymentStatusId payment status id
     * @static
     */
     static function updatePledgePayments( $pledgeId, $paymentStatusId, $paymentIds = null )
     {
        $paymentClause = null;
        if ( !empty( $paymentIds ) ) {
            $payments = implode( ',', $paymentIds );
            $paymentClause = " AND civicrm_pledge_payment.id IN ( {$payments} )";
        }
        
        $query = "
UPDATE civicrm_pledge_payment
SET    civicrm_pledge_payment.status_id = {$paymentStatusId}
WHERE  civicrm_pledge_payment.status_id != %1
   AND civicrm_pledge_payment.pledge_id  = %2    
       {$paymentClause}
";
        //get all status
        require_once 'CRM/Contribute/PseudoConstant.php';
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( );
        $params = array( 1 => array( array_search( 'Completed', $allStatus ),
                                     'Integer'),
                         2 => array( $pledgeId, 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $query, $params );
    }

    /**
     * Function to update pledge payment table when reminder is sent
     * @param int $paymentId payment id 
     * 
     * @static
     */
    static function updateReminderDetails( $paymentId )
    {
        $query = "
UPDATE civicrm_pledge_payment
SET civicrm_pledge_payment.reminder_date = CURRENT_TIMESTAMP,
    civicrm_pledge_payment.reminder_count = civicrm_pledge_payment.reminder_count + 1
WHERE  civicrm_pledge_payment.id = {$paymentId}
";
        $dao = CRM_Core_DAO::executeQuery( $query );
    }
    
    /**
     * Function to get oldest pending or in progress pledge payments
     *  
     * @param int $pledgeID pledge id
     *
     * @return array associated array of pledge details
     * @static
     */
    static function getOldestPledgePayment( $pledgeID )
    {
        //get pending / overdue statuses
        $pledgeStatuses = CRM_Core_OptionGroup::values( 'contribution_status');

        //get pending and overdue payments
        $status[] = array_search( 'Pending', $pledgeStatuses );
        $status[] = array_search( 'Overdue', $pledgeStatuses );

        $statusClause = " IN (" . implode( ',', $status ) . ")";
        
        $query = "
SELECT civicrm_pledge_payment.id id, civicrm_pledge_payment.scheduled_amount amount
FROM civicrm_pledge, civicrm_pledge_payment
WHERE civicrm_pledge.id = civicrm_pledge_payment.pledge_id
  AND civicrm_pledge_payment.status_id {$statusClause}        
  AND civicrm_pledge.id = %1
ORDER BY civicrm_pledge_payment.scheduled_date ASC
LIMIT 0, 1  
";

        $params[1] = array( $pledgeID, 'Integer' );
        $payment = CRM_Core_DAO::executeQuery( $query, $params );
        $paymentDetails = null;
        if ( $payment->fetch( ) ) {
            $paymentDetails = array( 'id'     => $payment->id,
                                     'amount' => $payment->amount);
        }

        return $paymentDetails;
    }
}

