<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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

require_once 'CRM/Contact/Form/Task.php';

/**
 * This class provides the functionality to alter a privacy
 * options for selected contacts
 */
class CRM_Contact_Form_Task_AlterDoNot extends CRM_Contact_Form_Task {

  /**
   * Build the form
   *
   * @access public
   *
   * @return void
   */ function buildQuickForm() {
    // add select for preferences

    $options = array(ts('Add Selected Options'), ts('Remove selected options'));
    
    $this->addRadio('actionTypeOption', ts('actionTypeOption'), $options);
    
    $privacyOptions = CRM_Core_SelectValues::privacy();
    
    foreach ($privacyOptions as $prefID => $prefName) {
      $this->_prefElement = &$this->addElement('checkbox', "pref[$prefID]", NULL, $prefName);
    }

    $this->addDefaultButtons(ts('Set Privacy Options'));
  }

  function addRules() {
    $this->addFormRule(array('CRM_Contact_Form_Task_AlterDoNot', 'formRule'));
  }
  
  /**
   * Set the default form values
   *
   * @access protected
   *
   * @return array the default array reference
   */
  function &setDefaultValues() {
    $defaults = array();

    $defaults['actionTypeOption'] = 0;
    return $defaults;
  }

  static
  function formRule($form, $rule) {
    $errors = array();
    if (empty($form['pref']) && empty($form['contact_taglist'])) {
      $errors['_qf_default'] = ts("Please select at least one privacy option.");
    }
    return $errors;
  }

  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    //get the submitted values in an array
    $params = $this->controller->exportValues($this->_name);
    $privacyValues = $privacy_labels = array();

    //set default action to "add"
    $privacyValueNew = true;
    $actionTypeMsg = 'Added';
    
    $actionTypeOption = CRM_Utils_Array::value('actionTypeOption', $params, NULL);
    if ($actionTypeOption) {
        //if remove option has been selected change new privacy value to "false"
        $privacyValueNew = false;
        $actionTypeMsg = 'Removed';
    }
    
    // check if any privay option has been checked
    if (CRM_Utils_Array::value('pref', $params)) {
        $privacyValues = $params['pref'];
    
        foreach($this->_contactIds as $contact_id) {
            $contact = new CRM_Contact_BAO_Contact();
            $contact->id = $contact_id;
            
            foreach($privacyValues as $privacy_key => $privacy_value) {
                $contact->$privacy_key = $privacyValueNew;
            }
            $contact->save();
        }
    
    }
    
    // prepare status messages
    $privacyOptions = CRM_Core_SelectValues::privacy();
    foreach($privacyValues as $privacy_key => $privacy_value) {
        $privacy_labels[] = $privacyOptions[$privacy_key];
    }
    
    
    $status = array($actionTypeMsg. ' privacy options: ' . implode(', ', $privacy_labels));
    $status[] = 'Total Contact(s) modified: ' . count($this->_contactIds);

 
    CRM_Core_Session::setStatus($status);
  }
  //end of function
}

 
