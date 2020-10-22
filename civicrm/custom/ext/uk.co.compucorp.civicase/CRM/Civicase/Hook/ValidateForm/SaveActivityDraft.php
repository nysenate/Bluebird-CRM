<?php

/**
 * Class CRM_Civicase_Hook_ValidateForm_SaveActivityDraft.
 */
class CRM_Civicase_Hook_ValidateForm_SaveActivityDraft {

  /**
   * Special forms to save draft activity for.
   *
   * @var array
   */
  private $specialForms = [
    'pdf' => 'CRM_Contact_Form_Task_PDF',
    'email' => 'CRM_Contact_Form_Task_Email',
  ];

  /**
   * Implement the save draft save functionality.
   *
   * @param string $formName
   *   Form Name.
   * @param array $fields
   *   Fields List.
   * @param array $files
   *   Files list.
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param array $errors
   *   Errors.
   */
  public function run($formName, array &$fields, array &$files, CRM_Core_Form &$form, array &$errors) {
    if (!$this->shouldRun($form, $formName)) {
      return;
    }

    if (!array_key_exists($form->getButtonName('refresh'), $fields['buttons'])) {
      return;
    }

    $this->saveDraftForSpecialForms($formName, $form, $fields);
  }

  /**
   * Save draft activity for the special forms.
   *
   * @param string $formName
   *   Form Name.
   * @param CRM_Core_Form $form
   *   Form object.
   * @param array $fields
   *   Fields.
   */
  private function saveDraftForSpecialForms($formName, CRM_Core_Form &$form, array $fields) {
    // Save draft feature
    // The validate stage provides an opportunity to bypass normal
    // form processing, save the draft & return early.
    $activityType = $form->getVar('_activityTypeId');
    $caseId = $form->getVar('_caseId');
    if (!$activityType) {
      $activityType = $formName == 'CRM_Contact_Form_Task_PDF' ? 'Print PDF Letter' : 'Email';
    }

    $params = [
      'activity_type_id' => $activityType,
      'status_id' => 'Draft',
      'id' => $form->getVar('_activityId'),
    ];

    if (!$caseId) {
      $params['target_contact_id'] = $form->getVar('_contactIds');
    }
    else {
      $params['case_id'] = $caseId;
      $params['assignee_contact_id'] = CRM_Core_Session::getLoggedInContactID();
    }

    if (in_array($formName, $this->specialForms)) {
      $params['details'] = CRM_Utils_Array::value('html_message', $fields);
    }

    if ($formName == $this->specialForms['email']) {
      $params['target_contact_id'] = explode(',', CRM_Utils_Array::value('to', $fields));
      $params['target_contact_id'] = array_map('intval', $params['target_contact_id']);
    }

    civicrm_api3('Activity', 'create', $params + $fields);

    $url = $this->getRedirectUrl($form, $caseId);
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext($url);
    CRM_Core_Session::setStatus('Activity saved as a draft', ts('Saved'), 'success');

    if (CRM_Utils_Array::value('snippet', $_GET) === 'json') {
      $response = [];
      if (!empty($form->civicase_reload)) {
        $api = civicrm_api3('Case', 'getdetails', ['check_permissions' => 1] + $form->civicase_reload);
        $response['civicase_reload'] = $api['values'];
      }
      CRM_Core_Page_AJAX::returnJsonResponse($response);
    }

    CRM_Utils_System::redirect($url);
  }

  /**
   * Get redirect URL.
   *
   * @param CRM_Core_Form $form
   *   Form object.
   * @param int $caseId
   *   Case Id.
   *
   * @return string
   *   Redirect URL.
   */
  private function getRedirectUrl(CRM_Core_Form $form, $caseId) {
    $referer = CRM_Utils_Array::value('HTTP_REFERER', $_SERVER);

    if (strpos($referer, 'civicrm/case/a') !== FALSE) {
      return CRM_Utils_System::url('civicrm/contact/view/case', "reset=1&action=view&cid={$form->getVar('_contactIds')[0]}&id={$caseId}&show=1&tab=Activities");
    }

    if (strpos($referer, 'civicrm/contact/view') !== FALSE) {
      $cid = $form->getVar('_contactIds')[0];

      return CRM_Utils_System::url('civicrm/contact/view', "&show=1&action=browse&cid={$cid}&selectedChild=activity");
    }
  }

  /**
   * Determines if the hook will run.
   *
   * @param CRM_Core_Form $form
   *   Form object class.
   * @param string $formName
   *   Form name.
   *
   * @return bool
   *   returns TRUE or FALSE.
   */
  private function shouldRun(CRM_Core_Form $form, $formName) {
    return is_a($form, 'CRM_Activity_Form_Activity') || in_array($formName, $this->specialForms);
  }

}
