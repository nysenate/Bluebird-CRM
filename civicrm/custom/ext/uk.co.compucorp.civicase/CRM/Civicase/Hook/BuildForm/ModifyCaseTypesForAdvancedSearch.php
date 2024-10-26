<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Case_BAO_CaseType as CaseType;

/**
 * Class CRM_Civicase_Hook_BuildForm_ModifyCaseTypesForAdvancedSearch.
 */
class CRM_Civicase_Hook_BuildForm_ModifyCaseTypesForAdvancedSearch {

  /**
   * Accessible case categories for logged in user.
   *
   * @var array
   */
  private $accessibleCaseCategories;

  /**
   * Runs the Case Client populator hook for the Case Form.
   *
   * @param CRM_Core_Form $form
   *   Form object class.
   * @param string $formName
   *   Form name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($form, $formName)) {
      return;
    }

    $this->accessibleCaseCategories = array_keys(CaseCategoryHelper::getAccessibleCaseTypeCategories());
    if (!$this->shouldModifyCaseTypeField()) {
      return;
    }

    $this->restrictCaseTypeOptions($form);
  }

  /**
   * Restrict case type options based on user's case category access.
   *
   * @param CRM_Core_Form $form
   *   Form object.
   */
  private function restrictCaseTypeOptions(CRM_Core_Form $form) {
    $caseTypeElement = $form->getElement('case_type_id');
    $caseTypeOptions = $caseTypeElement->_options;
    $accessibleCaseTypes = array_keys($this->getAccessibleCaseTypes());
    foreach ($caseTypeOptions as $key => $caseTypeOption) {
      $optionValue = $caseTypeOption['attr']['value'];
      if (!in_array($optionValue, $accessibleCaseTypes) && $optionValue) {
        unset($caseTypeOptions[$key]);
      }
    }
    sort($caseTypeOptions);

    $caseTypeElement->_options = $caseTypeOptions;
  }

  /**
   * Returns the case types user has access to based on case category.
   *
   * @return array
   *   Accessible case types.
   */
  private function getAccessibleCaseTypes() {
    $result = civicrm_api3('CaseType', 'get', [
      'return' => ['id'],
      'case_type_category' => ['IN' => $this->accessibleCaseCategories],
    ]);

    return $result['values'];
  }

  /**
   * Whether to modify case type or not.
   *
   * @return bool
   *   Whether the case type field should be modified or not.
   */
  private function shouldModifyCaseTypeField() {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');

    return !empty($this->accessibleCaseCategories) && (count($this->accessibleCaseCategories) < count($caseTypeCategories));
  }

  /**
   * Determines if the hook will run.
   *
   * @param CRM_Core_Form $form
   *   Form object.
   * @param string $formName
   *   Form Name.
   */
  public function shouldRun(CRM_Core_Form $form, $formName) {
    $isAdvancedSearchForm = $formName === CRM_Contact_Form_Search_Advanced::class;

    return $isAdvancedSearchForm && $form->elementExists('case_type_id');
  }

}
