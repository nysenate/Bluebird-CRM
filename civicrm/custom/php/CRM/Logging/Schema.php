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
      $sql = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "create_summary_trigger.sql");
      if ($sql) {
        $triggers[] = array(
          'table' => array('nyss_changelog_summary'),
          'event' => array('INSERT'),
          'when'  => 'BEFORE',
          'sql'   => $sql
        );
      }
    }
  }
  
  /**
   * Builds and installs the trigger for the delta log's detail table NYSS #7893
   */
  static function nyssBuildDetailTableTrigger(&$triggers) {
    if (is_array($triggers)) {
      $sql = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "create_detail_runtime_trigger.sql");
      if ($sql) {
        // set the trigger
        $triggers[] = array(
          'table' => array('nyss_changelog_detail'),
          'event' => array('INSERT'),
          'when'  => 'BEFORE',
          'sql'   => $sql
        );
      }
    }
  }

  /**
   * Prepares all SQL for new delta log triggers.  Resulting SQL is stored in 
   * $this->delta_triggers = array ( 'table_name' => 'SQL', )
   * NYSS #7893
   */
  function nyssPrepareDeltaTriggers() {
    // an array of tables and the SQL to be added
    $this->delta_triggers = array();
  
		$this->delta_triggers['civicrm_contact'] = 
		    "SET @nyss_altered_contact_id = NEW.`id`; \n" .
		    "SET @nyss_entity_info = 'Contact'; \n" .
		    "INSERT INTO `nyss_changelog_detail` \n" .
		    "  (`db_op`,`table_name`,`entity_id`) VALUES\n" .
		    "  ('{eventName}','contact',NEW.`id`);";

		$this->delta_triggers['civicrm_email']=
				"SET @nyss_altered_contact_id = NEW.`contact_id`;\n" .
		    "SET @nyss_entity_info = 'Contact'; \n" .
				"INSERT INTO `nyss_changelog_detail` \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','email',NEW.`id`);";


		$this->delta_triggers['civicrm_phone']=
				"SET @nyss_altered_contact_id = NEW.`contact_id`;\n" .
		    "SET @nyss_entity_info = 'Contact'; \n" .
				"INSERT INTO `nyss_changelog_detail` \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','phone',NEW.`id`);";


		$this->delta_triggers['civicrm_address']=
				"SET @nyss_altered_contact_id = NEW.`contact_id`;\n" .
		    "SET @nyss_entity_info = 'Contact'; \n" .
				"INSERT INTO `nyss_changelog_detail` \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','address',NEW.`id`);";


		$this->delta_triggers['civicrm_group_contact']=
				"SET @nyss_altered_contact_id = NEW.`contact_id`;\n" .
				"SELECT CONCAT_WS(CHAR(1),'Group',`title`,NEW.`status`) " .
				"INTO @nyss_entity_info FROM `civicrm_group` WHERE `id`=NEW.`group_id`;\n" .
				"INSERT INTO `nyss_changelog_detail` \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','group_contact',NEW.`id`);";


		$this->delta_triggers['civicrm_relationship']=
				"SET @nyss_altered_contact_id = NEW.`contact_id_a`;\n" .
				"SELECT CONCAT_WS(CHAR(1),'Relationship',`label_a_b`) INTO @nyss_entity_info " .
				"FROM `civicrm_relationship_type` WHERE `id`=NEW.`relationship_type_id`;\n" .
				"INSERT INTO `nyss_changelog_detail` \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','relationship',NEW.`id`);\n" .
				"SET @nyss_altered_contact_id = NEW.`contact_id_b`;\n" .
				"SELECT CONCAT_WS(CHAR(1),'Relationship',`label_b_a`) INTO @nyss_entity_info " .
				"FROM `civicrm_relationship_type` WHERE `id`=NEW.`relationship_type_id`;\n" .
				"INSERT INTO `nyss_changelog_detail` \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','relationship',NEW.`id`);";


		$this->delta_triggers['civicrm_case_contact']=
				"SET @nyss_altered_contact_id = NEW.`contact_id`;\n" .
				"SELECT CONCAT_WS(CHAR(1), 'Case', d.`label`) INTO @nyss_entity_info FROM \n" .
				"    `civicrm_case` a INNER JOIN ( \n" .
				"        `civicrm_option_group` c INNER JOIN `civicrm_option_value` d \n" .
				"        ON c.`name`='case_type' AND c.`id`=d.`option_group_id`) \n" .
				"    ON a.`case_type_id`=d.`value` \n" .
				"WHERE a.`id`=NEW.`case_id`; \n" .
				"INSERT INTO `nyss_changelog_detail` \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','case_contact',NEW.`id`);";


		$this->delta_triggers['civicrm_note']=
				"SET @tmp_trigger_cid = 0; \n" .
				"SET @tmp_trigger_typename = 'Note'; \n" .
				"SET @tmp_trigger_entinfo = NULL; \n" .
				"IF NEW.`entity_table` = 'civicrm_contact' THEN \n" .
				"  BEGIN \n" .
				"    SET @tmp_trigger_cid = NEW.`entity_id`; \n" .
				"    SET @tmp_trigger_entinfo = NEW.`subject`; \n" .
				"  END; \n" .
				"ELSEIF NEW.`entity_table` = 'civicrm_note' THEN \n" .
				"  BEGIN \n" .
				"    SELECT a.`entity_id`,a.`subject`,'Comment' \n" .
				"    INTO @tmp_trigger_cid, @tmp_trigger_entinfo, @tmp_trigger_typename \n" .
				"    FROM civicrm_note a \n" .
				"    WHERE a.`entity_table`='civicrm_contact' AND a.`id`=NEW.`entity_id`; \n" .
				"  END; \n" .
				"ELSE SET @tmp_trigger_cid = 0; \n" .
				"END IF; \n" .
				"IF @tmp_trigger_cid > 0 THEN \n" .
				"  BEGIN\n" .
				"    SET @nyss_altered_contact_id = @tmp_trigger_cid;\n" .
		    "    SET @nyss_entity_info = CONCAT_WS(CHAR(1), @tmp_trigger_typename, @tmp_trigger_entinfo); \n" .
				"    INSERT INTO nyss_changelog_detail\n" .
				"    (`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"    ('{eventName}','note',NEW.`id`);\n" .
				"  END;\n" .
				"END IF; \n";

		$this->delta_triggers['civicrm_entity_tag']=
				"SET @tmp_trigger_cid = 0; \n" .
				"IF NEW.entity_table = 'civicrm_contact' THEN \n" .
				"  SET @tmp_trigger_cid = NEW.entity_id; \n" .
				"END IF; \n" .
				"IF @tmp_trigger_cid > 0 THEN \n" .
				"  BEGIN\n" .
				"    SET @nyss_altered_contact_id = @tmp_trigger_cid;\n" .
				"    SELECT CONCAT_WS(CHAR(1),'Tag',`name`) INTO @nyss_entity_info \n" .
				"      FROM civicrm_tag WHERE `id` = NEW.`tag_id`; \n" .
				"    INSERT INTO nyss_changelog_detail \n" .
				"    (`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"    ('{eventName}','entity_tag',NEW.`id`);\n" .
				"  END;\n" .
				"END IF; \n";

		$this->delta_triggers['civicrm_activity_contact']=
				"SET @tmp_trigger_ctype = CASE NEW.`record_type_id`\n" .
        "                   WHEN 3 THEN 'Target'\n" .
        "                   WHEN 2 THEN 'Source'\n" .
        "                   WHEN 1 THEN 'Assignee'\n" .
        "                   ELSE 'Unknown'\n" .
        "                 END;\n" .
				"SELECT CONCAT('(',@tmp_trigger_ctype,') ',d.`label`) INTO @tmp_trigger_entinfo \n" .
				"FROM civicrm_activity a \n" .
				"    INNER JOIN ( \n" .
				"        civicrm_option_group c INNER JOIN civicrm_option_value d \n" .
				"        ON c.`name`='activity_type' AND c.`id`=d.`option_group_id`\n" .
				"    ) ON a.`activity_type_id`=d.`value` \n" .
				"WHERE a.`id`=NEW.`activity_id`; \n" .
				"SET @nyss_altered_contact_id = NEW.`contact_id`;\n" .
		    "SET @nyss_entity_info = CONCAT_WS(CHAR(1),'Activity',@tmp_trigger_entinfo); \n" .
				"INSERT INTO nyss_changelog_detail \n" .
				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
				"('{eventName}','activity_contact',NEW.`id`);";


    // add extended tables for contacts
    $add_table_list = self::nyssFetchExtendedTables('Contact');
    foreach ($add_table_list as $t) {
      $sqlname = CRM_Core_DAO::escapeString(str_replace(array('civicrm_','log_'),array('',''),$t));
      $this->delta_triggers[$t] = 
  				"SET @nyss_altered_contact_id = NEW.`entity_id`;\n" .
		      "SET @nyss_entity_info = 'Contact'; \n" .
  				"INSERT INTO `nyss_changelog_detail` \n" .
  				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
  				"('{eventName}','$sqlname',NEW.`id`);";
  	}

    // add extended tables for addresses
    $add_table_list = self::nyssFetchExtendedTables('Address');
    foreach ($add_table_list as $t) {
      $sqlname = CRM_Core_DAO::escapeString(str_replace(array('civicrm_','log_'),array('',''),$t));
      $this->delta_triggers[$t] = 
  				"SELECT `contact_id` INTO @nyss_altered_contact_id\n" .
  				"FROM `civicrm_address` WHERE `id`=NEW.`entity_id`;\n" .
		      "SET @nyss_entity_info = 'Contact'; \n" .
  				"INSERT INTO nyss_changelog_detail \n" .
  				"(`db_op`,`table_name`,`entity_id`) VALUES\n" .
  				"('{eventName}', '$sqlname', NEW.`id`);";
  	}

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

