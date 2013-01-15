<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2013-01-15

// ./migrateContactsTrash.php -S skelos --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');

class CRM_migrateContactsTrash {

  function run() {

    global $_SERVER;

    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "d:t:en";
    $longopts = array("dest=", "trash=", "employers", "dryrun");
    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
        $stdusage = civicrm_script_usage();
        $usage = '[--dest ID|DISTNAME] [--trash OPTION] [--employers] [--dryrun]';
        error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
        exit(1);
    }

    if ( empty($optlist['dest']) || empty($optlist['trash']) ) {
      bbscript_log("fatal", "The destination and trash options must be defined.");
      exit();
    }

    //get instance settings for source and destination
    $bbcfg_source = get_bluebird_instance_config($optlist['site']);
    //bbscript_log("trace", "bbcfg_source", $bbcfg_source);

    $civicrm_root = $bbcfg_source['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from migrateContactsTrash.');
      return FALSE;
    }

    $source = array(
      'name' => $optlist['site'],
      'num' => $bbcfg_source['district'],
      'db' => $bbcfg_source['db.civicrm.prefix'].$bbcfg_source['db.basename'],
      'files' => $bbcfg_source['data.rootdir'],
      'domain' => $optlist['site'].'.'.$bbcfg_source['base.domain'],
    );

    //destination may be passed as the instance name OR district ID
    if ( is_numeric($optlist['dest']) ) {
      $dest['num'] = $optlist['dest'];

      //retrieve the instance config using the district ID
      $bbFullConfig = get_bluebird_config();
      //bbscript_log("trace", "bbFullConfig", $bbFullConfig);
      foreach ( $bbFullConfig as $group => $details ) {
        if ( strpos($group, 'instance:') !== false ) {
          if ( $details['district'] == $optlist['dest'] ) {
            $dest['name'] = substr($group, 9);
            $bbcfg_dest = get_bluebird_instance_config($dest['name']);
            $dest['db'] = $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'];
            $dest['files'] = $bbcfg_dest['data.rootdir'];
            $dest['domain'] = $dest['name'].'.'.$bbcfg_dest['base.domain'];
            break;
          }
        }
      }
    }
    else {
      $bbcfg_dest = get_bluebird_instance_config($optlist['dest']);
      $dest = array(
        'name' => $optlist['dest'],
        'num' => $bbcfg_dest['district'],
        'db' => $bbcfg_dest['db.civicrm.prefix'].$bbcfg_dest['db.basename'],
        'files' => $bbcfg_dest['data.rootdir'],
        'domain' => $optlist['dest'].'.'.$bbcfg_dest['base.domain'],
      );
    }
    //bbscript_log("trace", "$source", $source);
    //bbscript_log("trace", "$dest", $dest);

    //if either dest or source unset, exit
    if ( empty($dest['db']) || empty($source['db']) ) {
      bbscript_log("fatal", "Unable to retrieve configuration for either source or destination instance.");
      exit();
    }

    // Initialize CiviCRM
    require_once 'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
    $session = CRM_Core_Session::singleton();

    self::trashContacts($source, $dest, $optlist['trash'], $optlist['employers'], $optlist['dryrun']);
  }//run

  /*
   * trash contacts in source database using options
   * we use the api to ensure all associated records are dealt with correctly
   */
  function trashContacts($source, $dest, $trashopt, $employers = FALSE, $optDry) {
    bbscript_log("trace", "here is where the trashing process will take place");
    exit();

    $sql = "
      SELECT *
      FROM {$migrateTbl}
    ";
    $contacts = CRM_Core_DAO::executeQuery($sql);

    while ( $contacts->fetch() ) {
      $params = array(
        'version' => 3,
        'id' => $contacts->contact_id,
      );
      civicrm_api('contact', 'delete', $params);
    }

    bbscript_log("info", "Completed contact migration trashing from district {$source['num']} ({$source['name']}) to district {$dest['num']} ({$dest['name']}).");

    //TODO generate stats
    $stats = array(
    );
    //bbscript_log("info", "Migration statistics:", $stats);
  }//trashContacts

}//end class

//run the script if called directly
$trashData = new CRM_migrateContactsTrash();
$trashData->run();
