<?php

/**
 * Class CRM_Civicase_Page_ContactActivityTab
 *
 * Implement the Angular version of the tab "View Contact => Activities".
 */
class CRM_Civicase_Page_ContactActivityTab extends CRM_Core_Page {

  public function run() {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $this->assign('cid', $cid);
    parent::run();
  }

}
