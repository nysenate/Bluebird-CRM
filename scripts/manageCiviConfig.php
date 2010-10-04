<?php

function getCiviConfig($dbcon)
{
  $sql = "SELECT id, config_backend FROM civicrm_domain;";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  //get the only row
  $row = mysql_fetch_assoc($result);
  if ($row['config_backend']) {
    $cb = unserialize($row['config_backend']);
    return $cb;
  }
  else {
    return null;
  }
} // getCiviConfig()


function listCiviConfig($cb)
{
  foreach ($cb as $key => $val) {
    if (is_string($val)) {
      echo "[$key] => [$val]\n";
    }
    else {
      echo "[$key] => ";
      print_r($val);
    }
  }
} // listCiviConfig()


function updateCiviConfig($dbcon, $cb, $crmhost, $appdir, $datadir)
{
  $http_prefix = "http://$crmhost";
  $data_prefix = "$datadir/$crmhost/civicrm";

  $cb['userFrameworkResourceURL'] = "$http_prefix/sites/all/modules/civicrm/";
  $cb['imageUploadURL'] = "$http_prefix/sites/default/files/civicrm/images/";
  $cb['uploadDir'] = "$data_prefix/upload/";
  $cb['imageUploadDir'] = "$data_prefix/images/";
  $cb['customFileUploadDir'] = "$data_prefix/custom/";
  $cb['customTemplateDir'] = "$appdir/civicrm/custom/templates";
  $cb['customPHPPathDir'] = "$appdir/civicrm/custom/php";
  $cb['civiAbsoluteURL'] = "$http_prefix/";
  $cb['configAndLogDir'] = "$data_prefix/templates_c/en_US/ConfigAndLog/";

  //save back to db
  $sql = "UPDATE civicrm_domain set config_backend='".serialize($cb)."';";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  return true;
} // updateCiviConfig()


function nullifyCiviConfig($dbcon)
{
  $sql = "UPDATE civicrm_domain set config_backend=NULL;";
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
  echo "$prog: Usage: cmd dbhost dbuser dbpass dbname [crmhost] [appdir] [datadir]\n";
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
    echo "$prog: Unable to get CiviCRM backend configuration\n";
    $rc = 1;
  }
  else if (is_array($civiConfig)) {
    if ($cmd == "update") {
      echo "Updating the CiviCRM backend config\n";
      if (updateCiviConfig($dbcon, $civiConfig, $crmhost, $appdir, $datadir) === false) {
        $rc = 1;
      }
    }
    else if ($cmd == "nullify") {
      echo "Nullifying the CiviCRM backend config\n";
      if (nullifyCiviConfig($dbcon) === false) {
        $rc = 1;
      }
    }
    else {
      listCiviConfig($civiConfig);
    }
  }
  else {
    echo "$prog: CiviCRM backend config is empty.\n";
  }

  mysql_close($dbcon);
  exit($rc);
}
