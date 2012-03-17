<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/DAO.php';

class CRM_Logging_Schema
{
    private $logs   = array();
    private $tables = array();

    private $db;

    private $reports = array(
        'logging/contact/detail',
        'logging/contact/summary',
        'logging/contribute/detail',
        'logging/contribute/summary',
    );

    /**
     * Populate $this->tables and $this->logs with current db state.
     */
    function __construct()
    {
        require_once 'CRM/Contact/DAO/Contact.php';
        $dao = new CRM_Contact_DAO_Contact( );
        $civiDBName = $dao->_database;

        $dao = CRM_Core_DAO::executeQuery("
SELECT TABLE_NAME 
FROM   INFORMATION_SCHEMA.TABLES 
WHERE  TABLE_SCHEMA = '{$civiDBName}'
AND    TABLE_TYPE = 'BASE TABLE' 
AND    TABLE_NAME LIKE 'civicrm_%'
");
        while ($dao->fetch()) {
            $this->tables[] = $dao->TABLE_NAME;
        }

        // do not log temp import and cache tables
        $this->tables = preg_grep('/^civicrm_import_job_/',       $this->tables, PREG_GREP_INVERT);
        $this->tables = preg_grep('/_cache$/',                    $this->tables, PREG_GREP_INVERT);
        $this->tables = preg_grep('/^civicrm_task_action_temp_/', $this->tables, PREG_GREP_INVERT);
        $this->tables = preg_grep('/^civicrm_export_temp_/',      $this->tables, PREG_GREP_INVERT);

        $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
        $this->db = $dsn['database'];

        $dao = CRM_Core_DAO::executeQuery("
SELECT TABLE_NAME 
FROM   INFORMATION_SCHEMA.TABLES 
WHERE  TABLE_SCHEMA = '{$this->db}'
AND    TABLE_TYPE = 'BASE TABLE' 
AND    TABLE_NAME LIKE 'log_civicrm_%'
");
        while ($dao->fetch()) {
            $log = $dao->TABLE_NAME;
            $this->logs[substr($log, 4)] = $log;
        }
    }

    /**
     * Return logging custom data tables.
     */
    function customDataLogTables()
    {
        return preg_grep('/^log_civicrm_value_/', $this->logs);
    }

    /**
     * Disable logging by dropping the triggers (but keep the log tables intact).
     */
    function disableLogging()
    {
        if (!$this->isEnabled()) return;

        //$this->dropTriggers();
		//NYSS invoke the meta trigger creation call
 	 	CRM_Core_DAO::triggerRebuild( );
        $this->deleteReports();
    }

    /**
     * Enable logging by creating the log tables (where needed) and creating the triggers.
     */
    function enableLogging()
    {
        if ($this->isEnabled()) return;

        foreach ($this->tables as $table) {
            $this->createLogTableFor($table);
        }

        $this->addReports(); //NYSS

        //$this->createTriggers();
		// invoke the meta trigger creation call //NYSS 5067
        CRM_Core_DAO::triggerRebuild( );
    }

    /**
     * Add missing log table columns.
     */
    function fixSchemaDifferences()
    {
        if (!$this->isEnabled()) return;

        foreach ($this->schemaDifferences() as $table => $cols) {
            $this->fixSchemaDifferencesFor($table, $cols);
        }
    }

    /**
     * Add missing (potentially specified) log table columns for the given table.
     *
     * param $table string  name of the relevant table
     * param $cols mixed    array of columns to add or null (to check for the missing columns)
     */
    function fixSchemaDifferencesFor($table, $cols = null)
    {
        if (!$this->isEnabled()) return;

        if (empty($this->logs[$table])) {
            $this->createLogTableFor($table);
        }

        if (is_null($cols)) {
           $cols = array_diff($this->columnsOf($table), $this->columnsOf("log_$table"));
        }
        if (empty($cols)) return;

        // use the relevant lines from CREATE TABLE to add colums to the log table
        $dao = CRM_Core_DAO::executeQuery("SHOW CREATE TABLE $table");
        $dao->fetch();
        $create = explode("\n", $dao->Create_Table);
        foreach ($cols as $col) {
            $line = substr(array_pop(preg_grep("/^  `$col` /", $create)), 0, -1);
            CRM_Core_DAO::executeQuery("ALTER TABLE `{$this->db}`.log_$table ADD $line");
        }

        // recreate triggers to cater for the new columns
        //$this->createTriggersFor($table);
		// invoke the meta trigger creation call //NYSS 5-67
        CRM_Core_DAO::triggerRebuild( $table );
    }

    /**
     * Find missing log table columns by comparing columns of the relevant tables.
     * Returns table-name-keyed array of arrays of missing columns, e.g. array('civicrm_value_foo_1' => array('bar_1', 'baz_2'))
     */
    function schemaDifferences()
    {
        $diffs = array();
        foreach ($this->tables as $table) {
            $diffs[$table] = array_diff($this->columnsOf($table), $this->columnsOf("log_$table"));
        }
        return array_filter($diffs);
    }

    private function addReports()
    {
        $titles = array(
            'logging/contact/detail'     => ts('Contact Logging Report (Detail)'),
            'logging/contact/summary'    => ts('Contact Logging Report (Summary)'),
            'logging/contribute/detail'  => ts('Contribution Logging Report (Detail)'),
            'logging/contribute/summary' => ts('Contribution Logging Report (Summary)'),
        );
        // enable logging templates
        CRM_Core_DAO::executeQuery("
            UPDATE civicrm_option_value
            SET is_active = 1
            WHERE value IN ('" . implode("', '", $this->reports) . "')
        ");

        // add report instances
        require_once 'CRM/Report/DAO/Instance.php';
        $domain_id = CRM_Core_Config::domainID();
        foreach ($this->reports as $report) {
            $dao = new CRM_Report_DAO_Instance;
            $dao->domain_id  = $domain_id;
            $dao->report_id  = $report;
            $dao->title      = $titles[$report];
            $dao->permission = 'administer CiviCRM';
            $dao->insert();
        }
    }

    /**
     * Get an array of column names of the given table.
     */
    private function columnsOf($table)
    {
        static $columnsOf = array();

        $from = (substr($table, 0, 4) == 'log_') ? "`{$this->db}`.$table" : $table;

        if (!isset($columnsOf[$table])) {
            $dao = CRM_Core_DAO::executeQuery("SHOW COLUMNS FROM $from");
            $columnsOf[$table] = array();
            while ($dao->fetch()) {
                $columnsOf[$table][] = $dao->Field;
            }
        }

        return $columnsOf[$table];
    }

    /**
     * Create a log table with schema mirroring the given table’s structure and seeding it with the given table’s contents.
     */
    private function createLogTableFor($table)
    {
        CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS `{$this->db}`.log_$table");

        $dao = CRM_Core_DAO::executeQuery("SHOW CREATE TABLE $table");
        $dao->fetch();
        $query = $dao->Create_Table;

        // rewrite the queries into CREATE TABLE queries for log tables:
        // - prepend the name with log_
        // - drop AUTO_INCREMENT columns
        // - drop non-column rows of the query (keys, constraints, etc.)
        // - set the ENGINE to ARCHIVE
        // - add log-specific columns (at the end of the table)
        $cols = <<<COLS
            log_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            log_conn_id INTEGER,
            log_user_id INTEGER,
            log_action  ENUM('Initialization', 'Insert', 'Update', 'Delete')
COLS;
        $query = preg_replace("/^CREATE TABLE `$table`/i", "CREATE TABLE `{$this->db}`.log_$table", $query);
        $query = preg_replace("/ AUTO_INCREMENT/i", '', $query);
        $query = preg_replace("/^  [^`].*$/m", '', $query);
        $query = preg_replace("/^\) ENGINE=[^ ]+ /im", ') ENGINE=ARCHIVE ', $query);
        $query = preg_replace("/^\) /m", "$cols\n) ", $query);

        CRM_Core_DAO::executeQuery($query);

        $columns = implode(', ', $this->columnsOf($table));
        CRM_Core_DAO::executeQuery("INSERT INTO `{$this->db}`.log_$table ($columns, log_conn_id, log_user_id, log_action) SELECT $columns, CONNECTION_ID(), @civicrm_user_id, 'Initialization' FROM {$table}");

        $this->tables[]     = $table;
        $this->logs[$table] = "log_$table";
    }

    /**
     * Create triggers for all logged tables.
     */
    /*private function createTriggers()
    {
        foreach ($this->tables as $table) {
            $this->createTriggersFor($table);
        }
    }*/ //NYSS 5067

    private function deleteReports()
    {
        // disable logging templates
        CRM_Core_DAO::executeQuery("
            UPDATE civicrm_option_value
            SET is_active = 0
            WHERE value IN ('" . implode("', '", $this->reports) . "')
        ");

        // delete report instances
        require_once 'CRM/Report/DAO/Instance.php';
        $domain_id = CRM_Core_Config::domainID();
        foreach($this->reports as $report) {
            $dao = new CRM_Report_DAO_Instance;
            $dao->domain_id = $domain_id;
            $dao->report_id = $report;
            $dao->delete();
        }
    }

    /**
     * Predicate whether logging is enabled.
     */
    public function isEnabled()
    {
        //return $this->tablesExist() and $this->triggersExist();
        $config = CRM_Core_Config::singleton();
        return $config->logging;
    }

    /**
     * Predicate whether any log tables exist.
     */
    private function tablesExist()
    {
        return !empty($this->logs);
    }

    /**
     * Predicate whether the logging triggers are in place.
     */
    private function triggersExist()
    {
        // FIXME: probably should be a bit more thorough…
        return (bool) CRM_Core_DAO::singleValueQuery("SHOW TRIGGERS LIKE 'civicrm_contact'");
    }

	//NYSS 5067
	function triggerInfo( &$info, $tableName = null ) {
        // check if we have logging enabled
        $config =& CRM_Core_Config::singleton( );
        if ( ! $config->logging ) {
            return;
        }

        $upsert = array( 'INSERT', 'UPDATE' );
        $delete = array( 'DELETE' );

        if ( $tableName ) {
            $tableNames = array( $tableName );
        } else {
            $tableNames = $this->tables;
        }

        // logging is enabled, so now lets create the trigger info tables
        foreach ( $tableNames as $table ) {
            $columns = $this->columnsOf($table);

            $upsertSQL = $deleteSQL = "INSERT INTO `{$this->db}`.log_{tableName} (";
            foreach ( $columns as $column ) {
                $upsertSQL .= "$column, ";
                $deleteSQL .= "$column, ";
            }
            $upsertSQL .= "log_conn_id, log_user_id, log_action) VALUES (";
            $deleteSQL .= "log_conn_id, log_user_id, log_action) VALUES (";
            
            foreach ( $columns as $column ) {
                $upsertSQL .= "NEW.$column, ";
                $deleteSQL .= "OLD.$column, ";
            }
            $upsertSQL .= "CONNECTION_ID(), @civicrm_user_id, '{eventName}');";
            $deleteSQL .= "CONNECTION_ID(), @civicrm_user_id, '{eventName}');";

            $info[] = array( 'table' => array( $table ),
                             'when'  => 'AFTER',
                             'event' => $upsert,
                             'sql'   => $upsertSQL );

            $info[] = array( 'table' => array( $table ),
                             'when'  => 'AFTER',
                             'event' => $delete,
                             'sql'   => $deleteSQL );
        }
    } //triggerInfo
}
