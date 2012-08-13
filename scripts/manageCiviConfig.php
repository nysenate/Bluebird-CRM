<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-11-23
// Revised: 2012-03-21
//

require_once dirname(__FILE__).'/../civicrm/scripts/bluebird_config.php';


function getDatabaseConnection($bbcfg)
{
  $dbcon = mysql_connect($bbcfg['db.host'], $bbcfg['db.user'], $bbcfg['db.pass']);
  if (!$dbcon) {
    echo mysql_error()."\n";
    return null;
  }

  $dbname = (isset($bbcfg['db.basename'])) ? $bbcfg['db.basename'] : $bbcfg['shortname'];
  $dbname = $bbcfg['db.civicrm.prefix'].$dbname;
  if (!mysql_select_db($dbname, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    mysql_close($dbcon);
    return null;
  }
  return $dbcon;
} // getDatabaseConnection()



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



function getConfigBackend($dbcon, $table='civicrm_domain', $field='config_backend')
{
  $sql = "SELECT id, $field FROM $table WHERE id=1;";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  //get the only row
  $row = mysql_fetch_assoc($result);
  if ($row[$field]) {
    return unserialize($row[$field]);
  }
  else {
    return null;
  }
} // getConfigBackend()



function getCiviConfig($dbcon)
{
  $civiConfig = array();
  $civiConfig['config_backend'] = getConfigBackend($dbcon, 'civicrm_domain', 'config_backend');
  $civiConfig['mailing_backend'] = getConfigBackend($dbcon, 'civicrm_preferences', 'mailing_backend');
  $civiConfig['dirprefs'] = getOptionValues($dbcon, 'directory_preferences');
  $civiConfig['urlprefs'] = getOptionValues($dbcon, 'url_preferences');
  $civiConfig['from_name'] = getOptionValues($dbcon, 'from_email_address');
  return $civiConfig;
} // getCiviConfig()



function listCiviConfig($civicfg)
{
  foreach ($civicfg as $cfggrp => $cfglist) {
    echo "\n==> Config group: $cfggrp\n";
    foreach ($cfglist as $key => $val) {
      if (is_scalar($val)) {
        if (is_string($val)) {
          echo "[$key] => \"$val\"\n";
        }
        else {
          echo "[$key] => $val\n";
        }
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
  $sql = "UPDATE civicrm_option_value SET value='$optval' ".
         "WHERE name='$optname' AND option_group_id=( ".
         "   SELECT id FROM civicrm_option_group ".
         "   WHERE name='$groupname' );";
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



function updateEmailMenu($dbcon)
{
  //enable CiviMail report menu items
  $sql = "UPDATE civicrm_navigation SET is_active=1 ".
         "WHERE parent_id=(".
                  "SELECT id FROM civicrm_navigation ".
                  "WHERE name='Mass Email');";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    return true;
  }
} // updateEmailMenu()



function updateFromEmail($dbcon, $bbcfg)
{
  //update the FROM email address
  $fromName = $bbcfg['senator.name.formal'];

  if (isset($bbcfg['senator.email'])) {
    $fromEmail = $bbcfg['senator.email'];
  }
  else {
    $fromEmail = $bbcfg['smtp.subuser'];
  }

  $from = '"'.addslashes($fromName).'"'." <$fromEmail>";
  $sql = "UPDATE civicrm_option_value SET label='$from', name='$from' ".
         "WHERE option_group_id=(".
                  "SELECT id FROM civicrm_option_group ".
                  "WHERE name='from_email_address');";
  if (!mysql_query($sql , $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    return true;
  }
} //updateFromEmail()



function updateBackend($dbcon, $table='civicrm_domain',
                       $field='config_backend', $backend)
{
  $sql = "UPDATE $table SET $field='".serialize($backend)."';";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  return true;
} // updateBackend()



function updateConfigBackend($dbcon, $backend)
{
  return updateBackend($dbcon, 'civicrm_domain', 'config_backend', $backend);
} // updateConfigBackend()



function updateMailingBackend($dbcon, $backend)
{
  return updateBackend($dbcon, 'civicrm_preferences', 'mailing_backend', $backend);
} // updateConfigBackend()



function updateCiviConfig($dbcon, $civicfg, $bbcfg)
{
  $crmhost = $bbcfg['servername'];
  $appdir = $bbcfg['app.rootdir'];
  $datadir = $bbcfg['data.rootdir'];
  $incemail = $bbcfg['search.include_email_in_name'];
  $incwild = $bbcfg['search.include_wildcard_in_name'];
  $batchlimit = $bbcfg['mailer.batch_limit'];
  $jobsize = $bbcfg['mailer.job_size'];
  $jobsmax = $bbcfg['mailer.jobs_max'];

  $http_prefix = "http://$crmhost";  // no longer necessary
  $data_prefix = "$datadir/$crmhost/civicrm";  // no longer necessary
  $rc = true;

  $cb = $civicfg['config_backend'];
  $cb['civiAbsoluteURL'] = "$http_prefix/";
  $cb['includeEmailInName'] = $incemail;
  $cb['includeWildCardInName'] = $incwild;
  $cb['enableComponents'][] = 'CiviMail';
  $cb['enableComponentIDs'][] = 4;
  $cb['mailerBatchLimit'] = $batchlimit;
  $cb['mailerJobSize'] = $jobsize;
  $cb['mailerJobsMax'] = $jobsmax;
  $rc &= updateConfigBackend($dbcon, $cb);

  $mb = $civicfg['mailing_backend'];
  $mb['smtpServer']   = $bbcfg['smtp.host'];
  $mb['smtpPort']     = $bbcfg['smtp.port'];
  $mb['smtpAuth']     = $bbcfg['smtp.auth'];
  $mb['smtpUsername'] = $bbcfg['smtp.subuser'];
  require_once $appdir.'/modules/civicrm/CRM/Utils/Crypt.php';
  $mb['smtpPassword'] = CRM_Utils_Crypt::encrypt($bbcfg['smtp.subpass']);
  $rc &= updateMailingBackend($dbcon, $mb);

  $rc &= updateDirPref($dbcon, 'uploadDir', "upload/");
  $rc &= updateDirPref($dbcon, 'imageUploadDir', "images/");
  $rc &= updateDirPref($dbcon, 'customFileUploadDir', "custom/");
  $rc &= updateDirPref($dbcon, 'customTemplateDir', "$appdir/civicrm/custom/templates");
  $rc &= updateDirPref($dbcon, 'customPHPPathDir', "$appdir/civicrm/custom/php");
  $rc &= updateUrlPref($dbcon, 'userFrameworkResourceURL', "sites/all/modules/civicrm/");
  $rc &= updateUrlPref($dbcon, 'imageUploadURL', "sites/default/files/civicrm/images/");

  $rc &= updateEmailMenu($dbcon);

  return $rc;
} // updateCiviConfig()



function nullifyCiviConfig($dbcon)
{
  $sql = "UPDATE civicrm_domain SET config_backend=NULL; ".
         "UPDATE civicrm_preferences SET mailing_backend=NULL WHERE id=1; ".
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

if ($argc != 3) {
  echo "Usage: $prog instance cmd\n";
  echo "   cmd can be: list, update, or nullify\n";
  exit(1);
}
else {
  $instance = $argv[1];
  $cmd = $argv[2];

  $bbconfig = get_bluebird_instance_config($instance);
  if (!$bbconfig) {
    echo "$prog: Unable to configure instance [$instance]\n";
    exit(1);
  }

  $dbcon = getDatabaseConnection($bbconfig);
  if (!$dbcon) {
    echo "$prog: Unable to connect to database for instance [$instance]\n";
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
      if (updateCiviConfig($dbcon, $civiConfig, $bbconfig) === false) {
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
