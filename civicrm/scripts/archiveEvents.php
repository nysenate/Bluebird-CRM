<?php
require_once 'script_utils.php';
require_once 'accumulatorEvents.inc.php';

// Bootstrap the script from the command line
$prog = basename(__FILE__);
$shortopts   = 'l:';
$longopts    = array('log-level=');
$stdusage = civicrm_script_usage();
//really should allow requirements
$scriptusage = '[--log-level|-l LEVEL]';

$optlist = civicrm_script_init($shortopts, $longopts);
if (!$optlist) {
  error_log("Usage: $prog  $stdusage $scriptusage");
  exit(1);
}

if (!empty($optlist['log-level'])) {
  set_bbscript_log_level($optlist['log-level']);
}

// Creating the CRM_Core_Config class bootstraps the rest
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config = CRM_Core_Config::singleton();
$bbconfig = get_bluebird_instance_config();
$instance = $optlist['site'];

// Establish a connection to the accumulator
$conn = get_accumulator_connection($bbconfig);

//greetings
bbscript_log(LL::INFO, "Checking Orphaned Events for $instance");

//gets all relevant pulls from the instance
$result = exec_query("
    SELECT *
    FROM incoming
    WHERE instance='$instance'
    ORDER BY mailing_id
    ", $conn, true);

// Eventually: join mailing id keys on summary for initial processed (dt_first)
// Can't do it because some old events get logged without mailing_id's in
// the summary table.
archive_orphaned_events($conn, $result, $optlist, $bbconfig);

?>
