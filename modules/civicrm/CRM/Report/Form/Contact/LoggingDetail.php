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

require_once 'CRM/Report/Form.php';

class CRM_Report_Form_Contact_LoggingDetail extends CRM_Report_Form
{
    private $loggingDB;

    private $log_conn_id;
    private $log_date;

    function __construct()
    {
        $this->_add2groupSupported = false; // don’t display the ‘Add these Contacts to Group’ button

        $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
        $this->loggingDB = $dsn['database'];

        $this->log_conn_id = CRM_Utils_Request::retrieve('log_conn_id', 'Integer', CRM_Core_DAO::$_nullObject);
        $this->log_date    = CRM_Utils_Request::retrieve('log_date',    'String',  CRM_Core_DAO::$_nullObject);

        // make sure the report works even without the params
        if (!$this->log_conn_id or !$this->log_date) {
            $dao = new CRM_Core_DAO;
            $dao->query("SELECT log_conn_id, log_date FROM {$this->loggingDB}.log_civicrm_contact WHERE log_action = 'Update' ORDER BY log_date DESC LIMIT 1");
            $dao->fetch();
            $this->log_conn_id = $dao->log_conn_id;
            $this->log_date    = $dao->log_date;
        }

        $this->_columnHeaders = array(
            'field' => array('title' => ts('Field')),
            'from'  => array('title' => ts('Changed From')),
            'to'    => array('title' => ts('Changed To')),
        );

        parent::__construct();
    }

    function buildRows($sql, &$rows)
    {
        // safeguard for when there aren’t any log entries yet
        if (!$this->log_conn_id or !$this->log_date) return;

        $params = array(
            1 => array($this->log_conn_id, 'Integer'),
            2 => array($this->log_date,    'String'),
        );

        // let the template know who updated whom when
        $sql = "
            SELECT who.id who_id, who.display_name who_name, whom.id whom_id, whom.display_name whom_name
            FROM {$this->loggingDB}.log_civicrm_contact l
            JOIN civicrm_contact who ON (l.log_user_id = who.id)
            JOIN civicrm_contact whom ON (l.id = whom.id)
            WHERE log_action = 'Update' AND log_conn_id = %1 AND log_date = %2 ORDER BY log_date DESC LIMIT 1
        ";
        $dao =& CRM_Core_DAO::executeQuery($sql, $params);
        $dao->fetch();
        $this->assign('who_url',   CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->who_id}"));
        $this->assign('whom_url',  CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->whom_id}"));
        $this->assign('who_name',  $dao->who_name);
        $this->assign('whom_name', $dao->whom_name);
        $this->assign('log_date',  $this->log_date);

        // link back to summary report
        require_once 'CRM/Report/Utils/Report.php';
        $this->assign('summaryReportURL', CRM_Report_Utils_Report::getNextUrl('logging/contact/summary', 'reset=1', false, true));

        // we look for the last change in the given connection that happended less than 10 seconds later than log_date to catch multi-query changes
        $changedSQL = "SELECT * FROM {$this->loggingDB}.log_civicrm_contact WHERE log_conn_id = %1 AND log_date < DATE_ADD(%2, INTERVAL 10 SECOND) ORDER BY log_date DESC LIMIT 1";
        $changed    = $this->sqlToArray($changedSQL, $params);

        // we look for the previous state (different log_conn_id) of the found id
        $params[3]   = array($changed['id'], 'Integer');
        $originalSQL = "SELECT * FROM {$this->loggingDB}.log_civicrm_contact WHERE log_conn_id != %1 AND log_date < %2 AND id = %3 ORDER BY log_date DESC LIMIT 1";
        $original    = $this->sqlToArray($originalSQL, $params);

        require_once 'CRM/Contact/DAO/Contact.php';
        $dao = new CRM_Contact_DAO_Contact;
        $fields =& $dao->fields();

        // populate $rows with only the differences between $changed and $original (skipping log_* columns)
        foreach (array_keys(array_diff_assoc($changed, $original)) as $diff) {
            if (substr($diff, 0, 4) == 'log_') continue;
            $rows[] = array(
                'field' => isset($fields[$diff]['title']) ? $fields[$diff]['title'] : $diff,
                'from'  => $original[$diff],
                'to'    => $changed[$diff],
            );
        }
    }

    function buildQuery()
    {
    }

    private function sqlToArray($sql, $params)
    {
        $dao =& CRM_Core_DAO::executeQuery($sql, $params);
        $dao->fetch();
        return $dao->toArray();
    }
}
