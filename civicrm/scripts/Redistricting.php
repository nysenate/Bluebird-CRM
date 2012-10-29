<?php

// Project: BluebirdCRM
// Authors: Stefan Crain & Graylin Kim
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-10-26

// ./Redistricting.php -S skelos --chunk "2000" --log "5"


error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

// start timer
$time_start = microtime(true);

// JSON Payload
$JSON_Payload = array();
$Update_Payload = array();

// Counts for looping
$Count_total = 0;
$Count_round = 0;

$prog = basename(__FILE__);

require_once 'script_utils.php';
$stdusage = civicrm_script_usage();
$usage = "[--chunk \"number\"] [--log \"5|4|3|2|1\"]";
$shortopts = "c:l";
$longopts = array("chunk=","log=");
$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}

$BB_CONFIG = get_bluebird_instance_config($optList['site']);
$BULK_DISTASSIGN_URL = $BB_CONFIG['sage.api.base'].'/json/bulkdistrict/body';
$CHUNK_SIZE = array_key_exists('chunk', $optlist) ? $optlist['chunk'] : 1000;
$LOG_LEVEL = array_key_exists('log', $optlist) ? $optlist['log'] : "trace";

// quicker CLI Logs
function echo_CLI_log($message_level, $message){
    global $LOG_LEVEL;

    $timestamp = date('G:i:s');
    $message_level = strtolower($message_level);
    $color_end= "\033[0m";

    if($message_level == "trace"){
        $log_num = 5 ;
        $color_start= "\033[35m";
    }elseif($message_level == "debug"){
        $log_num = 4;
        $color_start= "\33[1;35m";
    }elseif($message_level == "info"){
        $log_num = 3;
        $color_start= "\33[33m";
    }elseif($message_level == "warn"){
        $log_num = 2;
        $color_start= "\33[1;33m";
    }elseif($message_level == "error"){
        $log_num = 1;
        $color_start= "\33[31m";
    }elseif($message_level == "fatal"){
        $log_num = 0;
        $color_start= "\33[1;31m";
    }
    $message_level = strtoupper($message_level);
    if($LOG_LEVEL >= $log_num){
         echo "[$timestamp] [$color_start$message_level$color_end]    ".$message.$color_end."\n";
    }
}

echo_CLI_log("debug", "Starting with $prog with Chunk size of $CHUNK_SIZE");

// exit();
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();

// connect to db
$nyss_conn = new CRM_Core_DAO();
$nyss_conn = $nyss_conn->getDatabaseConnection();
$db = $nyss_conn->connection;

// Collect NY state addresses with a street_address; any
// address not matching this criteria will fail lookup.
$query = "SELECT address.id,
                 address.street_name AS street1,
                 address.street_type AS street2,
                 address.city AS town,
                 'NY' AS state,
                 address.postal_code AS zip,
                 address.street_number_suffix AS apt,
                 address.street_number AS building
        FROM civicrm_address as address
        JOIN civicrm_state_province as state_province
        WHERE address.state_province_id=state_province.id
          AND state_province.abbreviation='NY'
          AND IFNULL(address.street_address,'') != ''
        ORDER BY address.id ASC";

$result = mysql_query($query, $db);

do {
    $raw = mysql_fetch_assoc($result);

    $JSON_Payload[$raw['id']]= array(
        'street' => $raw['street1'].' '.$raw['street2'],
        'town' => $raw['town'],
        'state' => $raw['state'],
        'zip5' => $raw['zip'],
        'apt' => $raw['apt'],
        'building' => $raw['building'] ,
    );

    // A counter for this round, and total
    $Count_round++;
    $Count_total++;

    // if round has reached max size, curl it
    if ($Count_round >= $CHUNK_SIZE){

        $JSON_Payload_encoded = json_encode($JSON_Payload);

        // echo "[INFO] Starting Curl\n";
        $curl_time_start = microtime(true);
        sleep(1);

        $ch = curl_init();
        var_dump($BULK_DISTASSIGN_URL);
        curl_setopt($ch, CURLOPT_URL, $BULK_DISTASSIGN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $JSON_Payload_encoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($JSON_Payload_encoded)));
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        // $output = '[
        //   {
        //     "status": "MATCH",
        //     "matches": [
        //       {
        //         "town": "Brooklyn",
        //         "fire_code": "FF",
        //         "ward_code": "",
        //         "assembly_code": 2,
        //         "congressional_code": 3,
        //         "election_code": 4,
        //         "senate_code": 1,
        //         "state": "NY",
        //         "street": "Avenue D",
        //         "vill_code": "",
        //         "town_code": "BROOK",
        //         "county_code": 60,
        //         "cleg_code": "",
        //         "school_code": "RDK",
        //         "bldg_num": 1001,
        //         "zip5": 11203,
        //         "apt_num": 4516
        //       }
        //     ],
        //     "address_id": 1001,
        //     "message": ""
        //   },
        //   {
        //     "status": "INVALID",
        //     "matches": [],
        //     "address_id": 1002,
        //     "message": "Street address required"
        //   },
        //   {
        //     "status": "NOMATCH",
        //     "matches": [],
        //     "address_id": 1003,
        //     "message": ""
        //   },
        //   {
        //     "status": "MULTIMATCH",
        //     "matches": [{
        //         "town": "Brooklyn",
        //         "fire_code": "FF",
        //         "ward_code": "",
        //         "assembly_code": 2,
        //         "congressional_code": 3,
        //         "election_code": 4,
        //         "senate_code": 1,
        //         "state": "NY",
        //         "street": "East New York Avenue ",
        //         "vill_code": "",
        //         "town_code": "BROOK",
        //         "county_code": 60,
        //         "cleg_code": "",
        //         "school_code": "RDK",
        //         "bldg_num": 1004,
        //         "zip5": 11203,
        //         "apt_num": 720
        //       },
        //       {
        //         "town": "Brooklyn",
        //         "fire_code": "FF",
        //         "ward_code": "",
        //         "assembly_code": 2,
        //         "congressional_code": 3,
        //         "election_code": 4,
        //         "senate_code": 1,
        //         "state": "NY",
        //         "street": "East New York Avenue ",
        //         "vill_code": "",
        //         "town_code": "BROOK",
        //         "county_code": 60,
        //         "cleg_code": "",
        //         "school_code": "RDK",
        //         "bldg_num": 1004,
        //         "zip5": 11203,
        //         "apt_num": 720
        //       },
        //       {
        //         "town": "Brooklyn",
        //         "fire_code": "FF",
        //         "ward_code": "",
        //         "assembly_code": 2,
        //         "congressional_code": 3,
        //         "election_code": 4,
        //         "senate_code": 1,
        //         "state": "NY",
        //         "street": "Avenue D",
        //         "vill_code": "",
        //         "town_code": "BROOK",
        //         "county_code": 60,
        //         "cleg_code": "",
        //         "school_code": "RDK",
        //         "bldg_num": 1004,
        //         "zip5": 11203,
        //         "apt_num": 720
        //       }
        //     ],
        //     "address_id": 1004,
        //     "message": ""
        //   }
        // ]';

        $response = @json_decode($output, true);

        // check for malformed response
        if(( $output === null )){
            echo_CLI_log("fatal", "CURL Failed to recieve a Response");
        }elseif (($response === null && json_last_error() !== JSON_ERROR_NONE )) {
            echo_CLI_log("fatal", "Malformed JSON");
        }else{
            foreach ($response as $id => $value) {
                if($value['status'] == "MATCH"){
                    echo_CLI_log("trace","[MATCH] on record #".$value['address_id']." with message " .$value['message'] );
                    $Update_Payload[$value['address_id']] = array(
                        'town'=>$value['matches'][0]['town'],
                        'fire_code'=>$value['matches'][0]['fire_code'],
                        'ward_code'=>$value['matches'][0]['ward_code'],
                        'assembly_code'=>$value['matches'][0]['assembly_code'],
                        'congressional_code'=>$value['matches'][0]['congressional_code'],
                        'election_code'=>$value['matches'][0]['election_code'],
                        'senate_code'=>$value['matches'][0]['senate_code'],
                        'state'=>$value['matches'][0]['state'],
                        'street'=>$value['matches'][0]['street'],
                        'vill_code'=>$value['matches'][0]['vill_code'],
                        'town_code'=>$value['matches'][0]['town_code'],
                        'county_code'=>$value['matches'][0]['county_code'],
                        'cleg_code'=>$value['matches'][0]['cleg_code'],
                        'school_code'=>$value['matches'][0]['school_code'],
                        'bldg_num'=>$value['matches'][0]['bldg_num'],
                        'zip5'=>$value['matches'][0]['zip5'],
                        'apt_num'=>$value['matches'][0]['apt_num'],
                        'ward_code'=>$value['matches'][0]['ward_code']);

                }elseif ($value['status'] == "MULTIMATCH" ) {
                    echo_CLI_log("trace","[MULTIMATCH] record #".$value['address_id']." with message " .$value['message'] );

                 }elseif ($value['status'] == "NOMATCH" ) {
                    echo_CLI_log("trace","[NOMATCH] record #".$value['address_id']." with message " .$value['message'] );

                }elseif ($value['status'] == "INVALID"){
                    echo_CLI_log("warn","[INVALID] record #".$value['address_id']." with message " .$value['message'] );

                }else{ // no status we know how to deal with
                    echo_CLI_log("ERROR","on record ".$value['address_id']." with message " .$value['message'] );

                }
            }

        }

        $curl_time_end = microtime(true);
        $curl_time = $curl_time_end - $curl_time_start;
        echo_CLI_log("debug", "Recieved Curl in     ".round($curl_time, 3));

        // update database
        echo_CLI_log("trace", "Starting To update Database");
        // $Update_Payload ;
        $update_time_start = microtime(true);
        sleep(1);

        if(count($Update_Payload) > 0){
            $Query ="";
            mysql_query("BEGIN");

            echo_CLI_log("debug", count($Update_Payload)." records to update ");
            foreach ($Update_Payload as $id => $value) {
                echo_CLI_log("debug", "$id - ".$value['congressional_code']);

                mysql_query("UPDATE civicrm_value_district_information_7
                    SET  congressional_district_46 = ".$value['congressional_code'].",
                    ny_senate_district_47  = ".$value['senate_code'].",
                    ny_assembly_district_48  = ".$value['assembly_code'].",
                    election_district_49   = ".$value['election_code'].",
                    county_50   = ".$value['county_code'].",
                    county_legislative_district_51   = ".$value['cleg_code'].",
                    town_52   = ".$value['town_code'].",
                    ward_53   = ".$value['ward_code'].",
                    school_district_54   = ".$value['school_code'].",
                    WHERE civicrm_value_district_information_7.id = $id");
                }

            mysql_query("COMMIT");

        }else{
            echo_CLI_log("fatal", "No Records to update");
        }

        $update_time_end = microtime(true);
        $update_time = $update_time_end - $update_time_start;
        echo_CLI_log("debug", "Updated database in     ".round($update_time, 3));

        // reset the arrays
        $JSON_Payload = array();
        $Update_Payload = array();

        // reset counter
        $Count_round=0;
        // timer for debug
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo_CLI_log("debug", "Current Count: $Count_total     @ ".round($time, 4)."     CURL in ".round($curl_time, 4));
        // exit();

    }else{
        // echo "[INFO] Added user ".$raw['id']." - ".$raw['street_address']."\n";

    }

} while ($raw != NULL);

// print_r($JSON_Payload);

// end timer
$time_end = microtime(true);
$time = $time_end - $time_start;

echo_CLI_log("debug","Generated $Count_total records in $time");
