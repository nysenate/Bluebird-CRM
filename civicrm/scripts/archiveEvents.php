<?php
require_once 'accumulatorEvents.inc.php';

// Bootstrap the script from the command line
$prog = basename(__FILE__);
$shortOpts   = 'i:t';
$longOpts    = array('instance=','test');
$stdusage = civicrm_script_usage();
//really should allow requirements
$scriptUsage = "[--instance|-i instance name]";
//[--test|-t show events without archiving] [--output|-o path to output file] [--force|-f no warnings]
if (! $optList = civicrm_script_init($shortOpts, $longOpts) ) {
  error_log("Usage: $prog  $stdusage $scriptUsage");
  exit(1);
}

// Creating the CRM_Core_Config class bootstraps the rest
require_once 'CRM/Core/Config.php';
require_once 'CRM/Core/DAO.php';
$config = CRM_Core_Config::singleton();
$bbconfig = get_bluebird_instance_config();

// Establish a connection to the accumulator
global $conn;
$conn = get_accumulator_connection($bbconfig);

// Initialize a dict for messages
global $messages;
$messages = array();

//greetings
log_('Checking Orphaned Events for '. $optList['instance'], 'INFO');

//gets all relevant pulls from the instance
$result = exec_query("
    SELECT *
    FROM incoming
    WHERE instance='{$optList['instance']}'
    ORDER BY mailing_id
    ", $conn);

//eventually: join mailing id keys on summary for initial processed (dt_first)
//can't do it because some old events get lodged without mailing_id's in the summary table
archive_orphaned_events($result, $optList);


?>
