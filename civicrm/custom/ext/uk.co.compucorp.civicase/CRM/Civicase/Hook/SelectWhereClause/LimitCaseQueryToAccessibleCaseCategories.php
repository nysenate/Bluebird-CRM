<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Case_BAO_CaseType as CaseType;

/**
 * Restrict case query result to accessible case categories.
 */
class CRM_Civicase_Hook_SelectWhereClause_LimitCaseQueryToAccessibleCaseCategories {

  /**
   * Restrict case query to accessible case categories.
   *
   * @param string $entity
   *   Entity name.
   * @param array $clauses
   *   Where clauses.
   */
  public function run($entity, array &$clauses) {
    if (!$this->shouldRun($entity)) {
      return;
    }
    $accessibleCaseCategories = CaseCategoryHelper::getAccessibleCaseTypeCategories();
    if (!$this->shouldAddCaseTypeCategoryCondition($accessibleCaseCategories)) {
      return;
    }
    $accessibleCaseCategoriesIds = implode(',', array_keys($accessibleCaseCategories));
    $clauses['case_type_id'][] = "IN (SELECT id FROM civicrm_case_type WHERE case_type_category in ($accessibleCaseCategoriesIds))";
  }

  /**
   * Should only run when the entity is Case.
   *
   * @param string $entity
   *   Entity name.
   *
   * @return bool
   *   Whether the hook should run or not.
   */
  private function shouldRun($entity) {
    return $entity === 'Case';
  }

  /**
   * Whether to add case type category condition to query or not.
   *
   * @param array $accessibleCaseCategories
   *   Accessible case categories.
   *
   * @return bool
   *   Whether to add case type category.
   */
  private function shouldAddCaseTypeCategoryCondition(array $accessibleCaseCategories) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');

    return !empty($accessibleCaseCategories) &&
      count($accessibleCaseCategories) < count($caseTypeCategories);
  }

}
