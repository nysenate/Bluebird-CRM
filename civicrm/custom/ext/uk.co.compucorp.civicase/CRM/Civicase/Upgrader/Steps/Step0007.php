<?php

use CRM_Civicase_Setup_MoveCaseTypesToCasesCategory as MoveCaseTypesToCasesCategory;

/**
 * Class CRM_Civicase_Upgrader_Steps_Step0007.
 */
class CRM_Civicase_Upgrader_Steps_Step0007 {

  /**
   * Moves Case Types with no category to category "Cases".
   *
   * @return bool
   *   Return value in boolean.
   */
  public function apply() {
    $step = new MoveCaseTypesToCasesCategory();
    $step->apply();

    return TRUE;
  }

}
