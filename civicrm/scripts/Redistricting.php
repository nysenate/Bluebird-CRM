<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-12-17

// ./Redistricting.php -S skelos --batch 2000 --log 5 --max 10000
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_BATCH_SIZE', 1000);
define('DEFAULT_THREADS', 3);

// Parse the following user options
require_once 'script_utils.php';
$shortopts = "b:l:m:f:naoig:sct:p";
$longopts = array("batch=", "log=", "max=", "startfrom=", "dryrun", "addressmap", "outofstate", "instate", "usegeocoder=", "useshapefiles", "usecoordinates", "threads=", "purgenotes");
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--batch SIZE] [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] [--max COUNT] [--startfrom ADDRESS_ID] [--dryrun] [--purgenotes] [--addressmap] [--outofstate] [--instate] [--threads COUNT] [--usegeocoder NAME] [--useshapefiles] [--usecoordinates]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Use user options to configure the script
$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper(get($optlist, 'log', 'TRACE'))][0];
$opt_batch_size = get($optlist, 'batch', DEFAULT_BATCH_SIZE);
$BB_DRY_RUN = get($optlist, 'dryrun', false);
$opt_max = get($optlist, 'max', 0);
$opt_startfrom = get($optlist, 'startfrom', 0);
$opt_outofstate = get($optlist, 'outofstate', false);
$opt_addressmap = get($optlist, 'addressmap', false);
$opt_instate = get($optlist, 'instate', false);
$opt_usegeocoder = get($optlist, 'usegeocoder', '');
$opt_useshapefiles = get($optlist, 'useshapefiles', false);
$opt_usecoordinates = get($optlist, 'usecoordinates', false);
$opt_threads = get($optlist, 'threads', DEFAULT_THREADS);
$opt_purgenotes = get($optlist, 'purgenotes', false);

// Use instance settings to configure for SAGE
$bbcfg = get_bluebird_instance_config($optlist['site']);
$sage_base = array_key_exists('sage.api.base', $bbcfg) ? $bbcfg['sage.api.base'] : false;
$sage_key = array_key_exists('sage.api.key', $bbcfg) ? $bbcfg['sage.api.key'] : false;
if (!($sage_base && $sage_key)) {
    error_log(bbscript_log("fatal", "sage.api.base and sage.api.key must be set in your bluebird.cfg file."));
    exit(1);
}

// Dump the active options when in debug mode
bbscript_log("DEBUG", "Option: INSTANCE={$optlist['site']}");
bbscript_log("DEBUG", "Option: BATCH_SIZE=$opt_batch_size");
bbscript_log("DEBUG", "Option: LOG_LEVEL=$BB_LOG_LEVEL");
bbscript_log("DEBUG", "Option: DRY_RUN=".($BB_DRY_RUN ? "TRUE" : "FALSE"));
bbscript_log("DEBUG", "Option: SAGE_API=$sage_base");
bbscript_log("DEBUG", "Option: SAGE_KEY=$sage_key");
bbscript_log("DEBUG", "Option: INSTATE=".($opt_instate ? "TRUE" : "FALSE"));
bbscript_log("DEBUG", "Option: OUTOFSTATE=".($opt_outofstate ? "TRUE" : "FALSE"));
bbscript_log("DEBUG", "Option: ADDRESSMAP=".($opt_addressmap ? "TRUE" : "FALSE"));
bbscript_log("DEBUG", "Option: STARTFROM=".($opt_startfrom ? $opt_startfrom : "NONE"));
bbscript_log("DEBUG", "Option: MAX=".($opt_max ? $opt_max : "NONE"));
bbscript_log("DEBUG", "Option: USE_SHAPEFILES=".($opt_useshapefiles ? "TRUE" : "FALSE"));
bbscript_log("DEBUG", "Option: USE_COORDINATES=".($opt_usecoordinates ? "TRUE" : "FALSE"));
bbscript_log("DEBUG", "Option: THREADS=$opt_threads");
bbscript_log("DEBUG", "Option: USE_GEOCODER=".($opt_usegeocoder ? $opt_usegeocoder : "FALSE"));

// Construct the url with all our options...
$bulkdistrict_url = "$sage_base/json/bulkdistrict/body?threadCount=$opt_threads&key=$sage_key&useGeocoder=".($opt_usegeocoder ? "1&geocoder=$opt_usegeocoder" : "0")."&useShapefiles=".($opt_useshapefiles ? 1 : 0);

// Get CiviCRM database connection
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config =& CRM_Core_Config::singleton();
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

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
    handle_in_state($db, $opt_startfrom, $opt_batch_size, $opt_max,
                    $bulkdistrict_url, $opt_usecoordinates);
}

bbscript_log("INFO", "Completed all tasks");
exit(0);



function purge_notes($db)
{
  global $BB_DRY_RUN;

  bbscript_log("TRACE", "==> purge_notes()");

  if (!$BB_DRY_RUN) {
    // Remove any redistricting notes that already exist
    $q = "DELETE FROM civicrm_note
          WHERE entity_table='civicrm_contact'
          AND subject LIKE 'RD12%'";
    bb_mysql_query($q, $db, true);
    $row_cnt = mysql_affected_rows($db);
    bbscript_log("INFO", "Removed all $row_cnt redistricting notes from the database.");
  }
  else {
    bbscript_log("INFO", "DRYRUN mode enabled - No notes were deleted");
  }
  bbscript_log("TRACE", "<== purge_notes()");
} // purge_notes()



function address_map($db)
{
  global $BB_DRY_RUN;

  bbscript_log("TRACE", "==> address_map()");

  $address_map_changes = 0;
  bbscript_log("INFO", "Mapping old district numbers to new district numbers");
  $district_cycle = array(
    '17'=>18, '18'=>25, '25'=>26, '26'=>28, '27'=>17, '28'=>29, '29'=>27,
    '44'=>49, '46'=>44, '49'=>53, '53'=>58, '58'=>63
  );

  if (!$BB_DRY_RUN) {
    bb_mysql_query("BEGIN", $db, true);
  }

  $q = "SELECT id, ny_senate_district_47
        FROM civicrm_value_district_information_7";
  $result = bb_mysql_query($q, $db, true);
  $num_rows = mysql_num_rows($result);
  $actions = array();
  while (($row = mysql_fetch_assoc($result)) != null) {
    $district = $row['ny_senate_district_47'];
    if (isset($district_cycle[$district])) {
      if (!$BB_DRY_RUN) {
        $q = "UPDATE civicrm_value_district_information_7
              SET ny_senate_district_47 = {$district_cycle[$district]}
              WHERE id = {$row['id']};";
        bb_mysql_query($q, $db, true);
        $address_map_changes++;
        if ($address_map_changes % 1000 == 0) {
          bbscript_log("DEBUG", "$address_map_changes mappings so far");
        }
      }

      if (isset($actions[$district])) {
        $actions[$district]++;
      } else {
        $actions[$district] = 1;
      }
    }
  }

  if (!$BB_DRY_RUN) {
    bb_mysql_query("COMMIT", $db, true);
    bbscript_log("INFO", "Completed district mapping with $address_map_changes changes");
  }
  else {
    bbscript_log("INFO", "DRYRUN mode enabled - No changes were made");
  }

  foreach ($actions as $district => $fix_count) {
    bbscript_log("INFO", " $district => {$district_cycle[$district]}: $fix_count");
  }
  bbscript_log("TRACE", "<== address_map()");
} // address_map()



function handle_out_of_state($db)
{
  global $BB_DRY_RUN;

  bbscript_log("TRACE", "==> handle_out_of_state()");

  if (!$BB_DRY_RUN) {
    // Delete any `Removed Districts` notes that already exist
    $q = "DELETE FROM civicrm_note
          WHERE entity_table='civicrm_contact'
          AND subject='RD12 REMOVED DISTRICTS'";
    bb_mysql_query($q, $db, true);
  }

  // Remove AD, SD, CD info for any non-NY state addresses
  $q = "SELECT a.id, a.contact_id, di.id as district_id, ny_senate_district_47, ny_assembly_district_48, congressional_district_46
        FROM civicrm_address a
        LEFT JOIN civicrm_state_province sp ON (a.state_province_id=sp.id)
        LEFT JOIN civicrm_value_district_information_7 di ON (di.entity_id=a.id)
        WHERE sp.abbreviation!='NY'";
  $result = bb_mysql_query($q, $db, true);
  $total_outofstate = mysql_num_rows($result);

  while (($row = mysql_fetch_assoc($result)) != null) {
    $note = "ADDRESS_ID: {$row['id']}\n".
            "UPDATES:\n".
            " SD:".get($row, 'ny_senate_district_47', "NULL")."=>0\n".
            " CD:".get($row,'congressional_dstrict_46', "NULL")."=>0\n".
            " AD:".(empty($row['ny_assembly_district_48']) ? "NULL" : $row['ny_assembly_district_48'])."=>0";
    $subject = "RD12 REMOVED DISTRICTS";

    if (!$BB_DRY_RUN) {
      $q = "INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
            VALUES ('civicrm_contact', {$row['contact_id']}, '$note', 1, '".date("Y-m-d")."', '$subject', 0)";
      bb_mysql_query($q, $db, true);

      if ($row['district_id'] == null) {
        $q = "INSERT INTO civicrm_value_district_information_7
              (entity_id, congressional_district_46, ny_senate_district_47, ny_assembly_district_48)
              VALUES ({$row['id']}, 0, 0, 0)";
        bb_mysql_query($q, $db, true);
      }
      else {
        // Set district information to zero.
        $q = "UPDATE civicrm_value_district_information_7 di
              SET congressional_district_46 = 0,
                  ny_senate_district_47 = 0,
                  ny_assembly_district_48 = 0
              WHERE di.entity_id = {$row['id']}";
        bb_mysql_query($q, $db, true);
      }
    }
  }

  if (!$BB_DRY_RUN) {
    bbscript_log("INFO", "Completed removing districts from $total_outofstate out-of-state addresses.");
  }
  else {
    bbscript_log("INFO", "DRYRUN mode enabled - No updates were made to out-of-state addresses.");
  }
  bbscript_log("TRACE", "<== handle_out_of_state()");
} // handle_out_of_state()



function handle_in_state($db, $startfrom = 0, $batch_size, $max_addrs = 0,
             $bulkdistrict_url, $use_coords)
{
  bbscript_log("TRACE", "==> handle_in_state()");
  // Start a timer and a counter for results
  $time_start = microtime(true);
  $counters = array("TOTAL" => 0,
                    "MATCH" => 0,
                    "NOMATCH" => 0,
                    "INVALID" => 0,
                    "ERROR" => 0,
                    "HOUSE" => 0,
                    "STREET" => 0,
                    "ZIP5" => 0,
                    "SHAPEFILE" => 0,
                    "CURL" => 0,
                    "MYSQL" => 0);

  $start_id = $startfrom;
  $total_rec_cnt = 0;
  $batch_rec_cnt = $batch_size;  // to prime the while() loop

  bbscript_log("INFO", "Beginning batch processing of address records");

  while ($batch_rec_cnt == $batch_size) {
    // If max specified, then possibly constrain the batch size
    if ($max_addrs > 0 && $max_addrs - $total_rec_cnt < $batch_size) {
      $batch_size = $max_addrs - $total_rec_cnt;
      if ($batch_size == 0) {
        bbscript_log("DEBUG", "Max address count ($max_addrs) reached");
        break;
      }
    }

    $mysql_result = retrieve_addresses($db, $start_id, $batch_size);
    $formatted_batch = array();
    $orig_batch = array();
    $batch_rec_cnt = mysql_num_rows($mysql_result);

    if ($batch_rec_cnt == 0) {
      bbscript_log("TRACE", "No more rows to retrieve");
      break;
    }

    bbscript_log("DEBUG", "Query complete; about to fetch batch of $batch_rec_cnt records");

    while ($row = mysql_fetch_assoc($mysql_result)) {
      $addr_id = $row['id'];
      $total_rec_cnt++;

      // Save the original row for later; we'll need it when saving.
      $orig_batch[$addr_id] = $row;

      // Format for the bulkdistrict API
      $row = clean_row($row);

      // Attempt to fill in missing addresses with supplemental info
      $street = trim($row['street_name'].' '.$row['street_type']);
      if ($street == '') {
        if ($row['supplemental_address_1']) {
          $street = $row['supplemental_address_1'];
        } else if ($row['supplemental_address_2']) {
          $street = $row['supplemental_address_2'];
        }
      }

      if (preg_match('/^p\.?o\.?\s+(box\s+)?[0-9]+$/i', $street)) {
        $po_boxes_skipped++;
        $street = '';
      }

      // Format the address for sage
      $formatted_batch[$addr_id]= array(
        'street' => $street,
        'town' => $row['city'],
        'state' => $row['state'],
        'zip5' => $row['postal_code'],
        'apt' => null,
        'building' => $row['street_number'],
        'building_chr' => $row['street_number_suffix'],
      );

      // If requested, use the coordinates already in the system
      if ($use_coords) {
        $formatted_batch[$addr_id]['latitude'] = $row['geo_code_1'];
        $formatted_batch[$addr_id]['longitude'] = $row['geo_code_2'];
      }
    }

    bbscript_log("DEBUG", "Done fetching record batch; sending to SAGE");

    // Send formatted addresses to SAGE for geocoding & district assignment
    $batch_results = distassign($formatted_batch, $bulkdistrict_url, $counters);

    bbscript_log("DEBUG", "About to process batch results from SAGE");

    if ($batch_results && count($batch_results) > 0) {
      process_batch_results($db, $orig_batch, $batch_results, $counters);
      report_stats($total_rec_cnt, $counters, $time_start);
    }
    else {
      bbscript_log("ERROR", "No batch results; skipping processing for address IDs starting at $start_id.");
    }

    $start_id = $addr_id + 1;
    bbscript_log("INFO", "$total_rec_cnt address records fetched so far");
  }

  bbscript_log("INFO", "Completed assigning districts to in-state addresses.");
  bbscript_log("TRACE", "<== handle_in_state()");
} // handle_in_state()



function retrieve_addresses($db, $start_id = 0, $max_res = DEFAULT_BATCH_SIZE)
{
  bbscript_log("TRACE", "==> retrieve_addresses()");
  $q = "SELECT a.id, a.contact_id,
               a.street_address, a.street_number, a.street_number_suffix,
               a.street_name, a.street_type, a.city, a.postal_code,
               a.supplemental_address_1, a.supplemental_address_2,
               a.geo_code_1, a.geo_code_2,
               sp.abbreviation AS state,
               di.id as district_id,
               di.county_50,
               di.county_legislative_district_51,
               di.congressional_district_46,
               di.ny_senate_district_47,
               di.ny_assembly_district_48,
               di.election_district_49,
               di.town_52,
               di.ward_53,
               di.school_district_54,
               di.new_york_city_council_55,
               di.neighborhood_56,
               di.last_import_57
     FROM civicrm_address a
     JOIN civicrm_state_province sp
     LEFT JOIN civicrm_value_district_information_7 di ON (di.entity_id = a.id)
     WHERE a.state_province_id=sp.id
       AND sp.abbreviation='NY'
       AND a.id >= $start_id
     ORDER BY a.id ASC
     LIMIT $max_res";

  // Run query to obtain a batch of addresses
  bbscript_log("DEBUG", "Retrieving addresses starting at id $start_id with limit $max_res");
  bbscript_log("TRACE", "SQL query:\n$q");
  $res = bb_mysql_query($q, $db, true);
  bbscript_log("DEBUG", "Finished retrieving addresses");
  bbscript_log("TRACE", "<== retrieve_addresses()");
  return $res;
} // retrieve_addresses()



function distassign(&$fmt_batch, $url, &$cnts)
{
  bbscript_log("TRACE", "==> distassign()");

  // Attach the json data
  bbscript_log("TRACE", "About to encode address batch in JSON");
  $json_batch = json_encode($fmt_batch);

  // Initialize the cURL request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_batch);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($json_batch)));
  bbscript_log("TRACE", "About to send API request to SAGE using cURL [url=$url]");
  $response = curl_exec($ch);

  // Record the timings for the request and close
  $curl_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

  $cnts['CURL'] += $curl_time;
  bbscript_log("TRACE", "CURL: fetched in ".round($curl_time, 3)." seconds");
  curl_close($ch);

  // Return null on any kind of response error
  if ($response === null) {
    bbscript_log("ERROR", "Failed to receive a CURL response");
    $results = null;
  }
  else {
    bbscript_log("TRACE", "About to decode JSON response");
    $results = @json_decode($response, true);

    if ($results === null && json_last_error() !== JSON_ERROR_NONE) {
      bbscript_log("ERROR", "Malformed JSON Response");
      bbscript_log("DEBUG", "CURL DATA: $response");
      $results = null;
    }
    else if (count($results) == 0) {
      bbscript_log("ERROR", "Empty response from SAGE. SAGE server is likely offline.");
      $results = null;
    }
    else if (isset($results['message'])) {
      bbscript_log("ERROR", "SAGE server encountered a problem: ".$results['message']);
      $results = null;
    }
  }

  bbscript_log("TRACE", "<== distassign()");
  return $results;
} // distassign()



function process_batch_results($db, &$orig_batch, &$batch_results, &$cnts)
{
  global $BB_DRY_RUN;

  bbscript_log("TRACE", "==> process_batch_results()");
  $cnts['TOTAL'] += count($batch_results);
  $formatted_results = array();
  $MATCH_CODES = array("HOUSE", "STREET", "ZIP5", "SHAPEFILE");
  $batch_lo_id = $batch_hi_id = 0;

  foreach ($batch_results as $batch_res) {
    $address_id = $batch_res['address_id'];
    $status_code = $batch_res['status_code'];
    $message = $batch_res['message'];
    if ($batch_lo_id == 0 || $address_id < $batch_lo_id) {
      $batch_lo_id = $address_id;
    }
    if ($address_id > $batch_hi_id) {
      $batch_hi_id = $address_id;
    }

    if (in_array($status_code, $MATCH_CODES) !== false) {
      $cnts['MATCH']++;
      $cnts[$status_code]++;
      bbscript_log("TRACE", "[MATCH - $status_code][$message] on record #$address_id");
      $formatted_results[$address_id] = array(
        'congressional_district_46' => $batch_res['congressional_code'],
        'ny_senate_district_47' => $batch_res['senate_code'],
        'ny_assembly_district_48' => $batch_res['assembly_code'],
        'election_district_49' => $batch_res['election_code'],
        'county_50' => $batch_res['county_code'],
        'county_legislative_district_51' => $batch_res['cleg_code'],
        'town_52' => $batch_res['town_code'],
        'ward_53' => $batch_res['ward_code'],
        'school_district_54' => $batch_res['school_code'],
        // 'new_york_city_council_55' => $batch_res['nycc_code'],
        'geo_code_1' => $batch_res['latitude'],
        'geo_code_2' => $batch_res['longitude'],
        'geo_accuracy' => $batch_res['geo_accuracy'],
        'result_code' => $status_code,
        'result_message' => $message,
      );
    } elseif ($status_code == "NOMATCH") {
      $cnts['NOMATCH']++;
      bbscript_log("WARN", "[NOMATCH][$message] on record #$address_id");
    } elseif ($status_code == "INVALID") {
      $cnts['INVALID']++;
      bbscript_log("WARN", "[INVALID][$message] on record #$address_id");
    } else { // Unknown status_code, what?!?
      $cnts['ERROR']++;
      bbscript_log("ERROR", "Unknown status [$status_code] on record #$address_id with message [$message]");
    }
  }

  // Update districts in the database if --dryrun flag was not set
  // and insert a note describing the update.
  if (!$BB_DRY_RUN) {
    $update_time_start = microtime(true);
    bbscript_log("DEBUG", "Updating ".count($formatted_results)." records");

    // Delete only notes in the current batch
    $q = "DELETE FROM n USING civicrm_note n
          JOIN civicrm_address a ON n.entity_id = a.contact_id
          WHERE a.id BETWEEN $batch_lo_id AND $batch_hi_id
          AND (n.subject LIKE 'RD12 VERIFIED DISTRICTS%' OR
               n.subject LIKE 'RD12 UPDATED DISTRICTS%')
          AND preg_capture('/[[]id=([0-9]+)[]]/', n.subject, 1)
              BETWEEN $batch_lo_id AND $batch_hi_id";
    bb_mysql_query($q, $db, true);
    $row_cnt = mysql_affected_rows($db);
    bbscript_log("DEBUG", "Removed $row_cnt notes for address IDs from $batch_lo_id to $batch_hi_id");

    // Abbreviations for district codes used in the body of the notes.
    $districts = array(
      "congressional_district_46" => 'CD',
      "ny_senate_district_47" => 'SD',
      "ny_assembly_district_48" => 'AD',
      "election_district_49" => 'ED',
      "county_50" => 'CO',
      "county_legislative_district_51" => 'CLEG',
      "town_52" => 'TOWN',
      "ward_53" => 'WARD',
      "school_district_54" => 'SCHL',
      "new_york_city_council_55" => 'NYCC',
    );

    bb_mysql_query('BEGIN', $db, true);

    foreach ($formatted_results as $address_id => $fmt_res) {
      $row = $orig_batch[$address_id];
      $contact_id = $row['contact_id'];
      $result_type = $fmt_res['result_code'];

      // Record all the district mappings and note changes in the subject line
      $changes = array();
      $note_updates = array();
      $sql_updates = array();

      foreach ($districts as $dist_field => $dist_abbrev ) {
        $old_value = get($row, $dist_field, 'NULL');
        $new_value = get($fmt_res, $dist_field, $old_value);
        $note_updates[] = "$dist_abbrev:$old_value=>$new_value";

        if ($old_value != $new_value) {
          $changes[] = $dist_abbrev;
          if ($dist_field == 'town_52') {
            $sql_updates[] = "$dist_field = '$new_value'";
          } else {
            $sql_updates[] = "$dist_field = $new_value";
          }
        }
      }

      // If any of the districts changed, update district table
      if (count($changes) != 0) {
        if ($row['district_id']) {
          $q = "UPDATE civicrm_value_district_information_7 di
                SET ".implode(", ", $sql_updates)."
                WHERE di.entity_id = $address_id";
          bb_mysql_query($q, $db, true);
        }
        else {
          $q = "INSERT INTO civicrm_value_district_information_7
             (entity_id, ny_senate_district_47, ny_assembly_district_48, congressional_district_46, election_district_49, county_50, county_legislative_district_51, town_52, ward_53, school_district_54, new_york_city_council_55)
             VALUES
             ($address_id,
             ".get($fmt_res, 'ny_senate_district_47',0).",
             ".get($fmt_res, 'ny_assembly_district_48',0).",
             ".get($fmt_res, 'congressional_district_46',0).",
             ".get($fmt_res, 'election_district_49',0).",
             ".get($fmt_res, 'county_50',0).",
             ".get($fmt_res, 'county_legislative_district_51',0).",
             '".get($fmt_res, 'town_52','')."',
             ".get($fmt_res, 'ward_52',0).",
             ".get($fmt_res, 'school_district_54',0).",
             ".get($fmt_res, 'new_york_city_council_55',0).")";
          bb_mysql_query($q, $db, true);
        }
      }

      // Shape file lookups can result in new/changed coordinates.
      if ($result_type == 'SHAPEFILE') {
        $old_lat = get($row, 'geo_code_1', "NULL");
        $old_lon = get($row, 'geo_code_2', "NULL");
        $new_lat = get($fmt_res, 'geo_code_1', "NULL");
        $new_lon = get($fmt_res, 'geo_code_2', "NULL");
        $note_updates += array("lat:$old_lat=>$new_lat", "lon:$old_lon=>$new_lon");
        if ($old_lat != $new_lat || $old_lon != $new_lon) {
          bbscript_log("TRACE", "Saving new geocoordinates: ($new_lat,$new_lon)");
          $q = "UPDATE civicrm_address
                SET geo_code_1=$new_lat, geo_code_2=$new_lon
                WHERE id=$address_id";
          bb_mysql_query($q, $db, true);
        }
      }

      // Create a new contact note describing the state before
      // and after redistricting.
      $note = "ADDRESS_ID: $address_id\n".
              " MATCH_TYPE: $result_type\n".
              " ADDRESS: ".$row['street_number'].' '.$row['street_number_suffix'].' '.$row['street_name'].' '.$row['street_type'].', '.$row['city'].', '.$row['state'].', '.$row['postal_code']."\n".
              "UPDATES:\n ".implode("\n ", $note_updates);

      if (count($changes) != 0) {
        $subject = "RD12 UPDATED DISTRICTS [id=$address_id]: ".implode(',', $changes);
      } else {
        $subject = "RD12 VERIFIED DISTRICTS [id=$address_id]";
      }

      $note = mysql_real_escape_string($note, $db);
      $subject = mysql_real_escape_string($subject, $db);
      $q = "INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id,
                                      modified_date, subject, privacy)
            VALUES ('civicrm_contact', $contact_id, '$note', 1,
                    '".date("Y-m-d")."', '$subject', 0)";
      bb_mysql_query($q, $db, true);
    }

    bb_mysql_query("COMMIT", $db, true);
    $update_time = get_elapsed_time($update_time_start);
    bbscript_log("TRACE", "Updated database in ".round($update_time, 3));
    $cnts['MYSQL'] += $update_time;
  }
  else {
    bbscript_log("INFO", "DRYRUN mode enabled - No records to update");
  }
  bbscript_log("TRACE", "<== process_batch_results()");
} // process_batch_results()



function report_stats($total_found, $cnts, $time_start)
{
  bbscript_log("TRACE", "==> report_stats()");

  // Compute percentages for certain counts
  $percent = array(
    "MATCH" => 0,
    "NOMATCH" => 0,
    "INVALID" => 0,
    "ERROR" => 0,
    "HOUSE" => 0,
    "STREET" => 0,
    "ZIP5" => 0,
    "SHAPEFILE" => 0
  );

  // Timer for debug
  $time = get_elapsed_time($time_start);
  $Records_per_sec = round($cnts['TOTAL'] / $time, 1);
  $Mysql_per_sec = ($cnts['MYSQL'] == 0 ) ? 0 : round($cnts['TOTAL'] / $cnts['MYSQL'], 1);
  $Curl_per_sec = ($cnts['CURL'] == 0 ) ? 0 : round($cnts['TOTAL'] / $cnts['CURL'], 1);

  // Update the percentages using the counts
  foreach ($percent as $key => $value) {
    $percent[$key] = round($cnts[$key] / $cnts['TOTAL'] * 100, 2);
  }

  $seconds_left = round(($total_found - $cnts['TOTAL']) / $Records_per_sec, 0);
  $finish_at = date('Y-m-d H:i:s', (time() + $seconds_left));

  bbscript_log("INFO", "-------  ------- ---- ---- ---- ---- ");
  bbscript_log("INFO", "[DONE @]      $finish_at (in ".intval($seconds_left/60).":".($seconds_left%60).")");
  bbscript_log("INFO", "[COUNT]      {$cnts['TOTAL']}");
  bbscript_log("INFO", "[TIME]       ".round($time, 4));
  bbscript_log("INFO", "[SPEED]  [TOTAL] $Records_per_sec per second (".$cnts['TOTAL']." in ".round($time, 3).")");
  bbscript_log("TRACE","[SPEED]  [MYSQL] $Mysql_per_sec per second (".$cnts['TOTAL']." in ".round($cnts['MYSQL'], 3).")");
  bbscript_log("TRACE","[SPEED]  [CURL] $Curl_per_sec per second (".$cnts['TOTAL']." in ".round($cnts['CURL'], 3).")");
  bbscript_log("INFO", "[MATCH]  [TOTAL] {$cnts['MATCH']} ({$percent['MATCH']} %)");
  bbscript_log("INFO","[MATCH]  [HOUSE] {$cnts['HOUSE']} ({$percent['HOUSE']} %)");
  bbscript_log("INFO","[MATCH]  [STREET] {$cnts['STREET']} ({$percent['STREET']} %)");
  bbscript_log("INFO","[MATCH]  [ZIP5]  {$cnts['ZIP5']} ({$percent['ZIP5']} %)");
  bbscript_log("INFO","[MATCH]  [SHAPE] {$cnts['SHAPEFILE']} ({$percent['SHAPEFILE']} %)");
  bbscript_log("INFO", "[NOMATCH] [TOTAL] {$cnts['NOMATCH']} ({$percent['NOMATCH']} %)");
  bbscript_log("INFO", "[INVALID] [TOTAL] {$cnts['INVALID']} ({$percent['INVALID']} %)");
  bbscript_log("INFO", "[ERROR]  [TOTAL] {$cnts['ERROR']} ({$percent['ERROR']} %)");
  bbscript_log("TRACE", "<== report_stats()");
} // report_stats()



function clean_row($row)
{
  $match = array('/ AVENUE( EXT)?$/',
                 '/ STREET( EXT)?$/',
                 '/ PLACE/',
                 '/ EAST$/',
                 '/ WEST$/',
                 '/ SOUTH$/',
                 '/ NORTH$/',
                 '/^EAST (?!ST|AVE|RD|DR)/',
                 '/^WEST (?!ST|AVE|RD|DR)/',
                 '/^SOUTH (?!ST|AVE|RD|DR)/',
                 '/^NORTH (?!ST|AVE|RD|DR)/');

  $replace = array(' AVE$1',
                   ' ST$1',
                   ' PL',
                   ' E',
                   ' W',
                   ' S',
                   ' N',
                   'E ',
                   'W ',
                   'S ',
                   'N ');

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
    && $array[$key] !== 0 && $array[$key] !== "000") {
    return $array[$key];
  }
  else {
    return $default;
  }
} // get()

