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
require_once 'CRM/Utils/Type.php';
class CRM_Member_DAO_MembershipLog extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_membership_log';
    /**
     * static instance to hold the field values
     *
     * @var array
     * @static
     */
    static $_fields = null;
    /**
     * static instance to hold the FK relationships
     *
     * @var string
     * @static
     */
    static $_links = null;
    /**
     * static instance to hold the values that can
     * be imported / apu
     *
     * @var array
     * @static
     */
    static $_import = null;
    /**
     * static instance to hold the values that can
     * be exported / apu
     *
     * @var array
     * @static
     */
    static $_export = null;
    /**
     * static value to see if we should log any modifications to
     * this table in the civicrm_log table
     *
     * @var boolean
     * @static
     */
    static $_log = true;
    /**
     *
     * @var int unsigned
     */
    public $id;
    /**
     * FK to Membership table
     *
     * @var int unsigned
     */
    public $membership_id;
    /**
     * New status assigned to membership by this action. FK to Membership Status
     *
     * @var int unsigned
     */
    public $status_id;
    /**
     * New membership period start date
     *
     * @var date
     */
    public $start_date;
    /**
     * New membership period expiration date.
     *
     * @var date
     */
    public $end_date;
    /**
     * FK to Contact ID of person under whose credentials this data modification was made.
     *
     * @var int unsigned
     */
    public $modified_id;
    /**
     * Date this membership modification action was logged.
     *
     * @var date
     */
    public $modified_date;
    /**
     * The day we sent a renewal reminder
     *
     * @var date
     */
    public $renewal_reminder_date;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_membership_log
     */
    function __construct()
    {
        parent::__construct();
    }
    /**
     * return foreign links
     *
     * @access public
     * @return array
     */
    function &links()
    {
        if (!(self::$_links)) {
            self::$_links = array(
                'membership_id' => 'civicrm_membership:id',
                'status_id' => 'civicrm_membership_status:id',
                'modified_id' => 'civicrm_contact:id',
            );
        }
        return self::$_links;
    }
    /**
     * returns all the column names of this table
     *
     * @access public
     * @return array
     */
    function &fields()
    {
        if (!(self::$_fields)) {
            self::$_fields = array(
                'id' => array(
                    'name' => 'id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'membership_id' => array(
                    'name' => 'membership_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                    'FKClassName' => 'CRM_Member_DAO_Membership',
                ) ,
                'status_id' => array(
                    'name' => 'status_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Membership Status') ,
                    'required' => true,
                    'FKClassName' => 'CRM_Member_DAO_MembershipStatus',
                ) ,
                'start_date' => array(
                    'name' => 'start_date',
                    'type' => CRM_Utils_Type::T_DATE,
                    'title' => ts('Start Date') ,
                ) ,
                'end_date' => array(
                    'name' => 'end_date',
                    'type' => CRM_Utils_Type::T_DATE,
                    'title' => ts('End Date') ,
                ) ,
                'modified_id' => array(
                    'name' => 'modified_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Contact_DAO_Contact',
                ) ,
                'modified_date' => array(
                    'name' => 'modified_date',
                    'type' => CRM_Utils_Type::T_DATE,
                    'title' => ts('Membership Change Date') ,
                ) ,
                'renewal_reminder_date' => array(
                    'name' => 'renewal_reminder_date',
                    'type' => CRM_Utils_Type::T_DATE,
                    'title' => ts('Renewal Reminder Date') ,
                ) ,
            );
        }
        return self::$_fields;
    }
    /**
     * returns the names of this table
     *
     * @access public
     * @return string
     */
    function getTableName()
    {
        return self::$_tableName;
    }
    /**
     * returns if this table needs to be logged
     *
     * @access public
     * @return boolean
     */
    function getLog()
    {
        return self::$_log;
    }
    /**
     * returns the list of fields that can be imported
     *
     * @access public
     * return array
     */
    function &import($prefix = false)
    {
        if (!(self::$_import)) {
            self::$_import = array();
            $fields = & self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('import', $field)) {
                    if ($prefix) {
                        self::$_import['membership_log'] = & $fields[$name];
                    } else {
                        self::$_import[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_import;
    }
    /**
     * returns the list of fields that can be exported
     *
     * @access public
     * return array
     */
    function &export($prefix = false)
    {
        if (!(self::$_export)) {
            self::$_export = array();
            $fields = & self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('export', $field)) {
                    if ($prefix) {
                        self::$_export['membership_log'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
}
