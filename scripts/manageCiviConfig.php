<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-11-23
// Revised: 2013-05-12
// Revised: 2013-07-29 - added list_all and update_all options, along with
//                       getMailingComponent() and updateEmailTemplate()
// Revised: 2013-07-30 - added "scope" parameter
// Revised: 2014-07-23 - migrated from PHP mysql interface to PDO
// Revised: 2015-01-20 - email footer can now have three office addresses
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



function getSettings($dbh, $group_name)
{
  $settings = array();
  $sql = "SELECT name, value FROM civicrm_setting ".
         "WHERE group_name = '$group_name'";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->errorInfo());
    return null;
  }

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['name']] = unserialize($row['value']);
  }
  $stmt = null;
  return $settings;
} // getSettings()



function getSetting($dbh, $group_name, $name)
{
  $sql = "SELECT value FROM civicrm_setting ".
         "WHERE group_name = '$group_name' and name = '$name'";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->errorInfo());
    return null;
  }

  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    return unserialize($row['value']);
  }
  else {
    return null;
  }
} // getSetting()



function getOptionValues($dbh, $group_name)
{
  $optValues = array();
  $sql = "SELECT name, value FROM civicrm_option_value ".
         "WHERE option_group_id IN ".
         "  ( SELECT id FROM civicrm_option_group ".
         "    WHERE name='$group_name' )";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->errorInfo());
    return null;
  }

  //get all rows
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $optValues[$row['name']] = $row['value'];
  }
  $stmt = null;
  return $optValues;
} // getOptionValues()



function getConfigBackend($dbh)
{
  $sql = "SELECT id, config_backend FROM civicrm_domain WHERE id=1";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->error_info());
    return false;
  }

  //get the only row
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row['config_backend']) {
    return unserialize($row['config_backend']);
  }
  else {
    return null;
  }
} // getConfigBackend()



function getMailingComponent($dbh, $id)
{
  $sql = "SELECT name, body_html, body_text ".
         "FROM civicrm_mailing_component WHERE id=$id";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->errorInfo());
    return false;
  }

  //get the only row
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  return $row;
} // getMailingComponent()



function getCiviConfig($dbh, $scope)
{
  $civiConfig = array();

  if ($scope == 'default' || $scope == 'cb' || $scope == 'all') {
    $civiConfig['config_backend'] = getConfigBackend($dbh);
  }

  if ($scope == 'default' || $scope == 'mb' || $scope == 'all') {
    $civiConfig['mailing_backend'] = getSetting($dbh, 'Mailing Preferences', 'mailing_backend');
    $civiConfig['from_name'] = getOptionValues($dbh, 'from_email_address');
  }

  if ($scope == 'default' || $scope == 'prefs' || $scope == 'all') {
    $civiConfig['dirprefs'] = getSettings($dbh, 'Directory Preferences');
    $civiConfig['urlprefs'] = getSettings($dbh, 'URL Preferences');
  }

  if ($scope == 'tpl' || $scope == 'all') {
    $civiConfig['template_header'] = getMailingComponent($dbh, 1);
    $civiConfig['template_footer'] = getMailingComponent($dbh, 2);
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



function updateSetting($dbh, $groupname, $optname, $optval)
{
  $sql = "UPDATE civicrm_setting SET value=".sqlPrepareValue($optval)." ".
         "WHERE name='$optname' AND group_name='$groupname'";
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  return true;
} // updateSetting()



function updateOptionValue($dbh, $groupname, $optname, $optval)
{
  $sql = "UPDATE civicrm_option_value SET value='$optval' ".
         "WHERE name='$optname' AND option_group_id=( ".
         "   SELECT id FROM civicrm_option_group ".
         "   WHERE name='$groupname' )";
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  return true;
} // updateOptionValue()



function updateDirPref($dbh, $optname, $optval)
{
  return updateSetting($dbh, 'Directory Preferences', $optname, $optval);
} // updateDirPref()



function updateUrlPref($dbh, $optname, $optval)
{
  return updateSetting($dbh, 'URL Preferences', $optname, $optval);
} // updateUrlPref()



// Confirm that all Mass Email menu items are active.
function updateEmailMenu($dbh)
{
  // Must perform two queries here, since a sub-select cannot be used
  // on the same table when performing an UPDATE.

  $sql = "SELECT id FROM civicrm_navigation WHERE name='Mass Email'";
  $stmt = $dbh->query($sql);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $pid = $row['id'];
  $stmt = null;

  $sql = "UPDATE civicrm_navigation SET is_active=1 WHERE parent_id=$pid";
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // updateEmailMenu()



function updateFromEmail($dbh, $bbcfg)
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
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} //updateFromEmail()



function updateConfigBackend($dbh, $bkend)
{
  $sql = "UPDATE civicrm_domain ".
         "SET config_backend=".sqlPrepareValue($bkend)." WHERE id=1";
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  return true;
} // updateConfigBackend()



function updateMailingBackend($dbh, $bknd)
{
  return updateSetting($dbh, 'Mailing Preferences', 'mailing_backend', $bknd);
} // updateMailingBackend()



function updateEmailTemplate($dbh, $bbcfg)
{
  $rc = true;
  $comp_id = 1;

  foreach (array('header', 'footer') as $comp_type) {
    $comp_type_uc = ucfirst($comp_type);
    $comp_name = "NYSS Mailing $comp_type_uc";
    $body = array();

    foreach (array('html', 'txt') as $cont_type) {
      $comp_tpl = generateComponent($comp_type, $cont_type, $bbcfg);
      $body[$cont_type] = $dbh->quote($comp_tpl);
    }

    $sql = "UPDATE civicrm_mailing_component ".
           "SET name='$comp_name', component_type='$comp_type_uc', ".
               "subject='$comp_name', is_active=1, ".
               "body_html={$body['html']}, body_text={$body['txt']} ".
           "WHERE id = $comp_id";
    if ($dbh->exec($sql) === false) {
      print_r($dbh->errorInfo());
      $rc = false;
    }
    $comp_id++;
  }
  return $rc;
} // updateEmailTemplate()



function setEmailDefaults(&$cfg)
{
  if (empty($cfg['email.font.family'])) {
    $cfg['email.font.family'] = 'arial';
  }
  if (empty($cfg['email.font.size'])) {
    $cfg['email.font.size'] = 14;
  }
  if (empty($cfg['email.font.color'])) {
    $cfg['email.font.color'] = '#505050';
  }
  if (empty($cfg['email.background.color'])) {
    $cfg['email.background.color'] = '#ffffff';
  }
  if (!isset($cfg['email.header.include_banner'])) {
    $cfg['email.header.include_banner'] = true;
  }
  if (!isset($cfg['email.footer.include_banner'])) {
    $cfg['email.footer.include_banner'] = true;
  }
  if (!isset($cfg['email.footer.include_addresses'])) {
    $cfg['email.footer.include_addresses'] = true;
  }
  if (!isset($cfg['senator.name.formal'])) {
    $cfg['senator.name.formal'] = 'New York State Senator';
  }
  if (!isset($cfg['senator.address.albany'])) {
    $cfg['senator.address.albany'] = 'Legislative Office Bldg|Albany, NY 12247';
  }
  if (!isset($cfg['senator.address.district'])) {
    $cfg['senator.address.district'] = 'ADDRESS OF DISTRICT OFFICE';
  }
  if (!isset($cfg['senator.address.satellite'])) {
    $cfg['senator.address.satellite'] = '';
  }
} // setEmailDefaults()



/*
** @param $comp_type - the component type (HEADER or FOOTER)
** @param $cont_type - the content type (HTML or TEXT)
** @param $cfg - array of config params that control template construction
*/
function generateComponent($comp_type, $cont_type, $cfg)
{
  $s = null;

  // Set default values for all e-mail template config.
  setEmailDefaults($cfg);

  // *** Header Template ***
  if ($comp_type == 'header') {
    if ($cont_type == 'html') {
      if ($cfg['email.header.include_banner']) {
        $banner = <<<HTML
    <tr>
    <td><a href="http://{$cfg['shortname']}.nysenate.gov/" target="_blank"><img src="http://{$cfg['servername']}/sites/{$cfg['servername']}/pubfiles/images/template/header.png" alt="{$cfg['senator.name.formal']}"/></a></td>
    </tr>
HTML;
      }
      else {
        $banner = '';
      }

      $s = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>{mailing.name}</title>
</head>
<body style="font-family:{$cfg['email.font.family']}; font-size:{$cfg['email.font.size']}px; color:{$cfg['email.font.color']}; background-color:{$cfg['email.background.color']};" leftmargin="0" topmargin="0" marginheight="0" marginwidth="0" offset="0">
<center>
<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
  <tr>
  <td align="center" valign="top">
  <table style="border:1px solid #DDDDDD;" cellpadding="0" cellspacing="0" width="600px">
$banner
    <tr>
    <td valign="top">
    <div style="padding:20px; text-align:left; line-height:150%;">
HTML;
    }
    else if ($cont_type == 'txt') {
      $s = <<<TEXT

New York State Senate
http://{$cfg['shortname']}.nysenate.gov/
TEXT;
    }
  }
  // *** Footer Template ***
  else if ($comp_type == 'footer') {
    $offices = array();
    $offices['Albany'] = $cfg['senator.address.albany'];
    $offices['District'] = $cfg['senator.address.district'];
    if ($cfg['senator.address.satellite']) {
      $offices['Satellite'] = $cfg['senator.address.satellite'];
    }

    if ($cont_type == 'html') {
      if ($cfg['email.footer.include_addresses']) {
        $width = round(100 / count($offices));
        $addresses = <<<HTML
    <tr>
    <td align="center" valign="top">	
    <table style="color:#707070; font-size:12px; line-height:125%;" border="0" cellpadding="20px" cellspacing="0" width="100%">
      <tr>

HTML;

        foreach ($offices as $office_type => $office_address) {
          $office_html = str_replace("|", "\n<br/>", $office_address);
          $addresses .= <<<HTML
      <td valign="top" width="{$width}%"><strong>$office_type Office:</strong>
      <br/>$office_html
      </td>

HTML;
        }

        $addresses .= <<<HTML
      </tr>
    </table>
    </td>
    </tr>
HTML;
      }
      else {
        $addresses = '';
      }

      if ($cfg['email.footer.include_banner']) {
        $banner = <<<HTML
    <tr style="background-color:#D8E2EA;">
    <td><a href="http://www.nysenate.gov/" target="_blank"><img src="http://{$cfg['servername']}/sites/{$cfg['servername']}/pubfiles/images/template/footer.png" alt="New York State Senate seal"/></a></td>
    </tr>
HTML;
      }
      else {
        $banner = '';
      }

      $s = <<<HTML
    </div>
    </td>
    </tr>
$addresses
$banner
  </table>
  </td>
  </tr>
</table>
</center>
</body>
</html>
HTML;
    }
    else if ($cont_type == 'txt') {
      $addresses = '';
      if ($cfg['email.footer.include_addresses']) {
        foreach ($offices as $office_type => $office_address) {
          $office_txt = str_replace("|", "\n", $office_address);
          $addresses .= "$office_type Office:\n$office_txt\n\n";
        }
      }

      $s = <<<TEXT

---
http://www.nysenate.gov

$addresses
TEXT;
    }
  }

  return $s;
} // generateComponent()



function updateCiviConfig($dbh, $civicfg, $bbcfg)
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
    $rc &= updateConfigBackend($dbh, $cb);
  }

  if (isset($civicfg['mailing_backend'])) {
    $mb = $civicfg['mailing_backend'];
    $mb['smtpServer']   = $bbcfg['smtp.host'];
    $mb['smtpPort']     = $bbcfg['smtp.port'];
    $mb['smtpAuth']     = $bbcfg['smtp.auth'];
    $mb['smtpUsername'] = (!empty($bbcfg['smtp.subuser'])) ? $bbcfg['smtp.subuser'] : '';
    require_once $appdir.'/modules/civicrm/CRM/Utils/Crypt.php';
    $mb['smtpPassword'] = CRM_Utils_Crypt::encrypt($bbcfg['smtp.subpass']);
    $rc &= updateMailingBackend($dbh, $mb);
    $rc &= updateEmailMenu($dbh);
  }

  if (isset($civicfg['from_name'])) {
    $rc &= updateFromEmail($dbh, $bbcfg);
  }

  if (isset($civicfg['dirprefs'])) {
    $rc &= updateDirPref($dbh, 'uploadDir', "upload/");
    $rc &= updateDirPref($dbh, 'imageUploadDir', "images/");
    $rc &= updateDirPref($dbh, 'customFileUploadDir', "custom/");
    $rc &= updateDirPref($dbh, 'customTemplateDir', "$appdir/civicrm/custom/templates");
    $rc &= updateDirPref($dbh, 'customPHPPathDir', "$appdir/civicrm/custom/php");
  }

  if (isset($civicfg['urlprefs'])) {
    $rc &= updateUrlPref($dbh, 'userFrameworkResourceURL', "sites/all/modules/civicrm/");
    $rc &= updateUrlPref($dbh, 'imageUploadURL', "sites/default/files/civicrm/images/");
  }

  if (isset($civicfg['template_header']) || isset($civicfg['template_footer'])) {
    $rc &= updateEmailTemplate($dbh, $bbcfg);
  }

  return $rc;
} // updateCiviConfig()



function nullifyCiviConfig($dbh, $civicfg)
{
  if (isset($civicfg['config_backend'])) {
    $rc = updateConfigBackend($dbh, null);
  }
  if (isset($civicfg['mailing_backend'])) {
    $rc = updateMailingBackend($dbh, null);
  }
  return true;
} // nullifyCiviConfig()



$prog = basename($argv[0]);

if ($argc != 4) {
  echo "Usage: $prog instance cmd scope\n";
  echo "   cmd can be: list, update, preview, or nullify\n";
  echo " scope can be: default, cb, mb, tpl, or all\n";
  exit(1);
}
else {
  $instance = $argv[1];
  $cmd = $argv[2];
  $scope = $argv[3];

  $bootstrap = bootstrap_script($prog, $instance, DB_TYPE_CIVICRM);
  if ($bootstrap == null) {
    echo "$prog: Unable to bootstrap this script; exiting\n";
    exit(1);
  }

  $bbconfig = $bootstrap['bbconfig'];
  $dbh = $bootstrap['dbh'];

  $rc = 0;
  $civiConfig = getCiviConfig($dbh, $scope);

  if ($civiConfig === false) {
    echo "$prog: Unable to get CiviCRM configuration.\n";
    $rc = 1;
  }
  else if (is_array($civiConfig)) {
    if ($cmd == 'update') {
      echo "Updating the CiviCRM configuration.\n";
      if (updateCiviConfig($dbh, $civiConfig, $bbconfig) === false) {
        $rc = 1;
      }
    }
    else if ($cmd == 'preview') {
      echo "Previewing e-mail template components.\n";
      foreach (array('header','footer') as $comp_type) {
        foreach (array('html','txt') as $cont_type) {
          $s = generateComponent($comp_type, $cont_type, $bbconfig);
          echo "Template preview for [$comp_type/$cont_type]:\n$s\n\n";
        }
      }
    }
    else if ($cmd == 'nullify') {
      echo "Nullifying the CiviCRM configuration.\n";
      if (nullifyCiviConfig($dbh, $civiConfig) === false) {
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

  $dbh = null;
  exit($rc);
}
