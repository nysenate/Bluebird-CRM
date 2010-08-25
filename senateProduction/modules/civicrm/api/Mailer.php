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
 * API functions for registering/processing mailer events.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/**
 * Files required for this package
 */


require_once 'api/utils.php';

require_once 'CRM/Contact/BAO/Group.php';

require_once 'CRM/Mailing/BAO/BouncePattern.php';
require_once 'CRM/Mailing/Event/BAO/Bounce.php';
require_once 'CRM/Mailing/Event/BAO/Confirm.php';
require_once 'CRM/Mailing/Event/BAO/Opened.php';
require_once 'CRM/Mailing/Event/BAO/Queue.php';
require_once 'CRM/Mailing/Event/BAO/Reply.php';
require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
require_once 'CRM/Mailing/Event/BAO/Resubscribe.php';
require_once 'CRM/Mailing/Event/BAO/Forward.php';


/**
 * Process a bounce event by passing through to the BAOs.
 *
 * @param int $job          ID of the job that caused this bounce
 * @param int $queue        ID of the queue event that bounced
 * @param string $hash      Security hash
 * @param string $body      Body of the bounce message
 * @return boolean
 */
function crm_mailer_event_bounce($job, $queue, $hash, $body) {
    
    $params = CRM_Mailing_BAO_BouncePattern::match($body);
    
    $params += array(   'job_id'            => $job,
                        'event_queue_id'    => $queue,
                        'hash'              => $hash);

    CRM_Mailing_Event_BAO_Bounce::create($params);
    return true;
}


/**
 * Handle an unsubscribe event
 *
 * @param int $job          ID of the job that caused this unsub
 * @param int $queue        ID of the queue event
 * @param string $hash      Security hash
 * @return boolean
 */
function crm_mailer_event_unsubscribe($job, $queue, $hash) {
    $groups =& CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_mailing($job, 
                                                                     $queue, $hash);
    
    if (count($groups)) {
        CRM_Mailing_Event_BAO_Unsubscribe::send_unsub_response($queue, $groups, false, $job);
        return true;
    }
    return false;
}

/**
 * Handle a site-level unsubscribe event
 *
 * @param int $job          ID of the job that caused this unsub
 * @param int $queue        ID of the queue event
 * @param string $hash      Security hash
 * @return boolean
 */
function crm_mailer_event_domain_unsubscribe($job, $queue, $hash) {
    if (! CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_domain($job,$queue,$hash)) {
        return false;
    }

    CRM_Mailing_Event_BAO_Unsubscribe::send_unsub_response($queue, null, 
                                                            true, $job);
    return true;
}

/**
 * Handle a resubscription event
 *
 * @param string $email     The email address to resubscribe
 * @param int $group_id     The group of the resubscription
 * @param string $hash      Security hash
 * @return boolean
 */
function crm_mailer_event_resubscribe($job, $queue, $hash) {
    $groups =&
        CRM_Mailing_Event_BAO_Resubscribe::resub_to_mailing($job, $queue, $hash);
    
    if (count($groups)) {
        CRM_Mailing_Event_BAO_Resubscribe::send_resub_response($queue, $groups, false, $job);
        return true;
    }
    return false;
}

/**
 * Handle a subscription event
 *
 * @param string $email     The email address to subscribe
 * @param int $group_id     The group of the subscription
 * @return boolean
 */
function crm_mailer_event_subscribe($email, $group_id) {
    $se =&
        CRM_Mailing_Event_BAO_Subscribe::subscribe($group_id, $email);

    if ($se !== null) {
        /* Ask the contact for confirmation */
        $se->send_confirm_request($email);
        return true;
    }
    return false;
}

/**
 * Handle a confirm event
 *
 * @param int $contact_id       The contact id
 * @param int $subscribe_id     The subscription event id
 * @param string $hash          Security hash to validate against
 * @return boolean
 */
function crm_mailer_event_confirm($contact_id, $subscribe_id, $hash) {
    return CRM_Mailing_Event_BAO_Confirm::confirm($contact_id, $subscribe_id,
                    $hash);
}


/**
 * Handle a reply event
 *
 * @param int $job_id           The job ID
 * @param int $queue_id         The queue event ID
 * @param string $hash          Security hash
 * @param string $bodyTxt       text part of the body (ignored if $fullEmail supplied)
 * @param string $replyto       Reply-to of the incoming message
 * @param string $bodyHTML      HTML part of the body (ignored if $fullEmail supplied)
 * @param string $fullEmail     whole email to forward in one string
 * @return boolean              True on success
 */
function crm_mailer_event_reply($job_id, $queue_id, $hash, $bodyTxt, $replyto, $bodyHTML = null, $fullEmail = null)
{
    $mailing =& CRM_Mailing_Event_BAO_Reply::reply($job_id, $queue_id, 
                                                    $hash, $replyto);

    if (empty($mailing)) {
        return false;
    }

    CRM_Mailing_Event_BAO_Reply::send($queue_id, $mailing, $bodyTxt, $replyto, $bodyHTML, $fullEmail);

    return true;
}

/**
 * Handle a forward event
 *
 * @param int $job_id           The job ID
 * @param int $queue_id         The queue ID
 * @param string $hash          Security hash
 * @param string $email         Forward destination address
 * @return boolean              True on success
 */
function crm_mailer_event_forward($job_id, $queue_id, $hash, $email, $fromEmail = null, $params = null ) {
    return CRM_Mailing_Event_BAO_Forward::forward($job_id, $queue_id, $hash,
                                                  $email, $fromEmail, $params );
}


