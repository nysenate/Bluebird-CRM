<?php

/**
 * AddCaseCategoryInstanceField BuildForm Hook Class.
 */
class CRM_Civicase_Hook_BuildForm_AddCaseCategoryInstanceField extends CRM_Civicase_Hook_CaseCategoryInstanceBase {

  /**
   * Adds the Case Category Instance Form field.
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

    $this->addCategoryInstanceFormField($form);
    $this->addCategoryInstanceTemplate();
  }

  /**
   * Adds the Case Category Instance Form field.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   */
  private function addCategoryInstanceFormField(CRM_Core_Form $form) {
    $caseCategoryInstance = $form->add(
      'select',
      self::INSTANCE_TYPE_FIELD_NAME,
      ts('Instance Type'),
      CRM_Core_OptionGroup::values('case_category_instance_type', FALSE, FALSE, TRUE),
      TRUE,
      ['placeholder' => TRUE]
    );

    if ($form->getVar('_id')) {
      $caseCategoryValues = $form->getVar('_values');
      $defaultInstanceValue = $this->getDefaultValue($caseCategoryValues['value']);
      $caseCategoryInstance->setValue($defaultInstanceValue);
    }
  }

  /**
   * Adds the template for case category instance field template.
   */
  private function addCategoryInstanceTemplate() {
    $templatePath = CRM_Civicase_ExtensionUtil::path() . '/templates';
    CRM_Core_Region::instance('page-body')->add(
      [
        'template' => "{$templatePath}/CRM/Civicase/Form/CaseCategoryInstance.tpl",
      ]
    );
  }

  /**
   * Returns the default value for the category instance field.
   *
   * @param int $categoryValue
   *   Category value.
   *
   * @return mixed|null
   *   Default value.
   */
  private function getDefaultValue($categoryValue) {
    $result = civicrm_api3('CaseCategoryInstance', 'get', [
      'category_id' => $categoryValue,
      'sequential' => 1,
    ]);

    if ($result['count'] == 0) {
      return NULL;
    }

    return $result['values'][0]['instance_id'];
  }

}
