<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2021-10-04

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'INFO');

class CRM_NYSS_Scripts_RestoreCaseRoles {

  function run() {
    require_once 'script_utils.php';

    // Parse the options
    $shortopts = "d:t";
    $longopts = ["dryrun", "tbl="];
    $optlist = civicrm_script_init($shortopts, $longopts);
    //Civi::log()->debug(__FUNCTION__, ['$optlist' => $optlist]);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun] [--tbl TABLENAME]';
      error_log("Usage: ".basename(__FILE__)."  $stdusage  $usage\n");
      exit(1);
    }

    echo "Initiating cleanup for 14282...\n";

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, "bbcfg", $bbcfg);
    //Civi::log()->debug(__FUNCTION__, ['bbcfg' => $bbcfg]);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from CRM_NYSS_Scripts_RestoreCaseRoles.');
      return FALSE;
    }

    $logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];
    $civiDB = $bbcfg['db.civicrm.prefix'].$bbcfg['db.basename'];

    $sql = "
      SELECT c.id case_id, r.id rel_id
      FROM {$civiDB}.civicrm_case c
      JOIN (
        SELECT case_id, max(log_date) max_log_date
        FROM {$logDB}.log_civicrm_relationship
        WHERE case_id IS NOT NULL
		    AND relationship_type_id = 13
		    GROUP BY case_id
      ) most_recent_log
        ON c.id = most_recent_log.case_id
      JOIN {$logDB}.log_civicrm_relationship r
        ON most_recent_log.case_id = r.case_id
        AND most_recent_log.max_log_date = r.log_date
      WHERE c.status_id = 2
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    //Civi::log()->debug(__FUNCTION__, ['$sql' => $sql,'dao' => $dao]);

    bbscript_log(LL::INFO, "processing {$dao->N} records...");

    $i = 0;
    $ids = [];
    while ($dao->fetch()) {
      try {
        if (!$optlist['dryrun']) {
          civicrm_api3('Relationship', 'create', [
            'id' => $dao->rel_id,
            'is_active' => 1,
            'end_date' => 'null',
          ]);
        }

        $i++;
        $ids[] = $dao->rel_id;
        if ($i % 500 == 0) {
          echo "proceessed {$i} records...\n";
        }
      }
      catch (CiviCRM_API3_Exception $e) {}
    }

    return ['count' => $i, 'ids' => $ids];
  }
}

//run the script
$class = new CRM_NYSS_Scripts_RestoreCaseRoles();
$results = $class->run();

echo "proceessed {$results['count']} total records.\n";
print_r($results['ids']);
echo "processing completed.\n";
