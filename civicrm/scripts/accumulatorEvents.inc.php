<?php

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);
define('DEFAULT_LOG_LEVEL', 'TRACE');
ini_set("auto_detect_line_endings", "1");
require_once 'script_utils.php';
// Store the run parameters in a map for easy looping and clean DRY code.
// Key is the table name of the event in the accumulator
// Value is the function accepting ($events, $optList, $bbconfig) where
// events is an array of at least one array of event parameters
// To disable an event from processing, just change the value. This should be
// configurable in the future in one of a few different ways.
$event_map = array(
    'bounce' => 'process_bounce_events',
    'click' => 'process_click_events',
    'deferred' => 'process_deferred_events',
    'delivered' => 'process_delivered_events',
    'dropped' => 'process_dropped_events',
    'open' => 'process_open_events',
    'processed' => 'process_processed_events',
    'spamreport' => 'process_spamreport_events',
    'unsubscribe' => 'process_unsubscribe_events'
);

$event_types = implode(',',array_keys($event_map));


function get_accumulator_connection($bbcfg) {
    $host = array_get('accumulator.db.host', $bbcfg);
    $port = array_get('accumulator.db.port', $bbcfg);
    $name = array_get('accumulator.db.name', $bbcfg);
    $user = array_get('accumulator.db.user', $bbcfg);
    $pass = array_get('accumulator.db.pass', $bbcfg);

    if (!$host || !$name || !$user || !$pass) {
        log_("Accumulator configuration parameters missing. accumulator.{host,name,user,pass} required", 'ERROR');
        exit(1);
    }

    $full_host = ($port) ? $host.':'.$port : $host;

    $conn = mysql_connect($full_host, $user, $pass);
    if ($conn === FALSE) {
        log_("Could not connect to mysql://$user:$pass@$full_host: ".mysql_error(), 'ERROR');
        exit(1);
    }

    if (!mysql_select_db($name, $conn)) {
        log_("Could not use '$name': ".mysql_error($conn), 'ERROR');
        exit(1);
    }

    return $conn;
}

//function to get user input to continue
function getBoolInput($message)
{
    //do while check for input
    while(!isset($input) || (is_array($valid_inputs) && !in_array($input, $valid_inputs)) || ($valid_inputs == 'is_file' && !is_file($input))) { 
        print($message);
        print(' Y/N: ');
        print("");
        $input = strtolower(trim(fgets(STDIN))); 
        switch($input)
        {
            case 'y': return true; break;
            case 'n': return false; break;
            default: unset($input); break;
        }
    } 
}

/* Maybe these should get thown into the script_utils file at some point. */
function array_get($key, $source, $default='') {
    return isset($source[$key]) ? $source[$key] : $default;
}



function log_( $message, $message_level) {
    $LOG_LEVELS = array(
        "TRACE" => array(0,"\33[0;35m"),
        "DEBUG" => array(1,"\33[1;35m"),
        "INFO"  => array(2,"\33[0;33m"),
        "WARN"  => array(3,"\33[1;33m"),
        "ERROR" => array(4,"\33[0;31m"),
        "FATAL" => array(5,"\33[1;31m"),
    );
    if(!$message_level)
    {
        echo sprintf("%s\n",$message);
    }
    else
    {
        $log_level = strtoupper($message_level);
        list($log_num, $color) = $LOG_LEVELS[$log_level];
        $timestamp = date('G:i:s');
        $log_level = $color.$log_level."\33[0m";
        // Extra large padding to account for color strings!
        echo sprintf("[%s] %s %s\n",$timestamp, "[$log_level]", $message);
    }
    
}

function exec_query($sql, $conn) {
    if (($result = mysql_query($sql, $conn)) === FALSE) {
        log_("Accumulator query error: ".mysql_error($conn)."; while running: ".$sql, 'ERROR' );
        exit(1);
    }
    return $result;
}

function archive_events($events, $result, $optList, $bbconfig) {
    global $instance_id, $messages, $conn;
    $archive = array();
    $instance_id = "returnInstance('{$bbconfig['install_class']}','{$bbconfig['servername']}','{$bbconfig['shortname']}')";
    foreach ($events as $event_id => $pair) {
        list($event, $queue_event) = array_values($pair);
        $mailing_id = $event['mailing_id'];
        $category = mysql_real_escape_string($event['category'], $conn);
        $message_id = "returnMessage($instance_id, $mailing_id, '$category')";
        $archive[] = "($event_id, $message_id, {$event['job_id']}, {$event['queue_id']}, '{$event['event_type']}', '{$result}', '{$event['email']}', {$event['is_test']}, '{$event['dt_created']}', '{$event['dt_received']}', NOW())";
    }

    // Do the transaction
    exec_query("BEGIN", $conn);
    exec_query("DELETE FROM incoming WHERE event_id IN (".implode(',',array_keys($events)).");", $conn);
    exec_query("INSERT INTO archive
                    (event_id, message_id, job_id, queue_id, event_type, result, email, is_test, dt_created, dt_received, dt_processed)
                VALUES ".implode(',',$archive), $conn);
    exec_query("COMMIT", $conn);
}


function archive_orphaned_events($result, $optList)
{
    //if it's no server (from USGS) it'll send a string.
    if(!is_array($optList))
    {
        $newOptList = $optList;
        $optList = '';
        $optList['instance'] = $newOptList;
    }
    $event_count = mysql_num_rows($result);
    if($event_count != 0)
    {
        //set row to orphans, and then add an array for the results in archive_events 
        while($row = mysql_fetch_assoc($result)) 
        {
            $orphans[$row['event_id']] = array($row,array());
        }
        
        //if test flag is thrown, you can view all records to be processed
        if($optList['test'])
        {
            log_('Queued '.$event_count.' orphaned events from '.$optList['instance'].' to archive.','INFO');
            test_orphaned_events($orphans, $optList);
        }
        //moves the archive events to the table
        archive_events($orphans,'ARCHIVED', $optList, $bbconfig);
        log_('Archived '.$event_count.' orphaned events from '.$optList['instance'].'.','INFO');    
    } else {
        log_('There are no orphaned events for '.$optList['instance'].'.','ERROR');    
    }  
}
// flagged with the -t, which allows a preview of all emails that are to be archived.
function test_orphaned_events($orphans, $optList) 
{
    $limit = 20;
    $tracker = 1;
    if(getBoolInput('Show queued orphaned events?'))
    {
        //spacing the rows for command line, 9, 25, 8, 4, 4, 7, 16, set for 80x24 terminal control
        $header_row = 'event_id |      email address      | e_type |m_id|j_id| q_id  | dt_recieved';
        foreach($orphans as $forrow)
        {
            $rowparse = $forrow[0];
            //every 20, show a prompt to continue showing.
            if($tracker != 1 && $tracker%$limit == 1)
            {
                $tracker--;
                print("\n");
                log_('Showed '.$tracker.' orphaned events from '.$optList['instance'].'.','INFO');
                $tracker++;
                if(!getBoolInput('Continue?'))
                {
                    break;
                }
            }
            if($tracker%$limit == 1)
            {
               
                print($header_row . "\n");
            }
            //pads the rows for command line, 9, 25, 8, 4, 4, 7, 16
            $printed_row = str_pad($rowparse['event_id'], 9);
            $printed_row .= '|'. str_pad(substr($rowparse['email'],0,25), 25);
            $printed_row .= '|'. str_pad(substr($rowparse['event_type'],0,8), 8);
            $printed_row .= '|'. str_pad($rowparse['mailing_id'], 4);
            $printed_row .= '|'. str_pad($rowparse['job_id'], 4);
            $printed_row .= '|'. str_pad($rowparse['queue_id'], 7);
            $printed_row .= '|'. str_pad(substr($rowparse['dt_received'],2,18), 16);
            print($printed_row . "\n");
            $tracker++;
        }
    }
    //choice to continue archiving after either an escape from the queue or finished queue view
    if(!getBoolInput('Continue with archiving orphaned events from '.$optList['instance'].'?'))
    {
        log_("No events processed!",'WARN');
        die(); 
    }
}
