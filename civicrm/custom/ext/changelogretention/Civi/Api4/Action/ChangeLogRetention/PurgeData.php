<?php

namespace Civi\Api4\Action\ChangeLogRetention;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use \CRM_Core_Config;
use PHPUnit\Exception;

/**
 * purge change log entries older than specified retention interval
 */
class PurgeData extends AbstractAction {

  /**
   * How far back to keep change log entries, e.g. -7 years, 18 months ago.
   * Interval must be compatible with PHP strtotime(), and must be in the past.
   * Therefore, a minus sign (-) or "ago" is necessary for year or month intervals.
   * @var string
   */
  protected ?string $retention_interval = null;

  /**
   * Failsafe to avoid accidental deletion of data newer than 5 years.
   * @var bool
   */
  protected bool $force = false;

  /**
   * Limit the number of rows deleted in each logging table. Non-deterministic. 
   * Will unpredictably (in no particular order) choose rows for deletion.
   * @var int
   * Note -- LIMIT and ORDER BY are not allowed in DELETE clauses using JOIN
   * eliminating for now... if absolutely necessary can probably use WITH()
   * protected ?int $limit = null;
   */

  /**
   * Don't actually delete anything just report how many rows would be deleted
   * from each table
   * @var bool
   */
  protected bool $report_only = false;

  /**
   * Whether to write extra debugging info to log file
   * @var bool
   */
  protected bool $log_output = false;

  /**
   * When true, also attempts to purge records older than $retention_interval from
   * processed tables.
   * @var bool
   */
  protected bool $include_civicrm_log_table = false;

  /** @var Todays Timestamp*/
  private $_today_ts;
  /** @var data older than this timestamp will be purged */
  private int $_retention_threshold_ts;
  /** @var string database name -- should be set to logging database */
  private string $_db_name;

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {

    if (!\CRM_Core_Config::singleton()->logging) {
      throw new \CRM_Core_Exception('Logging must be enabled in order to execute the purge data action.');
    }

    // Will also put message in special log file if log_output is set.
    $this->_logOutput('Change Log Retention - Purge Data API action started', [
      'retention_interval' => $this->retention_interval,
      'force' => $this->force,
      'report_only' => $this->report_only,
      'log_output' => $this->log_output,
    ], true);

    $dsn = defined('CIVICRM_LOGGING_DSN') ? \DB::parseDSN(CIVICRM_LOGGING_DSN) : \DB::parseDSN(CIVICRM_DSN);
    $this->_db_name = $dsn['database'];

    $this->_today_ts = strtotime('today');
    $this->_retention_threshold_ts = $this->_getRetentionThresholdTimestamp();

    // Validate Retention Period
    // Must be in the past and force must be specified if less than 5 years ago.
    if ((! $this->_retention_threshold_ts) || (! is_numeric($this->_retention_threshold_ts))) {
      throw new \CRM_Core_Exception('Retention threshold is invalid.');
    }
    if ($this->_retention_threshold_ts >= $this->_today_ts) {
      throw new \CRM_Core_Exception('Retention threshold must be in the past.');
    }
    if ($this->_retention_threshold_ts >= strtotime('5 years ago') &&
        (! $this->force)) {
      throw new \CRM_Core_Exception('Use force option with retention intervals less than 5 years ago.');
    }

    // Get List of Excluded database tables
    $tables_to_purge = $this->_getIncludedTables();
    $excluded_tables = $this->_getExcludedTables();

    $this->_logOutput("Processing Tables:", ['included' => $tables_to_purge, 'excluded' => $excluded_tables]);

    foreach ($tables_to_purge as $table) {
      if ($this->report_only) {
        $this->_logOutput("report_only flag is set. Reporting count to be purged from table: $table ", null);
        $count = $this->_reportLogTable($table);
        $table_result = [
          'table' => $table,
          'count' => $count,
          'retention_threshold' => date('Y-m-d H:i:s', $this->_retention_threshold_ts),
        ];
        // If include_civicrm_log_table flag is set, then purge civicrm_log
        if ($this->include_civicrm_log_table) {
          $count_civicrm_log = $this->_reportCivicrmLog($table);
          $table_result['civicrm_log_count'] = $count_civicrm_log;
        }
        $result[] = $table_result;
        $this->_logOutput("purge report results for table: $table ", ['count'=>$count]);
      } else {
        $this->_logOutput("Start Purging Log Table: $table ", null);
        $count = $this->_purgeLogTable($table);
        $table_result = [
          'table' => $table,
          'count' => $count,
          'retention_threshold' => date('Y-m-d H:i:s', $this->_retention_threshold_ts),
        ];
        $details = ['count_deleted'=>$count];
        // If include_civicrm_log_table flag is set, then purge civicrm_log
        if ($this->include_civicrm_log_table) {
          $count_civicrm_log = $this->_purgeCivicrmLog($table);
          $table_result['civicrm_log_count'] = $count_civicrm_log;
          $details["civicrm_log_count_deleted"] = $count_civicrm_log;
        }
        $result[] = $table_result;
        $this->_storeRetentionLog($table,json_encode($details));
        $this->_logOutput("Finished Purging Log Table: $table ", ['details' => $details]);
      }
    }
    return $result;
  }

  private function _purgeLogTable($table_name) {
    if (! $this->_db_name) {
      throw new \CRM_Core_Exception('Logging database name is required to perform data purge');
    }

    $loggingDB = $this->_db_name;
    $params = [
      1 => [$this->_getFormattedRetentionDate(), 'String' ]
    ];

    $sql = "
    DELETE main FROM `{$loggingDB}`.$table_name main
    LEFT JOIN (
      SELECT sub.id, MAX(sub.log_date) AS latest_log_date
        FROM `{$loggingDB}`.$table_name sub
       WHERE sub.log_date < %1
    GROUP BY sub.id
    ) AS latest
    ON main.id = latest.id AND main.log_date = latest.latest_log_date
    WHERE main.log_date < %1
    AND latest.id IS NULL ";

    $dao = \CRM_Core_DAO::executeQuery($sql, $params);
    return $dao->affectedRows();
  }

  private function _purgeCivicrmLog($table_name) {
    $params = [
      1 => [$this->_getFormattedRetentionDate(), 'String' ],
      2 => [preg_replace('/^log_/', '', $table_name), 'String']
    ];
    $sql = "DELETE FROM civicrm_log
            WHERE modified_date < %1
              AND entity_table = %2";

    $this->_logOutput("Purge civicrm_log SQL", ['sql' => $sql]);
    $dao = \CRM_Core_DAO::executeQuery($sql, $params);
    return $dao->affectedRows();
  }

  private function _reportLogTable($table_name) {
    if (! $this->_db_name) {
      throw new \CRM_Core_Exception('Logging database name is required to provide data purge report');
    }
    $loggingDB = $this->_db_name;
    $params = [
      1 => [$this->_getFormattedRetentionDate(), 'String' ]
    ];

    $cnt = \CRM_Core_DAO::singleValueQuery("
        SELECT COUNT(1) AS cnt FROM (
            SELECT main.id, main.log_date
            FROM `{$loggingDB}`.$table_name main
            LEFT JOIN (
              SELECT sub.id, max(sub.log_date) AS latest_log_date
              FROM `{$loggingDB}`.$table_name sub
              WHERE sub.log_date < %1
              GROUP BY sub.id
            ) AS latest
            ON main.id = latest.id AND main.log_date = latest.latest_log_date
            WHERE main.log_date < %1
              AND latest.id IS NULL
            ORDER BY main.log_date ASC
        ) as a
      ", $params);

    return $cnt;
  }

  private function _reportCivicrmLog($table) {
    $params = [
      1 => [$this->_getFormattedRetentionDate(), 'String' ],
      2 => [preg_replace('/^log_/', '', $table), 'String']
    ];
    $sql = "SELECT COUNT(1) FROM (
                SELECT 1 FROM civicrm_log
                WHERE modified_date < %1
                AND entity_table = %2
            ) as a";
    $cnt = \CRM_Core_DAO::singleValueQuery($sql, $params);
    $this->_logOutput("Report civicrm_log SQL", ['sql' => $sql]);
    return $cnt;
  }

  private function _logOutput($label, $var = null, $also_standard_log = false) {
    if ($this->log_output) {
      \CRM_Core_Error::debug_var($label, $var, TRUE, TRUE, 'logretention');
      if ($also_standard_log) {
        \Civi::log()->debug($label, $var);
      }
    }
  }

  private function _getFormattedRetentionDate() {
    return date('Y-m-d H:i:s',$this->_retention_threshold_ts);
  }

  private function _getExcludedTables() {
    $tables_excluded = \Civi::settings()->get('tables_excluded');
    if (empty($tables_excluded)) {
      return [];
    }
    return $tables_excluded;
  }

  private function _getIncludedTables() {
    // Get List of excluded Tables
    $tables_excluded = $this->_getExcludedTables();
    // Get List of all Tables
    $schema = new \CRM_Logging_Schema();
    $all_tables = $schema->getLogTableNames();
    $tables_included = array_diff($all_tables, $tables_excluded);
    return $tables_included;
  }

  private function _getRetentionThresholdTimestamp() {
    // if override is provided in API call, use that.
    if (isset($this->retention_interval)) {
      return strtotime($this->retention_interval);
    }
    // Otherwise, default to Civi Settings.
    $retention_period_settings = \Civi::settings()->get('retention_period');
    if (is_numeric($retention_period_settings) && $retention_period_settings > 0) {
      $this->_logOutput("Using Retention Period Setting: $retention_period_settings ", []);
      return strtotime("$retention_period_settings months ago");
    }
    return false;
  }

  private function _storeRetentionLog($table, $details) {
    if (! $this->_db_name) {
      throw new \CRM_Core_Exception('Logging database name is required to provide data purge report');
    }
    $loggingDB = $this->_db_name;

    \CRM_Core_DAO::executeQuery("
    INSERT INTO `{$loggingDB}`.civicrm_logretention_log
    (action, log_table, details, action_date)
    VALUES
    (%1, %2, %3, %4)
  ", [
      1 => ['purge', 'String'],
      2 => [$table, 'String'],
      3 => [$details, 'String'],
      4 => [date('Y-m-d H:i:s',$this->_today_ts), 'String'],
    ]);
  }
}
