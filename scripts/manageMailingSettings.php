<?php
// Project: BluebirdCRM
// Authors: Ken Zalewski, Brian Shaughnessy
// Organization: New York State Senate
// Date: 2011-06-20
// Revised: 2012-01-18
//


function getConfigBackend($dbcon, $table, $field)
{
  $sql = "SELECT id, $field FROM $table WHERE id = 1;";
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
  return $civiConfig;
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



function updateMailingBackend($dbcon, $civiConfig, $appdir,
                              $smtpHost, $smtpPort, $smtpAuth,
                              $smtpSubuser, $smtpSubpass)
{
  $rc = 0;

  $mb = $civiConfig['mailing_backend'];
  
  //TODO we need to retrieve this from the settings file or instantiate civi
  if ( !defined('CIVICRM_SITE_KEY') ) {
    define('CIVICRM_SITE_KEY', '32425kj24h5kjh24542kjh524');
  }
  
  //set values
  require_once $appdir.'/modules/civicrm/CRM/Utils/Crypt.php';
  $mb['smtpServer']   = $smtpHost;
  $mb['smtpPort']     = $smtpPort;
  $mb['smtpAuth']     = $smtpAuth;
  $mb['smtpUsername'] = $smtpSubuser;
  $mb['smtpPassword'] = CRM_Utils_Crypt::encrypt($smtpSubpass);

  $sql = "UPDATE civicrm_preferences SET mailing_backend='".serialize($mb)."' WHERE id=1;";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    $rc = 1;
  }
  
  //enable civimail component and set mailer/job size
  $cb = $civiConfig['config_backend'];
  $cb['enableComponents'][]   = 'CiviMail';
  $cb['enableComponentIDs'][] = 4;
  $cb['mailerBatchLimit']     = 1000;
  $cb['mailerJobSize']        = 1000;
  $cb['mailerJobsMax']        = 10;
  
  $sql = "UPDATE civicrm_domain SET config_backend='".serialize($cb)."' WHERE id=1;";
  if ( !mysql_query($sql, $dbcon) ) {
    echo mysql_error($dbcon)."\n";
    $rc = 1;
  }
  
  return $rc;
} //updateMailingBackend()


function updateEmailReports( $dbcon ) {

  //enable civimail report menu items
  $sql = "UPDATE civicrm_navigation SET is_active = 1 WHERE id IN (240, 241, 242, 243)";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return 1;
  }
  else {
    return 0;
  }
} //updateMailingBackend()


function updateFromEmail( $dbcon, $emailFrom, $fromName, $smtpSubuser ) {

  //update the FROM email address
  if (!$emailFrom) $emailFrom = $smtpSubuser;
  $from = '"'.addslashes($fromName).'"'." <$emailFrom>";
  $sql = "UPDATE civicrm_option_value SET label = '{$from}', name = '{$from}' WHERE option_group_id = 30";
  if (!mysql_query($sql , $dbcon)) {
    echo mysql_error($dbcon)."\n";
    return 1;
  }
  else {
    return 0;
  }
} //updateFromEmail()


function cacheCleanup( $dbcon ) {

  //cache cleanup
  $cache_menu  = mysql_query( "TRUNCATE TABLE civicrm_menu", $dbcon );
  $cache_cache = mysql_query( "TRUNCATE TABLE civicrm_cache", $dbcon );
  $cache_nav   = mysql_query( "UPDATE civicrm_preferences SET navigation = null", $dbcon );

} //cacheCleanup()


function setHeaderFooter( $dbcon, $crmhost, $instance, $fromName )
{
  $headerhtml =
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
'<html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /><meta property="og:title" content="*|MC:{mailing.name}|*" /><title>{mailing.name}</title><style type="text/css">#outlook a{padding:0;}body{width:100% !important;}body{-webkit-text-size-adjust:none;}body{margin:0;padding:0;}img{border:none;font-size:14px;font-weight:bold;height:auto;line-height:100%;outline:none;text-decoration:none;text-transform:capitalize;}#backgroundTable{height:100% !important;margin:0;padding:0;width:100% !important;}body, #backgroundTable{background-color:#FAFAFA;}#templateContainer{border: 1px solid #DDDDDD;}h1, .h1{color:#202020;display:block;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h2, .h2{color:#202020;display:block;font-family:Arial;font-size:30px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h3, .h3{color:#202020;display:block;font-family:Arial;font-size:26px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h4, .h4{color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}#templatePreheader{background-color:#FAFAFA;}.preheaderContent div{color:#505050;font-family:Arial;font-size:10px;line-height:100%;text-align:left;}.preheaderContent div a:link, .preheaderContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#templateHeader{background-color:#D8E2EA;border-bottom:0;}.headerContent{color:#202020;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;padding:0;text-align:center;vertical-align:middle;}.headerContent a:link, .headerContent a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#headerImage{height:auto;max-width:600px !important;}#templateContainer, .bodyContent{background-color:#FDFDFD;}.bodyContent div{color:#505050;font-family:Arial;font-size:14px;line-height:150%;text-align:left;}.bodyContent div a:link, .bodyContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.bodyContent img{display:inline;height:auto;}#templateFooter{background-color:#FDFDFD;border-top:0;}.footerContent div{color:#707070;font-family:Arial;font-size:12px;line-height:125%;text-align:left;}.footerContent div a:link, .footerContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.footerContent img{display:inline;}#social{background-color:#FAFAFA;border:0;}#social div{text-align:center;}#utility{background-color:#FDFDFD;border:0;}#utility div{text-align:center;}#monkeyRewards img{max-width:190px;}</style></head>'.
'<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"><center><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><a href="http://'.$instance.'.nysenate.gov" target=_blank><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/template/header.png" alt="'.addslashes($fromName).'" style="max-width:600px;" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></a></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody"><tr><td valign="top" class="bodyContent"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tr><td valign="top"><div mc:edit="std_content00">';
  $headertext = 'New York State Senate
http://'.$instance.'.nysenate.gov

';
  $headername = 'NYSS Mailing Header';
  
  $footerhtml = '</div></td></tr></table></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="10" cellspacing="0" width="600" id="templateFooter"><tr><td valign="top" class="footerContent">
	
<table border="0" cellpadding="10" cellspacing="0" width="100%">
<tr><td valign="top" width="50%"><div mc:edit="std_footer"><strong>Albany Address:</strong><br />ADDRESS</div></td><td valign="top" width="50%"><div mc:edit="std_footer"><strong>District Address:</strong><br />ADDRESS</div></td></tr>
</table>
	
	</td></tr></table><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><a href="http://www.nysenate.gov" target=_blank><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/template/footer.png" style="max-width:600px;" alt="New York State Senate Seal" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></a></td></tr></table></td></tr></table><br /></td></tr></table></center></body></html>';
  $footertext = 'http://www.nysenate.gov

';
  $footername = 'NYSS Mailing Footer';
  
  $sql1 = "UPDATE civicrm_mailing_component SET name = '$headername', subject = '$headername', body_html = '$headerhtml', body_text = '$headertext' WHERE id = 1;";
  $sql2 = "UPDATE civicrm_mailing_component SET name = '$footername', subject = '$footername', body_html = '$footerhtml', body_text = '$footertext' WHERE id = 2;";
  
  //echo "\n\n".$sql1."\n\n";
  //echo "\n\n".$sql2."\n\n";
  
  if ( !mysql_query($sql1, $dbcon) || !mysql_query($sql2, $dbcon) ) {
    echo mysql_error($dbcon)."\n";
    return 1;
  }
  else {
    return 0;
  }
} // setHeaderFooter()



//run script
$prog = basename($argv[0]);

if ($argc != 15 && $argc != 17) {
  echo "Usage: $prog cmd dbhost dbuser dbpass dbname smtphost smtpport smtpauth smtpsubuser smtpsubpass instance fromName [crmhost] [appdir]\n";
  echo "   cmd can be: list, update-config, update-template, set-apps, update-all, update-from, update-reports\n";
  exit(1);
}
else {
  $cmd = $argv[1];
  $dbhost = $argv[2];
  $dbuser = $argv[3];
  $dbpass = $argv[4];
  $dbname = $argv[5];
  $smtpHost = $argv[6];
  $smtpPort = $argv[7];
  $smtpAuth = $argv[8];
  $smtpSubuser = $argv[9];
  $smtpSubpass = $argv[10];
  $instance = $argv[11];
  $fromName = $argv[12];
  $emailFrom = $argv[13];
  $emailReplyto = $argv[14];
  $crmhost = ($argc == 17) ? $argv[15] : "";
  $appdir = ($argc == 17) ? $argv[16] : "";

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
    echo "$prog: Unable to get CiviCRM configuration.\n";
    $rc = 1;
  }
  else if (is_array($civiConfig['mailing_backend'])) {
    switch ($cmd) {
    case 'update-config':
      echo "Updating the CiviCRM mailing configuration.\n";
      $rc = updateMailingBackend($dbcon, $civiConfig, $appdir, $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass);
      break;
    case 'update-template':
      echo "Resetting the header and footer to default values.\n";
      $rc = setHeaderFooter($dbcon, $crmhost, $instance, $fromName);
    case 'update-from':
      echo "Setting FROM email address.\n";
      $rc = updateFromEmail($dbcon, $emailFrom, $fromName, $smtpSubuser);
      break;
    case 'update-reports':
      echo "Enabling mailing reports.\n";
      $rc = updateEmailReports($dbcon);
      break;
    case 'update-all':
      echo "1. Updating the CiviCRM mailing configuration.\n";
      $rc = updateMailingBackend($dbcon, $civiConfig, $appdir, $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass);
      echo "2. Resetting the header and footer to default values.\n";
      $rc += setHeaderFooter($dbcon, $crmhost, $instance, $fromName);
      echo "3. Setting FROM email address.\n";
      $rc += updateFromEmail($dbcon, $emailFrom, $fromName, $smtpSubuser);
      echo "4. Enabling mailing reports.\n";
      $rc += updateEmailReports($dbcon);
    default:
      listCiviConfig($civiConfig);
    }
  } else {
    echo "$prog: CiviCRM mailing configuration is empty.\n";
  }
  
  echo "Clearing various caches gracefully (no session logout).\n";
  cacheCleanup($dbcon);

  mysql_close($dbcon);
  exit($rc);
}
