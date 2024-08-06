<?php
ini_set('display_errors', '1');
error_reporting(-1 && ~E_DEPRECATED);

require_once dirname(__FILE__).'/../script_utils.php';
require_once dirname(__FILE__).'/../bluebird_config.php';

// mark the start of the script
bbscript_log(LL::NOTICE, 'Beginning import process');

// set the default return code (success)
$return_code = 0;

// get config
$iconfig = get_integration_config_params();
$log_level = $iconfig['log_level'];
bbscript_log(LL::NOTICE, "Setting log level to $log_level");
set_bbscript_log_level($log_level);

bbscript_log(LL::DEBUG, "Using config:", $iconfig);

// Enforce character set and collation on connections.
// Use sql_mode="" to disable STRICT_TRANS_TABLES, which allows auto-truncation
// to occur for values from the website that are too long for the local
// db fields.  Otherwise, an error is generated and that record is skipped.
$pdo_options = [
//  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""; SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
];

// try to connect to local db
$localdsn = "mysql:host={$iconfig['local_db_host']};" .
                  "port={$iconfig['local_db_port']};" .
                  "dbname={$iconfig['local_db_name']}";
bbscript_log(LL::INFO, "Attempting to connect to local store with: $localdsn");
try {
  $localdb = new PDO($localdsn, $iconfig['local_db_user'], $iconfig['local_db_pass'], $pdo_options);
}
catch (PDOException $e) {
  bbscript_log(LL::FATAL, 'Could not connect to local store: '.$e->getMessage());
  exit(1);
}
bbscript_log(LL::NOTICE, "Connected to local store at $localdsn");

// pull local db settings
try {
  $query = "SELECT option_value, option_name FROM settings";
  $res = $localdb->query($query);
}
catch (PDOException $e) {
  bbscript_log(LL::FATAL, 'Could not query local database settings: '.$e->getMessage());
  exit(1);
}

$local_cfg = new stdClass();
while ($row = $res->fetchObject()) {
  $local_cfg->{$row->option_name} = $row->option_value;
}
$res->closeCursor();

if (!isset($local_cfg->max_eventid)) {
  $local_cfg->max_eventid = 0;
}
bbscript_log(LL::INFO, 'Retrieved max_eventid counter from local store');
bbscript_log(LL::DEBUG, 'Using local config:', $local_cfg);

// set the watch for current event message id
$current_max = $local_cfg->max_eventid;

// try to connect to remote db
$remotedsn = "mysql:host={$iconfig['source_db_host']};" .
                   "port={$iconfig['source_db_port']};" .
                   "dbname={$iconfig['source_db_name']}";
bbscript_log(LL::INFO, "Attempting to connect to remote db with: $remotedsn");
try {
  $remotedb = new PDO($remotedsn, $iconfig['source_db_user'], $iconfig['source_db_pass'], $pdo_options);
}
catch (PDOException $e) {
  bbscript_log(LL::FATAL, 'Could not connect to remote db: '.$e->getMessage());
  exit(1);
}
bbscript_log(LL::NOTICE, "Connected to remote db at $remotedsn");
bbscript_log(LL::INFO, "Searching for event messages after #$current_max");

// query for new event messages
$query = <<<REMOTEQUERY
SELECT a.id, a.user_id, a.user_is_verified,
       a.target_shortname, a.target_district,
       a.user_shortname, a.user_district,
       a.event_type, a.event_action, a.event_data,
       FROM_UNIXTIME(a.created_at) as created_at,
       IFNULL(u.mail,'') as email_address,
       IFNULL(fn.field_first_name_value,'') as first_name,
       IFNULL(ln.field_last_name_value,'') as last_name,
       IFNULL(ad.field_address_address_line1,'') as address1,
       IFNULL(ad.field_address_address_line2,'') as address2,
       IFNULL(ad.field_address_locality,'') as city,
       IFNULL(ad.field_address_administrative_area,'') as state,
       IFNULL(ad.field_address_postal_code,'') as zip,
       dob.field_dateofbirth_value as dob,
       IFNULL(gu.field_gender_user_value,'') as gender,
       IFNULL(taxdata.name,'') as top_issue
FROM accumulator a
LEFT JOIN users_field_data u on a.user_id=u.uid
LEFT JOIN user__field_first_name fn ON a.user_id=fn.entity_id
LEFT JOIN user__field_last_name ln ON a.user_id=ln.entity_id
LEFT JOIN user__field_address ad ON a.user_id=ad.entity_id
LEFT JOIN user__field_dateofbirth dob ON a.user_id=dob.entity_id
LEFT JOIN user__field_gender_user gu ON a.user_id=gu.entity_id
LEFT JOIN (user__field_top_issue ti
           INNER JOIN taxonomy_term_field_data taxdata ON ti.field_top_issue_target_id=taxdata.tid)
     ON a.user_id=ti.entity_id
  WHERE a.id > :currentmax ;
REMOTEQUERY;

bbscript_log(LL::DEBUG, "Executing query:", $query);
try {
  $remotestmt = $remotedb->prepare($query);
  $remotestmt->execute([':currentmax' => $current_max]);
  $found_count = $remotestmt->rowCount();
}
catch (PDOException $e) {
  bbscript_log(LL::FATAL, 'Remote query for new event messages failed: '.$e->getMessage());
  exit(1);
}
bbscript_log(LL::NOTICE, "Found $found_count event messages to import");

// set up the INSERT query
$fields = ['id', 'user_id', 'user_is_verified', 'target_shortname',
           'target_district', 'user_shortname', 'user_district',
           'event_type', 'event_action', 'event_data', 'created_at',
           'email_address', 'first_name', 'last_name',
           'address1', 'address2', 'city', 'state', 'zip',
           'dob', 'gender', 'top_issue'];
$query = "INSERT INTO accumulator (`".implode('`,`',$fields)."`) VALUES (:".implode(', :',$fields).")";
bbscript_log(LL::DEBUG, "Using query:", $query);

// init the success tracker
$imported_ids = [];
$failed_ids = [];

// begin INSERT for each event record
$localstmt = $localdb->prepare($query);
while ($row = $remotestmt->fetch(PDO::FETCH_ASSOC)) {
  $thisid = (int)$row['id'];
  $bindparam = [];
  foreach ($row as $k => $v) {
    if ($k == 'event_data') {
      // The event_data field is a JSON object with three subojects:
      //   user_info, event_info, and request_info
      // We have no need for the request_info, which is debugging info,
      // so eliminate it from the JSON.
      // Eventually, the web team will move the request_info data into
      // a separate field and this logic can be eliminated.
      $evdata = json_decode($v);
      unset($evdata->request_info);
      $v = json_encode($evdata);
    }
    $bindparam[":{$k}"] = $v;
  }
  bbscript_log(LL::INFO, "Importing event message: $thisid");
  bbscript_log(LL::DEBUG, "Event message $thisid bound values:", $bindparam);
  try {
    $localstmt->execute($bindparam);
    $imported_ids[] = $thisid;
    if ($thisid > $current_max) {
      $current_max = $thisid;
    }
  }
  catch (PDOException $e) {
    bbscript_log(LL::ERROR, "Import of message $thisid failed: ".$e->getMessage());
    $failed_ids[] = $thisid;
    $return_code = 1;
  }
  $localstmt->closeCursor();
}

$imported_count = count($imported_ids);
$failed_count = count($failed_ids);
bbscript_log(LL::NOTICE, "Imported $imported_count event messages from website; closing remote resources");
$remotestmt->closeCursor();
$remotedb = null;

// record activity
try {
  $query = "UPDATE settings SET option_value=NOW() WHERE option_name='last_update'";
  bbscript_log(LL::DEBUG, "Executing query:", $query);
  $localdb->query($query);
  if ($current_max != $local_cfg->max_eventid) {
    $query = "UPDATE settings SET option_value='$current_max' WHERE option_name='max_eventid'";
    $localdb->query($query);
  }
}
catch (PDOException $e) {
  bbscript_log(LL::FATAL, 'Unable to update accumulator ID tracker in [settings] table: '.$e->getMessage());
  $return_code = 1;
}

// cleaning up
$localdb = null;

if ($failed_count > 0) {
  $failed_id_str = implode(',', $failed_ids);
  bbscript_log(LL::WARN, "Event IDs that failed to import: ". $failed_id_str);
}

// mark end of process
$logmsg = $found_count
          ? "Imported $imported_count out of $found_count new event messages"
          : "No new event messages to import";
bbscript_log(LL::NOTICE, "Process complete: $logmsg");

exit($return_code);



function get_integration_config_params()
{
  $default_vals = [
    'source_db_host' => 'localhost',
    'source_db_port' => 3306,
    'source_db_user' => 'user',
    'source_db_pass' => '',
    'source_db_name' => 'accumulator',
    'local_db_host' => 'localhost',
    'local_db_port' => 3306,
    'local_db_user' => 'user',
    'local_db_pass' => '',
    'local_db_name' => 'web_integration',
    'log_level'     => LL::NOTICE
  ];

  // Command line options are the same as the default value indices, with
  // '-' instead of '_', and ending with '='.
  $longopts = str_replace('_', '-', array_keys($default_vals));
  $longopts = array_map(function($val) { return $val.'='; }, $longopts);
  $longopts[] = 'source-db-config-file=';
  $shortopts = "h:t:u:p:n:H:T:U:P:N:l:f:";

  $optlist = process_cli_args($shortopts, $longopts);
  if ($optlist == null) {
    $usage = implode('val --', $longopts);
    error_log("Usage: ".basename(__FILE__)."  --${usage}val\n");
    exit(1);
  }

  // Bluebird config MUST be read before the external config.
  $bbcfg = get_bluebird_config();
  $bbintcfg = $bbcfg['globals'];

  // If an external config file is being used, read it in and update values.
  // This applies to only the source database parameters ("website.source.db").
  $extcfgfile = null;
  if (isset($optlist['source-db-config-file'])) {
    $extcfgfile = $optlist['source-db-config-file'];
  }
  else if (isset($bbintcfg['source.db.config.file'])) {
    $extcfgfile = $bbintcfg['source.db.config.file'];
  }
  if ($extcfgfile) {
    $extcfg = parse_ini_file($extcfgfile, false, INI_SCANNER_TYPED);
    if ($extcfg === false) {
      bbscript_log(LL::WARN, "$extcfgfile: Unable to parse external config file");
    }
    else {
      // Replace parameters in Bluebird config with the external config.
      foreach ($extcfg as $k => $v) {
        $bbcfgidx = "website.source.db.$k";
        $bbintcfg[$bbcfgidx] = $v;
      }
    }
  }

  $cfgopts = [];

  foreach ($default_vals as $k => $v) {
    $cliopt = str_replace('_', '-', $k);
    $bbcfgopt = 'website.'.str_replace('_', '.', $k);
    if (isset($optlist[$cliopt])) {
      $cfgopts[$k] = $optlist[$cliopt];
    }
    else if (isset($bbintcfg[$bbcfgopt])) {
      $cfgopts[$k] = $bbintcfg[$bbcfgopt];
    }
    else {
      $cfgopts[$k] = $v;
    }
  }

  return $cfgopts;
} // get_integration_config_params()
