<?php

/*
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy
 * Organization: New York State Senate
 * Date: 2015-10-20
 *
 * Issue #10607
 * Reprocess any petition records with "signature update" action
 */

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

require_once dirname(__FILE__).'/../script_utils.php';


class CRM_Integration_Fix10607
{
  function run() {
    // Parse the options
    $shortopts = "ds:l:";
    $longopts = array("dryrun", "stats", "log-level=");

    //run under template site so we can bootstrap
    global $argv;
    $argv[] = '--site=template';

    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--stats] [--log-level LEVEL]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if (isset($optlist['log-level'])) {
      set_bbscript_log_level($optlist['log-level']);
    }

    bbscript_log(LL::INFO, 'Initiating fix for #10607...');

    $params = array(
      'optlist' => $optlist,
    );

    //get instance settings
    $bbcfg = $params['bbcfg'] = get_bluebird_instance_config($optlist['site']);
    bbscript_log(LL::DEBUG, 'Bluebird config:', $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), false, false, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS.');
      return false;
    }

    bbscript_log(LL::DEBUG, 'Command line opts:', $optlist);

    //set website integration DB
    $params['intDB'] = $bbcfg['website.local.db.name'];

    //get archived accumulator records with signature update action
    $sql = "
      SELECT *
      FROM {$params['intDB']}.archive
      WHERE target_shortname = '{$optlist['site']}'
        AND target_shortname = user_shortname
        AND msg_action = 'signature update'
    ";
    bbscript_log(LL::DEBUG, 'SQL query:', $sql);
    $row = CRM_Core_DAO::executeQuery($sql);

    $result = array('processed' => array());
    while ($row->fetch()) {
      bbscript_log(LL::TRACE, 'fetched row:', $row);
      $result['processed'][] = $row->id;

      //now implement standard processing for petitions

      //check contact/user
      bbscript_log(LL::TRACE, 'calling getContactId('.$row->user_id.')');
      $cid = CRM_NYSS_BAO_Integration_Website::getContactId($row->user_id);

      if (!$cid) {
        bbscript_log(LL::DEBUG, 'Contact with web_user_id='.$row->user_id.' was not found; attempting match');

        $contactParams = CRM_NYSS_BAO_Integration_Website::getContactParams($row);
        if (empty($contactParams)) {
          bbscript_log(LL::DEBUG, 'Unable to create user; not enough data provided.', $row);
          continue;
        }

        $contactParams['gender_id'] = '';
        if ($row->gender) {
          switch ($row->gender) {
            case 'male':
              $contactParams['gender_id'] = 2;
              break;
            case 'female':
              $contactParams['gender_id'] = 1;
              break;
            case 'other':
              $contactParams['gender_id'] = 4;
              break;
            default:
          }
        }

        $contactParams['birth_date'] = '';
        if (!empty($row->dob)) {
          $contactParams['birth_date'] = date('Y-m-d', $row->dob); //dob comes as timestamp
        }

        bbscript_log(LL::TRACE, 'calling matchContact() with:', $contactParams);
        $cid = CRM_NYSS_BAO_Integration_Website::matchContact($contactParams);
      }

      if (!$cid) {
        bbscript_log(LL::DEBUG, 'Failed to match or create contact', $contactParams);
        $stats['error'][] = array(
          'is_error' => 1,
          'error_message' => 'Unable to match or create contact',
          'params' => $contactParams
        );

        //already archived, so we can just continue to next record
        continue;
      }

      //prep params
      $params = json_decode($row->msg_info);
      bbscript_log(LL::TRACE, 'Params after json_decode():', $params);

      $result = CRM_NYSS_BAO_Integration_Website::processPetition($cid, $row->msg_action, $params);

      if ($result['is_error'] || $result == FALSE) {
        bbscript_log(LL::ERROR, 'Unable to process row', $result);
        $stats['error'][] = $result;
      }
      else {
        $stats['processed'][] = $row->id;
      }
    }


    //report stats
    $stats['counts'] = array(
      'processed' => count($stats['processed']),
      'error' => count($stats['error']),
    );

    bbscript_log(LL::NOTICE, "Fix #10607 stats:", $stats['counts']);

    if ($optlist['stats']) {
      bbscript_log(LL::NOTICE, "Fix #10607 details:");
      bbscript_log(LL::NOTICE, "Processed:", $result['processed']);
    }
  }//run
}//end class

//run the script
$script = new CRM_Integration_Fix10607();
$script->run();
