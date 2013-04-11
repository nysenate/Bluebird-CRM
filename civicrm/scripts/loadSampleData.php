<?php

/**
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy
 * Organization: New York State Senate
 * Date: 2013-04-11
 *
 * Script used to purge training/demo instances of old data and reload sample data.
 **/

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');

class CRM_loadSampleData {

  function run() {

    global $_SERVER;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "d";
    $longopts = array("dryrun");
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--dryrun]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log("trace", "bbcfg", $bbcfg);

    $civicrm_root = $bbcfg_source['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContactsTrash.');
      return FALSE;
    }

    //if source unset, exit
    if ( empty($bbcfg) ) {
      bbscript_log("fatal", "Unable to retrieve configuration for source instance.");
      exit();
    }

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    //now do something...
  }//run

}//end class

//run the script if called directly
$trashData = new CRM_loadSampleData();
$trashData->run();
