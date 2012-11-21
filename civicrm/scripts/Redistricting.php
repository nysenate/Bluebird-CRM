<?php

// Project: BluebirdCRM
// Authors: Stefan Crain & Graylin Kim
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./Redistricting.php -S skelos --chunk 2000 --log 5 --max 10000
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

// Parse the options
require_once 'script_utils.php';
$prog = basename(__FILE__);
$shortopts = "c:l:m:d";
$longopts = array("chunk=", "log=", "max=", "dryrun");
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = "[--chunk \"number\"] [--log \"5|4|3|2|1\"] [--max \"number\"] [--dryrun]";
    error_log("Usage: $prog  $stdusage  $usage\n");
    exit(1);
}

// Use instance settings to configure for SAGE
$BB_CONFIG = get_bluebird_instance_config($optList['site']);
$SAGE_BASE = array_key_exists('sage.api.base', $BB_CONFIG) ? $BB_CONFIG['sage.api.base'] : false;
$SAGE_KEY = array_key_exists('sage.api.key', $BB_CONFIG) ? $BB_CONFIG['sage.api.key'] : false;

if (!($SAGE_BASE && $SAGE_KEY)) {
    error_log(bbscript_log("fatal", "sage.api.base and sage.api.key must be set in your bluebird.cfg file."));
    exit(1);
}

$BULK_DISTASSIGN_URL = $SAGE_BASE.'/json/bulkdistrict/body?key='.$SAGE_KEY;

// Initialize script parameters from options and defaults
$CHUNK_SIZE = ($optList['chunk'] != '') ? $optlist['chunk'] : 1000;
$LOG_LEVEL = array_key_exists('log', $optlist) ? $optlist['log'] : "TRACE";
$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper($LOG_LEVEL)][0];
$DRYRUN = array_key_exists('dryrun',$optlist) ? $optlist['dryrun'] : false;
$MAX_ID = array_key_exists('max', $optlist) ? $optlist['max'] : false;

if ($MAX_ID && is_numeric($MAX_ID)) {
    $MAX_ID_CONDITION = " AND address.id < ".$MAX_ID;
}
else {
    $MAX_ID_CONDITION = "";
}

bbscript_log("debug", "Starting with $prog with Chunk size of $CHUNK_SIZE");

// Initialize CiviCRM
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

// Establish a connection to the instance database
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

// Collect NY state addresses with a street_address; any
// address not matching this criteria will fail lookup.

$query = "
    SELECT address.id,
           address.contact_id,
           address.street_name AS street1,
           address.street_type AS street2,
           address.city AS town,
           'NY' AS state,
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
      AND state_province.abbreviation='NY'
      AND IFNULL(address.street_name,'') != ''
      $MAX_ID_CONDITION
    ORDER BY address.id ASC
";

bbscript_log("debug", "Querying the database for addresses using\n$query");
$result = mysql_query($query, $db);
$total_found = mysql_num_rows($result);

bbscript_log("debug", $total_found." addresses found.");
// start timer
$time_start = microtime(true);

// Counts for looping
$Count_total = 0;
$Count_round = 0;
$Count_multimatch = 0;
$Count_match = 0;
$Count_nomatch = 0;
$Count_invalid = 0;
$Count_error = 0;
$Count_ExactMatch = 0;
$Count_ConsolidatedRangefill = 0;
$Count_ConsolidatedMultimatch = 0;
$Count_RangefillFailure = 0;
$Count_NotFound = 0;
$curl_time_total = 0;

$row_data = array();
$JSON_Payload = array();
$address_count = mysql_num_rows($result);

for ($rownum = 1; $rownum <= $address_count; $rownum++) {
    // Fetch the new row, no null check needed since we have the count
    // If we do pull back a NULL something bad happened and dying is okay
    $row = mysql_fetch_assoc($result);
    $row_data[$row['id']] = $row;

    // Clean the data manually till we can do mass validation.
    $town = clean($row['town']);
    $row['town'] = preg_replace(array('/^EAST /','/^WEST /','/^SOUTH /','/^NORTH /', '/ SPRINGS$/','/PETERSBURG$/'),array('E ','W ','S ','N ',' SPGS','PETERSBURGH'),$town);

    $match = array('/ AVENUE( EXT)?$/','/ STREET( EXT)?$/','/ PLACE/','/ EAST$/','/ WEST$/','/ SOUTH$/','/ NORTH$/','/^EAST (?!ST|AVE|RD|DR)/','/^WEST (?!ST|AVE|RD|DR)/','/^SOUTH (?!ST|AVE|RD|DR)/','/^NORTH (?!ST|AVE|RD|DR)/');
    $replace = array(' AVE$1',' ST$1',' PL',' E',' W',' S',' N','E ','W ','S ','N ');

    $street = clean($row['street2']);
    $row['street2'] = preg_replace($match, $replace, $street);

    $street = clean($row['street1']);
    $row['street1'] = preg_replace($match, $replace, $street);

    // Format for the bulkdistrict tool
    $JSON_Payload[$row['id']]= array(
        'street' => $row['street1'].' '.$row['street2'],
        'town' => $row['town'],
        'state' => $row['state'],
        'zip5' => $row['zip'],
        'apt' => NULL,
        'building_chr' => $row['building_chr'],
        'building' => $row['building'] ,
    );

    // keep accumulating until we reach CHUNK_SIZE or the end of our addresses.
    if (count($JSON_Payload) < $CHUNK_SIZE && $rownum != $address_count) {
        continue;
    }

    // Encode our payload and reset for the next batch
    $JSON_Payload_encoded = json_encode($JSON_Payload);
    $JSON_Payload = array();

    // Send the cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $BULK_DISTASSIGN_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $JSON_Payload_encoded);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($JSON_Payload_encoded)));
    $output = curl_exec($ch);
    $curl_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    curl_close($ch);
    bbscript_log("trace", "Received Curl in     ".round($curl_time, 3));
    $curl_time_total += $curl_time;

    // check for malformed response
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

    // Process the results
    $Count_total += count($response);
    $Update_Payload = array();

    foreach ($response as $id => $value) {
        $status_code = $value['status_code'];
        $message = $value['message'];

        if ($status_code == "MATCH") {
            $Count_match++;
            bbscript_log("trace", "[MATCH][".$value['message']."] on record #".$value['address_id']);
            if ($message == "EXACT MATCH") {
                $Count_ExactMatch++;
            }
            elseif ($message == "CONSOLIDATED RANGEFILL") {
                $Count_ConsolidatedRangefill++;
            }
            elseif ($message == "CONSOLIDATED MULTIMATCH") {
                $Count_ConsolidatedMultimatch++;
            }

            $Update_Payload[$value['address_id']] = array(
                'assembly_code'=>$value['assemblyCode'],
                'congressional_code'=>$value['congressionalCode'],
                'election_code'=>$value['electionCode'],
                'senate_code'=>$value['senateCode'],
                'county_code'=>$value['countyCode'],
                // 'town'=>$value['matches'][0]['town'],
                // 'state'=>$value['matches'][0]['state'],
                // 'street'=>$value['matches'][0]['street'],
                // 'zip5'=>$value['matches'][0]['zip5'],
                // 'fire_code'=>$value['matches'][0]['fire_code'],
                // 'ward_code'=>$value['matches'][0]['ward_code'],
                // 'vill_code'=>$value['matches'][0]['vill_code'],
                // 'town_code'=>$value['matches'][0]['town_code'],
                // 'cleg_code'=>$value['matches'][0]['cleg_code'],
                // 'school_code'=>$value['matches'][0]['school_code'],
                // 'bldg_num'=>$value['matches'][0]['bldg_num'],
                // 'apt_num'=>$value['matches'][0]['apt_num'],
                // 'ward_code'=>$value['matches'][0]['ward_code']
            );

        }
        elseif ($status_code == "MULTIMATCH") { // shouldn't exist anymore
            $Count_multimatch++;
            bbscript_log("warn", "[MULTIMATCH][".$value['message']."] on record #".$value['address_id']);

        }
        elseif ($status_code == "NOMATCH") {
            if ($message == "RANGEFILL") {
                $Count_RangefillFailure++;
            }
            else {
                $Count_NotFound++;
            }

            $Count_nomatch++;
            bbscript_log("warn", "[NOMATCH][".$value['message']."] on record #".$value['address_id']);

        }
        elseif ($status_code == "INVALID") {
             $Count_invalid++;
             bbscript_log("warn", "[INVALID][".$value['message']."] on record #".$value['address_id']);
        }
        else { // Uknown status_code, what?!?
            $Count_error++;
            bbscript_log("ERROR", "on record ".$value['address_id']." with message " .$value['message'] );
        }
    }

    // Store them into the database
    if (count($Update_Payload) > 0 && !$DRY_RUN) {
        $update_time_start = microtime(true);
        bbscript_log("trace", "Updating ".count($Update_Payload)." records.");

        mysql_query("BEGIN", $db);
        foreach ($Update_Payload as $id => $value) {
            // bbscript_log("trace", "ID:$id - SEN:{$value['senate_code']}, CO:{$value['county_code']}, CONG:{$value['congressional_code']}, ASSM:{$value['assembly_code']}, ELCT:{$value['election_code']}");

            $row = $row_data[$id];
     
            $note = "ADDRESS ID:$id \n ADDRESS:".$row['street1']." ".$row['street2'].", ".$row['town']." ". $row['state'].", ".$row['zip']." ".$row['building']." ".$row['building_chr']." \n UPDATES: SEN:".getValue($row['senate_code'])."=>{$value['senate_code']}, CO:".getValue($row['county_code'])."=>{$value['county_code']}, CONG:".getValue($row['congressional_code'])."=>{$value['congressional_code']}, ASSM:".getValue($row['assembly_code'])."=>{$value['assembly_code']}, ELCT:".getValue($row['election_code'])."=>{$value['election_code']}";

            /*********************************************************
            ** Commenting out this block of code.
            ** This is the CiviCRM API method for adding a note.
            ** It is too slow for our purposes.
            $params = array(
                'entity_table' => 'civicrm_contact',
                'entity_id' => $row['contact_id'],
                'note' => $note,
                'contact_id' => 1,
                'modified_date' => date("Y-m-d"),
                'subject' => 'Redistricting update'.date("m-d-Y"),
                'version' => 3,
            );
            require_once 'api/api.php';
            $civi_result = civicrm_api('note', 'create', $params);
            ** END OF COMMENTED OUT CODE
            **********************************************************/

            mysql_query("
                INSERT INTO civicrm_note (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
                VALUES ('civicrm_contact',{$row['contact_id']},'$note', 1, '".date("Y-m-d")."', 'Redistricting update 3 ".date("m-d-Y")."',0)", $db
            );

            mysql_query("
                UPDATE civicrm_value_district_information_7
                SET congressional_district_46 = {$value['congressional_code']},
                    ny_senate_district_47 = {$value['senate_code']},
                    ny_assembly_district_48 = {$value['assembly_code']},
                    election_district_49 = {$value['election_code']},
                    county_50 = {$value['county_code']}
                WHERE civicrm_value_district_information_7.entity_id = $id", $db
            );
            // ",
            // county_legislative_district_51   = {$value['cleg_code']},
            // town_52   = {$value['town_code']},
            // ward_53   = {$value['ward_code']},
            // school_district_54   = {$value['school_code']},
        }
        mysql_query("COMMIT", $db);

        $update_time = get_elapsed_time($update_time_start);
        bbscript_log("trace", "Updated database in ".round($update_time, 3));

    }
    else {
        bbscript_log("warn", "No Records to update");
    }

    $row_data = array();

    // timer for debug
    $time = get_elapsed_time($time_start);
    $Records_per_sec = round(($Count_total / $time),1);
    $Mysql_per_sec = round(($Count_total / ($time - $curl_time_total)),1);
    $Curl_per_sec = round(($Count_total / $curl_time_total),1);
    $Multimatch_percent = round((($Count_multimatch / $Count_total) * 100),2);
    $Match_percent = round((($Count_match / $Count_total) * 100),2);
    $Nomatch_percent = round((($Count_nomatch / $Count_total) * 100),2);
    $Invalid_percent = round((($Count_invalid / $Count_total) * 100),2);
    $Error_percent = round((($Count_error / $Count_total ) * 100),2);;
    $ExactMatch_percent = round((($Count_ExactMatch / $Count_total ) * 100),2);;
    $ConsolidatedRangefill_percent = round((($Count_ConsolidatedRangefill / $Count_total ) * 100),2);;
    $ConsolidatedMultimatch_percent = round((($Count_ConsolidatedMultimatch / $Count_total ) * 100),2);;
    $RangefillFailure_percent = round((($Count_RangefillFailure / $Count_total ) * 100),2);;
    $NotFound_percent = round((($Count_NotFound / $Count_total ) * 100),2);;

    $seconds_left = round((($total_found -$Count_total ) / $Records_per_sec ), 0);
    $finish_at = date('Y-m-d H:i:s', (time() + $seconds_left));

    bbscript_log("info", "-------    ------- ---- ---- ---- ---- ");
    bbscript_log("info", "[DONE @]           $finish_at (in ".$seconds_left." seconds)");
    bbscript_log("info", "[COUNT]            $Count_total");
    bbscript_log("info", "[TIME]             ".round($time, 4));

    bbscript_log("info", "[SPEED]    [TOTAL] $Records_per_sec per second (".$Count_total." in ".round($time,3).")");
    bbscript_log("trace", "[SPEED]    [MYSQL] $Mysql_per_sec per second (".$Count_total." in ".round(($time - $curl_time_total),3).")");
    bbscript_log("trace", "[SPEED]    [CURL]  $Curl_per_sec per second (".$Count_total." in ".round($curl_time_total,3).")");
    bbscript_log("info", "[MATCH]    [TOTAL] $Count_match ($Match_percent %)");
    bbscript_log("trace", "[MATCH]    [EXACT] $Count_ExactMatch ($ExactMatch_percent %)");
    bbscript_log("trace", "[MATCH]    [RANGE] $Count_ConsolidatedRangefill ($ConsolidatedRangefill_percent %)");
    bbscript_log("trace", "[MATCH]    [MULTI] $Count_ConsolidatedMultimatch ($ConsolidatedMultimatch_percent %)");
    bbscript_log("info", "[NOMATCH]  [TOTAL] $Count_nomatch ($Nomatch_percent %)");
    bbscript_log("trace", "[NOMATCH]  [RANGE] $Count_RangefillFailure ($RangefillFailure_percent %)");
 // bbscript_log("info", "[NOMATCH]  [NO]    $Count_NotFound ($NotFound_percent %)"); // not nessisary, only 2 options
    bbscript_log("info", "[MULTI]    [TOTAL] $Count_multimatch ($Multimatch_percent %)");
    bbscript_log("info", "[INVALID]  [TOTAL] $Count_invalid ($Invalid_percent %)");
    bbscript_log("info", "[ERROR]    [TOTAL] $Count_error ($Error_percent %)");

}


function clean($string)
{
    return preg_replace("/[.,']/","",strtoupper(trim($string)));
}


function getValue($string)
{
    if ($string == FAlSE) {
         return "null";
    }
    else {
        return $string;
    }
}
