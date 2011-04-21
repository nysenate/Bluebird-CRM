<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-11-23
// Revised: 2011-04-21
//

function getOptionValues($dbcon, $name)
{
  $optValues = array();
  $sql = "SELECT name, value FROM civicrm_option_value ".
         "WHERE option_group_id IN ".
         "  ( SELECT id FROM civicrm_option_group ".
         "    WHERE name='$name' );";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return null;
  }

  //get all rows
  while (($row = mysql_fetch_assoc($result))) {
    $optValues[$row['name']] = $row['value'];
  }
  return $optValues;
} // getOptionValues()


function getCiviConfig($dbcon)
{
  $civiconfig = array();
  $sql = "SELECT id, config_backend FROM civicrm_domain;";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  //get the only row
  $row = mysql_fetch_assoc($result);
  if ($row['config_backend']) {
    $civiconfig['backend'] = unserialize($row['config_backend']);
  }
  else {
    $civiconfig['backend'] = null;
  }

  $civiconfig['dirprefs'] = getOptionValues($dbcon, 'directory_preferences');
  $civiconfig['urlprefs'] = getOptionValues($dbcon, 'url_preferences');
  return $civiconfig;
} // getCiviConfig()


function listCiviConfig($civicfg)
{
  foreach ($civicfg as $cfggrp => $cfglist) {
    echo "\n==> Config group: $cfggrp\n";
    foreach ($cfglist as $key => $val) {
      if (is_string($val)) {
        echo "[$key] => [$val]\n";
      }
      else {
        echo "[$key] => ";
        print_r($val);
      }
    }
  }
} // listCiviConfig()


function updateOptionValue($dbcon, $groupname, $optname, $optval)
{
  $sql = "UPDATE civicrm_option_value SET value = '$optval' ".
         "WHERE name = '$optname' AND option_group_id IN ( ".
         "   SELECT id FROM civicrm_option_group ".
         "   WHERE name = '$groupname' );";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  return true;
} // updateOptionValue()


function updateDirPref($dbcon, $optname, $optval)
{
  return updateOptionValue($dbcon, 'directory_preferences', $optname, $optval);
} // updateDirPref()


function updateUrlPref($dbcon, $optname, $optval)
{
  return updateOptionValue($dbcon, 'url_preferences', $optname, $optval);
} // updateUrlPref()


function updateCiviConfig($dbcon, $civicfg, $crmhost, $appdir, $datadir)
{
  $http_prefix = "http://$crmhost";
  $data_prefix = "$datadir/$crmhost/civicrm";
  $rc = true;

  $cb = $civicfg['backend'];
  $cb['civiAbsoluteURL'] = "$http_prefix/";

  /***  
     The remainder of these parameters are deprecated in config_backend.
  $cb['configAndLogDir'] = "$data_prefix/templates_c/en_US/ConfigAndLog/";
  ****/

  $sql = "UPDATE civicrm_domain SET config_backend='".serialize($cb)."';";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    $rc = false;
  }

  updateDirPref($dbcon, 'uploadDir', "$data_prefix/upload/");
  updateDirPref($dbcon, 'imageUploadDir', "$data_prefix/images/");
  updateDirPref($dbcon, 'customFileUploadDir', "$data_prefix/custom/");
  updateDirPref($dbcon, 'customTemplateDir', "$appdir/civicrm/custom/templates");
  updateDirPref($dbcon, 'customPHPPathDir', "$appdir/civicrm/custom/php");
  updateUrlPref($dbcon, 'userFrameworkResourceURL', "$http_prefix/sites/all/modules/civicrm/");
  updateUrlPref($dbcon, 'imageUploadURL', "$http_prefix/sites/default/files/civicrm/images/");

  return $rc;
} // updateCiviConfig()


function nullifyCiviConfig($dbcon)
{
  $sql = "UPDATE civicrm_domain SET config_backend=NULL; ".
         "UPDATE civicrm_option_value SET value=NULL ".
         "WHERE option_group_id IN (".
         "   SELECT id FROM civicrm_option_group ".
         "   WHERE name='directory_preferences' OR name='url_preferences' );";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    return true;
  }
} // nullifyCiviConfig()



$prog = basename($argv[0]);

if ($argc != 6 && $argc != 9) {
  echo "Usage: $prog cmd dbhost dbuser dbpass dbname [crmhost] [appdir] [datadir]\n";
  echo "   cmd can be: list, update, or nullify\n";
  exit(1);
}
else {
  $cmd = $argv[1];
  $dbhost = $argv[2];
  $dbuser = $argv[3];
  $dbpass = $argv[4];
  $dbname = $argv[5];
  $crmhost = ($argc == 9) ? $argv[6] : "";
  $appdir = ($argc == 9) ? $argv[7] : "";
  $datadir = ($argc == 9) ? $argv[8] : "";

  $dbcon = mysql_connect($dbhost, $dbuser, $dbpass);
  if (!$dbcon) {
    echo mysql_error()."\n";
    exit(1);
  }

  if (!mysql_select_db($dbname, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    mysql_close($dbcon);
    exit(1);
  }

  $rc = 0;
  $civiConfig = getCiviConfig($dbcon);

  if ($civiConfig === false) {
    echo "$prog: Unable to get CiviCRM backend configuration.\n";
    $rc = 1;
  }
  else if (is_array($civiConfig)) {
    if ($cmd == "update") {
      echo "Updating the CiviCRM configuration.\n";
      if (updateCiviConfig($dbcon, $civiConfig, $crmhost, $appdir, $datadir) === false) {
        $rc = 1;
      }
    }
    else if ($cmd == "nullify") {
      echo "Nullifying the CiviCRM configuration.\n";
      if (nullifyCiviConfig($dbcon) === false) {
        $rc = 1;
      }
    }
    else {
      listCiviConfig($civiConfig);
    }
  }
  else {
    echo "$prog: CiviCRM configuration is empty.\n";
  }

  mysql_close($dbcon);
  exit($rc);
}
