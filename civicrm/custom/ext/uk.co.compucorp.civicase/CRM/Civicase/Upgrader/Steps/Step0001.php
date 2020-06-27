<?php

class CRM_Civicase_Upgrader_Steps_Step0001 {
  public function apply() {
    $upgrader = CRM_Civicase_Upgrader_Base::instance();
    $upgrader->executeSqlFile('sql/auto_install.sql');

    return TRUE;
  }
}
