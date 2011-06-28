<?php
// Project: BluebirdCRM
// Author: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2011-06-20
//

function getMailingBackend( $dbcon )
{
  $civiconfig = array();
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


function updateMailingBackend($dbcon, $civiMailing, $crmhost, $appdir, $datadir, 
                              $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass,
							  $instance) {
  	$rc = true;
	//print_r($civiMailing);

  	$mb = $civiMailing['backend'];
	
	//TODO we need to retrieve this from the settings file or instantiae civi
	if ( !defined('CIVICRM_SITE_KEY') ) {
		define('CIVICRM_SITE_KEY', '32425kj24h5kjh24542kjh524');
	}
	
	//set values
	require_once '../modules/civicrm/CRM/Utils/Crypt.php';
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

  	return $rc;
} // updateMailingBackend()

function setHeaderFooter( $dbcon, $crmhost ) {

	$headerhtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /><meta property="og:title" content="*|MC:{mailing.name}|*" /><title>{mailing.name}</title><style type="text/css">#outlook a{padding:0;}body{width:100% !important;}body{-webkit-text-size-adjust:none;}body{margin:0;padding:0;}img{border:none;font-size:14px;font-weight:bold;height:auto;line-height:100%;outline:none;text-decoration:none;text-transform:capitalize;}#backgroundTable{height:100% !important;margin:0;padding:0;width:100% !important;}body, #backgroundTable{background-color:#FAFAFA;}#templateContainer{border: 1px solid #DDDDDD;}h1, .h1{color:#202020;display:block;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h2, .h2{color:#202020;display:block;font-family:Arial;font-size:30px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h3, .h3{color:#202020;display:block;font-family:Arial;font-size:26px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h4, .h4{color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}#templatePreheader{background-color:#FAFAFA;}.preheaderContent div{color:#505050;font-family:Arial;font-size:10px;line-height:100%;text-align:left;}.preheaderContent div a:link, .preheaderContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#templateHeader{background-color:#D8E2EA;border-bottom:0;}.headerContent{color:#202020;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;padding:0;text-align:center;vertical-align:middle;}.headerContent a:link, .headerContent a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#headerImage{height:auto;max-width:600px !important;}#templateContainer, .bodyContent{background-color:#FDFDFD;}.bodyContent div{color:#505050;font-family:Arial;font-size:14px;line-height:150%;text-align:left;}.bodyContent div a:link, .bodyContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.bodyContent img{display:inline;height:auto;}#templateFooter{background-color:#FDFDFD;border-top:0;}.footerContent div{color:#707070;font-family:Arial;font-size:12px;line-height:125%;text-align:left;}.footerContent div a:link, .footerContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.footerContent img{display:inline;}#social{background-color:#FAFAFA;border:0;}#social div{text-align:center;}#utility{background-color:#FDFDFD;border:0;}#utility div{text-align:center;}#monkeyRewards img{max-width:190px;}</style></head><body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"><center><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/header.png" alt="New York State Senate" style="max-width:600px;" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody"><tr><td valign="top" class="bodyContent"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tr><td valign="top"><div mc:edit="std_content00">';
	$headertext = 'New York State Senate';
	$headername = 'NYSS Mailing Header';
	
	$footerhtml = '</div></td></tr></table></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="10" cellspacing="0" width="600" id="templateFooter"><tr><td valign="top" class="footerContent"><table border="0" cellpadding="10" cellspacing="0" width="100%"><tr><td valign="top" width="350"><br /><div mc:edit="std_footer"><em>Copyright ©2011 New York State Senate, All rights reserved.</em><br /><br /><strong>Mailing address:</strong><br /><br /><br /></div><br /></td><td valign="top" width="190" id="monkeyRewards"><br /><br /></td></tr></table></td></tr></table><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/footer_white.png" style="max-width:600px;" alt="New York State Senate Seal" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></td></tr></table></td></tr></table><br /></td></tr></table></center></body></html>';
	$footertext = '';
	$footername = 'NYSS Mailing Footer';
	
	$sql = "UPDATE civicrm_mailing_component SET name = '$headername', subject = '$headername', body_html = '$headerhtml', body_text = '$headertext' WHERE id = 1; UPDATE civicrm_mailing_component SET name = '$footername', subject = '$footername', body_html = '$footerhtml', body_text = '$footertext' WHERE id = 2;";
  	if (!mysql_query($sql, $dbcon)) {
    	echo mysql_error($dbcon)."\n";
    	$rc = false;
  	}
	
}

$prog = basename($argv[0]);

if ($argc != 12 && $argc != 15) {
  echo "Usage: $prog cmd dbhost dbuser dbpass dbname smtphost smtpport smtpauth smtpsubuser smtpsubpass instance [crmhost] [appdir] [datadir]\n";
  echo "   cmd can be: list, update\n";
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
  $crmhost = ($argc == 15) ? $argv[12] : "";
  $appdir = ($argc == 15) ? $argv[13] : "";
  $datadir = ($argc == 15) ? $argv[14] : "";

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

  if ($civiMailing === false) {
    echo "$prog: Unable to get CiviCRM mailing configuration.\n";
    $rc = 1;
  }
  else if (is_array($civiMailing)) {
    if ($cmd == "update") {
      echo "Updating the CiviCRM mailing configuration.\n";
      if (updateMailingBackend($dbcon, $civiMailing, $crmhost, $appdir, $datadir, $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass, $instance ) === false) {
        $rc = 1;
      }
    }
    else {
      listMailingBackend($civiMailing);
    }
  }
  else {
    echo "$prog: CiviCRM mailing configuration is empty.\n";
  }

  mysql_close($dbcon);
  exit($rc);
}
