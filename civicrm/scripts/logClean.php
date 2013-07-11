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
    $shortopts = "d";
    $longopts = array("dryrun");
    $optlist = civicrm_script_init($shortopts, $longopts);

    if ($optlist === null) {
      $stdusage = civicrm_script_usage();
      $usage = '[--dryrun]';
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
    $tbls = array(
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
      'log_civicrm_value_constituent_information_1',
      'log_civicrm_value_district_information_7',
    );

    $logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];
    $stats = array();

    // start db connection
    $conn = mysql_connect($bbcfg['db.host'], $bbcfg['db.user'], $bbcfg['db.pass'], TRUE);
    mysql_select_db($logDB, $conn);

    $memUse = memory_get_usage();
    $memUseMB = round($memUse/1048576,2);
    //bbscript_log("trace", "Memory usage before cycling through tables: {$memUseMB} M");

    foreach ( $tbls as $tbl ) {
      bbscript_log("info", "Processing {$tbl}...");

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

      $logCols = array(
        'log_date',
        'log_conn_id',
        'log_user_id',
        'log_action',
        'log_job_id',
        'last_run',
        'cache_date',
      );
      //$tblAttr = get_object_vars($r) + $logCols;
      $tblAttr = $logCols;

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
          if ( !in_array($f, $tblAttr) ) {
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
            /*$sql = "
              DELETE FROM {$logDB}.{$tbl}
              WHERE id = {$r['id']}
                AND log_conn_id = {$r['log_conn_id']}
                AND log_date = '{$r['log_date']}'
                AND log_action = '{$r['log_action']}'
            ";*/
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
              $deleteRows[] = $r['id'];
              //CRM_Core_DAO::executeQuery($sql);
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
        bbscript_log("info", "...deleting {$deleteCount} log records for: {$tbl}");

        $deleteList = implode(',', $deleteRows);
        $sql = "
          DELETE FROM {$logDB}.{$tbl}
          WHERE id IN ({$deleteList})
        ";
        CRM_Core_DAO::executeQuery($sql);
      }

      //free DAO
      //$r->free();
    }//end table loop

    bbscript_log("info", 'Final stats: ', $stats);
  }//run

}//end class

//run the script
$cleanLogs = new CRM_cleanLogs();
$cleanLogs->run();
