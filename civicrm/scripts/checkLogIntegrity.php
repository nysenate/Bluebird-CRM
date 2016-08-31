<?php
//no limit
set_time_limit(0);

// set script name for reference
$prog = basename(__FILE__);

// load the helper scripts
require_once 'bluebird_config.php';
require_once 'script_utils.php';

// load bluebird config file
$bbconfig = get_bluebird_config();

// generate usage statements and check CLI params
$stdusage = civicrm_script_usage();
$usage = "[--log-level LEVEL] [--table|-t TABLE_NAMES]";
$shortopts = "l:t:";
$longopts = array("log-level=", "table=");

$optlist = civicrm_script_init($shortopts, $longopts);
if ($optlist === null) {
  error_log("Usage: $prog  $stdusage  $usage\n");
  exit(1);
}

if (!empty($optlist['log-level'])) {
  set_bbscript_log_level($optlist['log-level']);
}

// Load just enough of the Civi framework to be useful
require_once 'CRM/Core/Config.php';
$config =& CRM_Core_Config::singleton();
$session =& CRM_Core_Session::singleton();
require_once 'CRM/Core/DAO.php';

// set up some easy reference variables, and our table stores
$db_name = $bbconfig['globals']['db.civicrm.prefix'].$optlist['site'];
$logdb_name = $bbconfig['globals']['db.log.prefix'].$optlist['site'];
$civi_tables = array();
$log_tables = array();
$tables = array();

// For the civi tables, check for names passed on command line
// if no tables are passed, load ALL tables
if (!$optlist['table']) {
  $q = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE " .
    "TABLE_SCHEMA = '{$db_name}' AND TABLE_TYPE = 'BASE TABLE' " .
    "AND TABLE_NAME LIKE 'civicrm_%';";
  $result = CRM_Core_DAO::executeQuery($q);
  while ($result->fetch()) {
    $civi_tables[] = $result->TABLE_NAME;
  }
}
else {
  $civi_tables = explode(',', $optlist['table']);
}
bbscript_log(LL::DEBUG, "Found Civi Tables:\n".var_export($civi_tables, true));

// Load all the logging tables
$q = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE " .
  "TABLE_SCHEMA = '{$logdb_name}' AND TABLE_TYPE = 'BASE TABLE' " .
  "AND TABLE_NAME LIKE 'log_%';";
$result = CRM_Core_DAO::executeQuery($q);
while ($result->fetch()) {
  $log_tables[] = str_replace('log_', '', $result->TABLE_NAME);
}
bbscript_log(LL::DEBUG, "Found Log Tables:\n".var_export($log_tables, true));

// Tables to check are any names found in both civi and log DBs
$tables = array_intersect($civi_tables, $log_tables);

// If no tables are found, leave
if (!count($tables)) {
  bbscript_log(LL::FATAL, "No tables found for processing (CLI param='{$optlist['table']}')");
  exit(1);
}

bbscript_log(LL::INFO, "Begin processing on " . count($tables) . " tables");
bbscript_log(LL::DEBUG, "Table list: " . implode(',', $tables));

// for each table, start the processor
foreach ($tables as $table) {
  process_table($table, $db_name, $logdb_name);
}

bbscript_log(LL::INFO, "Processing is completed");
exit(0);



function process_table($tablename, $cividbname, $logdbname)
{
  if (empty($tablename) || empty($cividbname) || empty($logdbname)) {
    bbscript_log(LL::ERROR, "Must provide table name and Civi/Log db names");
    return false;
  }

  $fields = get_common_fields($tablename, $cividbname, $logdbname);
  if (count($fields) < 1) {
    bbscript_log(LL::ERROR, "No common fields were found for $tablename");
    return false;
  }

  bbscript_log(LL::INFO, "Processing $tablename");

  $query = create_match_query($tablename, $cividbname, $logdbname, $fields);
  bbscript_log(LL::DEBUG, "Running query $query");
  $records = CRM_Core_DAO::executeUnbufferedQuery($query);

  // Prep a new db connection
  $log_db = DB::connect(CIVICRM_LOGGING_DSN);

  // For each PK value in the civi table, pull the civi record, the log record,
  // and compare the two
  while ($records->fetch()) {
    bbscript_log(LL::DEBUG, "Found no-match record:\n".var_export($records, true));
    $bad_id = $records->id;
    $match_query = "SELECT * FROM $logdbname.log_$tablename WHERE id=$bad_id ORDER BY log_date DESC LIMIT 1";
    $match_result = $log_db->query($match_query);
    if (!($match_row = $match_result->fetchRow(DB_FETCHMODE_ASSOC))) {
      bbscript_log(LL::ERROR, "Table:$tablename, ID:$bad_id: No log records found");
    }
    else {
      $bad_fields = array();
      foreach ($fields as $key => $val) {
        if ($records->{$key} != $match_row[$key]) {
          $bad_fields[] = $key;
        }
      }
      bbscript_log(LL::ERROR, "Table:$tablename, ID:$bad_id: Fields differ: " . implode(',', $bad_fields));
    }
  }
} // process_table()



function get_common_fields($tablename, $cividbname, $logdbname)
{
  $ignore_fields = array('created_date', 'modified_date');

  // Load the fields from the Civi table
  $civifields = retrieve_schema($tablename, $cividbname);
  bbscript_log(LL::DEBUG, "Discovered schema for CiviDB $tablename");
  // Load the fields from the Log table
  $logfields = retrieve_schema("log_$tablename", $logdbname);
  bbscript_log(LL::DEBUG, "Discovered schema for LogDB log_$tablename");

  // The only fields we check are those existing in both Civi and Log tables
  $common_fields = array_intersect(array_keys($civifields), array_keys($logfields));

  // Remove fields that are not subject to comparison.
  $filtered_fields = array_diff($common_fields, $ignore_fields);

  $fields = array();
  foreach ($filtered_fields as $fldname) {
    $fields[$fldname] = $civifields[$fldname];
  }
  bbscript_log(LL::DEBUG, "Found common fields for table $tablename:\n".implode(',', array_keys($fields)));
  return $fields;
} // get_common_fields()



function retrieve_schema($tablename, $db = '')
{
  // initialize return
  $ret = array();

  // Looking for the return of "DESCRIBE TABLE"
  $query = "DESCRIBE " . ($db ? "{$db}." : '') . $tablename;
  $result = CRM_Core_DAO::executeQuery($query);
  while ($result->fetch()) {
    // for each record, parse the Type to get some attributes, such as the data
    // type, precision, etc.  If no data type is found, don't include it.
    $m = array();
    preg_match('/^([a-z]+)(\\(([^)]+)\\))?( ?(.*))?/i', $result->Type, $m);
    if (isset($m[1])) {
      $ret[$result->Field] = array(
        'type' => $m[1],
        'precision' => isset($m[3]) ? $m[3] : null,
        'modifier' => isset($m[5]) ? $m[5] : null,
      );
    }
  }

  return $ret;
} // retrieve_schema()



function create_match_query($tabname, $cividb, $logdb, $flds)
{
  $civitable = "$cividb.$tabname";
  $logtable = "$logdb.log_$tabname";
  $fields = array();
  $join = array();

  foreach ($flds as $key => $val) {
    $fields[] = "main.$key";
    $fields[] = "hist.$key as log_$key";
    $join[] = "((main.$key = hist.$key) or (main.$key IS NULL AND hist.$key IS NULL))";
  }

  $query = "SELECT ".implode(',', $fields)." FROM $civitable main " .
    "JOIN ( SELECT MAX(log_date) as max_date, id FROM $logtable " .
    "GROUP BY id ) join_max ON (join_max.id = main.id) LEFT JOIN $logtable hist " .
    "ON (join_max.max_date = hist.log_date) AND ".implode(' AND ', $join)." WHERE hist.id IS NULL";
  return $query;
} // create_match_query()
