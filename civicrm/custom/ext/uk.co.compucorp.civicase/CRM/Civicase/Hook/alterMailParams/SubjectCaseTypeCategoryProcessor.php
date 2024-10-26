<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Class CRM_Civicase_Hook_alterMailParams_CaseTypeCategorySubjectProcessor.
 */
class CRM_Civicase_Hook_alterMailParams_SubjectCaseTypeCategoryProcessor {

  /**
   * A substring of email subject that should be replaced.
   *
   * @var string
   *   This substring will be replaced with respective case type category name.
   */
  private $toReplace = '[case ';

  /**
   * Email subject processor.
   *
   * Replaces word 'case' with related case type category in email subject.
   *
   * @param array $params
   *   Mail parameters.
   * @param string $context
   *   Mail context.
   */
  public function run(array &$params, $context) {
    $caseId = CRM_Utils_Request::retrieve('caseid', 'Integer');
    if (!$this->shouldRun($params, $context, $caseId)) {
      return;
    }

    // Get case category name for the case.
    $caseTypeCategory = CaseCategoryHelper::getCategoryName($caseId);
    if (empty($caseTypeCategory)) {
      return;
    }

    // Get replacement words and replace the word 'case' in subject.
    $wordReplacements = CaseCategoryHelper::getWordReplacements($caseTypeCategory);
    if (!empty($wordReplacements['case'])) {
      // Make sure we make just 1 replacement.
      $subject = explode($this->toReplace, $params['subject'], 2);
      $params['subject'] = '[' . $wordReplacements['case'] . ' ' . $subject[1];
    }
  }

  /**
   * Determines if the hook will run.
   *
   * @param array $params
   *   Mail parameters.
   * @param string $context
   *   Mail context.
   * @param int $caseId
   *   Case id.
   *
   * @return bool
   *   returns TRUE if hook should run, FALSE otherwise.
   */
  private function shouldRun(array $params, $context, $caseId) {
    // If case id is set and email subject starts with '[case '.
    return $caseId && strpos($params['subject'], $this->toReplace) === 0;
  }

}
