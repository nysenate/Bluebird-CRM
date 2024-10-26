<?php

/**
 * Class CRM_Civicase_Page_ContactActivityTab.
 *
 * Implements the Angular version of the tab "View Contact => Activities".
 */
class CRM_Civicase_Page_ContactActivityTab extends CRM_Core_Page {

  /**
   * Run function of the class.
   */
  public function run() {
    $this->preProcess();

    return parent::run();
  }

  /**
   * Pre process function.
   */
  public function preProcess() {
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);

    // Check logged in url permission.
    CRM_Contact_Page_View::checkUserPermission($this);

    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'browse');
    $this->assign('action', $this->_action);

    // Also create the form element for the activity links box.
    $controller = new CRM_Core_Controller_Simple(
      'CRM_Activity_Form_ActivityLinks',
      ts('Activity Links'),
      NULL,
      FALSE, FALSE, TRUE
    );
    $controller->setEmbedded(TRUE);
    $controller->run();
  }

}
