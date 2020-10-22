<?php

/**
 * Class CRM_Civicase_Hook_BuildForm_EnableCaseCategoryIconField.
 */
class CRM_Civicase_Hook_BuildForm_EnableCaseCategoryIconField {

  /**
   * Shows the icon field for case category option value.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($form, $formName)) {
      return;
    }

    $form->add('text',
      'icon',
      ts('Icon'),
      [
        'class' => 'crm-icon-picker',
        'title' => ts('Choose Icon'),
        'allowClear' => TRUE,
      ]
    );
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
    $optionGroupName = $form->getVar('_gName');
    return $formName == 'CRM_Admin_Form_Options' && $optionGroupName == 'case_type_categories';
  }

}
