<?php
# bluebird_config.php - Initial configuration for Drupal and CiviCRM settings
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
# Revised: 2011-03-22
#

function get_bluebird_config($filename = 'bluebird.cfg')
{
  $drupal_dir = realpath(dirname(__FILE__).'/../../');
  $base_dir = realpath($drupal_dir.'/../');

  if (file_exists($base_dir."/$filename")) {
    $cfg_file = $base_dir."/$filename";
  }
  else if (($cfg_file = getenv('BLUEBIRD_CONFIG_FILE')) === false) {
    $cfg_file = "/etc/$filename";
  }
  
  if (!file_exists($cfg_file)) {
    die("$cfg_file: Bluebird configuration file not found.\n");
  }

  if (isset($_SERVER['HTTP_HOST'])) {
    $servername = $_SERVER['HTTP_HOST'];
    $firstdot = strpos($servername, '.');
    if ($firstdot === false) {
      $shortname = $servername;
    }
    else {
      $shortname = substr($servername, 0, $firstdot);
    }
    $default_base_domain = substr($servername, $firstdot + 1);
  }
  else if (($shortname = getenv('INSTANCE_NAME')) !== false) {
    $servername = "";
    $default_base_domain = "crm.nysenate.gov";
  }
  else {
    die("Unable to determine CRM instance name.\n");
  }
  
  $bbini = parse_ini_file($cfg_file, true);
  if ($bbini === false) {
    die("Unable to parse the Bluebird configuration file.\n");
  }
  
  $dbhost = get_key_value($bbini, $shortname, 'db.host');
  $dbuser = get_key_value($bbini, $shortname, 'db.user');
  $dbpass = get_key_value($bbini, $shortname, 'db.pass');
  $dbciviprefix = get_key_value($bbini, $shortname, 'db.civicrm.prefix');
  $dbdrupprefix = get_key_value($bbini, $shortname, 'db.drupal.prefix');
  $db_basename = get_key_value($bbini, $shortname, 'db.basename') or
    $db_basename = $shortname;
  $base_domain = get_key_value($bbini, $shortname, 'base.domain') or
    $base_domain = $default_base_domain;
  $datadir = get_key_value($bbini, $shortname, 'data.rootdir');
  $data_basename = get_key_value($bbini, $shortname, 'data.basename') or
    $data_basename = $shortname;
  $data_dirname = "$data_basename.$base_domain";
  $imapaccts = get_key_value($bbini, $shortname, 'imap.accounts');

  // Always set servername, even if HTTP_HOST was set above.  This allows
  // us to override the domain in the config file.
  $servername = "$shortname.$base_domain";

  $civicrm_db_url = "mysql://$dbuser:$dbpass@$dbhost/$dbciviprefix$db_basename";
  $drupal_db_url = "mysql://$dbuser:$dbpass@$dbhost/$dbdrupprefix$db_basename";
  
  $bbcfg = array();
  $bbcfg['base_dir'] = $base_dir;
  $bbcfg['drupal_root'] = $drupal_dir;
  $bbcfg['servername'] = $servername;
  $bbcfg['shortname'] = $shortname;
  $bbcfg['base_domain'] = $base_domain;
  $bbcfg['db_basename'] = $db_basename;
  $bbcfg['drupal_db_url'] = $drupal_db_url;
  $bbcfg['civicrm_db_url'] = $civicrm_db_url;
  $bbcfg['data_rootdir'] = $datadir;
  $bbcfg['data_dirname'] = $data_dirname;
  $bbcfg['imap_accounts'] = $imapaccts;

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
