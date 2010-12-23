<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/DAO.php';

class CRM_Logging_Schema
{
    private $logs   = array();
    private $tables = array();

    private $loggingDB;

    /**
     * Populate $this->tables and $this->logs with current db state.
     */
    function __construct()
    {
        $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
        $this->loggingDB = $dsn['database'];

        $dao = CRM_Core_DAO::executeQuery('SHOW TABLES LIKE "civicrm_%"');
        while ($dao->fetch()) {
            $this->tables[] = $dao->toValue("Tables_in_{$dao->_database}_(civicrm_%)");
        }
        // do not log temp import and cache tables
        $this->tables = preg_grep('/^civicrm_import_job_/', $this->tables, PREG_GREP_INVERT);
        $this->tables = preg_grep('/_cache$/',              $this->tables, PREG_GREP_INVERT);

        $dao = CRM_Core_DAO::executeQuery("SHOW TABLES FROM {$this->loggingDB} LIKE 'log_civicrm_%'");
        while ($dao->fetch()) {
            $log = $dao->toValue("Tables_in_{$this->loggingDB}_(log_civicrm_%)");
            $this->logs[substr($log, 4)] = $log;
        }
    }

    /**
     * Disable logging by dropping the triggers (but keep the log tables intact).
     */
    function disableLogging()
    {
        if (!$this->isEnabled()) return;

        $this->dropTriggers();
        $this->deleteReports();
    }

    /**
     * Enable logging by creating the log tables (where needed) and creating the triggers.
     */
    function enableLogging()
    {
        if ($this->isEnabled()) return;

        foreach (array_diff($this->tables, array_keys($this->logs)) as $table) {
            $this->createLogTableFor($table);
        }
        $this->createTriggers();
        $this->addReports();
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
            CRM_Core_DAO::executeQuery("ALTER TABLE {$this->loggingDB}.log_$table ADD $line");
        }

        // recreate triggers to cater for the new columns
        $this->createTriggersFor($table);
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
        // enable logging templates
        $dao =& CRM_Core_DAO::executeQuery("UPDATE civicrm_option_value SET is_active = 1 WHERE value = 'logging/contact/summary' OR value = 'logging/contact/detail'");

        // return early if the logging report templates are missing
        require_once 'CRM/Core/OptionGroup.php';
        $templates = CRM_Core_OptionGroup::values('report_template');
        if (!isset($templates['logging/contact/summary']) or
            !isset($templates['logging/contact/detail'])) return;

        // add report instances
        require_once 'CRM/Report/BAO/Instance.php';

        $bao = new CRM_Report_BAO_Instance;
        $bao->domain_id  = CRM_Core_Config::domainID();
        $bao->title      = ts('Contact Logging Report (Summary)');
        $bao->report_id  = 'logging/contact/summary';
        $bao->permission = 'administer CiviCRM';
        $bao->insert();

        $bao = new CRM_Report_BAO_Instance;
        $bao->domain_id  = CRM_Core_Config::domainID();
        $bao->title      = ts('Contact Logging Report (Detail)');
        $bao->report_id  = 'logging/contact/detail';
        $bao->permission = 'administer CiviCRM';
        $bao->insert();
    }

    /**
     * Get an array of column names of the given table.
     */
    private function columnsOf($table)
    {
        static $columnsOf = array();

        $from = (substr($table, 0, 4) == 'log_') ? "{$this->loggingDB}.$table" : $table;

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
        CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS {$this->loggingDB}.log_$table");

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
        $query = preg_replace("/^CREATE TABLE `$table`/i", "CREATE TABLE {$this->loggingDB}.log_$table", $query);
        $query = preg_replace("/ AUTO_INCREMENT/i", '', $query);
        $query = preg_replace("/^  [^`].*$/m", '', $query);
        $query = preg_replace("/^\) ENGINE=[^ ]+ /im", ') ENGINE=ARCHIVE ', $query);
        $query = preg_replace("/^\) /m", "$cols\n) ", $query);

        CRM_Core_DAO::executeQuery($query);

        $columns = implode(', ', $this->columnsOf($table));
        CRM_Core_DAO::executeQuery("INSERT INTO {$this->loggingDB}.log_$table ($columns, log_conn_id, log_user_id, log_action) SELECT $columns, CONNECTION_ID(), @civicrm_user_id, 'Initialization' FROM {$table}");

        $this->tables[]     = $table;
        $this->logs[$table] = "log_$table";
    }

    /**
     * Create triggers populating the relevant log table every time the given table changes.
     */
    private function createTriggersFor($table)
    {
        $columns = $this->columnsOf($table);

        $queries = array();
        foreach (array('Insert', 'Update', 'Delete') as $action) {
            $trigger = "{$table}_after_" . strtolower($action);
            $queries[] = "DROP TRIGGER IF EXISTS $trigger";
            $query = "CREATE TRIGGER $trigger AFTER $action ON $table FOR EACH ROW INSERT INTO {$this->loggingDB}.log_$table (";
            foreach ($columns as $column) {
                $query .= "$column, ";
            }
            $query .= "log_conn_id, log_user_id, log_action) VALUES (";
            foreach ($columns as $column) {
                $query .= $action == 'Delete' ? "OLD.$column, " : "NEW.$column, ";
            }
            $query .= "CONNECTION_ID(), @civicrm_user_id, '$action')";
            $queries[] = $query;
        }

        $dao = new CRM_Core_DAO;
        foreach ($queries as $query) {
            $dao->executeQuery($query);
        }
    }

    /**
     * Create triggers for all logged tables.
     */
    private function createTriggers()
    {
        foreach ($this->tables as $table) {
            $this->createTriggersFor($table);
        }
    }

    private function deleteReports()
    {
        // disable logging templates
        $dao =& CRM_Core_DAO::executeQuery("UPDATE civicrm_option_value SET is_active = 0 WHERE value = 'logging/contact/summary' OR value = 'logging/contact/detail'");

        // delete report instances
        require_once 'CRM/Report/DAO/Instance.php';

        $bao = new CRM_Report_DAO_Instance;
        $bao->domain_id = CRM_Core_Config::domainID();
        $bao->report_id = 'logging/contact/summary';
        $bao->delete();

        $bao = new CRM_Report_DAO_Instance;
        $bao->domain_id = CRM_Core_Config::domainID();
        $bao->report_id = 'logging/contact/details';
        $bao->delete();
    }

    /**
     * Drop triggers for all logged tables.
     */
    private function dropTriggers()
    {
        $dao = new CRM_Core_DAO;
        foreach ($this->tables as $table) {
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_insert");
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_update");
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_delete");
        }
    }

    /**
     * Predicate whether logging is enabled.
     */
    private function isEnabled()
    {
        return $this->tablesExist() and $this->triggersExist();
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
}
