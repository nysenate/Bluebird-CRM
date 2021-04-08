<?php

use CRM_Civicase_Setup_AddManageWorkflowMenu as AddManageWorkflowMenu;
use CRM_Civicase_Service_CaseCategoryInstance as CaseCategoryInstance;

/**
 * Assigns instance to case type categories without an instance.
 *
 * Also creates Manage Workflow menu for existing 'case management' type
 * categories.
 */
class CRM_Civicase_Upgrader_Steps_Step0014 {

  /**
   * Runs the upgrader changes.
   *
   * @return bool
   *   Return value in boolean.
   */
  public function apply() {
    $instanceObj = new CaseCategoryInstance();
    $instanceObj->assignInstanceForExistingCaseCategories();

    $step = new AddManageWorkflowMenu();
    $step->apply();

    return TRUE;
  }

}
