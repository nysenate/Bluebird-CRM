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
	//TODO: this is manually set to override directory; revert when core has been updated
	require_once $appdir.'/civicrm/custom/php/CRM/Utils/Crypt.php';
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

/*
 * We use sendgrid subuser accounts for each district.
 * Enable subscription, click, and open tracking apps
 * Set configuration accordingly
 */
function setSendgridApps ( $smtpUser, $smtpPass, $smtpSubuser ) {

	//enable apps
	$apps = array ( 'subscriptiontrack', 'opentrack', 'clicktrack' );
	foreach ( $apps as $app ) {
		$appsUrl = "https://sendgrid.com/apiv2/customer.apps.xml?api_user=$smtpUser&api_key=$smtpPass&user=$smtpSubuser&task=activate&name=$app";
		$setApps = simplexml_load_file($appsUrl);
		echo $setApps."\n\n";
	}
	
	//set configuration
	
} //setSendgridApps

function setHeaderFooter( $dbcon, $crmhost, $instance ) {

	$headerhtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8" /><meta property="og:title" content="*|MC:{mailing.name}|*" /><title>{mailing.name}</title><style type="text/css">#outlook a{padding:0;}body{width:100% !important;}body{-webkit-text-size-adjust:none;}body{margin:0;padding:0;}img{border:none;font-size:14px;font-weight:bold;height:auto;line-height:100%;outline:none;text-decoration:none;text-transform:capitalize;}#backgroundTable{height:100% !important;margin:0;padding:0;width:100% !important;}body, #backgroundTable{background-color:#FAFAFA;}#templateContainer{border: 1px solid #DDDDDD;}h1, .h1{color:#202020;display:block;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h2, .h2{color:#202020;display:block;font-family:Arial;font-size:30px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h3, .h3{color:#202020;display:block;font-family:Arial;font-size:26px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}h4, .h4{color:#202020;display:block;font-family:Arial;font-size:22px;font-weight:bold;line-height:100%;margin-top:0;margin-right:0;margin-bottom:10px;margin-left:0;text-align:left;}#templatePreheader{background-color:#FAFAFA;}.preheaderContent div{color:#505050;font-family:Arial;font-size:10px;line-height:100%;text-align:left;}.preheaderContent div a:link, .preheaderContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#templateHeader{background-color:#D8E2EA;border-bottom:0;}.headerContent{color:#202020;font-family:Arial;font-size:34px;font-weight:bold;line-height:100%;padding:0;text-align:center;vertical-align:middle;}.headerContent a:link, .headerContent a:visited{color:#336699;font-weight:normal;text-decoration:underline;}#headerImage{height:auto;max-width:600px !important;}#templateContainer, .bodyContent{background-color:#FDFDFD;}.bodyContent div{color:#505050;font-family:Arial;font-size:14px;line-height:150%;text-align:left;}.bodyContent div a:link, .bodyContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.bodyContent img{display:inline;height:auto;}#templateFooter{background-color:#FDFDFD;border-top:0;}.footerContent div{color:#707070;font-family:Arial;font-size:12px;line-height:125%;text-align:left;}.footerContent div a:link, .footerContent div a:visited{color:#336699;font-weight:normal;text-decoration:underline;}.footerContent img{display:inline;}#social{background-color:#FAFAFA;border:0;}#social div{text-align:center;}#utility{background-color:#FDFDFD;border:0;}#utility div{text-align:center;}#monkeyRewards img{max-width:190px;}</style></head><body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"><center><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer"><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><a href="http://'.$instance.'.nysenate.gov" target=_blank><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/template/header.png" alt="New York State Senate" style="max-width:600px;" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></a></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody"><tr><td valign="top" class="bodyContent"><table border="0" cellpadding="20" cellspacing="0" width="100%"><tr><td valign="top"><div mc:edit="std_content00">';
	$headertext = 'New York State Senate';
	$headername = 'NYSS Mailing Header';
	
	$footerhtml = '</div></td></tr></table></td></tr></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="10" cellspacing="0" width="600" id="templateFooter"><tr><td valign="top" class="footerContent"><table border="0" cellpadding="10" cellspacing="0" width="100%"><tr><td valign="top" width="350"><br /><div mc:edit="std_footer"><em>Copyright &copy;2011 New York State Senate, All rights reserved.</em><br /><br /><strong>Mailing address:</strong><br /><br /><br /></div><br /></td><td valign="top" width="190" id="monkeyRewards"><br /><br /></td></tr></table></td></tr></table><table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader"><tr><td class="headerContent"><a href="www.nysenate.gov" target=_blank><img src="http://'.$crmhost.'/sites/'.$crmhost.'/pubfiles/images/template/footer.png" style="max-width:600px;" alt="New York State Senate Seal" id="headerImage campaign-icon" mc:label="header_image" mc:edit="header_image" mc:allowdesigner mc:allowtext /></a></td></tr></table></td></tr></table><br /></td></tr></table></center></body></html>';
	$footertext = ' ';
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

$prog = basename($argv[0]);

if ($argc != 14 && $argc != 17) {
    	
    echo "Usage: $prog cmd dbhost dbuser dbpass dbname smtphost smtpport smtpauth smtpsubuser smtpsubpass instance [crmhost] [appdir] [datadir]\n";
    echo "   cmd can be: list, update-config, update-template, set-apps\n";
    exit(1);

} else {
	
	//print_r($argv);
	
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
    $crmhost = ($argc == 17) ? $argv[14] : "";
    $appdir = ($argc == 17) ? $argv[15] : "";
    $datadir = ($argc == 17) ? $argv[16] : "";

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
  	
  	} else if (is_array($civiMailing)) {

    	if ( $cmd == "update-config" ) {
        	echo "Updating the CiviCRM mailing configuration.\n";
        	if (updateMailingBackend($dbcon, $civiMailing, $crmhost, $appdir, $datadir, $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass, $instance ) === false) {
        		$rc = 1;
        	}
		} elseif ( $cmd == "update-template" ) {
			
			echo "Resetting the header and footer to default values.\n";
			setHeaderFooter( $dbcon, $crmhost, $instance );
		
		} elseif ( $cmd == "set-apps" ) {
			
			echo "Activating Sendgrid apps.\n";
			setSendgridApps( $smtpUser, $smtpPass, $smtpSubuser );
		
		} elseif ( $cmd == "update-all" ) {
			
			echo "Updating the CiviCRM mailing configuration.\n";
        	updateMailingBackend($dbcon, $civiMailing, $crmhost, $appdir, $datadir, $smtpHost, $smtpPort, $smtpAuth, $smtpSubuser, $smtpSubpass, $instance);
		
			echo "Resetting the header and footer to default values.\n";
			setHeaderFooter( $dbcon, $crmhost );
		
		} else {
        	
        	listMailingBackend($civiMailing);
    
    	}
  	} else {
  		
      	echo "$prog: CiviCRM mailing configuration is empty.\n";
  	}

  	mysql_close($dbcon);
  	exit($rc);
}
