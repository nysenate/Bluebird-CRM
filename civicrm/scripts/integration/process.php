<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2015-04-10

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');
define('DB_INTEGRATION', 'senate_integration');

class CRM_Integration_Process {

  function run() {
    require_once '../script_utils.php';

    // Parse the options
    $shortopts = "d:s";
    $longopts = array("dryrun", "stats");
    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--tbl TABLENAME]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    bbscript_log('info', 'Initiating integration processing...');

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log("trace", "bbcfg", $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from cleanLogs.');
      return FALSE;
    }

    //set integration DB
    $intDB = DB_INTEGRATION;

    //get all accumulator records for instance (target)
    $row = CRM_Core_DAO::executeQuery("
      SELECT *
      FROM {$intDB}.accumulator
      WHERE target_shortname = '{$bbcfg['db.basename']}'
    ");

    $errors = $status = array();

    while ($row->fetch()) {
      //bbscript_log('trace', 'row', $row);

      //check contact/user
      if (!$cid = CRM_NYSS_BAO_Integration::getContact($row->user_id)) {
        $contactParams = array(
          'web_user_id' => $row->user_id,
          'first_name' => $row->first_name,
          'last_name' => $row->last_name,
          'email' => $row->email_address,
          'street_address' => $row->street_number,//TODO check
          'street_unit' => $row->apt_no,
          'city' => $row->city,
          'state' => $row->state,
          'postal_code' => $row->zip,
          'birth_date' => $row->birth_date,
          'gender_id' => ($row->gender == 'M') ?  2 : 1,//TODO check
        );

        $cid = CRM_NYSS_BAO_Integration::matchContact($contactParams);
      }
      //CRM_Core_Error::debug_var('cid', $cid);

      //prep params
      $params = json_decode($row->msg_info);

      switch ($row->msg_type) {
        case 'ISSUE':
          $result = CRM_NYSS_BAO_Integration::processIssue($cid, $row->msg_action, $params);
          break;

        case 'COMMITTEE':
          break;

        case 'PETITION':
          break;

        case 'CONTACT':
          break;

        case 'BILL':
          break;

        case 'ACCOUNT':
          break;

        case 'PROFILE':
          break;

        default:
          bbscript_log('error', 'Unable to process row. Message type is unknown.', $row);
          $stats['unprocessed'][] = $row;
      }

      if ($result['is_error']) {
        //TODO error handling
        $stats['error'][] = $result;

      }
      else {
        //TODO archive rows by ID
        $stats['processed'][] = $row->id;

      }
    }

    //TODO report stats
    $stats['counts'] = array(
      'processed' => count($stats['processed']),
      'unprocessed' => count($stats['unprocessed']),
      'error' => count($stats['error']),
    );

    echo "Processing stats:\n";
    print_r($stats['counts']);

    if ($optlist['stats']) {
      echo "\nProcessing details:\n";
      print_r($stats['processed']);
      print_r($stats['unprocessed']);
      print_r($stats['error']);
    }
  }//run

}//end class

//run the script
$script = new CRM_Integration_Process();
$script->run();
