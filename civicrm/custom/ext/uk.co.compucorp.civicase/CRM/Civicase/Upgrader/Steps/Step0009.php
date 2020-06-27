<?php

use CRM_Civicase_Setup_UpdateMenuLinks as UpdateMenuLinks;

/**
 * Updates the Manage Cases Menu URLs.
 */
class CRM_Civicase_Upgrader_Steps_Step0009 {

  /**
   * Updates the Manage Cases Menu URLs.
   */
  public function apply() {
    $step = new UpdateMenuLinks();
    $step->apply();

    return TRUE;
  }

}
