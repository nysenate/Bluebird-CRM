<?php

/**
 * Job.logretention API specification (optional)
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_job_logretention_spec(&$params) {
  $params['limit'] = [
    'title' => 'Limit',
    'description' => 'Limit how many rows per table are processed.',
  ];
  $params['logoutput'] = [
    'title' => 'Log Output',
    'description' => 'If set to 1, details about the log purging process will be logged to a CiviCRM logging file with the logretention prefix. This can be useful for debugging and to track progress.',
  ];
}

/**
 * Job.logretention API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_logretention($params) {
  if (!CRM_Core_Config::singleton()->logging) {
    return civicrm_api3_create_error('Logging must be enabled in order to use this API.');
  }

  if (CRM_Utils_Array::value('logoutput', $params)) {
    define('LOGOUTPUT', TRUE);
  }
  _logOutput('params', $params);

  $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
  $loggingDB = $dsn['database']; //logging database
  $retention_period = Civi::settings()->get('retention_period');
  if(!isset($retention_period) || empty($retention_period)){
    return civicrm_api3_create_error('Please set a retention period value in the Log Retention Settings before using this API.');
  }
  $retention_unit = 'month';
  if($retention_period > 1){
    $retention_unit = 'months';
  }
  $retentionPeriod = $retention_period .' '.$retention_unit;
  $ages_ago = date('Y-m-d H:i:s', strtotime("today - $retentionPeriod"));

  $schema = new CRM_Logging_Schema();
  $tables = $schema->getLogTableSpec();

  // build _logTables for custom tables
  $customTables = $schema->entityCustomDataLogTables('Contact');
  $logTables = [];
  $excludeLogTables = [];
  foreach($tables as $key=>$value) {
    if($value['engine'] == 'INNODB'){
       $logTables[] = 'log_'.$key;
    }
    else{
      $excludeLogTables[] = 'log_'.$key;
    }
  }
  $logTables = $logTables + $customTables;

  $paramsCommon = [
    1 => [$ages_ago, 'String'],
  ];
  //Civi::log()->debug('logretention', ['$paramsCommon' => $paramsCommon]);

  //get tables excluded from log retention
  $tables_excluded = Civi::settings()->get('tables_excluded');
  if (empty($tables_excluded)){
    $tables_excluded = [];
  }

  $endPremature = FALSE;
  foreach($logTables as $table){
    if (!in_array($table, $tables_excluded)){
      _logOutput('table', $table);

      $dateNow = date('Y-m-d H:i:s');
      $dateYesterday = date('Y-m-d H:i:s', strtotime('-1 days'));

      $tableCompleted = CRM_Core_DAO::singleValueQuery("
        SELECT id
        FROM `{$loggingDB}`.civicrm_logretention_log
        WHERE log_date BETWEEN '{$dateYesterday}' AND '{$dateNow}'
          AND log_table = '{$table}'
          AND log_completed = 1
        ORDER BY id DESC
        LIMIT 1
      ");
      if ($tableCompleted) {
        continue;
      }

      //check logretention_log to see if we need to pick up where we left off
      $lastLog = CRM_Core_DAO::singleValueQuery("
        SELECT log_id
        FROM `{$loggingDB}`.civicrm_logretention_log
        WHERE log_date BETWEEN '{$dateYesterday}' AND '{$dateNow}'
          AND log_table = '{$table}'
          AND log_completed = 0
        ORDER BY id DESC
        LIMIT 1
      ");
      _logOutput('$dateNow', $dateNow);
      _logOutput('$dateYesterday', $dateYesterday);
      _logOutput('$lastLog', $lastLog);
      $lastLogSql = (!empty($lastLog)) ? "AND id > {$lastLog}" : '';

      //get log row IDs with at least one log outside the retention window
      $limit = CRM_Utils_Array::value('limit', $params);
      $entity_id = "
        SELECT id
        FROM `{$loggingDB}`.$table
        WHERE log_date < %1
          {$lastLogSql}
        GROUP BY id
      ";
      _logOutput('entity_id', $entity_id);
      $entity_data = CRM_Core_DAO::executeQuery($entity_id, $paramsCommon);

      $i = $x = 0;
      while ($entity_data->fetch()) {
        $id = $entity_data->id;

        $daoMaxDate = "
          SELECT max(log_date) as max_log_date
          FROM `{$loggingDB}`.$table
          WHERE log_date < %1
            AND id = $id
          LIMIT 1
        ";
        $max_log_date = CRM_Core_DAO::singleValueQuery($daoMaxDate, $paramsCommon);

        if ($max_log_date) {
          $sql = "
            DELETE FROM `{$loggingDB}`.$table 
            WHERE log_date < %1 
              AND log_date <> '$max_log_date' 
              AND id = $id
          ";
          //Civi::log()->debug('logretention', ['max_log_date' => $max_log_date, 'sql' => $sql]);
          CRM_Core_DAO::executeQuery($sql, $paramsCommon);

          $sql = "
            DELETE FROM civicrm_log
            WHERE modified_date < %1
              AND modified_date <> '$max_log_date'
              AND entity_id = $id
            ";
          CRM_Core_DAO::executeQuery($sql, $paramsCommon);

          $i++;
          $x++;
          if ($i % 250 == 0) {
            _logOutput('i', $i);
            _storeRetentionLog($loggingDB, $table, $id);
          }
        }

        if (!empty($limit) && $x >= $limit) {
          $endPremature = TRUE;
          continue 2;
        }
      }

      //if we didn't end prematurely due to limit, set completed
      if (!$endPremature) {
        _storeRetentionLog($loggingDB, $table, 0, 1);
      }
    }
  }
  return civicrm_api3_create_success("Deleted log entries that were older than {$retentionPeriod} months.");
}

function _logOutput($label, $var) {
  if (defined('LOGOUTPUT') && LOGOUTPUT) {
    CRM_Core_Error::debug_var($label, $var, TRUE, TRUE, 'logretention');
  }
}

function _storeRetentionLog($db, $table, $id, $completed = 0) {
  CRM_Core_DAO::executeQuery("
    INSERT INTO `{$db}`.civicrm_logretention_log
    (log_table, log_id, log_completed)
    VALUES
    (%1, %2, %3)
  ", [
    1 => [$table, 'String'],
    2 => [$id, 'Positive'],
    3 => [$completed, 'Integer'],
  ]);
}
