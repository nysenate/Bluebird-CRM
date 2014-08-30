<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2013-11-08
// Revised: 2014-07-23 - Migrated from [deprecated] PHP mysql interface to PDO
//

require_once dirname(__FILE__).'/../civicrm/scripts/bluebird_config.php';

define('DB_TYPE_CIVICRM', 'civicrm');
define('DB_TYPE_DRUPAL', 'drupal');
define('DB_TYPE_LOG', 'log');

define('DRIVER_MYSQL', 1);
define('DRIVER_PGSQL', 2);


/**
 * Implements Python's dict.get() and CiviCRM's CRM_Utils_Array::value()
 * Check an array for a key.  If the key exists, return its value.
 * Otherwise, return a default value.
 */
function array_value($array, $key, $default_value = null)
{
  return (is_array($array) && isset($array[$key])) ? $array[$key] : $default_value;
} // array_value()



function get_database_name(&$bbcfg, $dbtype)
{
  $valid_dbtypes = array(DB_TYPE_CIVICRM, DB_TYPE_DRUPAL, DB_TYPE_LOG);

  if (in_array($dbtype, $valid_dbtypes)) {
    $dbname = array_value($bbcfg, 'db.basename');
    if (!$dbname) {
      $dbname = array_value($bbcfg, 'shortname', '');
    }
    $prefix_param = "db.$dbtype.prefix";
    $dbname = array_value($bbcfg, $prefix_param, '').$dbname;
    return $dbname;
  }
  else {
    echo "Invalid database type [$dbtype] specified\n";
    return null;
  }
} // get_database_name()



// Use PDO to connect to the database specified by the current configuration.
// $dbtype should be "civicrm", "drupal", or "log".  The DB_TYPE_CIVICRM,
// DB_TYPE_DRUPAL, and DB_TYPE_LOG constants help to enforce this.

function connect_to_database($bbcfg, $dbtype, $driver = DRIVER_MYSQL)
{
  $dbname = get_database_name($bbcfg, $dbtype);
  if (!$dbname) {
    echo "Unable to formulate database name\n";
    return null;
  }

  if ($driver == DRIVER_MYSQL) {
    $dsn = "mysql:host=${bbcfg['db.host']};dbname=$dbname";
  }
  else if ($driver == DRIVER_PGSQL) {
    $dsn = "pgsql:host=${bbcfg['db.host']};dbname=$dbname";
  }
  else {
    echo "Invalid database driver specified\n";
    return null;
  }

  try {
    $dbh = new PDO($dsn, $bbcfg['db.user'], $bbcfg['db.pass']);
    return $dbh;
  }
  catch (PDOException $e) {
    echo "Unable to connect to database\n";
    return null;
  }
} // connect_to_database()



function bootstrap_script($prog, $instance, $dbtype)
{
  $bbconfig = get_bluebird_instance_config($instance);
  if (!$bbconfig) {
    echo "$prog: Unable to configure instance [$instance]\n";
    return null;
  }

  // Since CiviCRM is not being bootstrapped, CIVICRM_SITE_KEY must be
  // manually defined here, since the CRM_Utils_Crypt::encrypt() method
  // depends on it.
  define('CIVICRM_SITE_KEY', $bbconfig['site.key']);

  $dbh = connect_to_database($bbconfig, $dbtype);
  if (!$dbh) {
    echo "$prog: Unable to connect to database for instance [$instance]\n";
    return null;
  }

  return array('bbconfig'=>$bbconfig, 'dbh'=>$dbh, 'dblayer'=>'PDO');
} // bootstrap_script()

?>
