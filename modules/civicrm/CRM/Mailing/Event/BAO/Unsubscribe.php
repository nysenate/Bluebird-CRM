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

require_once 'Mail/mime.php';
require_once 'CRM/Utils/Mail.php';

require_once 'CRM/Mailing/Event/DAO/Unsubscribe.php';
require_once 'CRM/Mailing/BAO/Job.php'; 
require_once 'CRM/Mailing/BAO/Mailing.php';
require_once 'CRM/Mailing/DAO/Group.php';
require_once 'CRM/Contact/BAO/Group.php';
require_once 'CRM/Contact/BAO/GroupContact.php';
require_once 'CRM/Core/BAO/Domain.php';

class CRM_Mailing_Event_BAO_Unsubscribe extends CRM_Mailing_Event_DAO_Unsubscribe {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Unsubscribe a contact from the domain
     *
     * @param int $job_id       The job ID
     * @param int $queue_id     The Queue Event ID of the recipient
     * @param string $hash      The hash
     * @return boolean          Was the contact succesfully unsubscribed?
     * @access public
     * @static
     */
    public static function unsub_from_domain($job_id, $queue_id, $hash) {
        $q =& CRM_Mailing_Event_BAO_Queue::verify($job_id, $queue_id, $hash);
        if (! $q) {
            return false;
        }
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        $contact = new CRM_Contact_BAO_Contact();
        $contact->id = $q->contact_id;
        $contact->is_opt_out = true;
        $contact->save();
        
        $ue = new CRM_Mailing_Event_BAO_Unsubscribe();
        $ue->event_queue_id = $queue_id;
        $ue->org_unsubscribe = 1;
        $ue->time_stamp = date('YmdHis');
        $ue->save();

        $shParams = array(
            'contact_id'    => $q->contact_id,
            'group_id'      => null,
            'status'        => 'Removed',
            'method'        => 'Email',
            'tracking'      => $ue->id
        );
        CRM_Contact_BAO_SubscriptionHistory::create($shParams);
        
        $transaction->commit( );
        
        return true;
    }

    /**
     * Unsubscribe a contact from all groups that received this mailing
     *
     * @param int $job_id       The job ID
     * @param int $queue_id     The Queue Event ID of the recipient
     * @param string $hash      The hash
     * @param boolean $return   If true return the list of groups.
     * @return array|null $groups    Array of all groups from which the contact was removed, or null if the queue event could not be found.
     * @access public
     * @static
     */
    public static function &unsub_from_mailing($job_id, $queue_id, $hash, $return = false) {
        /* First make sure there's a matching queue event */
        $q =& CRM_Mailing_Event_BAO_Queue::verify($job_id, $queue_id, $hash);
        $success = null;
        if (! $q) {
            return $success;
        }
        
        $contact_id = $q->contact_id;
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        $do = new CRM_Core_DAO();
        $mg         = CRM_Mailing_DAO_Group::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $group      = CRM_Contact_BAO_Group::getTableName();
        $gc         = CRM_Contact_BAO_GroupContact::getTableName();
        
        //We Need the mailing Id for the hook...
        $do->query("SELECT $job.mailing_id as mailing_id 
                     FROM   $job 
                     WHERE $job.id = " . CRM_Utils_Type::escape($job_id, 'Integer'));
        $do->fetch();
        $mailing_id = $do->mailing_id;

        $do->query("
            SELECT      $mg.entity_table as entity_table,
                        $mg.entity_id as entity_id,
                        $mg.group_type as group_type
            FROM        $mg
            INNER JOIN  $job
                ON      $job.mailing_id = $mg.mailing_id
            INNER JOIN  $group
                ON      $mg.entity_id = $group.id
            WHERE       $job.id = " 
                . CRM_Utils_Type::escape($job_id, 'Integer') . "
                AND     $mg.group_type IN ('Include', 'Base') 
                AND     $group.is_hidden = 0");
        
        /* Make a list of groups and a list of prior mailings that received 
         * this mailing */
         
        $groups = array();
        $base_groups = array();
        $mailings = array();
        
        while ($do->fetch()) {
            if ($do->entity_table == $group) {
                if($do->group_type == 'Base') {
                    $base_groups[$do->entity_id] = null;
                } else {
                    $groups[$do->entity_id] = null;
                }
            } else if ($do->entity_table == $mailing) {
                $mailings[] = $do->entity_id;
            }
        }
        
        /* As long as we have prior mailings, find their groups and add to the
         * list */
        while (! empty($mailings)) {
            $do->query("
                SELECT      $mg.entity_table as entity_table,
                            $mg.entity_id as entity_id
                FROM        $mg
                WHERE       $mg.mailing_id IN (".implode(', ', $mailings).")
                    AND     $mg.group_type = 'Include'");
            
            $mailings = array();
            
            while ($do->fetch()) {
                if ($do->entity_table == $group) {
                    $groups[$do->entity_id] = true;
                } else if ($do->entity_table == $mailing) {
                    $mailings[] = $do->entity_id;
                }
            }
        }

        //Pass the groups to be unsubscribed from through a hook.
        require_once 'CRM/Utils/Hook.php';
        $group_ids = array_keys($groups);
        $base_group_ids = array_keys($base_groups);
        CRM_Utils_Hook::unsubscribeGroups('unsubscribe', $mailing_id, $contact_id, $group_ids, $base_group_ids);

        /* Now we have a complete list of recipient groups.  Filter out all
         * those except smart groups, those that the contact belongs to and
         * base groups from search based mailings */
        $baseGroupClause = '';
        if ( !empty($base_group_ids) ) {
            $baseGroupClause = "OR  $group.id IN(".implode(', ', $base_group_ids).")";
        }
        $do->query("
            SELECT      $group.id as group_id,
                        $group.title as title,
                        $group.description as description
            FROM        $group
            LEFT JOIN   $gc
                ON      $gc.group_id = $group.id
            WHERE       $group.id IN (".implode(', ', array_merge($group_ids, $base_group_ids)) .")
                AND     $group.is_hidden = 0
                AND     ($group.saved_search_id is not null
                            OR  ($gc.contact_id = $contact_id
                                AND $gc.status = 'Added')
                            $baseGroupClause
                        )");
                        
        if ($return) {
            while ($do->fetch()) {
                $groups[$do->group_id] = array( 'title'       => $do->title,
                                                'description' => $do->description);
            }
            return $groups;
        } else {
            while ($do->fetch()) {
                $groups[$do->group_id] = $do->title;
            }
        }

        $contacts = array($contact_id);
        foreach ($groups as $group_id => $group_name) {
            $notremoved = false;
            if ($group_name) {
                if(in_array($group_id, $base_group_ids)) {
                    list($total, $removed, $notremoved) = CRM_Contact_BAO_GroupContact::addContactsToGroup( $contacts, $group_id, 'Email', 'Removed');
                } else {
                    list($total, $removed, $notremoved) = CRM_Contact_BAO_GroupContact::removeContactsFromGroup( $contacts, $group_id, 'Email');
                }
            }
            if ($notremoved) {
                unset($groups[$group_id]);
            }
        }
        
        $ue = new CRM_Mailing_Event_BAO_Unsubscribe();
        $ue->event_queue_id = $queue_id;
        $ue->org_unsubscribe = 0;
        $ue->time_stamp = date('YmdHis');
        $ue->save();
        
        $transaction->commit( );
        return $groups;
    }

    /**
     * Send a reponse email informing the contact of the groups from which he
     * has been unsubscribed.
     *
     * @param string $queue_id      The queue event ID
     * @param array $groups         List of group IDs
     * @param bool $is_domain       Is this domain-level?
     * @param int $job              The job ID
     * @return void
     * @access public
     * @static
     */
    public static function send_unsub_response($queue_id, $groups, $is_domain = false, $job) {
        $config = CRM_Core_Config::singleton();
        $domain =& CRM_Core_BAO_Domain::getDomain( );
        
        $jobTable = CRM_Mailing_BAO_Job::getTableName();
        $mailingTable = CRM_Mailing_DAO_Mailing::getTableName();
        $contacts = CRM_Contact_DAO_Contact::getTableName();
        $email = CRM_Core_DAO_Email::getTableName();
        $queue = CRM_Mailing_Event_BAO_Queue::getTableName();
        
        //get the default domain email address.
        list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );

        $dao = new CRM_Mailing_BAO_Mailing();
        $dao->query("   SELECT * FROM $mailingTable 
                        INNER JOIN $jobTable ON
                            $jobTable.mailing_id = $mailingTable.id 
                        WHERE $jobTable.id = $job");
        $dao->fetch();

        $component = new CRM_Mailing_BAO_Component();
        
        if ($is_domain) {
            $component->id = $dao->optout_id;
        } else {
            $component->id = $dao->unsubscribe_id;
        }
        $component->find(true);
        
        $html = $component->body_html;
        if ($component->body_text) {
            $text = $component->body_text;
        } else {
            $text = CRM_Utils_String::htmlToText($component->body_html);
        }

        $eq = new CRM_Core_DAO();
        $eq->query(
        "SELECT     $contacts.preferred_mail_format as format,
                    $contacts.id as contact_id,
                    $email.email as email,
                    $queue.hash as hash
        FROM        $contacts
        INNER JOIN  $queue ON $queue.contact_id = $contacts.id
        INNER JOIN  $email ON $queue.email_id = $email.id
        WHERE       $queue.id = " 
        . CRM_Utils_Type::escape($queue_id, 'Integer'));
        $eq->fetch();

        if ( $groups ) {
            foreach ( $groups as $key => $value ) {
                if (!$value) {
                    unset($groups[$key]);
                }
            }
        }
        
        $message = new Mail_mime("\n");

        list($addresses, $urls) = CRM_Mailing_BAO_Mailing::getVerpAndUrls($job, $queue_id, $eq->hash, $eq->email);
        $bao = new CRM_Mailing_BAO_Mailing();
        $bao->body_text = $text;
        $bao->body_html = $html;
        $tokens = $bao->getTokens();
        require_once 'CRM/Utils/Token.php';
        if ($eq->format == 'HTML' || $eq->format == 'Both') {
            $html = 
                CRM_Utils_Token::replaceDomainTokens($html, $domain, true, $tokens['html']);
            $html = 
                CRM_Utils_Token::replaceUnsubscribeTokens($html, $domain, $groups, true, $eq->contact_id, $eq->hash);
            $html = CRM_Utils_Token::replaceActionTokens($html, $addresses, $urls, true, $tokens['html']);
            $html = CRM_Utils_Token::replaceMailingTokens($html, $dao, null, $tokens['html']);
            $message->setHTMLBody($html);
        }
        if (!$html || $eq->format == 'Text' || $eq->format == 'Both') {
            $text = 
                CRM_Utils_Token::replaceDomainTokens($text, $domain, false, $tokens['text']);
            $text = 
                CRM_Utils_Token::replaceUnsubscribeTokens($text, $domain, $groups, false, $eq->contact_id, $eq->hash);
            $text = CRM_Utils_Token::replaceActionTokens($text, $addresses, $urls, false, $tokens['text']);
            $text = CRM_Utils_Token::replaceMailingTokens($text, $dao, null, $tokens['text']);
            $message->setTxtBody($text);
        }

        require_once 'CRM/Core/BAO/MailSettings.php';
        $emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();

        $headers = array(
                         'Subject'       => $component->subject,
                         'From'          => "\"$domainEmailName\" <do-not-reply@$emailDomain>",
                         'To'            => $eq->email,
                         'Reply-To'      => "do-not-reply@$emailDomain",
                         'Return-Path'   => "do-not-reply@$emailDomain",
                         );
        
        $b =& CRM_Utils_Mail::setMimeParams( $message );
        $h =& $message->headers($headers);

        $mailer =& $config->getMailer();
        
        PEAR::setErrorHandling( PEAR_ERROR_CALLBACK,
                                array('CRM_Core_Error', 'nullHandler' ) );
        if ( is_object( $mailer ) ) {
            $mailer->send($eq->email, $h, $b);
            CRM_Core_Error::setCallback();
        }
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
        
        $unsub      = self::getTableName();
        $queue      = CRM_Mailing_Event_BAO_Queue::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();

        $query = "
            SELECT      COUNT($unsub.id) as unsubs
            FROM        $unsub
            INNER JOIN  $queue
                    ON  $unsub.event_queue_id = $queue.id
            INNER JOIN  $job
                    ON  $queue.job_id = $job.id
            INNER JOIN  $mailing
                    ON  $job.mailing_id = $mailing.id
                    AND $job.is_test = 0
            WHERE       $mailing.id = " 
            . CRM_Utils_Type::escape($mailing_id, 'Integer');

        if (!empty($job_id)) {
            $query  .= " AND $job.id = " 
                    . CRM_Utils_Type::escape($job_id, 'Integer');
        }
        
        if ($is_distinct) {
            $query .= " GROUP BY $queue.id ";
        }

        $dao->query($query);
        $dao->fetch();
        if ($is_distinct) {
            return $dao->N;
        } else {
            return $dao->unsubs ? $dao->unsubs : 0;
        }
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
        
        $unsub      = self::getTableName();
        $queue      = CRM_Mailing_Event_BAO_Queue::getTableName();
        $mailing    = CRM_Mailing_BAO_Mailing::getTableName();
        $job        = CRM_Mailing_BAO_Job::getTableName();
        $contact    = CRM_Contact_BAO_Contact::getTableName();
        $email      = CRM_Core_BAO_Email::getTableName();

        $query =    "
            SELECT      $contact.display_name as display_name,
                        $contact.id as contact_id,
                        $email.email as email,
                        $unsub.time_stamp as date,
                        $unsub.org_unsubscribe as org_unsubscribe
            FROM        $contact
            INNER JOIN  $queue
                    ON  $queue.contact_id = $contact.id
            INNER JOIN  $email
                    ON  $queue.email_id = $email.id
            INNER JOIN  $unsub
                    ON  $unsub.event_queue_id = $queue.id
            INNER JOIN  $job
                    ON  $queue.job_id = $job.id
            INNER JOIN  $mailing
                    ON  $job.mailing_id = $mailing.id
                    AND $job.is_test = 0
            WHERE       $mailing.id = " 
            . CRM_Utils_Type::escape($mailing_id, 'Integer');
    
        if (!empty($job_id)) {
            $query .= " AND $job.id = " 
                    . CRM_Utils_Type::escape($job_id, 'Integer');
        }

        if ($is_distinct) {
            $query .= " GROUP BY $queue.id ";
        }

        $orderBy = "sort_name ASC, {$unsub}.time_stamp DESC";
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
                'org'       => $dao->org_unsubscribe ? ts('Yes') : ts('No'),
                'date'      => CRM_Utils_Date::customFormat($dao->date)
            );
        }
        return $results;
    }
    
    public static function getContactInfo($queueID) {
        $query = "
SELECT DISTINCT(civicrm_mailing_event_queue.contact_id) as contact_id,
       civicrm_contact.display_name as display_name
       civicrm_email.email as email
  FROM civicrm_mailing_event_queue,
       civicrm_contact,
       civicrm_email
 WHERE civicrm_mailing_event_queue.contact_id = civicrm_contact.id
   AND civicrm_mailing_event_queue.email_id = civicrm_email.id
   AND civicrm_mailing_event_queue.id = " . CRM_Utils_Type::escape($queueID, 'Integer');
        
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        
        $displayName = 'Unknown';
        $email       = 'Unknown';
        if ( $dao->fetch( ) ) { 
           $displayName = $dao->display_name;
           $email       = $dao->email;
        }
        
        return array( $displayName, $email );
    }
}


