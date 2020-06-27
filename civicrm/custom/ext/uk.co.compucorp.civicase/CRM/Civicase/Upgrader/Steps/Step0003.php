<?php

use CRM_Civicase_Setup_CreateCasesOptionValue as CreateCasesOptionValue;

class CRM_Civicase_Upgrader_Steps_Step0003 {

  public function apply() {
    $step = new CreateCasesOptionValue();
    $step->apply();

    return TRUE;
  }

}
