<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
require_once 'CRM/Core/DAO/Domain.php';
require_once 'CRM/Core/I18n/SchemaStructure.php';

class CRM_Core_I18n_Schema
{
    /**
     * Drop all views (for use by CRM_Core_DAO::dropAllTables() mostly).
     *
     * @return void
     */
    static function dropAllViews()
    {
        $domain = new CRM_Core_DAO_Domain();
        $domain->find(true);
        if (!$domain->locales) return;

        $locales = explode(CRM_Core_DAO::VALUE_SEPARATOR, $domain->locales);
        $tables =& CRM_Core_I18n_SchemaStructure::tables();

        foreach ($locales as $locale) {
            foreach ($tables as $table) {
                CRM_Core_DAO::executeQuery("DROP VIEW IF EXISTS {$table}_{$locale}");
            }
        }
    }

    /**
     * Switch database from single-lang to multi (by adding 
     * the first language and dropping the original columns).
     *
     * @param $locale string  the first locale to create (migrate to)
     * @return void
     */
    function makeMultilingual($locale)
    {
        $domain = new CRM_Core_DAO_Domain();
        $domain->find(true);

        // break early if the db is already multi-lang
        if ($domain->locales) return;

        $dao = new CRM_Core_DAO();

        // build the column-adding SQL queries
        $columns =& CRM_Core_I18n_SchemaStructure::columns();
        $indices =& CRM_Core_I18n_SchemaStructure::indices();
        $queries = array();
        foreach ($columns as $table => $hash) {
            // drop old indices
            if (isset($indices[$table])) {
                foreach ($indices[$table] as $index) {
                    $queries[] = "DROP INDEX {$index['name']} ON {$table}";
                }
            }
            // deal with columns
            foreach ($hash as $column => $type) {
                $queries[] = "ALTER TABLE {$table} ADD {$column}_{$locale} {$type}";
                $queries[] = "UPDATE {$table} SET {$column}_{$locale} = {$column}";
                $queries[] = "ALTER TABLE {$table} DROP {$column}";
            }

            // add view
            $queries[] = self::createViewQuery($locale, $table, $dao);

            // add new indices
            $queries = array_merge($queries, self::createIndexQueries($locale, $table));
        }

        // execute the queries without i18n rewriting
        foreach ($queries as $query) {
            $dao->query($query, false);
        }

        // update civicrm_domain.locales
        $domain->locales = $locale;
        $domain->save();
    }

    /**
     * Switch database from multi-lang back to single (by dropping 
     * additional columns and views and retaining only the selected locale).
     *
     * @param $retain string  the locale to retain
     * @return void
     */
    function makeSinglelingual($retain)
    {
        $domain = new CRM_Core_DAO_Domain;
        $domain->find(true);
        $locales = explode(CRM_Core_DAO::VALUE_SEPARATOR, $domain->locales);

        // break early if the db is already single-lang
        if (!$locales) return;

        // build the column-dropping SQL queries
        $columns =& CRM_Core_I18n_SchemaStructure::columns();
        $indices =& CRM_Core_I18n_SchemaStructure::indices();
        $queries = array();
        foreach ($columns as $table => $hash) {
            // drop old indices
            if (isset($indices[$table])) {
                foreach ($indices[$table] as $index) {
                    foreach ($locales as $loc) {
                        $queries[] = "DROP INDEX {$index['name']}_{$loc} ON {$table}";
                    }
                }
            }

            // drop triggers
            $queries[] = "DROP TRIGGER IF EXISTS {$table}_before_insert";
            $queries[] = "DROP TRIGGER IF EXISTS {$table}_before_update";

            // deal with columns
            foreach ($hash as $column => $type) {
                $queries[] = "ALTER TABLE {$table} ADD {$column} {$type}";
                $queries[] = "UPDATE {$table} SET {$column} = {$column}_{$retain}";
                foreach ($locales as $loc) {
                    $queries[] = "ALTER TABLE {$table} DROP {$column}_{$loc}";
                }
            }

            // drop views
            foreach ($locales as $loc) {
                $queries[] = "DROP VIEW {$table}_{$loc}";
            }

            // add original indices
            $queries = array_merge($queries, self::createIndexQueries(null, $table));
        }

        // execute the queries without i18n rewriting
        $dao = new CRM_Core_DAO;
        foreach ($queries as $query) {
            $dao->query($query, false);
        }

        // update civicrm_domain.locales
        $domain->locales = 'NULL';
        $domain->save();
        
        //CRM-6963 -fair assumption. 
        global $dbLocale;
        $dbLocale = '';
    }

    /**
     * Add a new locale to a multi-lang db, setting 
     * its values to the current default locale.
     *
     * @param $locale string  the new locale to add
     * @param $source string  the locale to copy from
     * @return void
     */
    function addLocale($locale, $source)
    {
        // get the current supported locales 
        $domain = new CRM_Core_DAO_Domain();
        $domain->find(true);
        $locales = explode(CRM_Core_DAO::VALUE_SEPARATOR, $domain->locales);

        // break early if the locale is already supported
        if (in_array($locale, $locales)) return;

        $dao = new CRM_Core_DAO();

        // build the required SQL queries
        $columns =& CRM_Core_I18n_SchemaStructure::columns();
        $indices =& CRM_Core_I18n_SchemaStructure::indices();
        $queries = array();
        foreach ($columns as $table => $hash) {
            // add new columns
            foreach ($hash as $column => $type) {
                $queries[] = "ALTER TABLE {$table} ADD {$column}_{$locale} {$type}";
                $queries[] = "UPDATE {$table} SET {$column}_{$locale} = {$column}_{$source}";
            }

            // add view
            $queries[] = self::createViewQuery($locale, $table, $dao);

            // add new indices
            $queries = array_merge($queries, self::createIndexQueries($locale, $table));
        }

        // add triggers
        $queries = array_merge($queries, self::createTriggerQueries($locales, $locale));

        // execute the queries without i18n rewriting
        foreach ($queries as $query) {
            $dao->query($query, false);
        }

        // update civicrm_domain.locales
        $locales[] = $locale;
        $domain->locales = implode(CRM_Core_DAO::VALUE_SEPARATOR, $locales);
        $domain->save();
    }

    /**
     * Rebuild multilingual indices, views and triggers (useful for upgrades)
     *
     * @param $locales array  locales to be rebuilt
     * @param $version string version of schema structure to use
     * @return void
     */
    static function rebuildMultilingualSchema($locales, $version = null)
    {
        if ($version) {
            // fetch all the SchemaStructure versions we ship and sort by version
            $schemas = array();
            foreach (scandir(dirname(__FILE__)) as $file) {
                $matches = array();
                if (preg_match('/^SchemaStructure_([0-9a-z_]+)\.php$/', $file, $matches)) {
                    $schemas[] = str_replace('_', '.', $matches[1]);
                }
            }
            usort($schemas, 'version_compare');

            // find the latest schema structure older than (or equal to) $version
            do {
                $latest = array_pop($schemas);
            } while (version_compare($latest, $version, '>'));
            $latest = str_replace('.', '_', $latest);

            $class = "CRM_Core_I18n_SchemaStructure_{$latest}";
            require_once "CRM/Core/I18n/SchemaStructure_{$latest}.php";
        } else {
            $class = 'CRM_Core_I18n_SchemaStructure';
            require_once 'CRM/Core/I18n/SchemaStructure.php';
        }
        eval("\$indices =& $class::indices();");
        eval("\$tables  =& $class::tables();");
        $queries = array();
        $dao = new CRM_Core_DAO;

        // get all of the already existing indices
        $existing = array();
        foreach (array_keys($indices) as $table) {
            $existing[$table] = array();
            $dao->query("SHOW INDEX FROM $table", false);
            while ($dao->fetch()) {
                if (preg_match('/_[a-z][a-z]_[A-Z][A-Z]$/', $dao->Key_name)) {
                    $existing[$table][] = $dao->Key_name;
                }
            }
        }

        // from all of the CREATE INDEX queries fetch the ones creating missing indices
        foreach ($locales as $locale) {
            foreach (array_keys($indices) as $table) {
                $allQueries = self::createIndexQueries($locale, $table, $class);
                foreach ($allQueries as $name => $query) {
                    if (!in_array("{$name}_{$locale}", $existing[$table])) {
                        $queries[] = $query;
                    }
                }
            }
        }

        // rebuild views
        foreach ($locales as $locale) {
            foreach ($tables as $table) {
                $queries[] = self::createViewQuery($locale, $table, $dao, $class);
            }
        }

        // rebuild triggers
        $last = array_pop($locales);
        $queries = array_merge($queries, self::createTriggerQueries($locales, $last, $class));

        foreach ($queries as $query) {
            $dao->query($query, false);
        }
    }

    /**
     * Rewrite SQL query to use views to access tables with localized columns.
     *
     * @param $query string  the query for rewrite
     * @return string        the rewritten query
     */
    static function rewriteQuery($query)
    {
        static $tables = null;
        if ($tables === null) {
            $tables =& CRM_Core_I18n_SchemaStructure::tables();
        }
        global $dbLocale;
        foreach ($tables as $table) {
            $query = preg_replace("/([^'\"])({$table})([^_'\"])/", "\\1\\2{$dbLocale}\\3", $query);
        }
        // uncomment the below to rewrite the civicrm_value_* queries
        // $query = preg_replace("/(civicrm_value_[a-z0-9_]+_\d+)([^_])/", "\\1{$dbLocale}\\2", $query);
        return $query;
    }

    /**
     * CREATE INDEX queries for a given locale and table
     *
     * @param $locale string  locale for which the queries should be created (null to create original indices)
     * @param $table string   table for which the queries should be created
     * @param $class string   schema structure class to use
     * @return array          array of CREATE INDEX queries
     */
    private function createIndexQueries($locale, $table, $class = 'CRM_Core_I18n_SchemaStructure')
    {
        eval("\$indices =& $class::indices();");
        eval("\$columns =& $class::columns();");
        if (!isset($indices[$table])) return array();

        $queries = array();
        foreach ($indices[$table] as $index) {
            $unique = isset($index['unique']) && $index['unique'] ? 'UNIQUE' : '';
            foreach ($index['field'] as $i => $col) {
                // if a given column is localizable, extend its name with the locale
                if ($locale and isset($columns[$table][$col])) $index['field'][$i] = "{$col}_{$locale}";
            }
            $cols = implode(', ', $index['field']);
            $name = $index['name'];
            if ($locale) $name .= '_' . $locale;
            $queries[$index['name']] = "CREATE {$unique} INDEX {$name} ON {$table} ({$cols})";
        }
        return $queries;
    }

    /**
     * CREATE TRIGGER queries for a given set of locales
     *
     * @param $locales array  array of current database locales
     * @param $locale string  new locale to add
     * @param $class string   schema structure class to use
     * @return array          array of CREATE TRIGGER queries
     */
    private function createTriggerQueries($locales, $locale, $class = 'CRM_Core_I18n_SchemaStructure')
    {
        eval("\$columns =& $class::columns();");
        $queries = array();
        $namesTrigger = array();
        $individualNamesTrigger = array();
        
        foreach (array_merge($locales, array($locale)) as $loc) {
            $namesTrigger[] = "IF NEW.contact_type = 'Household' THEN";
            $namesTrigger[] = "SET NEW.display_name_{$loc} = NEW.household_name_{$loc};";
            $namesTrigger[] = "SET NEW.sort_name_{$loc} = NEW.household_name_{$loc};";

            $namesTrigger[] = "ELSEIF NEW.contact_type = 'Organization' THEN";
            $namesTrigger[] = "SET NEW.display_name_{$loc} = NEW.organization_name_{$loc};";
            $namesTrigger[] = "SET NEW.sort_name_{$loc} = NEW.organization_name_{$loc};";

            $namesTrigger[] = "ELSEIF NEW.contact_type = 'Individual' THEN";
            $namesTrigger[] = "SET @prefix := NULL;";
            $namesTrigger[] = "SET @suffix := NULL;";
            $namesTrigger[] = "IF NEW.prefix_id IS NOT NULL THEN SELECT v.label_{$loc} INTO @prefix FROM civicrm_option_value v JOIN civicrm_option_group g ON (v.option_group_id = g.id) WHERE g.name = 'individual_prefix' AND v.value = NEW.prefix_id; END IF;";
            $namesTrigger[] = "IF NEW.suffix_id IS NOT NULL THEN SELECT v.label_{$loc} INTO @suffix FROM civicrm_option_value v JOIN civicrm_option_group g ON (v.option_group_id = g.id) WHERE g.name = 'individual_suffix' AND v.value = NEW.suffix_id; END IF;";
            $namesTrigger[] = 'END IF;';
            $individualNamesTrigger[] = "IF NEW.contact_type = 'Individual' THEN";
            $individualNamesTrigger[] = "SET NEW.display_name_{$loc} = TRIM(REPLACE(CONCAT_WS(' ', @prefix, NEW.first_name_{$loc}, NEW.middle_name_{$loc}, NEW.last_name_{$loc}, @suffix), '  ', ' '));";
            $individualNamesTrigger[] = "SET NEW.sort_name_{$loc} = TRIM(', ' FROM CONCAT_WS(', ', NEW.last_name_{$loc}, NEW.first_name_{$loc}));";
            $individualNamesTrigger[] = 'END IF;';
            $individualNamesTrigger[] = "SELECT email INTO @email FROM civicrm_email WHERE is_primary = 1 AND contact_id = NEW.id LIMIT 1;";
            $individualNamesTrigger[] = "IF NEW.display_name_{$loc} = '' THEN SET NEW.display_name_{$loc} = @email; END IF;";
            $individualNamesTrigger[] = "IF NEW.sort_name_{$loc}    = '' THEN SET NEW.sort_name_{$loc}    = @email; END IF;";
        }
        
        $beforeUpdateNamesTrigger = implode(' ', $namesTrigger) . implode(' ', $individualNamesTrigger );
        // ...for UPDATE it's a separate trigger, for INSERT it has to be merged into the below, general one
        $queries[] = "DROP TRIGGER IF EXISTS civicrm_contact_before_update";
        $queries[] = "CREATE TRIGGER civicrm_contact_before_update BEFORE UPDATE ON civicrm_contact FOR EACH ROW BEGIN " . $beforeUpdateNamesTrigger . ' END';
        
        // take care of the ON INSERT triggers
        foreach ($columns as $table => $hash) {
            $queries[] = "DROP TRIGGER IF EXISTS {$table}_before_insert";

            $trigger = array();
            $trigger[] = "CREATE TRIGGER {$table}_before_insert BEFORE INSERT ON {$table} FOR EACH ROW BEGIN";

            if ($locales) {
                foreach ($hash as $column => $_) {
                    if ($table == 'civicrm_contact' and ($column == 'display_name' or $column == 'sort_name')) {
                        // {display,sort}_name are handled by $individualNamesTrigger and shouldn't be copied between languages
                        continue;
                    }
                    $trigger[] = "IF NEW.{$column}_{$locale} IS NOT NULL THEN";
                    foreach ($locales as $old) {
                        $trigger[] = "SET NEW.{$column}_{$old} = NEW.{$column}_{$locale};";
                    }
                    foreach ($locales as $old) {
                        $trigger[] = "ELSEIF NEW.{$column}_{$old} IS NOT NULL THEN";
                        foreach (array_merge($locales, array($locale)) as $loc) {
                            if ($loc == $old) continue;
                            $trigger[] = "SET NEW.{$column}_{$loc} = NEW.{$column}_{$old};";
                        }
                    }
                    $trigger[] = 'END IF;';
                }
            }

            if ($table == 'civicrm_contact') {
                $trigger = array_merge($trigger, $namesTrigger, $individualNamesTrigger);
            }
            $trigger[] = 'END';

            $queries[] = implode(' ', $trigger);
        }
        return $queries;
    }

    /**
     * CREATE VIEW query for a given locale and table
     *
     * @param $locale string  locale of the view
     * @param $table string   table of the view
     * @param $dao object     a DAO object to run DESCRIBE queries
     * @param $class string   schema structure class to use
     * @return array          array of CREATE INDEX queries
     */
    private function createViewQuery($locale, $table, &$dao, $class = 'CRM_Core_I18n_SchemaStructure')
    {
        eval("\$columns =& $class::columns();");
        $cols = array();
        $dao->query("DESCRIBE {$table}", false);
        while ($dao->fetch()) {
            // view non-internationalized columns directly
            if (!in_array($dao->Field, array_keys($columns[$table])) and
                !preg_match('/_[a-z][a-z]_[A-Z][A-Z]$/', $dao->Field)) {
                $cols[] = $dao->Field;
            }
        }
        // view intrernationalized columns through an alias
        foreach ($columns[$table] as $column => $_) {
            $cols[] = "{$column}_{$locale} {$column}";
        }
        return "CREATE OR REPLACE VIEW {$table}_{$locale} AS SELECT ". implode(', ', $cols) . " FROM {$table}";
    }
}
