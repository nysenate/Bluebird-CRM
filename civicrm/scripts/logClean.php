<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2013-01-23

// ./cleanLogs.php -S district --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');

class CRM_cleanLogs {

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

    bbscript_log("info", 'Initiating log table cleanup...');

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log("trace", "bbcfg", $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from cleanLogs.');
      return FALSE;
    }

    //we can only cleanup MyISAM/InnoDB tables
    $tbls = $allowedTbls = array(
      'log_civicrm_address',
      'log_civicrm_activity',
      'log_civicrm_activity_assignment',
      'log_civicrm_activity_target',
      'log_civicrm_contact',
      'log_civicrm_dashboard_contact',
      'log_civicrm_email',
      'log_civicrm_entity_tag',
      'log_civicrm_group',
      'log_civicrm_group_contact',
      'log_civicrm_job',
      'log_civicrm_note',
      'log_civicrm_phone',
      'log_civicrm_relationship',
      'log_civicrm_subscription_history',
      'log_civicrm_value_constituent_information_1',
      'log_civicrm_value_district_information_7',
    );

    //check if table(s) are passed
    if ( $optlist['tbl'] ) {
      //param could be single table or comma sep list
      if ( strpos($optlist['tbl'], ',') !== FALSE ) {
        $tbls = explode(',', $optlist['tbl']);
      }
      else {
        $tbls = array(
          $optlist['tbl']
        );
      }

      //check prefix as log_ table or core table could be passed
      foreach ( $tbls as &$tbl ) {
        if ( strpos($tbl, 'log_') !== 0 ) {
          $tbl = 'log_'.$tbl;
        }
        if ( !in_array($tbl, $allowedTbls) ) {
          bbscript_log('info', "The {$tbl} table is not available for this cleaning process.");
          return FALSE;
        }
      }
      //bbscript_log('debug', 'tables to be processed:', $tbls);
    }

    $logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];
    $stats = array();

    //define some columns to skip or handle differently
    $logCols = array(
      'log_date',
      'log_conn_id',
      'log_user_id',
      'log_action',
      'log_job_id',
    );
    $cacheCols = array(
      'last_run',
      'cache_date',
    );
    $skipCols = array_merge($logCols, $cacheCols);
    //bbscript_log('trace', 'columns to be skipped', $skipCols);

    //list of fields to use when applying the index for deduping
    //we exclude user_id and job_id because they may be NULL, and a unique index permits multiple rows with NULL val cols
    //that's not ideal, but it is unlikely we would have the same date/conn/action with a different user or job id
    $idxFields = array(
      'id',
      'log_date',
      'log_conn_id',
      'log_action',
    );

    // start db connection
    $conn = mysql_connect($bbcfg['db.host'], $bbcfg['db.user'], $bbcfg['db.pass'], TRUE);
    mysql_select_db($logDB, $conn);

    $memUse = memory_get_usage();
    $memUseMB = round($memUse/1048576,2);
    //bbscript_log("trace", "Memory usage before cycling through tables: {$memUseMB} M");

    foreach ( $tbls as $tbl ) {
      bbscript_log("info", "processing {$tbl}...");

      //first remove log col-based duplicates using an index so that we can compare unique rows below
      //there is a bug in MySQL such that the IGNORE flag doesn't work when adding an index to InnoDB
      //http://bugs.mysql.com/bug.php?id=40344
      //consequently, we convert to MyISAM, apply the index to remove dupes, then change back to InnoDB
      $idxFieldsList = implode(',', $idxFields);
      $sqls = array(
        "ALTER TABLE {$logDB}.{$tbl} ENGINE MyISAM;",
        "ALTER IGNORE TABLE {$logDB}.{$tbl} ADD UNIQUE INDEX dupeRemove ({$idxFieldsList});",
        "DROP INDEX dupeRemove ON {$logDB}.{$tbl};",
        "ALTER TABLE {$logDB}.{$tbl} ENGINE InnoDB;",
      );
      if ( $optlist['dryrun'] ) {
        bbscript_log("debug", 'logClean index-based dedupe', $sql);
      }
      else {
        foreach ( $sqls as $sql ) {
          CRM_Core_DAO::executeQuery($sql);
        }
      }

      //retrieve logs for the table and order so we can meaningfully cycle through them
      //use mysql unbuffered query to try to reduce memory usage
      $sql = "
        SELECT *
        FROM {$logDB}.{$tbl}
        WHERE log_action = 'Update'
          OR log_action = 'Initialization'
        ORDER BY id, log_action, log_date
      ";
      //bbscript_log("trace", "{$tbl} sql", $sql);
      //$r = CRM_Core_DAO::executeQuery($sql);
      $rq = mysql_unbuffered_query($sql, $conn);

      $lastRecord = array('id' => 0);
      $stats[$tbl] = $i = 0;

      $memUse = memory_get_usage();
      $memUseMB = round($memUse/1048576,2);
      //bbscript_log("trace", "Memory usage before processing {$tbl}: {$memUseMB} M");

      $deleteRows = array();

      while ( $r = mysql_fetch_assoc($rq) ) {
        //bbscript_log("trace", "r", $r);
        $thisRecord = array();
        foreach ( $r as $f => $v ) {
          if ( !in_array($f, $skipCols) ) {
            $thisRecord[$f] = $v;
          }
        }
        //bbscript_log("trace", "thisRecord", $thisRecord);

        $i++;
        if ( $i % 50000 === 0 ) {
          $memUse = memory_get_usage();
          $memUseMB = round($memUse/1048576,2);
          //bbscript_log("trace", "Memory usage at {$i} records: {$memUseMB} M");

          flush();
          ob_flush();
        }

        //if we've moved to a new id, reset lastRecord and continue without comparing
        if ( $r['id'] != $lastRecord['id'] ) {
          $lastRecord = $thisRecord;
          continue;
        }
        else {
          //compare arrays; if not different, delete thisRecord
          $diff = array_diff_assoc($lastRecord, $thisRecord);
          if ( empty($diff) ) {
            if ( $optlist['dryrun'] ) {
              $dryLog = array(
                'id' => $r['id'],
                'log_conn_id' => $r['log_conn_id'],
                'log_date' => $r['log_date'],
                'log_action' => $r['log_action'],
              );
              bbscript_log("info", 'Deleting log record:', $dryLog);
            }
            else {
              $deleteRows[] = "
                DELETE FROM {$logDB}.{$tbl}
                WHERE id = {$r['id']}
                  AND log_conn_id = {$r['log_conn_id']}
                  AND log_date = '{$r['log_date']}'
                  AND log_action = '{$r['log_action']}';
              ";
            }
            $stats[$tbl]++;
          }
          else {
            //bbscript_log("trace", "diff", $diff);
            //set lastRecord to current record if we didn't delete this record
            $lastRecord = $thisRecord;
          }
        }
      }

      //now delete records from this table
      if ( !empty($deleteRows) ) {
        $deleteCount = count($deleteRows);
        bbscript_log("info", "deleting {$deleteCount} log records for: {$tbl}...");

        foreach ( $deleteRows as $sql ) {
          CRM_Core_DAO::executeQuery($sql);
        }
      }

      //free DAO
      //$r->free();
    }//end table loop

    bbscript_log("info", 'final stats: ', $stats);
  }//run

}//end class

//run the script
$cleanLogs = new CRM_cleanLogs();
$cleanLogs->run();
