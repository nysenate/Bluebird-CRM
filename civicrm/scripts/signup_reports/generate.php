#!/usr/bin/php
<?php

$prog = basename(__FILE__);
$this_dir = dirname(__FILE__);
require_once realpath("$this_dir/../script_utils.php");
require_once realpath("$this_dir/../bluebird_config.php");
require_once 'utils.php';

// Process the command line arguments
$shortopts = 'hS:d:n';
$longopts = array('help', 'site=', 'date=', 'dryrun');
$stdusage = civicrm_script_usage();
$usage = "[--help|-h] [--date|-d FORMATTED_DATE] [--dryrun|-n]";
$optList = civicrm_script_init($shortopts, $longopts);
if ($optList === null) {
  echo "Usage: $prog  $stdusage  $usage\n";
  exit(1);
}

// Load the config file
$config = get_bluebird_instance_config($optList['site']);
if ($config == null) {
  die("Unable to continue without a valid configuration.\n");
}

CRM_Core_Config::singleton();

// Retrieve and process the records
$conn = get_connection($config);
$get_bronto = ($optList['date'] == 'bronto') ? true : false;
$result = get_signups($config['district'], $get_bronto, $conn);
$header = get_header($result);
list($nysenate_records, $nysenate_emails, $list_totals) = process_records($result, $config['district']);

// Create the report
$tdate = (isset($optList['date'])) ? $optList['date'] : null;
$filename = get_report_path($config, $tdate);
create_report($filename, $header, $nysenate_records, $list_totals);
echo "Created signups report as [$filename].\n";

// Mark the records as successfully processed
if ($optList['dryrun']) {
  echo "[DRYRUN] Skipping database update to mark records as reported.\n";
}
else {
  $sql = "UPDATE signup
          JOIN person ON signup.person_id=person.id
          JOIN list ON list.id=signup.list_id
          JOIN senator ON senator.district={$config['district']}
          LEFT JOIN committee ON senator.nid=committee.chair_nid
          SET reported=1, dt_reported=NOW()
          WHERE (list.id=senator.list_id OR list.id=committee.list_id OR (list.title='New York Senate Updates' AND person.district=senator.district))
            AND signup.reported=0
            AND person.bronto=".(($get_bronto)?'1':'0');
  if (!mysql_query($sql, $conn)) {
    die(mysql_error($conn));
  }
  else {
    echo "[DEBUG] Marked records as 'reported'.\n";
  }
}



function get_signups($district, $bronto, $conn)
{
  // This massive query of doom collects people from 3 different places:
  //  * the senator's personal list:
  //
  //      list.id=senator.list_id
  //
  //  * committee lists for committies the senator is a chair of:
  //
  //      LEFT JOIN committee ON senator.nid=committee.chair_nid
  //      list.id=committee.list_id
  //
  //  * the general New York Senate Updates list when the person is in district
  //
  //      list.title='New York Senate Updates'
  //      person.district=senator.district
  //
  // Because we use reflection on the result to generate worksheet headers we have
  // custom named all of the fields and inserted a few new ones with deafult values
  // that we will override later.
  $sql = "SELECT 'FALSE' AS `In Bluebird`,
           person.first_name AS `First Name`,
           person.last_name AS `Last Name`,
           person.email AS `Email Address`,
           person.address1 AS `Street Address`,
           person.address2 AS `Supplemental Address`,
           person.city AS `City`,
           person.state AS `State`,
           person.zip AS `Postal Code`,
           person.phone AS `Phone`,
           GROUP_CONCAT(DISTINCT issue.name ORDER BY issue.name ASC SEPARATOR '|') as `Issues`,
           list.title as `Source List`,
           person.district as `District`,
           '' AS `In District`,
           person.nid AS ID,
           person.created AS `Signup Date`
      FROM person
        JOIN signup ON signup.person_id=person.id
        JOIN list ON list.id=signup.list_id
        JOIN senator ON senator.district=$district
        LEFT JOIN committee ON senator.nid=committee.chair_nid
        LEFT JOIN subscription ON subscription.person_id=person.id
        LEFT JOIN issue ON issue.id=subscription.issue_id
      WHERE (list.id=senator.list_id OR list.id=committee.list_id OR (list.title='New York Senate Updates' AND person.district=senator.district))
        AND signup.reported=0
        AND senator.active=1
        AND committee.active=1
        AND person.bronto=".(($bronto)?'1':'0')."
      GROUP BY person.id
      ORDER BY `Signup Date` asc";

  if (!$result = mysql_query($sql, $conn)) {
    die(mysql_error($conn)."\n".$sql."\n");
  }

  return $result;
} // get_signups()



function process_records($result, $district)
{
  // Pull all the matching people into memory and clean up the fields as we go.
  // TODO: This could be a bit on he memory intensive side in the distant future
  $list_totals = array();
  $nysenate_records = array();
  $nysenate_emails = array();
  while ($row = mysql_fetch_assoc($result)) {
    // TODO: might need a more robust cleaning method here (extensions, etc)
    $row['Phone'] = str_replace('-', '', $row['Phone']);

    // Don't show the zeros for districts, that's for internal use only
    if ($row['District'] == 0) {
      $row['District'] = '';

      // If we can't distassign it, it is either a bad address or out of state
      if ($row['State'] != 'New York' && $row['State'] != 'NY') {
        $row['In District'] = 'FALSE';
      }
      else {
        $row['In District'] = 'UNKNOWN';
      }
    }
    else {
      // Out of district implicitly means that they are still in New York
      if ($row['District'] == $district) {
        $row['In District'] = 'TRUE';
      }
      else {
        $row['In District'] = 'FALSE';
      }
    }

    //Clean up the Source List, use spaces and remove the 'Signup' values
    $parts = explode('-', $row['Source List']);
    if ($parts[count($parts)-1] == 'Signups') {
      array_pop($parts);
    }
    $row['Source List'] = implode(' ', $parts);

    // Store up some totals for summary stats, include a placeholder for stats
    // on 'In Bluebird' that will be generated later.
    $source = $row['Source List'];
    if (!isset($list_totals[$source])) {
      $list_totals[$source] = array(
        'In District'=>array('Total'=>0, 'In Bluebird'=>0),
        'Out of District'=>array('Total'=>0, 'In Bluebird'=>0)
      );
    }

    if ($row['In District']=='TRUE') {
      $list_totals[$source]['In District']['Total'] += 1;
    }
    else {
      $list_totals[$source]['Out of District']['Total'] += 1;
    }

    // Store the record for later, keep an additional store for emails so that
    // we can easily figure out which emails are already in bluebird later.
    $nysenate_records[] = $row;
    $nysenate_emails[] = strtolower(trim($row['Email Address']));
  }

  // Grab all bluebird records from the instance, keep an additional store for
  // emails for matching against the nysenate emails.
  // TODO: We might want to filter based on contact status (e.g. deleted)
  $bluebird_emails = array();
  $dao = CRM_Core_DAO::executeQuery("SELECT email FROM civicrm_email");
  while ($dao->fetch()) {
    $bluebird_emails[] = strtolower(trim($dao->email));
  }

  // Mark all the nysenate signups that bluebird already has contacts for
  // Accumulate the totals for reporting later on.
  $in_bluebird = array_intersect($nysenate_emails, $bluebird_emails);
  foreach ($in_bluebird as $key => $email) {
    $record = &$nysenate_records[$key];
    $source = $record['Source List'];
    $record['In Bluebird'] = 'TRUE';
    if ($record['In District'] == 'TRUE') {
      $list_totals[$source]['In District']['In Bluebird'] += 1;
    }
    else {
      $list_totals[$source]['Out of District']['In Bluebird'] += 1;
    }
  }

  return array($nysenate_records, $nysenate_emails, $list_totals);
} // process_records()



function create_report($filepath, $header, $nysenate_records, $list_totals)
{
  require_once 'Spreadsheet/Excel/Writer.php';
  $workbook = new Spreadsheet_Excel_Writer($filepath);
  $summary_worksheet = & $workbook->addWorksheet('Summary');
  $row_num = write_row($summary_worksheet, 0, array("", "In District", "", "Out of District"));
  $row_num = write_row($summary_worksheet, $row_num, array("Source List", "Total", "Not In Bluebird", "Total", "Not In Bluebird", "Total"));

  foreach ($list_totals as $list_name => $stats) {
    $row_num = write_row($summary_worksheet, $row_num, array(
      $list_name,
      $stats['In District']['Total'],
      $stats['In District']['Total']-$stats['In District']['In Bluebird'],
      $stats['Out of District']['Total'],
      $stats['Out of District']['Total']-$stats['Out of District']['In Bluebird'],
      "=B".($row_num+1)."+D".($row_num+1),
    ));
  }

  if (count($list_totals)) {
    $summary_worksheet->write($row_num, 5, "=SUM(F3:F$row_num)");
  }

  // TODO: This could use some formatting...
  $nysenate_worksheet = & $workbook->addWorksheet('NYSenate.gov Emails');
  write_row($nysenate_worksheet, 0, $header);
  foreach ($nysenate_records as $key => $record) {
    write_row($nysenate_worksheet,$key+1, $record);
  }

  $workbook->close();
} // create_report()



function write_row($worksheet, $row_num, $data)
{
  if (!$data) {
    return false;
  }

  foreach (array_values($data) as $col => $value) {
    $worksheet->write($row_num, $col, $value);
  }

  return $row_num+1;
} // write_row()



function get_header($result)
{
  $header = array();
  $num_fields = mysql_num_fields($result);
  for ($i = 0; $i < $num_fields; $i++) {
    $header[$i] = mysql_field_name($result, $i);
  }
  return $header;
} // get_header()

?>
