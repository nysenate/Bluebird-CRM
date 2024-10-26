<?php

/**
 * Default instance custom group post process helper class.
 */
class CRM_Civicase_Helper_InstanceCustomGroupPostProcess {

  /**
   * Returns case type ID's for a case category.
   *
   * @param int $caseTypeCategoryId
   *   Case Type Category ID.
   *
   * @return array
   *   Case Type Ids.
   */
  public function getCaseTypeIdsForCaseCategory($caseTypeCategoryId) {
    $result = civicrm_api3('CaseType', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'case_type_category' => $caseTypeCategoryId,
    ]);

    return array_column($result['values'], 'id');
  }

  /**
   * Fetches the case type category for the case type.
   *
   * @param int $caseTypeId
   *   Case Type ID.
   *
   * @return array
   *   Case category details.
   */
  public function getCaseCategoryForCaseType($caseTypeId) {
    $result = civicrm_api3('CaseType', 'getsingle', [
      'id' => $caseTypeId,
    ]);

    return $result;
  }

  /**
   * Gets the custom groups that the case type is associated with.
   *
   * This returns the custom groups extending the `Case` entity and
   * where the entity column value has teh case type ID or where
   * the entity column value is NULL.
   *
   * @param int $caseTypeId
   *   Case type Id.
   *
   * @return array
   *   Matched Case type custom groups.
   */
  public function getCaseTypeCustomGroups($caseTypeId) {
    $caseCategory = $this->getCaseCategoryForCaseType($caseTypeId);
    if (empty($caseCategory['case_type_category'])) {
      return [];
    }
    $caseCategoryId = $caseCategory['case_type_category'];
    $customGroupsWithoutCurrentCaseType = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Case',
      'extends_entity_column_id' => $caseCategoryId,
      'extends_entity_column_value' => ['NOT LIKE' => '%' . CRM_Core_DAO::VALUE_SEPARATOR . $caseTypeId . CRM_Core_DAO::VALUE_SEPARATOR . '%'],
    ]);

    // API above will not fetch results for when
    // extends_entity_column_value is NULL.
    $customGroupsWithNullCaseType = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Case',
      'extends_entity_column_id' => $caseCategoryId,
      'extends_entity_column_value' => ['IS NULL' => 1],
    ]);

    return array_merge($customGroupsWithoutCurrentCaseType['values'], $customGroupsWithNullCaseType['values']);
  }

  /**
   * Gets the custom groups that the case type is associated with.
   *
   * This is diffrent from the `getCaseTypeCustomGroups` function in
   * that it returns custom groups associated with the case type but
   * the case type category is not the same as the case category
   * the case type belongs to.
   *
   * @param int $caseTypeId
   *   Case type Id.
   *
   * @return array
   *   Mismatched case type custom groups
   */
  public function getCaseTypeCustomGroupsWithCategoryMismatch($caseTypeId) {
    $caseCategory = $this->getCaseCategoryForCaseType($caseTypeId);
    if (empty($caseCategory['case_type_category'])) {
      return [];
    }
    $caseCategoryId = $caseCategory['case_type_category'];

    $result = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Case',
      'extends_entity_column_id' => ['NOT IN' => [$caseCategoryId]],
      'extends_entity_column_value' => ['LIKE' => '%' . CRM_Core_DAO::VALUE_SEPARATOR . $caseTypeId . CRM_Core_DAO::VALUE_SEPARATOR . '%'],
    ]);

    return $result['values'];
  }

  /**
   * Returns values from cg_extend_object option group.
   *
   * @return array
   *   CG extends values.
   */
  public function getCgExtendValues() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => "cg_extend_objects",
    ]);

    return array_column($result['values'], 'label', 'value');
  }

  /**
   * Returns the available case type categories.
   *
   * @return array
   *   Case type categories.
   */
  public function getCaseTypeCategories() {
    return CRM_Case_BAO_CaseType::buildOptions('case_type_category', 'validate');
  }

}
