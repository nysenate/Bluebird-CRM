<?php
// Project: BluebirdCRM
// Authors: Ash Islam
// Organization: New York State Senate
// Date: 2012-12-05

//-------------------------------------------------------------------------------------
// This script will generate reports summarizing redistricting changes.

// Once the Redistricting script has been run and district information has been updated,
// a report will be generated to show the number of contacts that remain in the district
// as well the number of contacts in each new district.

// This is per the Redistricting Process Flow ( Step 5 ) outlined at:
// http://dev.nysenate.gov/projects/2012_redistricting/wiki/Redistricting_Process_Flow
// and Issue 5940: http://dev.nysenate.gov/issues/5940
//-------------------------------------------------------------------------------------

// ./RedistrictingReports.php -S mcdonald --format = [html|txt|csv], --infolevel [summary|details|reference]
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_FORMAT', 'txt');
define('DEFAULT_INFO_LEVEL', 'summary');

$formats = array( 'html', 'txt', 'csv', 'excel' );

// Parse the options
require_once 'script_utils.php';
$shortopts = "fsdr";
$longopts = array("format=", "summary", "details", "references");
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = 'RedistrictingReports.php -S mcdonald --format= [html|txt|csv], --summary, --details, --references';

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Set the options
$opt = array();
$opt['format'] = get($optlist, 'format', DEFAULT_FORMAT);
$opt['summary'] = get($optlist, 'summary', FALSE);
$opt['details'] = get($optlist, 'details', FALSE);
$opt['references'] = get($optlist, 'references', FALSE);

// Initialize CiviCRM
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

// Establish a connection to the instance database
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

// Get the senate district for this instance
$bb_cfg = get_bluebird_instance_config($optlist['site']);
$inst_senate_district = $bb_cfg['district'];

// Template for outputting text consistently.
// ---------------------------------------------------------------------------------
$tmpl = array(

);
// ---------------------------------------------------------------------------------

// Outputs to store report text
$output = array(
	'summary' => "",
	'detail' => "",
	'references' => ""
);

// `Note subject` prefixes used in Redistricting.php
$subjects = array(
    "unchanged" => "RD12 VERIFIED DISTRICTS",
    "changed" => "RD12 UPDATED DISTRICTS",
    "removed" => "RD12 REMOVED DISTRICTS"
);

// Summary level counts
$summary_count = array(
	'unchanged' => 0,
	'changed' => 0,
	'removed' => 0,
	'districts' => array()
);

// The SQL queries used in this script are contained here:
// -----------------------------------------------------------
$query = array();

// List of address counts per district
$query['district_counts'] = "
	SELECT `ny_senate_district_47` AS district, COUNT( * ) AS count
	FROM `civicrm_value_district_information_7`
	WHERE `ny_senate_district_47` IS NOT NULL
	GROUP BY `ny_senate_district_47`
";

// Number of addresses where no districts changed
$query['same_districts'] = "
	SELECT `entity_id`, `note`, `modified_date`, `subject`\n
	FROM `civicrm_note`\n
	WHERE `subject` LIKE '{$subjects['unchanged']}'
";

// Number of addresses where atleast one district changed
$query['new_districts'] = "
	SELECT `entity_id`, `note`, `modified_date`, `subject`\n
	FROM `civicrm_note`\n
	WHERE `subject` LIKE '{$subjects['changed']}%'
";

// Number of non-NY addresses whose district information was zeroed
$query['removed_districts'] = "
	SELECT `entity_id`, `note`, `modified_date`, `subject`\n
	FROM `civicrm_note`\n
	WHERE `subject` LIKE '{$subjects['removed']}'
";
// -----------------------------------------------------------

if ( $opt['summary'] != FALSE ){

	$results = array();

	$results['district_counts'] = mysql_query( $query['district_counts'], $db );
	while ( ($row = mysql_fetch_assoc($results['district_counts'])) != null ){
		$summary_count['districts'][$row['district']] = $row['count'];
	}

	$results['same_districts'] = mysql_query( $query['same_districts'], $db );
	$summary_count['unchanged'] = mysql_num_rows( $results['same_districts'] );
	bbscript_log("info", "Found {$summary_count['unchanged']} contacts that remain in the same district");

	$results['new_districts'] = mysql_query( $query['new_districts'], $db );
	$summary_count['changed'] = mysql_num_rows( $results['new_districts'] );
	bbscript_log("info", "Found {$summary_count['changed']} contacts that moved to other districts");

	$results['removed_districts'] = mysql_query( $query['removed_districts'], $db );
	$summary_count['removed'] = mysql_num_rows( $results['removed_districts'] );
	bbscript_log("info", "Found {$summary_count['removed']} contacts that were out of state");

	if ( $opt['format'] == 'txt' ) {

		$str = "Summary of Senate Redistricting\n\n";
		$str .= "SD\tOut of district records\n";
		foreach( $summary_count['districts'] as $district => $count ){
			if ( $district != $inst_senate_district ) {
				$str .= "$district\t$count\n";
			}
		}

		$output['summary'] = $str;
	}

	// [TODO] save to file...
	print $output['summary'];
}

if ( $opt['details'] != FALSE ){

	$a = "
	SELECT `civicrm_contact`.last_name,
		   `civicrm_contact`.first_name,
		   `civicrm_contact`.gender_id,
		   `civicrm_contact`.birth_date,
		   `civicrm_address`.city,
		   `civicrm_address`.state,
		   `civicrm_address`.zip,
		   `civicrm_contact`.email,

	FROM `civicrm_contact`
	JOIN `civicrm_address` ON `civicrm_contact`.id = `civicrm_address`.contact_id

	";

}

if ( $opt['references'] != FALSE ){


}

function get($array, $key, $default) {
    // blank, null, and 0 values are bad.
    return isset($array[$key]) && $array[$key]!=NULL && $array[$key]!=="" && $array[$key]!==0 && $array[$key]!=="000" ? $array[$key] : $default;
}







