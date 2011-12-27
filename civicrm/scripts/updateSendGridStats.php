<?php

//Global primarily for the log_ function
global $optList;

require_once 'script_utils.php';

$prog = basename(__FILE__);

$shortOpts   = 'm:l:acosdrbn';
$longOpts    = array('maxbatch=','limit=','all','click', 'open', 'spamreport', 'delivered', 'dropped', 'bounce', 'unsubscribe');

$stdusage = civicrm_script_usage();
$scriptUsage = "[--limit|-l LIMIT=0] [--maxbatch|-m MAX_BATCH=1] [--all|-a] [--click|-c] [--open|-o] [--spamreport|-s] [--delivered|-d] [--dropped|-r] [--bounce|-b] [--unsubscribe|-n]";

if (! $optList = civicrm_script_init($shortOpts, $longOpts) ) {
  error_log("Usage: $prog  $stdusage $scriptUsage");
  exit(1);
}

//Creating the CRM_Core_Config class bootstraps the rest
require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

//Store the run parameters in a map for easy looping and clean DRY code.
//  Key is the table name of the event in the accumulator
//  Value is the function accepting ($events, $optList, $bbconfig) where
//    events is an array of at least one array of event parameters
//
// To disable an event from processing, just change the value. This should be
// configurable in the future in one of a few different ways.
$event_map = array(
    'bounce'        => 'process_bounce_events',
    'click'         => 'process_click_events',
    //'deferred'      => '', //Ignore these, don't need to record delays
    'delivered'     => 'process_sendgrid_delivered_events',
    'dropped'       => '', //TODO: this is a red flag of sorts
    'open'          => 'process_open_events',
    //'processed'     => '', //Ignore these, already have a record from our side
    'spamreport'    => 'process_spamreport_events',
    'unsubscribe'   => 'process_unsubscribe_events'
);

// Allow filtering of the events to be processed on the commandline
if(!array_get('all',$optList,FALSE)) {
    foreach($event_map as $key => $value) {
        if(!$optList[$key])
            unset($event_map[$key]);
    }
}

// Limits can be useful for putting a cap on the amount of work done in any
// one run of the cron job
$limit = array_get('limit',$optList,FALSE);

// Batches can be useful for reducing the back and forth queries performed here.
// Unless batch inserts are defined on the CiviCRM level though using batches is
// risky because in theory a script could be interrupted with no record of what
// was processed
$batch_size = array_get('maxbatch',$optList,1);

$event_types = implode(',',array_keys($event_map));
log_("[NOTICE] Running for events ($event_types) with limit of ".(int)$limit." and batchsize of $batch_size.");

$bbconfig = get_bluebird_instance_config();
$conn = get_accumulator_connection($bbconfig);

require_once 'CRM/Core/DAO.php';

$total_events = 0;
foreach($event_map as $event_type => $event_processor) {
    //Skip event types without active processors
    if($event_processor) {
        $new_events = exec_query("
            SELECT *
            FROM event
            JOIN $event_type ON event.id=$event_type.event_id
            WHERE processed=0
              AND servername='{$bbconfig['servername']}'
              AND IFNULL(queue_id,0) != 0
            ORDER BY timestamp
            ".( $limit ? " LIMIT $limit" : ''), $conn
        );
        $total_events += mysql_num_rows($new_events);

        if(mysql_num_rows($new_events) != 0)
            log_("[NOTICE]   Processing ".mysql_num_rows($new_events)." {$event_type}s.");

        $events = array();
        $in_process = true;
        while($in_process) {
            if($row = mysql_fetch_assoc($new_events)) {
                //We should always have a queue_event, but if we don't...
                if(! $queue = get_queue_event($row)) {
                    //Now what? We can't do anything useful here. Log it?
                    continue;
                }
                $events[$row['id']] = array('event'=>$row,'queue'=>$queue);

            } else
                $in_process = false;

            //When we've reached the batch limit or the end of the rows
            if(!empty($events) && (count($events) >= $batch_size || $in_process == false)) {

                //Pass in both the optList and the bbconfig just in case one
                //of the event processors needs to be configurable either on
                //an instance or runtime basis.
                echo "Processing ".count($events)." $event_type events.\n";
                call_user_func($event_processor,$events,$optList,$bbconfig);

                //Record the successful processing of the batch in the database
                //This isn't a great way to do it (what if the event processor
                //encounters an error after the first one?) but CiviCRM doesn't
                //give you a chance to recover from errors so...we'll do this.
                $processed_ids = implode(',',array_keys($events));
                if($processed_ids) {
                    exec_query("UPDATE event
                                SET processed=1, dt_processed=NOW()
                                WHERE id IN ($processed_ids)", $conn);
                }

                //Reset for the next batch
                $events = array();
            }
        }
    }
}
log_("[NOTICE] Processed $total_events events.");

function process_sendgrid_delivered_events($events, $optList, $bbconfig) {
    /* Requires the following table to be created....

    DROP TABLE IF EXISTS civicrm_mailing_event_sendgrid_delivered;
    CREATE TABLE civicrm_mailing_event_sendgrid_delivered (
        id int(10) unsigned PRIMARY KEY AUTO_INCREMENT,
        event_queue_id int(10) unsigned,
        time_stamp datetime,
        FOREIGN KEY (event_queue_id) REFERENCES civicrm_mailing_event_queue(id)
    );
    */

    require_once 'CRM/Core/DAO.php';

    $values = array();
    foreach($events as $pair)
        $values[] = "({$pair['queue']['id']},NOW())";

    CRM_Core_DAO::executeQuery("
        INSERT INTO civicrm_mailing_event_sendgrid_delivered
            (event_queue_id, time_stamp)
        VALUES ".implode(', ',$values)
    );
}

function process_open_events($events, $optList, $bbconfig) {
    require_once 'CRM/Mailing/Event/BAO/Opened.php';
    foreach($events as $pair) {
        list($event, $queue_event) = array_values($pair);
        CRM_Mailing_Event_BAO_Opened::open($queue_event['id']);
    }
}

function process_click_events($events, $optList, $bbconfig) {
    require_once 'CRM/Mailing/BAO/TrackableURL.php';
    require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';

    foreach($events as $pair) {
        list($event, $queue_event) = array_values($pair);
        // Create the new urls as we come across them since we don't use the
        // CiviCRM url-encoder
        $tracker = new CRM_Mailing_BAO_TrackableURL();
        $tracker->url = $event['url'];
        $tracker->mailing_id = $event['mailing_id'];
        if(!$tracker->find(true))
            $tracker->save();

        CRM_Mailing_Event_BAO_TrackableURLOpen::track($queue_event['id'], $tracker->id);
    }
}

function process_bounce_events($events, $optList, $bbconfig) {
    require_once 'CRM/Mailing/Event/BAO/Bounce.php';
    require_once 'CRM/Mailing/BAO/BouncePattern.php';

    //If there was a way to do this in batches it'd be awesome....
    foreach($events as $pair) {
        list($event, $queue_event) = array_values($pair);
        $params = array(
            'job_id'         => $queue_event['job_id'],
            'event_queue_id' => $queue_event['id'],
            'hash'           => $queue_event['hash']
        );

        //Use the CiviCRM pattern matchers to clean up our bounce info
        $params += CRM_Mailing_BAO_BouncePattern::match($event['reason']);

        CRM_Mailing_Event_BAO_Bounce::create($params);

        //Since we do our own bounce handling mechanism, disable sendgrid's by
        //removing the emails from their internal bounce list.
        remove_email_from_sendgrid_list($event['email'],'bounces',$bbconfig);
    }
}

function process_unsubscribe_events($events, $optList, $bbconfig, $list='unsubscribes') {
    require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';

    foreach($events as $pair) {
        list($event, $queue_event) = array_values($pair);
        $unsubs = CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_domain(
            $queue_event['job_id'],
            $queue_event['id'],
            $queue_event['hash']
        );

        if(!$unsubs) {
            log_("[ERROR] Unsubscribe failed processing event_id {$event['id']}");
        } else {
            remove_email_from_sendgrid_list($event['email'],$list,$bbconfig);
        }
    }
}


function process_spamreport_events($events, $optList, $bbconfig) {
    // Currently just a register as an unsubscribe event.
    // TODO: we need to come up with a way to record the differences in
    //       origin since spamreporting and unsubscribing are really quite
    //       a bit different.
    process_unsubscribe_events($events, $optList, $bbconfig, 'spamreports');
}

function get_queue_event($event) {
    require_once 'CRM/Core/DAO.php';

    $result = CRM_Core_DAO::executeQuery("
        SELECT queue.*
        FROM civicrm_mailing_event_queue as queue
        WHERE queue.id={$event['queue_id']}
    ");

    return ($result && $result->fetch()) ? (array) $result : null;
}

function get_accumulator_connection($bbconfig) {
    $user = array_get('accumulator.user',$bbconfig);
    $pass = array_get('accumulator.pass',$bbconfig);
    $name = array_get('accumulator.name',$bbconfig);
    $host = array_get('accumulator.host',$bbconfig);

    if(!$user || !$pass || !$name || !$host) {
        log_("[ERROR] Accumulator configuration parameters missing. accumulator.user+pass+home+host required");
        exit(1);
    }

    $conn = mysql_connect($host,$user,$pass);
    if($conn === FALSE) {
        log_("[ERROR] Could not connect to mysql://$user:$pass@$host: ".mysql_error());
        exit(1);
    }

    if( !mysql_select_db($name,$conn) ) {
        log_("[ERROR] Could not use '$name': ".mysql_error($conn));
        exit(1);
    }

    return $conn;
}

function remove_email_from_sendgrid_list($email, $list, $bbconfig) {
    $smtpuser = $bbconfig['smtp.user'];
    $smtppass = $bbconfig['smtp.pass'];
    $smtpsubuser = $bbconfig['smtp.subuser'];

    // Attempt to delete the specified email; Example Response
    //
    //  <result>
    //      <message>success</message>
    //  </result>
    $url = "https://sendgrid.com/apiv2/customer.$list.xml?api_user=$smtpuser&api_key=$smtppass&user=$smtpsubuser&task=delete&email=$email";
    $response = simplexml_load_file($url);
    return ($response->message == 'success');
}

/* Maybe these should get thown into the script_utils file at some point. */
function array_get($key, $source, $default='') {
    return isset($source[$key]) ? $source[$key] : $default;
}

function exec_query($sql, $conn) {
    if(($result = mysql_query($sql,$conn)) === FALSE) {
        log_("[ERROR] Accumulator query error: ".mysql_error($conn)."; while running: ".$sql);
        exit(1);
    }
    return $result;
}

function log_($message) {
    echo date('Y-m-d H:i:s')." $message\n";
}

?>
