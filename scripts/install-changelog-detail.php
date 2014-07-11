<?php
require_once 'common_funcs.php';

// initialize some variables
// this script
$prog = basename($argv[0]);
// where this script lives
$thispath = dirname(__FILE__);
// the instance on which to act
$instance = (string) array_value($argv, 1, '');

// if no instance has been provided, exit
if (!$instance) {
  echo "Usage: $prog instance\n";
  echo "  instance must exist in the BlueBird configuration file.\n\n";
  exit(1);
}

$bootstrap = bootstrapScript($prog, $instance, DB_TYPE_CIVICRM);
if ($bootstrap == null) {
  echo "$prog: Unable to bootstrap this script; exiting\n";
  exit(1);
}

$bbconfig = $bootstrap['bbconfig'];
$dbcon = $bootstrap['dbcon'];

$rc = 0;

// load the SQL file
$sql = file_get_contents("{$thispath}/changelog-summary-detail-create-prepopulate.sql");
$civi_db = "`" . getDatabaseName($bbconfig, DB_TYPE_CIVICRM) . "`";
$log_db = "`" . getDatabaseName($bbconfig, DB_TYPE_LOG) . "`";

// replace database identifiers
$sql = str_replace(array('{{CIVIDB}}', '{{LOGDB}}'),
                   array($civi_db, $log_db),
                   $sql);

// execute the SQL
//file_put_contents('sql-test.sql',$sql);
if (!($dbcon->multi_query($sql))) {
  $rc = $dbcon->errno;
} else {
  do {
    $res = $dbcon->store_result();
    if ($dbcon->errno) {
      $rc = $dbcon->errno;
    }
  } while ($dbcon->more_results() && $dbcon->next_result());
  $rc = $dbcon->errno;
}

if ($rc) {
  echo "MySQL Error {$rc}: {$dbcon->error}\n";
  echo "Update failed!\n";
}

// clean up
$dbcon->close();
exit($rc);
