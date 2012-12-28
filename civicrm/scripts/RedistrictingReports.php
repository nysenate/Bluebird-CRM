<?php
//-------------------------------------------------------------------------------------
// Project: BluebirdCRM Redistricting
// Authors: Ash Islam
// Organization: New York State Senate
// Date: 2012-12-26

//-------------------------------------------------------------------------------------
// This script will generate reports pertaining to redistricting changes.

// Once the Redistricting script has been run and district information has been updated,
// a report will be generated to show the number of contacts that will be assigned to
// new districts.

// This is per the Redistricting Process Flow ( Step 5 ) outlined at:
// http://dev.nysenate.gov/projects/2012_redistricting/wiki/Redistricting_Process_Flow
// and Issue 5940: http://dev.nysenate.gov/issues/5940
//-------------------------------------------------------------------------------------

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_FORMAT', 'text');
define('DEFAULT_MODE', 'summary');
define('RD_CONTACT_CACHE_TABLE', 'civicrm_redist_contact_cache');
define('RD_NOTE_CACHE_TABLE', 'civicrm_redist_note_cache');

// Parse the options
require_once 'script_utils.php';
$shortopts = "l:f:m:tdc";
$longopts = array("log=", "format=", "mode=", "threshold=", "disableCache", "clearCache");
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = 'RedistrictingReports.php -S mcdonald [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] --format= [html|txt|csv], --mode=[summary|detail], --threshold=[THRESH], --disableCache, --clearCache';

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Available formats
$formats = array('html', 'text', 'csv');

// Set the options
$opt = array();
$opt['format'] = get($optlist, 'format', DEFAULT_FORMAT);
$opt['mode'] = get($optlist, 'mode', DEFAULT_MODE);
$opt['disable_cache'] = get($optlist, 'disableCache', FALSE);
$opt['clear_cache'] = get($optlist, 'clearCache', FALSE);
$opt['threshold'] = get($optlist, 'threshold', 0);

$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper(get($optlist, 'log', 'fatal'))][0];

// Initialize CiviCRM
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

// Establish a connection to the instance database
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

// Get the senate district for this instance
$bb_cfg = get_bluebird_instance_config($optlist['site']);
$site = $bb_cfg['db.basename'];
$senator_name = $bb_cfg['senator.name.formal'];
$senate_district = $bb_cfg['district'];

// ----------------------------------------------------------------------
// Data Arrays  														|
// ----------------------------------------------------------------------

// Stores all contacts and notes
$district_contact_data = array();

// Stores the individual, household, and org counts for each district
$district_counts = array();

// Store detailed contact information per district
$contacts_per_dist = array();

// ----------------------------------------------------------------------
// Request Handler 														|
// ----------------------------------------------------------------------

if ($opt['clear_cache'] != FALSE ){
	clear_reports_cache($db);
	die();
}

$district_contact_data = get_redist_data($db, true, $senate_district, !$opt['disable_cache']);

// Process out of district summary report
if ( $opt['mode'] == 'summary' ){

	$district_counts = process_summary_data($district_contact_data, $senate_district, $opt['threshold']);
	$summary_output = get_summary_output($opt['format'], $senate_district, $senator_name, $district_counts);
	print $summary_output;
}

// Process out of district detailed report
if ( $opt['mode'] == 'detail' ){

	$contacts_per_dist = process_detail_data($district_contact_data, $senate_district, $opt['threshold']);
	$detail_output = get_detail_output($opt['format'], $senate_district, $senator_name, $contacts_per_dist);
	print $detail_output;
}

function get_redist_data($db, $filter_contacts = true, $senate_district = -1, $use_cache = true ){

	$district_contact_data = array();

	$res = get_contacts($db, $filter_contacts, $senate_district, $use_cache);
	while (($row = mysql_fetch_assoc($res)) != null ) {

		$contact_id = $row['contact_id'];
		$district_contact_data[$contact_id] = $row;
	}
	mysql_free_result($res);

	$res = get_redist_notes($db, $senate_district, $use_cache);
	while (($row = mysql_fetch_assoc($res)) != null ) {

		$contact_id = $row['contact_id'];
		if (isset($district_contact_data[$contact_id])){
			$district_contact_data[$contact_id]['note'] = $row['note'];
			$district_contact_data[$contact_id]['subject'] = $row['subject'];
		}
	}
	mysql_free_result($res);

	bbscript_log("debug", "Stored " . count($district_contact_data) . " contacts in memory");
	return $district_contact_data;
}

// ----------------------------------------------------------------------
// Summary Reports - Provide basic counts for each district 			|
// ----------------------------------------------------------------------

function process_summary_data($district_contact_data, $senate_district, $threshold = 0) {

	$district_counts = array();
	foreach( $district_contact_data as $contact ){

		$district = $contact['district'];
		$contact_id = $contact['contact_id'];
		$contact_type = strtolower($contact['contact_type']);
		$note = get($contact, 'note', '');

		// Create an array to store district counts
		if (!isset($district_counts[$district])){
			$district_counts[$district] = array(
				'individual' => array("total"=>0,"changed"=>0),
				'household' => array("total"=>0,"changed"=>0),
				'organization' => array("total"=>0,"changed"=>0),
				'all' => array("total"=>0,"changed"=>0)
			);
		}

		$district_counts[$district]['all']['total']++;
		$district_counts[$district][$contact_type]['total']++;

		// Count the number of contacts that are moving from the instance district
		if (is_former_district($note, $senate_district)){
			$district_counts[$district]['all']['changed']++;
			$district_counts[$district][$contact_type]['changed']++;
		}
	}

	// Apply the threshold
	foreach($district_counts as $dist => $cnts){
		if ($cnts['all']['total'] < $threshold){
			unset($district_counts[$dist]);
		}
	}

	return $district_counts;
}// get_summary_report_data

function get_summary_output($format, $senate_district, $senator_name, $district_counts){

	global $site;
	$title = "Redistricting 2012 Summary";
	$mode = "summary";

	// Buffer output from template
	ob_start();
	include "RedistrictingReportsTmpl.php";
	$output = ob_get_clean();
	return $output;
}

// ----------------------------------------------------------------------
// Detail Reports - List all contacts outside the instance district 	|
// ----------------------------------------------------------------------

// List all contact information per outside district
// Assumptions: State will just be 'NY' because we ignore out of state contacts.
function process_detail_data($district_contact_data, $senate_district, $threshold = 0){

	$contacts_per_dist = array();
	foreach( $district_contact_data as $contact ){

		$district = $contact['district'];
		$contact_type = strtolower($contact['contact_type']);

		// Build the array so that contacts are grouped by contact type per district
		if (!isset($contacts_per_dist[$district])){
			$contacts_per_dist[$district] = array();
		}
		if (!isset($contacts_per_dist[$district][$contact_type])){
			$contacts_per_dist[$district][$contact_type] = array();
		}

		$contacts_per_dist[$district][$contact_type][] = $contact;
	}

	// Apply the threshold
	foreach($contacts_per_dist as $dist => $contact_types){
		$contact_cnt = 0;
		foreach($contact_types as $type => $contact_array ){
			$contact_cnt += count($contact_array);
		}
		if ($contact_cnt < $threshold ){
			unset($contacts_per_dist[$dist]);
		}
	}

	return $contacts_per_dist;
}

// Buffer output from RedistrictingReportsTmpl using mode = detail
function get_detail_output($format, $senate_district, $senator_name, $contacts_per_dist){

	global $site;
	$title = "Redistricting 2012 Contacts Reference";
	$mode = "detail";

	ob_start();
	include "RedistrictingReportsTmpl.php";
	$output = ob_get_clean();

	print $output;
}// output_detail_html

// ----------------------------------------------------------------------
// SQL Functions 		     											|
// ----------------------------------------------------------------------

// Retrieves a list of contacts along with counts of their cases,activities,etc.
// use_contact_filter: If true, return only contacts that have value-added info.
// filter_district: Return only contacts that are not in the district specified

// Returns the result set from the mysql query
function get_contacts($db, $use_contact_filter = true, $filter_district = -1, $use_cache = true ){

    if ($use_contact_filter){
    	bbscript_log("debug", "Fetching all 'value added' contacts that are not in District $filter_district...");
    }
    else {
    	bbscript_log("debug", "Fetching all contacts not in District $filter_district...");
    }

	$contact_query = "
		SELECT * FROM (
			SELECT DISTINCT contact.id AS contact_id, contact.contact_type, contact.first_name, contact.last_name,
			                 contact.birth_date, contact.gender_id,
			                 contact.household_name, contact.organization_name, contact.is_deceased, contact.source,
			                 a.street_address, a.city, a.postal_code,
			                 email.email, email.is_primary, district.ny_senate_district_47 AS district,
	                         COUNT(DISTINCT case_contact.id) AS case_count,
	                         COUNT(DISTINCT activity.id) AS activity_count,
	                         COUNT(DISTINCT group_contact.group_id, NULLIF(group_contact.status, 'Removed') ) AS group_count
			FROM `civicrm_contact` AS contact
			JOIN `civicrm_address` a ON contact.id = a.contact_id
			JOIN `civicrm_value_district_information_7` district ON a.id = district.entity_id
			LEFT JOIN `civicrm_email` email ON contact.id = email.contact_id
	        LEFT JOIN `civicrm_case_contact` case_contact ON contact.id = case_contact.contact_id
	        LEFT JOIN `civicrm_activity_target` activity ON contact.id = activity.target_contact_id
	        LEFT JOIN `civicrm_group_contact` group_contact ON contact.id = group_contact.contact_id

			WHERE district.`ny_senate_district_47` != {$filter_district}
			AND a.is_primary = 1
			AND contact.is_deleted = 0
			AND NOT (contact.do_not_phone = 1 AND contact.do_not_mail = 1 AND ( contact.do_not_email = 1 OR contact.is_opt_out = 1 ))
			GROUP BY contact_id
		) AS c
	";

	// Filter critera
	$contact_filter = "
		# Filter out contacts without relevant data or those that don't want to be contacted
		WHERE
		c.contact_type = 'Individual' AND NOT ( c.source = 'BOE' AND c.is_deceased = 0 )
		AND (
		       (c.email IS NOT NULL AND c.is_primary = 1 )
		       OR case_count > 0
		       OR activity_count > 0
		       OR group_count > 0

		       # Check if contact has any non-default notes
		       OR c.contact_id IN (
		         	SELECT note.entity_id
			       	FROM `civicrm_note` AS note
			       	WHERE note.entity_table = 'civicrm_contact'
			       	AND note.subject NOT LIKE 'OMIS%'
			       	AND note.subject NOT LIKE 'REDIST2012%'
		    	)
		    )
		OR c.contact_type = 'Household'
		OR c.contact_type = 'Organization'
	";

	// If filter option is true append filter criteria to query
	if($use_contact_filter){
		$contact_query .= $contact_filter;
	}

    // If cache option is set, check to see if the cache table exists, create it otherwise,
    // and select the data from that table.
    if ($use_cache){
    	if (!table_exists($db, RD_CONTACT_CACHE_TABLE)){
    		bbscript_log("info", "Creating redist contact cache table");
    		$contact_query = "CREATE TABLE " . RD_CONTACT_CACHE_TABLE . " AS (" . $contact_query . "); ";
			bb_mysql_query($contact_query, $db, true);
			bbscript_log("info", "Finished creating redist contact cache table");
    	}

    	$contact_query = "SELECT * FROM " . RD_CONTACT_CACHE_TABLE;
    }

	$res = bb_mysql_query($contact_query, $db, true);
	$num_rows = mysql_num_rows($res);
	bbscript_log("debug", "Retrieved $num_rows contacts");
	return $res;
}// get_contacts

function get_redist_notes($db, $filter_district = -1, $use_cache = true){

	bbscript_log("debug", "Fetching redistricting notes...");
	$note_query = "
		SELECT contact.id AS contact_id, address.id AS address_id, ny_senate_district_47 AS district, note.note, note.subject, note.modified_date
		FROM `civicrm_note` note
		JOIN `civicrm_contact` contact ON note.entity_id = contact.id
		JOIN `civicrm_address` address ON contact.id = address.contact_id
		JOIN `civicrm_value_district_information_7` district ON address.id = district.entity_id
		WHERE
		address.is_primary = 1 AND
		district.`ny_senate_district_47` != {$filter_district} AND
		note.entity_table = 'civicrm_contact' AND
		note.subject LIKE CONCAT('REDIST2012%[id=', address.id , ']%')
	";

	if ($use_cache){
    	if (!table_exists($db, RD_NOTE_CACHE_TABLE)){
    		bbscript_log("info", "Creating redist note cache table");
    		$note_query = "CREATE TABLE " . RD_NOTE_CACHE_TABLE . " AS (" . $note_query . "); ";
    		bb_mysql_query($note_query, $db, true);
    		bbscript_log("info", "Finished creating redist note cache table");
    	}

    	$note_query = "SELECT * FROM " . RD_NOTE_CACHE_TABLE;
    }

	$res = bb_mysql_query($note_query, $db, true);
	$num_rows = mysql_num_rows($res);

	bbscript_log("debug", "Retrieved {$num_rows} notes");
	return $res;
}// get_redist_notes

// ----------------------------------------------------------------------
// Cache Functions 											    		|
// ----------------------------------------------------------------------
function clear_reports_cache($db){
	bbscript_log("info", "Clearing redist report contact cache table");
	$drop = "DROP TABLE IF EXISTS " . RD_CONTACT_CACHE_TABLE .";\n";
	bb_mysql_query($drop, $db, true);

	bbscript_log("info", "Clearing redist report note cache table");
	$drop = "DROP TABLE IF EXISTS " . RD_NOTE_CACHE_TABLE .";";
	bb_mysql_query($drop, $db, true);
}

// ----------------------------------------------------------------------
// Helper Functions 													|
// ----------------------------------------------------------------------

// Checks the redist note to see if the address formerly belonged in the
// district specified by $district. $key refers to the district abbrv.
function is_former_district($note_subject, $district = 0, $key = 'SD'){
	return preg_match("/".$key.":".$district."=>(\d{0,2})/i", $note_subject);
}

// Create a table header given an array of column names as keys and widths as values
function create_table_header($columns, $border = '-', $separator = "|"){

	$header = "";
	$total_width = 0;

	foreach($columns as $name => $width){
		$header .= fixed_width($name, $width - 1, true) . $separator;
		$total_width += $width;
	}

	$border_row = "";
	for($i = 0; $i < $total_width; $i++){
		$border_row .= $border;
	}

	$header = $border_row . "\n" . $header . "\n" . $border_row . "\n";
	return $header;
}

function get($array, $key, $default) {
    // blank, null, and 0 values are bad.
    return isset($array[$key]) && $array[$key]!=NULL && $array[$key]!=="" && $array[$key]!==0 && $array[$key]!=="000" ? $array[$key] : $default;
}

// Pads the string to a certain length and chops off the rest on the right side
function fixed_width($string, $length = 10, $center = false, $default = ""){
	$pad_type = STR_PAD_RIGHT;
	if ($center) {
		$pad_type = STR_PAD_BOTH;
	}
	if ($string == NULL || $string == "" ){
		$string = $default;
	}
	return substr(str_pad($string, $length, " ", $pad_type), 0, $length );
}

function get_gender($value, $default = "-"){
	if ($value == 1){
		return "F";
	}
	else if ($value == 2){
		return "M";
	}
	return $default;
}

function get_age($birth_date, $default = '-'){
	if ( $birth_date != NULL && $birth_date != "" ){
		try {
			$b_date = new DateTime($birth_date);
			$today = new DateTime();
			$diff = $b_date->diff($today);
			return $diff->format("%y");
		}
		catch(Exception $e){
			bbscript_log("trace", "Failed to get age from date: $birth_date");
		}
	}
	return $default;
}

function table_exists($db, $table_name){
	$res = bb_mysql_query("SHOW TABLES LIKE '" . $table_name . "'", $db, true);
	return (mysql_num_rows($res) > 0);

}