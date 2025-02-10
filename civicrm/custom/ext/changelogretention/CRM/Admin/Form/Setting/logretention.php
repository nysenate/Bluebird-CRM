<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
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
 * @copyright CiviCRM LLC (c) 2004-2018
 */

require_once 'CRM/Admin/Form/Setting.php';

/**
 * This class generates form components for Relationship Type.
 */
class CRM_Admin_Form_Setting_logretention extends CRM_Admin_Form_Setting {
  
  /**
   * Set default values for the form.
   *
   * Default values are retrieved from the database.
   */
  public function setDefaultValues() {
    if (!$this->_defaults) {
      $this->_defaults = array();
      $this->_defaults['retention_period'] = Civi::settings()->get('retention_period');
      $this->_defaults['tables_excluded'] = Civi::settings()->get('tables_excluded');
    }
    return $this->_defaults;
  }
  

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Log Retention Settings'));
    $this->add('text', 'retention_period', ts('Log retention periods in month'), '', TRUE);

    $schema = new \CRM_Logging_Schema();
    $tables = $schema->getLogTableNames();
    $tableNames = array();
    foreach($tables as $table_name){
      $tableNames[$table_name] = $table_name;
    }
    $this->addElement('advmultiselect', 'tables_excluded', ts('Select logging tables to exclude from purging process'), $tableNames, array('class' => 'crm-select', 'size' => 10, 'style' => 'width:300px'));
   
    $this->addFormRule(array('CRM_Admin_Form_Setting_logretention', 'formRule'), $this);
    
    parent::buildQuickForm();
  }

  /**
   * Global form rule.
   *
   * @param array $fields
   *   The input form values.
   * @param array $files
   *   The uploaded files if any.
   * @param array $options
   *   Additional user data.
   *
   * @return bool|array
   *   true if no errors, else array of errors
   */
  public static function formRule($fields, $files, $options) {
    $errors = array();
    if (array_key_exists('retention_period', $fields) && !is_numeric($fields['retention_period'])) {
      $errors['retention_period'] = ts('Invalid field value. Only numeric value is allowed.');
    }
    return $errors;
  }
  
  /**
   * postProcess the form object.
   */
  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    
    Civi::settings()->set('retention_period', $params['retention_period']);
    Civi::settings()->set('tables_excluded', $params['tables_excluded']);
    CRM_Core_Config::clearDBCache();
    CRM_Utils_System::flushCache();
    CRM_Core_Resources::singleton()->resetCacheCode();
    CRM_Core_Session::setStatus(" ", ts('Changes Saved'), "success");
  }
}
