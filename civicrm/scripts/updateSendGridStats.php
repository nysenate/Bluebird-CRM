<?php

require_once 'script_utils.php';
require_once 'accumulatorEvents.inc.php';

// Map each of the nine possible events to a corresponding handler function.
// Function must accept params: ($events, $optlist, $bbconfig) where
// events is an array of at least one array of event parameters
$event_map = [
  'bounce' => 'process_bounce_events',
  'click' => 'process_click_events',
  'deferred' => 'process_deferred_events',
  'delivered' => 'process_delivered_events',
  'dropped' => 'process_dropped_events',
  'open' => 'process_open_events',
  'processed' => 'process_processed_events',
  'spamreport' => 'process_spamreport_events',
  'unsubscribe' => 'process_unsubscribe_events',
];

// Bootstrap the script from the command line
$prog = basename(__FILE__);
$shortopts = 'l:t:m:acosdrbnfp';
$longopts = ['log-level=', 'limit=', 'maxbatch=', 'all', 'click', 'open', 'spamreport', 'delivered', 'dropped', 'bounce', 'unsubscribe', 'deferred', 'processed'];
$stdusage = civicrm_script_usage();
$scriptusage = "[--log-level|-l LEVEL] [--limit|-t LIMIT=0] [--maxbatch|-m MAX_BATCH=1] [--all|-a] [--click|-c] [--open|-o] [--spamreport|-s] [--delivered|-d] [--dropped|-r] [--bounce|-b] [--unsubscribe|-n] [--deferred|-f] [--processed|-p]";

$optlist = civicrm_script_init($shortopts, $longopts);

if (!$optlist) {
  error_log("Usage: $prog  $stdusage $scriptusage");
  exit(1);
}

if (!empty($optlist['log-level'])) {
  set_bbscript_log_level($optlist['log-level']);
}

// Creating the CRM_Core_Config class bootstraps the rest
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Array.php';

$config = CRM_Core_Config::singleton();
$bbconfig = get_bluebird_instance_config();

// Allow filtering of the events to be processed on the commandline
if (CRM_Utils_Array::value('all', $optlist, false) === false) {
  foreach ($event_map as $key => $value) {
    if (!$optlist[$key]) {
      unset($event_map[$key]);
    }
  }
}

// Limits can be useful for putting a cap on the amount of work done in any
// one run of the cron job
$limit = (int)CRM_Utils_Array::value('limit', $optlist, false);

// Batches can be useful for reducing the back and forth queries performed here.
// Unless batch inserts are defined on the CiviCRM level though using batches is
// risky because in theory a script could be interrupted with no record of what
// was processed
$batch_size = (int)CRM_Utils_Array::value('maxbatch', $optlist, 1);

// Establish a connection to the accumulator
$dbcon = get_accumulator_connection($bbconfig);

$event_types = implode(',', array_keys($event_map));

// process all the event_types one by one
bbscript_log(LL::NOTICE, "Running on '{$bbconfig['servername']}' for events ($event_types) with limit of $limit and batchsize of $batch_size.");
$total_events = 0;
$total_events_processed = 0;

foreach ($event_map as $event_type => $cb_func) {
  $result = bb_mysql_query("
    SELECT *
    FROM incoming
    JOIN $event_type USING (event_id)
    WHERE servername='{$bbconfig['servername']}'
      AND event_type='$event_type'
    ORDER BY dt_created ASC
    ".($limit ? " LIMIT $limit" : ''), $dbcon, true);

  $event_count = mysqli_num_rows($result);
  $total_events += $event_count;
  bbscript_log(LL::INFO, "Processing $event_count $event_type events.");

  $batch = [];
  $errors = [];
  $in_process = true;

  while ($in_process) {
    if ($row = mysqli_fetch_assoc($result)) {
      $qid = $row['queue_id'];
      if (!($queue = get_queue_event($qid))) {
        // Log this as a failure! TODO: Mark as failure for archival as well.
        bbscript_log(LL::ERROR, "Queue Id $qid not found in {$bbconfig['servername']} (event_type: {$event_type})");
        $errors[$row['event_id']] = [$row, []];
        continue;
      }
      $batch[$row['event_id']] = ['event' => $row, 'queue' => $queue];
    }
    else {
      $in_process = false;
    }

    //When we've reached the batch limit or the end of the rows
    if ((count($batch) >= $batch_size || !$in_process)) {
      list($archived, $skipped, $failed) = [[], [], []];
      // Record the successful processing of the batch in the database
      // This isn't a great way to do it (what if the event processor
      // encounters an error after the first one?) but CiviCRM doesn't
      // give you a chance to recover from errors so...we'll do this.
      if (!empty($batch)) {
        list($archived, $skipped, $failed) = call_user_func($cb_func, $batch);
      }
      $failed += $errors;

      if (!empty($failed) && count($failed)) {
        bbscript_log(LL::ERROR, count($failed)." events failed processing.");
        archive_events($dbcon, $failed, 'FAILED', $bbconfig);
      }

      if (count($archived)) {
        bbscript_log(LL::INFO, count($archived)." events were archived.");
        archive_events($dbcon, $archived, 'ARCHIVED', $bbconfig);
      }

      if (count($skipped)) {
        bbscript_log(LL::INFO, count($skipped)." events were skipped.");
        archive_events($dbcon, $skipped, 'SKIPPED', $bbconfig);
      }

      //Reset for the next batch
      $batch = [];
      $errors = [];
    }
  }
  mysqli_free_result($result);
}

bbscript_log(LL::INFO, "Processed $total_events events.");

$result = bb_mysql_query("SELECT * FROM incoming WHERE servername is NULL OR servername = ''", $dbcon, true);
archive_orphaned_events($dbcon, $result, 'no servername', $bbconfig);
mysqli_free_result($result);
exit(0);


function process_delivered_events($events)
{
  $values = [];
  foreach ($events as $event_id => $pair) {
    list($event, $queue_event) = array_values($pair);
    $values[] = "({$queue_event['id']}, NOW())";
  }

  if (!empty($values)) {
    CRM_Core_DAO::executeQuery("
      INSERT IGNORE INTO civicrm_mailing_event_sendgrid_delivered
        (event_queue_id, time_stamp)
      VALUES ".implode(', ', $values)
    );
  }

  // Successful insert for all of them (or die!). SQL keeps consistency for us
  return [$events, [], []];
} // process_delivered_events()


function process_open_events($events)
{
  require_once 'CRM/Mailing/Event/BAO/Opened.php';
  $successes = [];
  $errors = [];
  foreach ($events as $event_id => $pair) {
    list($event, $queue_event) = array_values($pair);
    if (CRM_Mailing_Event_BAO_Opened::open($queue_event['id'])) {
       $successes[$event_id] = $pair;
    }
    else {
      $errors[$event_id] = $pair;
      bbscript_log(LL::ERROR, "Failed to process open event id '$event_id'");
    }
  }
  return [$successes, [], $errors];
} // process_open_events()


function process_deferred_events($events)
{
  return [[], $events, []];
} // process_deferred_events()


function process_processed_events($events)
{
  return [[], $events, []];
} // process_processed_events()


function process_click_events($events)
{
  require_once 'CRM/Mailing/BAO/TrackableURL.php';
  require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';
  $successes = $errors = [];
  foreach ($events as $event_id => $pair) {
    list($event, $queue_event) = array_values($pair);
    // Create the new URLs as we come across them since we don't use the
    // CiviCRM url-encoder.

    //check to see if mailing_id exists
    $mailingExists = CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_mailing WHERE id = %1", [
      1 => [$event['mailing_id'], 'Positive'],
    ]);

    if (!$mailingExists) {
      $errors[$event_id] = $pair;
      continue;
    }

    $tracker = new CRM_Mailing_BAO_TrackableURL();
    $tracker->url = $event['url'];
    $tracker->mailing_id = $event['mailing_id'];
    if (!$tracker->find(true)) {
      $tracker->save();
    }

    CRM_Mailing_Event_BAO_TrackableURLOpen::track($queue_event['id'], $tracker->id);

    $successes[$event_id] = $pair;
  }
  // Unable to determine success or failure; assume success for all.
  return [$successes, [], $errors];
} // process_click_events()


function process_bounce_events($events)
{
  require_once 'CRM/Mailing/Event/BAO/Bounce.php';
  require_once 'CRM/Mailing/BAO/BouncePattern.php';

  $errors = [];
  $successes = [];

  //If there was a way to do this in batches it'd be awesome....
  foreach ($events as $event_id => $pair) {
    list($event, $queue_event) = array_values($pair);
    $params = [
      'job_id' => $queue_event['job_id'],
      'event_queue_id' => $queue_event['id'],
      'hash' => $queue_event['hash'],
    ];

    //Use the CiviCRM pattern matchers to clean up our bounce info
    $params += CRM_Mailing_BAO_BouncePattern::match($event['reason']);

    if (CRM_Mailing_Event_BAO_Bounce::create($params)) {
      $successes[$event_id] = $pair;
    }
    else {
      $errors[$event_id] = $pair;
      bbscript_log(LL::ERROR, "Failed to process bounce event id '$event_id'");
    }
  }
  return [$successes, [], $errors];
} // process_bounce_events()


function process_unsubscribe_events($events)
{
  require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';

  $errors = [];
  $successes = [];
  foreach ($events as $event_id => $pair) {
    list($event, $queue_event) = array_values($pair);
    $unsubs = CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_domain(
      $queue_event['job_id'],
      $queue_event['id'],
      $queue_event['hash']
    );

    if ($unsubs) {
      $successes[$event_id] = $pair;
    }
    else {
      $errors[$event_id] = $pair;
      bbscript_log(LL::ERROR, "Failed to process unsubscribe/spamreport event id $event_id");
    }
  }
  return [$successes, [], $errors];
} // process_unsubscribe_events()


function process_spamreport_events($events)
{
  // Currently just a register as an unsubscribe event.
  // TODO: we need to come up with a way to record the differences in
  //       origin since spamreporting and unsubscribing are really quite
  //       a bit different.
  return process_unsubscribe_events($events);
} // process_spamreport_events()


function process_dropped_events($events)
{
  $errors = [];
  $spam_events = [];
  $bounce_events = [];
  $unsubscribed_events = [];

  foreach ($events as $event_id => $pair) {
    list($event, $queue_event) = array_values($pair);
    switch ($event['reason']) {
      case 'Unsubscribed Address':
        $unsubscribed_events[$event_id] = [$event, $queue_event];
        break;
      case 'Spam Reporting Address':
        $spam_events[$event_id] = [$event, $queue_event];
        break;
      case 'Invalid':
        $event['reason'] = 'Bad Destination';
        $bounce_events[$event_id] = [$event, $queue_event];
        break;
      case 'Bounced Address':
        $event['reason'] = 'Address '.$event['email'].' dropped due to previous bounce';
        $bounce_events[$event_id] = [$event, $queue_event];
        break;
      default:
        $errors[$event_id] = $event;
        bbscript_log(LL::ERROR, "Unknown dropped reason '{$event['reason']}' encountered on event {$event['id']}");
    }
  }

  list($archive1, $skip1, $error1) = process_unsubscribe_events($unsubscribed_events);
  list($archive2, $skip2, $error2) = process_spamreport_events($spam_events);
  list($archive3, $skip3, $error3) = process_bounce_events($bounce_events);
  return [$archive1+$archive2+$archive3, $skip1+$skip2+$skip3, $error1+$error2+$error3];
} // process_dropped_events()


function get_queue_event($queue_id)
{
  $result = CRM_Core_DAO::executeQuery(
    "SELECT * FROM civicrm_mailing_event_queue WHERE id=$queue_id");

  return ($result && $result->fetch()) ? (array) $result : null;
} // get_queue_event()
