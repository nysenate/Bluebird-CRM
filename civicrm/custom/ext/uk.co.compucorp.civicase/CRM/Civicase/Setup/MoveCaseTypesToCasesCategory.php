<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Case_BAO_CaseType as CaseType;

/**
 * Class CRM_Civicase_Setup_MoveCaseTypesToCasesCategory.
 */
class CRM_Civicase_Setup_MoveCaseTypesToCasesCategory {

  /**
   * Moves Case Types with no category to category "Cases".
   */
  public function apply() {
    $this->moveCaseTypesWithNoCategoryToCasesCategory();
  }

  /**
   * Moves Case Types with no category to category "Cases".
   */
  private function moveCaseTypesWithNoCategoryToCasesCategory() {
    $caseTypeTable = CaseType::getTableName();
    $caseTypes = CRM_Core_DAO::executeQuery("SELECT id FROM {$caseTypeTable} WHERE case_type_category IS NULL");
    $caseTypeIds = [];

    while ($caseTypes->fetch()) {
      $caseTypeIds[] = $caseTypes->id;
    }

    if (empty($caseTypeIds)) {
      return;
    }
    $this->updateCaseTypeCategory($caseTypeIds);
  }

  /**
   * Updates the case type category to "Cases".
   *
   * @param array $caseTypeId
   *   Case Type Id.
   */
  private function updateCaseTypeCategory(array $caseTypeId) {
    $caseTypeTable = CaseType::getTableName();
    $caseCategoryOptionValue = $this->getCaseCategoryOptionValue();

    CRM_Core_DAO::executeQuery(
      "UPDATE {$caseTypeTable} SET case_type_category = %1 WHERE id IN (" . implode(',', $caseTypeId) . ")",
      [1 => [$caseCategoryOptionValue, 'Integer']]
    );
  }

  /**
   * Returns the Case category option value.
   *
   * @return int|null
   *   Case category value.
   */
  private function getCaseCategoryOptionValue() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'case_type_categories',
      'name' => CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME,
      'return' => ['value'],
    ]);

    if ($result['count'] == 0) {
      return;
    }

    return $result['values'][0]['value'];
  }

}
