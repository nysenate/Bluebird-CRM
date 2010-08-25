<?php
/*
+--------------------------------------------------------------------+
| CiviCRM version 3.1                                                |
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
class CRM_Contribute_DAO_PCP extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_pcp';
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
    static $_log = false;
    /**
     * Personal Campaign Page ID
     *
     * @var int unsigned
     */
    public $id;
    /**
     * FK to Contact ID
     *
     * @var int unsigned
     */
    public $contact_id;
    /**
     *
     * @var int unsigned
     */
    public $status_id;
    /**
     *
     * @var string
     */
    public $title;
    /**
     *
     * @var text
     */
    public $intro_text;
    /**
     *
     * @var text
     */
    public $page_text;
    /**
     *
     * @var string
     */
    public $donate_link_text;
    /**
     * The Contribution Page which triggered this pcp
     *
     * @var int unsigned
     */
    public $contribution_page_id;
    /**
     *
     * @var int unsigned
     */
    public $is_thermometer;
    /**
     *
     * @var int unsigned
     */
    public $is_honor_roll;
    /**
     * Goal amount of this Personal Campaign Page.
     *
     * @var float
     */
    public $goal_amount;
    /**
     * 3 character string, value from config setting or input via user.
     *
     * @var string
     */
    public $currency;
    /**
     *
     * @var string
     */
    public $referer;
    /**
     * Is Personal Campaign Page enabled/active?
     *
     * @var boolean
     */
    public $is_active;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_pcp
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
                'contact_id' => 'civicrm_contact:id',
                'contribution_page_id' => 'civicrm_contribution_page:id',
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
                'pcp_id' => array(
                    'name' => 'id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Personal Campaign Page ID') ,
                    'required' => true,
                ) ,
                'pcp_contact_id' => array(
                    'name' => 'contact_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Contact ID') ,
                    'required' => true,
                    'FKClassName' => 'CRM_Contact_DAO_Contact',
                ) ,
                'status_id' => array(
                    'name' => 'status_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Personal Campaign Page Status') ,
                    'required' => true,
                ) ,
                'title' => array(
                    'name' => 'title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Personal Campaign Page Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'default' => 'UL',
                ) ,
                'intro_text' => array(
                    'name' => 'intro_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Intro Text') ,
                    'default' => 'UL',
                ) ,
                'page_text' => array(
                    'name' => 'page_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Page Text') ,
                    'default' => 'UL',
                ) ,
                'donate_link_text' => array(
                    'name' => 'donate_link_text',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Donate Link Text') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'default' => 'UL',
                ) ,
                'contribution_page_id' => array(
                    'name' => 'contribution_page_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                    'FKClassName' => 'CRM_Contribute_DAO_ContributionPage',
                ) ,
                'is_thermometer' => array(
                    'name' => 'is_thermometer',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'is_honor_roll' => array(
                    'name' => 'is_honor_roll',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'goal_amount' => array(
                    'name' => 'goal_amount',
                    'type' => CRM_Utils_Type::T_MONEY,
                    'title' => ts('Goal Amount') ,
                ) ,
                'currency' => array(
                    'name' => 'currency',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Currency') ,
                    'required' => true,
                    'maxlength' => 3,
                    'size' => CRM_Utils_Type::FOUR,
                ) ,
                'referer' => array(
                    'name' => 'referer',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Referer') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'default' => 'UL',
                ) ,
                'is_active' => array(
                    'name' => 'is_active',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
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
                        self::$_import['pcp'] = & $fields[$name];
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
                        self::$_export['pcp'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
}
