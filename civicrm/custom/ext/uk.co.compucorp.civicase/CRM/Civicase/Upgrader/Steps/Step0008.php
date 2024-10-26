<?php

use CRM_Civicase_Setup_CreateSafeFileExtensionOptionValue as CreateSafeFileExtensionOptionValue;

/**
 * Class CRM_Civicase_Upgrader_Steps_Step0008.
 */
class CRM_Civicase_Upgrader_Steps_Step0008 {

  /**
   * Installs new Safe File Extentions.
   *
   * @return bool
   *   Return value in boolean.
   */
  public function apply() {
    $step = new CreateSafeFileExtensionOptionValue();
    $step->apply();

    return TRUE;
  }

}
