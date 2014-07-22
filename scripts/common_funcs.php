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


function getDatabaseConnection($bbcfg, $dbtype)
{
  // $dbtype should be "civicrm", "drupal", or "log".  The DB_TYPE_CIVICRM,
  // DB_TYPE_DRUPAL, and DB_TYPE_LOG constants help to enforce this.

  $dbcon = mysql_connect($bbcfg['db.host'], $bbcfg['db.user'], $bbcfg['db.pass']);
  if (!$dbcon) {
    echo mysql_error()."\n";
    return null;
  }

  $dbname = (isset($bbcfg['db.basename'])) ? $bbcfg['db.basename'] : $bbcfg['shortname'];

  $prefix_index = "db.$dbtype.prefix";
  $dbname = $bbcfg[$prefix_index].$dbname;
  if (!mysql_select_db($dbname, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    mysql_close($dbcon);
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



function array_value($array, $key, $default_value = null)
{
  return (is_array($array) && isset($array[$key])) ? $array[$key] : $default_value;
}

?>
