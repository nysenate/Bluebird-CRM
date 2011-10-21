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

require_once 'CRM/Report/Form.php';

class CRM_Logging_ReportSummary extends CRM_Report_Form
{
    protected $cid;
    protected $loggingDB;

    function __construct()
    {
        $this->_add2groupSupported = false; // don’t display the ‘Add these Contacts to Group’ button

        $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
        $this->loggingDB = $dsn['database'];

        // used for redirect back to contact summary
        $this->cid = CRM_Utils_Request::retrieve('cid', 'Integer', CRM_Core_DAO::$_nullObject);

        parent::__construct();
    }

    function groupBy()
    {
        $this->_groupBy = 'GROUP BY log_conn_id, log_user_id, EXTRACT(DAY_MINUTE FROM log_date)';
    }

    function orderBy()
    {
        $this->_orderBy = 'ORDER BY log_date DESC';
    }

    function select() {
        $select = array();
        $this->_columnHeaders = array();
        foreach ($this->_columns as $tableName => $table) {
            if (array_key_exists('fields', $table)) {
                foreach ($table['fields'] as $fieldName => $field) {
                    if (CRM_Utils_Array::value('required', $field) or CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
                        $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value('type', $field);
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display']  = CRM_Utils_Array::value('no_display', $field);
                        $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
                    }
                }
            }
        }
        $this->_select = 'SELECT ' . implode(', ', $select) . ' ';
    }

    function where()
    {
        parent::where();
        $this->_where .= " AND (log_action != 'Initialization')";
    }
}
