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
    $shortopts = 'dsat:l:';
    $longopts = [ 'dryrun', 'stats', 'archive', 'type=', 'log-level=' ];
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

    bbscript_log(LL::INFO, 'Initiating website integration processing...');

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    bbscript_log(LL::DEBUG, 'Bluebird config:', $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? '';
    if (!CRM_Utils_System::loadBootstrap([], false, false, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from cleanLogs.');
      return false;
    }

    bbscript_log(LL::DEBUG, 'Command line opts:', $optlist);

    //set website integration DB
    $intDB = $bbcfg['website.local.db.name'];
    $typeSql = ($optlist['type']) ? "AND msg_type = '{$optlist['type']}'" : '';
    $addSql = '';

    //handle survey in special way
    if ($optlist['type'] == 'SURVEY') {
      $typeSql = "AND msg_type = 'PETITION'";
      $addSql = "AND msg_action = 'questionnaire response'";
    }

    //get all accumulator records for instance (target)
    $sql = "
      SELECT *
      FROM {$intDB}.accumulator
      WHERE target_shortname = '{$optlist['site']}'
        AND (target_shortname = user_shortname OR msg_type = 'PROFILE')
        $typeSql
        $addSql
    ";
    $row = CRM_Core_DAO::executeQuery($sql);
    bbscript_log(LL::DEBUG, 'SQL query:', $sql);

    $stats = [ 'processed' => [], 'unprocessed' => [], 'error' => [] ];

    while ($row->fetch()) {
      bbscript_log(LL::TRACE, 'fetched row:', $row);

      //if context/direct message and target != user, skip
      if ($row->target_shortname != $row->user_shortname &&
          in_array($row->msg_type, ['DIRECTMSG', 'CONTEXTMSG'])
      ) {
        CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, 'other', $row, null);
        continue;
      }

      //prep params
      $params = json_decode($row->msg_info);
      bbscript_log(LL::TRACE, 'Params after json_decode():', $params);

      $date = date('Y-m-d H:i:s', $row->created_at);

      //check contact/user
      bbscript_log(LL::TRACE, 'calling getContactId('.$row->user_id.')');
      $cid = CRM_NYSS_BAO_Integration_Website::getContactId($row->user_id);
      if (!$cid) {
        bbscript_log(LL::DEBUG, 'Contact with web_user_id='.$row->user_id.' was not found; attempting match');

        $contactParams = CRM_NYSS_BAO_Integration_Website::getContactParams($row);
        if (empty($contactParams)) {
          bbscript_log(LL::DEBUG, 'Unable to create user; not enough data provided.', $row);

          if ($optlist['archive']) {
            $archiveTable = strtolower($row->msg_type);
            bbscript_log(LL::DEBUG, "Archiving unmatched/uncreated record to $archiveTable and archive_error table");
            CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params, false);
          }

          continue;
        }

        $contactParams['gender_id'] = '';
        if ($row->gender) {
          switch ($row->gender) {
            case 'male':
              $contactParams['gender_id'] = 2;
              break;
            case 'female':
              $contactParams['gender_id'] = 1;
              break;
            case 'other':
              $contactParams['gender_id'] = 4;
              break;
            default:
          }
        }

        $contactParams['birth_date'] = '';
        if (!empty($row->dob)) {
          $contactParams['birth_date'] = date('Y-m-d', $row->dob); //dob comes as timestamp
        }

        bbscript_log(LL::TRACE, 'calling matchContact() with:', $contactParams);
        $cid = CRM_NYSS_BAO_Integration_Website::matchContact($contactParams);
      }

      if (!$cid) {
        bbscript_log(LL::DEBUG, 'Failed to match or create contact', $contactParams);
        $stats['error'][] = [
          'is_error' => 1,
          'error_message' => 'Unable to match or create contact',
          'params' => $contactParams
        ];

        //archive row with null date
        if ($optlist['archive']) {
          $archiveTable = strtolower($row->msg_type);
          bbscript_log(LL::DEBUG, "Archiving non-matched record to $archiveTable and archive_error table");
          CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params, false);
        }

        continue;
      }

      //update email address
      CRM_NYSS_BAO_Integration_Website::updateEmail($cid, $row);

      $archiveTable = $activity_data = '';
      $skipActivityLog = false;

      bbscript_log(LL::DEBUG, "Processing message of type [{$row->msg_type}]");

      switch ($row->msg_type) {
        case 'BILL':
          $result = CRM_NYSS_BAO_Integration_Website::processBill($cid, $row->msg_action, $params);
          $activity_type = 'Bill';
          $billName = CRM_NYSS_BAO_Integration_Website::buildBillName($params);
          $activity_details = "{$row->msg_action} :: {$billName}";
          break;

        case 'ISSUE':
          $result = CRM_NYSS_BAO_Integration_Website::processIssue($cid, $row->msg_action, $params);
          $activity_type = 'Issue';
          $activity_details = "{$row->msg_action} :: {$params->issue_name}";
          break;

        case 'COMMITTEE':
          $result = CRM_NYSS_BAO_Integration_Website::processCommittee($cid, $row->msg_action, $params);
          $activity_type = 'Committee';
          $activity_details = "{$row->msg_action} :: {$params->committee_name}";
          break;

        case 'DIRECTMSG':
          $result = CRM_NYSS_BAO_Integration_Website::processCommunication($cid, $row->msg_action, $params,
            $row->msg_type, $row->created_at);
          $activity_type = 'Direct Message';
          $activity_details = ($row->subject) ? $row->subject : '';
          $activity_data = json_encode(['note_id' => $result['id']]);
          break;

        case 'CONTEXTMSG':
          $result = CRM_NYSS_BAO_Integration_Website::processCommunication($cid, $row->msg_action, $params,
            $row->msg_type, $row->created_at);
          $activity_type = 'Context Message';
          $activity_details = ($row->subject) ? $row->subject : '';
          $activity_data = json_encode(['note_id' => $result['id']]);
          break;

        case 'PETITION':
          if ($row->msg_action == 'questionnaire response') {
            $result = CRM_NYSS_BAO_Integration_Website::processSurvey($cid, $row->msg_action, $params);
            $activity_type = 'Survey';
            $activity_details = "survey :: {$params->form_title}";
            $archiveTable = 'survey';
          }
          else {
            $result = CRM_NYSS_BAO_Integration_Website::processPetition($cid, $row->msg_action, $params);
            $activity_type = 'Petition';
            $tagName = CRM_NYSS_BAO_Integration_Website::getTagName($params, 'petition_name');
            $activity_details = "{$row->msg_action} :: {$tagName}";
          }
          break;

        /*case 'SURVEY':
          $result = CRM_NYSS_BAO_Integration_Website::processSurvey($cid, $row->msg_action, $params);
          break;*/

        case 'ACCOUNT':
          $result = CRM_NYSS_BAO_Integration_Website::processAccount($cid, $row->msg_action, $params, $date);
          $activity_type = 'Account';
          $activity_details = "{$row->msg_action}";

          if ($row->msg_action == 'account created') {
            CRM_NYSS_BAO_Integration_Website::processProfile($cid, 'account edited', $params, $row);
          }

          break;

        case 'PROFILE':
          $result = CRM_NYSS_BAO_Integration_Website::processProfile($cid, $row->msg_action, $params, $row);
          $activity_type = 'Profile';
          $activity_details = $row->msg_action;
          $activity_details .= ($params->status) ? " :: {$params->status}" : '';
          break;

        default:
          $result = [
            'is_error' => 1,
            'error_message' => "Unable to process row; message type [{$row->msg_type}] is unknown"
          ];
          $stats['unprocessed'][] = $row;
      }

      if ($result['is_error'] || $result == FALSE) {
        bbscript_log(LL::ERROR, 'Unable to process row', $result);
        bbscript_log(LL::ERROR, 'Row details', $row);
        $stats['error'][] = $result;

        //archive rows by ID into archive_error table
        if ($optlist['archive']) {
          $archiveTable = (!empty($archiveTable)) ? $archiveTable : strtolower($row->msg_type);
          bbscript_log(LL::DEBUG, 'Archiving matched/created record to $archiveTable and archive_error table');
          CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params, false);
        }
      }
      else {
        $stats['processed'][] = $row->id;

        //store activity log record
        if (!$skipActivityLog) {
          bbscript_log(LL::DEBUG, "Storing activity log record; cid=$cid; type=$activity_type");
          CRM_NYSS_BAO_Integration_Website::storeActivityLog($cid, $activity_type, $date, $activity_details, $activity_data);
        }

        //archive rows by ID
        if ($optlist['archive']) {
          $archiveTable = (!empty($archiveTable)) ? $archiveTable : strtolower($row->msg_type);
          bbscript_log(LL::DEBUG, 'Archiving matched/created record to $archiveTable table');
          CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params);
        }
      }
    }

    //report stats
    $counts = [
      'processed' => count($stats['processed']),
      'unprocessed' => count($stats['unprocessed']),
      'error' => count($stats['error'])
    ];

    bbscript_log(LL::NOTICE, "Processing stats:", $counts);

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
