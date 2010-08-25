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

require_once 'CRM/Admin/Form.php';
require_once 'CRM/Dedupe/DAO/Rule.php';
require_once 'CRM/Dedupe/BAO/RuleGroup.php';

/**
 * This class generates form components for DedupeRules
 * 
 */
class CRM_Admin_Form_DedupeRules extends CRM_Admin_Form
{
    const RULES_COUNT = 5;
    protected $_contactType;
    protected $_defaults = array();
    protected $_fields   = array();
    protected $_rgid;

    /**
     * Function to pre processing
     *
     * @return None
     * @access public
     */
    function preProcess()
    {
        $this->_rgid        = CRM_Utils_Request::retrieve('id', 'Positive', $this, false, 0);
        $this->_contactType = CRM_Utils_Request::retrieve('contact_type', 'String', $this, false, 0);
        if ($this->_rgid) {
            $rgDao            = new CRM_Dedupe_DAO_RuleGroup();
            $rgDao->id        = $this->_rgid;
            $rgDao->find(true);
            $this->_defaults['threshold']  = $rgDao->threshold;
            $this->_contactType            = $rgDao->contact_type;
            $this->_defaults['level']      = $rgDao->level;
            $this->_defaults['name']       = $rgDao->name;
            $this->_defaults['is_default'] = $rgDao->is_default;
            $ruleDao = new CRM_Dedupe_DAO_Rule();
            $ruleDao->dedupe_rule_group_id = $this->_rgid;
            $ruleDao->find();
            $count = 0;
            while ($ruleDao->fetch()) {
                $this->_defaults["where_$count"]  = "{$ruleDao->rule_table}.{$ruleDao->rule_field}";
                $this->_defaults["length_$count"] = $ruleDao->rule_length;
                $this->_defaults["weight_$count"] = $ruleDao->rule_weight;
                $count++;
            }
        }
        $supported =& CRM_Dedupe_BAO_RuleGroup::supportedFields($this->_contactType);
        if ( is_array( $supported ) ) {
            foreach($supported as $table => $fields) {
                foreach($fields as $field => $title) {
                    $this->_fields["$table.$field"] = $title;
                }
            }
        }
        asort($this->_fields);
    }
    
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm()
    {
        $this->add('text', 'name', ts('Rule Name') );
        $levelType = array(
                           'Fuzzy'  => ts('Fuzzy'),
                           'Strict' => ts('Strict')
                           );
        $ruleLevel = $this->add('select', 'level', ts('Level'), $levelType);

        $default = $this->add('checkbox', 'is_default', ts('Default?') );
        if ( CRM_Utils_Array::value( 'is_default', $this->_defaults ) ) {
            $default->freeze();
            $ruleLevel->freeze();
        }
        
        for ($count = 0; $count < self::RULES_COUNT; $count++) {
            $this->add('select', "where_$count", ts('Field'), array(null => ts('- none -')) + $this->_fields);
            $this->add('text', "length_$count", ts('Length'), array('class' => 'two', 'style' => 'text-align: right'));
            $this->add('text', "weight_$count", ts('Weight'), array('class' => 'two', 'style' => 'text-align: right'));
        }
        $this->add('text', 'threshold', ts("Weight Threshold to Consider Contacts 'Matching':"), array('class' => 'two', 'style' => 'text-align: right'));
        $this->addButtons(array(
            array('type' => 'next',   'name' => ts('Save'), 'isDefault' => true),
            array('type' => 'cancel', 'name' => ts('Cancel')),
        ));
        $this->assign('contact_type', $this->_contactType);
    }

    function setDefaultValues()
    {
        return $this->_defaults;
    }

    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $values = $this->exportValues();
        $isDefault = CRM_Utils_Array::value( 'is_default', $values, false );
        // reset defaults
        if ( $isDefault ) {
            $query = "
UPDATE civicrm_dedupe_rule_group 
   SET is_default = 0
 WHERE contact_type = %1 
   AND level = %2";
            $queryParams = array( 1 => array( $this->_contactType, 'String' ),
                                  2 => array( $values['level'], 'String' ) ); 
            CRM_Core_DAO::executeQuery( $query, $queryParams );
        }

        $rgDao            = new CRM_Dedupe_DAO_RuleGroup();
        if ($this->_action & CRM_Core_Action::UPDATE ) {
            $rgDao->id           = $this->_rgid;
        }
        $rgDao->threshold    = $values['threshold'];
        $rgDao->name         = $values['name'];
        $rgDao->level        = $values['level'];
        $rgDao->contact_type = $this->_contactType;
        $rgDao->is_default   = $isDefault;
        $rgDao->save();
        
        $ruleDao = new CRM_Dedupe_DAO_Rule();
        $ruleDao->dedupe_rule_group_id = $rgDao->id;
        $ruleDao->delete();
        $ruleDao->free();

        $substrLenghts = array();

        $tables = array( );
        for ($count = 0; $count < self::RULES_COUNT; $count++) {
            if ( ! CRM_Utils_Array::value( "where_$count", $values ) ) {
                continue;
            }
            list($table, $field) = explode('.', CRM_Utils_Array::value( "where_$count", $values ) );
            $length = CRM_Utils_Array::value( "length_$count", $values ) ? CRM_Utils_Array::value( "length_$count", $values ) : null;
            $weight = $values["weight_$count"];
            if ($table and $field) {
                $ruleDao = new CRM_Dedupe_DAO_Rule();
                $ruleDao->dedupe_rule_group_id = $rgDao->id;
                $ruleDao->rule_table           = $table;
                $ruleDao->rule_field           = $field;
                $ruleDao->rule_length          = $length;
                $ruleDao->rule_weight          = $weight;
                $ruleDao->save();
                $ruleDao->free();

                if ( ! array_key_exists( $table, $tables ) ) {
                    $tables[$table] = array( );
                }
                $tables[$table][] = $field;
            }

            // CRM-6245: we must pass table/field/length triples to the createIndexes() call below
            if ($length) {
                if (!isset($substrLenghts[$table])) $substrLenghts[$table] = array();
                $substrLenghts[$table][$field] = $length;
            }
        }

        // also create an index for this dedupe rule
        // CRM-3837
        require_once 'CRM/Core/BAO/SchemaHandler.php';
        CRM_Core_BAO_SchemaHandler::createIndexes( $tables, 'dedupe_index', $substrLenghts );
                
    }
    
}


