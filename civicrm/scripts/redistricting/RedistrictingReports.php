<?php
//-----------------------------------------------------------------------------
// Project: BluebirdCRM Redistricting
// Authors: Ash Islam
// Organization: New York State Senate
// Date: 2012-12-26
// Revised: 2023-02-15

//-----------------------------------------------------------------------------
// This script will generate reports pertaining to redistricting changes.
//
// Once the Redistricting script has been run and district information has been
// updated, a report will be generated to show the number of contacts that will
// be assigned to new districts.
//
// This is per the Redistricting Process Flow ( Step 5 ) outlined at:
// http://dev.nysenate.gov/projects/2012_redistricting/wiki/Redistricting_Process_Flow
// and Issue 5940: http://dev.nysenate.gov/issues/5940
//-----------------------------------------------------------------------------
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('REDIST_YEAR', '2022');
define('DEFAULT_FORMAT', 'text');
define('DEFAULT_MODE', 'summary');
define('RD_CONTACT_CACHE_TABLE', 'redist_report_contact_cache');
define('RD_NOTE_CACHE_TABLE', 'redist_report_note_cache');
define('RD_ACTS_CACHE_TABLE', 'redist_report_acts_cache');

// Parse the options
require_once realpath(dirname(__FILE__)).'/../script_utils.php';
$shortopts = "l:f:m:t:odc";
$longopts = ["log=", "format=", "mode=", "threshold=", "outfile", "disableCache", "clearCache"];
$optlist = civicrm_script_init($shortopts, $longopts);
$usage = '[--log {TRACE|DEBUG|INFO|WARN|ERROR|FATAL}] [--format {html|txt|csv}] [--mode {summary|detail}] [--threshold THRESH] [--outfile] [--disableCache] [--clearCache]';

if ($optlist === null) {
  $stdusage = civicrm_script_usage();
  error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
  exit(1);
}


// Available formats and modes
$formats = ['html', 'text', 'csv'];
$modes = ['summary', 'detail'];

// Set the options
set_bbscript_log_level(get($optlist, 'log', 'INFO'));
$opt = [];
$opt['format'] = get($optlist, 'format', DEFAULT_FORMAT);
$opt['mode'] = get($optlist, 'mode', DEFAULT_MODE);
$opt['threshold'] = get($optlist, 'threshold', 0);
$opt['outfile'] = get($optlist, 'outfile', false);
$opt['disable_cache'] = get($optlist, 'disableCache', false);
$opt['clear_cache'] = get($optlist, 'clearCache', false);

if (!in_array($opt['format'], $formats, true)) {
  error_log("Format must be one of: " . implode(', ', $formats) . "\n");
  exit(2);
}
else if (!in_array($opt['mode'], $modes, true)) {
  error_log("Mode must be one of: " . implode(', ', $modes) . "\n");
  exit(3);
}

require_once "RedistrictingReports_{$opt['format']}.php";


// Initialize CiviCRM
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

// Establish a connection to the instance database
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

// Get the senate district for this instance
$site = $optlist['site'];
$bb_cfg = get_bluebird_instance_config($site);
$cfg_name = $bb_cfg['senator.name.formal'];
$cfg_dist = $bb_cfg['district'];

// ----------------------------------------------------------------------
// Data Arrays
// ----------------------------------------------------------------------

// Stores all contacts and notes
$district_contact_data = [];

// Stores the individual, household, and org counts for each district
$district_counts = [];

// Store detailed contact information per district
$contacts_per_dist = [];

// Senator names and their url mapping
$senator_names = [
   '1' => ['Palumbo, Anthony H.', 'anthony-h-palumbo'],
   '2' => ['Mattera, Mario R.', 'mario-r-mattera'],
   '3' => ['Murray, Dean', 'dean-murray'],
   '4' => ['Martinez, Monica R.', 'monica-r-martinez'],
   '5' => ['Rhoads, Steven D.', 'steven-d-rhoads'],
   '6' => ['Thomas, Kevin', 'kevin-thomas'],
   '7' => ['Martins, Jack M.', 'jack-m-martins'],
   '8' => ['Weik, Alexis', 'alexis-weik'],
   '9' => ['Canzoneri-Fitzpatrick, Patricia', 'patricia-canzoneri-fitzpatrick'],
  '10' => ['Sanders, James', 'james-sanders-jr'],
  '11' => ['Stavisky, Toby Ann', 'toby-ann-stavisky'],
  '12' => ['Gianaris, Michael', 'michael-gianaris'],
  '13' => ['Ramos, Jessica', 'jessica-ramos'],
  '14' => ['Comrie, Leroy', 'leroy-comrie'],
  '15' => ['Addabbo, Joseph P., Jr', 'joseph-p-addabbo-jr'],
  '16' => ['Liu, John C.', 'john-c-liu'],
  '17' => ['Chu, Iwen', 'iwen-chu'],
  '18' => ['Salazar, Julia', 'julia-salazar'],
  '19' => ['Persaud, Roxanne J.', 'roxanne-j-persaud'],
  '20' => ['Myrie, Zellnor', 'zellnor-myrie'],
  '21' => ['Parker, Kevin S.', 'kevin-s-parker'],
  '22' => ['Felder, Simcha', 'simcha-felder'],
  '23' => ['Scarcella-Spanton, Jessica', 'jessica-scarcella-spanton'],
  '24' => ['Lanza, Andrew J', 'andrew-j-lanza'],
  '25' => ['Brisport, Jabari', 'jabari-brisport'],
  '26' => ['Gounardes, Andrew', 'andrew-gounardes'],
  '27' => ['Kavanagh, Brian', 'brian-kavanagh'],
  '28' => ['Krueger, Liz', 'liz-krueger'],
  '29' => ['Serrano, Jose M.', 'jose-m-serrano'],
  '30' => ['Cleare, Cordell', 'cordell-cleare'],
  '31' => ['Jackson, Robert', 'robert-jackson'],
  '32' => ['Sepulveda, Luis R.', 'luis-r-sepulveda'],
  '33' => ['Rivera, Gustavo', 'gustavo-rivera'],
  '34' => ['Fernandez, Nathalia', 'nathalia-fernandez'],
  '35' => ['Stewart-Cousins, Andrea', 'andrea-stewart-cousins'],
  '36' => ['Bailey, Jamaal T.', 'jamaal-t-bailey'],
  '37' => ['Mayer, Shelley B.', 'shelley-b-mayer'],
  '38' => ['Weber, Bill', 'bill-weber'],
  '39' => ['Rolison, Rob', 'rob-rolison'],
  '40' => ['Harckham, Pete', 'pete-harckham'],
  '41' => ['Hinchey, Michelle', 'michelle-hinchey'],
  '42' => ['Skoufis, James', 'james-skoufis'],
  '43' => ['Ashby, Jacob', 'jacob-ashby'],
  '44' => ['Tedisco, James', 'james-tedisco'],
  '45' => ['Stec, Daniel G.', 'daniel-g-stec'],
  '46' => ['Breslin, Neil D.', 'neil-d-breslin'],
  '47' => ['Hoylman-Sigal, Brad', 'brad-hoylman-sigal'],
  '48' => ['May, Rachel', 'rachel-may'],
  '49' => ['Walczyk, Mark', 'mark-walczyk'],
  '50' => ['Mannion, John W.', 'john-w-mannion'],
  '51' => ['Oberacker, Peter', 'peter-oberacker'],
  '52' => ['Webb, Lea', 'lea-webb'],
  '53' => ['Griffo, Joseph A.', 'joseph-griffo'],
  '54' => ['Helming, Pamela', 'pamela-helming'],
  '55' => ['Brouk, Samra G.', 'samra-g-brouk'],
  '56' => ['Cooney, Jeremy A.', 'jeremy-cooney'],
  '57' => ['Borrello, George M.', 'george-m-borrello'],
  '58' => ['O\'Mara, Thomas F.', 'thomas-f-omara'],
  '59' => ['Gonzalez, Kristen', 'kristen-gonzalez'],
  '60' => ['Gallivan, Patrick M.', 'patrick-m-gallivan'],
  '61' => ['Ryan, Sean M.', 'sean-m-ryan'],
  '62' => ['Ortt, Robert G.', 'robert-g-ortt'],
  '63' => ['Kennedy, Timothy M.', 'timothy-m-kennedy']
];

// ----------------------------------------------------------------------
// Request Handler
// ----------------------------------------------------------------------

if ($opt['clear_cache'] != false) {
  clear_reports_cache($db);
  exit(0);
}

// Retrieve district data, whether mode is SUMMARY or DETAIL.
$district_contact_data = get_redist_data($db, $cfg_dist, true, !$opt['disable_cache']);

if ($opt['mode'] == 'summary') {
  // Process out of district summary report
  $district_counts = process_summary_data($district_contact_data, $opt['threshold']);
  $output = get_summary_output($district_counts, $cfg_dist, $cfg_name);
}
else {
  // Process out of district detail report
  $contacts_per_dist = process_detail_data($district_contact_data, $opt['threshold']);
  $output = get_detail_output($contacts_per_dist, $cfg_dist, $cfg_name, $site);
}

$rc = 0;

if (!empty($output)) {
  if ($opt['outfile']) {
    $fname = "{$site}_{$opt['mode']}.{$opt['format']}";
    if (file_put_contents($fname, $output) === false) {
      $rc = 1;
    }
  }
  else {
    print $output;
  }
}

exit($rc);

// ----------------------------------------------------------------------
//  Data Aggregator
// ----------------------------------------------------------------------

function get_redist_data($db, $district = -1, $use_filter = true, $use_cache = true)
{
  $district_contact_data = [];

  // Get all value added out of district contacts
  $res = get_contacts($db, $district, $use_filter, $use_cache);
  while (($row = mysqli_fetch_assoc($res)) != null ) {
    $contact_id = $row['contact_id'];
    $district_contact_data[$contact_id] = $row;
  }
  mysqli_free_result($res);

  // Append the redistricting note to the contact
  $res = get_redist_notes($db, $district, $use_cache);
  while (($row = mysqli_fetch_assoc($res)) != null ) {
    $contact_id = $row['contact_id'];
    if (isset($district_contact_data[$contact_id])) {
      $district_contact_data[$contact_id]['note'] = $row['note'];
      $district_contact_data[$contact_id]['subject'] = $row['subject'];
      $district_contact_data[$contact_id]['prior_dist'] = get_former_district($row['note'], "-");
    }
  }
  mysqli_free_result($res);

  // Append the email counts to the contact
  $res = get_email_counts($db);
  while (($row = mysqli_fetch_assoc($res)) != null) {
    $contact_id = $row['contact_id'];
    if (isset($district_contact_data[$contact_id])) {
      $district_contact_data[$contact_id]['email_count'] = $row['email_count'];
      $district_contact_data[$contact_id]['active_email_count'] = $row['active_email_count'];

    }
  }
  mysqli_free_result($res);

  bbscript_log(LL::DEBUG, "Stored " . count($district_contact_data) . " contacts in memory");
  return $district_contact_data;
} // get_redist_data()


function initialize_counts()
{
  return [
    'contacts' => 0, 'individual' => 0, 'household' => 0, 'organization' => 0,
    'all_emails' => 0, 'active_emails' => 0,
    'all_cases' => 0, 'active_cases' => 0, 'inactive_cases' => 0,
    'open_cases' => 0, 'assigned_cases' => 0, 'urgent_cases' => 0,
    'all_activities' => 0, 'open_activities' => 0
  ];
} // initialize_counts()


// ----------------------------------------------------------------------
// Summary Reports - Provide basic counts for each district
// ----------------------------------------------------------------------

function process_summary_data($district_contact_data, $threshold = 0)
{
  $district_counts = [];

  foreach ($district_contact_data as $contact) {
    $district = $contact['district'];
    $contact_id = $contact['contact_id'];
    $contact_type = strtolower($contact['contact_type']);

    // Create an array to store district counts
    if (!isset($district_counts[$district])) {
      $district_counts[$district] = initialize_counts();
    }

    $district_counts[$district]['contacts']++;
    $district_counts[$district][$contact_type]++;
    $district_counts[$district]['all_emails'] += $contact['email_count'] ?? 0;
    $district_counts[$district]['active_emails'] += $contact['active_email_count'] ?? 0;
    $district_counts[$district]['all_cases'] += $contact['case_count'];
    $active_cases = $contact['open_case_count'] + $contact['assigned_case_count'] + $contact['urgent_case_count'];
    $district_counts[$district]['active_cases'] += $active_cases;
    $district_counts[$district]['inactive_cases'] += ($contact['case_count'] - $active_cases);
    $district_counts[$district]['open_cases'] += $contact['open_case_count'];
    $district_counts[$district]['assigned_cases'] += $contact['assigned_case_count'];
    $district_counts[$district]['urgent_cases'] += $contact['urgent_case_count'];
    $district_counts[$district]['all_activities'] += $contact['activity_count'];
    $district_counts[$district]['open_activities'] += $contact['open_activity_count'];
  }

  // Apply the threshold
  foreach ($district_counts as $dist => $counts) {
    if ($counts['contacts'] < $threshold) {
      unset($district_counts[$dist]);
    }
  }

  return $district_counts;
} // process_summary_data()


function compute_summary_totals($district_counts, $exclude_dist_zero = true)
{
  $total = initialize_counts();

  if ($exclude_dist_zero) {
    unset($district_counts[0]);
  }

  foreach ($district_counts as $counts) {
    $total['contacts'] += get($counts, 'contacts', 0);
    $total['individual'] += get($counts, 'individual', 0);
    $total['household'] += get($counts, 'household', 0);
    $total['organization'] += get($counts, 'organization', 0);
    $total['all_emails'] += get($counts,'emails',0);
    $total['active_emails'] += get($counts, 'active_emails', 0);
    $total['all_cases'] += get($counts, 'all_cases', 0);
    $total['active_cases'] += get($counts, 'active_cases', 0);
    $total['all_activities'] += get($counts, 'all_activities', 0);
    $total['open_activities'] += get($counts, 'open_activities', 0);
  }

  return $total;
} // compute_summary_totals()


// ----------------------------------------------------------------------
// Detail Reports - List all contacts outside the instance district    |
// ----------------------------------------------------------------------

// List all contact information per outside district
// Assumptions: State will just be 'NY' because we ignore out of state contacts.
function process_detail_data($district_contact_data, $threshold = 0)
{
  $contacts_per_dist = [];

  foreach ($district_contact_data as $contact) {
    $district = $contact['district'];
    $contact_type = strtolower($contact['contact_type']);

    // Build the array so that contacts are grouped by contact type per district
    if (!isset($contacts_per_dist[$district])) {
      $contacts_per_dist[$district] = [];
    }
    if (!isset($contacts_per_dist[$district][$contact_type])) {
      $contacts_per_dist[$district][$contact_type] = [];
    }

    $contacts_per_dist[$district][$contact_type][] = $contact;
  }

  // Apply the threshold
  foreach ($contacts_per_dist as $dist => $contact_types) {
    $contact_cnt = 0;
    foreach ($contact_types as $type => $contact_array) {
      $contact_cnt += count($contact_array);
    }
    if ($contact_cnt < $threshold) {
      unset($contacts_per_dist[$dist]);
    }
  }

  return $contacts_per_dist;
} // process_detail_data()


// ----------------------------------------------------------------------
// SQL Functions
// ----------------------------------------------------------------------

// Retrieve a list of contacts with counts of their cases, activities, etc.
//
// district: Return only contacts that are NOT in the given district.
// use_filter: If true, return only contacts that have value-added info.
// use_cache:  If true, store results in a cache table.
//
// Returns the result set from the mysql query
//
function get_contacts($db, $district = -1, $use_filter = true, $use_cache = true)
{
  $desc = ($use_filter ? "'value added'" : "all");
  bbscript_log(LL::DEBUG, "Fetching $desc contacts that are not in District $district...");

  if (!table_exists($db, RD_ACTS_CACHE_TABLE)) {
    bbscript_log(LL::INFO, "Creating and populating redist activities cache table");
    $act_query = "
      CREATE TABLE IF NOT EXISTS " . RD_ACTS_CACHE_TABLE . " (
        act_contact_id int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Unique Contact ID',
        activity_count bigint(24) NOT NULL DEFAULT '0',
        open_activity_count bigint(24) NOT NULL DEFAULT '0',
        PRIMARY KEY (act_contact_id)
      ) ENGINE=InnoDB

      SELECT c.id AS act_contact_id,
             # The activity count does not include case activities
             IFNULL(COUNT(DISTINCT act.id), 0) AS activity_count,
             # Also count the number of open activities
             IFNULL(SUM(act.status_id IN (1,7)), 0) AS open_activity_count

      FROM civicrm_contact c
      JOIN civicrm_address a ON c.id = a.contact_id
      JOIN civicrm_value_district_information_7 d ON a.id = d.entity_id
      LEFT JOIN civicrm_activity_contact ac ON ac.contact_id = c.id AND ac.record_type_id = 3
      LEFT JOIN civicrm_activity act ON act.id = ac.activity_id
      LEFT JOIN civicrm_case_activity ca ON ac.activity_id = ca.activity_id

      WHERE d.ny_senate_district_47 != $district
        AND a.is_primary = 1
        AND act.is_current_revision = 1
        AND act.is_deleted = 0
        AND act.is_test = 0
        AND c.is_deleted = 0
        AND ca.id IS NULL
        AND c.id != 1
      GROUP BY c.id
      ";
    bb_mysql_query($act_query, $db, true);
    bbscript_log(LL::INFO, "Finished creating redist activities cache table");
  }

  $contact_query = "
    SELECT * FROM (
      SELECT DISTINCT c.id AS contact_id, c.contact_type,
             c.first_name, c.last_name, c.birth_date, c.gender_id,
             c.household_name, c.organization_name, c.is_deceased, c.source,
             ci.contact_source_60 AS const_source, ANY_VALUE(p.phone) AS phone,
             ANY_VALUE(a.street_address) AS street_address,
             ANY_VALUE(a.city) AS city, ANY_VALUE(a.postal_code) AS postal_code,
             ANY_VALUE(e.email) AS email,
             ANY_VALUE(d.ny_senate_district_47) AS district,

             # All case counts and also a breakdown by case status
             COUNT(DISTINCT cc.id, NULLIF(cas.is_deleted, 1)) AS case_count,
             COUNT(DISTINCT cc.id, NULLIF(cas.is_deleted, 1), NULLIF(cas.status_id = 1, 0)) AS open_case_count,
             COUNT(DISTINCT cc.id, NULLIF(cas.is_deleted, 1), NULLIF(cas.status_id = 3, 0)) AS urgent_case_count,
             COUNT(DISTINCT cc.id, NULLIF(cas.is_deleted, 1), NULLIF(cas.status_id = 5, 0)) AS assigned_case_count,

             # Group Count
             COUNT(DISTINCT gc.group_id, NULLIF(gc.status, 'Removed') ) AS group_count

      FROM civicrm_contact c
      JOIN civicrm_address a ON c.id = a.contact_id AND a.is_primary = 1
      JOIN civicrm_value_district_information_7 d ON a.id = d.entity_id
      LEFT JOIN civicrm_value_constituent_information_1 ci ON c.id = ci.entity_id
      LEFT JOIN civicrm_email e ON c.id = e.contact_id AND e.is_primary = 1
      LEFT JOIN civicrm_phone p ON c.id = p.contact_id AND p.is_primary = 1

      # Counts of cases
      LEFT JOIN civicrm_case_contact cc ON c.id = cc.contact_id
      LEFT JOIN civicrm_case cas ON cas.id = cc.case_id

      # Counts of groups
      LEFT JOIN civicrm_group_contact gc ON c.id = gc.contact_id

      WHERE d.ny_senate_district_47 != $district
        AND a.is_primary = 1
        AND c.is_deleted = 0
        AND NOT (c.do_not_phone = 1 AND c.do_not_mail = 1 AND ( c.do_not_email = 1 OR c.is_opt_out = 1 ))
      GROUP BY contact_id
    ) AS c
    LEFT JOIN " . RD_ACTS_CACHE_TABLE . " acts_cache ON acts_cache.act_contact_id = c.contact_id
  ";

  // Filter criteria
  $contact_filter = "
    # Filter out contacts without relevant data or those that don't want to be contacted
    WHERE c.contact_type = 'Individual'
      AND NOT (
          IFNULL(c.const_source,'') = 'boe'
          AND c.is_deceased = 0
          AND case_count = 0
          AND activity_count = 0
          AND email IS NULL
          AND phone IS NULL

          # Check if contact has any non-boe addresses
          AND c.contact_id NOT IN (
            SELECT contact_id
            FROM civicrm_address
            WHERE location_type_id != 6 AND location_type_id != 13
          )

          # Check if contact has any non-default notes
          AND c.contact_id NOT IN (
            SELECT entity_id
            FROM civicrm_note
            WHERE entity_table = 'civicrm_contact'
              AND privacy = 0
              AND subject NOT LIKE 'OMIS%'
              AND subject NOT LIKE 'REDIST%'
          )
        )
    OR c.contact_type = 'Household'
    OR c.contact_type = 'Organization'
  ";

  // If filter option is true append filter criteria to query
  if ($use_filter) {
    $contact_query .= $contact_filter;
  }

  // If cache option is set, check to see if the cache table exists and
  // create it if necessary, then select the data from that table.
  if ($use_cache) {
    if (!table_exists($db, RD_CONTACT_CACHE_TABLE)) {
      bbscript_log(LL::INFO, "Creating and populating redist contact cache table");
      $contact_query = "CREATE TABLE " . RD_CONTACT_CACHE_TABLE . " AS ( $contact_query ); ";
      bb_mysql_query($contact_query, $db, true);
      bbscript_log(LL::INFO, "Finished creating redist contact cache table");
    }

    $contact_query = "SELECT * FROM " . RD_CONTACT_CACHE_TABLE;
  }

  $res = bb_mysql_query($contact_query, $db, true);
  $num_rows = mysqli_num_rows($res);
  bbscript_log(LL::DEBUG, "Retrieved $num_rows contacts");
  return $res;
} // get_contacts()


function get_redist_notes($db, $district = -1, $use_cache = true)
{
  bbscript_log(LL::DEBUG, 'Fetching redistricting notes...');
  $redist_tag = 'REDIST' . REDIST_YEAR;
  $note_query = "
    SELECT c.id AS contact_id, a.id AS address_id,
           d.ny_senate_district_47 AS district,
           n.note, n.subject, n.modified_date
    FROM civicrm_note n
    JOIN civicrm_contact c ON n.entity_id = c.id
    JOIN civicrm_address a ON c.id = a.contact_id
    JOIN civicrm_value_district_information_7 d ON a.id = d.entity_id
    WHERE a.is_primary = 1
      AND d.ny_senate_district_47 != $district
      AND n.entity_table = 'civicrm_contact'
      AND n.subject LIKE CONCAT('{$redist_tag}%[id=', a.id , ']%')
  ";

  if ($use_cache) {
    if (!table_exists($db, RD_NOTE_CACHE_TABLE)) {
      bbscript_log(LL::INFO, 'Creating and populating redist note cache table');
      $note_query = "CREATE TABLE " . RD_NOTE_CACHE_TABLE . " AS ( $note_query ); ";
      bb_mysql_query($note_query, $db, true);
      bbscript_log(LL::INFO, 'Finished creating redist note cache table');
    }

    $note_query = "SELECT * FROM " . RD_NOTE_CACHE_TABLE;
  }

  $res = bb_mysql_query($note_query, $db, true);
  $num_rows = mysqli_num_rows($res);

  bbscript_log(LL::DEBUG, "Retrieved {$num_rows} notes");
  return $res;
} // get_redist_notes()


// The summary page displays email counts per district. This includes
// non-primary email addresses. The following query returns the contact
// id and the number of email addresses which will be joined to the
// main data array.
function get_email_counts($db)
{
  $email_query = "
    SELECT c.id AS contact_id,
      COUNT(DISTINCT(e.email)) AS email_count,
      COUNT(DISTINCT e.email, NULLIF(c.do_not_email, 1), NULLIF(c.is_opt_out, 1), NULLIF(e.on_hold = 0, 0)) AS active_email_count
    FROM civicrm_contact c
    JOIN civicrm_email e ON c.id = e.contact_id
    GROUP BY c.id
  ";

  $res = bb_mysql_query($email_query, $db, true);
  $num_rows = mysqli_num_rows($res);
  bbscript_log(LL::DEBUG, "Retrieved {$num_rows} email records");
  return $res;
} // get_email_counts()


// ----------------------------------------------------------------------
// Cache Functions
// ----------------------------------------------------------------------
function clear_reports_cache($db)
{
  bbscript_log(LL::INFO, "Clearing redist report contact cache table");
  $drop = "DROP TABLE IF EXISTS " . RD_CONTACT_CACHE_TABLE .";\n";
  bb_mysql_query($drop, $db, true);

  bbscript_log(LL::INFO, "Clearing redist report note cache table");
  $drop = "DROP TABLE IF EXISTS " . RD_NOTE_CACHE_TABLE .";";
  bb_mysql_query($drop, $db, true);

  bbscript_log(LL::INFO, "Clearing redist act cache table");
  $drop = "DROP TABLE IF EXISTS " . RD_ACTS_CACHE_TABLE .";";
  bb_mysql_query($drop, $db, true);
} // clear_reports_cache()


// ----------------------------------------------------------------------
// Helper Functions
// ----------------------------------------------------------------------

// Checks the redist note to see if the address formerly belonged in the
// district specified by $district. $key refers to the district abbrv.
function is_former_district($note_subject, $district = 0, $key = 'SD')
{
  return preg_match("/".$key.":".$district."=>(\d{0,2})/i", $note_subject);
} // is_former_district()


// Return the former district if assigned
function get_former_district($note, $default = "N/A")
{
  $matches = [];
  preg_match("/SD:(\d{0,2}).{2}\d{0,2}/i", $note, $matches);
  if (count($matches) == 2 && $matches[1] != "") {
    return $matches[1];
  }
  return $default;
} // get_former_district()


// Create a table header given an array of column names as keys
// and widths as values
function create_table_header($columns, $border = '-', $separator = "|")
{
  $header = "";
  $total_width = 0;

  foreach ($columns as $name => $width) {
    $header .= fixed_width($name, $width - 1, true) . $separator;
    $total_width += $width;
  }

  $border_row = "";
  for ($i = 0; $i < $total_width; $i++) {
    $border_row .= $border;
  }

  $header = $border_row . "\n" . $header . "\n" . $border_row . "\n";
  return $header;
} // create_table_header()


function get($array, $key, $default)
{
  // blank, null, and 0 values are bad.
  return isset($array[$key]) && $array[$key] != null && $array[$key] !== "" && $array[$key] !== 0 && $array[$key] !== "000" ? $array[$key] : $default;
} // get()


// Pads the string to a certain length and chops off the rest on the right side
function fixed_width($string, $length = 10, $center = false, $default = '')
{
  $pad_type = STR_PAD_RIGHT;
  if ($center) {
    $pad_type = STR_PAD_BOTH;
  }
  if ($string == null || $string == "") {
    $string = $default;
  }
  return substr(str_pad($string, $length, ' ', $pad_type), 0, $length);
} // fixed_width()


function get_gender($value, $default = '-')
{
  if ($value == 1) {
    return "F";
  }
  else if ($value == 2) {
    return "M";
  }
  return $default;
} // get_gender()


function get_age($birth_date, $default = '-')
{
  if ($birth_date != null && $birth_date != "") {
    try {
      $b_date = new DateTime($birth_date);
      $today = new DateTime();
      $diff = $b_date->diff($today);
      return $diff->format("%y");
    }
    catch (Exception $e) {
      bbscript_log(LL::TRACE, "Failed to get age from date: $birth_date");
    }
  }
  return $default;
} // get_age()


function table_exists($db, $table_name)
{
  $res = bb_mysql_query("SHOW TABLES LIKE '" . $table_name . "'", $db, true);
  return (mysqli_num_rows($res) > 0);
} // table_exists()


function get_senator_name($district)
{
  global $senator_names;

  if (isset($senator_names[$district][0])) {
    return $senator_names[$district][0];
  }
  else {
    return 'Undecided';
  }
} // get_senator_name()


function get_senator_url($district)
{
  global $senator_names;

  $base_url = 'https://www.nysenate.gov/senators/';
  if (isset($senator_names[$district][1])) {
    return $base_url . $senator_names[$district][1];
  }
  else {
    return $base_url;
  }
} // get_senator_url()

