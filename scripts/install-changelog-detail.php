<?php
require_once 'common_funcs.php';


function installGetDatabaseConnection($bbcfg, $dbtype)
{
  // $dbtype should be "civicrm", "drupal", or "log".  The DB_TYPE_CIVICRM,
  // DB_TYPE_DRUPAL, and DB_TYPE_LOG constants help to enforce this.

  $dbcon = new mysqli($bbcfg['db.host'], $bbcfg['db.user'], $bbcfg['db.pass']);
  if (!$dbcon) {
    echo mysql_error()."\n";
    return null;
  }

  $dbname = getDatabaseName($bbcfg, $dbtype);
  if (!$dbcon->select_db($dbname)) {
    echo "{$dbcon->error}\n";
    $dbcon->close();
    return null;
  }
  return $dbcon;
} // installGetDatabaseConnection()


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

// This script needs to use MySQLi instead of MySQL, so close the
// already-opened MySQL connection, and reopen as MySQLi.
mysql_close($bootstrap['dbcon']);

// because we need a mysqli object to use multi_query()
$dbcon = installGetDatabaseConnection($bbconfig, DB_TYPE_CIVICRM);

$rc = 0;

// load the SQL file
$sql = file_get_contents("{$thispath}/changelog-summary-detail-create-prepopulate.sql");
$civi_db = "`" . getDatabaseName($bbconfig, DB_TYPE_CIVICRM) . "`";
$log_db = "`" . getDatabaseName($bbconfig, DB_TYPE_LOG) . "`";

// replace database identifiers
$sql = str_replace(array('{{CIVIDB}}', '{{LOGDB}}'),
                   array($civi_db, $log_db),
                   $sql);

echo "Executing data conversion using CIVIDB={$civi_db}, LOGDB={$log_db}\n";
echo "Please be patient . . . this process could take up to 30 minutes to complete.\n";

// execute the SQL
if (!($dbcon->multi_query($sql))) {
  $rc = $dbcon->errno;
}
else {
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
else {
  echo "Conversion success.  MySQL reported no errors.\n";
  echo "REBUILD ALL TRIGGERS NOW!\n";
}

// clean up
$dbcon->close();
exit($rc);
