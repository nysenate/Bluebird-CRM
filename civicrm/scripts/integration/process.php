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

    private bool $dry = false;
    private ?array $optlist = null;

    /**
     * @var ?int Max number of entries / rows to query and process during a run. Null means no limit.
     */
    private ?int $max = null;
  function run()
  {
    // Parse the options
    $shortopts = 'dsat:l:m:';
    $longopts = [ 'dryrun', 'stats', 'archive', 'type=', 'log-level=', 'max=' ];
    $this->optlist = civicrm_script_init($shortopts, $longopts);

    if ($this->optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--stats] [--archive] [--type TYPE] [--log-level LEVEL] [--max MAX_ENTRIES]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if (isset($this->optlist['dryrun'])) {
        $this->dry = true;
        bbscript_log(LL::NOTICE, 'Dryrun option is set. No changes will be made.🤞');
    }

      if (isset($this->optlist['max']) && is_numeric($this->optlist['max'])) {
          $this->max = $this->optlist['max'];
          bbscript_log(LL::NOTICE, 'Max option is set. Will only process '.$this->optlist['max'].' rows.');
      }

    if (isset($this->optlist['log-level'])) {
      set_bbscript_log_level($this->optlist['log-level']);
    }

    bbscript_log(LL::INFO, 'Initiating website integration processing...');

    //get instance settings
    $bbcfg = get_bluebird_instance_config($this->optlist['site']);
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
    $limitSql = (! empty($this->max)) ? "LIMIT $this->max" : '';
    $addSql = '';

    //handle survey in special way
    if ($optlist['type'] == 'SURVEY') {
      $typeSql = "AND msg_type = 'PETITION'";
      $addSql = "AND msg_action = 'questionnaire response'";
    }

    //get all accumulator records for instance (target)
    $sql = "
        SELECT * 
        FROM {$this->intDB}.accumulator 
        WHERE target_shortname = '{$this->optlist['site']}' 
          AND (target_shortname = user_shortname OR event_type = '".WebsiteEvent::EVENT_TYPE_PROFILE."') 
          $typeSql 
          $addSql
          $limitSql";

    bbscript_log(LL::DEBUG, 'SQL query:', $sql);

    $stats = [ 'processed' => [], 'unprocessed' => [], 'error' => [], 'dryrun_skips' => [] ];

    while ($row->fetch()) {
      bbscript_log(LL::TRACE, 'fetched row:', $row);

      //if context/direct message and target != user, skip
      if ($row->target_shortname != $row->user_shortname &&
          in_array($row->msg_type, ['DIRECTMSG', 'CONTEXTMSG'])
      ) {

          $this->dryrun(function() use ($intDB, $row) {
              CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, 'other', $row, null);
          }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");

        continue;
      }

      //prep params
      $params = json_decode($row->msg_info);
      bbscript_log(LL::TRACE, 'Params after json_decode():', $params);

      $created_at = new DateTime($row->created_at);
      $created_date = $created_at->format('Y-m-d H:i:s');

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
              $this->dryrun(function() use ($intDB, $archiveTable, $row, $params) {
                  CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params, false);
              }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");
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
          $birth_date = new DateTime($row->created_at);
          $contactParams['birth_date'] = $birth_date->format('Y-m-d'); //dob comes as timestamp
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
            $this->dryrun(function() use ($intDB, $archiveTable, $row, $params) {
                CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params, false);
            }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");
        }

        continue;
      }

      //update email address
        $this->dryrun(function() use ($cid, $row) {
            CRM_NYSS_BAO_Integration_Website::updateEmail($cid, $row);
        }, "CRM_NYSS_BAO_Integration_Website::updateEmail");

      $archiveTable = $activity_data = '';
      $skipActivityLog = false;

      bbscript_log(LL::DEBUG, "Processing message of type [{$row->msg_type}]");

      switch ($row->msg_type) {
        case 'BILL':
            $result = $this->dryrun(function() use ($cid, $row, $params) {
                return CRM_NYSS_BAO_Integration_Website::processBill($cid, $row->event_action, $params);
            }, "CRM_NYSS_BAO_Integration_Website::processBill");
          //$result = CRM_NYSS_BAO_Integration_Website::processBill($cid, $row->event_action, $params);
          $activity_type = 'Bill';
          $billName = CRM_NYSS_BAO_Integration_Website::buildBillName($params);
          $activity_details = "{$row->msg_action} :: {$billName}";
          break;

        case 'ISSUE':
          $result = $this->dryrun(function() use ($cid, $row, $params) {
              return CRM_NYSS_BAO_Integration_Website::processIssue($cid, $row->event_action, $params);
          }, "CRM_NYSS_BAO_Integration_Website::processIssue");
          $activity_type = 'Issue';
          $activity_details = "{$row->msg_action} :: {$params->issue_name}";
          break;

        case 'COMMITTEE':
          $result = $this->dryrun(function() use ($cid, $row, $params) {
              return CRM_NYSS_BAO_Integration_Website::processCommittee($cid, $row->event_action, $params);
          }, "CRM_NYSS_BAO_Integration_Website::processCommittee");
          $activity_type = 'Committee';
          $activity_details = "{$row->msg_action} :: {$params->committee_name}";
          break;

        case 'DIRECTMSG':
        $result = $this->dryrun(function() use ($cid, $row, $params) {
            return CRM_NYSS_BAO_Integration_Website::processCommunication($cid, $row->event_action, $params,
                                                                          $row->event_type, $row->created_at);
        }, "CRM_NYSS_BAO_Integration_Website::processCommunication");
          $activity_type = 'Direct Message';
          $activity_details = ($row->subject) ? $row->subject : '';
          $activity_data = json_encode(['note_id' => $result['id']]);
          break;

        case 'CONTEXTMSG':
            $result = $this->dryrun(function() use ($cid, $row, $params) {
                return CRM_NYSS_BAO_Integration_Website::processCommunication($cid, $row->event_action, $params,
                                                                              $row->event_type, $row->created_at);
            }, "CRM_NYSS_BAO_Integration_Website::processCommunication");
          $activity_type = 'Context Message';
          $activity_details = ($row->subject) ? $row->subject : '';
          $activity_data = json_encode(['note_id' => $result['id']]);
          break;

        case 'PETITION':
          if ($row->msg_action == 'questionnaire response') {
              $result = $this->dryrun(function() use ($cid, $row, $params) {
                  return CRM_NYSS_BAO_Integration_Website::processSurvey($cid, $row->event_action, $params);
              }, "CRM_NYSS_BAO_Integration_Website::processSurvey");
            $activity_type = 'Survey';
            $activity_details = "survey :: {$params->form_title}";
            $archiveTable = 'survey';
          }
          else {
              $result = $this->dryrun(function() use ($cid, $row, $params) {
                  return CRM_NYSS_BAO_Integration_Website::processPetition($cid, $row->event_action, $params);
              }, "CRM_NYSS_BAO_Integration_Website::processPetition");
            $activity_type = 'Petition';
            $tagName = CRM_NYSS_BAO_Integration_Website::getTagName($params, 'petition_name');
            $activity_details = "{$row->msg_action} :: {$tagName}";
          }
          break;

        /*case 'SURVEY':
          $result = CRM_NYSS_BAO_Integration_Website::processSurvey($cid, $row->msg_action, $params);
          break;*/

        case 'ACCOUNT':
            $result = $this->dryrun(function() use ($cid, $row, $params, $created_date) {
                return CRM_NYSS_BAO_Integration_Website::processAccount($cid, $row->event_action, $params, $created_date);
            }, "CRM_NYSS_BAO_Integration_Website::processAccount");
          $activity_type = 'Account';
          $activity_details = "{$row->msg_action}";

          if ($row->msg_action == 'account created') {
              $result = $this->dryrun(function() use ($cid, $row, $params) {
                  return CRM_NYSS_BAO_Integration_Website::processProfile($cid, 'account edited', $params, $row);
              }, "CRM_NYSS_BAO_Integration_Website::processProfile");
          }

          break;

        case 'PROFILE':
            $result = $this->dryrun(function() use ($cid, $row, $params) {
                return CRM_NYSS_BAO_Integration_Website::processProfile($cid, $row->event_action, $params, $row);
            }, "CRM_NYSS_BAO_Integration_Website::processProfile");
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
          $archiveTable = (!empty($archiveTable)) ? $archiveTable : strtolower($row->msg_type);
          bbscript_log(LL::DEBUG, 'Archiving matched/created record to $archiveTable and archive_error table');
            $this->dryrun(function() use ($intDB, $row, $archiveTable, $params) {
                CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params, false);
        if ($this->>optlist['archive']) {
            }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");

        }
      } elseif ($this->dry) {
          $stats['dryrun_skips'][] = $row->id;
      }
      else {
        $stats['processed'][] = $row->id;

        //store activity log record
        if (!$skipActivityLog) {
          bbscript_log(LL::DEBUG, "Storing activity log record; cid=$cid; type=$activity_type");
            $this->dryrun(function() use ($cid, $activity_type, $created_date, $activity_details, $activity_data) {
                CRM_NYSS_BAO_Integration_Website::storeActivityLog($cid, $activity_type, $created_date, $activity_details, $activity_data);
            }, "CRM_NYSS_BAO_Integration_Website::storeActivityLog");
        }

        //archive rows by ID
          $archiveTable = (!empty($archiveTable)) ? $archiveTable : strtolower($row->msg_type);
          bbscript_log(LL::DEBUG, 'Archiving matched/created record to $archiveTable table');
            $this->dryrun(function() use ($intDB, $archiveTable, $row, $params) {
                CRM_NYSS_BAO_Integration_Website::archiveRecord($intDB, $archiveTable, $row, $params);
        if ($this->>optlist['archive']) {
            }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");
        }
      }
    }

    //report stats
    $counts = [
      'processed' => count($stats['processed']),
      'unprocessed' => count($stats['unprocessed']),
      'dryrun_skips' => count($stats['dryrun_skips']),
      'error' => count($stats['error'])
    ];

    bbscript_log(LL::NOTICE, "Processing stats:", $counts);

    if ($this->optlist['stats']) {
      bbscript_log(LL::NOTICE, "\nProcessing details:");
      bbscript_log(LL::NOTICE, "Processed:", $stats['processed']);
      bbscript_log(LL::NOTICE, "Unprocessed:", $stats['unprocessed']);
      bbscript_log(LL::NOTICE, "Dry Run Skips:", $stats['dryrun_skips']);
      bbscript_log(LL::NOTICE, "Errors:", $stats['error']);
    }
  }//run

    /**
     * When dryrun mode is activated, prevents changes to stored data, and just logs the intended action instead.
     * @param callable $callback a callable block of code that will lead to data changes
     * @param string $desc description of the callable code block for the log message
     * @return array
     */
    function dryrun (callable $callback, string $desc) {
      if ($this->dry) {
          bbscript_log(LL::NOTICE, "Dryrun. Skipping:" . $desc);
          return ['dryrun' => true];
      } else {
          return $callback();
      }
    }

}//end class

//run the script
$script = new CRM_Integration_Process();
$script->run();
