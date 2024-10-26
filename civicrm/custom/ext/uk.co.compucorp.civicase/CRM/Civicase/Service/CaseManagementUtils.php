<?php

use CRM_Civicase_Service_CaseCategoryMenu as CaseCategoryMenu;
use CRM_Civicase_Service_CaseManagementCustomGroupPostProcessor as CaseManagementCustomGroupPostProcessor;
use CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess as CaseManagementCustomGroupPostProcessHelper;
use CRM_Civicase_Service_CaseManagementCaseTypePostProcessor as CaseManagementCaseTypePostProcessor;
use CRM_Civicase_Service_CaseManagementCustomGroupDisplayFormatter as CaseManagementCustomGroupDisplayFormatter;

/**
 * CaseManagementUtils class for case instance type.
 */
class CRM_Civicase_Service_CaseManagementUtils extends CRM_Civicase_Service_CaseCategoryInstanceUtils {

  /**
   * Returns the menu object for the default category instance.
   *
   * @return \CRM_Civicase_Service_CaseCategoryMenu
   *   Menu object.
   */
  public function getMenuObject() {
    return new CaseCategoryMenu();
  }

  /**
   * {@inheritDoc}
   */
  public function getCaseTypePostProcessor() {
    return new CaseManagementCaseTypePostProcessor(new CaseManagementCustomGroupPostProcessHelper());
  }

  /**
   * {@inheritDoc}
   */
  public function getCustomGroupDisplayFormatter() {
    return new CaseManagementCustomGroupDisplayFormatter(new CaseManagementCustomGroupPostProcessHelper());
  }

  /**
   * {@inheritDoc}
   */
  public function getCustomGroupPostProcessor() {
    return new CaseManagementCustomGroupPostProcessor(new CaseManagementCustomGroupPostProcessHelper());
  }

}
