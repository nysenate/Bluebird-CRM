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
$Count_multimatch = 0;
$Count_match = 0;
$Count_nomatch = 0;
$Count_invalid = 0;
$Count_error = 0;

$prog = basename(__FILE__);

require_once 'script_utils.php';
$stdusage = civicrm_script_usage();
$usage = "[--chunk \"number\"] [--log \"5|4|3|2|1\"] [--query \"number\"]";
$shortopts = "c:l:q";
$longopts = array("chunk=","log=","query=");
$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}

$BB_CONFIG = get_bluebird_instance_config($optList['site']);
$BULK_DISTASSIGN_URL = $BB_CONFIG['sage.api.base'].'/json/bulkdistrict/body?key='.$BB_CONFIG['sage.api.key'];
$CHUNK_SIZE = array_key_exists('chunk', $optlist) ? $optlist['chunk'] : 1000;
$LOG_LEVEL = array_key_exists('log', $optlist) ? $optlist['log'] : "trace";
$LIMIT = array_key_exists('query', $optlist) ? $optlist['query'] :"";
if($LIMIT > 0 ){
	$LIMIT = " AND address.id < ".$LIMIT;
}

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
          $LIMIT
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

    // A counter for this round
    $Count_round++;

    // if round has reached max size, curl it
    if ($Count_round >= $CHUNK_SIZE){

        $JSON_Payload_encoded = json_encode($JSON_Payload);

        // echo "[INFO] Starting Curl\n";
        $curl_time_start = microtime(true);
        sleep(1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $BULK_DISTASSIGN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $JSON_Payload_encoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($JSON_Payload_encoded)));

        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $response = @json_decode($output, true);

        // check for malformed response
        if(( $output === null )){
            echo_CLI_log("fatal", "CURL Failed to recieve a Response");
        }elseif (($response === null && json_last_error() !== JSON_ERROR_NONE )) {
            echo_CLI_log("fatal", "Malformed JSON");
        }else{
            foreach ($response as $id => $value) {
                $Count_total++;
                if($value['status_code'] == "MATCH"){
                	$Count_match++;
                    echo_CLI_log("trace","[MATCH] on record #".$value['address_id']." with message " .$value['message'] );
                    $Update_Payload[$value['address_id']] = array(
                        'town'=>$value['matches'][0]['town'],
                        // 'fire_code'=>$value['matches'][0]['fire_code'],
                        // 'ward_code'=>$value['matches'][0]['ward_code'],
                        'assembly_code'=>$value['matches'][0]['assemblyCode'],
                        'congressional_code'=>$value['matches'][0]['congressionalCode'],
                        'election_code'=>$value['matches'][0]['electionCode'],
                        'senate_code'=>$value['matches'][0]['senateCode'],
                        'state'=>$value['matches'][0]['state'],
                        'street'=>$value['matches'][0]['street'],
                        // 'vill_code'=>$value['matches'][0]['vill_code'],
                        // 'town_code'=>$value['matches'][0]['town_code'],
                        'county_code'=>$value['matches'][0]['countyCode'],
                        // 'cleg_code'=>$value['matches'][0]['cleg_code'],
                        // 'school_code'=>$value['matches'][0]['school_code'],
                        // 'bldg_num'=>$value['matches'][0]['bldg_num'],
                        'zip5'=>$value['matches'][0]['zip5'],
                        // 'apt_num'=>$value['matches'][0]['apt_num'],
                        // 'ward_code'=>$value['matches'][0]['ward_code']
                    );

                }elseif ($value['status'] == "MULTIMATCH" ) {
                 	$Count_multimatch++;
                 	echo_CLI_log("trace","[MULTIMATCH] record #".$value['address_id']." with message " .$value['message'] );

                 }elseif ($value['status'] == "NOMATCH" ) {
                    $Count_nomatch++;
                    echo_CLI_log("trace","[NOMATCH] record #".$value['address_id']." with message " .$value['message'] );

                }elseif ($value['status'] == "INVALID"){
                     $Count_invalid++;
                     echo_CLI_log("warn","[INVALID] record #".$value['address_id']." with message " .$value['message'] );

                }else{ // no status we know how to deal with
                    $Count_error++;
                    // echo_CLI_log("ERROR","on record ".$value['address_id']." with message " .$value['message'] );

                }
            }

        }

        $curl_time_end = microtime(true);
        $curl_time = $curl_time_end - $curl_time_start;
        echo_CLI_log("trace", "Recieved Curl in     ".round($curl_time, 3));
        $curl_time_total += $curl_time;

        // update database
        echo_CLI_log("trace", "Starting To update Database");
        // $Update_Payload ;
        $update_time_start = microtime(true);
 
        if(count($Update_Payload) > 0){
            $Query ="";
            mysql_query("BEGIN");

            echo_CLI_log("trace", count($Update_Payload)." records to update ");
            foreach ($Update_Payload as $id => $value) {
                // echo_CLI_log("debug", "ID:$id - SEN:{$value['senate_code']}, CO:{$value['county_code']}, CONG:{$value['congressional_code']}, ASSM:{$value['assembly_code']}, ELCT:{$value['election_code']}");

                mysql_query("UPDATE civicrm_value_district_information_7
                    SET  congressional_district_46 = ".$value['congressional_code'].",
                    ny_senate_district_47  = ".$value['senate_code'].",
                    ny_assembly_district_48  = ".$value['assembly_code'].",
                    election_district_49   = ".$value['election_code'].",
                    county_50   = ".$value['county_code']."
                    WHERE civicrm_value_district_information_7.id = $id");
                    // ",
                    // county_legislative_district_51   = ".$value['cleg_code'].",
                    // town_52   = ".$value['town_code'].",
                    // ward_53   = ".$value['ward_code'].",
                    // school_district_54   = ".$value['school_code'].",
                }

            mysql_query("COMMIT");

        }else{
            echo_CLI_log("fatal", "No Records to update");
        }

        $update_time_end = microtime(true);
        $update_time = $update_time_end - $update_time_start;
        echo_CLI_log("trace", "Updated database in     ".round($update_time, 3));

  
        // timer for debug
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $Records_per_sec = round(($Count_total / round($time,1)),1);
		$Curl_records = round(( $Count_total / $curl_time_total),1);

		echo_CLI_log("debug","---- 	----");
        echo_CLI_log("debug", "[COUNT]	$Count_total");
		echo_CLI_log("debug", "[TIME]	".round($time, 4));
        echo_CLI_log("debug", "[SPEED]	$Records_per_sec per second");
        echo_CLI_log("debug", "[CURL]	$Curl_records per second (".$Count_total." in ".round($time,1).")");

		if ($Count_match) $Match_percent = round((($Count_match / $Count_total) * 100),2);
		if ($Count_error) $Error_percent = round((($Count_error / $Count_total ) * 100),2);;
		echo_CLI_log("debug","[HIT]	$Count_match Matches ($Match_percent %) / $Count_error Error ($Error_percent %) ");

      // reset the arrays
        $JSON_Payload = array();
        $Update_Payload = array();

        // reset counter
        $Count_round=0;
    }else{
        // echo "[INFO] Added user ".$raw['id']." - ".$raw['street_address']."\n";

    }

} while ($raw != NULL);

 
echo_CLI_log("debug","---- ---- ---- ---- ---- ---- ");

// end timer
$time_end = microtime(true);
$time = $time_end - $time_start;

$Records_per_sec = round(($Count_total / round($time,1)),1);
$Curl_records = round(( $Count_total / $curl_time_total),1);
echo_CLI_log("debug","[COUNT]	$Count_total");
echo_CLI_log("debug","[TIME]	".round($time, 4));
echo_CLI_log("debug","[TOTAL] 	$Records_per_sec / second ($Count_total in ".round($time,1).")");
echo_CLI_log("debug","[CURL]	$Curl_records / second ($Count_total in ".round($curl_time_total,1).")");

// if ($Count_multimatch) $Multimatch_percent = round((($Count_multimatch / $Count_total) * 100),2);
if ($Count_match) $Match_percent = round((($Count_match / $Count_total) * 100),2);
// if ($Count_nomatch) $Nomatch_percent = round((($Count_total / $Count_nomatch) * 100),2);
// if ($Count_invalid) $Invalid_percent = round((($Count_total / $Count_invalid) * 100),2);
if ($Count_error) $Error_percent = round((($Count_error / $Count_total ) * 100),2);;

echo_CLI_log("debug","[HIT]	$Count_match Matches ($Match_percent %)/ $Count_error Error ($Error_percent %) ");
