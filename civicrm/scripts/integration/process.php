<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2015-04-10

use CRM_NYSS_BAO_Integration_WebsiteEvent as WebsiteEvent;
use CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent as BillEvent;
use CRM_NYSS_BAO_Integration_WebsiteEventFactory as WebsiteEventFactory;

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

require_once dirname(__FILE__) . '/../script_utils.php';

class CRM_Integration_Process {

  /**
   * @var bool specifies if script was run in dryrun mode, which means to not
   * make any changes to stored data. See doOrDry() for more info.* )
   */
  private bool $dry = false;

  /**
   * @var array list of options specified on the command line.
   */
  private array $optlist = [];

  /**
   * @var string name of accumulator database schema
   */
  private string $intDB = '';

  /**
   * @var int Max number of entries / rows to query and process during a run. 0
   *   means no limit.
   */
  private int $max = 0;

  public function run() {
    // Parse the options
    $shortopts = 'dsat:l:m:';
    $longopts = ['dryrun', 'stats', 'archive', 'type=', 'log-level=', 'max='];
    $this->optlist = civicrm_script_init($shortopts, $longopts) ?? [];

    if (sizeof($this->optlist) === 0) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--stats] [--archive] [--type TYPE] [--log-level LEVEL] [--max MAX_ENTRIES]';
      error_log("Usage: " . basename(__FILE__) . "  $stdusage  $usage\n");
      exit(1);
    }

    if (isset($this->optlist['dryrun'])) {
      $this->dry = true;
      bbscript_log(LL::NOTICE, 'Dryrun option is set. No changes will be made.🤞');
    }

    if (isset($this->optlist['max']) && is_numeric($this->optlist['max'])) {
      $this->max = $this->optlist['max'];
      bbscript_log(LL::NOTICE, 'Max option is set. Will only process ' . $this->optlist['max'] . ' rows.');
    }

    if (isset($this->optlist['log-level'])) {
      set_bbscript_log_level($this->optlist['log-level']);
    }

    bbscript_log(LL::INFO, 'Initiating website integration processing...');

    //get instance settings
    $bbcfg = get_bluebird_instance_config($this->optlist['site']);
    bbscript_log(LL::DEBUG, 'Bluebird config:', $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'] . '/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? '';
    if (!CRM_Utils_System::loadBootstrap([], false, false, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from cleanLogs.');
      return false;
    }

    bbscript_log(LL::DEBUG, 'Command line opts:', $this->optlist);

    //set website integration DB
    $this->intDB = $bbcfg['website.local.db.name'];
    $typeSql = ($this->optlist['type']) ? "AND event_type = '{$this->optlist['type']}'" : '';
    $limitSql = ($this->max > 0) ? "LIMIT $this->max" : '';
    $addSql = '';

    //handle survey in special way
    //if ($this->optlist['type'] == WebsiteEvent::EVENT_TYPE_SURVEY) {
    //  $typeSql = "AND event_type = " . WebsiteEvent::EVENT_TYPE_PETITION;
    //  $addSql = "AND event_action = " . WebsiteEvent::EVENT_ACTION_QUESTIONNAIRE_RESPONSE;
    //}

    //get all accumulator records for instance (target)
    $sql = "
        SELECT * 
        FROM {$this->intDB}.accumulator 
        WHERE target_shortname = '{$this->optlist['site']}' 
          AND user_id IS NOT NULL AND user_id != 0
          AND (target_shortname = user_shortname OR event_type = '" . WebsiteEventFactory::EVENT_TYPE_PROFILE . "') 
          $typeSql 
          $addSql
          ORDER BY id ASC
          $limitSql";

    bbscript_log(LL::DEBUG, 'SQL query:', $sql);
    $row = CRM_Core_DAO::executeQuery($sql);

    $stats = [
      'processed' => [],
      'unprocessed' => [],
      'error' => [],
      'dryrun_skips' => [],
    ];

    while ($row->fetch()) {
      bbscript_log(LL::TRACE, 'fetched row:', $row);

      // instantiate / hydrate WebsiteEventData class
      $event_data = new CRM_NYSS_BAO_Integration_WebsiteEventData($row);
      bbscript_log(LL::TRACE, 'Event Data being used:', $event_data);

      // Only continue if event type is implemented/supported. See WebsiteEvent::isSupported()
      // This "catch" was added during upgrades/fixes related to the restructuring of the
      // website accumulator data. As new events become supported on the website side,
      // we'll reimplement them here.
      if (!CRM_NYSS_BAO_Integration_WebsiteEventFactory::canCreate($event_data)) {
        bbscript_log(LL::NOTICE, "Skipping unsupported event type [{$event_data->getEventType()}]");
        continue;
      }

      // instantiate WebsiteEvent object and hydrate with WebsiteEventData object
      try {
        $web_event = CRM_NYSS_BAO_Integration_WebsiteEventFactory::create($event_data);
        // disable permission checking on api since there's no current user when run from the command line
        $web_event->setCiviPermissionCheck(false);
        bbscript_log(LL::DEBUG, 'Event Object Created:', $web_event->getEventDescription());
      }
      catch (Exception $e) {
        //echo $e->getTraceAsString();
        bbscript_log(LL::TRACE, 'Stack Trace:', $e->getTraceAsString());
        bbscript_log(LL::DEBUG, 'Exception: ' . $e->getMessage());
        bbscript_log(LL::NOTICE, 'Failed to instantiate event object for ' . $event_data->getWebUserId() . ':', $e->getMessage());
        continue; // Move to the next record / event
      }

      /*
       * Direct and Context Message events are currently not being sent from the website
       * and should be skipped in the condition above
       * commenting out for future reference
       *
      //if context/direct message and target != user, skip
      if ($row->target_shortname != $row->user_shortname &&
          in_array($row->event_type, [WebsiteEvent::EVENT_TYPE_DIRECTMSG, WebsiteEvent::EVENT_TYPE_CONTEXTMSG])
      ) {

          $this->(function() use ($this, $row) {
              CRM_NYSS_BAO_Integration_Website::archiveRecord($this->intDB, 'other', $row, null);
          }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");

          continue;
      }
      */

      //prep params
      //$params = json_decode($row->event_data);

      //$created_at = new DateTime($row->created_at);
      //$created_date = $created_at->format('Y-m-d H:i:s');

      // $contactParams['birth_date'] = '';
      // if (!empty($row->dob)) {
      //     $birth_date = new DateTime($row->created_at);
      //     $event_data->setDob($birth_date->format('Y-m-d')); //dob comes as timestamp
      // }


      //check contact/user
      $cid = NULL;
      if (!empty($event_data->getWebUserId())) {
        bbscript_log(LL::TRACE, 'calling getContactId(' . $event_data->getWebUserId() . ')');
        $cid = CRM_NYSS_BAO_Integration_Website::getContactId($event_data->getWebUserId());
      } else {
        // No Web User ID, then don't process row
        continue;
      }

      // Did not find user based on web user id. Try to match by name / contact info
      if (!$cid) {
        bbscript_log(LL::DEBUG, 'Contact with web_user_id=' . $event_data->getWebUserId() . ' was not found; attempting match');

        $contactParams = $event_data->getContactParams();

        if (empty($contactParams)) {
          bbscript_log(LL::DEBUG, 'Unable to create user; not enough data provided.', $row);
          $this->archiveError($web_event, $row, "unmatched / uncreated contact");
          continue;
        }

        bbscript_log(LL::TRACE, 'calling matchContact() with:', $contactParams);
        $cid = CRM_NYSS_BAO_Integration_Website::matchContact($contactParams);
        bbscript_log(LL::DEBUG, "matched contact $cid");
      }

      // Couldn't find contact by contact id nor matching info. Archive the record and move to the next.
      if (!$cid) {
        bbscript_log(LL::DEBUG, 'Failed to match or create contact', $contactParams);
        $stats['error'][] = [
          'is_error' => 1,
          'error_message' => 'Unable to match or create contact',
          'params' => $contactParams,
        ];

        //archive row with null date (null date??? I don't see that option -- question from nate)
        $this->archiveError($web_event, $row, "Non-matched contact record");
        continue;
      }

      // create email address if it doesn't already exist
      try {
        $this->doOrDry(function() use ($cid, $event_data) {
          CRM_NYSS_BAO_Integration_Website::createContactEmail($cid, $event_data->getEmail());
        }, "CRM_NYSS_BAO_Integration_Website::updateEmail");
      }
      catch (Exception $e) {
        bbscript_log(LL::WARN, "An unexpected error occurred while updating ");
        CRM_NYSS_Errorhandler_BAO::notifySlack('Update Contact Email Failed:' . var_export($row, true));
        CRM_NYSS_Errorhandler_BAO::notifyEmail('Update Contact Email Failed:' . var_export($row, true), 'Website Event Processing Error');
        // We'll report the error, keep moving forward, and try to process the event anyway
      }

      // Why is this here? Was there supposed to be a command line option? Did I erase something that I should not have?
      // removing because I don't see how it might ever be true
      // $skipActivityLog = false;

      bbscript_log(LL::DEBUG, "Processing message of type [{$row->event_type}]");

      // problem here because bill name is likely used in getEventDescription() ???
      try {
        $result = $this->doOrDry(function() use ($web_event, $cid) {
          return $web_event->process($cid);
        }, $web_event->getEventDescription() . "::process()");

        $activity_type = $web_event->getEventDescription();
        $activity_details = $web_event->getEventDetails();
        $activity_data = $web_event->getActivityData();

        // dry-run mode is activated. Accommodate that in the report.
        if ($this->dry) {
          $stats['dryrun_skips'][] = $row->id;
        }
        else {
          $stats['processed'][] = $row->id;

          //store activity log record
          //if (!$skipActivityLog) {
          bbscript_log(LL::DEBUG, "Storing activity log record; cid=$cid; type=$activity_type");
          $this->doOrDry(function() use ($cid, $activity_type, $event_data, $activity_details, $activity_data) {
            CRM_NYSS_BAO_Integration_Website::storeActivityLog($cid, $activity_type, $event_data->getCreatedAt(), $activity_details, $activity_data);
          }, "CRM_NYSS_BAO_Integration_Website::storeActivityLog");
          //}

          //archive rows by ID
          $this->archiveSuccess($web_event, $row, 'Record Successfully Processed');
        }
      }
      catch (Exception $e) {
        bbscript_log(LL::ERROR, "An unexpected error occurred while processing event.");
        bbscript_log(LL::DEBUG, $e->getMessage());
        bbscript_log(LL::TRACE, $e->getTraceAsString());
        $stats['error'][] = [
          'is_error' => 1,
          'error_message' => 'Unable to process event',
          'params' => $web_event->getEventInfo(),
        ];

        $this->archiveError($web_event, $row, "Event processing error");
        CRM_NYSS_Errorhandler_BAO::notifySlack('Website Event Processing Error:' . var_export($row, true));
        CRM_NYSS_Errorhandler_BAO::notifyEmail('Website Event Processing Error:' . var_export($row, true), 'Website Event Processing Error');
        // Not sure if I should archiveError() this or leave it to process again???
      }
      /*
       * Saving Old Logic, but see above for new implementation.
       * Switch is handled in WebsiteEventFactory, which returns the appropriate object based on event type

        switch ($row->event_type) {

          case WebsiteEvent::EVENT_TYPE_DIRECTMSG:
          $result = $this->(function() use ($cid, $row, $params) {
              return CRM_NYSS_BAO_Integration_Website::processCommunication($cid, $row->event_action, $params,
                                                                            $row->event_type, $row->created_at);
          }, "CRM_NYSS_BAO_Integration_Website::processCommunication");
            $activity_type = 'Direct Message';
            $activity_details = ($row->subject) ? $row->subject : '';
            $activity_data = json_encode(['note_id' => $result['id']]);
            break;

          case WebsiteEvent::EVENT_TYPE_CONTEXTMSG:
              $result = $this->(function() use ($cid, $row, $params) {
                  return CRM_NYSS_BAO_Integration_Website::processCommunication($cid, $row->event_action, $params,
                                                                                $row->event_type, $row->created_at);
              }, "CRM_NYSS_BAO_Integration_Website::processCommunication");
            $activity_type = 'Context Message';
            $activity_details = ($row->subject) ? $row->subject : '';
            $activity_data = json_encode(['note_id' => $result['id']]);
            break;

          case WebsiteEvent::EVENT_TYPE_PETITION:
            if ($row->event_action == 'questionnaire response') {
                $result = $this->(function() use ($cid, $row, $params) {
                    return CRM_NYSS_BAO_Integration_Website::processSurvey($cid, $row->event_action, $params);
                }, "CRM_NYSS_BAO_Integration_Website::processSurvey");
              $activity_type = 'Survey';
              $activity_details = "survey :: {$params->form_title}";
              $archiveTable = 'survey';
            }
            else {
                $result = $this->(function() use ($cid, $row, $params) {
                    return CRM_NYSS_BAO_Integration_Website::processPetition($cid, $row->event_action, $params);
                }, "CRM_NYSS_BAO_Integration_Website::processPetition");
              $activity_type = 'Petition';
              $tagName = CRM_NYSS_BAO_Integration_Website::getTagName($params, 'petition_name');
              $activity_details = "{$row->event_action} :: {$tagName}";
            }
            break;

          //case 'SURVEY':
            //$result = CRM_NYSS_BAO_Integration_Website::processSurvey($cid, $row->event_action, $params);
            //break;

          case WebsiteEvent::EVENT_TYPE_ACCOUNT:
              $result = $this->doOrDry(function() use ($cid, $row, $params, $created_date) {
                  return CRM_NYSS_BAO_Integration_Website::processAccount($cid, $row->event_action, $params, $created_date);
              }, "CRM_NYSS_BAO_Integration_Website::processAccount");
            $activity_type = 'Account';
            $activity_details = "{$row->event_action}";

            if ($row->event_action == 'account created') {
                $result = $this->(function() use ($cid, $row, $params) {
                    return CRM_NYSS_BAO_Integration_Website::processProfile($cid, 'account edited', $params, $row);
                }, "CRM_NYSS_BAO_Integration_Website::processProfile");
            }

            break;

          case WebsiteEvent::EVENT_TYPE_PROFILE:
              $result = $this->(function() use ($cid, $row, $params) {
                  return CRM_NYSS_BAO_Integration_Website::processProfile($cid, $row->event_action, $params, $row);
              }, "CRM_NYSS_BAO_Integration_Website::processProfile");
            $activity_type = 'Profile';
            $activity_details = $row->event_action;
            $activity_details .= ($params->status) ? " :: {$params->status}" : '';
            break;

          default:
            $result = [
              'is_error' => 1,
              'error_message' => "Unable to process row; message type [{$row->event_type}] is unknown"
            ];
            $stats['unprocessed'][] = $row;
        }


        if ($result['is_error'] || $result == false) {
          bbscript_log(LL::ERROR, 'Unable to process row', $result);
          bbscript_log(LL::ERROR, 'Row details', $row);
          $stats['error'][] = $result;

          //archive rows by ID into archive_error table
          if ($this->>optlist['archive']) {
            bbscript_log(LL::DEBUG, 'Archiving matched/created record to '. $web_event->getArchiveTableName().' and archive_error table');
              $this->(function() use ($row, $web_event) {
                  CRM_NYSS_BAO_Integration_Website::archiveRecord($this->intDB, $web_event, $row, $web_event->getEventInfo(), false);
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
              $this->(function() use ($cid, $activity_type, $event_data, $activity_details, $activity_data) {
                  CRM_NYSS_BAO_Integration_Website::storeActivityLog($cid, $activity_type, $event_data->getCreatedAt(), $activity_details, $activity_data);
              }, "CRM_NYSS_BAO_Integration_Website::storeActivityLog");
          }

          //archive rows by ID
          if ($this->>optlist['archive']) {
            bbscript_log(LL::DEBUG, 'Archiving matched/created record to '. $web_event->getArchiveTableName() .' table');
              $this->(function() use ($web_event, $row) {
                  CRM_NYSS_BAO_Integration_Website::archiveRecord($this->intDB, $web_event, $row, $web_event->getEventInfo());
              }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");
          }
        }
        */
    }

    //report stats
    $counts = [
      'processed' => count($stats['processed']),
      'unprocessed' => count($stats['unprocessed']),
      'dryrun_skips' => count($stats['dryrun_skips']),
      'error' => count($stats['error']),
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
   * When dryrun mode is activated, prevents changes to stored data, and just
   * logs the intended action instead.
   *
   * @param callable $callback a callable block of code that will lead to data
   *   changes
   * @param string $desc description of the callable code block for the log
   *   message
   *
   * @return array
   */
  private function doOrDry(callable $callback, string $desc) {
    if ($this->dry) {
      bbscript_log(LL::NOTICE, "Dryrun. Skipping: " . $desc);
      return ['' => true];
    }
    else {
      return $callback();
    }
  }

  public function archiveError(CRM_NYSS_BAO_Integration_WebsiteEventInterface $web_event, $row, string $message = ''): void {
    $this->archive($web_event, $row, $message, false);
  }

  public function archiveSuccess(CRM_NYSS_BAO_Integration_WebsiteEventInterface $web_event, $row, string $message = ''): void {
    $this->archive($web_event, $row, $message);
  }

  public function archive(CRM_NYSS_BAO_Integration_WebsiteEventInterface $web_event, $row, string $message = '', $success = true): void {

    if (!$this->optlist['archive']) {
      return;
    } // archive flag not set... don't do it

    $table = ($success) ? 'archive' : 'archive_error';

    try {
      bbscript_log(LL::DEBUG, 'Archiving record to '.$table.' and ' . $web_event->getArchiveTableName() . ' table: ' . $message);
      $this->doOrDry(function() use ($web_event, $row, $success) {
        CRM_NYSS_BAO_Integration_Website::archiveRecord($this->intDB, $web_event, $row, $web_event->getEventInfo(), $success);
      }, "CRM_NYSS_BAO_Integration_Website::archiveRecord");
    }
    catch (Exception $e) {
      bbscript_log(LL::WARN, "An unexpected error occurred while archiving a record.");
      CRM_NYSS_Errorhandler_BAO::notifySlack('Error Archiving Website Event: ' . $e->getMessage() . "\nDATA:\n" . var_export($row, true));
      CRM_NYSS_Errorhandler_BAO::notifyEmail('Error Archiving Website Event: ' . $e->getMessage() . "\nDATA:\n" . var_export($row, true), 'Error Archiving Website Event');
      // allow processing to continue
    }
  }

}//end class

//run the script
$script = new CRM_Integration_Process();
$script->run();
