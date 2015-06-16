<?php
ini_set('display_errors',1);
ini_set('error_reporting',-1 && ~E_DEPRECATED);

global $cfg;

// helper library
require_once 'import_integration_messages_library.inc';

// mark the start of the script
IL::log('Beginning import process',LOG_LEVEL_NOTICE);

// get config
$cfg = IntegrationConfig::getInstance();
IL::log('Read config complete');
IL::log("Using config:\n".print_r($cfg->config,1),LOG_LEVEL_DEBUG);

// enforce character set and collation on connections
$pdo_options = array(
                  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8 COLLATE utf8_unicode_ci',
                  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
               );

// try to connect to local db
$localdsn = "mysql:host={$cfg->config['local_host']};" .
                  "port={$cfg->config['local_port']};" .
                  "dbname={$cfg->config['local_db']}";
IL::log("Attempting to connect to local store with: $localdsn",LOG_LEVEL_DEBUG);
try {
  $localdb = new PDO($localdsn, $cfg->config['local_user'], $cfg->config['local_pass'], $pdo_options);
} catch (PDOException $e) {
  IL::log('Could not connect to local store: ' . $e->getMessage(), LOG_LEVEL_CRITICAL);
  die();
}
IL::log('Connected to local store');

// pull local db settings
try {
  $query = "SELECT option_value, option_name FROM settings";
  $res = $localdb->query($query);
} catch (PDOException $e) {
  IL::log('Could not query local database settings: ' . $e->getMessage(), LOG_LEVEL_CRITICAL);
  die();
}
$local_cfg = new stdClass();
while ($r=$res->fetchObject()) {
  $local_cfg->{$r->option_name} = $r->option_value;
}
$res->closeCursor();
if (!isset($local_cfg->max_pulled)) { $local_cfg->max_pulled = 0; }
IL::log('Local config retrieved');
IL::log('Using local config: ' . print_r($local_cfg,1), LOG_LEVEL_DEBUG);

// set the watch for current message id
$current_max = $local_cfg->max_pulled;

// try to connect to remote db
$remotedsn = "mysql:host={$cfg->config['source_host']};" .
                   "port={$cfg->config['source_port']};" .
                   "dbname={$cfg->config['source_db']}";
IL::log("Attempting to connect to remote db with: $remotedsn", LOG_LEVEL_DEBUG);
try {
  $remotedb = new PDO($remotedsn, $cfg->config['source_user'], $cfg->config['source_pass'], $pdo_options);
} catch (PDOException $e) {
  IL::log('Could not connect to remote db: ' . $e->getMessage(), LOG_LEVEL_CRITICAL);
  die();
}
IL::log('Connected to remote db');
IL::log("Searching for messages >$current_max",LOG_LEVEL_NOTICE);

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
IL::log("Executing query:\n$query",LOG_LEVEL_INFO);
$res = $remotedb->prepare($query);
$res->execute(array(':currentmax'=>$current_max));
$found_count = $res->rowCount();
IL::log("Found $found_count messages to import",LOG_LEVEL_NOTICE);

// set up the INSERT query
$fields = array('id', 'user_id', 'user_is_verified', 'target_shortname',
                'target_district', 'user_shortname', 'user_district',
                'msg_type', 'msg_action', 'msg_info', 'created_at',
                'email_address', 'first_name', 'last_name', 'contact_me',
                'address1', 'address2', 'city', 'state', 'zip', 'dob',
                'gender', 'top_issue');
$query = "INSERT INTO accumulator (`".implode('`,`',$fields)."`) VALUES (:".implode(', :',$fields).")";
IL::log("Using query:\n".print_r($query,1),LOG_LEVEL_DEBUG);

// init the success tracker
$success = array();

// begin INSERT for each message
$instmt = $localdb->prepare($query);
while ($onemsg = $res->fetch(PDO::FETCH_ASSOC)) {
  $thisid = (int)$onemsg['id'];
  $bindparam = array();
  foreach ($onemsg as $k=>$v) {
    if ($k=='contact_me') {
      $v = (int)unserialize($v)['contact'];
    }
    $bindparam[":{$k}"]=$v;
  }
  IL::log("Importing message {$thisid}");
  IL::log("Message {$thisid} bound values:\n".print_r($bindparam,1),LOG_LEVEL_DEBUG);
  try {
    $instmt->execute($bindparam);
    $success[]=$thisid;
    if ($thisid > $current_max) { $current_max = $thisid; }
  } catch (PDOException $e) {
    IL::log("Import of message {$onemsg['id']} failed: ".$e->getMessage(),LOG_LEVEL_ERROR);
  }
  $instmt->closeCursor();
}
$success_count = count($success);
IL::log("Message import complete (count:$success_count), closing remote resources",LOG_LEVEL_INFO);
$res->closeCursor();
$remotedb = NULL;

// record activity
try {
  $query = "UPDATE settings SET option_value=NOW() WHERE option_name='last_pulled'";
  $localdb->query($query);
  if ($current_max != $local_cfg->max_pulled) {
    $query = "UPDATE settings SET option_value='$current_max' WHERE option_name='max_pulled'";
    $localdb->query($query);
  }
} catch (PDOException $e) {
  IL::log('COULD NOT UPDATE STATE INFORMATION! ' . $e->getMessage(),LOG_LEVEL_CRITICAL);
}

// cleaning up
$localdb = NULL;

// mark end of process
$logmsg = $found_count
          ? "Imported $success_count of $found_count new messages"
          : "No new messages to import";
IL::log("Process complete: $logmsg",LOG_LEVEL_NOTICE);