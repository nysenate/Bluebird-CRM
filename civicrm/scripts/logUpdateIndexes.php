<?php

// Project: BluebirdCRM
// Authors: Brian Shaughnessy
// Organization: New York State Senate
// Date: 2014-03-24

// ./logUpdateIndexes.php -S district --dryrun
error_reporting(E_ERROR | E_PARSE | E_WARNING);
set_time_limit(0);

define('DEFAULT_LOG_LEVEL', 'TRACE');

class CRM_updateIndexes {

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

    bbscript_log(LL::INFO, 'Initiating log table cleanup...');

    //get instance settings
    $bbcfg = get_bluebird_instance_config($optlist['site']);
    //bbscript_log(LL::TRACE, "bbcfg", $bbcfg);

    $civicrm_root = $bbcfg['drupal.rootdir'].'/sites/all/modules/civicrm';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    if (!CRM_Utils_System::loadBootstrap(array(), FALSE, FALSE, $civicrm_root)) {
      CRM_Core_Error::debug_log_message('Failed to bootstrap CMS from cleanLogs.');
      return FALSE;
    }

    $logDB = $bbcfg['db.log.prefix'].$bbcfg['db.basename'];

    //check if table is passed
    if ( $optlist['tbl'] ) {
      $tbl = $optlist['tbl'];

      //check prefix as log_ table or core table could be passed
      if ( strpos($tbl, 'log_') !== 0 ) {
        $tbl = 'log_'.$tbl;
      }
      $indexes = self::getIndexes($logDB, $tbl);
    }
    else {
      $indexes = self::getIndexes($logDB);
    }
    //bbscript_log(LL::DEBUG, 'indexes to be processed:', $indexes);

    if ( empty($indexes) ) {
      echo "no indexes selected for updating. if you have passed a table as a parameter to the script, make sure it is a table for which we generate indexes.";
      exit();
    }

    /*$memUse = memory_get_usage();
    $memUseMB = round($memUse/1048576,2);
    bbscript_log(LL::TRACE, "Memory usage before cycling through indexes: {$memUseMB} M");*/

    foreach ( $indexes as $tbl => $indexList ) {
      bbscript_log(LL::INFO, "examining indexes for {$tbl}...");
      $existingIndexes = $indexSql = array();

      //get all indexes for the given table and build array
      $sql = "
        SELECT index_name
        FROM information_schema.statistics
        WHERE table_schema = '{$logDB}'
          AND table_name = '{$tbl}';
      ";
      $r = CRM_Core_DAO::executeQuery($sql);

      while ( $r->fetch() ) {
        $existingIndexes[] = $r->index_name;
      }
      //bbscript_log(LL::TRACE, 'existing indexes:', $existingIndexes);

      //foreach index, check to see if it already exists, and if not, create it
      foreach ($indexList as $index => $sql) {
        if ( !in_array($index, $existingIndexes) ) {
          bbscript_log(LL::INFO, "creating index {$index} on {$tbl}...");

          if ( !empty($sql) ) {
            CRM_Core_DAO::executeQuery($sql);
          }
          else {
            $indexSql[] = "ADD INDEX `{$index}` (`{$index}`)";
          }
        }
      }

      //run collected sql
      if ( !empty($indexSql) ) {
        $sql = "ALTER TABLE {$logDB}.{$tbl} ".implode(', ', $indexSql);
        //bbscript_log(LL::TRACE, "collected index creation sql:", $sql);

        CRM_Core_DAO::executeQuery($sql);
      }

      /*$memUse = memory_get_usage();
      $memUseMB = round($memUse/1048576,2);
      bbscript_log(LL::TRACE, "Memory usage after processing {$tbl}: {$memUseMB} M");*/

      $r->free();
    }//end table loop
  }//run

  /*
   * return array containing all index data
   * format is:
   * table => index name => index sql
   * if index sql is absent, we assume standard construction
   *
   * if tbl is passed, we only return the indexes for that table
   */
  function getIndexes($logDB, $tbl = NULL) {
    $indexes = array(
      'log_civicrm_activity' => array(
        'id' => '',
        'source_record_id' => '',
        'activity_type_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_activity_contact' => array(
        'activity_id' => '',
        'contact_id' => '',
        'record_type_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_address' => array(
        'id' => '',
        'contact_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_contact' => array(
        'id' => '',
        'sort_name' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_dashboard_contact' => array(
        'contact_id' => '',
        'dashboard_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_email' => array(
        'contact_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_entity_tag' => array(
        'entity_id' => '',
        'entity_table' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_group' => array(
        'id' => '',
        'index_id_log_conn_id' => "ALTER TABLE {$logDB}.log_civicrm_group ADD INDEX index_id_log_conn_id (id, log_conn_id)",
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_group_contact' => array(
        'group_id' => '',
        'contact_id' => '',
        'index_id_log_date' => "ALTER TABLE {$logDB}.log_civicrm_group_contact ADD INDEX index_id_log_date (id, log_date)",
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_note' => array(
        'entity_id' => '',
        'entity_table' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_phone' => array(
        'contact_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_relationship' => array(
        'contact_id_a' => '',
        'contact_id_b' => '',
        'relationship_type_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_value_constituent_information_1' => array(
        'entity_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
      'log_civicrm_value_district_information_7' => array(
        'entity_id' => '',
        'log_date' => '',
        'log_conn_id' => '',
        'log_user_id' => '',
        'log_action' => '',
        'log_job_id' => '',
      ),
    );

    if ( $tbl ) {
      if ( array_key_exists($tbl, $indexes) ) {
        $tblIndexes = array($tbl => $indexes[$tbl]);
        return $tblIndexes;
      }
      else {
        return NULL;
      }
    }

    return $indexes;
  }//getIndexes
}//end class

//run the script
$script = new CRM_updateIndexes();
$script->run();
