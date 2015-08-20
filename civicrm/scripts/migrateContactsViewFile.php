<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2013-01-15

// ./migrateContactsViewFile.php -S skelos --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');

class CRM_migrateContactsViewFile {

  function run() {

    global $_SERVER;

    //set memory limit so we don't max out
    ini_set('memory_limit', '3000M');

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "f:s";
    $longopts = array("filename=", "save");
    $optlist = civicrm_script_init($shortopts, $longopts, TRUE);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--filename FILENAME] [--save]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    if ( empty($optlist['filename']) ) {
      bbscript_log(LL::FATAL, "The filename must be provided.");
      exit();
    }

    //get instance
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, "$bbcfg", $bbcfg);

    require_once 'CRM/Utils/System.php';

    $civicrm_root = $bbcfg_source['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

    //set import folder based on environment
    $filename = '/data/redistricting/bluebird_'.$bbcfg['install_class'].'/migrate/'.$optlist['filename'];
    if ( !file_exists($filename) ) {
      bbscript_log(LL::FATAL, "Filename not found: {$filename}.");
    }

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    self::viewFile($filename, $optlist['save']);
  }//run

  /*
   * trash contacts in source database using options
   * we use the api to ensure all associated records are dealt with correctly
   */
  function viewFile($filename, $save = FALSE) {
    bbscript_log(LL::INFO, "Viewing file: $filename");

    $data = json_decode(file_get_contents($filename), TRUE);
    bbscript_log(LL::INFO, "File data:", $data);

    if ( $save ) {
      $data = print_r($data, TRUE);
      $fileResource = fopen($filename.'_structured.txt', 'w');
      fwrite($fileResource, $data);
    }

  }//viewFile

}//end class

//run the script if called directly
$viewFile = new CRM_migrateContactsViewFile();
$viewFile->run();
