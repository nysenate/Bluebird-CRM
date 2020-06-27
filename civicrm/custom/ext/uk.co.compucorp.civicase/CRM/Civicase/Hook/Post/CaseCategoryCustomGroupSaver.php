<?php

/**
 * Class CRM_Civicase_Hook_Post_CaseCategoryCustomGroupSaver.
 */
class CRM_Civicase_Hook_Post_CaseCategoryCustomGroupSaver {

  /**
   * Case Category Custom Group Saver.
   *
   * @param string $op
   *   The operation being performed.
   * @param string $objectName
   *   Object name.
   * @param mixed $objectId
   *   Object ID.
   * @param object $objectRef
   *   Object reference.
   */
  public function run($op, $objectName, $objectId, &$objectRef) {
    if (!$this->shouldRun($op, $objectName)) {
      return;
    }

    $this->saveCustomGroupForCaseCategory($objectRef);
  }

  /**
   * Saves case type category custom groups.
   *
   * This function allows saving the Custom groups that extends a Case category
   * entity to extend the Case entity and to save the ID of the case category
   * in the `extends_entity_column_id` of the `custom_group` table and
   * also to store all case types for the Case category in the
   * `extends_entity_column_value` column.
   *
   * @param object $objectRef
   *   Object reference.
   */
  private function saveCustomGroupForCaseCategory(&$objectRef) {
    $caseTypeCategories = array_flip(CRM_Case_BAO_CaseType::buildOptions('case_type_category', 'validate'));
    if (empty($caseTypeCategories[$objectRef->extends])) {
      return;
    }

    $caseTypeIds = $this->getCaseTypeIdsForCaseCategory($caseTypeCategories[$objectRef->extends]);
    $ids = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $caseTypeIds) . CRM_Core_DAO::VALUE_SEPARATOR;
    $objectRef->extends_entity_column_id = $caseTypeCategories[$objectRef->extends];
    if (!empty($ids)) {
      $objectRef->extends_entity_column_value = $ids;
    }
    $objectRef->extends = 'Case';
    $objectRef->save();
  }

  /**
   * Returns case type ID's for a case category.
   *
   * @param int $caseTypeCategoryId
   *   Case Type Category ID.
   *
   * @return array
   *   Case Type Ids.
   */
  private function getCaseTypeIdsForCaseCategory($caseTypeCategoryId) {
    $result = civicrm_api3('CaseType', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'case_type_category' => $caseTypeCategoryId,
    ]);

    return array_column($result['values'], 'id');
  }

  /**
   * Determines if the hook should run or not.
   *
   * @param string $op
   *   The operation being performed.
   * @param string $objectName
   *   Object name.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($op, $objectName) {
    return $objectName == 'CustomGroup' && in_array($op, ['create', 'edit']);
  }

}
