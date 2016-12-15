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

function run() {
  $prog = basename(__FILE__);
  $shortopts = 'c:d';
  $longopts = array('ct=', 'dg=');
  $stdusage = civicrm_script_usage();
  $usage = "[--ct|-c {Individual|Household|Organization}] [--dg|-d {Rule}]";
  $contactOpts = array(
    'i' => 'Individual',
    'h' => 'Household',
    'o' => 'Organization'
  );

  $optlist = civicrm_script_init($shortopts, $longopts);
  if ($optlist === null) {
    error_log("Usage: $prog  $stdusage  $usage");
    exit(1);
  }

  if (!is_cli_script()) {
    echo "<pre>\n";
  }

  require_once 'api/api.php';
  require_once 'CRM/Core/Error.php';
  require_once 'CRM/Core/DAO.php';
  
  //print_r($optlist);
  
  //log the execution of script
  CRM_Core_Error::debug_log_message('batchDedupeMerge.php');
  
  //if contact type provided and no dedupe rule group given, use default strict
  $contactType = 'Individual';
  if (!empty($optlist['ct'])) {
    $contactOptIdx = strtolower($optlist['ct'][0]);
    if (isset($contactOpts[$contactOptIdx])) {
      $contactType = $contactOpts[$contactOptIdx];
    }
    else {
      //CRM_Core_Error::fatal( ts('Invalid Contact Type.') );
      echo ts("$prog: {$optlist['ct']}: Invalid Contact Type.\n");
      exit(1);
    }
  }
  
  if (!empty($optlist['dg'])) {
    $dg = strtolower($optlist['dg'][0]);
  }
  else {
    $sql = "
      SELECT id 
      FROM civicrm_dedupe_rule_group 
      WHERE contact_type = '$contactType'
        AND used = 'Unsupervised';";
    $dg = CRM_Core_DAO::singleValueQuery($sql);
  }

  echo "Processing batch merge for {$contactType}s using dedupe rule $dg.\n";  

  $params = array(
    'version' => 3,
    'rule_group_id' => $dg
  );
  $return = civicrm_api('job', 'process_batch_merge', $params);

  if ($return['is_error']) {
    echo 'There was an error when processing the batch merge:\n';
    print_r($return);
  }

  echo "[{$optlist['site']}] Finished batch-merging duplicate contacts.\n";
}

run();
