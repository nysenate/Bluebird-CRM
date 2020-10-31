<?php

/**
 * Redirect to case details post process hook.
 */
class CRM_Civicase_Hook_PostProcess_RedirectToCaseDetails {

  /**
   * Redirects the user to the case details after creating the case.
   *
   * @param string $formName
   *   The class name of the submitted form.
   * @param object $form
   *   The submitted form instance.
   */
  public function run($formName, $form) {
    if (!$this->shouldRun($form)) {
      return;
    }

    $caseId = $form->getVar('_caseId');
    $caseDetailsUrl = CRM_Civicase_Helper_CaseUrl::getDetailsPage($caseId);

    CRM_Core_Session::singleton()
      ->pushUserContext($caseDetailsUrl);
  }

  /**
   * Determines if the hook should run.
   *
   * Runs when:
   * - Using the cases form.
   * - Is creating a new case.
   * - The user is not adding more cases after this one.
   *
   * @return bool
   *   True when the hook can be run.
   */
  private function shouldRun($form) {
    $submittedValues = $form->getVar('_submitValues');
    $isCaseForm = get_class($form) === 'CRM_Case_Form_Case';
    $isAddAction = $form->getVar('_action') === CRM_Core_Action::ADD;
    $isNotAddingMoreCases = isset($submittedValues['_qf_Case_upload']);

    return $isCaseForm && $isAddAction && $isNotAddingMoreCases;
  }

}
