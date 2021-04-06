<?php

/**
 * Handle Draft Activity.
 */
class CRM_Civicase_Hook_PostProcess_HandleDraftActivity {

  /**
   * Add bulk email as an activity to all the selected cases.
   *
   * @param string $formName
   *   The class name of the submitted form.
   * @param object $form
   *   The submitted form instance.
   */
  public function run($formName, $form) {
    $urlParams = parse_url(
      htmlspecialchars_decode($form->controller->_entryURL), PHP_URL_QUERY
    );
    parse_str($urlParams, $urlParams);

    if (!$this->shouldRun($formName, $urlParams)) {
      return;
    }

    $this->markPdfActivityAsComplete($form, $urlParams['draft_id']);
    $this->deleteEmailDraftActivity($form, $urlParams['draft_id']);
  }

  /**
   * Delete Email Draft Activity.
   *
   * @param object $form
   *   Form object.
   * @param string $draftActivityID
   *   Draft Activity ID.
   */
  private function deleteEmailDraftActivity($form, $draftActivityID) {
    $ifSendEmailButtonIsClicked = array_key_exists(
      '_qf_Email_upload',
      $form->getVar('_submitValues')['buttons']
    );

    if ($ifSendEmailButtonIsClicked) {
      civicrm_api3('Activity', 'delete', [
        'id' => $draftActivityID,
      ]);
    }
  }

  /**
   * Mark Pdf Activity As Complete.
   *
   * @param object $form
   *   Form object.
   * @param string $draftActivityID
   *   Draft Activity ID.
   */
  private function markPdfActivityAsComplete($form, $draftActivityID) {
    $ifDownloadDocumentButtonClicked = array_key_exists(
      '_qf_PDF_upload',
      $form->getVar('_submitValues')['buttons']
    );

    if ($ifDownloadDocumentButtonClicked) {
      civicrm_api3('Activity', 'create', [
        'id' => $draftActivityID,
        'status_id' => 'Completed',
      ]);
    }
  }

  /**
   * Check whether the form is for PDF of Email activity.
   *
   * @param string $formName
   *   The name for the current form.
   * @param object $urlParams
   *   URL parameters.
   *
   * @return bool
   *   Whether the hook should run or not.
   */
  private function shouldRun($formName, $urlParams) {
    $specialForms = ['CRM_Contact_Form_Task_PDF', 'CRM_Contact_Form_Task_Email'];

    return in_array($formName, $specialForms) && !empty($urlParams['draft_id']);
  }

}
