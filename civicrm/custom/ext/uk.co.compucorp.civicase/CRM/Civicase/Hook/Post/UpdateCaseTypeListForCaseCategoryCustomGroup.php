<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Handles custom group related logic when case type is created/edited.
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
    $caseCategoryInstance = CaseCategoryHelper::getInstanceObject($caseTypeDetails['case_type_category']);
    if (empty($caseCategoryInstance)) {
      return;
    }
    $caseTypePostProcessor = $caseCategoryInstance->getCaseTypePostProcessor();

    if (empty($caseTypePostProcessor)) {
      return;
    }

    if ($op === 'create') {
      $caseTypePostProcessor->processCaseTypeCustomGroupsOnCreate($objectId);
    }

    if ($op === 'edit') {
      $caseTypePostProcessor->processCaseTypeCustomGroupsOnUpdate($objectId);
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
