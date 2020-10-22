<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Civicase_Hook_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * FilterByCaseCategoryOnChangeCaseType class.
 */
class CRM_Civicase_Hook_BuildForm_FilterByCaseCategoryOnChangeCaseType {

  /**
   * Filters by case category.
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

    $this->filterCaseTypeOptionsByCaseCategory($form);
  }

  /**
   * Filters the options for the case type select element based on the category.
   *
   * @param CRM_Core_Form $form
   *   Form class object.
   */
  private function filterCaseTypeOptionsByCaseCategory(CRM_Core_Form $form) {
    $caseCategoryName = CaseCategoryHelper::getCategoryName($form->_caseId[0]);
    $caseTypesInCategory = CaseTypeCategoryHelper::getCaseTypesForCategory($caseCategoryName);

    if (!$caseTypesInCategory) {
      $caseTypesInCategory = [];
    }

    $caseTypeIdElement = &$form->getElement('case_type_id');
    $options = $caseTypeIdElement->_options;

    foreach ($options as $key => $option) {
      $optionValue = $option['attr']['value'];
      if (!in_array($optionValue, $caseTypesInCategory) && $optionValue) {
        unset($options[$key]);
      }
    }

    sort($options);
    $caseTypeIdElement->_options = $options;
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
    if ($formName != 'CRM_Case_Form_Activity') {
      return FALSE;
    }

    return $form->_activityTypeName == 'Change Case Type';
  }

}
