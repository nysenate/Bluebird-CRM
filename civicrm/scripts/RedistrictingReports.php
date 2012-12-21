<?php
//-------------------------------------------------------------------------------------
// Project: BluebirdCRM Redistricting
// Authors: Ash Islam
// Organization: New York State Senate
// Date: 2012-12-20

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
define('DEFAULT_INFO_LEVEL', 'summary');

// Parse the options
require_once 'script_utils.php';
$shortopts = "l:f:o:sdtrn";
$longopts = array("log=", "format=", "outfile=", "summary", "detail", "stats", "district=", "nofilter");
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = 'RedistrictingReports.php -S mcdonald [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] --format= [html|txt|csv], --outfile= [ FILENAME ], --summary, --detail, --stats, --district= [DISTRICT NUM], --nofilter';

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Available formats
$formats = array( 'html', 'text', 'csv', 'excel' );

// Set the options
$opt = array();
$opt['format'] = get($optlist, 'format', DEFAULT_FORMAT);
$opt['summary'] = get($optlist, 'summary', FALSE);
$opt['detail'] = get($optlist, 'detail', FALSE);
$opt['stats'] = get($optlist, 'stats', FALSE);
$opt['district'] = get($optlist, 'district', FALSE);
$opt['nofilter'] = get($optlist, 'nofilter', FALSE);

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

$district_contact_data = get_redist_data($db, true, $senate_district);

// Process out of district summary report
if ( $opt['summary'] != FALSE ){

	$district_counts = process_summary_data($district_contact_data, $senate_district);

	if ( $opt['format'] == 'text'){
		output_summary_text($senate_district, $senator_name, $district_counts);
	}

	else if ( $opt['format'] == 'html'){
		output_summary_html($senate_district, $senator_name, $district_counts);
	}
}

// Process out of district detailed report
if ( $opt['detail'] != FALSE ){

	process_detail_data($senate_district, $db, $contacts_per_dist, $filter_contacts = true);

	if ( $opt['format'] == 'text'){
		output_detail_text($senate_district, $senator_name, $contacts_per_dist);
	}
	else if ( $opt['format'] == "html"){
		output_detail_html($senate_district, $senator_name, $contacts_per_dist);
	}
}

// Process redistricting stats
if ( $opt['stats'] != FALSE ){

}

function get_redist_data($db, $filter_contacts = true, $senate_district = -1){

	$district_contact_data = array();

	$res = get_contacts($db, $filter_contacts, $senate_district);
	while (($row = mysql_fetch_assoc($res)) != null ) {

		$contact_id = $row['contact_id'];
		$district_contact_data[$contact_id] = $row;
	}
	mysql_free_result($res);

	$res = get_redist_notes($db, $senate_district);
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

function process_summary_data($district_contact_data, $senate_district) {

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
				'organization' => array("total"=>0,"changed"=>0)
			);
		}

		$district_counts[$district][$contact_type]['total']++;
		// Count the number of contacts that are moving from the instance district
		if (is_former_district($note, $senate_district)){
			$district_counts[$district][$contact_type]['changed']++;
		}

	}

	return $district_counts;
}// get_summary_report_data

function output_summary_text($senate_district, $senator_name, $district_counts){

	$label = <<<label
${senator_name} District {$senate_district}\n
Summary of contacts that are outside district {$senate_district}\n
The number on the left is a count of the contacts that were in District {$senate_district}
and are now in district specified. The number on the right is the total count
of value added contacts that reside in that district which includes contacts
that were already there before redistricting.\n
label;

	$columns = array(
		"Senate District" => 12,
		"Individuals" => 15,
		"Households" => 14,
		"Organization" => 14
	);

	$heading = $label . create_table_header($columns);

	$output_row = "";
	ksort($district_counts);

	foreach( $district_counts as $dist => $dist_cnts ){
		$output_row .=  fixed_width($dist, 12, false, "Unknown")
					   .fixed_width(get($dist_cnts['individual'], 'changed', '0') . " / " .get($dist_cnts['individual'], 'total', '0'), 15)
					   .fixed_width(get($dist_cnts['household'], 'changed', '0') . " / " . get($dist_cnts['household'], 'total', '0'), 14, false)
					   .fixed_width(get($dist_cnts['organization'], 'changed', '0') . " / " .get($dist_cnts['organization'], 'total', '0'), 14)."\n";
	}

	print $heading . $output_row;
}// output_summary_text

function output_summary_html($senate_district, $senator_name, &$district_counts){

	$title = "Redistricting 2012 Summary";
	$mode = "summary";

	// Buffer output from template
	ob_start();
	include "RedistrictingReportsTmpl.php";
	$output = ob_get_clean();
	print $output;
}// output_summary_html

// ----------------------------------------------------------------------
// Detail Reports - List all contacts outside the instance district 	|
// ----------------------------------------------------------------------

// List all contact information per outside district
// Assumptions: State will just be 'NY' because we ignore out of state contacts.
function process_detail_data($senate_district, $db, &$contacts_per_dist, $filter_contacts = true ){

	$res = get_contacts($senate_district, $db, $filter_contacts);
	bbscript_log("debug", "Storing contacts into array indexed by district");
	while (($row = mysql_fetch_assoc($res)) != null ) {

		$district = $row['district'];
		$contact_type = strtolower($row['contact_type']);
		$contact = $row;

		// Build the array so that contacts are grouped by contact type per district
		if (!isset($contacts_per_dist[$district])){
			$contacts_per_dist[$district] = array();
		}
		if (!isset($contacts_per_dist[$district][$contact_type])){
			$contacts_per_dist[$district][$contact_type] = array();
		}

		$contacts_per_dist[$district][$contact_type][] = $contact;
	}
	bbscript_log("debug", "Stored contacts in " . count($contacts_per_dist). " districts.");

	mysql_free_result($res);
}

function output_detail_text($senate_district, $senator_name, &$contacts_per_dist){
	bbscript_log("debug", "Generating detailed text report.");
	$output = "";

	$columns = array(
		"individual" => array(
			"Name" => 30, "Sex" => 6, "Age" => 6, "Address" => 25, "City" => 17, "Zip" => 6,
			"Email" => 20, "Source" => 9, "Cases" => 8, "Acts" => 10, "Groups" =>8, "BB Rec#" => 9 ),

		"organization" => array(
			"Organization Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
	        "Source" => 9, "Cases" => 8, "Acts" => 10, "Groups" =>8, "BB Rec#" => 9 ),

		"household" => array(
			"Household Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
	        "Source" => 9, "Cases" => 8, "Acts" => 10, "Groups" =>8, "BB Rec#" => 9)
	);

	ksort($contacts_per_dist);

	// Ignore contacts in District 0. They are either out of state or
	// won't be assigned to another district regardless.
	unset($contacts_per_dist["0"]);

	foreach( $contacts_per_dist as $dist => $contact_types ){
		foreach( $contact_types as $type => $contact_array ){

			$label = "\nDistrict $dist : " . ucfirst($type) . "s\n";
			$heading = create_table_header($columns[$type]);
			$output .= $label . $heading;

			foreach($contact_array as $contact){
				if ($type == "individual"){
					$output .= fixed_width($contact['last_name'].", ".$contact['first_name'], 30)
					         . fixed_width(get_gender($contact['gender_id']),6, true)
					         . fixed_width(get_age($contact['birth_date']), 6, false)
					         . fixed_width($contact['street_address'], 25, false, "---") . " ";
				}
				else if ($type == "household"){
					$output .= fixed_width($contact['household_name'], 29) . " "
							.  fixed_width($contact['street_address'], 37, false, "---") . " ";
				}
				else if ($type == "organization"){
					$output .= fixed_width($contact['organization_name'], 29) . " "
							.  fixed_width($contact['street_address'], 37, false, "---") . " ";
				}

				$output .=  fixed_width($contact['city'], 15) . " "
					      . fixed_width($contact['postal_code'],6)
					      . fixed_width($contact['email'], 21, false, "---")
					      . fixed_width($contact['source'], 9, true )
					      . fixed_width($contact['case_count'], 9)
					      . fixed_width($contact['activity_count'], 9)
					      . fixed_width($contact['group_count'], 9)
					      . fixed_width($contact['contact_id']);
				$output .= "\n";
			}
		}
	}

	print $output . "\n\n";
}// output_detail_text

// Buffer output from RedistrictingReportsTmpl using mode = detail
function output_detail_html($senate_district, $senator_name, &$contacts_per_dist){

	$title = "Redistricting 2012 Contacts Reference";
	$mode = "detail";

	ob_start();
	include "RedistrictingReportsTmpl.php";
	$output = ob_get_clean();

	print $output;
}// output_detail_html

function output_detail_csv($senate_district, $senator_name, &$contacts_per_dist){

}// output_detail_csv

// ----------------------------------------------------------------------
// SQL Functions 		     											|
// ----------------------------------------------------------------------

// Retrieves a list of contacts along with counts of their cases,activities,etc.
// use_contact_filter: If true, return only contacts that have value-added info.
// filter_district: Return only contacts that are not in the district specified

// Returns the result set from the mysql query
function get_contacts($db, $use_contact_filter = true, $filter_district = -1 ){
    if ($use_contact_filter){
    	bbscript_log("debug", "Fetching all 'value added' contacts that are not in District $filter_district...");
    }
    else {
    	bbscript_log("debug", "Fetching all contacts not in District $filter_district...");
    }
	// contact info, address, email, district, and activity/case/group counts
	$contact_query = "
		SELECT c.* FROM
		(SELECT c.*, COUNT(NULLIF(group_contact.status, 'Removed')) AS group_count 	FROM
		(SELECT c.*, COUNT(DISTINCT id) AS activity_count FROM
		(SELECT c.*, COUNT(DISTINCT id) AS case_count FROM
		(SELECT DISTINCT contact.id AS contact_id, contact.contact_type, contact.first_name, contact.last_name,
		                 contact.household_name, contact.organization_name, contact.is_deceased, contact.source,
		                 a.street_address, a.city, a.postal_code,
		                 email.email, email.is_primary, district.ny_senate_district_47 AS district
		FROM `civicrm_contact` AS contact
		JOIN `civicrm_address` a ON contact.id = a.contact_id
		JOIN `civicrm_value_district_information_7` district ON a.id = district.entity_id
		LEFT JOIN `civicrm_email` email ON contact.id = email.contact_id

		WHERE district.`ny_senate_district_47` != {$filter_district}
		AND a.is_primary = 1
		AND NOT (contact.do_not_phone = 1 AND contact.do_not_mail = 1 AND ( contact.do_not_email = 1 OR contact.is_opt_out = 1 ))
		) AS c

		LEFT JOIN `civicrm_case_contact` case_contact ON c.contact_id = case_contact.contact_id
		GROUP BY c.contact_id ) AS c
		LEFT JOIN `civicrm_activity_target` activity ON c.contact_id = activity.target_contact_id
		GROUP BY c.contact_id ) AS c
		LEFT JOIN `civicrm_group_contact` AS group_contact
		ON c.contact_id = group_contact.contact_id

		GROUP BY c.contact_id
		) AS c
	";

	// Filter critera
	$contact_filter = "
		# Filter out contacts without relevant data or those that don't want to be contacted
		WHERE
		( c.contact_type = 'Individual' AND NOT ( c.source = 'BOE' AND c.is_deceased = 0 )
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
		)
		OR
		( (c.contact_type = 'Household' OR c.contact_type = 'Organization')
		  AND c.contact_id IN (SELECT contact_id_b FROM `civicrm_relationship` WHERE is_active = 1 )
		)
	";

	// If filter option is true append filter criteria to query
	if($use_contact_filter){
		$contact_query .= $contact_filter;
	}

	$res = bb_mysql_query($contact_query, $db, true);
	$num_rows = mysql_num_rows($res);

	bbscript_log("debug", "Retrieved {$num_rows} contacts");
	return $res;
}// get_contacts

function get_redist_notes($db, $filter_district = -1){

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

	$res = bb_mysql_query($note_query, $db, true);
	$num_rows = mysql_num_rows($res);

	bbscript_log("debug", "Retrieved {$num_rows} notes");
	return $res;
}// get_redist_notes

// ----------------------------------------------------------------------
// Helper Functions 													|
// ----------------------------------------------------------------------

// Checks the redist note to see if the address formerly belonged in the
// district specified by $district. $key refers to the type of district.
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
	else return $default;
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