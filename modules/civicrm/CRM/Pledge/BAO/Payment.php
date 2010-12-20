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
SELECT    civicrm_pledge_payment.id id, 
          scheduled_amount,
          scheduled_date, 
          reminder_date, 
          reminder_count,
          actual_amount, 
          receive_date,
          civicrm_option_value.name as status,
          civicrm_option_value.label as label,
          civicrm_contribution.id as contribution_id
FROM      civicrm_pledge_payment

LEFT JOIN civicrm_contribution ON civicrm_pledge_payment.contribution_id = civicrm_contribution.id
LEFT JOIN civicrm_option_group ON ( civicrm_option_group.name = 'contribution_status' )
LEFT JOIN civicrm_option_value ON ( civicrm_pledge_payment.status_id = civicrm_option_value.value AND
                                    civicrm_option_group.id = civicrm_option_value.option_group_id )
WHERE     pledge_id = %1
";
  
        $params[1] = array( $pledgeId, 'Integer' );
        $payment = CRM_Core_DAO::executeQuery( $query, $params );

        $paymentDetails = array( );
        while ( $payment->fetch( ) ) {
            $paymentDetails[$payment->id]['scheduled_amount'] = $payment->scheduled_amount;
            $paymentDetails[$payment->id]['scheduled_date'  ] = $payment->scheduled_date;
            $paymentDetails[$payment->id]['reminder_date'   ] = $payment->reminder_date;
            $paymentDetails[$payment->id]['reminder_count'  ] = $payment->reminder_count;
            $paymentDetails[$payment->id]['total_amount'    ] = $payment->actual_amount;
            $paymentDetails[$payment->id]['receive_date'    ] = $payment->receive_date;
            $paymentDetails[$payment->id]['status'          ] = $payment->status;
            $paymentDetails[$payment->id]['label'           ] = $payment->label;
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
        
        $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        //calculation of schedule date according to frequency day of period
        //frequency day is not applicable for daily installments
        if ( $params['frequency_unit'] != 'day' && $params['frequency_unit'] != 'year') {
            if ( $params['frequency_unit'] != 'week' ) {
                
                //for month use day of next month as next payment date 
                $date['day'] = $params['frequency_day'];
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
                unset( $params['actual_amount'] );
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
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        
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
    function updatePledgePaymentStatus( $pledgeID,
                                        $paymentIDs = null,
                                        $paymentStatusID = null,
                                        $pledgeStatusID = null, 
                                        $actualAmount = 0,
                                        $adjustTotalAmount = false,
                                        $isScriptUpdate = false )
    {
        //get all status
        require_once 'CRM/Contribute/PseudoConstant.php';
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        
        // if we get do not get contribution id means we are editing the scheduled payment.
        if ( !empty( $paymentIDs ) ) {
            $payments = implode( ',', $paymentIDs );
            $paymentContributionId  =  CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Payment', 
                                                                    $payments,
                                                                    'contribution_id', 
                                                                    'id'
                                                                    );
            if ( ! $paymentContributionId ) {
                $editScheduled = true;
            }
        }
        
        // if payment ids are passed, we update payment table first, since payments statuses are not dependent on pledge status
        if ( ( !empty( $paymentIDs ) || $pledgeStatusID == array_search( 'Cancelled', $allStatus ) ) && !$editScheduled ) {
            if ( $pledgeStatusID == array_search( 'Cancelled', $allStatus ) ) {
                $paymentStatusID = $pledgeStatusID ;
            }
            
            self::updatePledgePayments( $pledgeID, $paymentStatusID, $paymentIDs, $actualAmount, $paymentContributionId ,$isScriptUpdate );
        }
        if ( !empty( $paymentIDs ) && $actualAmount  ) {
            $payments = implode( ',', $paymentIDs );
            $pledgeScheduledAmount = CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Payment', 
                                                                  $payments,
                                                                  'scheduled_amount', 
                                                                  'id'
                                                                  );
            
            $pledgeStatusId = self::calculatePledgeStatus( $pledgeID );
            // Actual Pledge Amount
            $actualPledgeAmount = CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Pledge', 
                                                               $pledgeID,
                                                               'amount', 
                                                               'id'
                                                               );
            //  while editing scheduled  we need to check if we are editing last pending
            if ( !$paymentContributionId ) {
                $checkPendingCount = self::getOldestPledgePayment( $pledgeID, 2 );
                if ( $checkPendingCount['count'] == 1 ) {
                    $lastPending = true;
                }
            }

            // check if this is the last payment and adjust the actual amount. 
            if ( $pledgeStatusId && $pledgeStatusId == array_search( 'Completed', $allStatus )  || $lastPending ) {
                // last scheduled payment
                if ( $actualAmount < $pledgeScheduledAmount ) {
                    // actual amount is less than the scheduled amount, so enter new pledge payment record
                    $pledgeFrequencyUnit= CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Pledge',$pledgeID,'frequency_unit','id');
                    $pledgeFrequencyInterval = CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Pledge',$pledgeID,'frequency_interval','id');
                    $pledgeScheduledDate = CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Payment',$payments,'scheduled_date','id');
                    $scheduled_date =  CRM_Utils_Date::processDate( $pledgeScheduledDate );
                    $date['year']   = (int) substr($scheduled_date,  0, 4);
                    $date['month']  = (int) substr($scheduled_date,  4, 2);
                    $date['day']    = (int) substr($scheduled_date,  6, 2);
                    $newDate = date( 'YmdHis', mktime ( 0, 0, 0, $date['month'], $date['day'], $date['year'] ));
                    $ScheduledDate = CRM_Utils_Date::format(CRM_Utils_Date::intervalAdd( $pledgeFrequencyUnit, 
                                                                                         $pledgeFrequencyInterval , $newDate ) );
                    $pledgeParams = array(
                                          'status_id' => array_search( 'Pending', $allStatus ),
                                          'pledge_id' => $pledgeID,
                                          'scheduled_amount' => ( $pledgeScheduledAmount - $actualAmount ), 
                                          'scheduled_date' => $ScheduledDate,
                                          );
                    $payment = self::add( $pledgeParams );
                    // while editing schedule,  after adding a new pledge payemnt update the scheduled amount of the current payment
                    if ( !$paymentContributionId )  {
                        CRM_Core_DAO::setFieldValue( 'CRM_Pledge_DAO_Payment', $payments, 'scheduled_amount', $actualAmount );
                    } 
                } else {
                    $adjustTotalAmount = true;
                }
            } elseif ( !$adjustTotalAmount ) {
                // not last schedule amount and also not selected to adjust Total
                $paymentContributionId  =  CRM_Core_DAO::getFieldValue( 'CRM_Pledge_DAO_Payment', 
                                                                        $payments,
                                                                        'contribution_id', 
                                                                        'id'
                                                                        );
                self::adjustPledgePayment( $pledgeID, $actualAmount , $pledgeScheduledAmount, $paymentContributionId , $payments );
                // while editing schedule,  after adding a new pledge payemnt update the scheduled amount of the current payment
                if ( !$paymentContributionId )  {
                    CRM_Core_DAO::setFieldValue( 'CRM_Pledge_DAO_Payment', $payments, 'scheduled_amount', $actualAmount );
                }
                // after adjusting all payments check if the actual amount was greater than the actual remaining amount , if so then update the total pledge amount.
                $pledgeStatusId = self::calculatePledgeStatus( $pledgeID );
                $balanceQuery = "
 SELECT sum( civicrm_pledge_payment.actual_amount )
 FROM civicrm_pledge_payment
 WHERE civicrm_pledge_payment.pledge_id = %1
 AND civicrm_pledge_payment.status_id = 1
 ";
                $totalPaidParams = array( 1 => array( $pledgeID, 'Integer' ) ); 
                $totalPaidAmount =  CRM_Core_DAO::singleValueQuery( $balanceQuery, $totalPaidParams );
                $remainingTotalAmount = ( $actualPledgeAmount - $totalPaidAmount );
                if ( ( $pledgeStatusId && $pledgeStatusId == array_search( 'Completed', $allStatus ) ) && ( ( $actualAmount > $remainingTotalAmount ) || ( $actualAmount >= $actualPledgeAmount ) ) ) {
                    $totalAmountClause = ", civicrm_pledge.amount = {$totalPaidAmount}";   
                }
            }
            if ( $adjustTotalAmount ) {
                $newTotalAmount = ( $actualPledgeAmount + ( $actualAmount - $pledgeScheduledAmount ) ); 
                $totalAmountClause = ", civicrm_pledge.amount = {$newTotalAmount}"; 
                if ( !$paymentContributionId )  {
                    CRM_Core_DAO::setFieldValue( 'CRM_Pledge_DAO_Payment', $payments, 'scheduled_amount', $actualAmount );
                } 
            }
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
       {$cancelDateClause} {$endDateClause} {$totalAmountClause}
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
     static function updatePledgePayments( $pledgeId, $paymentStatusId, $paymentIds = null, $actualAmount = 0 ,$contributionId = null ,$isScriptUpdate = false )
     {
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        $paymentClause = null;
        if ( !empty( $paymentIds ) ) {
            $payments = implode( ',', $paymentIds );
            $paymentClause = " AND civicrm_pledge_payment.id IN ( {$payments} )";
        }
        $actualAmountClause = NULL;
        if ( isset( $contributionId ) && !$isScriptUpdate ) {
            $contributionIdClause = ", civicrm_pledge_payment.contribution_id = {$contributionId}";
            $actualAmountClause =", civicrm_pledge_payment.actual_amount = {$actualAmount}";
        }
        
        $cancelClause = null;
        if ( $paymentStatusId == array_search( 'Cancelled', $allStatus ) ) { 
             $completedStatus  = array_search( 'Completed', $allStatus );
             $cancelClause = "AND civicrm_pledge_payment.status_id != {$completedStatus}";
         }
         
        $query = "
UPDATE civicrm_pledge_payment
SET    civicrm_pledge_payment.status_id = {$paymentStatusId}
       {$actualAmountClause} {$contributionIdClause}
WHERE  civicrm_pledge_payment.pledge_id = %1    
       {$paymentClause} {$cancelClause}
";
        
        //get all status
        require_once 'CRM/Contribute/PseudoConstant.php';
        $params = array( 1 => array( $pledgeId, 'Integer' ) );

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
    static function getOldestPledgePayment( $pledgeID, $limit = 1 )
    {
        //get pending / overdue statuses
        $pledgeStatuses = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );

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
LIMIT 0, %2  
";
        
        $params[1] = array( $pledgeID, 'Integer' );
        $params[2] = array( $limit, 'Integer' );
        $payment = CRM_Core_DAO::executeQuery( $query, $params );
        $count = 1;
        $paymentDetails = array();
        while ( $payment->fetch( ) ) {
            $paymentDetails[] = array( 'id'     => $payment->id,
                                       'amount' => $payment->amount,
                                       'count'  => $count );
            $count++;
        }
        return end($paymentDetails);
    }
    
    static function adjustPledgePayment( $pledgeID, $actualAmount, $pledgeScheduledAmount, $paymentContributionId = null, $pPaymentId = null )
    {   
        $allStatus = CRM_Contribute_PseudoConstant::contributionStatus( null, 'name' );
        $oldestPayment = self::getOldestPledgePayment( $pledgeID );
        if ( !$paymentContributionId  ) {
            // means we are editing payment scheduled payment, so get the second pending to update.
            $oldestPayment = self::getOldestPledgePayment( $pledgeID, 2 );
            if ( ( $oldestPayment['count'] != 1 ) && ( $oldestPayment['id'] == $pPaymentId ) ) {
                $oldestPayment = CRM_Pledge_BAO_Payment::getOldestPledgePayment( $pledgeID );
            } 
        }

        if ( $oldestPayment ) {
            // not the last scheduled payment and the actual amount is less than the expected , add it to oldest pending.
            if ( ( $actualAmount != $pledgeScheduledAmount ) && ( ( $actualAmount < $pledgeScheduledAmount ) || ( ( $actualAmount - $pledgeScheduledAmount )  < $oldestPayment['amount'] ) ) ) {
                $oldScheduledAmount = $oldestPayment['amount'];
                $newScheduledAmount = $oldScheduledAmount + ( $pledgeScheduledAmount - $actualAmount );
                //store new amount in oldest pending payment record.
                CRM_Core_DAO::setFieldValue('CRM_Pledge_DAO_Payment', $oldestPayment['id'], 'scheduled_amount', $newScheduledAmount );
            } elseif ( ( $actualAmount > $pledgeScheduledAmount ) && ( ( $actualAmount - $pledgeScheduledAmount )  >= $oldestPayment['amount'] ) ) {
                // here the actual amount is greater than expected and also greater than the next installment amount, so update the next installment as complete and again add it to next subsequent pending payment
                // set the actual amount of the next pending to '0', set contribution Id to current contribution Id and status as completed 
                $paymentId = array ( $oldestPayment['id'] ); 
                self::updatePledgePayments( $pledgeID, array_search( 'Completed', $allStatus ) , $paymentId, 0,$paymentContributionId );
                CRM_Core_DAO::setFieldValue( 'CRM_Pledge_DAO_Payment',  $oldestPayment['id'], 'scheduled_amount', 0 , 'id' );
                $oldestPayment = self::getOldestPledgePayment( $pledgeID );
                if ( !$paymentContributionId ) {
                    // means we are editing payment scheduled payment.
                    $oldestPaymentAmount = self::getOldestPledgePayment( $pledgeID, 2 );
                } 
                $newActualAmount = ( $actualAmount - $pledgeScheduledAmount );
                $newPledgeScheduledAmount = $oldestPayment['amount'];
                if ( !$paymentContributionId ) {
                    $newActualAmount = ( $actualAmount - $pledgeScheduledAmount );
                    $newPledgeScheduledAmount = $oldestPaymentAmount['amount'];
                    // means we are editing payment scheduled payment, so update scheduled amount.
                    CRM_Core_DAO::setFieldValue( 'CRM_Pledge_DAO_Payment', $oldestPaymentAmount['id'], 'scheduled_amount', $newActualAmount );
                }
                if ( $newActualAmount > 0 ) {
                    self::adjustPledgePayment( $pledgeID, $newActualAmount, $newPledgeScheduledAmount, $paymentContributionId );
                }
            } 
        }
    }
}

