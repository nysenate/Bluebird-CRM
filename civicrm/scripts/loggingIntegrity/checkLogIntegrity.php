<?php
//no limit
set_time_limit(0);

// set script name for reference
$prog = basename(__FILE__);

// load the helper scripts
require_once '../bluebird_config.php';
require_once '../script_utils.php';

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
$db_name = $bbconfig['globals']['db.civicrm.prefix'] . $optlist['site'];
$logdb_name = $bbconfig['globals']['db.log.prefix'] . $optlist['site'];
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
} else {
  $civi_tables = explode(',', $optlist['table']);
}
bbscript_log(LL::DEBUG, "Found Civi Tables:\n".var_export($civi_tables,1));

// Load all the logging tables
$q = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE " .
  "TABLE_SCHEMA = '{$logdb_name}' AND TABLE_TYPE = 'BASE TABLE' " .
  "AND TABLE_NAME LIKE 'log_%';";
$result = CRM_Core_DAO::executeQuery($q);
while ($result->fetch()) {
  $log_tables[] = str_replace('log_', '', $result->TABLE_NAME);
}
bbscript_log(LL::DEBUG, "Found Log Tables:\n".var_export($log_tables,1));

// Tables to check are any names found in both civi and log DBs
$tables = array_intersect($civi_tables, $log_tables);

// If no tables are found, leave
if (!count($tables)) {
  bbscript_log(LL::FATAL, "No tables found for processing (CLI param='{$optlist['table']}')");
  exit(1);
}

bbscript_log(LL::INFO, "Begin processing on " . count($tables) . " tables");
bbscript_log(LL::DEBUG, "Table list: " . implode(',', $tables));

// Load the processor class.
require_once 'TableProcessor.php';

// for each table, start the processor
foreach ($tables as $one_table) {
  $process = new TableProcessor($one_table, $db_name, $logdb_name);
  $process->process();
}

