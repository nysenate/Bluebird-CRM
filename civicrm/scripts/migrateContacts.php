<?php

// Project: BluebirdCRM
// Authors: Stefan Crain, Graylin Kim, Ken Zalewski
// Organization: New York State Senate
// Date: 2012-10-26
// Revised: 2012-11-21

// ./migrateContacts.php -S skelos --dest 45 --file --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DRY_COUNT', 25);
define('DEFAULT_LOG_LEVEL', 'TRACE');
define('LOC_TYPE_BOE', 6);

// Parse the options
require_once 'script_utils.php';
$shortopts = "d:fn";
$longopts = array("dest=", "file", "dryrun");
$optlist = civicrm_script_init($shortopts, $longopts);

if ($optlist === null) {
    $stdusage = civicrm_script_usage();
    $usage = '[--dest ID|DISTNAME] [--file] [--dryrun]';
    error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
    exit(1);
}

//get instance settings for source and destination
$bbcfg_source = get_bluebird_instance_config($optlist['site']);
//bbscript_log("trace", "bbcfg_source", $bbcfg_source);

$source = array(
  'name' => $optlist['site'],
  'num' => $bbcfg_source['district'],
  'db' => $bbcfg_source['db.civicrm.prefix'].$bbcfg_source['db.basename'],
);

//destination may be passed as the instance name OR district ID
if ( is_numeric($optlist['dest']) ) {
  $dest['num'] = $optlist['dest'];

  //retrieve the instance config using the district ID
  $bbFullConfig = get_bluebird_config();
  //bbscript_log("trace", "bbFullConfig", $bbFullConfig);
  foreach ( $bbFullConfig as $group => $details ) {
    if ( strpos($group, 'instance:') !== false ) {
      if ( $details['district'] == $optlist['dest'] ) {
        $dest['name'] = substr($group, 9);
        $bbcfg_dest = get_bluebird_instance_config($dest['name']);
        $dest['db'] = $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'];
        break;
      }
    }
  }
}
else {
  $bbcfg_dest = get_bluebird_instance_config($optlist['dest']);
  $dest = array(
    'name' => $optlist['dest'],
    'num' => $bbcfg_dest['district'],
    'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
  );
}
//bbscript_log("trace", "$source", $source);
//bbscript_log("trace", "$dest", $dest);

//if either dest or source unset, exit
if ( empty($dest['db']) || empty($source['db']) ) {
  bbscript_log("fatal", "Unable to retrieve configuration for either source or destination instance.");
  exit();
}

//retrieve/set other options
$optFile = $optlist['file'];
$optDry = $optlist['dryrun'];

// Initialize CiviCRM
require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();
$session = CRM_Core_Session::singleton();

//get contacts to migrate and construct in migration table
$migrateTbl = buildContactTable($source, $dest);

//if no contacts found we can exit immediately
if ( !$migrateTbl ) {
  bbscript_log("fatal", "No contacts can be migrated to district #{$dest['num']} ({$dest['name']}).");
  exit();
}

//set filename and create file
$fileDir = '/data/importData/migrate_'.$bbcfg_source['install_class'];
if ( !file_exists($fileDir) ) {
  mkdir( $fileDir, 0775, TRUE );
}
$today = date('Ymd_Hi');
$fileName = $migrateTbl.'_'.$today.'.log';
$filePath = $fileDir.'/'.$fileName;
$fileResource = fopen($filePath, 'w');

//get contacts and write sql to file
exportContacts($migrateTbl, $fileResource, $dest['db'], $optDry);

//cycle through related records and construct sql
$recordTypes = array(
  'email',
  'phone',
  'website',
);

exit();

// Initialize script parameters from options and defaults
$chunk_size = $optlist['chunk'] ? $optlist['chunk'] : DEFAULT_CHUNK_SIZE;
$log_level = $optlist['log'] ? $optlist['log'] : DEFAULT_LOG_LEVEL;
$BB_LOG_LEVEL = $LOG_LEVELS[strtoupper($log_level)][0];
$dry_run = $optlist['dryrun'];
$max_id = $optlist['max'];

$max_id_clause = is_numeric($max_id) ? "LIMIT $max_id" : "";

bbscript_log("debug", "Starting with $prog with chunk size of $chunk_size");





bbscript_log("info", "Completed contact migration.");

/*
 * given source and destination details, create a table and populate with contacts to be migrated
 * also construct external ID to be used as FK during import
 * query criteria: exclude trashed contacts; only include those with a BOE address in destination district
 * if no contacts are found to migrate, return FALSE so we can exit immediately.
 */
function buildContactTable($source, $dest) {
  //create table to store contact IDs with constructed external_id
  $tbl = "migrate_{$source['num']}_{$dest['num']}";
  CRM_Core_DAO::executeQuery( "DROP TABLE IF EXISTS $tbl;", CRM_Core_DAO::$_nullArray );

  $sql = "
    CREATE TABLE $tbl
    (contact_id int not null primary key, external_id varchar(40) not null)
    ENGINE = myisam;
  ";
  CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

  $sql = "
    INSERT INTO $tbl
    SELECT a.contact_id, CONCAT('SD{$source['num']}_BB', a.contact_id, '_EXT', c.external_identifier) external_id
    FROM civicrm_address a
    JOIN civicrm_value_district_information_7 di
      ON a.id = di.entity_id
      AND di.ny_senate_district_47 = {$dest['num']}
    JOIN civicrm_contact c
      ON a.contact_id = c.id
      AND c.is_deleted = 0
    WHERE a.location_type_id = ".LOC_TYPE_BOE."
    GROUP BY a.contact_id
  ";
  //bbscript_log("trace", "buildContactTable sql insertion", $sql);
  CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

  $count = CRM_Core_DAO::singleValueQuery("SELECT count(*) FROM $tbl");
  //bbscript_log("trace", "buildContactTable $count", $count);

  if ( $count ) {
    return $tbl;
  }
  else {
    return FALSE;
  }
}//buildContactTable

function exportContacts($migrateTbl, $fileResource, $destDB, $optDry = FALSE) {
  //get field list
  $c = new CRM_Contact_DAO_Contact();
  $fields = $c->fields();
  //bbscript_log("trace", "exportContacts fields", $fields);

  foreach ( $fields as $field ) {
    $fieldNames[] = $field['name'];
  }

  //unset these from select statement
  unset($fieldNames[array_search('id', $fieldNames)]);
  unset($fieldNames[array_search('external_identifier', $fieldNames)]);
  unset($fieldNames[array_search('primary_contact_id', $fieldNames)]);
  unset($fieldNames[array_search('employer_id', $fieldNames)]);

  $select = 'external_id external_identifier, '.implode(', ',$fieldNames);
  //bbscript_log("trace", "exportContacts select", $select);

  //start writing to file
  $selectInsert = str_replace('external_id ', '', $select);
  fwrite($fileResource, "
    -- contact insert

    INSERT INTO {$destDB}.civicrm_contact
    ( {$selectInsert} )
    VALUES
  ");

  $sql = "
    SELECT $select
    FROM $migrateTbl mt
    JOIN civicrm_contact
      ON mt.contact_id = civicrm_contact.id
  ";
  $contacts = CRM_Core_DAO::executeQuery($sql);
  //bbscript_log("trace", 'exportContacts sql', $sql);

  $contactsAttr = get_object_vars($contacts);
  //bbscript_log("trace", 'exportContacts contactsAttr', $contactsAttr);

  //cycle through contacts and write to file
  $valSql = array();
  while ( $contacts->fetch() ) {
    //bbscript_log("trace", 'exportContacts contacts', $contacts);
    $cData = array();
    foreach ( $contacts as $f => $v ) {
      if ( !array_key_exists($f, $contactsAttr) ) {
        $cData[$f] = addslashes($v);
      }
    }
    $valSql[] = "('".implode("', '", $cData)."')";
  }
  //bbscript_log("trace", 'exportContacts valSql', $valSql);

  //now gather insert statements and write to file
  $valSqlString = implode(",\n", $valSql).";\n";

  if ( $optDry ) {
    bbscript_log("info", 'contact insert sql', $valSqlString);
  }
  else {
    fwrite($fileResource, $valSqlString);
  }
}

function getValue($string) {
  if ($string == FALSE) {
    return "null";
  }
  else {
    return $string;
  }
}
