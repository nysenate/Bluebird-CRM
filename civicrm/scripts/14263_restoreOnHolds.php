<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2021-10-01

error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'INFO');

class CRM_NYSS_Scripts_RestoreOnHold {

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

    echo "Initiating cleanup for 14263...\n";

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, "bbcfg", $bbcfg);
    //Civi::log()->debug(__FUNCTION__, ['bbcfg' => $bbcfg]);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap([], FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from CRM_NYSS_Scripts_RestoreOnHold.');
      return FALSE;
    }

    $logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];
    $civiDB = $bbcfg['db.civicrm.prefix'].$bbcfg['db.basename'];

    $sql = "
      SELECT ec.id, ec.email, el.email, ec.on_hold ec_on_hold, el.on_hold el_on_hold
      FROM {$civiDB}.civicrm_email ec
      JOIN (
        SELECT id, email, on_hold
        FROM {$logDB}.log_civicrm_email
        WHERE on_hold = 2
          AND log_date >= '2021-07-01'
        GROUP BY id, email, on_hold
      ) el
        ON el.id = ec.id
      WHERE ec.on_hold = 0
        AND ec.email = el.email
      GROUP BY ec.id
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    Civi::log()->debug(__FUNCTION__, ['$sql' => $sql,'dao' => $dao]);

    bbscript_log(LL::INFO, "processing {$dao->N} records...");

    $i = 0;
    while ($dao->fetch()) {
      try {
        if (!$optlist['dryrun']) {
          civicrm_api3('Email', 'create', [
            'id' => $dao->id,
            'on_hold' => 2,
          ]);
        }

        $i++;
        if ($i % 500 == 0) {
          echo "proceessed {$i} records...\n";
        }
      }
      catch (CiviCRM_API3_Exception $e) {}
    }

    return $i;
  }
}

//run the script
$class = new CRM_NYSS_Scripts_RestoreOnHold();
$i = $class->run();

echo "proceessed {$i} total records.\n";
echo "processing completed.\n";
