<?php

use CRM_Civicase_Hook_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * CRM_Civicase_Hook_BuildForm_CaseCategoryCustomFieldsProcessing class.
 */
class CRM_Civicase_Hook_BuildForm_CaseCategoryCustomFieldsProcessing {

  /**
   * Filters the options for the case type select element based on the category.
   *
   * Updates the onchange attribute for the case type element.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    $caseCategoryName = $this->getCaseCategoryName($form);

    if (!$this->shouldRun($formName, $caseCategoryName)) {
      return;
    }

    if (!CaseTypeCategoryHelper::isValidCategory($caseCategoryName)) {
      return;
    }

    $this->filterCaseTypeOptionValues($form, $caseCategoryName);
  }

  /**
   * Filters the options for the case type select element based on the category.
   *
   * @param CRM_Core_Form $form
   *   Form class object.
   * @param string $caseCategoryName
   *   Case category name.
   */
  private function filterCaseTypeOptionValues(CRM_Core_Form $form, $caseCategoryName) {
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
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($formName, $caseCategoryName) {
    if (!$caseCategoryName) {
      return FALSE;
    }

    $isCaseForm = $formName == CRM_Case_Form_Case::class;

    return $isCaseForm && $caseCategoryName;
  }

  /**
   * Gets the Category Name for the case.
   *
   * @param CRM_Core_Form $form
   *   Form name.
   *
   * @return string|null
   *   case category name.
   */
  private function getCaseCategoryName(CRM_Core_Form $form) {
    $urlParams = parse_url(htmlspecialchars_decode($form->controller->_entryURL), PHP_URL_QUERY);
    parse_str($urlParams, $urlParams);

    return !empty($urlParams['case_type_category']) ? $urlParams['case_type_category'] : 'cases';
  }

}
