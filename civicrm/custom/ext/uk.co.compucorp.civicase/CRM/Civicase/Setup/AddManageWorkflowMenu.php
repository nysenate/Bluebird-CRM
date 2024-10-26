<?php

use CRM_Civicase_Service_CaseCategoryMenu as CaseCategoryMenu;

/**
 * Adds the Manage Workflow Menu item for existing Case types.
 */
class CRM_Civicase_Setup_AddManageWorkflowMenu {

  /**
   * Updates the Manage Cases Menu URLs.
   */
  public function apply() {
    $caseCategoryMenuObj = new CaseCategoryMenu();

    $caseCategoryMenuObj->createManageWorkflowMenu('case_management', FALSE);
  }

}
