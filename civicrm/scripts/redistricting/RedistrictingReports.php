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
define('RD_CONTACT_CACHE_TABLE', 'redist_report_contact_cache');
define('RD_NOTE_CACHE_TABLE', 'redist_report_note_cache');
define('RD_ACTS_CACHE_TABLE', 'redist_report_acts_cache');

// Parse the options
require_once 'script_utils.php';
$shortopts = "l:f:m:tdc";
$longopts = array("log=", "format=", "mode=", "threshold=", "disableCache", "clearCache");
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = '[--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] --format= [html|txt|csv], --mode=[summary|detail], --threshold=[THRESH], --disableCache, --clearCache';

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

// Sum the total counts from district_counts
$summary_totals = array();

// Store detailed contact information per district
$contacts_per_dist = array();

// Senator names and their url mapping
$senator_names = array(
	'1' => array('LaValle, Kenneth P.', 'kenneth-p-lavalle'),
	'2' => array('Flanagan, John J.', 'john-j-flanagan'),
	'3' => array('Zeldin, Lee M.', 'lee-m-zeldin'),
	'4' => array('Boyle, Philip M.', 'philip-m-boyle'),
	'5' => array('Marcellino, Carl L', 'carl-l-marcellino'),
	'6' => array('Hannon, Kemp', 'kemp-hannon'),
	'7' => array('Martins, Jack M.', 'jack-m-martins'),
	'8' => array('Fuschillo, Charles J. Jr', 'charles-j-fuschillo-jr'),
	'9' => array('Skelos, Dean G.', 'dean-g-skelos'),
	'10' => array('Sanders, James', 'james-sanders-jr'),
	'11' => array('Avella, Tony', 'tony-avella'),
	'12' => array('Gianaris, Michael', 'michael-gianaris'),
	'13' => array('Peralta, Jose', 'jose-peralta'),
	'14' => array('Smith, Malcolm A.', 'malcolm-smith'),
	'15' => array('Addabbo, Joseph P., Jr', 'joseph-p-addabbo-jr'),
	'16' => array('Stavisky, Toby Ann', 'toby-ann-stavisky'),
	'17' => array('Felder, Simcha', 'simcha-felder'),
	'18' => array('Dilan, Martin Malave', 'martin-malave-dilan'),
	'19' => array('Sampson, John L.', 'john-l-sampson'),
	'20' => array('Adams, Eric', 'eric-adams'),
	'21' => array('Parker, Kevin S.', 'kevin-s-parker'),
	'22' => array('Golden, Martin J.', 'martin-j-golden'),
	'23' => array('Savino, Diane J.', 'diane-j-savino'),
	'24' => array('Lanza, Andrew J', 'andrew-j-lanza'),
	'25' => array('Montgomery, Velmanette', 'velmanette-montgomery'),
	'26' => array('Squadron, Daniel L', 'daniel-l-squadron'),
	'27' => array('Hoylman, Brad', 'brad-hoylman'),
	'28' => array('Krueger, Liz', 'liz-krueger'),
	'29' => array('Serrano, Jose M.', 'jose-m-serrano'),
	'30' => array('Perkins, Bill', 'bill-perkins'),
	'31' => array('Espaillat, Adriano', 'adriano-espaillat'),
	'32' => array('Diaz, Ruben', 'ruben-diaz'),
	'33' => array('Rivera, Gustavo', 'gustavo-rivera'),
	'34' => array('Klein, Jeffrey D.', 'jeffrey-d-klein'),
	'35' => array('Stewart-Cousins, Andrea', 'andrea-stewart-cousins'),
	'36' => array('Hassell-Thompson, Ruth', 'ruth-hassell-thompson'),
	'37' => array('Latimer, George S.', 'george-s-latimer'),
	'38' => array('Carlucci, David', 'david-carlucci'),
	'39' => array('Larkin, William J., Jr', 'william-j-larkin-jr'),
	'40' => array('Ball, Greg', 'greg-ball'),
	'41' => array('Gipson, Terry', 'terry-gipson'),
	'42' => array('Bonacic, John J.', 'john-j-bonacic'),
	'43' => array('Marchione, Kathleen A.', 'kathleen-a-marchione'),
	'44' => array('Breslin, Neil D.', 'neil-d-breslin'),
	'45' => array('Little, Elizabeth', 'elizabeth-little'),
	'46' => array('Tkaczyk, Cecilia', 'cecilia-tkaczyk'),
	'47' => array('Griffo, Joseph A.', 'joseph-griffo'),
	'48' => array('Ritchie, Patty', 'patty-ritchie'),
	'49' => array('Farley, Hugh T.', 'hugh-t-farley'),
	'50' => array('DeFrancisco, John A.', 'john-defrancisco'),
	'51' => array('Seward, James L.', 'james-l-seward'),
	'52' => array('Libous, Tom', 'tom-libous'),
	'53' => array('Valesky, David J.', 'david-j-valesky'),
	'54' => array('Nozzolio, Michael F.', 'michael-f-nozzolio'),
	'55' => array('O\'Brien, Ted', 'ted-obrien'),
	'56' => array('Robach, Joseph E.', 'joseph-e-robach'),
	'57' => array('Young, Catharine', 'catharine-young'),
	'58' => array('O\'Mara, Thomas F.', 'thomas-f-omara'),
	'59' => array('Gallivan, Patrick M.', 'pat-gallivan'),
	'60' => array('Grisanti, Mark', 'mark-grisanti'),
	'61' => array('Ranzenhofer, Michael H.', 'michael-h-ranzenhofer'),
	'62' => array('Maziarz, George D.', 'george-d-maziarz'),
	'63' => array('Kennedy, Timothy', 'timothy-kennedy')
);

// ----------------------------------------------------------------------
// Request Handler 														|
// ----------------------------------------------------------------------

if ($opt['clear_cache'] != FALSE ){
	clear_reports_cache($db);
	die();
}

// Process out of district summary report
if ( $opt['mode'] == 'summary' ){
	$district_contact_data = get_redist_data($db, true, $senate_district, !$opt['disable_cache']);
	$district_counts = process_summary_data($district_contact_data, $opt['threshold']);
	$summary_totals = compute_summary_totals($district_counts);
	$summary_output = get_summary_output($opt['format'], $senate_district, $senator_name, $district_counts, $summary_totals);
	print $summary_output;
}

// Process out of district detailed report
if ( $opt['mode'] == 'detail' ){
	$district_contact_data = get_redist_data($db, true, $senate_district, !$opt['disable_cache']);
	$contacts_per_dist = process_detail_data($district_contact_data, $senate_district, $opt['threshold']);
	$detail_output = get_detail_output($opt['format'], $senate_district, $senator_name, $contacts_per_dist);
	print $detail_output;
}

// ----------------------------------------------------------------------
//  Data Aggregator 													|
// ----------------------------------------------------------------------

function get_redist_data($db, $filter_contacts = true, $senate_district = -1, $use_cache = true ){

	$district_contact_data = array();

	// Get all value added out of district contacts
	$res = get_contacts($db, $filter_contacts, $senate_district, $use_cache);
	while (($row = mysqli_fetch_assoc($res)) != null ) {
		$contact_id = $row['contact_id'];
		$district_contact_data[$contact_id] = $row;
	}
	mysqli_free_result($res);

	// Append the redistricting note to the contact
	$res = get_redist_notes($db, $senate_district, $use_cache);
	while (($row = mysqli_fetch_assoc($res)) != null ) {
		$contact_id = $row['contact_id'];
		if (isset($district_contact_data[$contact_id])){
			$district_contact_data[$contact_id]['note'] = $row['note'];
			$district_contact_data[$contact_id]['subject'] = $row['subject'];
			$district_contact_data[$contact_id]['prior_dist'] = get_former_district($row['note'], "-");
		}
	}
	mysqli_free_result($res);

	// Append the email counts to the contact
	$res = get_email_counts($db);
	while (($row = mysqli_fetch_assoc($res)) != null ) {
		$contact_id = $row['contact_id'];
		if (isset($district_contact_data[$contact_id])){
			$district_contact_data[$contact_id]['email_count'] = $row['email_count'];
			$district_contact_data[$contact_id]['active_email_count'] = $row['active_email_count'];

		}
	}
	mysqli_free_result($res);

	bbscript_log(LL::DEBUG, "Stored " . count($district_contact_data) . " contacts in memory");
	return $district_contact_data;
}

// ----------------------------------------------------------------------
// Summary Reports - Provide basic counts for each district 			|
// ----------------------------------------------------------------------

function process_summary_data($district_contact_data, $threshold = 0) {

	$district_counts = array();
	foreach( $district_contact_data as $contact ){

		$district = $contact['district'];
		$contact_id = $contact['contact_id'];
		$contact_type = strtolower($contact['contact_type']);
		
		// Create an array to store district counts
		if (!isset($district_counts[$district])){
			$district_counts[$district] = array();
		}

		$district_counts[$district]['contacts']++;
		$district_counts[$district][$contact_type]++;
		$district_counts[$district]['emails'] += $contact['email_count'];
		$district_counts[$district]['active_emails'] += $contact['active_email_count'];
		$district_counts[$district]['open_cases'] += $contact['open_case_count'];
		$district_counts[$district]['assigned_cases'] += $contact['assigned_case_count'];
		$district_counts[$district]['urgent_cases'] += $contact['urgent_case_count'];
		$district_counts[$district]['inactive_cases'] += ($contact['case_count'] - $contact['open_case_count'] - $contact['assigned_case_count'] - $contact['urgent_case_count']);
		$district_counts[$district]['all_cases'] += $contact['case_count'];		
		$district_counts[$district]['open_activities'] += $contact['open_activity_count'];
		$district_counts[$district]['activities'] += $contact['activity_count'];
	}

	// Apply the threshold
	foreach($district_counts as $district => $counts){
		if ($counts['contacts'] < $threshold){
			unset($district_counts[$district]);
		}
	}

	return $district_counts;
}// get_summary_report_data

function compute_summary_totals($district_counts, $exclude_dist_zero = true) {
	$total = array();

	if ($exclude_dist_zero){
		unset($district_counts["0"]);
	}

	foreach( $district_counts as $district => $counts ){
		$total['individual'] += get($counts,'individual',0);
		$total['household'] += get($counts,'household',0);
		$total['organization'] += get($counts,'organization',0);
		$total['contacts'] += get($counts,'contacts',0);
		$total['active_emails'] += get($counts,'active_emails',0);
		$total['emails'] += get($counts,'emails',0);
		$total['active_cases'] +=  get($counts,'all_cases',0) - get($counts,'inactive_cases',0);
		$total['cases'] += get($counts,'all_cases',0);
		$total['open_activities'] += get($counts,'open_activities',0);
		$total['activities'] += get($counts,'activities',0);
	}

	return $total;
}// compute_summary_totals

function get_summary_output($format, $senate_district, $senator_name, $district_counts, $summary_totals){

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

	return $output;
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
    	bbscript_log(LL::DEBUG, "Fetching all 'value added' contacts that are not in District $filter_district...");
    }
    else {
    	bbscript_log(LL::DEBUG, "Fetching all contacts not in District $filter_district...");
    }

    // Repeated conditions! 
    $valid_source_activity = "NULLIF(source_activity.is_current_revision, 0), NULLIF(source_activity.is_deleted, 1), NULLIF(source_activity.is_test, 1)";
    $valid_target_activity = "NULLIF(activity.is_current_revision, 0), NULLIF(activity.is_deleted, 1), NULLIF(activity.is_test, 1)";
    $open_source_activity = "NULLIF(source_activity.status_id = 1 OR source_activity.status_id = 7, 0)";
    $open_target_activity = "NULLIF(activity.status_id = 1 OR activity.status_id = 7, 0)";
    $valid_case = "NULLIF(c_case.is_deleted, 1)";
    
    if (!table_exists($db, RD_ACTS_CACHE_TABLE)){
    	$act_query = 
	    	"CREATE TABLE IF NOT EXISTS `" . RD_ACTS_CACHE_TABLE . "` (
			  `act_contact_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Unique Contact ID',
			  `activity_count` bigint(24) NOT NULL DEFAULT '0',
			  `open_activity_count` bigint(24) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`act_contact_id`)
			) ENGINE=InnoDB;";
		
		bbscript_log(LL::INFO, "Creating redist activities cache table");
		bb_mysql_query($act_query, $db, true);

		$q = 	"INSERT INTO " . RD_ACTS_CACHE_TABLE . " 
	    	SELECT 
	    		contact.id AS act_contact_id,

				# The activity count does not include case activities
				GREATEST(
				COUNT(DISTINCT source_activity.id, {$valid_source_activity}) 
				- COUNT(DISTINCT source_activity.id, source_case_activity.id, {$valid_source_activity})
				+ COUNT(DISTINCT activity_target.id, {$valid_target_activity}) 
				- COUNT(DISTINCT activity_target.id, case_activity.id, {$valid_target_activity})
				,0) AS activity_count,

				# Also count the number of open activities
				GREATEST(
				COUNT(DISTINCT source_activity.id, {$valid_source_activity}, {$open_source_activity}) 
				- COUNT(DISTINCT source_activity.id, source_case_activity.id, {$valid_source_activity}, {$open_source_activity})
				+ COUNT(DISTINCT activity_target.id, {$valid_target_activity}, {$open_target_activity}) 
				- COUNT(DISTINCT activity_target.id, case_activity.id, {$valid_target_activity}, {$open_target_activity})
				,0) AS open_activity_count

			FROM `civicrm_contact` AS contact
			JOIN `civicrm_address` a ON contact.id = a.contact_id
			JOIN `civicrm_value_district_information_7` district ON a.id = district.entity_id
			LEFT JOIN `civicrm_activity` source_activity ON source_activity.source_contact_id = contact.id
			LEFT JOIN `civicrm_case_activity` source_case_activity ON source_activity.id = source_case_activity.activity_id 
			LEFT JOIN `civicrm_activity_target` activity_target ON contact.id = activity_target.target_contact_id
			LEFT JOIN `civicrm_activity` activity ON activity.id = activity_target.activity_id
			LEFT JOIN `civicrm_case_activity` case_activity ON activity_target.activity_id = case_activity.activity_id

			WHERE district.`ny_senate_district_47` != {$filter_district} AND a.is_primary = 1
			AND contact.id != 1
			GROUP BY contact.id
			";
		bbscript_log(LL::INFO, "Populating redist activities cache table");
		bb_mysql_query($q, $db, true);	
    }      

	$contact_query = 
		"SELECT * FROM (
			SELECT DISTINCT contact.id AS contact_id, contact.contact_type, contact.first_name, contact.last_name,
			                contact.birth_date, contact.gender_id, 
			                contact.household_name, contact.organization_name, contact.is_deceased, contact.source,
			                constituent.contact_source_60 AS const_source, phone.phone AS phone,
			                a.street_address, a.city, a.postal_code,
			                email.email, email.is_primary, district.ny_senate_district_47 AS district,

			                # All case counts and also a breakdown by case status
	                        COUNT(DISTINCT case_contact.id, {$valid_case}) AS case_count,
							COUNT(DISTINCT case_contact.id, {$valid_case}, NULLIF(c_case.status_id = 1, 0)) AS open_case_count,
							COUNT(DISTINCT case_contact.id, {$valid_case}, NULLIF(c_case.status_id = 3, 0)) AS urgent_case_count,
							COUNT(DISTINCT case_contact.id, {$valid_case}, NULLIF(c_case.status_id = 5, 0)) AS assigned_case_count,

							# Group Count
	                        COUNT(DISTINCT group_contact.group_id, NULLIF(group_contact.status, 'Removed') ) AS group_count

			FROM `civicrm_contact` AS contact
			JOIN `civicrm_address` a ON contact.id = a.contact_id
			JOIN `civicrm_value_district_information_7` district ON a.id = district.entity_id			
			LEFT JOIN `civicrm_value_constituent_information_1` constituent on contact.id = constituent.entity_id  
			LEFT JOIN `civicrm_email` email ON contact.id = email.contact_id AND email.is_primary = 1
			LEFT JOIN `civicrm_phone` phone ON contact.id = phone.contact_id

	   		# Counts of cases
	        LEFT JOIN `civicrm_case_contact` case_contact ON contact.id = case_contact.contact_id
	        LEFT JOIN `civicrm_case` c_case ON c_case.id = case_contact.case_id        

	        # Counts of groups
	        LEFT JOIN `civicrm_group_contact` group_contact ON contact.id = group_contact.contact_id

			WHERE district.`ny_senate_district_47` != {$filter_district}
			AND a.is_primary = 1
			AND contact.is_deleted = 0
			AND NOT (contact.do_not_phone = 1 AND contact.do_not_mail = 1 AND ( contact.do_not_email = 1 OR contact.is_opt_out = 1 ))
			GROUP BY contact_id
		) AS c
		LEFT JOIN `" . RD_ACTS_CACHE_TABLE . "` acts_cache ON acts_cache.act_contact_id = c.contact_id
	";

	// Filter critera
	$contact_filter = "
		# Filter out contacts without relevant data or those that don't want to be contacted
		WHERE
		c.contact_type = 'Individual' 
		AND NOT ( 
				  IFNULL(c.const_source,'') = 'boe' 
			      AND c.is_deceased = 0 
				  AND c.email IS NULL
				  AND case_count = 0
				  AND activity_count = 0
				  AND phone IS NULL

				  # Check if contact has any non-boe addresses
				  AND c.contact_id NOT IN (
				  	SELECT address.contact_id
				  	FROM `civicrm_address` AS address
				  	WHERE address.location_type_id != 6 AND 
				  	      address.location_type_id != 13
				  )
			      
			      # Check if contact has any non-default notes
			      AND c.contact_id NOT IN (
			        SELECT note.entity_id
				    FROM `civicrm_note` AS note
				    WHERE note.entity_table = 'civicrm_contact'
				    AND privacy = 0
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
    		bbscript_log(LL::INFO, "Creating redist contact cache table");
    		$contact_query = "CREATE TABLE " . RD_CONTACT_CACHE_TABLE . " AS (" . $contact_query . "); ";
			bb_mysql_query($contact_query, $db, true);
			bbscript_log(LL::INFO, "Finished creating redist contact cache table");
    	}

    	$contact_query = "SELECT * FROM " . RD_CONTACT_CACHE_TABLE;
    }

	$res = bb_mysql_query($contact_query, $db, true);
	$num_rows = mysqli_num_rows($res);
	bbscript_log(LL::DEBUG, "Retrieved $num_rows contacts");
	return $res;
}// get_contacts

function get_redist_notes($db, $filter_district = -1, $use_cache = true){

	bbscript_log(LL::DEBUG, "Fetching redistricting notes...");
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
    		bbscript_log(LL::INFO, "Creating redist note cache table");
    		$note_query = "CREATE TABLE " . RD_NOTE_CACHE_TABLE . " AS (" . $note_query . "); ";
    		bb_mysql_query($note_query, $db, true);
    		bbscript_log(LL::INFO, "Finished creating redist note cache table");
    	}

    	$note_query = "SELECT * FROM " . RD_NOTE_CACHE_TABLE;
    }

	$res = bb_mysql_query($note_query, $db, true);
	$num_rows = mysqli_num_rows($res);

	bbscript_log(LL::DEBUG, "Retrieved {$num_rows} notes");
	return $res;
}// get_redist_notes

// The summary page displays email counts per district. This includes
// non-primary email addresses. The following query returns the contact
// id and the number of email addresses which will be joined to the 
// main data array. 
function get_email_counts($db){

	$email_query = "
		SELECT contact.id AS contact_id, 
			COUNT(DISTINCT(email.email)) AS email_count,
			COUNT(DISTINCT email.email, NULLIF(contact.do_not_email, 1),
				  NULLIF(contact.is_opt_out, 1), NULLIF(email.on_hold = 0, 0)
				) AS active_email_count
		FROM `civicrm_contact` AS contact
		JOIN `civicrm_email` email ON contact.id = email.contact_id
		GROUP BY contact.id
	";

	$res = bb_mysql_query($email_query, $db, true);
	$num_rows = mysqli_num_rows($res);
	bbscript_log(LL::DEBUG, "Retrieved {$num_rows} email records");
	return $res;
}// get_email_counts

// ----------------------------------------------------------------------
// Cache Functions 											    		|
// ----------------------------------------------------------------------
function clear_reports_cache($db){
	bbscript_log(LL::INFO, "Clearing redist report contact cache table");
	$drop = "DROP TABLE IF EXISTS " . RD_CONTACT_CACHE_TABLE .";\n";
	bb_mysql_query($drop, $db, true);

	bbscript_log(LL::INFO, "Clearing redist report note cache table");
	$drop = "DROP TABLE IF EXISTS " . RD_NOTE_CACHE_TABLE .";";
	bb_mysql_query($drop, $db, true);

	bbscript_log(LL::INFO, "Clearing redist act cache table");
	$drop = "DROP TABLE IF EXISTS " . RD_ACTS_CACHE_TABLE .";";
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

// Return the former district if assigned
function get_former_district($note, $default = "N/A"){

	$matches = array();
	preg_match("/SD:(\d{0,2}).{2}\d{0,2}/i", $note, $matches);
	if (count($matches) == 2 && $matches[1] != ""){
		return $matches[1];
	}
	return $default;
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
			bbscript_log(LL::TRACE, "Failed to get age from date: $birth_date");
		}
	}
	return $default;
}

function table_exists($db, $table_name){
	$res = bb_mysql_query("SHOW TABLES LIKE '" . $table_name . "'", $db, true);
	return (mysqli_num_rows($res) > 0);
}

function get_senator_name($district){
	global $senator_names;
	
	if (isset($senator_names[$district][0])){
		return $senator_names[$district][0];
	}
	else {
		return "Undecided";
	}
}

function get_senator_url($district){
	global $senator_names;
	
	if (isset($senator_names[$district][1])){
		return $senator_names[$district][1];
	}
	else {
		return "";
	}
}
