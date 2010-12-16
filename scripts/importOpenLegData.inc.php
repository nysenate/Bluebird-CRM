<?php
// $Id: field_file.inc,v 1.33 2009/04/28 04:03:24 quicksketch Exp $

/**
 * @file
 * Common functionality for querying the OpenLeg API.
 *
 * Author: Sacha Stanton, Rayogram
 * Author: Ken Zalewski, New York State Senate
 */

$script_dir = dirname(__FILE__);

define('OPENLEG_ROOT', 'http://open.nysenate.gov:8080/legislation/search/?term=otype:bill&format=json');

#hard coded, represents hidden issue code tag.
define('TAG_PARENT_ID', 292);

error_reporting(E_ALL & ~E_WARNING);

try {
  $config = strToLower($argv[1]);
  $function = strToLower($argv[2]);
  $dbBasename = $argv[3];

}
catch (Exception $e) {
  echo "ARGUMENT ERROR";
  exit;
}

//get the config
include_once("$script_dir/config.php");

$dbHost = $SC['dbHost'];
$dbUser = $SC['dbUser'];
$dbPass = $SC['dbPassword'];
$dbPrefix = $SC['dbCiviPrefix'];
$dbName = $dbPrefix.$dbBasename;

echo "Downloading OpenLeg bill data...\n\n";

$batch = 1000;
$issueCodes = array();
$done = false;
$i = 1;

// Allow 6 minutes for this script to run, then return a fatal error.
set_time_limit(360);

while (!$done) {
  $json = openleg_retrieve("&pageIdx=$i&pageSize=$batch");
  if (count($json) == 1) {
    $done = true;
    echo "Got to end of data set.\n";
    break;
  }
  echo "Got ".count($json)." results for page $i in batches of $batch\n";

  foreach ($json as $key=>$bill) {
    $title = $bill->title;
    $summary = "";
    if (isset($bill->summary)) {
      $summary = " - ".$bill->summary;
    }
    $size = count($issueCodes);
    $issueCodes[$size]['name'] = cleanForDb($bill->billno." - ".$bill->year);
    $issueCodes[$size]['description'] = cleanForDb($title.$summary);
    $issueCodes[$size]['parent_id'] = TAG_PARENT_ID;
  }
  ++$i;
}

echo "There were ".count($issueCodes)." bills downloaded.\n";

echo "\nInserting bill data as tags into database...\n\n";

$dbcon = mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
mysql_select_db($dbName, $dbcon) or die(mysql_error());

$insertCount = $skipCount = 0;

foreach ($issueCodes as $issueCode) {
  if (checkAndInsert($dbcon, $issueCode, null)) {
    $insertCount++;
    checkAndInsert($dbcon, $issueCode, "FOR");
    checkAndInsert($dbcon, $issueCode, "AGAINST");
  }
  else {
    // If the bill itself was already in the DB, then assume the FOR and
    // AGAINST versions are also in the DB.
    $skipCount++;
  }
}

echo "Done inserting bill data into database (inserted=$insertCount, skipped=$skipCount)\n\n";


//**************************************************************************


function checkAndInsert($dbcon, $issueCode, $postFix)
{
  if ($postFix != null) {
    $issueCode['name'] = $issueCode['name'].' - '.$postFix;
  }

  $sql = "SELECT * FROM civicrm_tag where name='".$issueCode['name']."'";

  $result = mysql_query($sql, $dbcon) or die(mysql_error());
  $row = mysql_fetch_assoc($result);

  if (!is_array($row)) {
    echo "Found missing issueCode: {$issueCode['name']}\n";
    $sqlVals = "'{$issueCode['name']}','{$issueCode['description']}','{$issueCode['parent_id']}'";
    $sql = "INSERT INTO civicrm_tag (name, description, parent_id) VALUES ({$sqlVals});";
    mysql_query($sql, $dbcon);
    return true;
  }
  else {
    // The issueCode was already in the DB.
    return false;
  }
} // checkAndInsert()


function cleanForDb($str)
{
  return str_replace(array(',', '\''), '', $str);
} // cleanForDb()


function openleg_curl_request($url, $request_body = '')
{
  $ch = curl_init();

  try {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
  }
  catch (Exception $e) {
    print_r(curl_getinfo($ch));
  }
  curl_close($ch);
  return $data;
} // openleg_curl_request()


function openleg_retrieve($path, $source = OPENLEG_ROOT)
{
  $json = openleg_curl_request($source.$path);
  $ret = json_decode($json);
  if ($ret) return $ret;
  else return false;
} // openleg_retrieve()


function openleg_meeting($simplexml, $field = 'id')
{
  switch ($field) {
    case 'date_time':
    case 'meetingDateTime':
      return (string)$simplexml->attributes()->meetingDateTime;
      break;
    case 'day_of_week':
    case 'meetday':
      return (string)$simplexml->attributes()->meetday;
      break;
    case 'location':
      return (string)$simplexml->attributes()->location;
      break;
    case 'committee_name':
    case 'committeeName':
      return (string)$simplexml->attributes()->committeeName;
      break;
    case 'committee_chair':
    case 'committeeChair':
      return (string)$simplexml->attributes()->committeeChair;
      break;
    case 'attendees':
      return $simplexml->attendees[0];
      break;
    case 'bills':
      return $simplexml->bills[0];
      break;
    case 'note':
    case 'notes':
      return (string)$simplexml->notes;
      break;
    case 'id':
    default:
      return (string)$simplexml->attributes()->id;
      break;
  }
}


function openleg_attribute($simplexml, $field = 'name')
{
  return (string)$simplexml->attributes()->{$field};
}


function openleg_bill($simplexml, $field = 'sponsor')
{
  switch ($field) {
    case 'year':
      return (string)$simplexml->attributes()->year;
      break;
    case 'id':
      return (string)$simplexml->attributes()->id;
      break;
    case 'current_committee':
    case 'currentCommittee':
      return (string)$simplexml->currentCommittee;
      break;
    case 'law_section':
    case 'lawSection':
      return (string)$simplexml->lawSection;
      break;
    case 'same_as':
    case 'sameAs':
      return (string)$simplexml->sameAs;
      break;
    case 'sponsor':
      return (string)$simplexml->sponsor->attributes()->fullname;
      break;
    case 'summary':
      return (string)$simplexml->summary;
      break;
    case 'title':
      return (string)$simplexml->title;
      break;
  }
}
