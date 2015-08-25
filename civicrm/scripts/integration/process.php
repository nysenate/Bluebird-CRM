<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2015-04-10

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

require_once dirname(__FILE__).'/../script_utils.php';


class CRM_Integration_Process
{
  function run()
  {
    // Parse the options
    $shortopts = "dsat:l:";
    $longopts = array("dryrun", "stats", "archive", "type=", "log-level=");
    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--stats] [--archive] [--type TYPE] [--log-level LEVEL]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if (isset($optlist['log-level'])) {
      set_bbscript_log_level($optlist['log-level']);
    }

    bbscript_log(LL::INFO, 'Initiating integration processing...');

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    bbscript_log(LL::DEBUG, 'Bluebird config:', $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from cleanLogs.');
      return FALSE;
    }

    bbscript_log(LL::DEBUG, 'Command line opts:', $optlist);

    //set integration DB
    $intDB = $bbcfg['integration.local.db.name'];
    $typeSql = ($optlist['type']) ? "AND msg_type = '{$optlist['type']}'" : '';

    //handle survey in special way
    if ($optlist['type'] == 'SURVEY') {
      $typeSql = "AND msg_type = 'PETITION'";
      $addSql = "AND msg_action = 'questionnaire response'";
    }

    //get all accumulator records for instance (target)
    $sql = "
      SELECT *
      FROM {$intDB}.accumulator
      WHERE user_shortname = '{$bbcfg['db.basename']}'
        $typeSql
        $addSql
    ";
    $row = CRM_Core_DAO::executeQuery($sql);
    bbscript_log(LL::DEBUG, 'SQL query:', $sql);

    $errors = $status = array();

    while ($row->fetch()) {
      bbscript_log(LL::TRACE, 'fetched row:', $row);

      //if context/direct message and target != user, skip
      if ($row->target_shortname != $row->user_shortname &&
        in_array($row->msg_type, array('DIRECTMSG', 'CONTEXTMSG'))
      ) {
        continue;
      }

      //check contact/user
      if (!$cid = CRM_NYSS_BAO_Integration::getContact($row->user_id)) {
        $contactParams = array(
          'web_user_id' => $row->user_id,
          'first_name' => $row->first_name,
          'last_name' => $row->last_name,
          'email' => $row->email_address,
          'street_address' => $row->address1,
          'supplemental_addresss_1' => $row->address2,
          'city' => $row->city,
          'state' => $row->state,
          'postal_code' => $row->zip,
          'birth_date' => date('Y-m-d', $row->dob),//dob comes as timestamp
          'gender_id' => ($row->gender == 'male') ?  2 : 1,//TODO check
        );

        $cid = CRM_NYSS_BAO_Integration::matchContact($contactParams);
      }
      //CRM_Core_Error::debug_var('cid', $cid);

      if (!empty($cid['is_error'])) {
        $stats['error'][] = array(
          'msg' => 'Unable to match or create contact',
          'cid' => $cid,
        );

        //archive row with null date
        if ($optlist['archive']) {
          CRM_NYSS_BAO_Integration::archiveRecord($intDB, 'other', $row, null, null);
        }

        continue;
      }

      //prep params
      $params = json_decode($row->msg_info);
      bbscript_log(LL::TRACE, 'Params after json_decode():', $params);

      $date = date('Y-m-d H:i:s', $row->created_at);
      $archiveTable = '';

      switch ($row->msg_type) {
        case 'BILL':
          $result = CRM_NYSS_BAO_Integration::processBill($cid, $row->msg_action, $params);
          $activity_type = 'Bill';
          $activity_details = "{$row->msg_action} :: {$params->bill_number}-{$params->bill_year} ({$params->bill_sponsor})";
          break;

        case 'ISSUE':
          $result = CRM_NYSS_BAO_Integration::processIssue($cid, $row->msg_action, $params);
          $activity_type = 'Issue';
          $activity_details = "{$row->msg_action} :: {$params->issue_name}";
          break;

        case 'COMMITTEE':
          $result = CRM_NYSS_BAO_Integration::processCommittee($cid, $row->msg_action, $params);
          $activity_type = 'Committee';
          $activity_details = "{$row->msg_action} :: {$params->committee_name}";
          break;

        case 'DIRECTMSG':
          $result = CRM_NYSS_BAO_Integration::processCommunication($cid, $row->msg_action, $params, $row->msg_type);
          $activity_type = 'Direct Message';
          $activity_details = "";
          break;

        case 'CONTEXTMSG':
          //disregard if no bill number
          if (!empty($params->bill_number)) {
            $result = CRM_NYSS_BAO_Integration::processCommunication($cid, $row->msg_action, $params, $row->msg_type);
            $activity_type = 'Context Message';
            $activity_details = "";
          }
          else {
            $archiveTable = 'other';
          }
          break;

        case 'PETITION':
          if ($row->msg_action == 'questionnaire response') {
            $result = CRM_NYSS_BAO_Integration::processSurvey($cid, $row->msg_action, $params);
            $activity_type = 'Survey';
            $activity_details = "survey :: {$params->form_title}";
            $archiveTable = 'survey';
          }
          else {
            $result = CRM_NYSS_BAO_Integration::processPetition($cid, $row->msg_action, $params);
            $activity_type = 'Petition';
            $activity_details = "{$row->msg_action} :: {$params->petition_name}";
          }
          break;

        /*case 'SURVEY':
          $result = CRM_NYSS_BAO_Integration::processSurvey($cid, $row->msg_action, $params);
          break;*/

        case 'ACCOUNT':
          $result = CRM_NYSS_BAO_Integration::processAccount($cid, $row->msg_action, $params, $date);
          $activity_type = 'Account';
          $activity_details = "{$row->msg_action}";
          break;

        case 'PROFILE':
          $result = CRM_NYSS_BAO_Integration::processProfile($cid, $row->msg_action, $params, $row);
          $activity_type = 'Profile';
          $activity_details = $row->msg_action;
          $activity_details .= ($params->status) ? " :: {$params->status}" : '';
          break;

        default:
          bbscript_log(LL::ERROR, 'Unable to process row. Message type is unknown.', $row);
          $result = array(
            'is_error' => 1,
            'details' => 'Unable to process row. Message type is unknown.',
          );
          $stats['unprocessed'][$row->msg_type][] = $row;
      }

      if ($result['is_error']) {
        $stats['error'][] = $result;
      }
      else {
        $stats['processed'][] = $row->id;

        //store activity log record
        CRM_NYSS_BAO_Integration::storeActivityLog($cid, $activity_type, $date, $activity_details);

        //archive rows by ID
        if ($optlist['archive']) {
          $archiveTable = (!empty($archiveTable)) ? $archiveTable : strtolower($row->msg_type);
          CRM_NYSS_BAO_Integration::archiveRecord($intDB, $archiveTable, $row, $params, $date);
        }
      }
    }

    //report stats
    $stats['counts'] = array(
      'processed' => count($stats['processed']),
      'unprocessed' => count($stats['unprocessed']),
      'error' => count($stats['error']),
    );

    bbscript_log(LL::NOTICE, "Processing stats:", $stats['counts']);

    if ($optlist['stats']) {
      bbscript_log(LL::NOTICE, "\nProcessing details:");
      bbscript_log(LL::NOTICE, "Processed:", $stats['processed']);
      bbscript_log(LL::NOTICE, "Unprocessed:", $stats['unprocessed']);
      bbscript_log(LL::NOTICE, "Errors:", $stats['error']);
    }
  }//run

}//end class

//run the script
$script = new CRM_Integration_Process();
$script->run();