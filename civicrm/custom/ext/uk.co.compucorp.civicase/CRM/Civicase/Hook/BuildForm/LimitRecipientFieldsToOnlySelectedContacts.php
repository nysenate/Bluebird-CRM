<?php

/**
 * Limits recipient fields to selected contacts only for an email popup.
 */
class CRM_Civicase_Hook_BuildForm_LimitRecipientFieldsToOnlySelectedContacts {

  /**
   * Limits recipient fields to selected contacts only for an email popup.
   *
   * @param CRM_Core_Form $form
   *   The current form's instance.
   * @param string $formName
   *   The name for the current form.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($formName)) {
      return;
    }
    $this->limitRecipientFields($form);
  }

  /**
   * Limits recipient fields to selected contacts only for an email popup.
   *
   * @param CRM_Core_Form $form
   *   The current form's instance.
   */
  public function limitRecipientFields(CRM_Core_Form &$form) {
    $contacts = array_map(function ($contact) {
      return [
        'email' => $contact['email'],
        'value' => $contact['contact_id'] . '::' . $contact['email'],
        'display_name' => $contact['display_name'],
        'contact_id' => $contact['contact_id'],
      ];
    }, $form->_contactDetails);

    CRM_Core_Resources::singleton()
      ->addScriptFile('uk.co.compucorp.civicase', 'js/restrict-email-contacts.js')
      ->addSetting([
        'civicase-base' => [
          'recipients' => json_encode(array_values($contacts)),
        ],
      ]);
  }

  /**
   * Check whether the hook should run or not.
   *
   * @param string $formName
   *   The name for the current form.
   *
   * @return bool
   *   Whether the hook should run or not.
   */
  private function shouldRun($formName) {
    return (
      $formName === CRM_Contact_Form_Task_Email::class &&
      CRM_Utils_Array::value('caseRolesBulkEmail', $_GET, '0') === '1' &&
      CRM_Utils_Array::value('snippet', $_GET, '0') === CRM_Core_Smarty::PRINT_JSON &&
      CRM_Utils_Array::value('cid', $_GET, '0') &&
      (bool) Civi::settings()->get('civicaseLimitRecipientFields')
    );
  }

}
