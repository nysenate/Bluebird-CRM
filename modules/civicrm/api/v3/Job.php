<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * new version of civicrm apis. See blog post at
 * http://civicrm.org/node/131
 * @todo Write sth
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Job
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id: Contact.php 30879 2010-11-22 15:45:55Z shot $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v3/utils.php';

/**
 * Dumb wrapper to execute scheduled jobs. Always creates success - errors
 * and results are handled in the job log.
 *
 * @param  array   	  $params (reference ) input parameters
 *
 * @return array API Result Array
 *
 * @static void
 * @access public
 *
 */
function civicrm_api3_job_execute($params) {
  require_once 'CRM/Core/JobManager.php';
  $facility = new CRM_Core_JobManager();
  $facility->execute(CRM_Utils_Array::value('auth', $params, TRUE));

  // always creates success - results are handled elsewhere
  return civicrm_api3_create_success();
}

/**
 * Geocode group of contacts based on given params
 *
 * @param  array   	  $params (reference ) input parameters
 *
 * @return array API Result Array
 * {@getfields contact_geocode}
 *
 * @static void
 * @access public
 *
 *
 */
function civicrm_api3_job_geocode($params) {

  // available params:
  // 'start=', 'end=', 'geocoding=', 'parse=', 'throttle='

  require_once 'CRM/Utils/Address/BatchUpdate.php';
  $gc = new CRM_Utils_Address_BatchUpdate($params);


  $result = $gc->run();

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success($result['messages']);
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}
/*
 * First check on Code documentation
 */
function _civicrm_api3_contact_geocode_spec(&$params) {
  $params['start'] = array('title' => 'Start Date');
}

/**
 * Send the scheduled reminders for all contacts (either for activities or events)
 *
 * @param  array   	  $params (reference ) input parameters
 *                        now - the time to use, in YmdHis format
 *                            - makes testing a bit simpler since we can simulate past/future time
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 *
 */
function civicrm_api3_job_send_reminder($params) {
  require_once 'CRM/Core/BAO/ActionSchedule.php';
  $result = CRM_Core_BAO_ActionSchedule::processQueue(CRM_Utils_Array::value('now', $params));

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}

/**
 * Execute a specific report instance and send the output via email
 *
 * @param  array   	  $params (reference ) input parameters
 *                        sendmail - Boolean - should email be sent?, required
 *                        instanceId - Integer - the report instance ID
 *                        resetVal - Integer - should we reset form state (always true)?
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 *
 */
function civicrm_api3_job_mail_report($params) {
  require_once 'CRM/Report/Utils/Report.php';
  $result = CRM_Report_Utils_Report::processReport($params);

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}

/**
 *
 * This method allows to update Email Greetings, Postal Greetings and Addressee for a specific contact type.
 * IMPORTANT: You must first create valid option value before using via admin interface.
 * Check option lists for Email Greetings, Postal Greetings and Addressee
 *
 * @param  array   	  $params (reference ) input parameters
 *                        ct - String - ct=Individual or ct=Household or ct=Organization
 *                        gt - String - gt=email_greeting or gt=postal_greeting or gt=addressee
 *                        id - Integer - greetings option group
 *
 * @return boolean        true if success, else false
 * @static
 * @access public
 *
 */
function civicrm_api3_job_update_greeting($params) {
  require_once 'CRM/Contact/BAO/Contact/Utils.php';

  civicrm_api3_verify_mandatory($params, NULL, array('ct', 'gt'));
  // fixme - use the wrapper & getfields to do this checking - advertise as an enum
  if (!in_array($params['ct'],
      array('Individual', 'Household', 'Organization')
    )) {
    return civicrm_api3_create_error(ts('Invalid contact type (ct) parameter value'));
  }

  if (!in_array($params['gt'],
      array('email_greeting', 'postal_greeting', 'addressee')
    )) {
    return civicrm_api3_create_error(ts('Invalid greeting type (gt) parameter value'));
  }

  $result = CRM_Contact_BAO_Contact_Utils::updateGreeting($params);

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}

/**
 * Mass update pledge statuses
 *
 * @param  array   	  $params (reference ) input parameters
 *
 * @return boolean        true if success, else false
 * @static
 * @access public
 *
 */
function civicrm_api3_job_process_pledge($params) {

  require_once 'CRM/Pledge/BAO/Pledge.php';
  $result = CRM_Pledge_BAO_Pledge::updatePledgeStatus($params);

  if ($result['is_error'] == 0) {
    // experiment: detailed execution log is a result here
    return civicrm_api3_create_success($result['messages']);
  }
  else {
    return civicrm_api3_create_error($result['error_message']);
  }
}

/**
 * Process mail queue
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_job_process_mailing($params) {
  require_once 'CRM/Mailing/BAO/Mailing.php';
  if (!CRM_Mailing_BAO_Mailing::processQueue()) {
    return civicrm_api3_create_error("Process Queue failed");
  }
  else {
    $values = array();
    return civicrm_api3_create_success($values, $params, 'mailing', 'process');
  }
}

/**
 * Process sms queue
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_job_process_sms($params) {
  require_once 'CRM/Mailing/BAO/Mailing.php';
  if (!CRM_Mailing_BAO_Mailing::processQueue('sms')) {
    return civicrm_api3_create_error("Process Queue failed");
  }
  else {
    $values = array();
    return civicrm_api3_create_success($values, $params, 'mailing', 'process');
  }
}

function civicrm_api3_job_fetch_bounces($params) {
  require_once 'CRM/Utils/Mail/EmailProcessor.php';
  require_once 'CRM/Core/Lock.php';
  $lock = new CRM_Core_Lock('EmailProcessor');
  if (!$lock->isAcquired()) {
    return civicrm_api3_create_error("Could not acquire lock, another EmailProcessor process is running");
  }
  if (!CRM_Utils_Mail_EmailProcessor::processBounces()) {
    return civicrm_api3_create_error("Process Bounces failed");
  }
  //   FIXME: processBounces doesn't return true/false on success/failure
  $values = array();
  $lock->release();
  return civicrm_api3_create_success($values, $params, 'mailing', 'bounces');
}

function civicrm_api3_job_fetch_activities($params) {
  require_once 'CRM/Utils/Mail/EmailProcessor.php';
  require_once 'CRM/Core/Lock.php';
  $lock = new CRM_Core_Lock('EmailProcessor');
  if (!$lock->isAcquired()) {
    return civicrm_api3_create_error("Could not acquire lock, another EmailProcessor process is running");
  }
    try {
       CRM_Utils_Mail_EmailProcessor::processActivities();
       $values = array( );
    $lock->release();
       return civicrm_api3_create_success($values, $params,'mailing','activities');
    } catch (Exception $e) {
    $lock->release();
    return civicrm_api3_create_error("Process Activities failed");
  }
}

/**
 * Process participant statuses
 *
 * @param  array   $params           (reference ) input parameters
 *
 * @return array (reference )        array of properties, if error an array with an error id and error message
 * @access public
 */
function civicrm_api3_job_process_participant($params) {
  require_once 'CRM/Event/BAO/ParticipantStatusType.php';
  $result = CRM_Event_BAO_ParticipantStatusType::process($params);

  if (!$result['is_error']) {
    return civicrm_api3_create_success(implode("\r\r", $result['messages']));
  }
  else {
    return civicrm_api3_create_error('Error while processing participant statuses');
  }
}


/*
 * This api checks and updates the status of all membership records for a given domain using the calc_membership_status and
 * update_contact_membership APIs. It also sends renewal reminders if those have been configured for your membership types.
 *
 * IMPORTANT:
 * It uses the default Domain FROM Name and FROM Email Address as the From email address for emails sent by this api.
 * Verify that this value has been properly set from Administer > Configure > Domain Information
 * If you want to use some other FROM email address, modify line 2341 in CRM/Member/BAO/Membership.php and set your valid email address.
 *
 * @param  array   	  $params (reference ) input parameters NOT USED
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_api3_job_process_membership($params) {
  require_once 'CRM/Member/BAO/Membership.php';
  $result = CRM_Member_BAO_Membership::updateAllMembershipStatus();

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success($result['messages']);
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}

/*
 * This api checks and updates the status of all survey respondants.
 *
 * @param  array   	  $params (reference ) input parameters
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_api3_job_process_respondent($params) {
  require_once 'CRM/Campaign/BAO/Survey.php';
  $result = CRM_Campaign_BAO_Survey::releaseRespondent($params);

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}


/*
 * This api sets the renewal reminder date for memberships which do not have one set yet. Useful for memberships which were
 * added prior to the reminder date property being set for a given membership type (and hence do not have a reminder date set).
 *
 * @param  array   	  $params (reference ) - NOT USED for this api
 *
 * @return boolean        true if success, else false
 * @static void
 * @access public
 */
function civicrm_api3_job_process_membership_reminder_date($params) {
  require_once 'CRM/Member/BAO/Membership.php';
  $result = CRM_Member_BAO_Membership::updateMembershipReminderDate($params);

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}

/**
 * Merges given pair of duplicate contacts.
 *
 * @param  array   $params   input parameters
 *
 * Allowed @params array keys are:
 * {int     $rgid        rule group id}
 * {int     $gid         group id}
 * {string  mode        helps decide how to behave when there are conflicts.
 *                      A 'safe' value skips the merge if there are no conflicts. Does a force merge otherwise.}
 * {boolean auto_flip   wether to let api decide which contact to retain and which to delete.}
 *
 * @return array  API Result Array
 *
 * @static void
 * @access public
 */
function civicrm_api3_job_process_batch_merge($params) {
  $rgid = CRM_Utils_Array::value('rgid', $params);
  $gid = CRM_Utils_Array::value('gid', $params);

  $mode = CRM_Utils_Array::value('mode', $params, 'safe');
  $autoFlip = CRM_Utils_Array::value('auto_flip', $params, TRUE);

  require_once 'CRM/Dedupe/Merger.php';
  $result = CRM_Dedupe_Merger::batchMerge($rgid, $gid, $mode, $autoFlip);

  if ($result['is_error'] == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($result['messages']);
  }
}

/**
 * Runs handlePaymentCron method in the specified payment processor
 *
 * @param  array   $params   input parameters
 *
 * Expected @params array keys are:
 * {string  'processor_name' - the name of the payment processor, eg: Sagepay}
 *
 * @access public
 */
function civicrm_api3_job_run_payment_cron($params) {

  require_once 'CRM/Core/Payment.php';

  // live mode
  CRM_Core_Payment::handlePaymentMethod(
    'PaymentCron',
    array_merge(
      $params,
      array(
        'caller' => 'api',
      )
    )
  );

  // test mode
  CRM_Core_Payment::handlePaymentMethod(
    'PaymentCron',
    array_merge(
      $params,
      array(
        'mode' => 'test',
      )
    )
  );
}

/*
 * This api cleans up all the old session entries and temp tables. We recommend that sites run this on an hourly basis
 *
 * @param  array    $params (reference ) - sends in various config parameters to decide what needs to be cleaned
 *
 * @return boolean  true if success, else false
 * @static void
 * @access public
 */
function civicrm_api3_job_cleanup( $params ) {
  require_once 'CRM/Utils/Array.php';

  $session   = CRM_Utils_Array::value( 'session'   , $params, true  );
  $tempTable = CRM_Utils_Array::value( 'tempTables', $params, true  );
  $jobLog    = CRM_Utils_Array::value( 'jobLog'    , $params, true  );
  $dbCache   = CRM_Utils_Array::value( 'dbCache'   , $params, false );
  $memCache  = CRM_Utils_Array::value( 'memCache'  , $params, false );
  $prevNext  = CRM_Utils_Array::value( 'prevNext'  , $params, false );

  if ( $session || $tempTable || $prevNext ) {
    require_once 'CRM/Core/BAO/Cache.php';
    CRM_Core_BAO_Cache::cleanup( $session, $tempTable, $prevNext );
  }

  if ( $jobLog ) {
    CRM_Core_BAO_Job::cleanup( );
  }

  if ( $dbCache ) {
    CRM_Core_Config::clearDBCache( );
  }

  if ( $memCache ) {
    CRM_Utils_System::flushCache( );
  }
}
