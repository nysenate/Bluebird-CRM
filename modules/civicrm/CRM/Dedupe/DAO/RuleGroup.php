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
class CRM_Dedupe_DAO_RuleGroup extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_dedupe_rule_group';
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
     * Unique dedupe rule group id
     *
     * @var int unsigned
     */
    public $id;
    /**
     * The type of contacts this group applies to
     *
     * @var enum('Individual', 'Organization', 'Household')
     */
    public $contact_type;
    /**
     * The weight threshold the sum of the rule weights has to cross to consider two contacts the same
     *
     * @var int
     */
    public $threshold;
    /**
     * Whether the rule should be used for cases where strict maching of the given contact type is required or a fuzzy one
     *
     * @var enum('Strict', 'Fuzzy')
     */
    public $level;
    /**
     * Is this a default rule (one rule for every contact type + level combination should be default)
     *
     * @var boolean
     */
    public $is_default;
    /**
     * Name of the rule group
     *
     * @var string
     */
    public $name;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_dedupe_rule_group
     */
    function __construct()
    {
        parent::__construct();
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
                'contact_type' => array(
                    'name' => 'contact_type',
                    'type' => CRM_Utils_Type::T_ENUM,
                    'title' => ts('Contact Type') ,
                    'enumValues' => 'Individual, Organization, Household',
                ) ,
                'threshold' => array(
                    'name' => 'threshold',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Threshold') ,
                    'required' => true,
                ) ,
                'level' => array(
                    'name' => 'level',
                    'type' => CRM_Utils_Type::T_ENUM,
                    'title' => ts('Level') ,
                    'enumValues' => 'Strict, Fuzzy',
                ) ,
                'is_default' => array(
                    'name' => 'is_default',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'name' => array(
                    'name' => 'name',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Name') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
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
                        self::$_import['dedupe_rule_group'] = & $fields[$name];
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
                        self::$_export['dedupe_rule_group'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
    /**
     * returns an array containing the enum fields of the civicrm_dedupe_rule_group table
     *
     * @return array (reference)  the array of enum fields
     */
    static function &getEnums()
    {
        static $enums = array(
            'contact_type',
            'level',
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
                'contact_type' => array(
                    'Individual' => ts('Individual') ,
                    'Organization' => ts('Organization') ,
                    'Household' => ts('Household') ,
                ) ,
                'level' => array(
                    'Strict' => ts('Strict') ,
                    'Fuzzy' => ts('Fuzzy') ,
                ) ,
            );
        }
        return $translations[$field][$value];
    }
    /**
     * adds $value['foo_display'] for each $value['foo'] enum from civicrm_dedupe_rule_group
     *
     * @param array $values (reference)  the array up for enhancing
     * @return void
     */
    static function addDisplayEnums(&$values)
    {
        $enumFields = & CRM_Dedupe_DAO_RuleGroup::getEnums();
        foreach($enumFields as $enum) {
            if (isset($values[$enum])) {
                $values[$enum . '_display'] = CRM_Dedupe_DAO_RuleGroup::tsEnum($enum, $values[$enum]);
            }
        }
    }
}
