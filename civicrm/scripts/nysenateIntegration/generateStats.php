<?php

// Bootstrap the script and progress the command line arguments
require_once realpath(dirname(__FILE__).'/../script_utils.php');
add_packages_to_include_path();
$prog = basename(__FILE__);
$short_opts = 'U:P:H:N:hS:D:';
$long_opts = array('user=','pass=','host=', 'name=', 'help', 'site=', 'district=');
$usage = "--user|-U USER --pass|-P --host|-H --name|-N [--help|-h] --site|-s SITE --district|-d DISTRICT";
if(! $optList = process_cli_args($short_opts, $long_opts))
    die("$prog $usage\n");

// We don't have any way 100% sure way of correlating the two right now so require both
if(!$optList['site'] || !$optList['district']) {
    echo "Both site and district options are required.\n";
    die("$prog $usage\n");
}

if(!$optList['user'] || !$optList['pass'] || !$optList['host'] || !$optList['name']) {
    echo "Username, Password, Host, and database name for the signups database are required.\n";
    die("$prog $usage\n");
}

// Bootstrap CiviCRM so we can use the SAGE and DAO utilities
$root = dirname(dirname(dirname(dirname(__FILE__))));
$_SERVER["HTTP_HOST"] = $_SERVER['SERVER_NAME'] = $optList['site'];
require_once "$root/drupal/sites/default/civicrm.settings.php";
require_once "$root/civicrm/custom/php/CRM/Utils/SAGE.php";
require_once "CRM/Core/Config.php";
require_once "CRM/Core/DAO.php";
$config = CRM_Core_Config::singleton();

// This massive query of doom collects people from 3 different places:
//  * the senator's personal list:
//
//          list.id=senator.list_id
//
//  * committee lists for committies the senator is a chair of:
//
//          LEFT JOIN committee ON senator.nid=committee.chair_nid
//          list.id=committee.list_id
//
//  * the general New York Senate Updates list when the person is in her district
//
//          list.title='New York Senate Updates'
//          person.district=senator.district
//
// Because we use reflection on the result to generate worksheet headers we have
// custom named all of the fields and inserted a few new ones with deafult values
// that we will override later.
$sql = "SELECT 'FALSE' AS `In Bluebird`,
               list.title as `Source List`,
               person.district as `District`,
               '' AS `In District`,
               person.id AS ID,
               person.first_name AS `First Name`,
               person.last_name AS `Last Name`,
               person.address1 AS `Street Address`,
               person.address2 AS `Supplemental Address`,
               person.city AS `City`,
               person.state AS `State`,
               person.zip AS `Postal Code`,
               person.phone AS `Phone`,
               person.email AS `Email Address`,
               GROUP_CONCAT(DISTINCT issue.issue ORDER BY issue.issue ASC SEPARATOR '|') as `Issues`
        FROM person
          JOIN signup ON signup.person_id=person.id
          JOIN list ON list.id=signup.list_id
          JOIN senator ON senator.district={$optList['district']}
          LEFT JOIN committee ON senator.nid=committee.chair_nid
          LEFT JOIN issue ON issue.person_id=person.id
        WHERE (list.id=senator.list_id OR list.id=committee.list_id OR (list.title='New York Senate Updates' AND person.district=senator.district))
        GROUP BY person.id
        ORDER BY person.id";

//Connect to the signups SQL database as constructed by the signups ingest script
$conn = get_signups_connection($optList['host'],$optList['user'],$optList['pass'],$optList['name']);
if(!$result = mysql_query($sql, $conn))
    die(mysql_error($conn)."\n".$sql."\n");


// Pull all the matching people into memory and clean up the fields as we go.
// TODO: This could be a bit on the memory intensive side in the distant future
$list_totals = array();
$nysenate_records = array();
$nysenate_header = get_header($result);
while($row = mysql_fetch_assoc($result)) {

    // TODO: might need a more robust cleaning method here (extensions, etc)
    $row['Phone'] = str_replace('-','',$row['Phone']);

    // Don't show the zeros for districts, that's for internal use only
    if($row['District'] == 0) {
        $row['District'] = '';

        // If we can't distassign it, it is either a bad address or out of state
        if($row['State'] != 'New York')
            $row['In District'] = 'OUT OF STATE';
        else
            $row['In District'] = 'UNKNOWN';

    } else {

        // Out of district implicitly means that they are still in New York
        if($row['District'] == $optList['district'])
            $row['In District'] = 'TRUE';
        else
            $row['In District'] = 'OUT OF DISTRICT';

    }

    //Clean up the Source List, use spaces and remove the 'Signup' values
    $parts = explode('-',$row['Source List']);
    if($parts[count($parts)-1] == 'Signups')
        array_pop($parts);
    $row['Source List'] = implode(' ',$parts);

    // Store up some totals for summary stats, include a placeholder for stats
    // on 'In Bluebird' that will be generated later.
    $source = $row['Source List'];
    if(!isset($list_totals[$source])) {
        $list_totals[$source] = array(
            'In District'=>array('Total'=>0,'In Bluebird'=>0),
            'Out of District'=>array('Total'=>0,'In Bluebird'=>0)
        );
    }

    if($row['In District']=='TRUE') {
        $list_totals[$source]['In District']['Total'] += 1;
    } else {
        $list_totals[$source]['Out of District']['Total'] += 1;
    }


    // Store the record for later, keep an additional store for emails so that
    // we can easily figure out which emails are already in bluebird later.
    $nysenate_records[]=$row;
    $nysenate_emails[] = $row['Email Address'];
}


// Grab all bluebird records from the instance, keep an additional store for
// emails for matching against the nysenate emails.
// TODO: We might want to filter based on contact status (e.g. deleted)
$bluebird_records = array();
$bluebird_emails = array();
$dao = CRM_Core_DAO::executeQuery("SELECT email FROM civicrm_email");
while($dao->fetch()) {
    $bluebird_records[] = (array)$dao;
    $bluebird_emails[] = $dao->email;
}

// Mark all the nysenate signups that bluebird already has contacts for
// Accumulate the totals for reporting later on.
$in_bluebird = array_intersect($nysenate_emails,$bluebird_emails);
foreach($in_bluebird as $key => $email) {
    $record = &$nysenate_records[$key];
    $source = $record['Source List'];
    $record['In Bluebird']='TRUE';
    if($record['In District']=='TRUE') {
        $list_totals[$source]['In District']['In Bluebird'] += 1;
    } else {
        $list_totals[$source]['Out of District']['In Bluebird'] += 1;
    }
}

//Now we've got all the stats, lets do some reporting!
require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer("District_{$optList['district']}.xls");
createSummarySheet($workbook, $list_totals);
createSignupsSheet($workbook, $nysenate_header, $nysenate_records);
$workbook->close();


function createSummarySheet($workbook, $list_totals) {
    $summary_worksheet = & $workbook->addWorksheet('Summary');
    $row_num = write_row(array("","In District","","Out of District"), 0, $summary_worksheet);
    $row_num = write_row(array("Source List","Total","Not In Bluebird","Total","Not In Bluebird", "Total"), $row_num, $summary_worksheet);

    foreach($list_totals as $list_name => $stats) {
        $data = array(
            $list_name,
            $stats['In District']['Total'],
            $stats['In District']['Total']-$stats['In District']['In Bluebird'],
            $stats['Out of District']['Total'],
            $stats['Out of District']['Total']-$stats['Out of District']['In Bluebird'],
            "=B".($row_num+1)."+D".($row_num+1),
        );
        $row_num = write_row($data, $row_num, $summary_worksheet);
    }

    $summary_worksheet->write($row_num,5,"=SUM(F3:F$row_num)");
}

function createSignupsSheet($workbook, $nysenate_header, $nysenate_records) {
    // TODO: This could use some formatting...
    $nysenate_worksheet = & $workbook->addWorksheet('NYSenate.gov Emails');
    write_row($nysenate_header, 0, $nysenate_worksheet);
    foreach($nysenate_records as $key => $record)
        write_row($record,$key+1,$nysenate_worksheet);
}

function write_row($data, $row_num, $worksheet) {
    if(!$data)
        return false;

    foreach(array_values($data) as $col => $value)
        $worksheet->write($row_num, $col, $value);

    return $row_num+1;
}

function get_header($result) {
    $header = array();
    $num_fields = mysql_num_fields($result);
    for($i=0; $i < $num_fields; $i++)
        $header[$i] = mysql_field_name($result, $i);
    return $header;
}

function get_signups_connection($host,$user,$pass,$database) {

    if(! $conn = mysql_connect($host,$user,$pass) )
        die(mysql_error());

    elseif(! mysql_select_db($database,$conn) )
        die(mysql_error($conn));

    return $conn;
}
?>
