<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataType;
use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;

/**
 * Class CRM_Civicase_Uninstall_RemoveCustomGroupSupportForCaseCategory.
 */
class CRM_Civicase_Uninstall_RemoveCustomGroupSupportForCaseCategory {

  /**
   * Deletes the Cases option from the CG Extends option values.
   */
  public function apply() {
    $caseCategoryCustomData = new CaseCategoryCustomDataType();
    $caseCategoryCustomFieldExtends = new CaseCategoryCustomFieldExtends();
    $caseCategoryCustomFieldExtends->delete(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME);
    $caseCategoryCustomData->delete(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME);
  }

}
