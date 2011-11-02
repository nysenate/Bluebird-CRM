<?php
// Project: BluebirdCRM
// Author: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2011-06-20
//

function getMailingBackend( $dbcon )
{
  $civiMailing = array();
  $sql = "SELECT id, mailing_backend FROM civicrm_preferences WHERE id = 1;";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  //get the only row
  $row = mysql_fetch_assoc($result);
  if ($row['mailing_backend']) {
    $civiMailing['backend'] = unserialize($row['mailing_backend']);
  }
  else {
    $civiMailing['backend'] = null;
  }

  return $civiMailing;
} // getMailingBackend()



function getConfigBackend( $dbcon )
{
  $civiConfig = array();
  $sql = "SELECT id, config_backend FROM civicrm_domain WHERE id = 1;";
  $result = mysql_query($sql, $dbcon);
  if (!$result) {
    echo mysql_error($dbcon)."\n";
    return false;
  }

  //get the only row
  $row = mysql_fetch_assoc($result);
  if ($row['config_backend']) {
    $civiConfig['backend'] = unserialize($row['config_backend']);
  }
  else {
    $civiConfig['backend'] = null;
  }

  return $civiConfig;
} // getConfigBackend()



function listMailingBackend( $civiMailing )
{
  foreach ($civiMailing as $mailgrp => $maillist) {
    echo "\n==> Config group: $mailgrp\n";
    foreach ($maillist as $key => $val) {
      if (is_string($val)) {
        echo "[$key] => [$val]\n";
      }
      else {
        echo "[$key] => ";
        print_r($val);
      }
    }
  }
} // listMailingBackend()



function updateMailingBackend($dbcon, $civiMailing, $civiConfig, $crmhost,
                              $appdir, $datadir, $smtpHost, $smtpPort,
                              $smtpAuth, $smtpSubuser, $smtpSubpass,
                              $instance, $fromName, $emailFrom)
{
  $rc = true;
  //print_r($civiMailing);

  $mb = $civiMailing['backend'];
  
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
  //print_r($mb);

  $sql = "UPDATE civicrm_preferences SET mailing_backend='".serialize($mb)."' WHERE id=1;";
  if (!mysql_query($sql, $dbcon)) {
    echo mysql_error($dbcon)."\n";
    $rc = false;
  }
  
  //enable civimail component and set mailer/job size
  $cb = $civiConfig['backend'];
  $cb['enableComponents'][]   = 'CiviMail';
  $cb['enableComponentIDs'][] = 4;
  $cb['mailerBatchLimit']     = 2500;
  $cb['mailerJobSize']        = 2500;
  $cb['mailerJobsMax']        = 2;
  
  $sql = "UPDATE civicrm_domain SET config_backend='".serialize($cb)."' WHERE id=1;";
  if ( !mysql_query($sql, $dbcon) ) {
    echo mysql_error($dbcon)."\n";
    $rc = false;
  }
  
  return $rc;
} //updateMailingBackend()

function updateEmailReports( $dbcon ) {

  //enable civimail report menu items
  $sql = "UPDATE civicrm_navigation SET is_active = 1 WHERE id IN (240, 241, 242, 243)";
  $nav = mysql_query( $sql, $dbcon );

} //updateMailingBackend()

function updateFromEmail( $dbcon, $emailFrom, $fromName, $smtpSubuser ) {

  //update the FROM email address
  if ( !$emailFrom ) $emailFrom = $smtpSubuser;
  $from = "\"$fromName\" <$emailFrom>";
  $sql = "UPDATE civicrm_option_value SET label = '{$from}', name = '{$from}' WHERE option_group_id = 30";
  $from_set   = mysql_query( $sql , $dbcon );

} //updateFromEmail()

function cacheCleanup( $dbcon ) {

  //cache cleanup
  $cache_menu  = mysql_query( "TRUNCATE TABLE civicrm_menu", $dbcon );
  $cache_cache = mysql_query( "TRUNCATE TABLE civicrm_cache", $dbcon );
  $cache_nav   = mysql_query( "UPDATE civicrm_preferences SET navigation = null", $dbcon );

} //cacheCleanup()

/*
 * We use sendgrid subuser accounts for each district.
 * Enable subscription, click, and open tracking apps
 */
function setSendgridApps ( $smtpUser, $smtpPass, $smtpSubuser, $smtpSubpass )
{
  //enable apps
  $apps = array ( 'opentrack', 'clicktrack' );
  foreach ( $apps as $app ) {
  	//uncomment to print existing settings to screen
	//$appsList = "https://sendgrid.com/api/filter.getsettings.xml?api_user=$smtpSubuser&api_key=$smtpSubpass&name=$app";
	//print_r( simplexml_load_file($appsList) );
	
    $appsUrl = "https://sendgrid.com/api/filter.activate.xml?api_user=$smtpSubuser&api_key=$smtpSubpass&name=$app";
    $setApps = simplexml_load_file($appsUrl);
    echo "Activate: $app\n";
  }
  
  //disable domain keys app (to remove on behalf of in from name)
  //also disable subscription tracking as we will handle on a per mailing basis
  $apps = array ( 'subscriptiontrack', 'domainkeys' );
  foreach ( $apps as $app ) {
  	$dapps = "https://sendgrid.com/api/filter.deactivate.xml?api_user=$smtpSubuser&api_key=$smtpSubpass&name=$app";
  	$dk = simplexml_load_file($dapps);
  	echo "Deactivate: $app\n";
  }

} //setSendgridApps



function setHeaderFooter( $dbcon, $crmhost, $instance, $fromName )
{
  $headerhtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /><meta property="og:title" content="*|MC:{mailing.name}|*" /><title>{mailing.name}</title><style type="text/css">#outlook a{padding:0;}body{width:100% !important;}body{-webkit-text-size-adjust:none;}body{margin:0;padding:0;}img{border:none;font-size:14px;font-weight:bold;height:auto;line-height:100%;outline:none;text-decoration:none;text-transform:capitalize;}#backgroundTable{height:100% !important;margin:0;padding:0;width:100% !important;}body, #backgroundTable{background-color:#FAFAFA;}#templateContainer{border: 1px solid #DDDDDD;}h1, .h1{color:#202020;display:block;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h2, .h2{color:#202020;display:block;font-family:Arial;font-size:30px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h3, .h3{color:#202020;display:block;font-family:Arial;font-size:26px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h4, .h4{color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}#templatePreheader{background-color:#FAFAFA;}.preheaderContent div{color:#505050;font-family:Arial;font-size:10px;line-height:100%;text-align:left;}.preheaderContent div a:link, .preheaderContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#templateHeader{background-color:#D8E2EA;border-bottom:0;}.headerContent{color:#202020;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;padding:0;text-align:center;vertical-align:middle;}.headerContent a:link, .headerContent a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#headerImage{height:auto;max-width:600px !important;}#templateContainer, .bodyContent{background-color:#FDFDFD;}.bodyContent div{color:#505050;font-family:Arial;font-size:14px;line-height:150%;text-align:left;}.bodyContent div a:link, .bodyContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.bodyContent img{display:inline;height:auto;}#templateFooter{background-color:#FDFDFD;border-top:0;}.footerContent div{color:#707070;font-family:Arial;font-size:12px;line-height:125%;text-align:left;}.footerContent div a:link, .footerContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.footerContent img{display:inline;}#social{background-color:#FAFAFA;border:0;}#social div{text-align:center;}#utility{background-color:#FDFDFD;border:0;}#utility div{text-align:center;}#monkeyRewards img{max-width:190px;}</style></head><body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"><center><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><a href="http://'.$instance.'.nysenate.gov" target=_blank><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/template/header.png" alt="'.$fromName.'" style="max-width:600px;" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></a></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody"><tr><td valign="top" class="bodyContent"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tr><td valign="top"><div mc:edit="std_content00">';
  $headertext = 'New York State Senate
http://'.$instance.'.nysenate.gov

';
  $headername = 'NYSS Mailing Header';
  
  $footerhtml = '</div></td></tr></table></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="10" cellspacing="0" width="600" id="templateFooter"><tr><td valign="top" class="footerContent">
	
<table border="0" cellpadding="10" cellspacing="0" width="100%">
<tr><td colspan="2"><div mc:edit="std_footer"><em>Copyright &copy;2011 New York State Senate, All rights reserved.</em></div></td></tr>
<tr><td valign="top" width="50%"><div mc:edit="std_footer"><strong>Albany Address:</strong><br />ADDRESS</div></td><td valign="top" width="50%"><div mc:edit="std_footer"><strong>District Address:</strong><br />ADDRESS</div></td></tr>
</table>
	
	</td></tr></table><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><a href="http://www.nysenate.gov" target=_blank><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/template/footer.png" style="max-width:600px;" alt="New York State Senate Seal" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></a></td></tr></table></td></tr></table><br /></td></tr></table></center></body></html>';
  $footertext = '

Copyright 2011 New York State Senate, All rights reserved.
http://www.nysenate.gov

';
  $footername = 'NYSS Mailing Footer';
  
  $sql1 = "UPDATE civicrm_mailing_component SET name = '$headername', subject = '$headername', body_html = '$headerhtml', body_text = '$headertext' WHERE id = 1;";
  $sql2 = "UPDATE civicrm_mailing_component SET name = '$footername', subject = '$footername', body_html = '$footerhtml', body_text = '$footertext' WHERE id = 2;";
  
  //echo "\n\n".$sql1."\n\n";
  //echo "\n\n".$sql2."\n\n";
  
  if ( !mysql_query($sql1, $dbcon) || !mysql_query($sql2, $dbcon) ) {
    echo mysql_error($dbcon)."\n";
    $rc = false;
  }
}



//run script
$prog = basename($argv[0]);
//print_r($argv);

if ($argc != 17 && $argc != 20) {
  echo "Usage: $prog cmd dbhost dbuser dbpass dbname smtphost smtpport smtpauth smtpsubuser smtpsubpass instance fromName [crmhost] [appdir] [datadir]\n";
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
  $smtpUser = $argv[9];
  $smtpPass = $argv[10];
  $smtpSubuser = $argv[11];
  $smtpSubpass = $argv[12];
  $instance = $argv[13];
  $fromName = $argv[14];
  $emailFrom = $argv[15];
  $emailReplyto = $argv[16];
  $crmhost = ($argc == 20) ? $argv[17] : "";
  $appdir = ($argc == 20) ? $argv[18] : "";
  $datadir = ($argc == 20) ? $argv[19] : "";

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
  $civiMailing = getMailingBackend($dbcon);
  $civiConfig  = getConfigBackend($dbcon);

  if ($civiMailing === false) {
    echo "$prog: Unable to get CiviCRM mailing configuration.\n";
    $rc = 1;
  }
  else if (is_array($civiMailing)) {
    if ( $cmd == "update-config" ) {
      echo "Updating the CiviCRM mailing configuration.\n";
      if (updateMailingBackend($dbcon, $civiMailing, $civiConfig, $crmhost, $appdir, $datadir, $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass, $instance, $fromName, $emailFrom) === false) {
        $rc = 1;
      }
	  
    } elseif ( $cmd == "update-template" ) {
      echo "Resetting the header and footer to default values.\n";
      setHeaderFooter( $dbcon, $crmhost, $instance, $fromName );
	  
    } elseif ( $cmd == "set-apps" ) {
      echo "Activating and configuring Sendgrid apps.\n";
      setSendgridApps( $smtpUser, $smtpPass, $smtpSubuser, $smtpSubpass );
	  
	} elseif ( $cmd == 'update-from' ) {
	  echo "Setting FROM email address.\n";
	  updateFromEmail( $dbcon, $emailFrom, $fromName, $smtpSubuser );
	  
	} elseif ( $cmd == 'update-reports' ) {
	  echo "Enabling mailing reports.\n";
	  updateEmailReports( $dbcon );
	  
    } elseif ( $cmd == "update-all" ) {
      echo "1. Updating the CiviCRM mailing configuration.\n";
      updateMailingBackend($dbcon, $civiMailing, $civiConfig, $crmhost, $appdir, $datadir, $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass, $instance, $fromName, $emailFrom);
      echo "2. Resetting the header and footer to default values.\n";
	  echo "   From Name: ".$fromName."\n";
      setHeaderFooter( $dbcon, $crmhost, $instance, $fromName );
      echo "3. Activating and configuring Sendgrid apps.\n";
      setSendgridApps( $smtpUser, $smtpPass, $smtpSubuser, $smtpSubpass );
	  echo "4. Setting FROM email address.\n";
	  updateFromEmail( $dbcon, $emailFrom, $fromName, $smtpSubuser );
	  echo "5. Enabling mailing reports.\n";
	  updateEmailReports( $dbcon );
	  
    } else {
      listMailingBackend($civiMailing);
	  
    }
  } else {
    echo "$prog: CiviCRM mailing configuration is empty.\n";
  }
  
  echo "Clearing various caches gracefully (no session logout).\n";
  cacheCleanup( $dbcon );

  mysql_close($dbcon);
  exit($rc);
}
