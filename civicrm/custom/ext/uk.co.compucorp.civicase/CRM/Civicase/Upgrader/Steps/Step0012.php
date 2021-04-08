<?php

use CRM_Civicase_Setup_CaseCategoryInstanceSupport as CaseCategoryInstanceSupport;

/**
 * Creates the activity types for case role date changes.
 */
class CRM_Civicase_Upgrader_Steps_Step0012 {

  /**
   * Add the Case category Instance support.
   *
   * @return bool
   *   Return value in boolean.
   */
  public function apply() {
    $upgrader = CRM_Civicase_Upgrader_Base::instance();
    $upgrader->executeSqlFile('sql/auto_install.sql');

    $step = new CaseCategoryInstanceSupport();
    $step->apply();

    return TRUE;
  }

}
