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

require_once 'Mail.php';
require_once 'CRM/Mailing/DAO/Job.php';
require_once 'CRM/Mailing/DAO/Mailing.php';
require_once 'CRM/Mailing/BAO/Job.php';
require_once 'CRM/Mailing/BAO/Mailing.php';

class CRM_Mailing_BAO_Job extends CRM_Mailing_DAO_Job {

    const MAX_CONTACTS_TO_PROCESS = 1000;

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }
    
    /**
     * Initiate all pending/ready jobs
     *
     * @return void
     * @access public
     * @static
     */
    public static function runJobs($testParams = null) {
        $job = new CRM_Mailing_BAO_Job();
        
        $mailing = new CRM_Mailing_DAO_Mailing();
        
        $config = CRM_Core_Config::singleton();
        $jobTable     = CRM_Mailing_DAO_Job::getTableName();
        $mailingTable = CRM_Mailing_DAO_Mailing::getTableName();

        if ( ! empty( $testParams ) ) {
            $query = "
SELECT *
  FROM $jobTable
 WHERE id = {$testParams['job_id']}";
            $job->query($query);
        } else {
            $currentTime = date( 'YmdHis' );
            $mailingACL  = CRM_Mailing_BAO_Mailing::mailingACL( 'm' );

            /* FIXME: we might want to go to a progress table.. */
            $query = "
SELECT   j.*
  FROM   $jobTable     j,
         $mailingTable m
 WHERE   m.id = j.mailing_id
   AND   j.is_test = 0
   AND   ( ( j.start_date IS null
   AND       j.scheduled_date <= $currentTime
   AND       j.status = 'Scheduled' )
    OR     ( j.status = 'Running'
   AND       j.end_date IS null ) )
   AND   {$mailingACL}
ORDER BY j.scheduled_date,
         j.start_date";

            $job->query($query);
        }

        require_once 'CRM/Core/Lock.php';

        /* TODO We should parallelize or prioritize this */
        while ($job->fetch()) {
            $lockName = "civimail.job.{$job->id}";

            // get a lock on this job id
            $lock = new CRM_Core_Lock( $lockName );
            if ( ! $lock->isAcquired( ) ) {
                continue;
            }

            // for test jobs we do not change anything, since its on a short-circuit path
            if ( empty( $testParams ) ) {
                // we've got the lock, but while we were waiting and processing
                // other emails, this job might have changed under us
                // lets get the job status again and check
                $job->status = CRM_Core_DAO::getFieldValue( 'CRM_Mailing_DAO_Job', 
                                                            $job->id,
                                                            'status' );

                if ( $job->status != 'Running' &&
                     $job->status != 'Scheduled' ) {
                    // this includes Cancelled and other statuses, CRM-4246
                    $lock->release( );
                    continue;
                }
            }
          
            /* Queue up recipients for all jobs being launched */
            if ($job->status != 'Running') {
                require_once 'CRM/Core/Transaction.php';
                $transaction = new CRM_Core_Transaction( );

                $job->queue($testParams);

                /* Start the job */
                // use a seperate DAO object to protect the loop
                // integrity. I think transactions messes it up
                // check CRM-2469
                $saveJob = new CRM_Mailing_DAO_Job( );
                $saveJob->id         = $job->id;
                $saveJob->start_date = date('YmdHis');
                $saveJob->status     = 'Running';
                $saveJob->save();

                $transaction->commit( );
            }
            
            $mailer =& $config->getMailer();

            /* Compose and deliver */
            $isComplete = $job->deliver($mailer, $testParams);
            
            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::post( 'create', 'CRM_Mailing_DAO_Spool', $job->id, $isComplete);
            
            if ( $isComplete ) {
                /* Finish the job */
                require_once 'CRM/Core/Transaction.php';
                $transaction = new CRM_Core_Transaction( );

                // use a seperate DAO object to protect the loop
                // integrity. I think transactions messes it up
                // check CRM-2469
                $saveJob = new CRM_Mailing_DAO_Job( );
                $saveJob->id   = $job->id;
                $saveJob->end_date = date('YmdHis');
                $saveJob->status   = 'Complete';
                $saveJob->save();

                $mailing->reset();
                $mailing->id = $job->mailing_id;
                $mailing->is_completed = true;
                $mailing->save();
                $transaction->commit( );
            } 
            
            $lock->release( );
            
            if ($testParams) {
                return $isComplete;
            } 
        }
    }
    
    /**
     * Queue recipients of a job.
     *
     * @return void
     * @access public
     */
    public function queue($testParams = null) {
       
        require_once 'CRM/Mailing/BAO/Mailing.php';
        $mailing = new CRM_Mailing_BAO_Mailing();
        $mailing->id = $this->mailing_id;
        if (!empty($testParams)) {
            $mailing->getTestRecipients($testParams);
        } else {
            $recipients =& $mailing->getRecipientsObject($this->id);

            // since this is such a simple db operation, we could potentially optimize it a lot
            // by doing a batch of inserts at one time
            // INSERT INTO civicrm_..._queue ( job_id, email_id, contact_id, hash ) VALUES ( ... ),( ... ),...
            // this will cut down the number of trips to the db quite nicely rather than doing one insert at a time
            while ($recipients->fetch()) {
                $params = array(
                                'job_id'        => $this->id,
                                'email_id'      => $recipients->email_id,
                                'contact_id'    => $recipients->contact_id
                                );
                CRM_Mailing_Event_BAO_Queue::create($params);
            }
        }
    }
    /**
     * Number of mailings of a job.
     *
     * @return int
     * @access public
     */
    public function getMailingSize() {
        require_once 'CRM/Mailing/BAO/Mailing.php';
        $mailing = new CRM_Mailing_BAO_Mailing();
        $mailing->id = $this->mailing_id;

        $recipients =& $mailing->getRecipientsObject($this->id, true);
        $mailingSize = 0;
        while ($recipients->fetch()) {
            $mailingSize ++;
        }
        return $mailingSize;
    }

    /**
     * Send the mailing
     *
     * @param object $mailer        A Mail object to send the messages
     * @return void
     * @access public
     */
    public function deliver(&$mailer, $testParams =null) {
        require_once 'CRM/Mailing/BAO/Mailing.php';
        $mailing = new CRM_Mailing_BAO_Mailing();
        $mailing->id = $this->mailing_id;
        $mailing->find(true);

        $eq = new CRM_Mailing_Event_BAO_Queue();
        $eqTable        = CRM_Mailing_Event_BAO_Queue::getTableName();
        $emailTable     = CRM_Core_BAO_Email::getTableName();
        $contactTable   = CRM_Contact_BAO_Contact::getTableName();
        $edTable        = CRM_Mailing_Event_BAO_Delivered::getTableName();
        $ebTable        = CRM_Mailing_Event_BAO_Bounce::getTableName();
        
        $query = "  SELECT      $eqTable.id,
                                $emailTable.email as email,
                                $eqTable.contact_id,
                                $eqTable.hash
                    FROM        $eqTable
                    INNER JOIN  $emailTable
                            ON  $eqTable.email_id = $emailTable.id
                    LEFT JOIN   $edTable
                            ON  $eqTable.id = $edTable.event_queue_id
                    LEFT JOIN   $ebTable
                            ON  $eqTable.id = $ebTable.event_queue_id
                    WHERE       $eqTable.job_id = " . $this->id . "
                        AND     $edTable.id IS null
                        AND     $ebTable.id IS null";
                    
        $eq->query($query);

        static $config = null;
        $mailsProcessed = 0;

        if ( $config == null ) {
            $config = CRM_Core_Config::singleton();
        }

        $job_date = CRM_Utils_Date::isoToMysql( $this->scheduled_date );
        $fields = array( );
        
        if (! empty($testParams)) {
            $mailing->from_name     = ts('CiviCRM Test Mailer (%1)',
                                         array( 1 => $mailing->from_name ) );
            $mailing->subject = ts('Test Mailing:') . ' ' . $mailing->subject;
        }
        
        CRM_Mailing_BAO_Mailing::tokenReplace($mailing);

        // get and format attachments
        require_once 'CRM/Core/BAO/File.php';
        $attachments =& CRM_Core_BAO_File::getEntityFile( 'civicrm_mailing',
                                                          $mailing->id );


        if ( defined( 'CIVICRM_MAIL_SMARTY' ) ) {
            require_once 'CRM/Core/Smarty/resources/String.php';
            civicrm_smarty_register_string_resource( );
        }

        // make sure that there's no more than $config->mailerBatchLimit mails processed in a run
        while ($eq->fetch()) {
            // if ( ( $mailsProcessed % 100 ) == 0 ) {
            // CRM_Utils_System::xMemory( "$mailsProcessed: " );
            // }

            if ( $config->mailerBatchLimit > 0 &&
                 $mailsProcessed >= $config->mailerBatchLimit ) {
                $this->deliverGroup( $fields, $mailing, $mailer, $job_date, $attachments );
                return false;
            }
            $mailsProcessed++;
            
            $fields[] = array( 'id'         => $eq->id,
                               'hash'       => $eq->hash,
                               'contact_id' => $eq->contact_id,
                               'email'      => $eq->email );
            if ( count( $fields ) == self::MAX_CONTACTS_TO_PROCESS ) {
                $isDelivered = $this->deliverGroup( $fields, $mailing, $mailer, $job_date, $attachments );
                if ( !$isDelivered ) {
                    return $isDelivered;
                }
                $fields = array( );
            }
        }

        $isDelivered = $this->deliverGroup( $fields, $mailing, $mailer, $job_date, $attachments );
        return $isDelivered;
    }

    public function deliverGroup ( &$fields, &$mailing, &$mailer, &$job_date, &$attachments ) {
        // get the return properties
        $returnProperties = $mailing->getReturnProperties( );
        $params       = array( );
        $targetParams = array( );
        foreach ( $fields as $key => $field ) {
            $params[] = $field['contact_id'];
        }

        $details = $mailing->getDetails($params, $returnProperties);

        foreach ( $fields as $key => $field ) {
            $contactID = $field['contact_id'];
            /* Compose the mailing */
            $recipient = null;
            $message =& $mailing->compose( $this->id, $field['id'], $field['hash'],
                                           $field['contact_id'], $field['email'],
                                           $recipient, false, $details[0][$contactID], $attachments );
            
            /* Send the mailing */
            $body    =& $message->get();
            $headers =& $message->headers();
            // make $recipient actually be the *encoded* header, so as not to baffle Mail_RFC822, CRM-5743
            $recipient = $headers['To'];
            $result = null;
            /* TODO: when we separate the content generator from the delivery
             * engine, maybe we should dump the messages into a table */

            // disable error reporting on real mailings (but leave error reporting for tests), CRM-5744
            if ($job_date) CRM_Core_Error::ignoreException();

            if ( is_object( $mailer ) ) {
                
                // hack to stop mailing job at run time, CRM-4246.
                $mailingJob = new CRM_Mailing_DAO_Job( ); 
                $mailingJob->mailing_id = $mailing->id;
                if ( $mailingJob->find( true ) ) {
                    // mailing have been canceled at run time.
                    if ( $mailingJob->status == 'Canceled' ) {
                        return false;
                    }
                } else {
                    // mailing have been deleted at run time. 
                    return false;
                }
                $mailingJob->free( );
                
                $result = $mailer->send($recipient, $headers, $body, $this->id);
                CRM_Core_Error::setCallback();
            }
            $params = array( 'event_queue_id' => $field['id'],
                             'job_id'         => $this->id,
                             'hash'           => $field['hash'] );
            
            if ( is_a( $result, 'PEAR_Error' ) ) {
                /* Register the bounce event */
                require_once 'CRM/Mailing/BAO/BouncePattern.php';
                require_once 'CRM/Mailing/Event/BAO/Bounce.php';
                $params = array_merge($params, 
                                      CRM_Mailing_BAO_BouncePattern::match($result->getMessage()));
                CRM_Mailing_Event_BAO_Bounce::create($params);
            } else {
                /* Register the delivery event */
                CRM_Mailing_Event_BAO_Delivered::create($params);
            }
            
            $targetParams[] = $field['contact_id'];

            unset( $result );
        }

        if ( ! empty( $targetParams ) ) {
            // add activity record for every mail that is send
            $activityTypeID = CRM_Core_OptionGroup::getValue( 'activity_type',
                                                              'Bulk Email',
                                                              'name' );
        
            $activity = array('source_contact_id'    => $mailing->scheduled_id,
                              'target_contact_id'    => $targetParams,
                              'activity_type_id'     => $activityTypeID,
                              'source_record_id'     => $this->mailing_id,
                              'activity_date_time'   => $job_date,
                              'subject'              => $mailing->subject,
                              'status_id'            => 2,
                              'deleteActivityTarget' => false,
                              );

            //check whether activity is already created for this mailing.
            //if yes then create only target contact record.   
            $query  = "
SELECT id 
FROM   civicrm_activity
WHERE  civicrm_activity.activity_type_id = %1
AND    civicrm_activity.source_record_id = %2";
        
            $queryParams = array( 1 => array( $activityTypeID  , 'Integer' ),
                                  2 => array( $this->mailing_id, 'Integer' ) );
            $activityID  = CRM_Core_DAO::singleValueQuery( $query,
                                                           $queryParams );    
        
            if ( $activityID ) {
                $activity['id'] = $activityID;  
            }
        
            require_once 'api/v2/Activity.php';
            if ( is_a( civicrm_activity_create($activity, 'Email'), 'CRM_Core_Error' ) ) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * cancel a mailing
     *
     * @param int $mailingId  the id of the mailing to be canceled
     * @static
     */
    public static function cancel($mailingId) {
        $job = new CRM_Mailing_BAO_Job();
        $job->mailing_id = $mailingId;
        // test mailing should not be included during Cancellation
        $job->is_test    = 0;
        if ($job->find(true) and in_array($job->status, array('Scheduled', 'Running', 'Paused'))) {
            // fix MySQL dates...
            $job->scheduled_date = CRM_Utils_Date::isoToMysql($job->scheduled_date);
            $job->start_date     = CRM_Utils_Date::isoToMysql($job->start_date);
            $job->end_date       = CRM_Utils_Date::isoToMysql($job->end_date);
            $job->status         = 'Canceled';
            $job->save();
            CRM_Core_Session::setStatus(ts('The mailing has been canceled.'));
        }
    }


    /**
     * Return a translated status enum string
     *
     * @param string $status        The status enum
     * @return string               The translated version
     * @access public
     * @static
     */
    public static function status($status) {
        static $translation = null;
        
        if (empty($translation)) {
            $translation = array(
                'Scheduled' =>  ts('Scheduled'),
                'Running'   =>  ts('Running'),
                'Complete'  =>  ts('Complete'),
                'Paused'    =>  ts('Paused'),
                'Canceled'  =>  ts('Canceled'),
            );
        }
        return CRM_Utils_Array::value($status, $translation, ts('Not scheduled'));
    }
}


