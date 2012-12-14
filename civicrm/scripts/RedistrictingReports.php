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

// ---------------------------------------------------------------------------------

// Outputs to store report text
$output = array(
	'summary' => "",
	'detail' => "",
	'references' => ""
);

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
	report_out_of_district_summary($senator_district, $db, &$summary_data, &$summary_cnts, !$opt['nofilter']);
	generate_text_summary_report($senator_district, $senator_name, $summary_cnts);
}

// Process out of district detailed report
if ( $opt['detail'] != FALSE ){
	report_out_of_district_details($senator_district, $db, &$detail_data, $filter_contacts = true );
	generate_text_detailed_report($senator_district, $senator_name, $detail_data);
}

//--------------------------------------------------------------------------------------
// Retrieves a list of contacts that are outside of the district specified             |
// and have "value added" data associated with them. The contact ids per district      |
// are stored in $summary_data and the counts per district are stored in $summary_cnts.|
// If use_filter is false then all out of district contacts will be retrieved.         |
// Returns: $summary_cnts
function report_out_of_district_summary($senator_district, $db, &$summary_data, &$summary_cnts, $filter_contacts = true) {

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
}// report_out_of_district_summary


function report_out_of_district_details($senator_district, $db, &$detail_data, $filter_contacts = true ){

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

function generate_text_summary_report($senator_district, $senator_name, &$summary_cnts){

	$heading = <<<heading
${senator_name} District {$senator_district}
Summary of contacts that are outside district {$senator_district}

District    Individuals    Households    Organizations
-------------------------------------------------------

heading;

	$output_row = "";
	ksort($summary_cnts);
	foreach( $summary_cnts as $dist => $dist_cnts ){
		$output_row .= str_pad($dist, 12)
		               .str_pad(get($dist_cnts, 'individual', 0), 15)
					   .str_pad(get($dist_cnts, 'household', 0), 14)
					   .str_pad(get($dist_cnts, 'organization', 0), 14)."\n";
	}

	print $heading . $output_row;
}// generate_text_summary_report

function retrieve_contacts_from_outside_dist($senator_district, $db, $filter_contacts = true ){
	bbscript_log("debug", "Retrieving contacts that are out of district and are relevant");

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

	// Select out of district contacts
	$q = "
		SELECT DISTINCT contact.id AS contact_id, contact.contact_type, contact.first_name, contact.last_name, contact.display_name, contact.gender_id, contact.birth_date,
                        a.street_address, a.street_number, a.street_number_suffix, a.street_name, a.street_type, a.city, a.postal_code,
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

function generate_text_detailed_report($senator_district, $senator_name, &$detail_data){
	bbscript_log("debug", "Generating detailed text report.");
	$output = "";

	foreach( $detail_data as $dist => $contact_types ){
		$heading = <<<heading
District $dist : Detailed Contact report
----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
Name                         | Sex | Age | Address                        | City        | State | Zip | Email              | Contact Source | Case Count| Activity Count | Bluebird Rec # |
----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

heading;
		$output .= $heading;
		/*

		*/
		if (isset($contact_types['individual'])){
			foreach( $contact_types['individual'] as $contact ){
				$output .= fixed_width($contact['last_name'].", ".$contact['first_name'], 30)
				         . fixed_width(get_gender($contact['gender_id']),6, true)
				         . fixed_width(get_age($contact['birth_date']), 6)
				         . fixed_width()

				$output .= "\n";
			}
		}
	}

	print $output;

}

function get($array, $key, $default) {
    // blank, null, and 0 values are bad.
    return isset($array[$key]) && $array[$key]!=NULL && $array[$key]!=="" && $array[$key]!==0 && $array[$key]!=="000" ? $array[$key] : $default;
}

function fixed_width($string, $length, $center = false){
	$pad_type = STR_PAD_RIGHT;
	if ($center) {
		$pad_type = STR_PAD_BOTH;
	}
	return substr(str_pad($string, $length, " ", $pad_type), 0, $length );
}

function get_gender($value, $unknown = "-"){
	if ($value == 1){
		return "F";
	}
	else if ($value == 2){
		return "M";
	}
	else return $unknown;
}

function get_age($birth_date, $unknown = '-'){
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
	return $unknown;
}




