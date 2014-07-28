<?php

require_once 'common_funcs.php';


// initialize some variables
// this script
$prog = basename($argv[0]);
// where this script lives
$thispath = dirname(__FILE__);
// the instance on which to act
$instance = (string) array_value($argv, 1, '');
$debug = ((string)array_value($argv, 2, ''))=='debug' ? true : false;

// if no instance has been provided, exit
if (!$instance) {
  echo "Usage: $prog instance [debug]\n";
  echo "  instance must exist in the BlueBird configuration file.\n";
  echo "  if debug option is provided, CIVI_DB.nyss_debug will be created and populated.\n\n";
  exit(1);
}

$bootstrap = bootstrap_script($prog, $instance, DB_TYPE_CIVICRM);
if ($bootstrap == null) {
  echo "$prog: Unable to bootstrap this script; exiting\n";
  exit(1);
}

$bbconfig = $bootstrap['bbconfig'];
$dbh = $bootstrap['dbh'];

$rc = 0;

// load the SQL file
$sql = file_get_contents("{$thispath}/changelog-summary-detail-create-prepopulate.sql");
$civi_db = "`" . get_database_name($bbconfig, DB_TYPE_CIVICRM) . "`";
$log_db = "`" . get_database_name($bbconfig, DB_TYPE_LOG) . "`";

// replace database identifiers
$sql = str_replace(array('{{CIVIDB}}', '{{LOGDB}}'),
                   array($civi_db, $log_db),
                   $sql);

// detect debug status
if ($debug) {
  $sql = "SET @nyss_debug_flag = 1;\n$sql";
}

echo "Executing data conversion using CIVIDB=$civi_db, LOGDB=$log_db\n";
echo "Please be patient... this process could take up to 30 minutes to complete.\n";

// execute the SQL
if ($dbh->exec($sql) === false) {
  echo "Database Error: ".print_r($dbh->errorInfo());
  echo "Update failed!\n";
}
else {
  echo "Conversion success.  MySQL reported no errors.\n";
  echo "REBUILD ALL TRIGGERS NOW!\n";
}

if ($debug) {
  echo "\nDebug status was on.  Check {$civi_db}.nyss_debug in the CiviCRM database for information.\n\n";
}

// clean up
$dbh = null;
exit($rc);
