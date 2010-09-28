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
class CRM_Core_DAO_FinancialTrxn extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_financial_trxn';
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
     * Gift ID
     *
     * @var int unsigned
     */
    public $id;
    /**
     * FK to financial_account table.
     *
     * @var int unsigned
     */
    public $from_account_id;
    /**
     * FK to financial_account table.
     *
     * @var int unsigned
     */
    public $to_account_id;
    /**
     *
     * @var datetime
     */
    public $trxn_date;
    /**
     *
     * @var enum('Debit', 'Credit')
     */
    public $trxn_type;
    /**
     * amount of transaction
     *
     * @var float
     */
    public $total_amount;
    /**
     * actual processor fee if known - may be 0.
     *
     * @var float
     */
    public $fee_amount;
    /**
     * actual funds transfer amount. total less fees. if processor does not report actual fee during transaction, this is set to total_amount.
     *
     * @var float
     */
    public $net_amount;
    /**
     * 3 character string, value from config setting or input via user.
     *
     * @var string
     */
    public $currency;
    /**
     * derived from Processor setting in civicrm.settings.php.
     *
     * @var string
     */
    public $payment_processor;
    /**
     * unique processor transaction id, bank id + trans id,... depending on payment_method
     *
     * @var string
     */
    public $trxn_id;
    /**
     * processor result code
     *
     * @var string
     */
    public $trxn_result_code;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_financial_trxn
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
                'from_account_id' => 'civicrm_financial_account:id',
                'to_account_id' => 'civicrm_financial_account:id',
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
                'from_account_id' => array(
                    'name' => 'from_account_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Core_DAO_FinancialAccount',
                ) ,
                'to_account_id' => array(
                    'name' => 'to_account_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Core_DAO_FinancialAccount',
                ) ,
                'trxn_date' => array(
                    'name' => 'trxn_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Trxn Date') ,
                    'required' => true,
                ) ,
                'trxn_type' => array(
                    'name' => 'trxn_type',
                    'type' => CRM_Utils_Type::T_ENUM,
                    'title' => ts('Trxn Type') ,
                    'required' => true,
                    'enumValues' => 'Debit,Credit',
                ) ,
                'total_amount' => array(
                    'name' => 'total_amount',
                    'type' => CRM_Utils_Type::T_MONEY,
                    'title' => ts('Total Amount') ,
                    'required' => true,
                ) ,
                'fee_amount' => array(
                    'name' => 'fee_amount',
                    'type' => CRM_Utils_Type::T_MONEY,
                    'title' => ts('Fee Amount') ,
                ) ,
                'net_amount' => array(
                    'name' => 'net_amount',
                    'type' => CRM_Utils_Type::T_MONEY,
                    'title' => ts('Net Amount') ,
                ) ,
                'currency' => array(
                    'name' => 'currency',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Currency') ,
                    'required' => true,
                    'maxlength' => 3,
                    'size' => CRM_Utils_Type::FOUR,
                    'import' => true,
                    'where' => 'civicrm_financial_trxn.currency',
                    'headerPattern' => '/cur(rency)?/i',
                    'dataPattern' => '/^[A-Z]{3}$/',
                    'export' => true,
                ) ,
                'payment_processor' => array(
                    'name' => 'payment_processor',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Payment Processor') ,
                    'required' => true,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'trxn_id' => array(
                    'name' => 'trxn_id',
                    'type' => CRM_Utils_Type::T_STRING,
                    'required' => true,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'trxn_result_code' => array(
                    'name' => 'trxn_result_code',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Trxn Result Code') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
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
                        self::$_import['financial_trxn'] = & $fields[$name];
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
                        self::$_export['financial_trxn'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
    /**
     * returns an array containing the enum fields of the civicrm_financial_trxn table
     *
     * @return array (reference)  the array of enum fields
     */
    static function &getEnums()
    {
        static $enums = array(
            'trxn_type',
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
                'trxn_type' => array(
                    'Debit' => ts('Debit') ,
                    'Credit' => ts('Credit') ,
                ) ,
            );
        }
        return $translations[$field][$value];
    }
    /**
     * adds $value['foo_display'] for each $value['foo'] enum from civicrm_financial_trxn
     *
     * @param array $values (reference)  the array up for enhancing
     * @return void
     */
    static function addDisplayEnums(&$values)
    {
        $enumFields = & CRM_Core_DAO_FinancialTrxn::getEnums();
        foreach($enumFields as $enum) {
            if (isset($values[$enum])) {
                $values[$enum . '_display'] = CRM_Core_DAO_FinancialTrxn::tsEnum($enum, $values[$enum]);
            }
        }
    }
}
