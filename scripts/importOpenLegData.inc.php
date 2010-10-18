<?php
// $Id: field_file.inc,v 1.33 2009/04/28 04:03:24 quicksketch Exp $

/**
 * @file
 * Common functionality for querying the OpenLeg API.
 */

$script_dir = dirname(__FILE__);
include_once("$script_dir/functions.inc.php");

//define('OPENLEG_ROOT', 'http://open.nysenate.gov/legislation/api/1.0/xml/');
//define('OPENLEG_ROOT', 'http://open.nysenate.gov/legislation/search/?term=otype:action%20AND%20when:[0946684800000%20TO%201270900799000]&format=json');
//define('OPENLEG_ROOT', 'http://open.nysenate.gov/legislation/search/?term=otype:bill&searchType=bill&format=json');
define('OPENLEG_ROOT', 'http://open.nysenate.gov/legislation/search/?term=otype:bill%20AND%20modified:[1276128000000%20TO%201276689599000]&format=json');

#hard coded, represents hidden issue code tag.
define('TAG_PARENT_ID', 292);

error_reporting(E_ALL & ~E_WARNING);

try {
        $config = strToLower($argv[1]);
        $function = strToLower($argv[2]);
        $SC['dbName'] = $argv[3];

} catch (Exception $e) {
        echo "ARGUMENT ERROR";
        exit;
}

//get the config
include_once("$script_dir/config.php");

global $SC;

ob_start();

echo "requesting...\n\n<br><br>";

$masterList = array();

$batch = 1000;

//do it all in one shot
$issueCodes = array();
$done = false;
$i=1;
while (!$done) {

	//echo "pulling ".($i*$batch)." records.\n"; //$committee\n<br>";
	$json = openleg_retrieve("&pageIdx=$i&pageSize=$batch");
	if (count($json)==1) {
		$done = true;
		cLog(0, 'INFO', 'got to end of data set');
		break;
	}
print_r("\nGot " . count($json) . " results for page $i in batches of $batch");
//print_r($json);

	foreach($json as $key=>$bill) {
//print_r($bill);
//exit;
		$size = count($issueCodes);
		//$issueCodes[$i]['name'] = $bill['billId'][0]." - ".$bill['year'][0];
		//$issueCodes[$i]['description'] =   str_replace(',','',$bill['title'][0]);
                //$issueCodes[$i]['parent_id'] =  TAG_PARENT_ID;

		$summary = "";
		if (isset($bill->summary)) $summary = $bill->summary;
                $issueCodes[$size]['name'] = cleanForDb($bill->id." - ".$bill->year);
                $issueCodes[$size]['description'] =   cleanForDb($bill->title." - ".$summary);
                $issueCodes[$size]['parent_id'] =  TAG_PARENT_ID;
	}
	set_time_limit(360);
	++$i;
}

echo "\n\n<br><br>" . count($issueCodes) . " bills downloaded.";

echo "<pre>";
//print_r(count($issueCodes));
//print_r($issueCodes[count($issueCodes)-1]);
//exit;
echo "</pre>";

echo "inserting into database...\n\n<br><br>";

mysql_connect($SC['dbHost'], $SC['dbUser'],$SC['dbPassword']) or die(mysql_error());
mysql_select_db($SC['dbCiviPrefix'].$SC['dbName']) or die(mysql_error());

foreach ($issueCodes as $issueCode) {

                checkAndInsert($issueCode, null);
		checkAndInsert($issueCode, "FOR");
		checkAndInsert($issueCode, "AGAINST");
}

echo "\n\ndone...\n\n";


//***********************************************************************************

function checkAndInsert($issueCode, $postFix) {

	global $SC;

	if ($postFix != null) $issueCode['name'] = $issueCode['name'] . ' - ' . $postFix;

                $sql = "SELECT * FROM {$SC['dbCiviPrefix']}{$SC['dbName']}.civicrm_tag where name='".$issueCode['name']."'";
//print_r("\n\n".$sql."\n\n");

                $result = mysql_query($sql) or die(mysql_error());

                $row = mysql_fetch_assoc( $result );
//print_r($row);
                if (!is_array($row)) {

                        cLog(0,"INFO","found missing issueCode {$issueCode['name']}");

                        $sqlVals = "'{$issueCode['name']}','{$issueCode['description']}','{$issueCode['parent_id']}'";
                        $sql = "INSERT INTO {$SC['dbCiviPrefix']}{$SC['dbName']}.civicrm_tag(name,description,parent_id) VALUES({$sqlVals});";
                        if ($SC['noExec']) cLog(0,"INFO", $sql);
                        else {
				mysql_query($sql);
				//print "wrote to db.";
			}
                }

}

function cleanForDb($str) {


	$str=str_replace(',','',$str);
	$str=str_replace('\'','',$str);

	return $str;
}

function openleg_curl_request($url, $request_body='') {

$ch = curl_init();

 try {
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $data = curl_exec($ch);
 } catch (Exception $e) {
  print_r(curl_getinfo($ch));
 }
  curl_close($ch);
  return $data;
}

function openleg_retrieve($path, $source=OPENLEG_ROOT) {

	$json = openleg_curl_request($source . $path);

	$ret = json_decode($json);
	if ($ret) return $ret;
	else return false;
}

function openleg_retrieveOLD($path, $source=OPENLEG_ROOT) {

  $xml = openleg_curl_request($source . str_replace("%2F", "/", urlencode($path)));

  $xml = str_replace("\0","",$xml);

        $ret = simplexml_load_string($xml);
        if ($ret) return $ret;
        else return false;
}

function openleg_meeting($simplexml, $field = 'id') {
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

function openleg_attribute($simplexml, $field = 'name') {
  return (string)$simplexml->attributes()->{$field};
}

function openleg_bill($simplexml, $field = 'sponsor') {
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
