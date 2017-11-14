<?php

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);
ini_set('auto_detect_line_endings', true);
require_once 'script_utils.php';


function get_accumulator_connection($bbcfg)
{
  $host = CRM_Utils_Array::value('accumulator.db.host', $bbcfg);
  $port = CRM_Utils_Array::value('accumulator.db.port', $bbcfg);
  $name = CRM_Utils_Array::value('accumulator.db.name', $bbcfg);
  $user = CRM_Utils_Array::value('accumulator.db.user', $bbcfg);
  $pass = CRM_Utils_Array::value('accumulator.db.pass', $bbcfg);

  if (!$host || !$name || !$user || !$pass) {
    bbscript_log(LL::ERROR, 'Accumulator configuration parameters missing. accumulator.{host,name,user,pass} required');
    return null;
  }

  $full_host = ($port) ? $host.':'.$port : $host;

  $dbcon = mysqli_connect($full_host, $user, $pass, $name);
  if ($dbcon === false) {
    bbscript_log(LL::ERROR, "Could not connect to mysqli://$user:$pass@$full_host/$name: ".mysqli_connect_error());
    return null;
  }

  return $dbcon;
} // get_accumulator_connection()


//function to get user input to continue
function getBoolInput($message)
{
  //do while check for input
  while (!isset($input) || (is_array($valid_inputs) && !in_array($input, $valid_inputs)) || ($valid_inputs == 'is_file' && !is_file($input))) { 
    print($message);
    print(' Y/N: ');
    print("");
    $input = strtolower(trim(fgets(STDIN))); 
    switch ($input) {
      case 'y': return true; break;
      case 'n': return false; break;
      default: unset($input); break;
    }
  } 
} // getBoolInput()


function archive_events($dbcon, $events, $status, $bbcfg)
{
  $archive = array();
  $instance_id = "returnInstance('{$bbcfg['install_class']}','{$bbcfg['servername']}','{$bbcfg['shortname']}')";
  foreach ($events as $event_id => $pair) {
    list($event, $queue_event) = array_values($pair);
    $mailing_id = $event['mailing_id'];
    $category = mysqli_real_escape_string($dbcon, $event['category']);
    $message_id = "returnMessage($instance_id, $mailing_id, '$category')";
    $archive[] = "($event_id, $message_id, {$event['job_id']}, {$event['queue_id']}, '{$event['event_type']}', '$status', '{$event['email']}', {$event['is_test']}, '{$event['dt_created']}', '{$event['dt_received']}', NOW())";
  }

  // Do the transaction
  bb_mysql_query('BEGIN', $dbcon, true);
  bb_mysql_query('DELETE FROM incoming WHERE event_id IN ('.implode(',', array_keys($events)).')', $dbcon, true);
  bb_mysql_query('INSERT INTO archive
              (event_id, message_id, job_id, queue_id, event_type, result, email, is_test, dt_created, dt_received, dt_processed)
              VALUES '.implode(',', $archive), $dbcon, true);
  bb_mysql_query('COMMIT', $dbcon, true);
} // archive_events()


function archive_orphaned_events($dbcon, $result, $opts, $bbcfg)
{
  //if it's no server (from USGS) it'll send a string.
  if (!is_array($opts)) {
    $opts = array('instance' => $opts);
  }

  $event_count = mysqli_num_rows($result);
  if ($event_count != 0) {
    //set row to orphans, then add an array for the results in archive_events 
    while ($row = mysqli_fetch_assoc($result)) {
      $orphans[$row['event_id']] = array($row, array());
    }
      
    //if test flag is thrown, you can view all records to be processed
    if (isset($opts['test'])) {
      bbscript_log(LL::INFO, 'Queued '.$event_count.' orphaned events from '.$opts['instance'].' to archive.');
      test_orphaned_events($orphans, $opts);
    }

    //moves the archive events to the table
    archive_events($dbcon, $orphans, 'ARCHIVED', $bbcfg);
    bbscript_log('Archived '.$event_count.' orphaned events from '.$opts['instance']);
  }
  else {
    bbscript_log(LL::NOTICE, 'There are no orphaned events for '.$opts['instance']);
  }
} // archive_orphaned_events()


// flagged with the -t, which allows a preview of all emails that are to be archived.
function test_orphaned_events($orphans, $opts) 
{
  $limit = 20;
  $tracker = 1;
  if (getBoolInput('Show queued orphaned events?')) {
    //spacing the rows for command line, 9, 25, 8, 4, 4, 7, 16, set for 80x24 terminal control
    $header_row = 'event_id |      email address      | e_type |m_id|j_id| q_id  | dt_received';
    foreach ($orphans as $forrow) {
      $rowparse = $forrow[0];
      //every 20, show a prompt to continue showing.
      if ($tracker != 1 && $tracker % $limit == 1) {
        $tracker--;
        print("\n");
        bbscript_log(LL::INFO, 'Showed '.$tracker.' orphaned events from '.$opts['instance']);
        $tracker++;
        if (!getBoolInput('Continue?')) {
          break;
        }
      }

      if ($tracker % $limit == 1) {
        print("$header_row\n");
      }

      //pads the rows for command line, 9, 25, 8, 4, 4, 7, 16
      $printed_row = str_pad($rowparse['event_id'], 9);
      $printed_row .= '|'. str_pad(substr($rowparse['email'], 0, 25), 25);
      $printed_row .= '|'. str_pad(substr($rowparse['event_type'], 0, 8), 8);
      $printed_row .= '|'. str_pad($rowparse['mailing_id'], 4);
      $printed_row .= '|'. str_pad($rowparse['job_id'], 4);
      $printed_row .= '|'. str_pad($rowparse['queue_id'], 7);
      $printed_row .= '|'. str_pad(substr($rowparse['dt_received'], 2, 18), 16);
      print("$printed_row\n");
      $tracker++;
    }
  }

  //choice to continue archiving after either an escape from the queue or finished queue view
  if (!getBoolInput('Continue with archiving orphaned events from '.$opts['instance'].'?')) {
    bbscript_log(LL::WARN, 'No events processed!');
    die(); 
  }
} // test_orphaned_events()
