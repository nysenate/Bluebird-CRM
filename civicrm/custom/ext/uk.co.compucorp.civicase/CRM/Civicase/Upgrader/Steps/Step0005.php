<?php

use CRM_Civicase_Setup_AddCaseCategoryWordReplacementOptionGroup as AddCaseCategoryWordReplacementOptionGroup;

/**
 * CRM_Civicase_Upgrader_Steps_Step005 class.
 */
class CRM_Civicase_Upgrader_Steps_Step0005 {

  /**
   * Add the case type category word replacement option group.
   *
   * @return bool
   *   Return value in boolean.
   */
  public function apply() {
    $step = new AddCaseCategoryWordReplacementOptionGroup();
    $step->apply();

    return TRUE;
  }

}
