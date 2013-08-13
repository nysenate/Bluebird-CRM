#!/usr/bin/php
<?php

require_once realpath(dirname(__FILE__).'/../script_utils.php');
# Some packages required for command line parsing
add_packages_to_include_path();

require_once realpath(dirname(__FILE__).'/../bluebird_config.php');
require_once 'Spreadsheet/Excel/Reader.php';
require_once 'utils.php';

$config = get_bluebird_config();

$conn = get_connection($config['globals']);

$files = scandir($argv[1]);

foreach ($files as $file) {
  if ($file[0] == '.') {
    continue;
  }

  $list = str_replace('_', '-', substr($file,9,-4));
  $reader = new Spreadsheet_Excel_Reader();
  $reader->setUTFEncoder('iconv');
  $reader->setOutputEncoding('UTF-8');
  $reader->read($argv[1].'/'.$file);
  $data = $reader->sheets[0];

  echo "Importing $list\n";
  for ($i = 5; $i <= $data['numRows']; $i++) {
    save_record($data['cells'][$i], $list, $conn);
  }
}


function array_get($array, $key, $default='')
{
  if (isset($array[$key])) {
    return $array[$key];
  }
  else {
    return $default;
  }
} // array_get()


function get_date($excel_time)
{
  // Sometimes the date comes through as poorly formatted date string
  // Parse it and convert it to a timestamp
  if (!is_numeric($excel_time)) {
    $t = strptime($excel_time, "%m/%d/%Y %H:%M");

    // Some bronto files use different date formats...WHY!?!?!?
    if (!$t) {
      $t = strptime($excel_time, "%d/%m/%Y %H:%M");
    }
    $excel_time = mktime($t['tm_hour'],$t['tm_min'],$t['tm_sec'],$t['tm_mon']+1,$t['tm_mday'],$t['tm_year']+1900);

  // Other times it comes through as a fractional count of days since 1900
  // including fractional leap days + 1 day because they miscounted.
  }
  else {
    $excel_time = $excel_time*24*60*60-2209143600;
  }

  return date('Y-m-d H:i:s',$excel_time);
} // get_date()


function save_record($record, $list, $conn)
{
  $email = mysql_real_escape_string(array_get($record, 1), $conn);
  $status = mysql_real_escape_string(array_get($record, 2), $conn);
  $dt_created = mysql_real_escape_string(get_date(array_get($record, 3)), $conn);
  $dt_modified = mysql_real_escape_string(get_date(array_get($record, 4)), $conn);
  $first_name = mysql_real_escape_string(array_get($record, 11), $conn);
  $last_name = mysql_real_escape_string(array_get($record, 12), $conn);
  $address1 = mysql_real_escape_string(array_get($record, 13), $conn);
  $address2 = mysql_real_escape_string(array_get($record, 14), $conn);
  $city = mysql_real_escape_string(array_get($record, 15), $conn);
  $state = mysql_real_escape_string(array_get($record, 16), $conn);
  $phone = mysql_real_escape_string(array_get($record, 17), $conn);
  $zip = mysql_real_escape_string(array_get($record, 18), $conn);

  // Create a new person
  $sql = "INSERT INTO person (first_name, last_name, address1, address2,
                              city, state, zip, phone, email, status,
                              created, modified, bronto)
          VALUES ('$first_name', '$last_name', '$address1', '$address2',
                  '$city', '$state', '$zip', '$phone', '$email', '$status',
                  '$dt_created', '$dt_modified', 1)";

  if (!$result = mysql_query($sql, $conn)) {
    die($sql."\n".mysql_error()."\n");
  }

  // Add the new signup
  $person_id = mysql_insert_id();
  $list_id = get_or_create_list($list, $conn);
  $sql = "INSERT IGNORE INTO signup (list_id,person_id)
          VALUES ($list_id, $person_id)";

  if (!$result = mysql_query($sql,$conn)) {
    die(mysql_error($conn)."\n".$sql);
  }

  $issues = array();
  foreach (explode('|', array_get($record, 19)) as $issue) {
    if (!$issue) {
      continue;
    }

    $issue = mysql_real_escape_string(trim($issue));
    $issue_id = get_or_create_issue($issue, $conn);

    $sql = "INSERT IGNORE INTO subscription (person_id, issue_id)
            VALUES ($person_id, $issue_id)";
    if (!$result = mysql_query($sql, $conn)) {
      die(mysql_error($conn)."\n".$sql);
    }
  }
} // save_record()

?>
