<?php

use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataType;
use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;
use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * CRM_Civicase_Setup_ProcessCaseCategoryForCustomGroupSupport class.
 */
class CRM_Civicase_Setup_ProcessCaseCategoryForCustomGroupSupport {

  const CASE_CATEGORY_LABEL = 'Case (Cases)';

  /**
   * Add Cases as a valid Entity that a custom group can extend.
   */
  public function apply() {
    $caseCategoryCustomData = new CaseCategoryCustomDataType();
    $caseCategoryCustomFieldExtends = new CaseCategoryCustomFieldExtends();
    $caseCategoryCustomFieldExtends->create(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME, self::CASE_CATEGORY_LABEL);
    $caseCategoryCustomData->create(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME);
  }

}
