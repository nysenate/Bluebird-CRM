<?php

/**
 * Change default status to completed for link case activity.
 */
class CRM_Civicase_Hook_BuildForm_LinkCaseActivityDefaultStatus {

  /**
   * Handles the hook's implementation.
   *
   * @param CRM_Core_Form $form
   *   The current form's instance.
   * @param string $formName
   *   The name for the current form.
   */
  public function run(CRM_Core_Form $form, $formName) {
    if (!$this->shouldRun($formName)) {
      return;
    }

    $this->changeActivityDefaultStatus($form);
  }

  /**
   * Determines if the hook should run.
   *
   * @param string $formName
   *   Form name.
   *
   * @return bool
   *   True when the hook can run.
   */
  private function shouldRun($formName) {
    return $formName === CRM_Case_Form_Activity::class &&
      CRM_Utils_Request::retrieve('atype', 'Integer') === CRM_Core_PseudoConstant::getKey(
        'CRM_Activity_BAO_Activity',
        'activity_type_id',
        'Link Cases'
      );
  }

  /**
   * Change default status to completed for link case activity.
   *
   * @param CRM_Core_Form $form
   *   The current form's instance.
   */
  private function changeActivityDefaultStatus(CRM_Core_Form $form) {
    if ($form->elementExists('status_id')) {
      $completedStatusId = CRM_Core_PseudoConstant::getKey(
        'CRM_Activity_BAO_Activity',
        'status_id',
        'Completed'
      );
      if ($completedStatusId) {
        $form->setDefaults(['status_id' => $completedStatusId]);
      }
    }
  }

}
