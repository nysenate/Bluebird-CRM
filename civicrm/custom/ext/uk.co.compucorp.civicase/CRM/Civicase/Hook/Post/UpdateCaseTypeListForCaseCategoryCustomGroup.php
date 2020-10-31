<?php

/**
 * Class CCRM_Civicase_Hook_Post_UpdateCaseTypeListForCaseCategoryCustomGroup.
 */
class CRM_Civicase_Hook_Post_UpdateCaseTypeListForCaseCategoryCustomGroup {

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

    $caseTypeDetails = $this->getCaseTypeDetails($objectId);

    if ($op === 'create') {
      $this->addCaseTypeToCaseCategoryCustomGroupList($caseTypeDetails['case_type_category'], $objectId);
    }

    if ($op === 'edit') {
      $this->removeCaseTypeFromNonRelatedCaseCategoryCustomGroupList($caseTypeDetails['case_type_category'], $objectId);
      $this->addCaseTypeToCaseCategoryCustomGroupList($caseTypeDetails['case_type_category'], $objectId);
    }
  }

  /**
   * Gets the case type details.
   *
   * We need to use this function because core does not pass
   * the Case Type object in the ObjectRef.
   *
   * @param int $caseTypeId
   *   Case type ID.
   */
  private function getCaseTypeDetails($caseTypeId) {
    $result = civicrm_api3('CaseType', 'getsingle', [
      'id' => $caseTypeId,
    ]);

    return $result;
  }

  /**
   * Adds a case type to the the Case Category CustomGroup list.
   *
   * @param int $caseCategoryId
   *   Case category Id.
   * @param int $caseTypeId
   *   Case type Id.
   */
  private function addCaseTypeToCaseCategoryCustomGroupList($caseCategoryId, $caseTypeId) {
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

    $affectedCustomGroups = array_merge($customGroupsWithoutCurrentCaseType['values'], $customGroupsWithNullCaseType['values']);
    if (count($affectedCustomGroups) == 0) {
      return;
    }

    foreach ($affectedCustomGroups as $cusGroup) {
      $extendColValue = !empty($cusGroup['extends_entity_column_value']) ? $cusGroup['extends_entity_column_value'] : [];
      $entityColumnValues = array_merge($extendColValue, [$caseTypeId]);
      $this->updateCustomGroup($cusGroup['id'], $entityColumnValues);
    }
  }

  /**
   * Removes a case type from list for non related Case Category CustomGroup.
   *
   * @param int $caseCategoryId
   *   Case category Id.
   * @param int $caseTypeId
   *   Case type Id.
   */
  private function removeCaseTypeFromNonRelatedCaseCategoryCustomGroupList($caseCategoryId, $caseTypeId) {
    $result = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Case',
      'extends_entity_column_id' => ['NOT IN' => [$caseCategoryId]],
      'extends_entity_column_value' => ['LIKE' => '%' . CRM_Core_DAO::VALUE_SEPARATOR . $caseTypeId . CRM_Core_DAO::VALUE_SEPARATOR . '%'],
    ]);

    if ($result['count'] == 0) {
      return;
    }

    foreach ($result['values'] as $cusGroup) {
      $entityColumnValues = array_diff($cusGroup['extends_entity_column_value'], [$caseTypeId]);
      $entityColumnValues = $entityColumnValues ? $entityColumnValues : NULL;
      $this->updateCustomGroup($cusGroup['id'], $entityColumnValues);
    }
  }

  /**
   * Updates a custom group.
   *
   * We are using the custom group object here rather than the API because if
   * this is updated via the API the `extends_entity_column_id` field will be
   * set to NULL and this is needed to keep track of custom groups extending
   * case categories.
   *
   * @param int $id
   *   Custom group Id.
   * @param array|null $entityColumnValues
   *   Entity custom values for custom group.
   */
  private function updateCustomGroup($id, $entityColumnValues) {
    $cusGroup = new CRM_Core_BAO_CustomGroup();
    $cusGroup->id = $id;
    $entityColValue = is_null($entityColumnValues) ? 'null' : CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $entityColumnValues) . CRM_Core_DAO::VALUE_SEPARATOR;
    $cusGroup->extends_entity_column_value = $entityColValue;
    $cusGroup->save();
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
    return $objectName == 'CaseType' && in_array($op, ['create', 'edit']);
  }

}
