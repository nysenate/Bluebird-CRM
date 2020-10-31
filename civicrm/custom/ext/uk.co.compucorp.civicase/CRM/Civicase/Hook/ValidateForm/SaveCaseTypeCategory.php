<?php

/**
 * Class CRM_Civicase_Hook_ValidateForm_SaveCaseTypeCategory.
 */
class CRM_Civicase_Hook_ValidateForm_SaveCaseTypeCategory {

  /**
   * Validates case type category.
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

    // Validate the label (category name).
    $field_name = 'label';
    if (!empty($fields[$field_name]) && !preg_match('!^[a-zA-Z0-9 -]+$!', $fields[$field_name])) {
      $errors[$field_name] = ts('Allowed characters: letters (a-z), numbers, space and hyphen.');
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
   *   TRUE if hook should run, FALSE otherwise.
   */
  private function shouldRun(CRM_Core_Form $form, $formName) {
    return $formName == 'CRM_Admin_Form_Options' && $form->getVar('_gName') == 'case_type_categories';
  }

}
