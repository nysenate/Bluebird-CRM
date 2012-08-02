<?php
/*
** Project: BluebirdCRM
** Author: Brian Shaughnessy
** Organization: New York State Senate
** Date: 2012-01-03
**
** merge duplicate contacts safely
**
*/
  
require_once 'script_utils.php';

error_reporting(E_ERROR | E_PARSE | E_WARNING);

function run()
{
  $prog = basename(__FILE__);
  $shortopts = '';
  $longopts = array();
  $stdusage = civicrm_script_usage();
  $usage = "";
  $contactOpts = array();

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    error_log("Usage: $prog  $stdusage  $usage");
    exit(1);
  }

  if (!is_cli_script()) {
      echo "<pre>\n";
  }

  require_once 'CRM/Core/Config.php';
  $config = CRM_Core_Config::singleton();

  require_once 'api/api.php';
  require_once 'CRM/Core/Error.php';
  require_once 'CRM/Core/DAO.php';
  require_once 'CRM/Dedupe/Form/RemoveDupeAddress.php';
  
  //print_r($optlist);
  
  //log the execution of script
  CRM_Core_Error::debug_log_message('dedupeAddresses.php');

  echo "Removing duplicate addresses for: {$optlist['site']}\n";
  $output_status = false;
  CRM_Dedupe_Form_RemoveDupeAddress::postProcess($output_status);
}

run();
