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

// enforce character set and collation on connections
$pdo_options = array(
  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8 COLLATE utf8_unicode_ci',
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
);

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
while ($r = $res->fetchObject()) {
  $local_cfg->{$r->option_name} = $r->option_value;
}
$res->closeCursor();

if (!isset($local_cfg->max_pulled)) {
  $local_cfg->max_pulled = 0;
}
bbscript_log(LL::INFO, 'Retrieved max_pulled counter from local store');
bbscript_log(LL::DEBUG, 'Using local config:', $local_cfg);

// set the watch for current message id
$current_max = $local_cfg->max_pulled;

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
bbscript_log(LL::INFO, "Searching for messages after #$current_max");

// query for new messages
$query = <<<REMOTEQUERY
SELECT
  a.id, a.user_id, a.user_is_verified, a.target_shortname, a.target_district,
  a.user_shortname, a.user_district, a.msg_type, a.msg_action, a.msg_info,
  a.created_at,
  IFNULL(users.mail,'') as email_address,                 /* email address */
  IFNULL(fdf_fn.field_first_name_value,'') as first_name, /* first name */
  IFNULL(fdf_ln.field_last_name_value,'') as last_name,   /* last name */
  IFNULL(users.data,'') as contact_me,                    /* Contact Me from serialized array blob */
  IFNULL(l.street,'') as address1,                        /* street number */
  IFNULL(l.additional,'') as address2,                    /* address line 2 */
  IFNULL(l.city,'') as city,                              /* city */
  IFNULL(l.province,'') as `state`,                       /* state */
  IFNULL(l.postal_code,'') as zip,                        /* zip code */
  IFNULL(fdf_dob.field_dateofbirth_value,'') as dob,      /* date of birth */
  IFNULL(fdf_gu.field_gender_user_value,'') as gender,    /* gender (m/f) */
  IFNULL(taxdata.name,'') as top_issue                    /* Top Issue */
FROM accumulator a
       LEFT JOIN users on a.user_id=users.uid
       LEFT JOIN field_data_field_first_name fdf_fn ON a.user_id=fdf_fn.entity_id
       LEFT JOIN field_data_field_last_name fdf_ln ON a.user_id=fdf_ln.entity_id
       LEFT JOIN (field_data_field_address fdf_ad
       INNER JOIN location l ON fdf_ad.field_address_lid = l.lid ) ON a.user_id=fdf_ad.entity_id
       LEFT JOIN field_data_field_dateofbirth fdf_dob ON a.user_id=fdf_dob.entity_id
       LEFT JOIN field_data_field_gender_user fdf_gu ON a.user_id=fdf_gu.entity_id
       LEFT JOIN (field_data_field_top_issue fdf_ti
       INNER JOIN taxonomy_term_data taxdata ON fdf_ti.field_top_issue_target_id=taxdata.tid)
       ON a.user_id=fdf_ti.entity_id
WHERE a.id > :currentmax /*AND a.user_is_verified > 0*/
REMOTEQUERY;

bbscript_log(LL::DEBUG, "Executing query:", $query);
try {
  $res = $remotedb->prepare($query);
  $res->execute(array(':currentmax'=>$current_max));
  $found_count = $res->rowCount();
}
catch (PDOException $e) {
  bbscript_log(LL::FATAL, 'Remote query for new messages failed: '.$e->getMessage());
  exit(1);
}
bbscript_log(LL::NOTICE, "Found $found_count messages to import");

// set up the INSERT query
$fields = array('id', 'user_id', 'user_is_verified', 'target_shortname',
                'target_district', 'user_shortname', 'user_district',
                'msg_type', 'msg_action', 'msg_info', 'created_at',
                'email_address', 'first_name', 'last_name', 'contact_me',
                'address1', 'address2', 'city', 'state', 'zip', 'dob',
                'gender', 'top_issue');
$query = "INSERT INTO accumulator (`".implode('`,`',$fields)."`) VALUES (:".implode(', :',$fields).")";
bbscript_log(LL::DEBUG, "Using query:", $query);

// init the success tracker
$success = array();

// begin INSERT for each message
$instmt = $localdb->prepare($query);
while ($onemsg = $res->fetch(PDO::FETCH_ASSOC)) {
  $thisid = (int)$onemsg['id'];
  $bindparam = array();
  foreach ($onemsg as $k => $v) {
    if ($k == 'contact_me') {
      $v = (int)unserialize($v)['contact'];
    }
    $bindparam[":{$k}"] = $v;
  }
  bbscript_log(LL::INFO, "Importing message: $thisid");
  bbscript_log(LL::DEBUG, "Message $thisid bound values:", $bindparam);
  try {
    $instmt->execute($bindparam);
    $success[] = $thisid;
    if ($thisid > $current_max) {
      $current_max = $thisid;
    }
  }
  catch (PDOException $e) {
    bbscript_log(LL::ERROR, "Import of message {$onemsg['id']} failed: ".$e->getMessage());
    $return_code = 1;
  }
  $instmt->closeCursor();
}

$success_count = count($success);
bbscript_log(LL::NOTICE, "Message import complete (count:$success_count), closing remote resources");
$res->closeCursor();
$remotedb = null;

// record activity
try {
  $query = "UPDATE settings SET option_value=NOW() WHERE option_name='last_pulled'";
  bbscript_log(LL::DEBUG, "Executing query:", $query);
  $localdb->query($query);
  if ($current_max != $local_cfg->max_pulled) {
    $query = "UPDATE settings SET option_value='$current_max' WHERE option_name='max_pulled'";
    $localdb->query($query);
  }
}
catch (PDOException $e) {
  bbscript_log(LL::FATAL, 'COULD NOT UPDATE STATE INFORMATION! '.$e->getMessage());
  $return_code = 1;
}

// cleaning up
$localdb = null;

// mark end of process
$logmsg = $found_count
          ? "Imported $success_count of $found_count new messages"
          : "No new messages to import";
bbscript_log(LL::NOTICE, "Process complete: $logmsg");

exit($return_code);



function get_integration_config_params()
{
  $default_vals = array(
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
  );

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

  $cfgopts = array();

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
