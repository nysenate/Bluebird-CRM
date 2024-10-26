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
  $shortopts = 'l';
  $longopts = ['log='];
  $stdusage = civicrm_script_usage();
  $usage = "";
  $contactOpts = [];

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    error_log("Usage: $prog  $stdusage  $usage");
    exit(1);
  }

  //use the log level passed to params or existing level via parent script
  set_bbscript_log_level($optlist['log'] ?? get_bbscript_log_level());

  if (!is_cli_script()) {
    echo "<pre>\n";
  }

  require_once 'CRM/Core/Config.php';
  CRM_Core_Config::singleton();

  require_once 'CRM/Core/Error.php';
  require_once 'CRM/Dedupe/Form/RemoveDupeAddress.php';

  //print_r($optlist);

  //log the execution of script
  CRM_Core_Error::debug_log_message('dedupeAddresses.php');

  echo "Removing duplicate addresses for: {$optlist['site']}\n";

  CRM_Dedupe_Form_RemoveDupeAddress::removeDuplicateAddresses(FALSE);
}

run();
