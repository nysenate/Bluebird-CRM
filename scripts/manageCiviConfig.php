<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-11-23
// Revised: 2013-05-12
// Revised: 2013-07-29 - added list_all and update_all options, along with
//                       getMailingComponent() and updateEmailTemplate()
// Revised: 2013-07-30 - added "scope" parameter
//

require_once 'common_funcs.php';


function sqlPrepareValue($val)
{
  if ($val) {
    return "'".serialize($val)."'";
  }
  else {
    return 'NULL';
  }
} // sqlPrepareValue()



function getSettings($dbcon, $group_name)
{
  $settings = array();
  $sql = "SELECT name, value FROM civicrm_setting ".
         "WHERE group_name = '$group_name'";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return null;
  }

  while (($row = mysql_fetch_assoc($result))) {
    $settings[$row['name']] = unserialize($row['value']);
  }
  mysql_free_result($result);
  return $settings;
} // getSettings()



function getSetting($dbcon, $group_name, $name)
{
  $sql = "SELECT value FROM civicrm_setting ".
         "WHERE group_name = '$group_name' and name = '$name'";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return null;
  }

  $row = mysql_fetch_assoc($result);
  mysql_free_result($result);
  if ($row) {
    return unserialize($row['value']);
  }
  else {
    return null;
  }
} // getSetting()



function getOptionValues($dbcon, $group_name)
{
  $optValues = array();
  $sql = "SELECT name, value FROM civicrm_option_value ".
         "WHERE option_group_id IN ".
         "  ( SELECT id FROM civicrm_option_group ".
         "    WHERE name='$group_name' )";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return null;
  }

  //get all rows
  while (($row = mysql_fetch_assoc($result))) {
    $optValues[$row['name']] = $row['value'];
  }
  mysql_free_result($result);
  return $optValues;
} // getOptionValues()



function getConfigBackend($dbcon)
{
  $sql = "SELECT id, config_backend FROM civicrm_domain WHERE id=1";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  //get the only row
  $row = mysql_fetch_assoc($result);
  mysql_free_result($result);
  if ($row['config_backend']) {
    return unserialize($row['config_backend']);
  }
  else {
    return null;
  }
} // getConfigBackend()



function getMailingComponent($dbcon, $id)
{
  $sql = "SELECT name, body_html, body_text ".
         "FROM civicrm_mailing_component WHERE id=$id";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  //get the only row
  $row = mysql_fetch_assoc($result);
  mysql_free_result($result);
  return $row;
} // getMailingComponent()



function getCiviConfig($dbcon, $scope)
{
  $civiConfig = array();

  if ($scope == 'default' || $scope == 'cb' || $scope == 'all') {
    $civiConfig['config_backend'] = getConfigBackend($dbcon);
  }

  if ($scope == 'default' || $scope == 'mb' || $scope == 'all') {
    $civiConfig['mailing_backend'] = getSetting($dbcon, 'Mailing Preferences', 'mailing_backend');
    $civiConfig['from_name'] = getOptionValues($dbcon, 'from_email_address');
  }

  if ($scope == 'default' || $scope == 'prefs' || $scope == 'all') {
    $civiConfig['dirprefs'] = getSettings($dbcon, 'Directory Preferences');
    $civiConfig['urlprefs'] = getSettings($dbcon, 'URL Preferences');
  }

  if ($scope == 'tpl' || $scope == 'all') {
    $civiConfig['template_header'] = getMailingComponent($dbcon, 1);
    $civiConfig['template_footer'] = getMailingComponent($dbcon, 2);
  }

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
        echo "[$key] => ".print_r($val, true)."\n";
      }
    }
  }
} // listCiviConfig()



function updateSetting($dbcon, $groupname, $optname, $optval)
{
  $sql = "UPDATE civicrm_setting SET value=".sqlPrepareValue($optval)." ".
         "WHERE name='$optname' AND group_name='$groupname'";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  return true;
} // updateSetting()



function updateOptionValue($dbcon, $groupname, $optname, $optval)
{
  $sql = "UPDATE civicrm_option_value SET value='$optval' ".
         "WHERE name='$optname' AND option_group_id=( ".
         "   SELECT id FROM civicrm_option_group ".
         "   WHERE name='$groupname' )";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  return true;
} // updateOptionValue()



function updateDirPref($dbcon, $optname, $optval)
{
  return updateSetting($dbcon, 'Directory Preferences', $optname, $optval);
} // updateDirPref()



function updateUrlPref($dbcon, $optname, $optval)
{
  return updateSetting($dbcon, 'URL Preferences', $optname, $optval);
} // updateUrlPref()



// Confirm that all Mass Email menu items are active.
function updateEmailMenu($dbcon)
{
  // Must perform two queries here, since a sub-select cannot be used
  // on the same table when performing an UPDATE.

  $sql = "SELECT id FROM civicrm_navigation WHERE name='Mass Email'";
  $result = mysql_query($sql, $dbcon);
  $row = mysql_fetch_assoc($result);
  $pid = $row['id'];

  $sql = "UPDATE civicrm_navigation SET is_active=1 WHERE parent_id=$pid";
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
                  "WHERE name='from_email_address')";
  if (!mysql_query($sql , $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  else {
    return true;
  }
} //updateFromEmail()



function updateConfigBackend($dbcon, $bkend)
{
  $sql = "UPDATE civicrm_domain ".
         "SET config_backend=".sqlPrepareValue($bkend)." WHERE id=1";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return false;
  }
  return true;
} // updateConfigBackend()



function updateMailingBackend($dbcon, $bknd)
{
  return updateSetting($dbcon, 'Mailing Preferences', 'mailing_backend', $bknd);
} // updateMailingBackend()



function updateEmailTemplate($dbcon, $bbcfg)
{
  // Read in the four e-mail templates (header HTML and text, footer HTML
  // and text), and replace the following macros:
  //   %INSTANCE%  %SERVER_NAME%  %SENATOR_FORMAL%
  //   %ALBANY_OFFICE_INFO% %DISTRICT_OFFICE_INFO%

  $appdir = $bbcfg['app.rootdir'];
  $tpldir = "$appdir/templates";
  $server_name = $bbcfg['servername'];
  $instance = $bbcfg['shortname'];
  $senator_formal = "New York State Senator";
  $albany_office = "Legislative Office Bldg|Albany, NY 12247";
  $district_office = "ADDRESS OF DISTRICT OFFICE";

  if (isset($bbcfg['senator.name.formal'])) {
    $senator_formal = $bbcfg['senator.name.formal'];
  }
  if (isset($bbcfg['senator.address.albany'])) {
    $albany_office = $bbcfg['senator.address.albany'];
  }
  if (isset($bbcfg['senator.address.district'])) {
    $district_office = $bbcfg['senator.address.district'];
  }

  $albany_office_html = str_replace("|", "\n<br/>", $albany_office);
  $albany_office_txt = str_replace("|", "\n", $albany_office);
  $district_office_html = str_replace("|", "\n<br/>", $district_office);
  $district_office_txt = str_replace("|", "\n", $district_office);

  $rc = true;
  $comp_id = 1;
  $search = array('%INSTANCE%', '%SERVER_NAME%', '%SENATOR_FORMAL%',
                  '%ALBANY_OFFICE_INFO%', '%DISTRICT_OFFICE_INFO%');
  $replace['html'] = array($instance, $server_name, $senator_formal,
                           $albany_office_html, $district_office_html);
  $replace['txt'] = array($instance, $server_name, $senator_formal,
                          $albany_office_txt, $district_office_txt);

  foreach (array('header', 'footer') as $comp_type) {
    $comp_type_uc = ucfirst($comp_type);
    $comp_name = "NYSS Mailing $comp_type_uc";
    $body = array();
    foreach (array('html', 'txt') as $cont_type) {
      $filename = $tpldir."/email_$comp_type.$cont_type";
      $comp_tpl = file_get_contents($filename);
      $comp_tpl = str_replace($search, $replace[$cont_type], $comp_tpl);
      $body[$cont_type] = mysql_real_escape_string($comp_tpl, $dbcon);
    }

    $sql = "UPDATE civicrm_mailing_component ".
           "SET name='$comp_name', component_type='$comp_type_uc', ".
               "subject='$comp_name', is_active=1, ".
               "body_html='{$body['html']}', body_text='{$body['txt']}' ".
           "WHERE id = $comp_id";
    if (!mysql_query($sql , $dbcon)) {
      echo mysql_error($dbcon)."\n";
      $rc = false;
    }
    $comp_id++;
  }
  return $rc;
} // updateEmailTemplate()



function updateCiviConfig($dbcon, $civicfg, $bbcfg)
{
  $server_name = $bbcfg['servername'];
  $appdir = $bbcfg['app.rootdir'];
  $incemail = $bbcfg['search.include_email_in_name'];
  $incwild = $bbcfg['search.include_wildcard_in_name'];
  $batchlimit = $bbcfg['mailer.batch_limit'];
  $jobsize = $bbcfg['mailer.job_size'];
  $jobsmax = $bbcfg['mailer.jobs_max'];

  $rc = true;

  if (isset($civicfg['config_backend'])) {
    $cb = $civicfg['config_backend'];
    $cb['civiAbsoluteURL'] = "http://$server_name/";
    $cb['includeEmailInName'] = $incemail;
    $cb['includeWildCardInName'] = $incwild;
    $cb['enableComponents'] = array('CiviMail', 'CiviCase', 'CiviReport');
    $cb['enableComponentIDs'] = array(4, 7, 8);
    $cb['mailerBatchLimit'] = $batchlimit;
    $cb['mailerJobSize'] = $jobsize;
    $cb['mailerJobsMax'] = $jobsmax;
    $cb['geoAPIKey'] = '';
    $cb['mapAPIKey'] = '';
    $cb['wkhtmltopdfPath'] = '/usr/local/bin/wkhtmltopdf';
    $rc &= updateConfigBackend($dbcon, $cb);
  }

  if (isset($civicfg['mailing_backend'])) {
    $mb = $civicfg['mailing_backend'];
    $mb['smtpServer']   = $bbcfg['smtp.host'];
    $mb['smtpPort']     = $bbcfg['smtp.port'];
    $mb['smtpAuth']     = $bbcfg['smtp.auth'];
    $mb['smtpUsername'] = (!empty($bbcfg['smtp.subuser'])) ? $bbcfg['smtp.subuser'] : '';
    require_once $appdir.'/modules/civicrm/CRM/Utils/Crypt.php';
    $mb['smtpPassword'] = CRM_Utils_Crypt::encrypt($bbcfg['smtp.subpass']);
    $rc &= updateMailingBackend($dbcon, $mb);
    $rc &= updateEmailMenu($dbcon);
  }

  if (isset($civicfg['from_name'])) {
    $rc &= updateFromEmail($dbcon, $bbcfg);
  }

  if (isset($civicfg['dirprefs'])) {
    $rc &= updateDirPref($dbcon, 'uploadDir', "upload/");
    $rc &= updateDirPref($dbcon, 'imageUploadDir', "images/");
    $rc &= updateDirPref($dbcon, 'customFileUploadDir', "custom/");
    $rc &= updateDirPref($dbcon, 'customTemplateDir', "$appdir/civicrm/custom/templates");
    $rc &= updateDirPref($dbcon, 'customPHPPathDir', "$appdir/civicrm/custom/php");
  }

  if (isset($civicfg['urlprefs'])) {
    $rc &= updateUrlPref($dbcon, 'userFrameworkResourceURL', "sites/all/modules/civicrm/");
    $rc &= updateUrlPref($dbcon, 'imageUploadURL', "sites/default/files/civicrm/images/");
  }

  if (isset($civicfg['template_header']) || isset($civicfg['template_footer'])) {
    $rc &= updateEmailTemplate($dbcon, $bbcfg);
  }

  return $rc;
} // updateCiviConfig()



function nullifyCiviConfig($dbcon, $civicfg)
{
  if (isset($civicfg['config_backend'])) {
    $rc = updateConfigBackend($dbcon, null);
  }
  if (isset($civicfg['mailing_backend'])) {
    $rc = updateMailingBackend($dbcon, null);
  }
  return true;
} // nullifyCiviConfig()



$prog = basename($argv[0]);

if ($argc != 4) {
  echo "Usage: $prog instance cmd scope\n";
  echo "   cmd can be: list, update, or nullify\n";
  echo " scope can be: default, cb, mb, tpl, or all\n";
  exit(1);
}
else {
  $instance = $argv[1];
  $cmd = $argv[2];
  $scope = $argv[3];

  $bootstrap = bootstrapScript($prog, $instance, DB_TYPE_CIVICRM);
  if ($bootstrap == null) {
    echo "$prog: Unable to bootstrap this script; exiting\n";
    exit(1);
  }

  $bbconfig = $bootstrap['bbconfig'];
  $dbcon = $bootstrap['dbcon'];

  $rc = 0;
  $civiConfig = getCiviConfig($dbcon, $scope);

  if ($civiConfig === false) {
    echo "$prog: Unable to get CiviCRM configuration.\n";
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
      if (nullifyCiviConfig($dbcon, $civiConfig) === false) {
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
