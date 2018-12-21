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

/**
 * This class provides the functionality to save a search
 * Saved Searches are used for saving frequently used queries
 */
class CRM_Contact_Form_Task_Print extends CRM_Contact_Form_Task {

  /**
   * Build all the data structures needed to build the form.
   */
  public function preProcess() {
    parent::preprocess();

    // set print view, so that print templates are called
    $this->controller->setPrint(1);
    $this->assign('id', $this->get('id'));
    $this->assign('pageTitle', ts('CiviCRM Contact Listing'));

    $params = $this->get('queryParams');
    if (!empty($this->_contactIds)) {
      //using _contactIds field for creating params for query so that multiple selections on multiple pages
      //can be printed.
      foreach ($this->_contactIds as $contactId) {
        $params[] = array(
          CRM_Core_Form::CB_PREFIX . $contactId,
          '=',
          1,
          0,
          0,
        );
      }
    }

    // create the selector, controller and run - store results in session
    $fv = $this->get('formValues');
    $returnProperties = $this->get('returnProperties');

    $sortID = NULL;
    if ($this->get(CRM_Utils_Sort::SORT_ID)) {
      $sortID = CRM_Utils_Sort::sortIDValue($this->get(CRM_Utils_Sort::SORT_ID),
        $this->get(CRM_Utils_Sort::SORT_DIRECTION)
      );
    }

    $includeContactIds = FALSE;
    if ($fv['radio_ts'] == 'ts_sel') {
      $includeContactIds = TRUE;
    }

    $selectorName = $this->controller->selectorName();
    require_once str_replace('_', DIRECTORY_SEPARATOR, $selectorName) . '.php';

    //NYSS dev/core#70
    $returnP = isset($returnProperties) ? $returnProperties : "";
    $customSearchClass = $this->get('customSearchClass');
    $this->assign('customSearchID', $this->get('customSearchID'));
    $selector = new $selectorName($customSearchClass,
      $fv,
      $params,
      $returnP,
      $this->_action,
      $includeContactIds
    );
    $controller = new CRM_Core_Selector_Controller($selector,
      NULL,
      $sortID,
      CRM_Core_Action::VIEW,
      $this,
      CRM_Core_Selector_Controller::SCREEN
    );
    $controller->setEmbedded(TRUE);
    $controller->run();
  }

  /**
   * Build the form object - it consists of
   *    - displaying the QILL (query in local language)
   *    - displaying elements for saving the search
   */
  public function buildQuickForm() {
    //
    // just need to add a javacript to popup the window for printing
    //
    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Print Contact List'),
          'js' => array('onclick' => 'window.print()'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'back',
          'name' => ts('Done'),
        ),
      )
    );
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    // redirect to the main search page after printing is over
  }

}