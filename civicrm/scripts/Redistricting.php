<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./Redistricting.php -S skelos --chunk 2000 --log 5 --max 10000
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_CHUNK_SIZE', 1000);
define('DEFAULT_LOG_LEVEL', 'TRACE');

// Parse the options
require_once 'script_utils.php';
$shortopts = "c:l:m:na";
$longopts = array("chunk=", "log=", "max=", "dryrun", "addressMap");
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--chunk SIZE] [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] [--max COUNT] [--dryrun] [--addressMap]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Use instance settings to configure for SAGE
$bbcfg = get_bluebird_instance_config($optlist['site']);
$sage_base = array_key_exists('sage.api.base', $bbcfg) ? $bbcfg['sage.api.base'] : false;
$sage_key = array_key_exists('sage.api.key', $bbcfg) ? $bbcfg['sage.api.key'] : false;

if (!($sage_base && $sage_key)) {
    error_log(bbscript_log("fatal", "sage.api.base and sage.api.key must be set in your bluebird.cfg file."));
    exit(1);
}

$bulk_distassign_url = $sage_base.'/json/bulkdistrict/body?key='.$sage_key;

// Initialize script parameters from options and defaults
$chunk_size = $optlist['chunk'] ? $optlist['chunk'] : DEFAULT_CHUNK_SIZE;
$log_level = $optlist['log'] ? $optlist['log'] : DEFAULT_LOG_LEVEL;
$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper($log_level)][0];
$dry_run = $optlist['dryrun'];
$max_id = $optlist['max'];

$max_id_clause = is_numeric($max_id) ? "LIMIT $max_id" : "";

bbscript_log("debug", "Starting with $prog with chunk size of $chunk_size");

// Initialize CiviCRM
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

// Establish a connection to the instance database
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

// Map old district numbers to new district numbers if the addressMap option is set

if ( $optlist['addressMap'] ) {
    address_map($db);
}

// Collect NY state addresses with a street address; any
// address not matching this criteria will not be included.
$query = "
    SELECT address.id,
           address.contact_id,
           address.street_name,
           address.street_type,
           address.city AS town,
           state_province.abbreviation AS state,
           address.postal_code AS zip,
           address.street_number_suffix AS building_chr,
           address.street_number AS building,
           district.county_50 AS county_code,
           district.county_legislative_district_51,
           district.congressional_district_46 AS congressional_code,
           district.ny_senate_district_47 AS senate_code,
           district.ny_assembly_district_48 AS assembly_code,
           district.election_district_49 AS election_code,
           district.town_52,
           district.ward_53,
           district.school_district_54,
           district.new_york_city_council_55,
           district.neighborhood_56,
           district.last_import_57
    FROM civicrm_address as address
    JOIN civicrm_state_province as state_province
    JOIN civicrm_value_district_information_7 as district
    WHERE address.state_province_id=state_province.id
      AND district.entity_id = address.id
    ORDER BY address.id ASC
    $max_id_clause
";

// Run query to obtain all addresses
bbscript_log("debug", "Querying the database for addresses using\n$query");
$result = mysql_query($query, $db);
if ( $result == null )
{
    bbscript_log("error", "The database query failed with the following error: " .  mysql_error());
    die();
}
$total_found = mysql_num_rows($result);
bbscript_log("debug", $total_found." addresses found.");

// Start timer
$time_start = microtime(true);

// Store count statistics
$count = array(
    "TOTAL" => 0,
    "MATCH" => 0,
    "STREETNUM" => 0,
    "STREETNAME" => 0,
    "ZIPCODE" => 0,
    "SHAPEFILE" => 0,
    "NOMATCH" => 0,
    "INVALID" => 0,
    "OUTOFSTATE" => 0,
    "ERROR" => 0,

    "totalCurlTime" => 0,
    "totalMysqlTime" => 0
);

// Subject prefixes will indicate whether district information
// has been updated, kept the same, or removed. RD12 stands for
// Redistricting 2012 and prefixing it is a way to filter through
// relevant notes pertaining to redistricting.
$subject_prefixes = array(
    "unchanged" => "RD12 VERIFIED DISTRICTS",
    "changed" => "RD12 UPDATED DISTRICTS",
    "removed" => "RD12 REMOVED DISTRICTS"
);

// Abbreviations for district codes used in the body of the notes.
$district_codes = array(
    "senate_code" => "SD",
    "assembly_code" => "AD",
    "congressional_code" => "CD",
    "election_code" => "ED",
    "county_code" => "CO"
);

$ny_address_data = array();
$non_ny_address_data = array();
$JSON_payload = array();
$address_count = mysql_num_rows($result);

for ($rownum = 1; $rownum <= $address_count; $rownum++) {

    // Fetch the new row, no null check needed since we have the count
    // If we do pull back a NULL something bad happened and dying is okay
    $row = mysql_fetch_assoc($result);

    $ny_address_data[$row['id']] = $row;

    // Since we're only concerned with NY state addresses for the lookup process
    // the out of state addresses will have their district information set to 0.
    if ( $row['state'] != 'NY') {
        $non_ny_address_data[$row['id']] = $row;
        $count['OUTOFSTATE']++;
        bbscript_log("trace", "Found out of state address with id: {$row['id']}");
        continue;
    }

    if ( $row['street_name'] == null || trim($row['street_name']) == '' ) {
        // Might want to do something here in the future.
        // bbscript_log("warn", "Incomplete add");
        continue;
    }

    $row = clean_row($row);

    // Format for the bulkdistrict tool
    $JSON_payload[$row['id']]= array(
        'street' => $row['street_name'].' '.$row['street_type'],
        'town' => $row['town'],
        'state' => $row['state'],
        'zip5' => $row['zip'],
        'apt' => NULL,
        'building_chr' => $row['building_chr'],
        'building' => $row['building'] ,
    );

    // Keep accumulating until we reach chunk size or the end of our addresses.
    if (count($JSON_payload) < $chunk_size && $rownum != $address_count) {
        continue;
    }

    // Encode our payload and reset for the next batch
    $JSON_payload_encoded = json_encode($JSON_payload);
    $JSON_payload = array();

    // Send the cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $bulk_distassign_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $JSON_payload_encoded);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($JSON_payload_encoded)));
    $output = curl_exec($ch);
    $curl_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    bbscript_log("trace", "Received Curl in     ".round($curl_time, 3));
    $count['totalCurlTime'] += $curl_time;

    // Check for malformed response
    if (( $output === null )) {
        bbscript_log("fatal", "CURL Failed to receive a Response");
        continue;
    }

    // Parse the response and check for errors
    $response = @json_decode($output, true);

    if (($response === null && json_last_error() !== JSON_ERROR_NONE )) {
        bbscript_log("fatal", "Malformed JSON");
        continue;
    }
    else if ( count($response) == 0 ){
        bbscript_log("error", "No response from SAGE.");
        continue;
    }

    // Process the results
    $count['TOTAL'] += count($response);
    $update_payload = array();

    foreach ($response as $id => $value) {
        $status_code = $value['status_code'];
        $message = $value['message'];

        if ($status_code == "STREETNUM" || $status_code == "STREETNAME" || $status_code == "ZIPCODE" || $status_code == "SHAPEFILE") {
            $count['MATCH']++;
            $count[$status_code]++;
            bbscript_log("trace", "[MATCH - $status_code][$message] on record #".$value['address_id']);

            $update_payload[$value['address_id']] = array(
                'assembly_code'=>$value['assemblyCode'],
                'congressional_code'=>$value['congressionalCode'],
                'election_code'=>$value['electionCode'],
                'senate_code'=>$value['senateCode'],
                'county_code'=>$value['countyCode'],
                'latitude'=>$value['latitude'],
                'longitude'=>$value['longitude'],
                'geo_accuracy'=>$value['geo_accuracy'],
                'result_code' => $status_code,
                'result_message' => $message
                // 'fire_code'=>$value['matches'][0]['fire_code'],
                // 'ward_code'=>$value['matches'][0]['ward_code'],
                // 'vill_code'=>$value['matches'][0]['vill_code'],
                // 'town_code'=>$value['matches'][0]['town_code'],
                // 'cleg_code'=>$value['matches'][0]['cleg_code'],
                // 'school_code'=>$value['matches'][0]['school_code'],
            );

        }
        elseif ($status_code == "NOMATCH") {
            $count[$status_code]++;
            bbscript_log("warn", "[NOMATCH][$message] on record #".$value['address_id']);

        }
        elseif ($status_code == "INVALID") {
            $count[$status_code]++;
            bbscript_log("warn", "[INVALID][$message] on record #".$value['address_id']);
        }
        else { // Unknown status_code, what?!?
            $count['ERROR']++;
            bbscript_log("ERROR", "Unknown status [$status_code] on record ".$value['address_id']." with message [$message]");
        }
    }

    // Update districts in the database if --dryrun flag was not set
    // and insert a note describing the update.
    if (count($update_payload) > 0 && !$dry_run) {
        $update_time_start = microtime(true);
        bbscript_log("trace", "Updating ".count($update_payload)." records.");

        mysql_query("BEGIN", $db);
        foreach ($update_payload as $id => $value) {
            // bbscript_log("trace", "ID:$id - SD:{$value['senate_code']}, CO:{$value['county_code']}, CD:{$value['congressional_code']}, AD:{$value['assembly_code']}, ED:{$value['election_code']}");

            $row = $ny_address_data[$id];

            $note = "A_ID: $id \nMODE: {$value['result_code']}\n ADDRESS: ".$row['building'].' '.$row['building_chr'].' '.$row['street1'].' '.$row['street2'].', '.$row['town'].', '.$row['state'].', '.$row['zip']."\nUPDATES: SD:".getValue($row['senate_code'])."=>{$value['senate_code']}, CO:".getValue($row['county_code'])."=>{$value['county_code']}, CD:".getValue($row['congressional_code'])."=>{$value['congressional_code']}, AD:".getValue($row['assembly_code'])."=>{$value['assembly_code']}, ED:".getValue($row['election_code'])."=>{$value['election_code']}";
            $subject = "";

            // Determine if any of the districts changed and take note of it.
            foreach ( $district_codes as $code => $abbrv ) {
                if ( isset($value[$code]) && getValue($row[$code]) != $value[$code] )
                {
                    $subject .= $abbrv . ' ';
                }
            }

            // Set the appropriate subject prefix depending on whether districts changed or not.
            if ( $subject != '' ) {
                $subject = $subject_prefixes['changed'] . ' ' . $subject;
            }
            else {
                $subject = $subject_prefixes['unchanged'];
            }

            // Remove any redistricting notes that already exist for this address.
            mysql_query("
                DELETE FROM `civicrm_note`
                WHERE `entity_id` = {$row['contact_id']} AND `entity_table` = 'civicrm_contact'
                AND `subject` LIKE 'RD12%' AND note LIKE 'A_ID: {$id}%'"
            );

            // Insert a new note describing the redistricting changes.
            mysql_query("
                INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
                VALUES ('civicrm_contact', {$row['contact_id']}, '$note', 1, '".date("Y-m-d")."', '$subject', 0)", $db
            );

            // Only need to run an update query if the districts have changed.
            if ( $subject != $subject_prefixes['unchanged'] )
            {
                mysql_query("
                UPDATE civicrm_value_district_information_7
                SET congressional_district_46 = {$value['congressional_code']},
                    ny_senate_district_47 = {$value['senate_code']},
                    ny_assembly_district_48 = {$value['assembly_code']},
                    election_district_49 = {$value['election_code']},
                    county_50 = {$value['county_code']}
                WHERE civicrm_value_district_information_7.entity_id = $id", $db
                );
            }

            if ( $value['result_code'] == 'SHAPEFILE' ) {
                // Also save the new coordinate information
                // bbscript_log("TRACE", "Saving new geocoordinates: ({$value['latitude']},{$value['longitude']})");
                mysql_query("
                    UPDATE civicrm_address
                    SET geo_code_1={$value['latitude']}, geo_code_2={$value['longitude']}
                    WHERE id=$id
                ");
            }

            // Currently Unused ----------------------------------------
            // county_legislative_district_51   = {$value['cleg_code']},
            // town_52   = {$value['town_code']},
            // ward_53   = {$value['ward_code']},
            // school_district_54   = {$value['school_code']},
            // ---------------------------------------------------------
        }

        // Remove the district info for any non-NY state address that have been picked up so far.
        foreach( $non_ny_address_data as $id => $value )
        {
            $row = $value;
            $note = "A_ID: $id\nUPDATES: SD:". getValue($row['senate_code']) ."=> 0, CO:".getValue($row['county_code'])."=> 0, CD:".getValue($row['congressional_code'])."=> 0, AD:".getValue($row['assembly_code'])."=> 0, ED:".getValue($row['election_code'])."=> 0";
            $subject = $subject_prefixes['removed'];

            // Remove any redistricting notes that already exist for this address.
            mysql_query("
                DELETE FROM `civicrm_note`
                WHERE `entity_id` = {$row['contact_id']} AND `entity_table` = 'civicrm_contact'
                AND `subject` LIKE 'RD12%' AND note LIKE 'A_ID: {$id}%'"
            );

            mysql_query("INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
                VALUES ('civicrm_contact', {$row['contact_id']}, '$note', 1, '".date("Y-m-d")."', '$subject', 0)", $db
            );

            // Set district information to zero.
            mysql_query("
                UPDATE civicrm_value_district_information_7
                SET congressional_district_46 = 0,
                    ny_senate_district_47 = 0,
                    ny_assembly_district_48 = 0,
                    election_district_49 = 0,
                    county_50 = 0
                WHERE civicrm_value_district_information_7.entity_id = {$id}", $db
            );
        }

        mysql_query("COMMIT", $db);
        $update_time = get_elapsed_time($update_time_start);
        bbscript_log("trace", "Updated database in ".round($update_time, 3));
        $count['totalMysqlTime'] += $update_time;
    }
    else {
        bbscript_log("warn", "No Records to update");
    }

    // Reset the arrays to repeat the batch lookup process for the next chunk
    $ny_address_data = array();
    $non_ny_address_data = array();

    report_stats($total_found, $count, $time_start);
}

bbscript_log("info", "Completed redistricting addresses");


function report_stats($total_found, $count, $time_start) {
    // Compute percentages for certain counts
    $percent = array(
        "MATCH" => 0,
        "NOMATCH" => 0,
        "INVALID" => 0,
        "ERROR" => 0,
        "STREETNUM" => 0,
        "STREETNAME" => 0,
        "ZIPCODE" => 0,
        "SHAPEFILE" => 0,
        "OUTOFSTATE" => 0,
        "ERROR" => 0
    );

    // Timer for debug
    $time = get_elapsed_time($time_start);
    $Records_per_sec = round($count['TOTAL'] / $time, 1);
    $Mysql_per_sec = ($count['totalMysqlTime'] == 0 ) ? 0 : round($count['TOTAL'] / $count['totalMysqlTime'], 1);
    $Curl_per_sec = round($count['TOTAL'] / $count['totalCurlTime'], 1);

    // Update the percentages using the counts
    foreach ( $percent as $key => $value ) {
        $percent[$key] = round( $count[$key] / $count['TOTAL'] * 100, 2 );
    }

    $seconds_left = round(($total_found - $count['TOTAL']) / $Records_per_sec, 0);
    $finish_at = date('Y-m-d H:i:s', (time() + $seconds_left));

    bbscript_log("info", "-------    ------- ---- ---- ---- ---- ");
    bbscript_log("info", "[DONE @]           $finish_at (in ".$seconds_left." seconds)");
    bbscript_log("info", "[COUNT]            {$count['TOTAL']}");
    bbscript_log("info", "[TIME]             ".round($time, 4));

    bbscript_log("info", "[SPEED]    [TOTAL] $Records_per_sec per second (".$count['TOTAL']." in ".round($time, 3).")");
    bbscript_log("trace","[SPEED]    [MYSQL] $Mysql_per_sec per second (".$count['TOTAL']." in ".round($count['totalMysqlTime'], 3).")");
    bbscript_log("trace","[SPEED]    [CURL]  $Curl_per_sec per second (".$count['TOTAL']." in ".round($count['totalCurlTime'], 3).")");
    bbscript_log("info", "[MATCH]    [TOTAL] {$count['MATCH']} ({$percent['MATCH']} %)");
    bbscript_log("trace","[MATCH]    [EXACT] {$count['STREETNUM']} ({$percent['STREETNUM']} %)");
    bbscript_log("trace","[MATCH]    [RANGE] {$count['STREETNAME']} ({$percent['STREETNAME']} %)");
    bbscript_log("trace","[MATCH]    [ZIP5]  {$count['ZIPCODE']} ({$percent['ZIPCODE']} %)");
    bbscript_log("trace","[MATCH]    [SHAPE]  {$count['SHAPEFILE']} ({$percent['SHAPEFILE']} %)");
    bbscript_log("info", "[NOMATCH]  [TOTAL] {$count['NOMATCH']} ({$percent['NOMATCH']} %)");
    bbscript_log("info", "[INVALID]  [TOTAL] {$count['INVALID']} ({$percent['INVALID']} %)");
    bbscript_log("info", "[ERROR]    [TOTAL] {$count['ERROR']} ({$percent['ERROR']} %)");
    bbscript_log("info", "[NON_NY]   [TOTAL] {$count['OUTOFSTATE']} ({$percent['OUTOFSTATE']} %)");
}


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


function clean_row($row) {
    $match = array('/ AVENUE( EXT)?$/','/ STREET( EXT)?$/','/ PLACE/','/ EAST$/','/ WEST$/','/ SOUTH$/','/ NORTH$/','/^EAST (?!ST|AVE|RD|DR)/','/^WEST (?!ST|AVE|RD|DR)/','/^SOUTH (?!ST|AVE|RD|DR)/','/^NORTH (?!ST|AVE|RD|DR)/');
    $replace = array(' AVE$1',' ST$1',' PL',' E',' W',' S',' N','E ','W ','S ','N ');

    $street = preg_replace("/[.,']/","",strtoupper(trim($row['street_name'])));
    $row['street_name'] = preg_replace($match, $replace, $street);

    $street = preg_replace("/[.,']/","",strtoupper(trim($row['street_type'])));
    $row['street_type'] = preg_replace($match, $replace, $street);

    return $row;
}

function getValue($string)
{
    if ($string == FALSE) {
         return "null";
    }
    else {
        return $string;
    }
}
