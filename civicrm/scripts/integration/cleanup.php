<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2015-12-03

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

require_once dirname(__FILE__).'/../script_utils.php';


class CRM_Integration_Cleanup
{
  function run()
  {
    // Parse the options
    $shortopts = "dsa:t:l:";
    $longopts = array("dryrun", "stats", "action=", "type=", "log-level=");

    //run under template site so we can bootstrap
    global $argv;
    $argv[] = '--site=template';

    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--stats] [--action ACTION] [--type TYPE] [--log-level LEVEL]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    if (isset($optlist['log-level'])) {
      set_bbscript_log_level($optlist['log-level']);
    }

    bbscript_log(LL::INFO, 'Initiating website integration cleanup...');

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
    $params['typeSql'] = ($optlist['type']) ? "AND msg_type = '{$optlist['type']}'" : '';
    $params['addSql'] = '';

    //handle survey in special way
    if ($optlist['type'] == 'SURVEY') {
      $params['typeSql'] = "AND msg_type = 'PETITION'";
      $params['addSql'] = "AND msg_action = 'questionnaire response'";
    }

    if (empty($optlist['action'])) {
      bbscript_log(LL::ERROR, 'Action must be specified.');
      return false;
    }

    $result = array();

    switch ($optlist['action']) {
      case 'unassigned':
        $result = self::archiveUnassigned($params);
        break;

      default:
        bbscript_log(LL::ERROR, 'Requested action is not available.');
        return;
    }

    //report stats
    $stats['counts'] = array(
      'processed' => count($result['processed']),
    );

    bbscript_log(LL::NOTICE, "Clean up stats:", $stats['counts']);

    if ($optlist['stats']) {
      bbscript_log(LL::NOTICE, "Clean up details:");
      bbscript_log(LL::NOTICE, "Processed:", $result['processed']);
    }
  }//run

  function archiveUnassigned($params) {
    //get accumulator records with no target_shortname; exclude surveys
    $sql = "
      SELECT *
      FROM {$params['intDB']}.accumulator
      WHERE (target_shortname IS NULL OR target_shortname = '')
        {$params['typeSql']}
        {$params['addSql']}
        AND msg_action != 'questionnaire response'
    ";
    bbscript_log(LL::DEBUG, 'SQL query:', $sql);
    $row = CRM_Core_DAO::executeQuery($sql);

    $result = array('processed' => array());

    while ($row->fetch()) {
      bbscript_log(LL::TRACE, 'fetched row:', $row);
      $result['processed'][] = $row->id;

      //archive record
      CRM_NYSS_BAO_Integration_Website::archiveRecord($params['intDB'], 'other', $row, null, null);
    }

    return $result;
  }//archiveUnassigned
}//end class

//run the script
$script = new CRM_Integration_Cleanup();
$script->run();
