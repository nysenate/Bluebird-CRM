<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class generates form components for batch entry
 *
 */
class CRM_Batch_Form_Batch extends CRM_Admin_Form {

  public function preProcess() {
    parent::preProcess();
    // set the usercontext
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/batch', "reset=1"));
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    if ($this->_action & CRM_Core_Action::DELETE) {
      return;
    }

    $this->applyFilter('__ALL__', 'trim');
    $attributes = CRM_Core_DAO::getAttribute('CRM_Core_DAO_Batch');
    $this->add('text', 'title', ts('Batch Name'), $attributes['name'], TRUE);

    $type = $this->add('select', 'type_id', ts('Type'), CRM_Core_PseudoConstant::getBatchType());

    if ($this->_action & CRM_Core_Action::UPDATE) {
      $type->freeze();
    }

    $this->add('textarea', 'description', ts('Description'), $attributes['description']);
    $this->add('text', 'item_count', ts('Number of items'), $attributes['item_count'], TRUE);
    $this->add('text', 'total', ts('Total Amount'), $attributes['total'], TRUE);
    $this->add('select', 'status_id', ts('Status'), CRM_Core_PseudoConstant::getBatchStatus());
  }

  /**
   * This function sets the default values for the form.
   *
   * @access public
   *
   * @return None
   */
  function setDefaultValues() {
    $defaults = array();

    if ($this->_action & CRM_Core_Action::ADD) {
      // set batch name default
      $defaults['title'] = CRM_Core_BAO_Batch::generateBatchName();
    }
    else {
      $defaults = $this->_values;
    }


    return $defaults;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    if ($this->_action & CRM_Core_Action::DELETE) {
      CRM_Core_Session::setStatus("Batch has been deleted successfully.");
      CRM_Core_BAO_Batch::deleteBatch($this->_id);
      return;
    }

    if ($this->_id) {
      $params['id'] = $this->_id;
    }
    else {
      $session = CRM_Core_Session::singleton();
      $params['created_id'] = $session->get('userID');
      $params['created_date'] = CRM_Utils_Date::processDate(date("Y-m-d his"));
    }

    $batch = CRM_Core_BAO_Batch::create($params);

    // redirect to batch entry page.
    $session = CRM_Core_Session::singleton();
    if ( $this->_action & CRM_Core_Action::ADD ) {
      $session->replaceUserContext(CRM_Utils_System::url('civicrm/batch/entry', "id={$batch->id}&reset=1&action=add"));
    }
    else {
      $session->replaceUserContext(CRM_Utils_System::url('civicrm/batch/entry', "id={$batch->id}&reset=1"));
    }
  }
  //end of function
}

