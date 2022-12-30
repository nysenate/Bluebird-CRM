<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2022-12-27 - Updated for new SAGE API calls

// ./Redistricting.php -S breslin --batch 2000 --log 5 --max 10000
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_BATCH_SIZE', 1000);
define('UPDATE_NOTES', 1);
define('UPDATE_DISTRICTS', 2);
define('UPDATE_ADDRESSES', 4);
define('UPDATE_GEOCODES', 8);
define('UPDATE_ALL', UPDATE_NOTES|UPDATE_DISTRICTS|UPDATE_ADDRESSES|UPDATE_GEOCODES);
define('REDIST_NOTE_TAG', 'REDIST2022');
define('REDIST_NOTE_PATTERN', 'REDIST____ ');
define('INSTATE_NOTE', 'IN-STATE');
define('OUTOFSTATE_NOTE', 'OUT-OF-STATE');
define('DISTRICT_TABLE', 'civicrm_value_district_information_7');
define('UPDATE_DATE_FIELD', 'last_import_57');

// Parse the following user options
require_once realpath(dirname(__FILE__)).'/../script_utils.php';
$shortopts = "ab:nGil:m:Nopf:g:s";
$longopts = [
  "addressmap",
  "batch=",
  "dryrun",
  "geocodeonly",
  "instate",
  "log=",
  "max=",
  "nonotes",
  "outofstate",
  "purgenotes",
  "startfrom=",
  "usegeocoder=",
  "useshapefiles"
];
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--addressmap] [--batch SIZE] [--dryrun] [--geocodeonly] [--instate] [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] [--max COUNT] [--nonotes] [--outofstate] [--purgenotes] [--startfrom ADDRESS_ID] [--usegeocoder {nysgeo|google|tiger}] [--useshapefiles]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Use user options to configure the script
set_bbscript_log_level(get($optlist, 'log', 'INFO'));
$BB_UPDATE_FLAGS = UPDATE_ALL;
$opt_addressmap = get($optlist, 'addressmap', false);
$opt_batch_size = get($optlist, 'batch', DEFAULT_BATCH_SIZE);
$opt_dry_run = get($optlist, 'dryrun', false);
$opt_geocode_only = get($optlist, 'geocodeonly', false);
$opt_instate = get($optlist, 'instate', false);
$opt_max = get($optlist, 'max', 0);
$opt_no_notes = get($optlist, 'nonotes', false);
$opt_outofstate = get($optlist, 'outofstate', false);
$opt_purgenotes = get($optlist, 'purgenotes', false);
$opt_startfrom = get($optlist, 'startfrom', 0);
$opt_usegeocoder = get($optlist, 'usegeocoder', '');
$opt_useshapefiles = get($optlist, 'useshapefiles', false);

// Use instance settings to configure for SAGE
$bbcfg = get_bluebird_instance_config($optlist['site']);
$sage_base = array_key_exists('sage.api.base', $bbcfg) ? $bbcfg['sage.api.base'] : false;
$sage_key = array_key_exists('sage.api.key', $bbcfg) ? $bbcfg['sage.api.key'] : false;
if (!($sage_base && $sage_key)) {
    error_log(bbscript_log(LL::FATAL, "sage.api.base and sage.api.key must be set in your bluebird.cfg file."));
    exit(1);
}

// Dump the active options when in debug mode
bbscript_log(LL::DEBUG, "Option: INSTANCE={$optlist['site']}");
bbscript_log(LL::DEBUG, "Option: SAGE_API=$sage_base");
bbscript_log(LL::DEBUG, "Option: SAGE_KEY=$sage_key");
bbscript_log(LL::DEBUG, "Option: ADDRESSMAP=".($opt_addressmap ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: BATCH_SIZE=$opt_batch_size");
bbscript_log(LL::DEBUG, "Option: DRY_RUN=".($opt_dry_run ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: GEOCODE_ONLY=".($opt_geocode_only ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: INSTATE=".($opt_instate ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: LOG_LEVEL={$optlist['log']}");
bbscript_log(LL::DEBUG, "Option: MAX=".($opt_max ? $opt_max : "NONE"));
bbscript_log(LL::DEBUG, "Option: NO_NOTES=".($opt_no_notes ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: OUTOFSTATE=".($opt_outofstate ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: PURGE_NOTES=".($opt_purgenotes ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: STARTFROM=".($opt_startfrom ? $opt_startfrom : "NONE"));
bbscript_log(LL::DEBUG, "Option: USE_GEOCODER=".($opt_usegeocoder ? $opt_usegeocoder : "FALSE"));
bbscript_log(LL::DEBUG, "Option: USE_SHAPEFILES=".($opt_useshapefiles ? "TRUE" : "FALSE"));

// District mappings for Notes, Distinfo, and SAGE
$FIELD_MAP = [
  'CD' => ['db'=>'congressional_district_46', 'sage'=>'congressional'],
  'SD' => ['db'=>'ny_senate_district_47', 'sage'=>'senate'],
  'AD' => ['db'=>'ny_assembly_district_48', 'sage'=>'assembly'],
  'ED' => ['db'=>'election_district_49', 'sage'=>'election'],
  'CO' => ['db'=>'county_50', 'sage'=>'county'],
  'CLEG' => ['db'=>'county_legislative_district_51', 'sage'=>'cleg'],
  'TOWN' => ['db'=>'town_52', 'sage'=>'town'],
  'WARD' => ['db'=>'ward_53', 'sage'=>'ward'],
  'SCHL' => ['db'=>'school_district_54', 'sage'=>'school'],
  'CC' => ['db'=>'new_york_city_council_55', 'sage'=>'cityCouncil'],
  'LAT' => ['db'=>'geo_code_1', 'sage'=>'lat'],
  'LON' => ['db'=>'geo_code_2', 'sage'=>'lon'],
];

$DIST_FIELDS = ['CD', 'SD', 'AD', 'ED', 'CO', 'CLEG', 'TOWN', 'WARD', 'SCHL', 'CC'];
$ADDR_FIELDS = ['LAT', 'LON'];
$NULLIFY_INSTATE = ['CD', 'SD', 'AD', 'ED'];
$NULLIFY_OUTOFSTATE = $DIST_FIELDS;

// Construct the url with all our options...
//$batch_url = "$sage_base/district/bluebird/batch?key=$sage_key&useGeocoder=".($opt_usegeocoder ? "1&geocoder=$opt_usegeocoder" : "0")."&useShapefiles=".($opt_useshapefiles ? 1 : 0);
$batch_url = "$sage_base/district/bluebird/batch";
bbscript_log(LL::DEBUG, "batch_url={$batch_url}");

// Track the full time it takes to run the redistricting process.
$script_start_time = microtime(true);

// Get CiviCRM database connection
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config = CRM_Core_Config::singleton();
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

if ($opt_dry_run) {
  $BB_UPDATE_FLAGS = 0;
}
elseif ($opt_geocode_only) {
  $BB_UPDATE_FLAGS = UPDATE_GEOCODES;
}

if ($opt_no_notes) {
  $BB_UPDATE_FLAGS &= ~UPDATE_NOTES;
}

if ($opt_purgenotes) {
  purge_notes($db);
}

// Map old district numbers to new district numbers if addressMap option is set
if ($opt_addressmap) {
  address_map($db);
}

if ($opt_outofstate) {
  handle_out_of_state($db);
}

if ($opt_instate) {
  handle_in_state($db, $batch_url, $opt_startfrom, $opt_batch_size, $opt_max);
}

$elapsed_time = round(get_elapsed_time($script_start_time), 3);
bbscript_log(LL::INFO, "Completed all tasks in $elapsed_time seconds.");
exit(0);


function purge_notes($db)
{
  global $BB_UPDATE_FLAGS;

  bbscript_log(LL::TRACE, "==> purge_notes()");

  if ($BB_UPDATE_FLAGS & UPDATE_NOTES) {
    // Remove any redistricting notes that already exist
    $q = "DELETE FROM civicrm_note
          WHERE entity_table='civicrm_contact'
          AND subject LIKE '".REDIST_NOTE_PATTERN."%'";
    bb_mysql_query($q, $db, true);
    $row_cnt = mysqli_affected_rows($db);
    bbscript_log(LL::INFO, "Removed all $row_cnt redistricting notes from the database.");
  }
  else {
    bbscript_log(LL::INFO, "UPDATE_NOTES disabled - No notes were deleted");
  }
  bbscript_log(LL::TRACE, "<== purge_notes()");
} // purge_notes()


function address_map($db)
{
  global $BB_UPDATE_FLAGS;

  bbscript_log(LL::TRACE, "==> address_map()");

  $address_map_changes = 0;
  bbscript_log(LL::INFO, "Mapping old district numbers to new district numbers");
  $district_cycle = [
    '3'=>'8', '11'=>'16', '16'=>'11', '17'=>'22', '22'=>'26', '26'=>'27',
    '27'=>'47', '39'=>'42', '44'=>'46', '46'=>'41', '47'=>'53', '49'=>'44',
    '53'=>'48', '59'=>'60', '60'=>'61'
  ];

  if ($BB_UPDATE_FLAGS & UPDATE_DISTRICTS) {
    bb_mysql_query('BEGIN', $db, true);
  }

  $q = "SELECT id, ny_senate_district_47 FROM ".DISTRICT_TABLE;
  $result = bb_mysql_query($q, $db, true);
  $num_rows = mysqli_num_rows($result);
  $actions = [];
  while (($row = mysqli_fetch_assoc($result)) != null) {
    $district = $row['ny_senate_district_47'];
    if (isset($district_cycle[$district])) {
      if ($BB_UPDATE_FLAGS & UPDATE_DISTRICTS) {
        $q = "UPDATE ".DISTRICT_TABLE."
              SET ny_senate_district_47 = {$district_cycle[$district]}
              WHERE id = {$row['id']};";
        bb_mysql_query($q, $db, true);
        $address_map_changes++;
        if ($address_map_changes % 1000 == 0) {
          bbscript_log(LL::DEBUG, "$address_map_changes mappings so far");
        }
      }

      if (isset($actions[$district])) {
        $actions[$district]++;
      }
      else {
        $actions[$district] = 1;
      }
    }
  }

  if ($BB_UPDATE_FLAGS & UPDATE_DISTRICTS) {
    bb_mysql_query('COMMIT', $db, true);
    bbscript_log(LL::INFO, "Completed district mapping with $address_map_changes changes");
  }
  else {
    bbscript_log(LL::INFO, "UPDATE_DISTRICTS disabled - No changes were made");
  }

  foreach ($actions as $district => $fix_count) {
    bbscript_log(LL::INFO, " $district => {$district_cycle[$district]}: $fix_count");
  }
  bbscript_log(LL::TRACE, "<== address_map()");
} // address_map()


function handle_out_of_state($db)
{
  global $BB_UPDATE_FLAGS;

  bbscript_log(LL::TRACE, "==> handle_out_of_state()");

  if ($BB_UPDATE_FLAGS & UPDATE_NOTES) {
    // Delete any out-of-state notes that already exist
    $q = "DELETE FROM civicrm_note
          WHERE entity_table='civicrm_contact'
          AND subject like '".REDIST_NOTE_PATTERN.OUTOFSTATE_NOTE."%'";
    bb_mysql_query($q, $db, true);
    $row_cnt = mysqli_affected_rows($db);
    bbscript_log(LL::TRACE, "Removed $row_cnt ".OUTOFSTATE_NOTE." notes");
  }
  else {
    bbscript_log(LL::TRACE, 'UPDATE_NOTES disabled - No notes were removed');
  }

  // Retrieve all out-of-state addresses with distinfo
  $result = retrieve_addresses($db, 0, 0, false);
  $total_outofstate = mysqli_num_rows($result);

  while ($row = mysqli_fetch_assoc($result)) {
    if ($BB_UPDATE_FLAGS & UPDATE_DISTRICTS) {
      $note_updates = nullify_district_info($db, $row, false);
      if ($BB_UPDATE_FLAGS & UPDATE_NOTES) {
        insert_redist_note($db, OUTOFSTATE_NOTE, 'NOLOOKUP', $row, null, $note_updates);
      }
    }
  }

  if ($BB_UPDATE_FLAGS & UPDATE_DISTRICTS) {
    bbscript_log(LL::INFO, "Completed nullifying districts for $total_outofstate out-of-state addresses.");
  }
  else {
    bbscript_log(LL::INFO, "UPDATE_DISTRICTS disabled - No updates were made to out-of-state addresses.");
  }
  bbscript_log(LL::TRACE, "<== handle_out_of_state()");
} // handle_out_of_state()


function handle_in_state($db, $url, $startfrom = 0, $batch_size, $max_addrs = 0)
{
  bbscript_log(LL::TRACE, "==> handle_in_state()");
  // Start a timer and a counter for results
  $time_start = microtime(true);
  $counters = [
    "TOTAL" => 0,
    "MATCH" => 0,
    "NOMATCH" => 0,
    "INVALID" => 0,
    "ERROR" => 0,
    "HOUSE" => 0,
    "STREET" => 0,
    "ZIP5" => 0,
    "SHAPEFILE" => 0,
    "CURL" => 0,
    "MYSQL" => 0,
  ];

  $start_id = $startfrom;
  $total_rec_cnt = 0;
  $batch_rec_cnt = $batch_size;  // to prime the while() loop

  bbscript_log(LL::INFO, "Beginning batch processing of address records");

  while ($batch_rec_cnt == $batch_size) {
    // If max specified, then possibly constrain the batch size
    if ($max_addrs > 0 && $max_addrs - $total_rec_cnt < $batch_size) {
      $batch_size = $max_addrs - $total_rec_cnt;
      if ($batch_size == 0) {
        bbscript_log(LL::DEBUG, "Max address count ($max_addrs) reached");
        break;
      }
    }

    // Retrieve a batch of in-state addresses with distinfo
    $res = retrieve_addresses($db, $start_id, $batch_size, true);
    $formatted_batch = [];
    $orig_batch = [];
    $batch_rec_cnt = mysqli_num_rows($res);

    if ($batch_rec_cnt == 0) {
      bbscript_log(LL::TRACE, "No more rows to retrieve");
      break;
    }

    bbscript_log(LL::DEBUG, "Query complete; about to fetch batch of $batch_rec_cnt records");

    while ($row = mysqli_fetch_assoc($res)) {
      $addr_id = $row['id'];
      $total_rec_cnt++;

      // Save the original row for later; we'll need it when saving.
      $orig_batch[$addr_id] = $row;

      // Format for the batch district assignment API
      $row = clean_row($row);

      /***********  Old street logic that is not being used ********
      // Attempt to fill in missing addresses with supplemental info
      $street = trim($row['street_name'].' '.$row['street_type']);
      if ($street == '') {
        if ($row['supplemental_address_1']) {
          $street = $row['supplemental_address_1'];
        }
        else if ($row['supplemental_address_2']) {
          $street = $row['supplemental_address_2'];
        }
      }

      // Remove any PO Box information from street address.
      if (preg_match('/^p\.?o\.?\s+(box\s+)?[0-9]+$/i', $street)) {
        $street = '';
      }
      ************ End of old street logic **********************/

      // Format the address for sage
      $formatted_batch[] = [
        'id' => $addr_id,
        'addr1' => $row['street_address'],
        'city' => $row['city'],
        'state' => $row['state'],
        'zip5' => $row['postal_code'],
      ];
    }

    bbscript_log(LL::DEBUG, "Done fetching record batch; sending to SAGE");

    // Send formatted addresses to SAGE for geocoding & district assignment
    $batch_results = distassign($formatted_batch, $url, $counters);

    bbscript_log(LL::DEBUG, "About to process batch results from SAGE");

    if ($batch_results && count($batch_results) > 0) {
      process_batch_results($db, $orig_batch, $batch_results, $counters);
      report_stats($total_rec_cnt, $counters, $time_start);
    }
    else {
      bbscript_log(LL::ERROR, "No batch results; skipping processing for address IDs starting at $start_id.");
    }

    $start_id = $addr_id + 1;
    bbscript_log(LL::INFO, "$total_rec_cnt address records fetched so far");
  }

  bbscript_log(LL::INFO, "Completed assigning districts to in-state addresses.");
  bbscript_log(LL::TRACE, "<== handle_in_state()");
} // handle_in_state()



function retrieve_addresses($db, $start_id = 0, $max_res = 0, $in_state = true)
{
  global $FIELD_MAP, $DIST_FIELDS;

  bbscript_log(LL::TRACE, "==> retrieve_addresses()");

  $limit_clause = ($max_res > 0 ? "LIMIT $max_res" : "");
  $state_compare_op = $in_state ? '=' : '!=';
  $dist_colnames = [];

  foreach ($DIST_FIELDS as $abbrev) {
    $dist_colnames[] = "di.".$FIELD_MAP[$abbrev]['db'];
  }

  $q = "
    SELECT a.id, a.contact_id,
      a.street_address, a.street_number, a.street_number_suffix,
      a.street_name, a.street_type, a.city, a.postal_code,
      a.supplemental_address_1, a.supplemental_address_2,
      a.geo_code_1, a.geo_code_2,
      sp.abbreviation AS state,
      di.id as district_id,
      ".implode(",\n", $dist_colnames)."
    FROM civicrm_address a
    JOIN civicrm_state_province sp
    LEFT JOIN ".DISTRICT_TABLE." di ON (di.entity_id = a.id)
    WHERE a.state_province_id=sp.id
      AND sp.abbreviation $state_compare_op 'NY'
      AND a.id >= $start_id
    ORDER BY a.id ASC
    $limit_clause
  ";

  // Run query to obtain a batch of addresses
  bbscript_log(LL::DEBUG, "Retrieving addresses starting at id $start_id with limit $max_res");
  bbscript_log(LL::TRACE, "SQL query:\n$q");
  $res = bb_mysql_query($q, $db, true);
  bbscript_log(LL::DEBUG, "Finished retrieving addresses");
  bbscript_log(LL::TRACE, "<== retrieve_addresses()");
  return $res;
} // retrieve_addresses()


function distassign(&$fmt_batch, $url, &$cnts)
{
  bbscript_log(LL::TRACE, "==> distassign()");

  // Attach the json data
  bbscript_log(LL::TRACE, "About to encode address batch in JSON");
  $json_batch = json_encode($fmt_batch);
//  $first_id = $fmt_batch[0]['id'];
//  file_put_contents("/tmp/addr_batch_{$first_id}.json", $json_batch);

  // Initialize the cURL request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_batch);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Content-length: ".strlen($json_batch)]);
  bbscript_log(LL::TRACE, "About to send API request to SAGE using cURL [url=$url]");
  $response = curl_exec($ch);

  // Record the timings for the request and close
  $curl_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

  $cnts['CURL'] += $curl_time;
  bbscript_log(LL::TRACE, "CURL: fetched in ".round($curl_time, 3)." seconds");
  curl_close($ch);

  // Return null on any kind of response error
  if ($response === null) {
    bbscript_log(LL::ERROR, "Failed to receive a CURL response");
    $results = null;
  }
  else {
    bbscript_log(LL::TRACE, "About to decode JSON response");
    $results = @json_decode($response, true);

    if ($results === null && json_last_error() !== JSON_ERROR_NONE) {
      bbscript_log(LL::ERROR, "Malformed JSON Response");
      bbscript_log(LL::DEBUG, "CURL DATA: $response");
      $results = null;
    }
    else if (!isset($results['results']) && !isset($results['total'])) {
      bbscript_log(LL::ERROR, "SAGE server encountered a problem: ", $results);
      $results = null;
    }
    else {
      bbscript_log(LL::DEBUG, "Received ".$results['total']." district assignment records from SAGE");
      $results = $results['results'];
    }
  }

  bbscript_log(LL::TRACE, "<== distassign()");
  return $results;
} // distassign()


function process_batch_results($db, &$orig_batch, &$batch_results, &$cnts)
{
  global $BB_UPDATE_FLAGS, $DIST_FIELDS, $ADDR_FIELDS;

  bbscript_log(LL::TRACE, '==> process_batch_results()');
  bbscript_log(LL::TRACE, 'Batch results:', $batch_results);

  $batch_cntrs = [
    'TOTAL'=>count($batch_results), 'MATCH'=>0,
    'HOUSE'=>0, 'STREET'=>0, 'ZIP5'=>0, 'SHAPEFILE'=>0,
    'NOMATCH'=>0, 'INVALID'=>0, 'ERROR'=>0, 'MYSQL'=>0,
    'STATUSOK'=>0, 'STATUSBAD'=>0
  ];

  $batch_start_time = microtime(true);

  $addr_ids = array_keys($orig_batch);
  $addr_lo_id = min($addr_ids);
  $addr_hi_id = max($addr_ids);

  if ($BB_UPDATE_FLAGS & UPDATE_NOTES) {
    // Delete all notes associated with the current address batch.
    delete_batch_notes($db, $addr_lo_id, $addr_hi_id);
  }
  else {
    bbscript_log(LL::INFO, "UPDATE_NOTES disabled - No notes were removed and none will be added");
  }

  // Iterate over all batch results and update Bluebird tables accordingly.
  bbscript_log(LL::DEBUG, "Updating ".count($batch_results)." records");

  bb_mysql_query('BEGIN', $db, true);

  foreach ($batch_results as $batch_res) {
    $address_id = $batch_res['address']['id'] ?? NULL;
    $match_source = $batch_res['source'] ?? NULL;
    $match_level = $batch_res['matchLevel'] ?? NULL;
    $status_code = $batch_res['statusCode'] ?? NULL;
    $message = $batch_res['description'] ?? NULL;
    $orig_rec = $orig_batch[$address_id];

    if ($status_code == 0) {
      $batch_cntrs['STATUSOK']++;
      if ($match_source == 'DistrictShapefile') {
        $match_level = 'SHAPEFILE';
      }
    }
    else {
      $batch_cntrs['STATUSBAD']++;
    }

    switch ($match_level) {
      case 'HOUSE':
      case 'STREET':
      case 'ZIP5':
      case 'SHAPEFILE':
        $batch_cntrs['MATCH']++;
        $batch_cntrs[$match_level]++;
        bbscript_log(LL::TRACE, "[MATCH - $match_level][$message] on record #$address_id");

        // Determine differences between original record and SAGE results.
        $changes = calculate_changes($DIST_FIELDS, $orig_rec, $batch_res['districts']);
        $subj_abbrevs = $changes['abbrevs'];
        $note_updates = $changes['notes'];
        $sql_updates = $changes['sqldata'];

        if (count($sql_updates) > 0) {
          if ($BB_UPDATE_FLAGS & UPDATE_DISTRICTS) {
            if ($orig_rec['district_id']) {
              update_district_info($db, $address_id, $sql_updates);
            }
            else {
              insert_district_info($db, $address_id, $sql_updates);
            }
          }
          else {
            bbscript_log(LL::TRACE, "UPDATE_DISTRICTS disabled - district information for id=$address_id not updated");
          }
        }

        // Shape file lookups can result in new/changed coordinates.
        if ($match_level == 'SHAPEFILE') {
          $changes = calculate_changes($ADDR_FIELDS, $orig_rec, $batch_res['geocode']);
          $geonote = [
            "GEO_QUALITY: {$batch_res['geocode']['quality']}",
            "GEO_METHOD: {$batch_res['geocode']['method']}"
          ];
          $note_updates = array_merge($note_updates, $changes['notes'], $geonote);
          $sql_updates = $changes['sqldata'];

          if (count($sql_updates) > 0) {
            if ($BB_UPDATE_FLAGS & UPDATE_GEOCODES) {
              update_geocodes($db, $address_id, $sql_updates);
            }
            else {
              bbscript_log(LL::TRACE, "UPDATE_GEOCODES disabled - Geocoordinates for id=$address_id not updated");
            }
          }
        }

        bbscript_log(LL::TRACE, "Change Notes: ", $note_updates);

        if ($BB_UPDATE_FLAGS & UPDATE_NOTES) {
          insert_redist_note($db, INSTATE_NOTE, $match_level, $orig_rec, $subj_abbrevs, $note_updates);
        }
        break;

      case 'NOMATCH':
      case 'INVALID':
        $batch_cntrs[$match_level]++;
        bbscript_log(LL::WARN, "[NOMATCH][$message] on record #$address_id");
        if ($BB_UPDATE_FLAGS & UPDATE_DISTRICTS) {
          $note_updates = nullify_district_info($db, $orig_rec, true);
          if ($BB_UPDATE_FLAGS & UPDATE_NOTES) {
            insert_redist_note($db, INSTATE_NOTE, $match_level, $orig_rec,
                               null, $note_updates);
          }
        }
        else {
          bbscript_log(LL::TRACE, "UPDATE_DISTRICTS disabled - Cannot nullify district info for id=$address_id");
        }
        break;

      default:
        $batch_cntrs['ERROR']++;
        bbscript_log(LL::ERROR, "Unknown status [$match_level] on record #$address_id with message [$message]");
    }
  }

  bb_mysql_query('COMMIT', $db, true);
  $batch_cntrs['MYSQL'] = round(get_elapsed_time($batch_start_time), 4);
  bbscript_log(LL::TRACE, "Updated database in {$batch_cntrs['MYSQL']} secs");

  bbscript_log(LL::INFO, "Stats for current batch:");

  foreach ($batch_cntrs as $key => $val) {
    $cnts[$key] += $val;
    bbscript_log(LL::INFO, "  $key = $val [total={$cnts[$key]}]");
  }
  
  bbscript_log(LL::TRACE, '<== process_batch_results()');
} // process_batch_results()


function delete_batch_notes($db, $lo_id, $hi_id)
{
  bbscript_log(LL::TRACE, "==> delete_batch_notes()");

  // Delete only notes in the current batch
  $q = "
    DELETE FROM n USING civicrm_note n
    JOIN civicrm_address a ON n.entity_id = a.contact_id
    WHERE a.id BETWEEN $lo_id AND $hi_id
      AND n.subject LIKE '".REDIST_NOTE_PATTERN.INSTATE_NOTE."%'
      AND preg_capture('/[[]id=([0-9]+)[]]/', n.subject, 1) BETWEEN $lo_id AND $hi_id
  ";
  bb_mysql_query($q, $db, true);
  $row_cnt = mysqli_affected_rows($db);
  bbscript_log(LL::INFO, "Removed $row_cnt notes for address IDs from $lo_id to $hi_id");
  bbscript_log(LL::TRACE, "<== delete_batch_notes()");
} // delete_batch_notes()


// Determine the differences, value-by-value, between an augmented
// Bluebird address record (address + distinfo) and the SAGE
// response after distassigning and/or geocoding that record.
function calculate_changes(&$fields, &$db_rec, &$sage_rec)
{
  global $FIELD_MAP, $NULLIFY_INSTATE;

  $changes = ['notes'=> [], 'abbrevs'=> [], 'sqldata'=> []];

  foreach ($fields as $abbr) {
    $dbfld = $FIELD_MAP[$abbr]['db'];
    $sagefld = $FIELD_MAP[$abbr]['sage'];
    $db_val = get($db_rec, $dbfld, 'NULL');
    $sage_val = get($sage_rec, $sagefld, 'NULL');
    if (is_array($sage_val)) {
      $sage_val = get($sage_val, 'district', 'NULL');
    }

    if ($db_val != $sage_val) {
      if ($sage_val != 'NULL' || in_array($abbr, $NULLIFY_INSTATE)) {
        // If the SAGE value for the current field is "null" (and the original
        // value was not null), then the field will be nullified only if it's
        // one of the four primary district fields (CD, SD, AD, or ED).
        if ($sage_val == 'NULL') {
          $sage_val = 0;
        }
        $changes['abbrevs'][] = $abbr;
        $changes['sqldata'][$dbfld] = $sage_val;
        $changes['notes'][] = "$abbr:$db_val=>$sage_val";
      }
      else {
        $changes['notes'][] = "$abbr:$db_val~=$db_val";
      }
    }
    else {
      $changes['notes'][] = "$abbr:$db_val==$sage_val";
    }
  }

  return $changes;
} // calculate_changes()


function update_district_info($db, $address_id, $sqldata)
{
  // If the last_import_57 field is not included, then set it to be
  // the current date at midnight.
  if (!array_key_exists(UPDATE_DATE_FIELD, $sqldata)) {
    $sqldata[UPDATE_DATE_FIELD] = "CURDATE()";
  }

  $sql_updates = [];
  foreach ($sqldata as $colname => $value) {
    if (is_numeric($value)) {
      $sql_updates[] = "$colname = $value";
    }
    else {
      $sql_updates[] = "$colname = '$value'";
    }
  }

  $q = "
    UPDATE ".DISTRICT_TABLE." di
    SET ".implode(', ', $sql_updates)."
    WHERE di.entity_id = $address_id
  ";
  bb_mysql_query($q, $db, true);
} // update_district_info()


function insert_district_info($db, $address_id, $sqldata)
{
  // If the last_import_57 field is not included, then set it to be
  // the current date at midnight.
  if (!array_key_exists(UPDATE_DATE_FIELD, $sqldata)) {
    $sqldata[UPDATE_DATE_FIELD] = "CURDATE()";
  }

  $cols = 'entity_id';
  $vals = "$address_id";

  foreach ($sqldata as $colname => $value) {
    $cols .= ", $colname";
    if (is_numeric($value)) {
      $vals .= ", $value";
    }
    else {
      $vals .= ", '$value'";
    }
  }

  $q = "
    INSERT INTO ".DISTRICT_TABLE." ( $cols )
    VALUES ( $vals )
  ";
  bb_mysql_query($q, $db, true);
} // insert_district_info()


function nullify_district_info($db, $row, $instate = true)
{
  global $FIELD_MAP, $NULLIFY_INSTATE, $NULLIFY_OUTOFSTATE;

  $sql_updates = [];
  $note_updates = [];
  $dist_abbrevs = ($instate ? $NULLIFY_INSTATE : $NULLIFY_OUTOFSTATE);

  foreach ($dist_abbrevs as $abbrev) {
    $colname = $FIELD_MAP[$abbrev]['db'];
    $sql_updates[$colname] = 0;
    $note_updates[] = "$abbrev:".get($row, $colname, 'NULL')."=>0";
  }

  if ($row['district_id']) {
    update_district_info($db, $row['id'], $sql_updates);
  }
  else {
    insert_district_info($db, $row['id'], $sql_updates);
  }
  return $note_updates;
} // nullify_district_info()


function update_geocodes($db, $address_id, $sqldata)
{
  $sql_updates = [];
  foreach ($sqldata as $colname => $value) {
    $sql_updates[] = "$colname = $value";
  }

  $update_str = implode(', ', $sql_updates);
  bbscript_log(LL::TRACE, "Saving new geocoordinates: $update_str");
  $q = "
    UPDATE civicrm_address
    SET $update_str
    WHERE id=$address_id
  ";
  bb_mysql_query($q, $db, true);
} // update_geocodes()


function insert_redist_note($db, $note_type, $match_type, &$row, $abbrevs, &$update_notes)
{
  // Create a new contact note describing the state before
  // and after redistricting.
  $addr_id = $row['id'];
  $contact_id = $row['contact_id'];

  if (!$contact_id) {
    bbscript_log(LL::WARN, "No contact ID for address record id=$addr_id; unable to create an $note_type [$match_type] note");
    return;
  }

  $note = "== ".REDIST_NOTE_TAG." ==\n".
          "ADDRESS_ID: $addr_id\n".
          "NOTE_TYPE: $note_type\n".
          "MATCH_TYPE: $match_type\n".
          "ADDRESS: ".$row['street_address'].", ".$row['postal_code']."\n";

  if ($update_notes && is_array($update_notes)) {
    $note .= "UPDATES:\n".implode("\n", $update_notes);
  }

  $subj_ext = '';
  if ($note_type == OUTOFSTATE_NOTE || $match_type == 'NOMATCH'
      || $match_type == 'INVALID' || $match_type == 'NOLOOKUP') {
    $action = 'NULLIFIED';
  }
  else if ($abbrevs && count($abbrevs) > 0) {
    $action = 'UPDATED';
    $subj_ext = ": ".implode(',', $abbrevs);
  }
  else {
    $action = 'VERIFIED';
  }
  
  $subject = REDIST_NOTE_TAG." $note_type $action [id=$addr_id]$subj_ext";

  $note = mysqli_real_escape_string($db, $note);
  $subject = mysqli_real_escape_string($db, $subject);
  $q = "
    INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
    VALUES ('civicrm_contact', $contact_id, '$note', 1, '".date("Y-m-d")."', '$subject', 0)
  ";
  bb_mysql_query($q, $db, true);
} // insert_redist_note()


function report_stats($total_found, $cnts, $time_start)
{
  bbscript_log(LL::TRACE, "==> report_stats()");

  // Compute percentages for certain counts
  $percent = [
    "MATCH" => 0,
    "NOMATCH" => 0,
    "INVALID" => 0,
    "ERROR" => 0,
    "HOUSE" => 0,
    "STREET" => 0,
    "ZIP5" => 0,
    "SHAPEFILE" => 0,
    "STATUSOK" => 0
  ];

  // Timer for debug
  $time = get_elapsed_time($time_start);
  $records_per_sec = round($cnts['TOTAL'] / $time, 1);
  $mysql_per_sec = ($cnts['MYSQL'] == 0 ) ? 0 : round($cnts['TOTAL'] / $cnts['MYSQL'], 1);
  $curl_per_sec = ($cnts['CURL'] == 0 ) ? 0 : round($cnts['TOTAL'] / $cnts['CURL'], 1);

  // Update the percentages using the counts
  foreach ($percent as $key => $value) {
    $percent[$key] = round($cnts[$key] / $cnts['TOTAL'] * 100, 2);
  }

  $seconds_left = round(($total_found - $cnts['TOTAL']) / $records_per_sec, 0);
  $finish_at = date('Y-m-d H:i:s', (time() + $seconds_left));

  bbscript_log(LL::INFO, "-------  ------- ---- ---- ---- ---- ");
  bbscript_log(LL::INFO, "[DONE @]      $finish_at (in ".intval($seconds_left/60).":".($seconds_left%60).")");
  bbscript_log(LL::INFO, "[COUNT]      {$cnts['TOTAL']}");
  bbscript_log(LL::INFO, "[TIME]       ".round($time, 4));
  bbscript_log(LL::INFO, "[SPEED]  [TOTAL] $records_per_sec per second (".$cnts['TOTAL']." in ".round($time, 3).")");
  bbscript_log(LL::TRACE, "[SPEED]  [MYSQL] $mysql_per_sec per second (".$cnts['TOTAL']." in ".round($cnts['MYSQL'], 3).")");
  bbscript_log(LL::TRACE, "[SPEED]  [CURL] $curl_per_sec per second (".$cnts['TOTAL']." in ".round($cnts['CURL'], 3).")");
  bbscript_log(LL::INFO, "[MATCH]  [TOTAL] {$cnts['MATCH']} ({$percent['MATCH']} %)");
  bbscript_log(LL::INFO, "[MATCH]  [HOUSE] {$cnts['HOUSE']} ({$percent['HOUSE']} %)");
  bbscript_log(LL::INFO, "[MATCH]  [STREET] {$cnts['STREET']} ({$percent['STREET']} %)");
  bbscript_log(LL::INFO, "[MATCH]  [ZIP5]  {$cnts['ZIP5']} ({$percent['ZIP5']} %)");
  bbscript_log(LL::INFO, "[MATCH]  [SHAPE] {$cnts['SHAPEFILE']} ({$percent['SHAPEFILE']} %)");
  bbscript_log(LL::INFO, "[MATCH]  [STATUS] {$cnts['STATUSOK']} ({$percent['STATUSOK']} %)");
  bbscript_log(LL::INFO, "[NOMATCH] [TOTAL] {$cnts['NOMATCH']} ({$percent['NOMATCH']} %)");
  bbscript_log(LL::INFO, "[INVALID] [TOTAL] {$cnts['INVALID']} ({$percent['INVALID']} %)");
  bbscript_log(LL::INFO, "[ERROR]  [TOTAL] {$cnts['ERROR']} ({$percent['ERROR']} %)");
  bbscript_log(LL::TRACE, "<== report_stats()");
} // report_stats()



function clean_row($row)
{
  $match = [
    '/ AVENUE( EXT)?$/',
    '/ STREET( EXT)?$/',
    '/ PLACE/',
    '/ EAST$/',
    '/ WEST$/',
    '/ SOUTH$/',
    '/ NORTH$/',
    '/^EAST (?!ST|AVE|RD|DR)/',
    '/^WEST (?!ST|AVE|RD|DR)/',
    '/^SOUTH (?!ST|AVE|RD|DR)/',
    '/^NORTH (?!ST|AVE|RD|DR)/'
  ];

  $replace = [
    ' AVE$1',
    ' ST$1',
    ' PL',
    ' E',
    ' W',
    ' S',
    ' N',
    'E ',
    'W ',
    'S ',
    'N '
  ];

  $s = preg_replace("/[.,']/", "", strtoupper(trim($row['street_name'])));
  $row['street_name'] = preg_replace($match, $replace, $s);

  $s = preg_replace("/[.,']/", "", strtoupper(trim($row['street_type'])));
  $row['street_type'] = preg_replace($match, $replace, $s);
  return $row;
} // clean_row()


function get($array, $key, $default)
{
  // blank, null, and 0 values are bad.
  if (isset($array[$key]) && $array[$key] != null && $array[$key] !== ''
    && $array[$key] !== 0 && $array[$key] !== '0'
    && $array[$key] !== '00' && $array[$key] !== '000'
  ) {
    return $array[$key];
  }
  else {
    return $default;
  }
} // get()
