<?php

require_once 'script_utils.php';
require_once 'accumulatorEvents.inc.php';
// Bootstrap the script from the command line
$prog = basename(__FILE__);
$shortOpts   = 'm:l:acosdrbnfp';
$longOpts    = array('maxbatch=','limit=','all','click', 'open', 'spamreport', 'delivered', 'dropped', 'bounce', 'unsubscribe', 'deferred', 'processed');
$stdusage = civicrm_script_usage();
$scriptUsage = "[--limit|-l LIMIT=0] [--maxbatch|-m MAX_BATCH=1] [--all|-a] [--click|-c] [--open|-o] [--spamreport|-s] [--delivered|-d] [--dropped|-r] [--bounce|-b] [--unsubscribe|-n] [--deferred|-f] [--processed|-p]";
if (! $optList = civicrm_script_init($shortOpts, $longOpts) ) {
  error_log("Usage: $prog  $stdusage $scriptUsage");
  exit(1);
}

// Creating the CRM_Core_Config class bootstraps the rest
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config = CRM_Core_Config::singleton();
$bbconfig = get_bluebird_instance_config();

// Allow filtering of the events to be processed on the commandline
if (!array_get('all', $optList, FALSE)) {
    foreach ($event_map as $key => $value) {
        if (!$optList[$key]) {
            unset($event_map[$key]);
        }
    }
}

// Limits can be useful for putting a cap on the amount of work done in any
// one run of the cron job
$limit = array_get('limit', $optList, FALSE);

// Batches can be useful for reducing the back and forth queries performed here.
// Unless batch inserts are defined on the CiviCRM level though using batches is
// risky because in theory a script could be interrupted with no record of what
// was processed
$batch_size = array_get('maxbatch', $optList, 1);

// Establish a connection to the accumulator
global $conn;
$conn = get_accumulator_connection($bbconfig);

$event_types = implode(',',array_keys($event_map));

// Initialize a dict for messages
global $messages;
$messages = array();

// process all th event_types one by one
log_("Running on '{$bbconfig['servername']}' for events ($event_types) with limit of ".(int)$limit." and batchsize of $batch_size.", 'INFO' );
$total_events = 0;
$total_events_processed = 0;
foreach ($event_map as $event_type => $callback) {
    $result = exec_query("
        SELECT *
        FROM incoming
        JOIN {$event_type} USING (event_id)
        WHERE servername='{$bbconfig['servername']}'
          AND event_type='{$event_type}'
        ORDER BY dt_created ASC
        ".( $limit ? " LIMIT $limit" : ''), $conn
    );

    $event_count = mysql_num_rows($result);
    $total_events += $event_count;
    log_("Processing ".$event_count." {$event_type} events.", 'INFO');

    $batch = array();
    $errors = array();
    $in_process = true;
    while($in_process) {
        if( $row = mysql_fetch_assoc($result) ) {
            if (! $queue = get_queue_event($row)) {
                // Log this as a failure! TODO: Mark as failure for archival as well.
                log_("Queue Id {$row['queue_id']} not found in {$bbconfig['servername']}",'ERROR');
                $errors[$row['event_id']] = array($row, array());
                continue;
            }
            $batch[$row['event_id']] = array('event'=>$row, 'queue'=>$queue);
        } else {
            $in_process = false;
        }

        //When we've reached the batch limit or the end of the rows
        if ((count($batch) >= $batch_size || $in_process == false)) {
            list($archived, $skipped, $failed) = array(array(), array(), array());

            // Pass in both the optList and the bbconfig just in case one
            // of the event processors needs to be configurable either on
            // an instance or runtime basis.
            // Record the successful processing of the batch in the database
            // This isn't a great way to do it (what if the event processor
            // encounters an error after the first one?) but CiviCRM doesn't
            // give you a chance to recover from errors so...we'll do this.
            if(!empty($batch)) {
                list($archived, $skipped, $failed) = call_user_func($callback, $batch, $optList, $bbconfig);
            }
            $failed += $errors;

            if (!empty($failed) && count($failed)) {
                log_(count($failed)." events failed processing.", 'ERROR');
                archive_events($failed, "FAILED", $optList, $bbconfig);
            }

            if (count($archived)) {
                log_(count($archived)." events were archived.", "INFO");
                archive_events($archived, "ARCHIVED", $optList, $bbconfig);
            }

            if (count($skipped)) {
                log_(count($skipped)." events were skipped.", 'INFO');
                archive_events($skipped, "SKIPPED", $optList, $bbconfig);
            }

            //Reset for the next batch
            $batch = array();
            $errors = array();
        }

    }
}
log_("Processed $total_events events.", 'INFO');

$result = exec_query("SELECT * FROM incoming WHERE IFNULL(servername, '')=''", $conn);
archive_orphaned_events($result, 'no servername');

function process_delivered_events($events, $opts, $bbcfg) {
    /* Requires the following table to be created....

    DROP TABLE IF EXISTS civicrm_mailing_event_sendgrid_delivered;
    CREATE TABLE civicrm_mailing_event_sendgrid_delivered (
        id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
        event_queue_id int(10) unsigned,
        time_stamp datetime,
        FOREIGN KEY (event_queue_id) REFERENCES civicrm_mailing_event_queue(id)
    );
    */

    $values = array();
    foreach ($events as $event_id => $pair) {
        list($event, $queue_event) = array_values($pair);
        $values[] = "({$queue_event['id']},NOW())";
    }

    if (!empty($values)) {
        CRM_Core_DAO::executeQuery("
            INSERT INTO civicrm_mailing_event_sendgrid_delivered
                (event_queue_id, time_stamp)
            VALUES ".implode(', ',$values)
        );
    }

    // Successful insert for all of them (or die!). SQL keeps consistency for us
    return array($events, array(), array());
}


function process_open_events($events, $opts, $bbcfg) {
    require_once 'CRM/Mailing/Event/BAO/Opened.php';
    $successes = array();
    $errors = array();
    foreach ($events as $event_id => $pair) {
        list($event, $queue_event) = array_values($pair);
        if ( CRM_Mailing_Event_BAO_Opened::open($queue_event['id']) ) {
           $successes[$event_id] = $pair;
        }
        else {
            $errors[$event_id] = $pair;
            log_("Failed to process open event id '$event_id'", 'ERROR');
        }
    }
    return array($successes, array(), $errors);
}

function process_deferred_events($events, $opts, $bbcfg) {
    return array(array(), $events, array());
}

function process_processed_events($events, $opts, $bbcfg) {
    return array(array(), $events, array());
}

function process_click_events($events, $opts, $bbcfg) {
    require_once 'CRM/Mailing/BAO/TrackableURL.php';
    require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';
    $successes = array();
    foreach ($events as $event_id => $pair) {
        list($event, $queue_event) = array_values($pair);
        // Create the new urls as we come across them since we don't use the
        // CiviCRM url-encoder
        $tracker = new CRM_Mailing_BAO_TrackableURL();
        $tracker->url = $event['url'];
        $tracker->mailing_id = $event['mailing_id'];
        if (!$tracker->find(true))
            $tracker->save();

        CRM_Mailing_Event_BAO_TrackableURLOpen::track($queue_event['id'], $tracker->id);
    }
    //Couldn't figure out how to tell if this failed or not, assume success for all
    return array($events, array(), array());
}


function process_bounce_events($events, $opts, $bbcfg) {
    require_once 'CRM/Mailing/Event/BAO/Bounce.php';
    require_once 'CRM/Mailing/BAO/BouncePattern.php';

    $errors = array();
    $successes = array();
    //If there was a way to do this in batches it'd be awesome....
    foreach ($events as $event_id => $pair) {
        list($event, $queue_event) = array_values($pair);
        $params = array(
            'job_id'         => $queue_event['job_id'],
            'event_queue_id' => $queue_event['id'],
            'hash'           => $queue_event['hash']
        );

        //Use the CiviCRM pattern matchers to clean up our bounce info
        $params += CRM_Mailing_BAO_BouncePattern::match($event['reason']);

        if ( CRM_Mailing_Event_BAO_Bounce::create($params) )
            $successes[$event_id] = $pair;
        else {
            $errors[$event_id] = $pair;
            log_("Failed to process bounce event id '$event_id'", 'ERROR');
        }
    }
    return array($successes, array(), $errors);
}


function process_unsubscribe_events($events, $opts, $bbcfg) {
    require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';

    $errors = array();
    $successes = array();
    foreach ($events as $event_id => $pair) {
        list($event, $queue_event) = array_values($pair);
        $unsubs = CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_domain(
                                                 $queue_event['job_id'],
                                                 $queue_event['id'],
                                                 $queue_event['hash']
                  );

        if ($unsubs)
            $successes[$event_id] = $pair;
        else {
            $errors[$event_id] = $pair;
            log_("Failed to process unsubscribe/spamreport event id $event_id", 'ERROR');
        }
    }
    return array($successes, array(), $errors);
}


function process_spamreport_events($events, $opts, $bbcfg) {
    // Currently just a register as an unsubscribe event.
    // TODO: we need to come up with a way to record the differences in
    //       origin since spamreporting and unsubscribing are really quite
    //       a bit different.
    return process_unsubscribe_events($events, $opts, $bbcfg);
}


function process_dropped_events($events, $opts, $bbcfg) {
    $errors = array();
    $spam_events = array();
    $bounce_events = array();
    $unsubscribed_events = array();
    foreach ($events as $event_id => $pair) {
        list($event, $queue_event) = array_values($pair);
        switch ($event['reason']) {
            // TODO: I just made this up, I don't know what it would actually come through as
            case 'Unsubscribed Address':
                $unsubscribed_events[$event_id] = array($event, $queue_event);
                break;
            case 'Spam Reporting Address':
                $spam_events[$event_id] = array($event, $queue_event);
                break;
            case 'Invalid':
                $event['reason'] = 'Bad Destination';
                $bounce_events[$event_id] = array($event, $queue_event);
                break;
            case 'Bounced Address':
                $result = exec_query("
                        SELECT reason
                        FROM bounce JOIN event ON event.id=bounce.event_id
                        WHERE event_id < $event_id
                          AND email='{$event['email']}'
                          AND servername='{$bbcfg['servername']}'
                        ORDER BY event_id DESC
                        LIMIT 1",$GLOBALS['conn']);

                if ( $row = mysql_fetch_assoc($result) )
                    $event['reason'] = $row['reason'];
                else //The database must have been reset, leave the reason blank
                    $event['reason'] = '';

                $bounce_events[$event_id] = array($event, $queue_event);
                break;
            default:
                $errors[$event_id] = $event;
                log_("Unknown dropped reason '{$event['reason']}' encountered on event {$event['id']}",'ERROR');
        }
    }

    list($archive1, $skip1, $error1) = process_unsubscribe_events($unsubscribed_events, $opts, $bbcfg);
    list($archive2, $skip2, $error2) = process_spamreport_events($spam_events, $opts, $bbcfg);
    list($archive3, $skip3, $error3) = process_bounce_events($bounce_events, $opts, $bbcfg);
    return array($archive1+$archive2+$archive3, $skip1+$skip2+$skip3, $error1+$error2+$error3);
}


function get_queue_event($event) {
    $result = CRM_Core_DAO::executeQuery("
        SELECT queue.*
        FROM civicrm_mailing_event_queue as queue
        WHERE queue.id={$event['queue_id']}
    ");

    return ($result && $result->fetch()) ? (array) $result : null;
}



?>
