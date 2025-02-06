<?php

/**
 * Job.logretention API specification (optional)
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_job_logretention_spec(&$params) {
  $params['interval'] = [
    'title' => 'Retention Interval',
    'description' => 'How long to retain logged data, e.g. -7 years, 18 months ago. Interval must be compatible with PHP strtotime(), and must be in the past. Therefore, a minus sign (-) or "ago" is necessary for year or month intervals. Data OLDER than the retention period will be DELETED.'
  ];
  $params['force'] = [
    'title' => 'Force',
    'description' => ' Failsafe to avoid accidental deletion of data newer than 5 years.'
  ];
  $params['reportonly'] = [
    'title' => 'Report Only',
    'description' => 'Do not delete. Only report on what would be deleted.'
  ];

  $params['limit'] = [
    'title' => 'Limit',
    'description' => 'Limit how many rows per table are deleted.',
  ];

  $params['logoutput'] = [
    'title' => 'Log Output',
    'description' => 'If TRUE, details about the log purging process will be logged to a CiviCRM logging file with the logretention prefix. This can be useful for debugging and to track progress.',
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

  $retention_period_ts = _getRetentionThresholdTimestamp($params);
  $formatted_period = _getFormattedRetentionDate($retention_period_ts);

  if ((! $retention_period_ts) || (! is_numeric($retention_period_ts))) {
    return civicrm_api3_create_error('Retention threshold is invalid.');
  }
  if ($retention_period_ts >= strtotime("today")) {
    return civicrm_api3_create_error('Retention threshold must be in the past.');
  }
  if ($retention_period_ts >= strtotime('5 years ago') &&
    (! $params['force'])) {
    return civicrm_api3_create_error('Use force option with retention intervals less than 5 years ago.');
  }

  //$ages_ago = date('Y-m-d H:i:s', $retention_period_ts);

  // build _logTables for custom tables
  $logTables = _getIncludedTables();
  /* saving this logic to ask Brian about...
  $customTables = $schema->entityCustomDataLogTables('Contact'); ???
  $logTables = $logTables + $customTables;
  */
  //$endPremature = FALSE;
  $results = [];
  foreach ($logTables as $table) {
    if ($params['reportonly']) {
      _logOutput("report_only flag is set. Reporting count to be purged from table: $table ", null);
      try {
        $count = _reportLogTable($table,$retention_period_ts,$params['limit'], $loggingDB);
        $result[] = [ 'table' => $table, 'count' => $count,
          'retention_threshold' => date('Y-m-d H:i:s', $retention_period_ts),
          'limit' => $params['limit']];
        $results[] = ['table' => $table, 'count_deleted' => $count];
      } catch (Exception $e) {
        return civicrm_api3_create_error($e->getMessage());
      }
      _logOutput("purge report results for table: $table ", ['count'=>$count]);
    } else {
      _logOutput("Start Purging Log Table: $table ", null);
      try {
        $count = _purgeLogTable($table,$retention_period_ts,$params['limit'], $loggingDB);
        $result[] = [ 'table' => $table, 'count' => $count,
          'retention_threshold' => date('Y-m-d H:i:s', $retention_period_ts),
          'limit' => $params['limit']];
        _storeRetentionLog($loggingDB,$table);
        $results[] = ['table' => $table, 'count_deleted' => $count];
      } catch (Exception $e) {
        return civicrm_api3_create_error($e->getMessage());
      }
      _logOutput("Finished Purging Log Table: $table ", ['count'=>$count]);
      //$details = ['count_deleted'=>$count];
      //_logOutput("Finished Purging Log Table: $table ", ['details' => $details]);
    }
  }
  return civicrm_api3_create_success($results);
}

function _logOutput($label, $var = NULL) {
  if (defined('LOGOUTPUT') && LOGOUTPUT) {
    CRM_Core_Error::debug_var($label, $var, TRUE, TRUE, 'logretention');
  }
}

function _getLimitClause($limit) {
  if ($limit && $limit > 0) {
    return "LIMIT ".$limit;
  } else {
    return '';
  }
}

function _getFormattedRetentionDate(int $timestamp) {
  return date('Y-m-d H:i:s',$timestamp);
}

function _getRetentionThresholdTimestamp($params = []) {
  // if override is provided in API call, use that.
  if (isset($params['interval'])) {
    return strtotime($params['interval']);
  }
  // Otherwise, default to Civi Settings.
  $retention_period_settings = \Civi::settings()->get('retention_period');
  if (is_numeric($retention_period_settings) && $retention_period_settings > 0) {
    _logOutput("Using Retention Period Setting: $retention_period_settings ");
    return strtotime("$retention_period_settings months ago");
  }
  return false;
}

function _purgeLogTable($table_name, int $retention_period, $limit = 0, $db_name = '') {

  if (! $db_name) {
    throw new \CRM_Core_Exception('Logging database name is required to perform data purge');
  }

  $loggingDB = $db_name;
  $limit_clause = _getLimitClause($limit);
  $params = [
    1 => [_getFormattedRetentionDate($retention_period), 'String' ]
  ];

  $sql = "
    DELETE FROM `{$loggingDB}`.$table_name main 
    WHERE main.log_date < %1 
      AND (main.id, main.log_date) NOT IN 
            (
              SELECT sub_id, sub_log_date FROM
                (
                  SELECT sub.id as sub_id, max(sub.log_date) as sub_log_date
                  FROM `{$loggingDB}`.$table_name sub
                  WHERE sub.log_date < %1
                  GROUP BY sub.id
                ) xtra
            ) 
     ORDER BY main.log_date ASC
     $limit_clause
  ";
  $dao = \CRM_Core_DAO::executeQuery($sql, $params);
  return $dao->affectedRows();
}

function _reportLogTable($table_name, int $retention_period, $limit = 0, $db_name = '') {

  if (! $db_name) {
    throw new \CRM_Core_Exception('Logging database name is required to provide data purge report');
  }
  $loggingDB = $db_name;
  $limit_clause = _getLimitClause($limit);
  $params = [
    1 => [_getFormattedRetentionDate($retention_period), 'String' ]
  ];

  $cnt = \CRM_Core_DAO::singleValueQuery("
        SELECT COUNT(1) AS cnt FROM `{$loggingDB}`.$table_name main 
         WHERE main.log_date < %1 
           AND (main.id, main.log_date) NOT IN (
              SELECT sub.id, max(sub.log_date)
              FROM `{$loggingDB}`.$table_name sub
              WHERE sub.log_date < %1
              GROUP BY sub.id
              ) 
       ORDER BY main.log_date ASC
       $limit_clause
      ", $params);

  return $cnt;
}

function _storeRetentionLog($db, $table, $id = 0, $completed = 1) {
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

function _getExcludedTables() {
  $tables_excluded = \Civi::settings()->get('tables_excluded');
  if (empty($tables_excluded)) {
    return [];
  }
  array_walk($tables_excluded, function (&$value, $key) {
    $value = 'log_'.$value;
  });
  return $tables_excluded;
}

function _getIncludedTables() {
  // Get List of excluded Tables
  $tables_excluded = _getExcludedTables();
  // Get List of all Tables
  $schema = new \CRM_Logging_Schema();
  $all_tables = $schema->getLogTableNames();
  $tables_included = array_diff($all_tables, $tables_excluded);
  return $tables_included;
}
