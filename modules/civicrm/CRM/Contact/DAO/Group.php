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
class CRM_Contact_DAO_Group extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_group';
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
     * Group ID
     *
     * @var int unsigned
     */
    public $id;
    /**
     * Internal name of Group.
     *
     * @var string
     */
    public $name;
    /**
     * Name of Group.
     *
     * @var string
     */
    public $title;
    /**
     * Optional verbose description of the group.
     *
     * @var text
     */
    public $description;
    /**
     * Module or process which created this group.
     *
     * @var string
     */
    public $source;
    /**
     * FK to saved search table.
     *
     * @var int unsigned
     */
    public $saved_search_id;
    /**
     * Is this entry active?
     *
     * @var boolean
     */
    public $is_active;
    /**
     * In what context(s) is this field visible.
     *
     * @var enum('User and User Admin Only', 'Public Pages')
     */
    public $visibility;
    /**
     * the sql where clause if a saved search acl
     *
     * @var text
     */
    public $where_clause;
    /**
     * the tables to be included in a select data
     *
     * @var text
     */
    public $select_tables;
    /**
     * the tables to be included in the count statement
     *
     * @var text
     */
    public $where_tables;
    /**
     * FK to group type
     *
     * @var string
     */
    public $group_type;
    /**
     * Date when we created the cache for a smart group
     *
     * @var datetime
     */
    public $cache_date;
    /**
     * IDs of the parent(s)
     *
     * @var text
     */
    public $parents;
    /**
     * IDs of the child(ren)
     *
     * @var text
     */
    public $children;
    /**
     * Is this group hidden?
     *
     * @var boolean
     */
    public $is_hidden;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_group
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
                'saved_search_id' => 'civicrm_saved_search:id',
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
                'name' => array(
                    'name' => 'name',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Name') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'title' => array(
                    'name' => 'title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Title') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'description' => array(
                    'name' => 'description',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Description') ,
                    'rows' => 2,
                    'cols' => 60,
                ) ,
                'source' => array(
                    'name' => 'source',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Source') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'saved_search_id' => array(
                    'name' => 'saved_search_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Contact_DAO_SavedSearch',
                ) ,
                'is_active' => array(
                    'name' => 'is_active',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'visibility' => array(
                    'name' => 'visibility',
                    'type' => CRM_Utils_Type::T_ENUM,
                    'title' => ts('Visibility') ,
                    'default' => 'User and User Admin Only',
                    'enumValues' => 'User and User Admin Only,Public Pages',
                ) ,
                'where_clause' => array(
                    'name' => 'where_clause',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Where Clause') ,
                ) ,
                'select_tables' => array(
                    'name' => 'select_tables',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Select Tables') ,
                ) ,
                'where_tables' => array(
                    'name' => 'where_tables',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Where Tables') ,
                ) ,
                'group_type' => array(
                    'name' => 'group_type',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Group Type') ,
                    'maxlength' => 128,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'cache_date' => array(
                    'name' => 'cache_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Cache Date') ,
                ) ,
                'parents' => array(
                    'name' => 'parents',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Parents') ,
                ) ,
                'children' => array(
                    'name' => 'children',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Children') ,
                ) ,
                'is_hidden' => array(
                    'name' => 'is_hidden',
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
                        self::$_import['group'] = & $fields[$name];
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
                        self::$_export['group'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
    /**
     * returns an array containing the enum fields of the civicrm_group table
     *
     * @return array (reference)  the array of enum fields
     */
    static function &getEnums()
    {
        static $enums = array(
            'visibility',
        );
        return $enums;
    }
    /**
     * returns a ts()-translated enum value for display purposes
     *
     * @param string $field  the enum field in question
     * @param string $value  the enum value up for translation
     *
     * @return string  the display value of the enum
     */
    static function tsEnum($field, $value)
    {
        static $translations = null;
        if (!$translations) {
            $translations = array(
                'visibility' => array(
                    'User and User Admin Only' => ts('User and User Admin Only') ,
                    'Public Pages' => ts('Public Pages') ,
                ) ,
            );
        }
        return $translations[$field][$value];
    }
    /**
     * adds $value['foo_display'] for each $value['foo'] enum from civicrm_group
     *
     * @param array $values (reference)  the array up for enhancing
     * @return void
     */
    static function addDisplayEnums(&$values)
    {
        $enumFields = & CRM_Contact_DAO_Group::getEnums();
        foreach($enumFields as $enum) {
            if (isset($values[$enum])) {
                $values[$enum . '_display'] = CRM_Contact_DAO_Group::tsEnum($enum, $values[$enum]);
            }
        }
    }
}
