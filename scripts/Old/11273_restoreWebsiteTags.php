<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2018-03-30

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'INFO');

class CRM_NYSS_Scripts_RestoreWebsiteTags {

  function run() {
    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "d:t";
    $longopts = array("dryrun", "tbl=");
    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--tbl TABLENAME]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    echo "Initiating cleanup for 11273...\n";

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, "bbcfg", $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from CRM_NYSS_Scripts_RestoreWebsiteTags.');
      return FALSE;
    }

    $logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];
    $dao = CRM_Core_DAO::executeQuery("
      SELECT entity_id, tag_id
      FROM {$logDB}.log_civicrm_entity_tag et
      JOIN {$logDB}.log_civicrm_tag t
        ON et.tag_id = t.id
      JOIN {$logDB}.log_civicrm_tag pt
        ON t.parent_id = pt.id
        AND pt.name IN ('Website Issues', 'Website Committees', 'Website Bills', 'Website Petitions')
      WHERE et.log_action = 'Delete'
        AND et.entity_table = 'civicrm_contact'
    ");
    bbscript_log(LL::INFO, "processing {$dao->N} records...");

    $i = 0;
    while ($dao->fetch()) {
      try {
        civicrm_api3('entity_tag', 'create', array(
          'entity_id' => $dao->entity_id,
          'entity_table' => 'civicrm_contact',
          'tag_id' => $dao->tag_id,
        ));

        $i++;
        if ($i % 500 == 0) {
          echo "proceessed {$i} records...\n";
        }
      }
      catch (CiviCRM_API3_Exception $e) {}
    }
  }//run
}//end class

//run the script
$class = new CRM_NYSS_Scripts_RestoreWebsiteTags();
$class->run();

echo "processing completed.\n";
