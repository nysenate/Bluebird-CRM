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
// Revised: 2015-07-22 - added email.header.website_url
// Revised: 2019-03-14 - remove parameters that are now set in civicrm.settings
//

require_once 'common_funcs.php';

define('SERIALIZED_FALSE', serialize(false));


function sqlPrepareValue($val)
{
  if ($val !== null) {
    return "'".serialize($val)."'";
  }
  else {
    return 'NULL';
  }
} // sqlPrepareValue()



/**
 * @param $dbh reference to database connection
 * @param $tablename name of table to query using the "name" field
 * @param $names zero or more values to match against the "name" field
 *               If $names is a string, the query will match on a single
 *               value.  If $names is an array, the query will match based
 *               on any of the values in the array.
**/
function getSerializedValues($dbh, $tablename, $names = null)
{
  $settings = array();

  if (empty($tablename)) {
    fwrite(STDERR, "ERROR: Must provide a table name\n");
    return null;
  }

  if (is_array($names) && count($names) > 0) {
    $where = "WHERE name IN ('".implode("','", $names)."')";
  }
  else if ($names) {
    $where = "WHERE name = '$names'";
  }
  else {
    $where = '';
  }

  $sql = "SELECT name, value FROM $tablename $where";
  $stmt = $dbh->query($sql);
  if (!$stmt) {
    print_r($dbh->errorInfo());
    return null;
  }

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $name = $row['name'];
    $val = $row['value'];
    if ($val) {
      $val = unserialize($val);
    }
    // Must check for a serialized "false", since the unserialize() function
    // will return "false" if it fails or if the value is truly "false".
    if ($val !== false || $row['value'] === SERIALIZED_FALSE) {
      $settings[$name] = $val;
    }
    else {
      fwrite(STDERR, "ERROR: Unable to unserialize value for [$name]\n");
    }
  }
  $stmt = null;
  return $settings;
} // getSerializedValues()



// Get name/value settings from the CiviCRM "civicrm_setting" table.
// $name can be a single variable name or an array of names
function getSettings($dbh, $names = null)
{
  return getSerializedValues($dbh, 'civicrm_setting', $names);
} // getSettings()



// Get variable/value settings from the Drupal "variable" table.
// $name can be a single variable name or an array of names
function getVariableValues($dbh, $names = null)
{
  return getSerializedValues($dbh, 'variable', $names);
} // getVariableValues()



// Get name/value settings from the CiviCRM "option_value" table for the
// provided option-value group.  If a value is given, retrieve only those
// entries that match the value.  If a limit is given, limit the results.
function getOptionValues($dbh, $group_name, $val = null, $limit = 0)
{
  $optValues = array();
  $where_extra = '';

  if ($val) {
    $where_extra .= " AND value='$val'";
  }

  if ($limit > 0) {
    $where_extra .= " LIMIT $limit";
  }

  $sql = "SELECT name, value FROM civicrm_option_value ".
         "WHERE option_group_id IN ".
         "  ( SELECT id FROM civicrm_option_group ".
         "    WHERE name='$group_name' ) $where_extra";
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



function getFromEmail($dbh)
{
  $optval = getOptionValues($dbh, 'from_email_address', "1", 1);
  return key($optval);
} // getFromEmail()



function getMailingComponent($dbh, $id)
{
  $sql = "SELECT id, name, body_html, body_text ".
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



function getConfig($dbrefs, $scope)
{
  $cividb = $dbrefs[DB_TYPE_CIVICRM];
  $drupdb = $dbrefs[DB_TYPE_DRUPAL];
  $cfg = array();

  if ($scope == 'def' || $scope == 'all') {
    $cfg['civicrm']['settings'] = getSettings($cividb);
    $cfg['civicrm']['from_email'] = getFromEmail($cividb);
  }

  if ($scope == 'tpl' || $scope == 'all') {
    $cfg['civicrm']['template_header'] = getMailingComponent($cividb, 1);
    $cfg['civicrm']['template_footer'] = getMailingComponent($cividb, 2);
  }

  if ($scope == 'def' || $scope == 'drup' || $scope == 'all') {
    $cfg['drupal']['variables'] = getVariableValues($drupdb, 'file_public_path');
  }

  return $cfg;
} // getConfig()



function listConfig($cfg)
{
  foreach ($cfg as $cfggrp => $cfglist) {
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
} // listConfig()



/*
** @param $comp_type - the component type (HEADER or FOOTER)
** @param $cont_type - the content type (HTML or TEXT)
** @param $cfg - array of config params that control template construction
*/
function generateComponent($comp_type, $cont_type, $cfg)
{
  $s = null;

  // *** Header Template ***
  if ($comp_type == 'header') {
    if ($cont_type == 'html') {
      if ($cfg['email.header.include_banner']) {
        $banner = <<<HTML
    <tr>
    <td><a href="{$cfg['email.header.website_url']}" target="_blank"><img src="http://{$cfg['servername']}/data/{$cfg['shortname']}/pubfiles/images/template/header.png" alt="{$cfg['senator.name.formal']}"/></a></td>
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
    else if ($cont_type == 'text') {
      $s = <<<TEXT

New York State Senate
{$cfg['email.header.website_url']}
TEXT;
    }
  }
  // *** Footer Template ***
  else if ($comp_type == 'footer') {
    $offices = array();
    foreach (['Albany', 'District', 'Satellite', 'Satellite2'] as $offtype) {
      $cfgparam = 'senator.address.' . strtolower($offtype);
      if ($cfg[$cfgparam]) {
        $offices[$offtype] = $cfg[$cfgparam];
      }
    }

    if ($cont_type == 'html') {
      $num_offices = count($offices);
      if ($cfg['email.footer.include_addresses'] && $num_offices > 0) {
        $width = round(100 / $num_offices);
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
    <td><a href="http://www.nysenate.gov/" target="_blank"><img src="http://{$cfg['servername']}/data/{$cfg['shortname']}/pubfiles/images/template/footer.png" alt="New York State Senate seal"/></a></td>
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
    else if ($cont_type == 'text') {
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



// Modify a config setting in-memory.
function modifyParam(&$params, $name, $val)
{
  if (array_key_exists($name, $params)) {
    $params[$name] = $val;
    return true;
  }
  else {
    echo "ERROR: Param [$name] does not exist and cannot be modified\n";
    return false;
  }
} // modifyParam()



// Modify the entire configuration in-memory.
function modifyConfig(&$cfg, $bbcfg)
{
  $data_dirname = $bbcfg['data_dirname'];

  if (isset($cfg['civicrm']['settings'])) {
    $cs = &$cfg['civicrm']['settings'];
    // All settings are now being handled in civicrm.settings.php.
    // As a result, there is nothing to store in the civicrm_setting table.
//    modifyParam($cs, 'uploadDir', "upload/");
//    modifyParam($cs, 'customFileUploadDir', "custom/");
//    modifyParam($cs, 'imageUploadURL', "data/$data_dirname/pubfiles");
  }

  if (isset($cfg['civicrm']['from_email'])) {
    // Update the FROM name and email address
    $fromName = (!empty($bbcfg['senator.name.formal'])) ? $bbcfg['senator.name.formal'] : 'Bluebird Mail Sender';

    if (isset($bbcfg['senator.email'])) {
      $fromEmail = $bbcfg['senator.email'];
    }
    else {
      $fromEmail = (!empty($bbcfg['smtp.username'])) ? $bbcfg['smtp.username'] : 'bluebird.admin@nysenate.gov';
    }

    $from = '"'.addslashes($fromName).'"'." <$fromEmail>";
    modifyParam($cfg['civicrm'], 'from_email', $from);
  }

  if (isset($cfg['civicrm']['template_header'])) {
    $tpl['id'] = 1;
    $tpl['name'] = 'NYSS Mailing Header';
    $tpl['body_html'] = generateComponent('header', 'html', $bbcfg);
    $tpl['body_text'] = generateComponent('header', 'text', $bbcfg);
    modifyParam($cfg['civicrm'], 'template_header', $tpl);
  }

  if (isset($cfg['civicrm']['template_footer'])) {
    $tpl['id'] = 2;
    $tpl['name'] = 'NYSS Mailing Footer';
    $tpl['body_html'] = generateComponent('footer', 'html', $bbcfg);
    $tpl['body_text'] = generateComponent('footer', 'text', $bbcfg);
    modifyParam($cfg['civicrm'], 'template_footer', $tpl);
  }

  if (isset($cfg['drupal']['variables'])) {
    $dv = &$cfg['drupal']['variables'];
    modifyParam($dv, 'file_public_path', "data/$data_dirname/drupal");
  }
} // modifyConfig()



function clearSettings($dbh, $scope)
{
  // $setlist is a list of settings that are already configured in the
  // civicrm.settings.php file, and should not be present in the settings table.

  $setlist = [
    // Core settings
    'advanced_search_options', 'checksum_timeout', 'communityMessagesUrl',
    'contact_autocomplete_options', 'contact_reference_options', 'empoweredBy',
    'enable_components', 'max_attachments', 'maxFileSize',
    'recentItemsMaxCount', 'remote_profile_submissions', 'securityAlert',
    'smart_group_cache_refresh_mode', 'syncCMSEmail', 'wkhtmltopdfPath',

    // Case settings
    'civicaseActivityRevisions',

    // Dir settings
    'uploadDir', 'imageUploadDir', 'customFileUploadDir',
    'customTemplateDir', 'customPHPPathDir', 'extensionsDir',

    // Extension settings
    'ext_repo_url',

    // Mailing settings
    'profile_double_optin', 'track_civimail_replies', 'civimail_workflow',
    'civimail_server_wide_lock', 'mailing_backend',
    'profile_add_to_group_double_optin', 'disable_mandatory_tokens_check',
    'hash_mailing_url', 'civimail_multiple_bulk_emails', 'include_message_id',
    'mailerBatchLimit', 'mailerJobSize', 'mailerJobsMax',
    'write_activity_record', 'auto_recipient_rebuild',

    // Map settings
    'geoProvider', 'geoAPIKey', 'mapProvider', 'mapAPIKey',

    // Search settings
    'search_autocomplete_count', 'enable_innodb_fts', 'fts_query_mode',
    'includeWildCardInName', 'includeEmailInName', 'smartGroupCacheTimeout',
    'searchPrimaryDetailsOnly',

    // URL settings
    'userFrameworkResourceURL', 'imageUploadURL', 'extensionsURL',
  ];

  if ($scope == 'all') {
    $sql = 'DELETE FROM civicrm_setting';
  }
  else {
    $setlist_str = "'" . implode("','", $setlist) . "'";
    $sql = "DELETE FROM civicrm_setting WHERE name IN ( $setlist_str )";
  }

  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // clearSettings()



function updateSerializedValues($dbh, $tablename, $namevalmap)
{
  $rc = true;
  foreach ($namevalmap as $name => $val) {
    $sql = "UPDATE $tablename SET value=".sqlPrepareValue($val)." ".
           "WHERE name='$name'";
    if ($dbh->exec($sql) === false) {
      print_r($dbh->errorInfo());
      $rc = false;
    }
  }
  return $rc;
} // updateSerializedValues()



function updateSettings($dbh, $settings)
{
  return updateSerializedValues($dbh, 'civicrm_setting', $settings);
} // updateSettings()



function updateVariableValues($dbh, $varvals)
{
  return updateSerializedValues($dbh, 'variable', $varvals);
} // updateVariableValues()



// Confirm that all Mass Email menu items are active.
// [This function is a candidate for removal.]
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



function updateFromEmail($dbh, $from)
{
  $sql = "UPDATE civicrm_option_value SET label='$from', name='$from' ".
         "WHERE value='1' AND option_group_id=(".
                  "SELECT id FROM civicrm_option_group ".
                  "WHERE name='from_email_address')";
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  else {
    return true;
  }
} // updateFromEmail()



function updateMailingComponent($dbh, $tpl)
{
  $id = $tpl['id'];
  $name = $tpl['name'];
  $bh = $dbh->quote($tpl['body_html']);
  $bt = $dbh->quote($tpl['body_text']);

  $sql = "UPDATE civicrm_mailing_component ".
         "SET name='$name', subject='$name', body_html=$bh, body_text=$bt ".
         "WHERE id = $id";
  if ($dbh->exec($sql) === false) {
    print_r($dbh->errorInfo());
    return false;
  }
  return true;
} // updateMailingComponent()



function updateConfig($dbrefs, $cfg)
{
  $rc = true;
  $cividb = $dbrefs[DB_TYPE_CIVICRM];
  $drupdb = $dbrefs[DB_TYPE_DRUPAL];

  if (isset($cfg['civicrm']['settings'])) {
    echo "Updating CiviCRM settings\n";
    $rc &= updateSettings($cividb, $cfg['civicrm']['settings']);
  }

  if (isset($cfg['civicrm']['from_email'])) {
    echo "Updating CiviCRM from_email_address\n";
    $rc &= updateFromEmail($cividb, $cfg['civicrm']['from_email']);
  }

  if (isset($cfg['civicrm']['template_header'])) {
    echo "Updating CiviCRM header template\n";
    $rc &= updateMailingComponent($cividb, $cfg['civicrm']['template_header']);
  }

  if (isset($cfg['civicrm']['template_footer'])) {
    echo "Updating CiviCRM footer template\n";
    $rc &= updateMailingComponent($cividb, $cfg['civicrm']['template_footer']);
  }

  if (isset($cfg['drupal']['variables'])) {
    echo "Updating Drupal file_public_path\n";
    $rc &= updateVariableValues($drupdb, $cfg['drupal']['variables']);
  }

  return $rc;
} // updateConfig()



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
  if (!isset($cfg['email.header.website_url'])) {
    $cfg['email.header.website_url'] = "http://{$cfg['shortname']}.nysenate.gov/";
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
    $cfg['senator.address.district'] = 'COMING SOON';
  }
  if (!isset($cfg['senator.address.satellite'])) {
    $cfg['senator.address.satellite'] = '';
  }
  if (!isset($cfg['senator.address.satellite2'])) {
    $cfg['senator.address.satellite2'] = '';
  }
} // setEmailDefaults()



function _getval($a, $idx, $def)
{
  if (array_key_exists($idx, $a)) {
    return $a[$idx];
  }
  else {
    echo "Parameter '$idx' is not set in config; using default value [$def]\n";
    return $def;
  }
} // _getval()



$prog = basename($argv[0]);

if ($argc != 4) {
  echo "Usage: $prog instance cmd scope\n";
  echo "   cmd can be: list, preview, update, or clear\n";
  echo " scope can be: def, tpl, drup, or all\n";
  exit(1);
}
else {
  $instance = $argv[1];
  $cmd = $argv[2];
  $scope = $argv[3];

  $dbtypes = array(DB_TYPE_CIVICRM, DB_TYPE_DRUPAL);
  $bootstrap = bootstrap_script($prog, $instance, $dbtypes);
  if ($bootstrap == null) {
    echo "$prog: Unable to bootstrap this script; exiting\n";
    exit(1);
  }

  $bbconfig = $bootstrap['bbconfig'];
  $dbrefs = $bootstrap['dbrefs'];
  // Set default values for all e-mail template config.
  setEmailDefaults($bbconfig);

  $rc = 0;
  $config = getConfig($dbrefs, $scope);

  if ($config === false) {
    echo "$prog: Unable to get CiviCRM/Drupal configuration.\n";
    $rc = 1;
  }
  else if ($cmd == 'clear') {
    if ($scope == 'all') {
      echo "Removing ALL settings from the civicrm_setting table\n";
    }
    else {
      echo "Removing settings that are configured via civicrm.settings.php\n";
    }
    if (clearSettings($dbrefs[DB_TYPE_CIVICRM], $scope) === false) {
      $rc = 1;
    }
  }
  else if (is_array($config) && $config) {
    if ($cmd == 'update' || $cmd == 'preview') {
      modifyConfig($config, $bbconfig);
      if ($cmd == 'preview') {
        echo "Previewing the CiviCRM/Drupal configuration.\n";
        listConfig($config);
      }
      else {
        echo "Updating the CiviCRM/Drupal configuration.\n";
        if (updateConfig($dbrefs, $config) === false) {
          $rc = 1;
        }
      }
    }
    else {
      listConfig($config);
    }
  }
  else {
    echo "$prog: CiviCRM configuration is empty.\n";
  }

  foreach ($dbtypes as $dbtype) {
    $dbrefs[$dbtype] = null;
  }
  exit($rc);
}
