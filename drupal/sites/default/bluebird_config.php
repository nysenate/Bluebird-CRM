<?php
# bluebird_config.php - Initial configuration for Drupal and CiviCRM settings
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
# Revised: 2010-09-28
#

function get_bluebird_config($filename = 'bluebird.cfg')
{
  $cur_dir = dirname(__FILE__);
  $base_dir = realpath($cur_dir.'/../../../');

  if (file_exists($base_dir."/$filename")) {
    $cfg_file = $base_dir."/$filename";
  }
  else if (($cfg_file = getenv('BLUEBIRD_CONFIG_FILE')) === false) {
    $cfg_file = "/etc/$filename";
  }
  
  if (isset($_SERVER['SERVER_NAME'])) {
    $servername = $_SERVER['SERVER_NAME'];
  }
  else if (getenv('SERVER_NAME') !== false) {
    $servername = getenv('SERVER_NAME');
  }
  else {
    die("Unable to determine server name.\n");
  }
  
  $shortname = substr($servername, 0, strpos($servername, '.'));
  
  $bbini = parse_ini_file($cfg_file, true);
  
  $dbhost = get_key_value($bbini, $shortname, 'db.host');
  $dbuser = get_key_value($bbini, $shortname, 'db.user');
  $dbpass = get_key_value($bbini, $shortname, 'db.pass');
  $dbciviprefix = get_key_value($bbini, $shortname, 'db.civicrm.prefix');
  $dbdrupprefix = get_key_value($bbini, $shortname, 'db.drupal.prefix');
  $drupaldir = realpath($cur_dir.'/../../');

  $civicrm_db_url = "mysql://$dbuser:$dbpass@$dbhost/$dbciviprefix$shortname";
  $drupal_db_url = "mysql://$dbuser:$dbpass@$dbhost/$dbdrupprefix$shortname";
  
  $bbcfg = array();
  $bbcfg['servername'] = $servername;
  $bbcfg['shortname'] = $shortname;
  $bbcfg['drupal_db_url'] = $drupal_db_url;
  $bbcfg['civicrm_db_url'] = $civicrm_db_url;
  $bbcfg['drupal_root'] = $drupaldir;

  return $bbcfg;
} // get_bluebird_config()


/*
** Return the value of the given key.  If the key is found within the
** provide instance group, use that.  Otherwise, attempt to locate the
** key in the 'globals' group.
*/
function get_key_value($ini, $instance, $keyname)
{
  if (isset($ini['instance:'.$instance][$keyname])) {
    return $ini['instance:'.$instance][$keyname];
  }
  else if (isset($ini['globals'][$keyname])) {
    return $ini['globals'][$keyname];
  }
  else {
    return false;
  }
} // get_key_value()
