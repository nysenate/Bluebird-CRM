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
//-------------------------------------------------------------------------------------

// ./RedistrictingReports.php -S mcdonald --format = [html|txt|csv], --infolevel [summary|details|reference]
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_FORMAT', 'txt');
define('DEFAULT_INFO_LEVEL', 'summary');

$formats = array( 'html', 'txt', 'csv', 'excel' );
$info_levels = array( 'summary', 'details', 'reference');

// Parse the options
require_once 'script_utils.php';
$shortopts = "fi";
$longopts = array("format=", "infolevel=");
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = 'RedistrictingReports.php -S mcdonald --format= [html|txt|csv], --infolevel= [summary|details|reference]';

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Set the options
$format = $optlist['format'] ? $optlist['format'] : DEFAULT_FORMAT;
$info_level = $optlist['infolevel'] ? $optlist['infolevel'] : DEFAULT_INFO_LEVEL;

// Check if the format and info level specified is proper or terminate.
if ( !in_array( $format, $formats ) || !in_array( $info_level, $info_levels ) ) {
	bbscript_log("error", "Check usage for correct --format --infolevel parameters");
	bbscript_log("info", "Usage: $usage");
	exit();
}

// Initialize CiviCRM
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

// Establish a connection to the instance database
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

// `Note subject` prefixes used in Redistricting.php
$subject_prefixes = array(
    "unchanged" => "RD12 VERIFIED DISTRICTS",
    "changed" => "RD12 UPDATED DISTRICTS",
    "removed" => "RD12 REMOVED DISTRICTS"
);

// The SQL queries used in this script are contained here:
// -----------------------------------------------------------
$query_same_district = "
	SELECT `entity_id`, `note`, `modified_date`, `subject`\n
	FROM `civicrm_note`\n
	WHERE `subject` LIKE '{$subject_prefixes['unchanged']}'
";

$query_new_districts = "
	SELECT `entity_id`, `note`, `modified_date`, `subject`\n
	FROM `civicrm_note`\n
	WHERE `subject` LIKE '{$subject_prefixes['changed']}%'
";

$query_removed_districts = "
	SELECT `entity_id`, `note`, `modified_date`, `subject`\n
	FROM `civicrm_note`\n
	WHERE `subject` LIKE '{$subject_prefixes['removed']}'
";
// -----------------------------------------------------------

// The arrays to store statistics based on the info level specified

// Summary level counts
$count = array(
	'unchanged' => 0,
	'changed' => 0,
	'removed' => 0
);

// Detail level counts
$detail_count = array();

// Store Reference Information
$reference = array();

$same_district_result = mysql_query( $query_same_district, $db );
$count['unchanged'] = mysql_num_rows( $same_district_result );
bbscript_log("info", "Found {$count['unchanged']} contacts that remain in the same district");

$new_district_result = mysql_query( $query_new_districts, $db );
$count['changed'] = mysql_num_rows( $new_district_result );
bbscript_log("info", "Found {$count['changed']} contacts that moved to other districts");

$removed_district_result = mysql_query( $query_removed_districts, $db );
$count['removed'] = mysql_num_rows( $removed_district_result );
bbscript_log("info", "Found {$count['removed']} contacts that were out of state");

// Template for outputting the pertinent information consistently.
// ---------------------------------------------------------------------------------
$redistrict_tmpl = array(
	'report_title' => '2012 Redistricting Results',
	'summary' => array(
		'title' => 'Summary of redistricting results:',
		'counts' => array(
			'unchanged' => 'District information remained the same for %d contacts.',
			'changed' => 'One or more districts were updated for %d contacts.',
			'removed' => 'There were %d `non NY state addresses` found in this district.'
		)
	),
	'detail' => array(
		'counts' => array(
			'new_districts' => 'Constituents have been transferred into the following new districts:'
		)
	)
);
// ---------------------------------------------------------------------------------

generateTextReport( $redistrict_tmpl, $count );

// Generate output functions

function generateHTMLReport( $tmpl ) {

}

function generateTextReport( $tmpl, $count ) {

	$output = $tmpl['report_title'] . "\n\n";
	$output .= $tmpl['summary']['title'] . "\n\n";
	$output .= sprintf($tmpl['summary']['counts']['unchanged'], $count['unchanged']) . "\n";
	$output .= sprintf($tmpl['summary']['counts']['changed'], $count['changed']) . "\n";
	$output .= sprintf($tmpl['summary']['counts']['removed'], $count['removed']) . "\n";

	print $output;
}

function generateCSVReport( $tmpl ) {

}

function generateExcelReport( $tmpl ) {

}












