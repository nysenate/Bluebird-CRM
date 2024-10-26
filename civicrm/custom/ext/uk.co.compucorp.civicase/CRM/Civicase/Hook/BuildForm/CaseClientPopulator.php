<?php

/**
 * CRM_Civicase_Hook_BuildForm_CaseClientPopulator class.
 */
class CRM_Civicase_Hook_BuildForm_CaseClientPopulator {

  /**
   * Runs the Case Client populator hook for the Case Form.
   *
   * When a client id is provided as a request parameter,
   *
   * It adds this value to the client id field of the form.
   *
   * @param CRM_Core_Form $form
   *   Form object class.
   * @param string $formName
   *   Form name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    $clientId = CRM_Utils_Request::retrieve('civicase_cid', 'Positive');

    if (!$this->shouldRun($form, $clientId)) {
      return;
    }

    $form->setDefaults(['client_id' => $clientId]);
  }

  /**
   * Determines if the hook will run.
   *
   * This hook is only valid for the Case form.
   *
   * The civicase client id parameter must be defined.
   *
   * @param CRM_Core_Form $form
   *   Form class.
   * @param INT|null $clientId
   *   Case Client ID.
   */
  public function shouldRun(CRM_Core_Form $form, $clientId) {
    $isCaseForm = CRM_Case_Form_Case::class === get_class($form);

    return $isCaseForm && $clientId;
  }

}
