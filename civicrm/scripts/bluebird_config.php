<?php
# bluebird_config.php - Initial configuration for Drupal and CiviCRM settings
#
# Project: BluebirdCRM
# Author: Ken Zalewski
# Organization: New York State Senate
# Date: 2010-09-10
# Revised: 2011-05-11
#

define('BASE_DIR', realpath(dirname(__FILE__).'/../../'));


/*
** Locate the Bluebird configuration file.
*/
function get_config_filepath($filename)
{
  $cfg_file = null;

  if (file_exists(BASE_DIR."/$filename")) {
    $cfg_file = BASE_DIR."/$filename";
  }
  else if (($cfg_file = getenv('BLUEBIRD_CONFIG_FILE')) === false) {
    $cfg_file = "/etc/$filename";
  }
  
  return $cfg_file;
} // get_config_filepath()


/*
** Retrieve an array of all configuration parameters within the Bluebird
** configuration file.  The array is indexed by the config groups.  Each
** value is itself an array of name-value pairs.
*/
function get_bluebird_config($filename = 'bluebird.cfg')
{
  $cfg_file = get_config_filepath($filename);

  if (!file_exists($cfg_file)) {
    error_log("$cfg_file: Bluebird configuration file not found.");
    return null;
  }

  $bbini = parse_ini_file($cfg_file, true);
  if ($bbini === false) {
    error_log("$cfg_file: Unable to parse the Bluebird configuration file.");
    return null;
  }

  return $bbini;
} // get_bluebird_config()


/*
** Retrieve an array of configuration parameters for a given CRM instance.
** If no instance is specified, attempt to determine the instance from the
** HTTP_HOST server variable, or the INSTANCE_NAME environment variable.
**
** The instance can be a short name (hostname only), or a server name (fully
** qualified domain name).
*/
function get_bluebird_instance_config($filename, $instance = null)
{
  $shortname = null;

  if ($instance == null) {
    if (isset($_SERVER['HTTP_HOST'])) {
      $instance = $_SERVER['HTTP_HOST'];
    }
    else if (($instance = getenv('INSTANCE_NAME')) === false) {
      error_log("Unable to determine CRM instance name.");
      return null;
    }
  }

  $firstdot = strpos($instance, '.');
  if ($firstdot === false) {
    $shortname = $instance;
    $default_base_domain = "crm.nysenate.gov";
  }
  else {
    $shortname = substr($instance, 0, $firstdot);
    $default_base_domain = substr($instance, $firstdot + 1);
  }

  $bbini = get_bluebird_config($filename);
  if ($bbini) {
    $bbcfg = array();
    // Grab the globals first.
    if (isset($bbini['globals'])) {
      $bbcfg = array_merge($bbcfg, $bbini['globals']);
    }
    // Now merge the instance-specific parameters, which override the globals.
    $instance_key = 'instance:'.$shortname;
    if (isset($bbini[$instance_key])) {
      $bbcfg = array_merge($bbcfg, $bbini[$instance_key]);
    }
  }

  $db_url = 'mysql://'.$bbcfg['db.user'].':'.$bbcfg['db.pass'].'@'.$bbcfg['db.host'].'/';
  $db_basename = isset($bbcfg['db.basename']) ? $bbcfg['db.basename'] : $shortname;
  $base_domain = isset($bbcfg['base.domain']) ? $bbcfg['base.domain'] : $default_base_domain;
  $civicrm_db_url = $db_url.$bbcfg['db.civicrm.prefix'].$db_basename;
  $drupal_db_url = $db_url.$bbcfg['db.drupal.prefix'].$db_basename;
  $data_basename = isset($bbcfg['data.basename']) ? $bbcfg['data.basename'] : $shortname;

  // Add some extra convenience parameters.
  $bbcfg['civicrm_db_url'] = $civicrm_db_url;
  $bbcfg['drupal_db_url'] = $drupal_db_url;
  $bbcfg['base_dir'] = BASE_DIR;
  $bbcfg['data_dirname'] = "$data_basename.$base_domain";
  $bbcfg['servername'] = "$shortname.$base_domain";
  $bbcfg['shortname'] = $shortname;
  return $bbcfg;
} // get_bluebird_instance_config()


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
