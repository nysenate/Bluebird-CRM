<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./Redistricting.php -S skelos --batch 2000 --log 5 --max 10000
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

// Parse the following user options
require_once 'script_utils.php';
$shortopts = "b:l:m:naoig:sct:";
$longopts = array("batch=", "log=", "max=", "dryrun", "addressmap","outofstate","instate","usegeocoder=","useshapefiles","usecoordinates","threads=");
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--batch SIZE] [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] [--max COUNT] [--dryrun] [--addressmap] [--outofstate] [--instate] [--threads COUNT] [--usegeocoder NAME] [--useshapefiles] [--usecoordinates]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Use user options to configure the script
$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper(get($optlist, 'log', 'trace'))][0];
$opt_batch_size = get($optlist, 'batch', 1000);
$BB_DRY_RUN = get($optlist, 'dryrun', FALSE);
$opt_max = get($optlist, 'max', FALSE);
$opt_outofstate = get($optlist, 'outofstate', FALSE);
$opt_addressmap = get($optlist, 'addressmap', FALSE);
$opt_instate = get($optlist, 'instate', FALSE);
$opt_usegeocoder = get($optlist, 'usegeocoder', FALSE);
$opt_useshapefiles = get($optlist, 'useshapefiles', FALSE);
$opt_usecoordinates = get($optlist, 'usecoordinates', FALSE);
$opt_threads = get($optlist, 'threads', 3);

// Use instance settings to configure for SAGE
$bbcfg = get_bluebird_instance_config($optlist['site']);
$sage_base = array_key_exists('sage.api.base', $bbcfg) ? $bbcfg['sage.api.base'] : false;
$sage_key = array_key_exists('sage.api.key', $bbcfg) ? $bbcfg['sage.api.key'] : false;
if (!($sage_base && $sage_key)) {
    error_log(bbscript_log("fatal", "sage.api.base and sage.api.key must be set in your bluebird.cfg file."));
    exit(1);
}

// Dump the active options when in debug mode
bbscript_log("debug", "Option: INSTANCE={$optlist['site']}");
bbscript_log("debug", "Option: BATCH_SIZE=$opt_batch_size");
bbscript_log("debug", "Option: LOG_LEVEL=$BB_LOG_LEVEL");
bbscript_log("debug", "Option: DRY_RUN=".($BB_DRY_RUN ? "TRUE" : "FALSE"));
bbscript_log("debug", "Option: SAGE_API=$sage_base");
bbscript_log("debug", "Option: SAGE_KEY=$sage_key");
bbscript_log("debug", "Option: INSTATE=".($opt_instate ? "TRUE" : "FALSE"));
bbscript_log("debug", "Option: OUTOFSTATE=".($opt_outofstate ? "TRUE" : "FALSE"));
bbscript_log("debug", "Option: ADDRESSMAP=".($opt_addressmap ? "TRUE" : "FALSE"));
bbscript_log("debug", "Option: MAX=".($opt_max ? $opt_max : "NONE"));
bbscript_log("debug", "Option: USE_SHAPEFILES=".($opt_useshapefiles ? "TRUE" : "FALSE"));
bbscript_log("debug", "Option: USE_COORDINATES=".($opt_usecoordinates ? "TRUE" : "FALSE"));
bbscript_log("debug", "Option: THREADS=$opt_threads");
bbscript_log("debug", "Option: USE_GEOCODER=".($opt_usegeocoder ? $opt_usegeocoder : "FALSE"));

// Construct the url with all our options...
$bulkdistrict_url = "$sage_base/json/bulkdistrict/body?threadCount=$opt_threads&key=$sage_key&useGeocoder=".($opt_usegeocoder ? "1&geocoder=$opt_usegeocoder" : "0")."&useShapefiles=".($opt_useshapefiles ? 1 : 0);

// Get CiviCRM database connection
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config =& CRM_Core_Config::singleton();
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

// Remove any redistricting notes that already exist
mysql_query("
    DELETE FROM civicrm_note
    WHERE entity_table='civicrm_contact'
    AND subject LIKE 'RD12%'", $db
);

// Map old district numbers to new district numbers if the addressMap option is set
if ( $opt_addressmap ) {
    address_map($db);
}

if ( $opt_outofstate ) {
    handle_out_of_state($db);
}

if ( $opt_instate ) {
    handle_in_state($db, $opt_max, $bulkdistrict_url, $opt_batch_size);
}

bbscript_log("info", "Completed redistricting addresses");

function address_map($db) {
    $address_map_changes = 0;
    bbscript_log("info", "Mapping old district numbers to new district numbers");
    $district_cycle = array(
        '27' => 17, '29' => 27, '28' => 29, '26' => 28, '25' => 26, '18' => 25, '17' => 18,
        '58' => 63, '53' => 58, '49' => 53, '44' => 49, '46' => 44
    );

    mysql_query("BEGIN", $db);
    $result = mysql_query("SELECT id, ny_senate_district_47 FROM civicrm_value_district_information_7");
    $num_rows = mysql_num_rows($result);
    while (($row = mysql_fetch_assoc($result)) != null) {
        $district = $row['ny_senate_district_47'];
        if ( isset( $district_cycle[$district]) ) {
            mysql_query("
                UPDATE civicrm_value_district_information_7
                SET ny_senate_district_47 = {$district_cycle[$district]}
                WHERE id = {$row['id']};", $db
            );
            $address_map_changes++;
        }
    }
    mysql_query("COMMIT", $db);
    bbscript_log("info", "Completed district mapping with $address_map_changes changes");
}

function handle_out_of_state($db, $subject_prefixes) {
    // Remove AD, SD, CD info for any non-NY state addresses
    $result = mysql_query("
        SELECT address.*, ny_senate_district_47, ny_assembly_district_48, congressional_district_46
        FROM civicrm_address  as address
          JOIN civicrm_state_province as state ON (address.state_province_id=state.id)
          JOIN civicrm_value_district_information_7 as district ON (district.entity_id=address.id)
        WHERE state.abbreviation!='NY'", $db
    );

    while (($row = mysql_fetch_assoc($result)) != null) {

        $note = "A_ID: {$row['id']}\n".
                "UPDATES:\n".
                " SD:".empty($row['ny_senate_district_47']) ? "NULL" : $row['ny_senate_district_47']."=>0\n".
                " CD:".empty($row['congressional_district_46']) ? "NULL" : $row['congressional_district_46']."=>0\n".
                " AD:".empty($row['ny_assembly_district_48']) ? "NULL" : $row['ny_assembly_district_48']."=>0";
        $subject = "RD12 REMOVED DISTRICTS";

        mysql_query("INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
            VALUES ('civicrm_contact', {$row['contact_id']}, '$note', 1, '".date("Y-m-d")."', '$subject', 0)", $db
        );

        // Set district information to zero.
        mysql_query("
            UPDATE civicrm_value_district_information_7
            SET congressional_district_46 = 0,
                ny_senate_district_47 = 0,
                ny_assembly_district_48 = 0,
            WHERE civicrm_value_district_information_7.entity_id = {$row['id']}", $db
        );
    }
}

function handle_in_state($db, $opt_max, $bulkdistrict_url, $opt_batch_size) {
    // Start a timer and a counter for results
    $time_start = microtime(true);
    $count = array("TOTAL" => 0,"MATCH" => 0,"HOUSE" => 0,"STREET" => 0,"ZIP5" => 0,"SHAPEFILE" => 0,"NOMATCH" => 0,"INVALID" => 0,"ERROR" => 0,"CURL" => 0,"MYSQL" => 0);

    // Collect all NY state addresses from all contacts.
    $query = "SELECT address.*,
        state.abbreviation AS state,
        district.county_50,
        district.county_legislative_district_51,
        district.congressional_district_46,
        district.ny_senate_district_47,
        district.ny_assembly_district_48,
        district.election_district_49,
        district.town_52,
        district.ward_53,
        district.school_district_54,
        district.new_york_city_council_55,
        district.neighborhood_56,
        district.last_import_57
      FROM civicrm_address as address
        JOIN civicrm_state_province as state
        JOIN civicrm_value_district_information_7 as district
      WHERE address.state_province_id=state.id
        AND district.entity_id = address.id
        AND state.abbreviation='NY'
      ORDER BY address.id ASC
      ".(($opt_max != FALSE) ? "LIMIT $opt_max" : "");

    // Run query to obtain all addresses
    bbscript_log("debug", "Querying the database for addresses using\n$query");
    $mysql_result = mysql_query($query, $db);
    if ( $mysql_result == null ) {
        bbscript_log("fatal", "The database query failed with the following error: " .  mysql_error());
        die();
    }

    $originals_batch = array();
    $formatted_batch = array();
    $total_addresses = mysql_num_rows($mysql_result);
    bbscript_log("INFO", $total_addresses." addresses found.");
    for ($rownum = 1; $rownum <= $total_addresses; $rownum++) {
        // Fetch the new row, no null check needed since we have the count
        // If we do pull back a NULL something bad happened and dying is okay
        $row = mysql_fetch_assoc($mysql_result);

        // Save the original row for later, we'll need it when saving.
        $originals_batch[$row['id']] = $row;

        // Format for the bulkdistrict api
        $row = clean_row($row);

        // Attempt to fill in missing addresses with supplemental information
        $street = trim($row['street_name'].' '.$row['street_type']);
        if ($street=='') {
            if ($row['supplemental_address_1']) {
                $street = $row['supplemental_address_1'];
            } else if ($row['supplemental_address_2']) {
                $street = $row['supplemental_address_2'];
            }
        }

        // Format the address for sage
        $formatted_batch[$row['id']]= array(
            'street' => $street,
            'town' => $row['city'],
            'state' => $row['state'],
            'zip5' => $row['postal_code'],
            'apt' => NULL,
            'building' => $row['street_number'],
            'building_chr' => $row['street_number_suffix'],
        );

        // If requested, use the coordinates already in the system
        if ($opt_usecoordinates) {
            $formatted_batch[$row['id']]['latitude'] = $row['geo_code_1'];
            $formatted_batch[$row['id']]['latitude'] = $row['geo_code_2'];
        }

        // Keep accumulating until we reach batch size or the end of our addresses.
        if (count($formatted_batch) < $opt_batch_size && $rownum != $total_addresses) {
            continue;
        }

        // Let SAGE do all the hard work
        $batch_results = distassign($formatted_batch, $bulkdistrict_url, $count);
        $count['TOTAL'] += count($batch_results);

        // Process the results
        $formatted_results = array();
        foreach ($batch_results as $batch_result) {
            $address_id = $batch_result['address_id'];
            $status_code = $batch_result['status_code'];
            $message = $batch_result['message'];

            $MATCH_CODES = array("HOUSE","STREET","ZIP5","SHAPEFILE");
            if (in_array($status_code,$MATCH_CODES)!==FALSE) {
                $count['MATCH']++;
                $count[$status_code]++;
                bbscript_log("trace", "[MATCH - $status_code][$message] on record #$address_id");
                $formatted_results[$address_id] = array(
                    'ny_assembly_district_48'=>$batch_result['assembly_ode'],
                    'congressional_district_46'=>$batch_result['congressional_code'],
                    'election_district_49'=>$batch_result['election_code'],
                    'ny_senate_district_47'=>$batch_result['senate_code'],
                    'county_50'=>$batch_result['county_code'],
                    'geo_code_1'=>$batch_result['latitude'],
                    'geo_code_2'=>$batch_result['longitude'],
                    'geo_accuracy'=>$batch_result['geo_accuracy'],
                    'result_code' => $status_code,
                    'result_message' => $message,
                    'ward_53'=>$batch_result['ward_code'],
                    'town_52'=>$batch_result['town_code'],
                    'county_legislative_district_51'=>$batch_result['cleg_code'],
                    'school_district_54'=>$batch_result['school_code'],
                    // 'new_york_city_council_55'=>$batch_result['nycc_code'],
                );

            } elseif ($status_code == "NOMATCH") {
                $count['NOMATCH']++;
                bbscript_log("warn", "[NOMATCH][$message] on record #$address_id");

            } elseif ($status_code == "INVALID") {
                $count['INVALID']++;
                bbscript_log("warn", "[INVALID][$message] on record #$address_id");

            } else { // Unknown status_code, what?!?
                $count['ERROR']++;
                bbscript_log("ERROR", "Unknown status [$status_code] on record #$address_id with message [$message]");
            }
        }

        // Update districts in the database if --dryrun flag was not set
        // and insert a note describing the update.
        if (!$BB_DRY_RUN) {
            $update_time_start = microtime(true);
            bbscript_log("trace", "Updating ".count($formatted_results)." records.");

            // Abbreviations for district codes used in the body of the notes.
            $districts = array(
                "ny_senate_district_47" => "SD",
                "ny_assembly_district_48" => "AD",
                "congressional_district_46" => "CD",
                "election_district_49" => "ED",
                "county_50" => "CO",
                "county_legislative_district_51" => "CLEG",
                "town_52" => "TOWN",
                "ward_53" => "WARD",
                "school_district_54" => "SCHL",
                "new_york_city_council_55" => "NYCC",
            );

            mysql_query("BEGIN", $db);
            foreach ($formatted_results as $address_id => $formatted_result) {
                $row = $originals_batch[$address_id];
                $contact_id = $row['contact_id'];
                $result_type = $formatted_result['result_code'];

                // Record all the district mappings and note changes in the subject line
                $changes = array();
                $note_updates = "";
                $sql_updates = "";
                foreach ($districts as $field => $abbrv ) {
                    $old_value = get($row, $field, "NULL");
                    $new_value = get($formatted_result, $field, $old_value);
                    $note_updates[] = "$abbrv:$old_value=>$new_value";
                    if ( $old_value != $new_value ) {
                        $changes[]=$abbrv;
                        $sql_updates[] = "$field = $new_value";
                    }
                }

                // If any of the districts changed, update the district table
                if ( count($changes) != 0 ) {
                    mysql_query("
                        UPDATE civicrm_value_district_information_7
                        SET ".implode("\n                      ",$sql_updates)."
                        WHERE civicrm_value_district_information_7.entity_id = $address_id", $db
                    );
                }

                // Shape file lookups can result in new/changed coordinates.
                if ( $result_type == 'SHAPEFILE' ) {
                    $old_lat = get($row,'geo_code_1',"NULL");
                    $old_lon = get($row,'geo_code_2',"NULL");
                    $new_lat = get($formatted_result,'geo_code_1',"NULL");
                    $new_lon = get($formatted_result,'geo_code_2',"NULL");
                    $note_updates += array("lat:$old_lat=>$new_lat","lon:$old_lon=>$new_lon");
                    if ($old_lat != $new_lat || $old_lon != $new_lon) {
                        bbscript_log("TRACE", "Saving new geocoordinates: ($new_lat,$new_lon)");
                        mysql_query("
                            UPDATE civicrm_address
                            SET geo_code_1=$new_lat, geo_code_2=$new_lon
                            WHERE id=$address_id", $db
                        );
                    }
                }

                // Create a new contact note describing the state before and after redistricting.
                $note = "A_ID: $address_id\n".
                        " MATCH_TYPE: $result_type\n".
                        " ADDRESS: ".$row['street_number'].' '.$row['street_number_suffix'].' '.$row['street_name'].' '.$row['street_type'].', '.$row['city'].', '.$row['state'].', '.$row['postal_code']."\n".
                        "UPDATES:\n ".implode("\n ",$note_updates);

                if ( count($changes) != 0) {
                    $subject = "RD12 UPDATED DISTRICTS: ".implode(', ',$changes);
                } else {
                    $subject = "RD12 VERIFIED DISTRICTS";
                }

                mysql_query("
                    INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
                    VALUES ('civicrm_contact', $contact_id, '$note', 1, '".date("Y-m-d")."', '$subject', 0)", $db
                );
            }

            mysql_query("COMMIT", $db);
            $update_time = get_elapsed_time($update_time_start);
            bbscript_log("trace", "Updated database in ".round($update_time, 3));
            $count['MYSQL'] += $update_time;

        } else {
            bbscript_log("info", "DRY_RUN - No Records to update");
        }

        // Reset the arrays to repeat the batch lookup process for the next batch
        $formatted_batch = array();

        report_stats($total_addresses, $count, $time_start);
    }
}

function distassign($formatted_batch, $endpoint, $count) {
    // Initialize the cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);

    // Attach the json data
    $json_batch = json_encode($formatted_batch);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_batch);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($json_batch)));
    $response = curl_exec($ch);

    // Record the timings for the request and close
    $curl_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $count['CURL'] += $curl_time;
    bbscript_log("trace", "CURL: fetched in     ".round($curl_time, 3));
    curl_close($ch);

    // Return null on any kind of response error
    if ($response === null) {
        bbscript_log("fatal", "CURL: failed to receive a response");
        return null;
    }

    $results = @json_decode($response, true);

    if (($results === null && json_last_error() !== JSON_ERROR_NONE )) {
        bbscript_log("fatal", "Malformed JSON Response");
        echo $output."\n";
        return null;

    } else if ( count($results) == 0 ){
        bbscript_log("error", "Empty response from SAGE.");
        return null;
    }

    return $results;
}

function report_stats($total_found, $count, $time_start) {
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
    $Records_per_sec = round($count['TOTAL'] / $time, 1);
    $Mysql_per_sec = ($count['MYSQL'] == 0 ) ? 0 : round($count['TOTAL'] / $count['MYSQL'], 1);
    $Curl_per_sec = ($count['CURL'] == 0 ) ? 0 : round($count['TOTAL'] / $count['CURL'], 1);

    // Update the percentages using the counts
    foreach ( $percent as $key => $value ) {
        $percent[$key] = round( $count[$key] / $count['TOTAL'] * 100, 2 );
    }

    $seconds_left = round(($total_found - $count['TOTAL']) / $Records_per_sec, 0);
    $finish_at = date('Y-m-d H:i:s', (time() + $seconds_left));

    bbscript_log("info", "-------    ------- ---- ---- ---- ---- ");
    bbscript_log("info", "[DONE @]           $finish_at (in ".intval($seconds_left/60).":".($seconds_left%60).")");
    bbscript_log("info", "[COUNT]            {$count['TOTAL']}");
    bbscript_log("info", "[TIME]             ".round($time, 4));
    bbscript_log("info", "[SPEED]    [TOTAL] $Records_per_sec per second (".$count['TOTAL']." in ".round($time, 3).")");
    bbscript_log("trace","[SPEED]    [MYSQL] $Mysql_per_sec per second (".$count['TOTAL']." in ".round($count['MYSQL'], 3).")");
    bbscript_log("trace","[SPEED]    [CURL]  $Curl_per_sec per second (".$count['TOTAL']." in ".round($count['CURL'], 3).")");
    bbscript_log("info", "[MATCH]    [TOTAL] {$count['MATCH']} ({$percent['MATCH']} %)");
    bbscript_log("info","[MATCH]    [HOUSE]  {$count['HOUSE']} ({$percent['HOUSE']} %)");
    bbscript_log("info","[MATCH]    [STREET] {$count['STREET']} ({$percent['STREET']} %)");
    bbscript_log("info","[MATCH]    [ZIP5]   {$count['ZIP5']} ({$percent['ZIP5']} %)");
    bbscript_log("info","[MATCH]    [SHAPE]  {$count['SHAPEFILE']} ({$percent['SHAPEFILE']} %)");
    bbscript_log("info", "[NOMATCH]  [TOTAL] {$count['NOMATCH']} ({$percent['NOMATCH']} %)");
    bbscript_log("info", "[INVALID]  [TOTAL] {$count['INVALID']} ({$percent['INVALID']} %)");
    bbscript_log("info", "[ERROR]    [TOTAL] {$count['ERROR']} ({$percent['ERROR']} %)");
}


function clean_row($row) {
    $match = array('/ AVENUE( EXT)?$/','/ STREET( EXT)?$/','/ PLACE/','/ EAST$/','/ WEST$/','/ SOUTH$/','/ NORTH$/','/^EAST (?!ST|AVE|RD|DR)/','/^WEST (?!ST|AVE|RD|DR)/','/^SOUTH (?!ST|AVE|RD|DR)/','/^NORTH (?!ST|AVE|RD|DR)/');
    $replace = array(' AVE$1',' ST$1',' PL',' E',' W',' S',' N','E ','W ','S ','N ');

    $street = preg_replace("/[.,']/","",strtoupper(trim($row['street_name'])));
    $row['street_name'] = preg_replace($match, $replace, $street);

    $street = preg_replace("/[.,']/","",strtoupper(trim($row['street_type'])));
    $row['street_type'] = preg_replace($match, $replace, $street);

    return $row;
}

function get($array, $key, $default) {
    // blank, null, and 0 values are bad.
    return isset($array[$key]) && $array[$key]!=NULL && $array[$key]!=="" && $array[$key]!==0 && $array[$key]!=="000" ? $array[$key] : $default;
}