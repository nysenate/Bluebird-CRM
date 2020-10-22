<?php

/**
 * CRM_Civicase_Hook_BuildForm_CaseCategoryCustomGroupDisplay class.
 */
class CRM_Civicase_Hook_BuildForm_CaseCategoryCustomGroupDisplay {

  /**
   * Displays Case Category Custom Group on custom group form page.
   *
   * This hook properly displays the Entity that the custom group is extending
   * on the form based on the case type category value stored in the
   * `extends_entity_column_id` of the `custom_group` table.
   *
   * Updates the onchange attribute for the case type element.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($formName, $form)) {
      return;
    }

    $this->setDefaultFormValueForCaseCategory($form);
  }

  /**
   * Sets default form value if the extended entity is for a case category.
   *
   * @param CRM_Core_Form $form
   *   Form Object.
   */
  private function setDefaultFormValueForCaseCategory(CRM_Core_Form &$form) {
    $defaults = $form->getVar('_defaults');
    $extends = $defaults['extends'][0];
    $extendsId = $defaults['extends_entity_column_id'];
    $caseTypeCategories = (CRM_Case_BAO_CaseType::buildOptions('case_type_category', 'validate'));
    if ($extends === 'Case' && !empty($extendsId)) {
      $defaults['extends'][0] = $caseTypeCategories[$extendsId];
      $hierSelect = $form->getElement('extends');
      $hierSelectElements = $hierSelect->getElements();
      $hierSelectElements[1]->_options = [];
      $hierSelect->setValue([$caseTypeCategories[$extendsId]]);
    }
  }

  /**
   * Determines if the hook will run.
   *
   * @param string $formName
   *   Form name.
   * @param CRM_Core_Form $form
   *   Form class object.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($formName, CRM_Core_Form $form) {
    return $formName == CRM_Custom_Form_Group::class && $form->getVar('_action') != CRM_Core_Action::ADD;
  }

}
