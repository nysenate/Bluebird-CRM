<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Handles case category custom group post processing.
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

    $caseTypeCategories = CRM_Civicase_Helper_CaseCategory::getCaseCategories();
    $caseTypeCategories = array_column($caseTypeCategories, 'value', 'name');

    if (empty($caseTypeCategories[$objectRef->extends])) {
      return;
    }

    $caseCategoryValue = $caseTypeCategories[$objectRef->extends];
    $caseCategoryInstance = CaseCategoryHelper::getInstanceObject($caseCategoryValue);
    $customGroupPostProcessor = $caseCategoryInstance->getCustomGroupPostProcessor();
    $customGroupPostProcessor->saveCustomGroupForCaseCategory($objectRef);
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
