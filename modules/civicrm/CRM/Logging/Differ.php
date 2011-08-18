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

require_once 'CRM/Contribute/PseudoConstant.php';
require_once 'CRM/Core/PseudoConstant.php';

class CRM_Logging_Differ
{
    private $db;
    private $log_conn_id;
    private $log_date;

    function __construct($log_conn_id, $log_date)
    {
        $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
        $this->db          = $dsn['database'];
        $this->log_conn_id = $log_conn_id;
        $this->log_date    = $log_date;
    }

    function diffsInTables($tables)
    {
        $diffs = array();
        foreach ($tables as $table) {
            $diff = $this->diffsInTable($table);
            if (!empty($diff)) $diffs[$table] = $diff;
        }
        return $diffs;
    }

    function diffsInTable($table)
    {
        $diffs = array();

        $params = array(
            1 => array($this->log_conn_id, 'Integer'),
            2 => array($this->log_date,    'String'),
        );

        // find ids in this table that were affected in the given connection (based on connection id and a ±10 s time period around the date)
        $sql = "SELECT DISTINCT id FROM `{$this->db}`.`log_$table` WHERE log_conn_id = %1 AND log_date BETWEEN DATE_SUB(%2, INTERVAL 10 SECOND) AND DATE_ADD(%2, INTERVAL 10 SECOND)";
        $dao =& CRM_Core_DAO::executeQuery($sql, $params);
        while ($dao->fetch()) {
            $diffs = array_merge($diffs, $this->diffsInTableForId($table, $dao->id));
        }

        return $diffs;
    }

    private function diffsInTableForId($table, $id)
    {
        $diffs = array();

        $params = array(
            1 => array($this->log_conn_id, 'Integer'),
            2 => array($this->log_date,    'String'),
            3 => array($id,                'Integer'),
        );

        // look for the last change in the given connection that happended less than 10 seconds later than log_date to the given id to catch multi-query changes
        $changedSQL = "SELECT * FROM `{$this->db}`.`log_$table` WHERE log_conn_id = %1 AND log_date < DATE_ADD(%2, INTERVAL 10 SECOND) AND id = %3 ORDER BY log_date DESC LIMIT 1";
        $changed    = $this->sqlToArray($changedSQL, $params);

        // return early if nothing found
        if (empty($changed)) return array();

        switch ($changed['log_action']) {
        case 'Delete':
            // the previous state is kept in the current state, current should keep the keys and clear the values
            $original = $changed;
            foreach ($changed as &$val) $val = null;
            $changed['log_action'] = 'Delete';
            break;
        case 'Insert':
            // the previous state does not exist
            $original = array();
            break;
        case 'Update':
            // look for the previous state (different log_conn_id) of the given id
            $originalSQL = "SELECT * FROM `{$this->db}`.`log_$table` WHERE log_conn_id != %1 AND log_date < %2 AND id = %3 ORDER BY log_date DESC LIMIT 1";
            $original    = $this->sqlToArray($originalSQL, $params);
            break;
        }

        // populate $diffs with only the differences between $changed and $original
        $skipped = array('log_action', 'log_conn_id', 'log_date', 'log_user_id');
        foreach (array_keys(array_diff_assoc($changed, $original)) as $diff) {
            if (in_array($diff, $skipped))            continue;
            if ($original[$diff] === $changed[$diff]) continue;
            $diffs[] = array(
                'action' => $changed['log_action'],
                'id'     => $id,
                'field'  => $diff,
                'from'   => $original[$diff],
                'to'     => $changed[$diff],
            );
        }

        return $diffs;
    }

    function titlesAndValuesForTable($table)
    {
        // static caches for subsequent calls with the same $table
        static $titles = array();
        static $values = array();

        // FIXME: split off the table → DAO mapping to a GenCode-generated class
        static $daos = array(
            'civicrm_address'      => 'CRM_Core_DAO_Address',
            'civicrm_contact'      => 'CRM_Contact_DAO_Contact',
            'civicrm_email'        => 'CRM_Core_DAO_Email',
            'civicrm_im'           => 'CRM_Core_DAO_IM',
            'civicrm_openid'       => 'CRM_Core_DAO_OpenID',
            'civicrm_phone'        => 'CRM_Core_DAO_Phone',
            'civicrm_website'      => 'CRM_Core_DAO_Website',
            'civicrm_contribution' => 'CRM_Contribute_DAO_Contribution',
        );

        if (!isset($titles[$table]) or !isset($values[$table])) {

            if (in_array($table, array_keys($daos))) {
                // FIXME: these should be populated with pseudo constants as they
                // were at the time of logging rather than their current values
                $values[$table] = array(
                    'contribution_page_id'           => CRM_Contribute_PseudoConstant::contributionPage(),
                    'contribution_status_id'         => CRM_Contribute_PseudoConstant::contributionStatus(),
                    'contribution_type_id'           => CRM_Contribute_PseudoConstant::contributionType(),
                    'country_id'                     => CRM_Core_PseudoConstant::country(),
                    'gender_id'                      => CRM_Core_PseudoConstant::gender(),
                    'location_type_id'               => CRM_Core_PseudoConstant::locationType(),
                    'payment_instrument_id'          => CRM_Contribute_PseudoConstant::paymentInstrument(),
                    'phone_type_id'                  => CRM_Core_PseudoConstant::phoneType(),
                    'preferred_communication_method' => CRM_Core_PseudoConstant::pcm(),
                    'preferred_language'             => CRM_Core_PseudoConstant::languages(),
                    'prefix_id'                      => CRM_Core_PseudoConstant::individualPrefix(),
                    'provider_id'                    => CRM_Core_PseudoConstant::IMProvider(),
                    'state_province_id'              => CRM_Core_PseudoConstant::stateProvince(),
                    'suffix_id'                      => CRM_Core_PseudoConstant::individualSuffix(),
                    'website_type_id'                => CRM_Core_PseudoConstant::websiteType(),
                );

                require_once str_replace('_', DIRECTORY_SEPARATOR, $daos[$table]) . '.php';
                eval("\$dao = new $daos[$table];");
                foreach ($dao->fields() as $field) {
                    $titles[$table][$field['name']] = $field['title'];

                    if ($field['type'] == CRM_Utils_Type::T_BOOLEAN) {
                        $values[$table][$field['name']] = array('0' => ts('false'), '1' => ts('true'));
                    }
                }
            } elseif (substr($table, 0, 14) == 'civicrm_value_') {
                list($titles[$table], $values[$table]) = $this->titlesAndValuesForCustomDataTable($table);
            }
        }

        return array($titles[$table], $values[$table]);
    }

    private function sqlToArray($sql, $params)
    {
        $dao =& CRM_Core_DAO::executeQuery($sql, $params);
        $dao->fetch();
        return $dao->toArray();
    }

    private function titlesAndValuesForCustomDataTable($table)
    {
        $titles = array();
        $values = array();

        $params = array(
            1 => array($this->log_conn_id, 'Integer'),
            2 => array($this->log_date,    'String'),
            3 => array($table,             'String'),
        );

        $sql = "SELECT id, title FROM `{$this->db}`.log_civicrm_custom_group WHERE log_date <= %2 AND table_name = %3 ORDER BY log_date DESC LIMIT 1";
        $cgDao =& CRM_Core_DAO::executeQuery($sql, $params);
        $cgDao->fetch();

        $params[3] = array($cgDao->id, 'Integer');
        $sql = "SELECT column_name, data_type, label, name, option_group_id FROM `{$this->db}`.log_civicrm_custom_field WHERE log_date <= %2 AND custom_group_id = %3 ORDER BY log_date";
        $cfDao =& CRM_Core_DAO::executeQuery($sql, $params);

        while ($cfDao->fetch()) {
            $titles[$cfDao->column_name] = "{$cgDao->title}: {$cfDao->label}";

            switch ($cfDao->data_type) {
            case 'Boolean':
                $values[$cfDao->column_name] = array('0' => ts('false'), '1' => ts('true'));
                break;
            case 'String':
                $values[$cfDao->column_name] = array();
                $params[3] = array($cfDao->option_group_id, 'Integer');
                $sql = "SELECT label, value FROM `{$this->db}`.log_civicrm_option_value WHERE log_date <= %2 AND option_group_id = %3 ORDER BY log_date";
                $ovDao =& CRM_Core_DAO::executeQuery($sql, $params);
                while ($ovDao->fetch()) {
                    $values[$cfDao->column_name][$ovDao->value] = $ovDao->label;
                }
                break;
            }
        }

        return array($titles, $values);
    }
}
