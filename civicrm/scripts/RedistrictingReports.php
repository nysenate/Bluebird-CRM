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

define('DEFAULT_FORMAT', 'text');
define('DEFAULT_INFO_LEVEL', 'summary');

$formats = array( 'html', 'text', 'csv', 'excel' );

// Parse the options
require_once 'script_utils.php';
$shortopts = "l:fo:sdrn";
$longopts = array("log=", "format=", "outfile=", "summary", "detail", "references", "nofilter");
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = 'RedistrictingReports.php -S mcdonald [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] --format= [html|txt|csv], --outfile= [ FILENAME ], --summary, --detail, --references, --nofilter';

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Set the options
$opt = array();
$opt['format'] = get($optlist, 'format', DEFAULT_FORMAT);
$opt['summary'] = get($optlist, 'summary', FALSE);
$opt['detail'] = get($optlist, 'detail', FALSE);
$opt['references'] = get($optlist, 'references', FALSE);
$opt['nofilter'] = get($optlist, 'nofilter', FALSE);

$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper(get($optlist, 'log', 'trace'))][0];

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
$senator_district = $bb_cfg['district'];

// Prefixes used in Redistricting.php
$subjects = array(
    "unchanged" => "RD12 VERIFIED DISTRICTS",
    "changed" => "RD12 UPDATED DISTRICTS",
    "removed" => "RD12 REMOVED DISTRICTS"
);

// Stores a list of contacts that are outside of the district.
$summary_data = array();

// Stores the summary counts for each district
$summary_cnts = array();

// Store detailed contact information for each outside district
$detail_data = array();

// Process out of district summary report
if ( $opt['summary'] != FALSE ){
	get_summary_report_data($senator_district, $db, &$summary_data, &$summary_cnts, !$opt['nofilter']);

	if ( $opt['format'] == 'text'){
		generate_text_summary_report($senator_district, $senator_name, $summary_cnts);
	}
	else if ( $opt['format'] == 'html'){
		generate_html_summary_report($senator_district, $senator_name, $summary_cnts);
	}
}

// Process out of district detailed report
if ( $opt['detail'] != FALSE ){
	get_detail_report_data($senator_district, $db, &$detail_data, $filter_contacts = true );

	if ( $opt['format'] == 'text'){
		generate_text_detailed_report($senator_district, $senator_name, $detail_data);
	}
	else if ( $opt['format'] == "html"){
		generate_html_detail_report($senator_district, $senator_name, $detail_data);
	}
}

// ----------------------------------------------------------------------
// Summary Reports 														|
// ----------------------------------------------------------------------

// The contact ids per district are stored in $summary_data and the counts
// per district are stored in $summary_cnts. If use_filter is false then all
// out of district contacts will be retrieved.
// Returns: $summary_cnts
function get_summary_report_data($senator_district, $db, &$summary_data, &$summary_cnts, $filter_contacts = true) {

	$res = retrieve_contacts_from_outside_dist($senator_district, $db, $filter_contacts);

	while (($row = mysql_fetch_assoc($res)) != null ) {

		$district = $row['district'];
		$contact_id = $row['contact_id'];
		$contact_type = strtolower($row['contact_type']);

		// Create an array to store contacts in each district
		if (!isset($summary_data[$district])){
			$summary_data[$district] = array();
		}

		// Create an array to store district counts
		if (!isset($summary_cnts[$district])){
			$summary_cnts[$district] = array();
		}

		// Set the counts for the contact type to 0
		if (!isset($summary_cnts[$district][$contact_type])){
			$summary_cnts[$district][$contact_type] = 0;
		}

		$summary_data[$district][$contact_type][] = $contact_id;
		$summary_cnts[$district][$contact_type]++;
	}

	mysql_free_result($res);
	return $summary_cnts;
}// get_summary_report_data

function generate_text_summary_report($senator_district, $senator_name, &$summary_cnts){

	$label = "${senator_name} District {$senator_district}\n\nSummary of contacts that are outside district {$senator_district}\n";
	$columns = array(
		"District" => 12,
		"Individuals" => 15,
		"Households" => 14,
		"Organization" => 14
	);

	$heading = $label . create_table_header($columns);

	$output_row = "";
	ksort($summary_cnts);
	foreach( $summary_cnts as $dist => $dist_cnts ){
		$output_row .=  fixed_width($dist, 12)
					   .fixed_width(get($dist_cnts, 'individual', '0'), 15)
					   .fixed_width(get($dist_cnts, 'household', '0'), 14, false)
					   .fixed_width(get($dist_cnts, 'organization', '0'), 14)."\n";
	}

	print $heading . $output_row;
}// generate_text_summary_report

function generate_html_summary_report($senator_district, $senator_name, &$summary_cnts){

	$title = "Redistricting 2012 Summary";
	$mode = "summary";

	// Buffer output from template
	ob_start();
	include "RedistrictingReportsTmpl.php";
	$output = ob_get_clean();
	print $output;
}// generate_html_summary_report

// ----------------------------------------------------------------------
// Detail Reports 														|
// ----------------------------------------------------------------------

// List all contact information per outside district
// Assumptions: State will just be 'NY' because we ignore out of state contacts.
function get_detail_report_data($senator_district, $db, &$detail_data, $filter_contacts = true ){

	$res = retrieve_contacts_from_outside_dist($senator_district, $db, $filter_contacts);
	bbscript_log("debug", "Storing contacts into array indexed by district");
	while (($row = mysql_fetch_assoc($res)) != null ) {

		$district = $row['district'];
		$contact_type = strtolower($row['contact_type']);
		$contact = $row;

		// Build the array so that contacts are grouped by contact type per district
		if (!isset($detail_data[$district])){
			$detail_data[$district] = array();
		}
		if (!isset($detail_data[$district][$contact_type])){
			$detail_data[$district][$contact_type] = array();
		}

		$detail_data[$district][$contact_type][] = $contact;
	}
	bbscript_log("debug", "Stored contacts in " . count($detail_data). " districts.");

	mysql_free_result($res);
}

function generate_text_detailed_report($senator_district, $senator_name, &$detail_data){
	bbscript_log("debug", "Generating detailed text report.");
	$output = "";

	$columns = array(
		"individual" => array(
			"Name" => 30, "Sex" => 6, "Age" => 6, "Address" => 25, "City" => 17, "Zip" => 6,
			"Email" => 20, "Source" => 9, "Cases" => 8, "Actvities" => 10, "BB Rec#" => 9 ),

		"organization" => array(
			"Organization Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
	        "Source" => 9, "Cases" => 8, "Actvities" => 10, "BB Rec#" => 9 ),

		"household" => array(
			"Household Name" => 30, "Address" => 37, "City" => 17, "Zip" => 6, "Email" => 20,
	        "Source" => 9, "Cases" => 8, "Actvities" => 10, "BB Rec#" => 9)
	);

	ksort($detail_data);
	foreach( $detail_data as $dist => $contact_types ){
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
					$output .= fixed_width($contact['household_name'], 30)
							.  fixed_width($contact['street_address'], 37, false, "---") . " ";
				}
				else if ($type == "organization"){
					$output .= fixed_width($contact['organization_name'], 30)
							.  fixed_width($contact['street_address'], 37, false, "---") . " ";
				}

				$output .=  fixed_width($contact['city'], 15) . " "
					      . fixed_width($contact['postal_code'],6)
					      . fixed_width($contact['email'], 21, false, "---")
					      . fixed_width($contact['source'], 9, true )
					      . fixed_width($contact['case_count'], 9)
					      . fixed_width($contact['activity_count'], 9)
					      . fixed_width($contact['contact_id']);
				$output .= "\n";
			}
		}
	}

	print $output . "\n\n";
}// generate_text_detailed_report

function generate_html_detail_report($senator_district, $senator_name, &$detail_data){

	$title = "Redistricting 2012 Contacts Reference";
	$mode = "detail";
	// Buffer output from template
	ob_start();
	include "RedistrictingReportsTmpl.php";
	$output = ob_get_clean();
	print $output;

}// generate_html_detail_report

// Retrieves a list of contacts that are outside of the district specified
// and have "value added" data associated with them.
// Returns the result set from the mysql query
function retrieve_contacts_from_outside_dist($senator_district, $db, $filter_contacts = true ){
	bbscript_log("debug", "Retrieving contacts that are out of district and are relevant");

	// Select out of district contacts
	$q = "
		SELECT DISTINCT contact.id AS contact_id, contact.contact_type, contact.first_name, contact.last_name, contact.display_name, contact.gender_id, contact.birth_date, contact.source,
                        contact.household_name, contact.organization_name,
                        a.street_address, a.city, a.postal_code,
                        email.email, district.ny_senate_district_47 AS district, COUNT(activity_target.id ) AS activity_count, COUNT(case_contact.id ) AS case_count
		FROM `civicrm_contact` AS contact
		JOIN `civicrm_value_district_information_7` district ON contact.id = district.entity_id
        LEFT JOIN `civicrm_address` a ON contact.id = a.contact_id
		LEFT JOIN `civicrm_email` email ON contact.id = email.id
		LEFT JOIN `civicrm_case_contact` case_contact ON contact.id = case_contact.contact_id
        LEFT JOIN `civicrm_activity_target` activity_target ON contact.id = activity_target.target_contact_id
		WHERE district.`ny_senate_district_47` != {$senator_district}
                AND a.is_primary = 1
	";

	// Filter critera
	$f = "
		# Filter out contacts without relevant data or those that don't want to be contacted
		AND
		(
		    ( contact.contact_type = 'Individual' AND NOT ( contact.source = 'BOE' AND contact.is_deceased = 0 )
		        AND (
	                email.id IS NOT NULL
	                OR case_contact.id IS NOT NULL
			   		OR contact.id IN (
				       	SELECT note.entity_id
				       	FROM `civicrm_note` AS note
				       	WHERE note.entity_table = 'civicrm_contact'
				       	AND note.subject NOT LIKE 'OMIS%'
				       	AND note.subject NOT LIKE 'RD12%'
				    	)
					OR
				    activity_target.id IS NOT NULL
		       )
		       OR (
		           contact.do_not_phone = 1 AND contact.do_not_mail = 1 AND ( contact.do_not_email = 1 OR contact.is_opt_out = 1 )
		       )
		    )

		    # Filter out households and organizations that have no relationships
			# [NOTE] I'm not sure if this simple relationship check is correct
		    OR
		    ( (contact.contact_type = 'Household' OR contact.contact_type = 'Organization')
		      AND contact.id IN
		          ( SELECT contact_id_b FROM `civicrm_relationship` WHERE is_active = 1 )
		    )
		)
	";

	// If filter option is true append filter criteria to query
	if($filter_contacts){
		$q .= $f;
	}

	// Group by contact in order to get the counts
	$q .= "
		GROUP BY contact.id
	";

	bbscript_log("trace", "SQL query:\n{$q}");

	$res = bb_mysql_query($q, $db, true);
	$num_rows = mysql_num_rows($res);

	bbscript_log("debug", "Retrieved {$num_rows} contacts");
	return $res;
}// retrieve_contacts_from_outside_dist

// ----------------------------------------------------------------------
// Helper Functions 													|
// ----------------------------------------------------------------------

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




