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
$shortopts = "fo:sdr";
$longopts = array("format=", "outfile=", "summary", "details", "references");
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = 'RedistrictingReports.php -S mcdonald --format= [html|txt|csv], --outfile= [ FILENAME ], --summary, --details, --references';

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
$detailed_data = array();

// Process out of district summary report
if ( $opt['summary'] != FALSE ){

	report_out_of_district_summary($senator_district, $db, &$summary_data, &$summary_cnts);
	generate_text_summary_report($senator_district, $senator_name, $summary_cnts);

}

//--------------------------------------------------------------------------------------
// Retrieves a list of contacts that are outside of the district specified             |
// and have "value added" data associated with them. The contact ids per district      |
// are stored in $summary_data and the counts per district are stored in $summary_cnts |
// Returns: $summary_cnts
function report_out_of_district_summary($senator_district, $db, &$summary_data, &$summary_cnts) {

	bbscript_log("info", "Retrieving contacts that are out of district and are relevant");
	$q = "
		SELECT DISTINCT contact.id AS contact_id, contact.contact_type, email.email, district.ny_senate_district_47 AS district
		FROM `civicrm_contact` AS contact
		JOIN `civicrm_value_district_information_7` district ON contact.id = district.entity_id
		LEFT JOIN `civicrm_email` email ON contact.id = email.id
		LEFT JOIN `civicrm_case_contact` case_contact ON contact.id = case_contact.contact_id
		WHERE district.`ny_senate_district_47` != {$senator_district}
		AND
		(
		    # Filter out contacts without relevant data or those that don't want to be contacted

		    ( contact.contact_type = 'Individual' AND NOT ( contact.source = 'BOE' AND contact.is_deceased = 0 )
		        AND (
			   		email.id IS NOT NULL OR
			   		case_contact.id IS NOT NULL OR
			   		contact.id IN (
				       SELECT note.entity_id
				       FROM `civicrm_note` AS note
				       WHERE note.entity_table = 'civicrm_contact'
				       AND note.subject NOT LIKE 'OMIS%'
				       AND note.subject NOT LIKE 'RD12%'
				    ) OR
				    contact.id IN (
				       SELECT target_contact_id
				       FROM `civicrm_activity_target` activity_target
				       JOIN `civicrm_activity` activity ON activity.id = activity_target.activity_id
				    )
		       )
		       OR (
		           contact.do_not_phone = 1 AND contact.do_not_mail = 1 AND ( contact.do_not_email = 1 OR contact.is_opt_out = 1 )
		       )
		    )

		    # Filter out households and organizations that have no relationships
			# [NOTE] I'm not sure if the simple relationship check is correct
		    OR
		    ( (contact.contact_type = 'Household' OR contact.contact_type = 'Organization')
		      AND contact.id IN
		          ( SELECT contact_id_b FROM `civicrm_relationship` WHERE is_active = 1 )
		    )
		)";

	bbscript_log("trace", "SQL query:\n{$q}");

	$res = bb_mysql_query($q, $db, true);
	$num_rows = mysql_num_rows($res);

	bbscript_log("debug", "Retrieved {$num_rows} contacts");

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


function generate_text_summary_report($senator_district, $senator_name, &$summary_cnts){

	$heading = <<<heading
${senator_name} District {$senator_district}
Summary of contacts that are outside district {$senator_district}

District    Individuals    Households    Organizations
-------------------------------------------------------

heading;

	$output_row = "";
	foreach( $summary_cnts as $dist => $dist_cnts ){
		$output_row .= str_pad($dist, 12)
		               .str_pad(get($dist_cnts, 'individual', 0), 15)
					   .str_pad(get($dist_cnts, 'household', 0), 14)
					   .str_pad(get($dist_cnts, 'organization', 0), 14)."\n";
	}

	print $heading . $output_row;
}

function generate_text_detailed_report(){

}

function get($array, $key, $default) {
    // blank, null, and 0 values are bad.
    return isset($array[$key]) && $array[$key]!=NULL && $array[$key]!=="" && $array[$key]!==0 && $array[$key]!=="000" ? $array[$key] : $default;
}






