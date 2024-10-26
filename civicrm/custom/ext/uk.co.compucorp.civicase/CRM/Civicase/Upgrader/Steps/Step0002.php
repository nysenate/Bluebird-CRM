<?php

use CRM_Civicase_Setup_CaseTypeCategorySupport as CaseTypeCategorySupport;

class CRM_Civicase_Upgrader_Steps_Step0002 {

  public function apply() {
    $step = new CaseTypeCategorySupport();
    $step->apply();

    return TRUE;
  }

}
