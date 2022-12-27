<?php

// Project: BluebirdCRM
// Authors: Ash Islam
// Organization: New York State Senate
// Date: 2013-1-25

/*
Redistricting Election District Fix ( adapted from Redistricting.php )
-------------------------------------------------------------------------------------
The street file matches with match type STREET always reported ED = 0 
due to a bug in the consolidate function in SAGE. This script is intended to:

1. Gather all contacts with addresses that were distassigned using STREET level match.
2. Rerun street district lookup on SAGE for those contacts and get the correct ED code.
3. Correct the ED in the notes and? the district table according to the following plan: 

*N,M,X are postive integers and `ED in table` means the value in the dist table.
-------------------------------------------------------------------------------------------
Case | Snippet in the note  | ED in table | New SAGE ED | Action                 
-------------------------------------------------------------------------------------------
1     ED:null==null          ED: null      ED: 0         Ignore
2     ED:0==0                ED: 0         ED: 0         Ignore
3     ED:N=>0                ED: 0         ED: 0         Ignore
4     ED:N=>0                ED: 0         ED: N         Update: ED:N==N, Set: Dist value N 
5     ED:N=>0                ED: 0         ED: M         Update: ED:N=>M, Set: Dist value M
6     ED:0=>0                ED: 0         ED: N         Update: ED:0=>N, Set: Dist value N
7     ED:N=>0                ED: N         ED: N         Update: ED:N==N
8     ED:N=>X                ED: X         ED: M         Log (Mismatch)
-------------------------------------------------------------------------------------------
*/

// ./Redistricting.php -S skelos --batch 2000 --log 5 --max 10000
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_BATCH_SIZE', 1000);
define('UPDATE_NOTES', 1);
define('UPDATE_DISTRICTS', 2);
define('UPDATE_ALL', 3);
define('REDIST_NOTE', 'REDIST2012');

// Parse the following user options
require_once realpath(dirname(__FILE__)).'/../script_utils.php';
$shortopts = "b:l:m:f:d";
$longopts = array("batch=", "log=", "max=", "startfrom=", "dryrun");
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--batch SIZE] [--log "TRACE|DEBUG|INFO|WARN|ERROR|FATAL"] [--max COUNT] [--startfrom ADDRESS_ID] [--dryrun]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

// Use user options to configure the script
$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper(get($optlist, 'log', 'TRACE'))][0];
$BB_UPDATE_FLAGS = UPDATE_ALL;
$opt_batch_size = get($optlist, 'batch', DEFAULT_BATCH_SIZE);
$opt_dry_run = get($optlist, 'dryrun', false);
$opt_max = get($optlist, 'max', 0);
$opt_startfrom = get($optlist, 'startfrom', 0);

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
bbscript_log(LL::DEBUG, "Option: BATCH_SIZE=$opt_batch_size");
bbscript_log(LL::DEBUG, "Option: LOG_LEVEL=$BB_LOG_LEVEL");
bbscript_log(LL::DEBUG, "Option: DRY_RUN=".($opt_dry_run ? "TRUE" : "FALSE"));
bbscript_log(LL::DEBUG, "Option: SAGE_API=$sage_base");
bbscript_log(LL::DEBUG, "Option: SAGE_KEY=$sage_key");
bbscript_log(LL::DEBUG, "Option: STARTFROM=".($opt_startfrom ? $opt_startfrom : "NONE"));
bbscript_log(LL::DEBUG, "Option: MAX=".($opt_max ? $opt_max : "NONE"));

// District mappings for Notes, Distinfo, and SAGE
$FIELD_MAP = array(
    'ED' => array('db'=>'election_district_49', 'sage'=>'election_code')
);

$DIST_FIELDS = array('ED');

// Construct the url with all our options...
$bulkdistrict_url = "$sage_base/json/bulkdistrict/body?threadCount=3&key=$sage_key&useGeocoder=0&useShapefiles=0";

// Track the time
$script_start_time = microtime(true);

// Get CiviCRM database connection
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config =& CRM_Core_Config::singleton();
$dao = new CRM_Core_DAO();
$db = $dao->getDatabaseConnection()->connection;

if ($opt_dry_run) {
  $BB_UPDATE_FLAGS = 0;
}

start_process($db, $opt_startfrom, $opt_batch_size, $opt_max, $bulkdistrict_url);

$elapsed_time = round(get_elapsed_time($script_start_time), 3);
bbscript_log(LL::INFO, "Fixed election districts in $elapsed_time seconds.");
exit(0);

$cnts = array(
   'FIXED' => 0,
   'MISMATCH'=>0,
);

function start_process($db, $startfrom = 0, $batch_size, $max_addrs = 0, $url)
{
  bbscript_log(LL::TRACE, "==> fix_election_districts()");
  
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
    $formatted_batch = array();
    $orig_batch = array();
    $batch_rec_cnt = mysqli_num_rows($res);

    if ($batch_rec_cnt == 0) {
      bbscript_log(LL::TRACE, "No more rows to retrieve");
      break;
    }

    bbscript_log(LL::DEBUG, "Query complete; about to fetch batch of $batch_rec_cnt records");

    while ($row = mysqli_fetch_assoc($res)) {
      $addr_id = $row['id'];
      $note_subject = $row['subject'];
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

      // Remove any PO Box information from street address.
      if (preg_match('/^p\.?o\.?\s+(box\s+)?[0-9]+$/i', $street)) {
        $street = '';
      }

      // Format the address for sage
      $formatted_batch[$addr_id] = array(
        'street' => $street,
        'town' => $row['city'],
        'state' => $row['state'],
        'zip5' => $row['postal_code'],
        'apt' => null,
        'building' => $row['street_number'],
        'building_chr' => $row['street_number_suffix'],
      );      
    }

    // Send formatted addresses to SAGE for geocoding & district assignment
    $batch_results = distassign($formatted_batch, $url, $counters);
    bbscript_log(LL::DEBUG, "About to fix election codes using SAGE result");

    if ($batch_results && count($batch_results) > 0) {
       fix_election_districts($db, $orig_batch, $batch_results);      
    }
    else {
      bbscript_log(LL::ERROR, "No batch results; skipping processing for address IDs starting at $start_id.");
    }

    $start_id = $addr_id + 1;
    bbscript_log(LL::INFO, "$total_rec_cnt address records fetched so far");
  }
} // handle_in_state()

function fix_election_districts($db, &$orig_batch, &$batch_results)
{
  global $BB_UPDATE_FLAGS, $DIST_FIELDS, $FIELD_MAP, $cnts;

  $batch_start_time = microtime(true);

  $addr_ids = array_keys($orig_batch);
  $addr_lo_id = min($addr_ids);
  $addr_hi_id = max($addr_ids);

  // Iterate over all batch results and update Bluebird tables accordingly.
  bbscript_log(LL::DEBUG, "Investigating ".count($batch_results)." addresses");

  bb_mysql_query('BEGIN', $db, true);

  foreach ($batch_results as $batch_res) {
    $address_id = $batch_res['address_id'];
    $status_code = $batch_res['status_code'];
    $message = $batch_res['message'];
    $orig_rec = $orig_batch[$address_id];
    
    switch ($status_code) {      
      case 'HOUSE': // Allow for the possibility that street lookup might return house?
      case 'STREET':
        
        $note_id = $orig_rec['note_id'];
        $note_text = $orig_rec['note'];
        $note_subject = $orig_rec['subject'];

        $note_matches = array();
        preg_match('/.*ED:([0-9]+)(?:==|=>|~=)([0-9]+)/', $orig_rec['note'], $note_matches);
        $ed_note = $note_matches[0];
        $ed_previous = $note_matches[1];
        $ed_assigned = $note_matches[2];
        $ed_in_table = $orig_rec[$FIELD_MAP['ED']['db']];
        $new_ed_from_sage = $batch_res['election_code']; 

        /* Regexes */
        $ed_note_replace = "/(ED:[0-9]+)(==|=>|~=)([0-9]+)/";

        if (count($note_matches)){  
          
          if ($new_ed_from_sage > 0){

            /*Note       ED:N=>0 
              Dist Table ED: 0 
              Correct    ED: N or M 
              Action:    Update note to read ED:N==N or ED:N=>M and set correct dist value in table if needed
            */
            if ($ed_assigned == 0){
              
                if ($ed_previous == $new_ed_from_sage) {
                    $new_note = preg_replace('/(ED:[0-9]+)(==|=>|~=)([0-9]+)/', "$1==$new_ed_from_sage", $orig_rec['note']);
                    $new_subject = preg_replace('/(,ED|ED,|,ED,|ED$)/', '', $note_subject);
                    if (preg_match('/UPDATED \[id=\d+\]:\s*$/', $new_subject)){
                        // Change UPDATED to VERIFIED if only the election district changed previously.
                        $new_subject = preg_replace('/UPDATED/', 'VERIFIED', $new_subject);
                        bbscript_log(LL::TRACE, "Note subject should say 'VERIFIED' now");    
                    }
                }
                else {
                    $new_note = preg_replace('/(ED:[0-9]+)(==|=>|~=)([0-9]+)/', "$1=>$new_ed_from_sage", $orig_rec['note']);
                    $new_subject = $note_subject;
                    if (preg_match('/VERIFIED/', $new_subject)){
                        $new_subject = preg_replace('/VERIFIED/', 'UPDATED', $new_subject . " ED");
                    }                          
                }
                
                if ($ed_in_table == 0 || $ed_in_table == $new_ed_from_sage){
                    $cnts['FIXED_NOTES']++;
                    bbscript_log(LL::DEBUG,"[FIX NOTES | " . (($ed_previous == $new_ed_from_sage) ? "SAME ED" : "DIFF ED") . "] Address ID: {$address_id} - Note: $ed_note Assigned in table: $ed_in_table New ED: $new_ed_from_sage");
                    update_note($db, $note_id, $new_note, $new_subject);   

                    // Only want to update instances where the district was set to zero.
                    if ($ed_in_table == 0){
                        $cnts['FIXED_DIST']++;
                        bbscript_log(LL::DEBUG,"[FIX DIST] Address ID: {$address_id} - Note: $ed_note Assigned in table: $ed_in_table New ED: $new_ed_from_sage");
                        update_election_district($db, $address_id, $new_ed_from_sage);    
                    }
                    // Somehow the district may already be set to the correct value.
                    else {
                        bbscript_log(LL::WARN, "[ALREADY SET] Redist Note [id:$note_id] shows $ed_note, but district table is already set with the correct ED = $ed_in_table.");
                    }                    
                }
                // Don't know how to deal with a mismatch between the new sage ed and the district stored in the table.
                else {
                    $cnts['MISMATCH']++;
                    bbscript_log(LL::WARN, "[MISMATCH] Redist Note [id:$note_id] shows $ed_note but in the district table ED = $ed_in_table and SAGE returned ED = $new_ed_from_sage. $message");
                } 
            }
          }
        }
             
        break;
      
      default:
        $batch_cntrs['ERROR']++;
        bbscript_log(LL::ERROR, "Status [$status_code] on record #$address_id with message [$message]");
      }      
  }

  bb_mysql_query('COMMIT', $db, true);
  bbscript_log(LL::INFO, sprintf("NOTES FIXED %d | DIST UPDATED %d | MISMATCHES %d", $cnts['FIXED_NOTES'], $cnts['FIXED_DIST'], $cnts['MISMATCH'] ));

} // process_batch_results()

function retrieve_addresses($db, $start_id = 0, $max_res = 0, $in_state = true)
{
  global $FIELD_MAP, $DIST_FIELDS;

  $limit_clause = ($max_res > 0 ? "LIMIT $max_res" : "");
  $state_compare_op = $in_state ? '=' : '!=';
  $dist_colnames = array();

  foreach ($DIST_FIELDS as $abbrev) {
    $dist_colnames[] = "di.".$FIELD_MAP[$abbrev]['db'];
  }

  $q = "SELECT a.id, a.contact_id,
               a.street_address, a.street_number, a.street_number_suffix,
               a.street_name, a.street_type, a.city, a.postal_code,
               a.supplemental_address_1, a.supplemental_address_2,
               a.geo_code_1, a.geo_code_2,
               n.id AS note_id, n.subject, n.note,
               di.id as district_id,
              ".implode(",\n", $dist_colnames)."
     FROM civicrm_address a
     LEFT JOIN civicrm_value_district_information_7 di ON (di.entity_id = a.id)
     JOIN civicrm_contact c ON c.id = a.contact_id
     JOIN civicrm_note n ON n.entity_id = c.id
     WHERE a.id >= $start_id
       AND n.subject LIKE CONCAT('REDIST2012%[id=', a.id , ']%')
       AND n.note LIKE '%MATCH_TYPE: STREET%'
     ORDER BY a.id ASC
     $limit_clause";

  // Run query to obtain a batch of addresses
  bbscript_log(LL::DEBUG, "Retrieving addresses starting at id $start_id with limit $max_res");
  return bb_mysql_query($q, $db, true);
} // retrieve_addresses()

function distassign(&$fmt_batch, $url, &$cnts)
{
  bbscript_log(LL::TRACE, "==> distassign()");

  // Attach the json data
  bbscript_log(LL::TRACE, "About to encode address batch in JSON");
  $json_batch = json_encode($fmt_batch);

  // Initialize the cURL request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_batch);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-length: ".strlen($json_batch)));
  bbscript_log(LL::TRACE, "About to send API request to SAGE using cURL [url=$url]");
  $response = curl_exec($ch);

  // Record the timings for the request and close
  $curl_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);

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
    else if (count($results) == 0) {
      bbscript_log(LL::ERROR, "Empty response from SAGE. SAGE server is likely offline.");
      $results = null;
    }
    else if (isset($results['message'])) {
      bbscript_log(LL::ERROR, "SAGE server encountered a problem: ".$results['message']);
      $results = null;
    }
  }

  bbscript_log(LL::TRACE, "<== distassign()");
  return $results;
} // distassign()

function update_election_district($db, $address_id, $correct_ed)
{  
  global $BB_UPDATE_FLAGS;
  if ($BB_UPDATE_FLAGS >= UPDATE_DISTRICTS){
    
    $q = "UPDATE civicrm_value_district_information_7 di
        SET di.election_district_49 = $correct_ed
        WHERE di.entity_id = $address_id";

    bbscript_log(LL::TRACE, "Setting Address $address_id to election district $correct_ed");
    bb_mysql_query($q, $db, true);  
  }  
} // update_district_info()

function update_note($db, $note_id, $new_note, $new_subject){
   
  global $BB_UPDATE_FLAGS;
  if ($BB_UPDATE_FLAGS >= UPDATE_NOTES){
   
    $q ="UPDATE civicrm_note n
        SET n.note = '$new_note', n.subject = '$new_subject', n.modified_date = '" . date("Y-m-d") . "' 
        WHERE n.id = $note_id";

    bbscript_log(LL::TRACE, "Updating note $note_id");
    bb_mysql_query($q, $db, true);
  }  
}

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
      && $array[$key] !== 0 && $array[$key] !== '0'
      && $array[$key] !== '00' && $array[$key] !== '000') {
    return $array[$key];
  }
  else {
    return $default;
  }
} // get()

