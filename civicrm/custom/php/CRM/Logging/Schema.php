<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Logging_Schema {
  private $logs = array();
  private $tables = array();

  private $db;
  private $primary_db;
  private $useDBPrefix = TRUE;

  private $reports = array(
    'logging/contact/detail',
    'logging/contact/summary',
    'logging/contribute/detail',
    'logging/contribute/summary',
  );

  //CRM-13028 / NYSS-6933 - table => array (cols) - to be excluded from the update statement
  private $exceptions = array(
    'civicrm_job'   => array('last_run'),
    'civicrm_group' => array('cache_date'),
  );
  
  // to cache results of fetchTableList
  static $_fetch_cache = array();
  
  // default trigger filters
  static $_trigger_filters = array(
                                  // do not log temp import, cache and log tables
                                  array('/^civicrm_import_job_/',PREG_GREP_INVERT),
                                  array('/_cache$/',PREG_GREP_INVERT),
                                  array('/_log/',PREG_GREP_INVERT),
                                  array('/^civicrm_task_action_temp_/',PREG_GREP_INVERT),
                                  array('/^civicrm_export_temp_/',PREG_GREP_INVERT),
                                  array('/^civicrm_queue_/',PREG_GREP_INVERT),
                                  // do not log civicrm_mailing_event* tables, CRM-12300
                                  array('/^civicrm_mailing_event_/',PREG_GREP_INVERT),
                                  //NYSS 6560 add other tables to exclusion list
                                  array('/^civicrm_menu/',PREG_GREP_INVERT),
                                  // do not log civicrm_changelog_summary (delta logging) #7893
                                  array('/_changelog_/',PREG_GREP_INVERT),
                                  // do not log new sequence tables #7893
                                  array('/_sequence$/',PREG_GREP_INVERT),
                                  );

  /**
   * Populate $this->tables and $this->logs with current db state.
   */
  function __construct() {
    // does a distinct logging DSN exist?
    $this->setUsePrefix();

    // get the primary civi database
    $this->primary_db = self::getDatabaseName(false);

    // fetch the list of tables
    $this->tables = self::fetchTableList($this->primary_db);
    
    // filter the tables
    $this->tables = self::filterTriggerTables($this->tables);

    // get the logging database name
    $this->db = self::getDatabaseName();
    
    // fetch the list of logging tables and "correct" it
    $all_logs = self::fetchTableList($this->db, 'log_civicrm_%');

    foreach ($all_logs as $v) {
      $this->logs[substr($v, 4)] = $v;
    }
  }
  
  public function setUsePrefix() {
    if ((defined('CIVICRM_LOGGING_DSN')) && (CIVICRM_LOGGING_DSN != CIVICRM_DSN)) {
      $this->useDBPrefix = true;
    } else {
      $this->useDBPrefix = false;
    }
  }

  /**
   * Get the name of the primary civi database
   */
  public static function getDatabaseName($logdb = true) {
    if ($logdb && defined('CIVICRM_LOGGING_DSN')) {
      $dsn = CIVICRM_LOGGING_DSN;
    } else {
      $dsn = CIVICRM_DSN;
    }
    $ret = DB::parseDSN($dsn);
    return $ret['database'];
  }
  
  /**
   * Retrieve a list of tables from the database
   */
  public static function fetchTableList($schema='', $filter='civicrm_%') {

    if (!isset(self::$_fetch_cache)) {
      self::$_fetch_cache = array();
    }
    
    if (!isset(self::$_fetch_cache[$filter])) {
      // initialize 
      $target = array();
      
      // generate the base SQL
      $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = %1 " .
             "AND TABLE_TYPE = 'BASE TABLE'";
             
      // create the parameter array
      $params = array(1 => array($schema,'String'));
  
      // add the filter, if present
      if ($filter) { 
        $sql .= " AND TABLE_NAME LIKE %2"; 
        $params[2] = array($filter,'String');
      }
  
      // fetch the table names
      $dao = CRM_Core_DAO::executeQuery("{$sql};", $params);
      
      // add each table name to the array
      while ($dao->fetch()) {
        $target[] = $dao->TABLE_NAME;
      }
      self::$_fetch_cache[$filter] = $target;
    }
      
    return self::$_fetch_cache[$filter];
  }
  
  /**
   * Remove all non-trigger tables
   * $tables should be a one-dimensional array of table names
   */
  public static function filterTriggerTables($tables = array(), $filters = array()) {
    // if no filters were passed, use the default filters
    if (!(is_array($filters) && count($filters))) {
      $filters = self::$_trigger_filters;
    }
    
    // standardize the input
    if (!is_array($tables)) { $tables = array((string)$tables); }
    
    // run the filters
    foreach ($filters as $one_filter) {
      $tables = preg_grep($one_filter[0], $tables, $one_filter[1]);
    }
    
    return $tables;
  }

  /**
   * Return logging custom data tables.
   */
  function customDataLogTables() {
    return preg_grep('/^log_civicrm_value_/', $this->logs);
  }

  /**
   * Return custom data tables for specified entity / extends.
   */
  function entityCustomDataLogTables($extends) {
    $customGroupTables = array();
    $customGroupDAO = CRM_Core_BAO_CustomGroup::getAllCustomGroupsByBaseEntity($extends);
    $customGroupDAO->find();
    while ($customGroupDAO->fetch()) {
      $customGroupTables[$customGroupDAO->table_name] = $this->logs[$customGroupDAO->table_name];
    }
    return $customGroupTables;
  }

  /**
   * Disable logging by dropping the triggers (but keep the log tables intact).
   */
  function disableLogging() {
    $config = CRM_Core_Config::singleton();
    $config->logging = FALSE;

    $this->dropTriggers();

    // invoke the meta trigger creation call
    CRM_Core_DAO::triggerRebuild();

    $this->deleteReports();
  }

  /**
   * Drop triggers for all logged tables.
   */
  function dropTriggers($tableName = NULL) {
    $dao = new CRM_Core_DAO;

    if ($tableName) {
      $tableNames = array($tableName);
    }
    else {
      $tableNames = $this->tables;
    }

    foreach ($tableNames as $table) {
      $validName = CRM_Core_DAO::shortenSQLName($table, 48, TRUE);

      // before triggers
      $dao->executeQuery("DROP TRIGGER IF EXISTS {$validName}_before_insert");
      $dao->executeQuery("DROP TRIGGER IF EXISTS {$validName}_before_update");
      $dao->executeQuery("DROP TRIGGER IF EXISTS {$validName}_before_delete");

     // after triggers
      $dao->executeQuery("DROP TRIGGER IF EXISTS {$validName}_after_insert");
      $dao->executeQuery("DROP TRIGGER IF EXISTS {$validName}_after_update");
      $dao->executeQuery("DROP TRIGGER IF EXISTS {$validName}_after_delete");
    }

    // now lets also be safe and drop all triggers that start with
    // civicrm_ if we are dropping all triggers
    // we need to do this to capture all the leftover triggers since
    // we did the shortening trigger name for CRM-11794
    if ($tableName === NULL) {
      $triggers = $dao->executeQuery("SHOW TRIGGERS LIKE 'civicrm_%'");

      while ($triggers->fetch()) {
        // note that drop trigger has a wierd syntax and hence we do not
        // send the trigger name as a string (i.e. its not quoted
        $dao->executeQuery("DROP TRIGGER IF EXISTS {$triggers->Trigger}");
      }
    }
  }

  /**
   * Enable sitewide logging.
   *
   * @return void
   */
  function enableLogging() {
    $this->fixSchemaDifferences(TRUE);
    $this->addReports();
  }

  /**
   * Sync log tables and rebuild triggers.
   *
   * @param bool $enableLogging: Ensure logging is enabled
   *
   * @return void
   */
  function fixSchemaDifferences($enableLogging = FALSE) {
    $config = CRM_Core_Config::singleton();
    if ($enableLogging) {
      $config->logging = TRUE;
    }
    if ($config->logging) {
      $this->fixSchemaDifferencesForALL();
    }
    // invoke the meta trigger creation call
    CRM_Core_DAO::triggerRebuild(NULL, TRUE);
  }

  /**
   * Add missing (potentially specified) log table columns for the given table.
   *
   * @param $table string  name of the relevant table
   * @param $cols mixed    array of columns to add or null (to check for the missing columns)
   * @param $rebuildTrigger boolean should we rebuild the triggers
   *
   * @return void
   */
  function fixSchemaDifferencesFor($table, $cols = array(), $rebuildTrigger = FALSE) {
    if (empty($table)) {
      return FALSE;
    }
    if (empty($this->logs[$table])) {
      $this->createLogTableFor($table);
      return TRUE;
    }

    if (empty($cols)) {
      $cols = $this->columnsWithDiffSpecs($table, "log_$table");
    }

    // use the relevant lines from CREATE TABLE to add colums to the log table
    $create = $this->_getCreateQuery($table);
    foreach ((array('ADD', 'MODIFY')) as $alterType) {
      if (!empty($cols[$alterType])) {
        foreach ($cols[$alterType] as $col) {
          $line = $this->_getColumnQuery($col, $create);
          CRM_Core_DAO::executeQuery("ALTER TABLE `{$this->db}`.log_$table {$alterType} {$line}");
        }
      }
    }

    // for any obsolete columns (not null) we just make the column nullable.
    if (!empty($cols['OBSOLETE'])) {
      $create = $this->_getCreateQuery("`{$this->db}`.log_{$table}");
      foreach ($cols['OBSOLETE'] as $col) {
        $line = $this->_getColumnQuery($col, $create);
        // This is just going to make a not null column to nullable
        CRM_Core_DAO::executeQuery("ALTER TABLE `{$this->db}`.log_$table MODIFY {$line}");
      }
    }

    if ($rebuildTrigger) {
      // invoke the meta trigger creation call
      CRM_Core_DAO::triggerRebuild($table);
    }
    return TRUE;
  }

  private function _getCreateQuery($table) {
    $dao = CRM_Core_DAO::executeQuery("SHOW CREATE TABLE {$table}");
    $dao->fetch();
    $create = explode("\n", $dao->Create_Table);
    return $create;
  }

  private function _getColumnQuery($col, $createQuery) {
    $line = preg_grep("/^  `$col` /", $createQuery);
    $line = rtrim(array_pop($line), ',');
    // CRM-11179
    $line = $this->fixTimeStampAndNotNullSQL($line);
    return $line;
  }

  function fixSchemaDifferencesForAll($rebuildTrigger = FALSE) {
    $diffs = array();
    foreach ($this->tables as $table) {
      if (empty($this->logs[$table])) {
        $this->createLogTableFor($table);
      }
      else {
        $diffs[$table] = $this->columnsWithDiffSpecs($table, "log_$table");
      }
    }

    foreach ($diffs as $table => $cols) {
      $this->fixSchemaDifferencesFor($table, $cols, FALSE);
    }

    if ($rebuildTrigger) {
      // invoke the meta trigger creation call
      CRM_Core_DAO::triggerRebuild($table);
    }
  }

  /*
   * log_civicrm_contact.modified_date for example would always be copied from civicrm_contact.modified_date,
   * so there's no need for a default timestamp and therefore we remove such default timestamps
   * also eliminate the NOT NULL constraint, since we always copy and schema can change down the road)
   */
  function fixTimeStampAndNotNullSQL($query) {
    $query = str_ireplace("TIMESTAMP NOT NULL", "TIMESTAMP NULL", $query);
    $query = str_ireplace("DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", '', $query);
    $query = str_ireplace("DEFAULT CURRENT_TIMESTAMP", '', $query);
    $query = str_ireplace("NOT NULL", '', $query);
    return $query;
  }

  private function addReports() {
    $titles = array(
      'logging/contact/detail' => ts('Logging Details'),
      'logging/contact/summary' => ts('Contact Logging Report (Summary)'),
      'logging/contribute/detail' => ts('Contribution Logging Report (Detail)'),
      'logging/contribute/summary' => ts('Contribution Logging Report (Summary)'),
    );
    // enable logging templates
    CRM_Core_DAO::executeQuery("
            UPDATE civicrm_option_value
            SET is_active = 1
            WHERE value IN ('" . implode("', '", $this->reports) . "')
        ");

    // add report instances
    $domain_id = CRM_Core_Config::domainID();
    foreach ($this->reports as $report) {
      $dao             = new CRM_Report_DAO_ReportInstance;
      $dao->domain_id  = $domain_id;
      $dao->report_id  = $report;
      $dao->title      = $titles[$report];
      $dao->permission = 'administer CiviCRM';
      if ($report == 'logging/contact/summary')
        $dao->is_reserved = 1;
      $dao->insert();
    }
  }

  /**
   * Get an array of column names of the given table.
   */
  private function columnsOf($table, $force = FALSE) {
    static $columnsOf = array();

    $from = (substr($table, 0, 4) == 'log_') ? "`{$this->db}`.$table" : $table;

    if (!isset($columnsOf[$table]) || $force) {
      CRM_Core_Error::ignoreException();
      $dao = CRM_Core_DAO::executeQuery("SHOW COLUMNS FROM $from");
      CRM_Core_Error::setCallback();
      if (is_a($dao, 'DB_Error')) {
        return array();
      }
      $columnsOf[$table] = array();
      while ($dao->fetch()) {
        $columnsOf[$table][] = $dao->Field;
      }
    }

    return $columnsOf[$table];
  }

  /**
   * Get an array of columns and their details like DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT for the given table.
   */
  private function columnSpecsOf($table) {
    static $columnSpecs = array(), $civiDB = NULL;

    if (empty($columnSpecs)) {
      if (!$civiDB) {
        $dao = new CRM_Contact_DAO_Contact();
        $civiDB = $dao->_database;
      }
      CRM_Core_Error::ignoreException();
      // NOTE: W.r.t Performance using one query to find all details and storing in static array is much faster
      // than firing query for every given table.
      $query = "
SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM   INFORMATION_SCHEMA.COLUMNS
WHERE  table_schema IN ('{$this->db}', '{$civiDB}')";
      $dao = CRM_Core_DAO::executeQuery($query);
      CRM_Core_Error::setCallback();
      if (is_a($dao, 'DB_Error')) {
        return array();
      }
      while ($dao->fetch()) {
        if (!array_key_exists($dao->TABLE_NAME, $columnSpecs)) {
          $columnSpecs[$dao->TABLE_NAME] = array();
        }
        $columnSpecs[$dao->TABLE_NAME][$dao->COLUMN_NAME] =
          array(
              'COLUMN_NAME' => $dao->COLUMN_NAME,
              'DATA_TYPE'   => $dao->DATA_TYPE,
              'IS_NULLABLE' => $dao->IS_NULLABLE,
              'COLUMN_DEFAULT' => $dao->COLUMN_DEFAULT
            );
      }
    }
    return $columnSpecs[$table];
  }

  function columnsWithDiffSpecs($civiTable, $logTable) {
    $civiTableSpecs = $this->columnSpecsOf($civiTable);
    $logTableSpecs  = $this->columnSpecsOf($logTable);

    $diff = array('ADD' => array(), 'MODIFY' => array(), 'OBSOLETE' => array());

    // columns to be added
    $diff['ADD'] = array_diff(array_keys($civiTableSpecs), array_keys($logTableSpecs));

    // columns to be modified
    // NOTE: we consider only those columns for modifications where there is a spec change, and that the column definition
    // wasn't deliberately modified by fixTimeStampAndNotNullSQL() method.
    foreach ($civiTableSpecs as $col => $colSpecs) {
      if (!isset($logTableSpecs[$col]) || !is_array($logTableSpecs[$col]) ) {
        $logTableSpecs[$col] = array();
      }

      $specDiff = array_diff($civiTableSpecs[$col], $logTableSpecs[$col]);
      if (!empty($specDiff) && $col != 'id' && !array_key_exists($col, $diff['ADD'])) {
        // ignore 'id' column for any spec changes, to avoid any auto-increment mysql errors
        if ($civiTableSpecs[$col]['DATA_TYPE'] != CRM_Utils_Array::value('DATA_TYPE', $logTableSpecs[$col])) {
          // if data-type is different, surely consider the column
          $diff['MODIFY'][] = $col;
        } else if ($civiTableSpecs[$col]['IS_NULLABLE'] != CRM_Utils_Array::value('IS_NULLABLE', $logTableSpecs[$col]) &&
          $logTableSpecs[$col]['IS_NULLABLE'] == 'NO') {
          // if is-null property is different, and log table's column is NOT-NULL, surely consider the column
          $diff['MODIFY'][] = $col;
        } else if ($civiTableSpecs[$col]['COLUMN_DEFAULT'] != CRM_Utils_Array::value('COLUMN_DEFAULT', $logTableSpecs[$col]) &&
          !strstr($civiTableSpecs[$col]['COLUMN_DEFAULT'], 'TIMESTAMP')) {
          // if default property is different, and its not about a timestamp column, consider it
          $diff['MODIFY'][] = $col;
        }
      }
    }

    // columns to made obsolete by turning into not-null
    $oldCols = array_diff(array_keys($logTableSpecs), array_keys($civiTableSpecs));
    foreach ($oldCols as $col) {
      if (!in_array($col, array('log_date', 'log_conn_id', 'log_user_id', 'log_action')) &&
        $logTableSpecs[$col]['IS_NULLABLE'] == 'NO') {
        // if its a column present only in log table, not among those used by log tables for special purpose, and not-null
        $diff['OBSOLETE'][] = $col;
      }
    }

    return $diff;
  }

  /**
   * Create a log table with schema mirroring the given table’s structure and seeding it with the given table’s contents.
   */
  private function createLogTableFor($table) {
    $dao = CRM_Core_DAO::executeQuery("SHOW CREATE TABLE $table");
    $dao->fetch();
    $query = $dao->Create_Table;

    // rewrite the queries into CREATE TABLE queries for log tables:
    //NYSS handle job id
    $cols = <<<COLS
            log_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            log_conn_id INTEGER,
            log_user_id INTEGER,
            log_action  ENUM('Initialization', 'Insert', 'Update', 'Delete'),
            log_job_id VARCHAR (64) null
COLS;

    // - prepend the name with log_
    // - drop AUTO_INCREMENT columns
    // - drop non-column rows of the query (keys, constraints, etc.)
    // - set the ENGINE to ARCHIVE
    // - add log-specific columns (at the end of the table)
    $query = preg_replace("/^CREATE TABLE `$table`/i", "CREATE TABLE `{$this->db}`.log_$table", $query);
    $query = preg_replace("/ AUTO_INCREMENT/i", '', $query);
    $query = preg_replace("/^  [^`].*$/m", '', $query);
    $query = preg_replace("/^\) ENGINE=[^ ]+ /im", ') ENGINE=InnoDB ', $query);//NYSS

    // log_civicrm_contact.modified_date for example would always be copied from civicrm_contact.modified_date,
    // so there's no need for a default timestamp and therefore we remove such default timestamps
    // also eliminate the NOT NULL constraint, since we always copy and schema can change down the road)
    $query = self::fixTimeStampAndNotNullSQL($query);
    $query = preg_replace("/^\) /m", "$cols\n) ", $query);

    CRM_Core_DAO::executeQuery($query);

    $columns = implode(', ', $this->columnsOf($table));
    CRM_Core_DAO::executeQuery("INSERT INTO `{$this->db}`.log_$table ($columns, log_conn_id, log_user_id, log_action) SELECT $columns, CONNECTION_ID(), @civicrm_user_id, 'Initialization' FROM {$table}");

    $this->tables[] = $table;
    $this->logs[$table] = "log_$table";
  }

  private function deleteReports() {
    // disable logging templates
    CRM_Core_DAO::executeQuery("
            UPDATE civicrm_option_value
            SET is_active = 0
            WHERE value IN ('" . implode("', '", $this->reports) . "')
        ");

    // delete report instances
    $domain_id = CRM_Core_Config::domainID();
    foreach ($this->reports as $report) {
      $dao            = new CRM_Report_DAO_ReportInstance;
      $dao->domain_id = $domain_id;
      $dao->report_id = $report;
      $dao->delete();
    }
  }

  /**
   * Predicate whether logging is enabled.
   */
  public function isEnabled() {
    $config = CRM_Core_Config::singleton();

    if ($config->logging) {
      return $this->tablesExist() and $this->triggersExist();
    }
    return FALSE;
  }

  /**
   * Predicate whether any log tables exist.
   */
  private function tablesExist() {
    return !empty($this->logs);
  }

  /**
   * Predicate whether the logging triggers are in place.
   */
  private function triggersExist() {
    // FIXME: probably should be a bit more thorough…
    // note that the LIKE parameter is TABLE NAME
    return (bool) CRM_Core_DAO::singleValueQuery("SHOW TRIGGERS LIKE 'civicrm_domain'"); //NYSS
  }

  function triggerInfo(&$info, $tableName = NULL, $force = FALSE) {
    
    // check if we have logging enabled
    $config = &CRM_Core_Config::singleton();
    if (!$config->logging) {
      return;
    }
    
    // build the triggers for the delta log summary and detail tables #NYSS 7893
    self::nyssBuildSummaryTableTrigger($info);
    self::nyssBuildDetailTableTrigger($info);
    
    // prepare the trigger SQL for tables included in the delta log
    $this->nyssPrepareDeltaTriggers();

    $insert = array('INSERT');
    $update = array('UPDATE');
    $delete = array('DELETE');

    if ($tableName) {
      $tableNames = array($tableName);
    }
    else {
      $tableNames = $this->tables;
    }

    // logging is enabled, so now lets create the trigger info tables
    foreach ($tableNames as $table) {
      $columns = $this->columnsOf($table, $force);

      // only do the change if any data has changed
      $cond = array( );
      foreach ($columns as $column) {
        // ignore modified_date changes
        if ($column != 'modified_date' && !in_array($column, CRM_Utils_Array::value($table, $this->exceptions, array()))) {
          $cond[] = "IFNULL(OLD.$column,'') <> IFNULL(NEW.$column,'')";
          }
        }
      $suppressLoggingCond = "@civicrm_disable_logging IS NULL OR @civicrm_disable_logging = 0";
      $updateSQL = "IF ( (" . implode( ' OR ', $cond ) . ") AND ( $suppressLoggingCond ) ) THEN BEGIN \n";

      if ($this->useDBPrefix) {
      $sqlStmt = "INSERT INTO `{$this->db}`.log_{tableName} (";
      }
      else {
        $sqlStmt = "INSERT INTO log_{tableName} (";
      }
      foreach ($columns as $column) {
        $sqlStmt .= "$column, ";
      }
      //NYSS jobID
      $sqlStmt .= "log_conn_id, log_user_id, log_action, log_job_id) VALUES (";

      $insertSQL = $deleteSQL = "IF ( $suppressLoggingCond ) THEN BEGIN \n$sqlStmt ";
      $updateSQL .= $sqlStmt;

      $sqlStmt = '';
      foreach ($columns as $column) {
        $sqlStmt   .= "NEW.$column, ";
        $deleteSQL .= "OLD.$column, ";
      }
      //NYSS jobID
      $sqlStmt   .= "CONNECTION_ID(), @civicrm_user_id, '{eventName}', @jobID);";
      $deleteSQL .= "CONNECTION_ID(), @civicrm_user_id, '{eventName}', @jobID);";

      //NYSS #7893 delta logging
      $delta_trigger = CRM_Utils_Array::value($table, $this->delta_triggers, '');
      if ($delta_trigger) {
        $sqlStmt   .= "\n{$delta_trigger}\n";
        $deleteSQL .= "\n" . str_replace('NEW.','OLD.',$delta_trigger) . "\n";
      }

      $sqlStmt   .= "\nEND; \nEND IF;";
      $deleteSQL .= "\nEND; \nEND IF;";

      $insertSQL .= $sqlStmt;
      $updateSQL .= $sqlStmt;

      $info[] = array(
        'table' => array($table),
        'when' => 'AFTER',
        'event' => $insert,
        'sql' => $insertSQL,
      );

      $info[] = array(
        'table' => array($table),
        'when' => 'AFTER',
        'event' => $update,
        'sql' => $updateSQL,
      );

      $info[] = array(
        'table' => array($table),
        'when' => 'AFTER',
        'event' => $delete,
        'sql' => $deleteSQL,
      );
    }
  }

  /**
   * This allow logging to be temporarily disabled for certain cases
   * where we want to do a mass cleanup but dont want to bother with
   * an audit trail
   *
   * @static
   * @public
   */
  static function disableLoggingForThisConnection( ) {
    // do this only if logging is enabled
    $config = CRM_Core_Config::singleton( );
    if ( $config->logging ) {
      CRM_Core_DAO::executeQuery( 'SET @civicrm_disable_logging = 1' );
    }
  }

  /**
   * Builds and installs the trigger for the delta log's summary table NYSS #7893
   */
  static function nyssBuildSummaryTableTrigger(&$triggers) {
    if (is_array($triggers)) {
      // add the changelog summary trigger
      $trigger_sql = "IF NEW.`log_user_id` IS NULL THEN \n" .
                       "SET NEW.`log_user_id` = @civicrm_user_id; \n" .
                     "END IF; \n" .
                     "SET NEW.`log_change_seq`=nyss_fnGetChangelogSequence();";
      $triggers[] = array(
        'table' => array('nyss_changelog_summary'),
        'event' => array('INSERT'),
        'when'  => 'BEFORE',
        'sql'   => $trigger_sql
      );
    }
  }
  
  /**
   * Builds and installs the trigger for the delta log's detail table NYSS #7893
   */
  static function nyssBuildDetailTableTrigger(&$triggers) {
    /* **** IMPORTANT
        This trigger expects to receive the altered_contact_id in place of
        the log_change_seq field.  The change_seq is generated from a session
        variable, and does not need to be passed in the original insert.  On
        the other hand, the summary table needs the altered_contact_id, but
        the detail has no where to store it.  The log_change_seq field is used
        as a temporary delivery mechanism.  Sloppy, but it works. */
  
    // set up mapping for log_table_name -> log_type_label
    // note that this does not include civicrm_group_contact
    $labels = array(
                    'civicrm_email'                                    => 'Contact',
                    'civicrm_phone'                                    => 'Contact',
                    'civicrm_address'                                  => 'Contact',
                    'civicrm_openid'                                   => 'Contact',
                    'civicrm_im'                                       => 'Contact',
                    'civicrm_website'                                  => 'Contact',
                    'civicrm_value_constituent_information_1'          => 'Contact',
                    'civicrm_value_organization_constituent_informa_3' => 'Contact',
                    'civicrm_value_attachments_5'                      => 'Contact',
                    'civicrm_value_district_information_7'             => 'Contact',
                    'civicrm_value_contact_details_8'                  => 'Contact',
                    'civicrm_activity_contact'                         => 'Activity',
                    'civicrm_value_activity_details_6'                 => 'Activity',
                    'civicrm_case_contact'                             => 'Case',
                    );
  
    // preliminary initialization
    $trigger_sql =  "SET @this_altered_contact_id=NEW.`log_change_seq`; \n" .
                    "SET @this_log_action=NEW.`log_action`; \n" .
                    "SET @this_log_type_label=''; \n" .
                    "CASE NEW.`log_table_name` \n";
  
    // write the labeling map.  includes everything but group_contact and note_comment
    foreach ($labels as $k=>$v) {
      if ($k != 'civicrm_group_contact') {
        $trigger_sql .= "WHEN 'log_{$k}' THEN SET @this_log_type_label='$v'; \n";
      }
    }

    // write the label map for note comment (special SQL)
    $trigger_sql   .= "WHEN 'log_civicrm_note' THEN \n" .
                        "BEGIN \n" .
                          "IF NEW.log_type='Comment' THEN SET @this_log_type_label='Comment'; END IF; \n " .
                        "END; \n";
                        
    // write the label map for group contact (special SQL)
    $trigger_sql   .= "WHEN 'log_civicrm_group_contact' THEN \n" .
                        "BEGIN \n" .
                          "SET @this_log_type_label='Group'; \n" .
                          "IF NEW.`log_action` = 'Update' THEN \n" .
                            "SET @this_log_action = 'Update'; \n" .
                          "ELSEIF NEW.`log_action` = 'Insert' THEN \n" .
                            "SET @this_log_action = 'Added'; \n" .
                          "END IF; \n" .
                        "END; \n" .
                      "ELSE \n" .
                        "BEGIN \n" .
                          "SET @rev_type = REVERSE(NEW.`log_table_name`); \n" .
                          "SET @this_log_type_label=REVERSE(SUBSTR(@rev_type,1,LOCATE('_',@rev_type)-1)); \n" .
                        "END; \n" .
                    "END CASE; \n";
  
    // done with label mapping, write the rest of the trigger
    $trigger_sql .= 
            "SET @this_log_type_label = CONCAT(UCASE(LEFT(@this_log_type_label,1)),SUBSTR(@this_log_type_label,2)); \n" .
          	"IF @this_log_type_label = 'Activity' THEN \n" .
          		"BEGIN \n" .
          			"SET @nyss_changelog_sequence = NULL; \n" .
          			"SELECT `log_change_seq` INTO @nyss_changelog_sequence \n" .
          			"FROM `nyss_changelog_summary` \n" .
          			"WHERE altered_contact_id=@this_altered_contact_id  \n" .
          					"AND log_conn_id = CONNECTION_ID() \n" .
          					"AND log_type_label = 'Activity' \n" .
          			"ORDER BY log_change_seq DESC LIMIT 1; \n" .
          			"INSERT into stuff (tt) VALUES (concat('setting sequence=',@nyss_changelog_sequence)); \n" .
          		"END; \n" .
          	"ELSEIF @this_log_type_label <> 'Contact' THEN  \n" .
          		"BEGIN  \n" .
          			"SET @nyss_changelog_sequence = NULL;  \n" .
          		"END;  \n" .
          	"END IF;  \n" .
            "IF @nyss_changelog_sequence IS NULL THEN \n" .
              "BEGIN \n" .
                "INSERT INTO `nyss_changelog_summary` \n" .
                "(`log_action_label`,`log_type_label`,`altered_contact_id`, `log_conn_id`) \n" .
                "VALUES \n" .
                "(@this_log_action, @this_log_type_label, @this_altered_contact_id, CONNECTION_ID()); \n" .
              "END; \n" .
            "ELSE \n" .
              "BEGIN \n" .
                "UPDATE `nyss_changelog_summary` \n" .
                  "SET `log_action_label`='Update' \n" .
                  "WHERE `log_change_seq`=@nyss_changelog_sequence; \n" .
              "END; \n" .
            "END IF; \n" .
            "SET NEW.`log_change_seq` = @nyss_changelog_sequence; \n";
  
    // set the trigger
    $triggers[] = array(
      'table' => array('nyss_changelog_detail'),
      'event' => array('INSERT'),
      'when'  => 'BEFORE',
      'sql'   => $trigger_sql
    );
  }

  /**
   * Prepares all SQL for new delta log triggers.  Resulting SQL is stored in 
   * $this->delta_triggers = array ( 'table_name' => 'SQL', )
   * NYSS #7893
   */
  function nyssPrepareDeltaTriggers() {
    // an array of tables and the SQL to be added
    $this->delta_triggers = array();
  
    // begin standard tables
    // the "standard" tables are those that relate directly to civicrm_contact
    // i.e., no need for further joins in order to discover the altered contact_id
    // a table list in the form of array ('table_name' => array ('contact_id_field', 'log_type') )
    $standard_tables = array(
                            'civicrm_contact'       => array('id','Contact'),
                            'civicrm_email'         => array('contact_id','Contact'),
                            'civicrm_phone'         => array('contact_id','Contact'),
                            'civicrm_address'       => array('contact_id','Contact'),
                            'civicrm_group_contact' => array('contact_id','Group'),
                            'civicrm_relationship'  => array('contact_id_a','Relationship'),
                            );
  
    // add the extended tables for all contact types.  all should be keyed on entity_id
    foreach (self::nyssFetchExtendedTables('Contact') as $v) {
      $standard_tables[$v] = array('entity_id','Contact');
    }
    
    // sql for the "standard table" triggers
    $sql = "INSERT IGNORE INTO `nyss_changelog_detail` (" .
              "`log_id`, `log_action`, `log_table_name`, " .
              "`log_type`, `log_conn_id`, `log_change_seq`" .
            ") VALUES (" .
              "NEW.`id`, '{eventName}', '{{table_name}}', " .
              "'{{log_type}}', CONNECTION_ID(), NEW.`{{contact_id}}`" .
            ");";
  
    // construct the "standard table" triggers, replacing the tokens during iteration
  	foreach ($standard_tables as $k=>$v) {
      $search = array('{{table_name}}','{{log_type}}','{{contact_id}}');
      $replace = array("log_{$k}",$v[1],$v[0]);
      $this->delta_triggers[$k] = str_replace($search, $replace, $sql);
    }
    // end standard tables
  
    // begin special table triggers
    // these tables need special SQL to handle intermediate logic
    // this section includes tables created for custom data points (civicrm_custom_group)
    // $special_tables array('table_name' => 'trigger_sql')
    $special_tables = array();

    // begin civicrm_case
    $special_tables['civicrm_case_contact'] = 
            "INSERT IGNORE INTO `nyss_changelog_detail` (" .
              "`log_id`, `log_action`, `log_table_name`, " .
              "`log_type`, `log_conn_id`, `log_change_seq`" .
            ") VALUES (" .
              "NEW.`id`, '{eventName}', '{{table_name}}', " .
              "'Case', CONNECTION_ID(), NEW.`contact_id`" .
            ");";
    // end civicrm_case
  
    // begin civicrm_note
    $special_tables['civicrm_note'] =
           "SET @trigger_contact_id = 0; \n" .
           "SET @trigger_tname = 'Note'; \n" .
           "IF NEW.`entity_table` = 'civicrm_contact' THEN \n" .
              "SET @trigger_contact_id = NEW.`entity_id`; \n" .
           "ELSEIF NEW.`entity_table` = 'civicrm_note' THEN \n" .
              "BEGIN \n" .
                "SELECT a.`entity_id` INTO @trigger_contact_id FROM civicrm_note a \n" .
                "WHERE a.`entity_table`='civicrm_contact' AND a.`id`=NEW.`entity_id`; \n" .
                "SET @trigger_tname = 'Comment'; \n" .
              "END; \n" .
           "ELSE SET @trigger_contact_id = 0; \n" .
           "END IF; \n" .
           "IF @trigger_contact_id > 0 THEN \n" .
              "INSERT INTO nyss_changelog_detail ( \n" .
                "`log_id`, `log_action`, `log_table_name`, \n" .
                "`log_type`, `log_conn_id`, `log_change_seq` \n" .
              " ) VALUES ( \n" .
                "NEW.id, '{eventName}', '{{table_name}}', \n" .
                "@trigger_tname, CONNECTION_ID(), @trigger_contact_id \n" .
              ");\n" .
           "END IF; ";
    // end civicrm_note
  
    // begin civicrm_entity_tag
    $special_tables['civicrm_entity_tag'] =
           "SET @trigger_contact_id = 0; \n" .
           "IF NEW.entity_table = 'civicrm_contact' THEN \n" .
              "SET @trigger_contact_id = NEW.entity_id; \n" .
           "ELSE SET @trigger_contact_id = 0; \n" .
           "END IF; \n" .
           "IF @trigger_contact_id > 0 THEN \n" .
              "INSERT INTO nyss_changelog_detail ( \n" .
                "`log_id`, `log_action`, `log_table_name`, \n" .
                "`log_type`, `log_conn_id`, `log_change_seq` \n" .
              " ) VALUES ( \n" .
                "NEW.id, '{eventName}', '{{table_name}}', \n" .
                "'Tag', CONNECTION_ID(), @trigger_contact_id \n" .
              ");\n" .
           "END IF; ";
    // end civicrm_entity_tag

    // begin civicrm_activity_contact
    $special_tables['civicrm_activity_contact'] =
           "INSERT INTO nyss_changelog_detail (" .
              "`log_id`, `log_action`, `log_table_name`, \n" .
              "`log_type`, `log_conn_id`, `log_change_seq` \n" .
           ") VALUES (" .
              "NEW.id, '{eventName}', '{{table_name}}', \n" .
              "CONCAT( \n" .
                "'Activity_for_', \n" .
                "CASE NEW.record_type_id \n" .
                  "WHEN 1 THEN 'Target' \n" .
                  "WHEN 2 THEN 'Source' \n" .
                  "WHEN 3 THEN 'Assignee' \n" .
                  "ELSE 'Unknown' \n" .
                "END \n" .
              "), \n" .
              "CONNECTION_ID(), NEW.contact_id \n" .
            ");";
    // end civicrm_activity_contact
    
    // begin civicrm_activity and tables related to "Activity"
    // list of tables dependent on activity entities
    $add_table_list = array_merge(array('civicrm_activity'), self::nyssFetchExtendedTables('Activity'));
    // the special SQL for activity-based triggers
    $sql = "INSERT INTO nyss_changelog_detail (" .
              "`log_id`, `log_action`, `log_table_name`, \n" .
              "`log_type`, `log_conn_id`, `log_change_seq` \n" .
           ") SELECT \n" .
              "NEW.id, '{eventName}', '{{table_name}}', \n" .
              "CONCAT( \n" .
                "'Activity_for_', \n" .
                "CASE b.record_type_id \n" .
                  "WHEN 1 THEN 'Target' \n" .
                  "WHEN 2 THEN 'Source' \n" .
                  "WHEN 3 THEN 'Assignee' \n" .
                  "ELSE 'Unknown' \n" .
                "END \n" .
              "), \n" .
              "CONNECTION_ID(), \n" .
              "b.contact_id \n" .
              "FROM civicrm_activity_contact b \n" .
              "WHERE b.activity_id = NEW.{{contact_field}};";
    // add each table to the special tables array
    foreach ($add_table_list as $k) {
      // civicrm_activity is keyed on `id`.  All the others are on `entity_id`
      $onesql = str_replace('{{contact_field}}', $k=='civicrm_activity' ? 'id' : 'entity_id', $sql);
      $special_tables[$k] = $onesql;
    }
    // end civicrm_activity and tables related to "Activity"
  
    // begin tables related to "Address"
    $add_table_list = self::nyssFetchExtendedTables('Address');
    // the special trigger SQL for each address-based triggers
    $sql = "INSERT INTO nyss_changelog_detail (" .
              "`log_id`, `log_action`, `log_table_name`, \n" .
              "`log_type`, `log_conn_id`, `log_change_seq` \n" .
           ") SELECT \n" .
              "NEW.id, '{eventName}', '{{table_name}}', \n" .
              "'Contact', CONNECTION_ID(), b.contact_id \n" .
           "FROM civicrm_address b WHERE b.id = NEW.entity_id;";
    foreach($add_table_list as $k) {
      $special_tables[$k] = $sql;
    }
    // end tables related to "Address"
  
    // construct the "special table" triggers
    foreach ($special_tables as $k=>$v) {
      $this->delta_triggers[$k] = str_replace('{{table_name}}',"log_{$k}",$v);
    }
    // end special table triggers
  }

  /**
   * Return custom data tables for specified entity / extends. 
   * THIS RETURNS ACTUAL TABLES, NOT LOG TABLES
   */
  static function nyssFetchExtendedTables($table_groups) {
    $customGroupTables = array();
    $customGroupDAO = CRM_Core_BAO_CustomGroup::getAllCustomGroupsByBaseEntity($table_groups);
    $customGroupDAO->find();
    while ($customGroupDAO->fetch()) {
      $customGroupTables[] = $customGroupDAO->table_name;
    }
    return $customGroupTables;
  }
}

