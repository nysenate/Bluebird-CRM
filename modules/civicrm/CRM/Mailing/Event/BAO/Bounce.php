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
require_once 'CRM/Mailing/Event/DAO/Bounce.php';
require_once 'CRM/Mailing/DAO/BounceType.php';
require_once 'CRM/Core/BAO/Email.php';
require_once 'CRM/Mailing/Event/BAO/Queue.php';

class CRM_Mailing_Event_BAO_Bounce extends CRM_Mailing_Event_DAO_Bounce {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }


    /**
     * Create a new bounce event, update the email address if necessary
     */
    static function &create(&$params) {
        $q =& CRM_Mailing_Event_BAO_Queue::verify($params['job_id'],
                                                  $params['event_queue_id'],
                                                  $params['hash']);
        $success = null;

        if (! $q) {
            return $success;
        }

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        $bounce = new CRM_Mailing_Event_BAO_Bounce();
        $bounce->time_stamp = date('YmdHis');

        // if we dont have a valid bounce type, we should set it
        // to bounce_type_id 6 which is Invalid. this allows such email
        // addresses to be put on hold immediately, CRM-4814
        if ( empty( $params['bounce_type_id'] ) ) {
            $params['bounce_type_id'] = 6;
            $params['bounce_reason'] = ts( 'Unknown bounce type: Could not parse bounce email' );
        }
             
        $bounce->copyValues($params);
        $bounce->save();
        $success = true;

        $bounceTable    = CRM_Mailing_Event_BAO_Bounce::getTableName();
        $bounceType     = CRM_Mailing_DAO_BounceType::getTableName();
        $emailTable     = CRM_Core_BAO_Email::getTableName();
        $queueTable     = CRM_Mailing_Event_BAO_Queue::getTableName();
        
        $bounce->reset();
        // might want to put distinct inside the count
        $query =
                "SELECT     count($bounceTable.id) as bounces,
                            $bounceType.hold_threshold as threshold
                FROM        $bounceTable
                INNER JOIN  $bounceType
                        ON  $bounceTable.bounce_type_id = $bounceType.id
                INNER JOIN  $queueTable
                        ON  $bounceTable.event_queue_id = $queueTable.id
                INNER JOIN  $emailTable
                        ON  $queueTable.email_id = $emailTable.id
                WHERE       $emailTable.id = {$q->email_id}
                    AND     ($emailTable.reset_date IS NULL
                        OR  $bounceTable.time_stamp >= $emailTable.reset_date)
                GROUP BY    $bounceTable.bounce_type_id
                ORDER BY    threshold, bounces desc";
                                
        $bounce->query($query);

        while ($bounce->fetch()) {
            if ($bounce->bounces >= $bounce->threshold) {
                $email = new CRM_Core_BAO_Email();
                $email->id = $q->email_id;
                $email->on_hold = true;
                $email->hold_date = date('YmdHis');
                $email->save();
                break;
            }
        }
        $transaction->commit( );

        return $success;
    }

    /**
     * Get row count for the event selector
     *
     * @param int $mailing_id       ID of the mailing
     * @param int $job_id           Optional ID of a job to filter on
     * @param boolean $is_distinct  Group by queue ID?
     * @return int                  Number of rows in result set
     * @access public
     * @static
     */
    public static function getTotalCount($mailing_id, $job_id = null,
                                            $is_distinct = false) {
        $dao = new CRM_Core_DAO();
        
        $bounce     = self::getTableName();
        $queue      = CRM_Mailing_Event_BAO_Queue::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();

        $query = "
            SELECT      COUNT($bounce.id) as bounce
            FROM        $bounce
            INNER JOIN  $queue
                    ON  $bounce.event_queue_id = $queue.id
            INNER JOIN  $job
                    ON  $queue.job_id = $job.id
            INNER JOIN  $mailing
                    ON  $job.mailing_id = $mailing.id
            WHERE       $mailing.id = " 
            . CRM_Utils_Type::escape($mailing_id, 'Integer');

        if (!empty($job_id)) {
            $query  .= " AND $job.id = " 
                    . CRM_Utils_Type::escape($job_id, 'Integer');
        }
        
        if ($is_distinct) {
            $query .= " GROUP BY $queue.id ";
        }

        $dao->query($query);//query was missing
        
        if ( $dao->fetch() ) {
            return $dao->bounce;
        } 

        return null;
    }



    /**
     * Get rows for the event browser
     *
     * @param int $mailing_id       ID of the mailing
     * @param int $job_id           optional ID of the job
     * @param boolean $is_distinct  Group by queue id?
     * @param int $offset           Offset
     * @param int $rowCount         Number of rows
     * @param array $sort           sort array
     * @return array                Result set
     * @access public
     * @static
     */
    public static function &getRows($mailing_id, $job_id = null, 
        $is_distinct = false, $offset = null, $rowCount = null, $sort = null) {
       
        $dao = new CRM_Core_Dao();
        
        $bounce     = self::getTableName();
        $bounceType = CRM_Mailing_DAO_BounceType::getTableName();
        $queue      = CRM_Mailing_Event_BAO_Queue::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $contact    = CRM_Contact_BAO_Contact::getTableName();
        $email      = CRM_Core_BAO_Email::getTableName();

        $query =    "
            SELECT      $contact.display_name as display_name,
                        $contact.id as contact_id,
                        $email.email as email,
                        $bounce.time_stamp as date,
                        $bounce.bounce_reason as reason,
                        $bounceType.name as bounce_type
            FROM        $contact
            INNER JOIN  $queue
                    ON  $queue.contact_id = $contact.id
            INNER JOIN  $email
                    ON  $queue.email_id = $email.id
            INNER JOIN  $bounce
                    ON  $bounce.event_queue_id = $queue.id
            LEFT JOIN   $bounceType
                    ON  $bounce.bounce_type_id = $bounceType.id
            INNER JOIN  $job
                    ON  $queue.job_id = $job.id
                    AND $job.is_test = 0
            INNER JOIN  $mailing
                    ON  $job.mailing_id = $mailing.id
            WHERE       $mailing.id = " 
            . CRM_Utils_Type::escape($mailing_id, 'Integer');
    
        if (!empty($job_id)) {
            $query .= " AND $job.id = " 
                    . CRM_Utils_Type::escape($job_id, 'Integer');
        }

        if ($is_distinct) {
            $query .= " GROUP BY $queue.id ";
        }

        $orderBy = "sort_name ASC, {$bounce}.time_stamp DESC";
        if ($sort) {
            if ( is_string( $sort ) ) {
                $orderBy = $sort;
            } else {
                $orderBy = trim( $sort->orderBy() );
            }
        }
        $query .= " ORDER BY {$orderBy} ";
        
        if ($offset||$rowCount) {//Added "||$rowCount" to avoid displaying all records on first page
            $query .= ' LIMIT ' 
                    . CRM_Utils_Type::escape($offset, 'Integer') . ', ' 
                    . CRM_Utils_Type::escape($rowCount, 'Integer');
        }

        $dao->query($query);
        
        $results = array();

        while ($dao->fetch()) {
            $url = CRM_Utils_System::url('civicrm/contact/view',
                                "reset=1&cid={$dao->contact_id}");
            $results[] = array(
                'name'      => "<a href=\"$url\">{$dao->display_name}</a>",
                'email'     => $dao->email,
                            // FIXME: translate this
                'type'      => (empty($dao->bounce_type) 
                            ? ts('Unknown') : $dao->bounce_type),
                'reason'    => $dao->reason,
                'date'      => CRM_Utils_Date::customFormat($dao->date),
            );
        }
        return $results;
    }



    
}


