<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Class CRM_Civicase_Hook_Post_PopulateCaseCategoryForCaseType.
 */
class CRM_Civicase_Hook_Post_PopulateCaseCategoryForCaseType {

  /**
   * Updates the Case Type category for new cases.
   *
   * Updates the case category for a new case to be of type "Cases".
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

    $this->updateCaseTypeCategory($objectId);
  }

  /**
   * Updates the case type category to "Cases".
   *
   * @param int $caseTypeId
   *   Case Type Id.
   */
  private function updateCaseTypeCategory($caseTypeId) {
    $result = civicrm_api3('CaseType', 'getsingle', [
      'return' => ['case_type_category'],
      'id' => $caseTypeId,
    ]);

    if (!empty($result['case_type_category'])) {
      return;
    }

    civicrm_api3('CaseType', 'create', [
      'id' => $caseTypeId,
      'case_type_category' => CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME,
    ]);
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
    return $op == 'create' && $objectName == 'CaseType';
  }

}
