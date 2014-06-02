<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id: Email.php 26615 2010-03-21 21:05:35Z kurund $
 *
 */

/**
 * This class provides the functionality to email a group of
 * contacts.
 */
class CRM_Activity_Form_Task_UpdateStatus extends CRM_Activity_Form_Task {

  /**
   * Are we operating in "single mode", i.e. sending email to one
   * specific contact?
   *
   * @var boolean
   */
  public $_single = FALSE;

  /**
   * build all the data structures needed to build the form
   *
   * @return void
   * @access public
   */
  function preProcess() {
    parent::preProcess();
  }

  /**
   * Build the form
   *
   * @access public
   *
   * @return void
   */
  public function buildQuickForm() {
    $statusTypes = CRM_Core_PseudoConstant::activityStatus();
    $this->add('select', 'status_type', ts('Select New Status'),
      array(
        '' => ts('- select status -')) + $statusTypes, TRUE
    );
    $this->addDefaultButtons(ts('Update Activity Status'));
  }

  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    //CRM_Core_Error::debug_var('this', $this);

    $params = $this->exportValues();
    $updateCount = 0;
    //CRM_Core_Error::debug_var('$params', $params);

    foreach ($this->_activityHolderIds as $activityID) {
      $update = CRM_Core_DAO::executeQuery("
        UPDATE civicrm_activity
        SET status_id = {$params['status_type']}
        WHERE id = {$activityID}
      ");
      //CRM_Core_Error::debug_var('$update', $update);
      $updateCount ++;
    }

    CRM_Core_Session::setStatus($updateCount, ts('Activities Updated Count'), "success");
  }
}

