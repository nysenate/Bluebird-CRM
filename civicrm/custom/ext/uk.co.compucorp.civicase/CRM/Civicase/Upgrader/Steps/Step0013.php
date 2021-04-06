<?php

use CRM_Civicase_Setup_AddChangeCaseRoleDateActivityTypes as AddChangeCaseRoleDateActivityTypes;

/**
 * Creates the activity types for case role date changes.
 */
class CRM_Civicase_Upgrader_Steps_Step0013 {

  /**
   * Runs the upgrader changes.
   *
   * @return bool
   *   Return value in boolean.
   */
  public function apply() {
    $step = new AddChangeCaseRoleDateActivityTypes();
    $step->apply();

    return TRUE;
  }

}
