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
require_once 'CRM/Utils/Type.php';
class CRM_Contribute_DAO_PCPBlock extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_pcp_block';
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
     * PCP block Id
     *
     * @var int unsigned
     */
    public $id;
    /**
     *
     * @var string
     */
    public $entity_table;
    /**
     * FK to civicrm_contribution_page.id
     *
     * @var int unsigned
     */
    public $entity_id;
    /**
     * FK to civicrm_uf_group.id. Does Personal Campaign Page require manual activation by administrator? (is inactive by default after setup)?
     *
     * @var int unsigned
     */
    public $supporter_profile_id;
    /**
     * Does Personal Campaign Page require manual activation by administrator? (is inactive by default after setup)?
     *
     * @var boolean
     */
    public $is_approval_needed;
    /**
     * Does Personal Campaign Page allow using tell a friend?
     *
     * @var boolean
     */
    public $is_tellfriend_enabled;
    /**
     * Maximum recipient fields allowed in tell a friend
     *
     * @var int unsigned
     */
    public $tellfriend_limit;
    /**
     * Link text for PCP.
     *
     * @var string
     */
    public $link_text;
    /**
     * Is Personal Campaign Page Block enabled/active?
     *
     * @var boolean
     */
    public $is_active;
    /**
     * If set, notification is automatically emailed to this email-address on create/update Personal Campaign Page
     *
     * @var string
     */
    public $notify_email;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_pcp_block
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
                'entity_id' => 'civicrm_contribution_page:id',
                'supporter_profile_id' => 'civicrm_uf_group:id',
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
                'entity_table' => array(
                    'name' => 'entity_table',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Entity Table') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'entity_id' => array(
                    'name' => 'entity_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                    'FKClassName' => 'CRM_Contribute_DAO_ContributionPage',
                ) ,
                'supporter_profile_id' => array(
                    'name' => 'supporter_profile_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'default' => 'UL',
                    'FKClassName' => 'CRM_Core_DAO_UFGroup',
                ) ,
                'is_approval_needed' => array(
                    'name' => 'is_approval_needed',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'default' => 'UL',
                ) ,
                'is_tellfriend_enabled' => array(
                    'name' => 'is_tellfriend_enabled',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'default' => 'UL',
                ) ,
                'tellfriend_limit' => array(
                    'name' => 'tellfriend_limit',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Tellfriend Limit') ,
                    'default' => 'UL',
                ) ,
                'link_text' => array(
                    'name' => 'link_text',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Link Text') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'default' => 'UL',
                ) ,
                'is_active' => array(
                    'name' => 'is_active',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'default' => '',
                ) ,
                'notify_email' => array(
                    'name' => 'notify_email',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Notify Email') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'default' => 'UL',
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
                        self::$_import['pcp_block'] = & $fields[$name];
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
                        self::$_export['pcp_block'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
}
