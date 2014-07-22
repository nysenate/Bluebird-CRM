<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2013-11-08
//

require_once dirname(__FILE__).'/../civicrm/scripts/bluebird_config.php';

define('DB_TYPE_CIVICRM', 'civicrm');
define('DB_TYPE_DRUPAL', 'drupal');
define('DB_TYPE_LOG', 'log');

$commonFunc_db_types = array(DB_TYPE_CIVICRM, DB_TYPE_DRUPAL, DB_TYPE_LOG);

/**
 * Implements Python's dict.get(), CRM_Utils_Array::value()
 * check an array for a key.  If the key exists, return its value, otherwise
 * return a default value.  
 */
function array_value($array, $key, $default_value = null) {
    return (is_array($array) && isset($array[$key])) ? $array[$key] : $default_value;
}

/**
 * returns the database name, constructed from the BlueBird configuration
 */
function getDatabaseName($bbcfg, $dbtype) {
  global $commonFunc_db_types;
  $ret = false;
  if (is_array($bbcfg) && in_array($dbtype, $commonFunc_db_types)) {
    $dbname = array_value($bbcfg,'db.basename');
    if (!$dbname) { 
      $dbname = array_value($bbcfg,'shortname',''); 
    }
    if (!$dbname) { 
      $ret = false; 
    } else {
      $prefix = "db.$dbtype.prefix";
      $ret = array_value($bbcfg,$prefix,'') . $dbname;
    }
  }
  return $ret;
}

function getDatabaseConnection($bbcfg, $dbtype)
{
  // $dbtype should be "civicrm", "drupal", or "log".  The DB_TYPE_CIVICRM,
  // DB_TYPE_DRUPAL, and DB_TYPE_LOG constants help to enforce this.

  $dbcon = new mysqli($bbcfg['db.host'], $bbcfg['db.user'], $bbcfg['db.pass']);
  if (!$dbcon) {
    echo mysql_error()."\n";
    return null;
  }

  $dbname = (isset($bbcfg['db.basename'])) ? $bbcfg['db.basename'] : $bbcfg['shortname'];

  $prefix_index = "db.$dbtype.prefix";
  $dbname = getDatabaseName($bbcfg, $dbtype);
  if (!$dbcon->select_db($dbname)) {
    echo "{$dbcon->error}\n";
    $dbcon->close();
    return null;
  }
  return $dbcon;
} // getDatabaseConnection()



function bootstrapScript($prog, $instance, $dbtype)
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

  $dbcon = getDatabaseConnection($bbconfig, $dbtype);
  if (!$dbcon) {
    echo "$prog: Unable to connect to database for instance [$instance]\n";
    return null;
  }

  return array('bbconfig'=>$bbconfig, 'dbcon'=>$dbcon);
} // bootstrapScript()

?>
