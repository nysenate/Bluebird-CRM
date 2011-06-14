<?php
// Project: BluebirdCRM
// Author: Ken Zalewski
// Organization: New York State Senate
// Date: 2010-11-23
// Revised: 2011-04-21
//

function getMailingBackend($dbcon)
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


function listMailingBackend($civiMailing)
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
	
	//require_once '../civicrm/scripts/script_utils.php';
	//civicrm_script_init(null, null, $instance, $mb['qfKey']);
	
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
