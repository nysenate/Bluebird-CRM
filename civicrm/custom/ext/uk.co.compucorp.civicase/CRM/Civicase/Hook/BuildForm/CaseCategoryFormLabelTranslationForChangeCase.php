<?php

/**
 * Class CaseCategoryFormLabelTranslationForChangeCase.
 */
class CRM_Civicase_Hook_BuildForm_CaseCategoryFormLabelTranslationForChangeCase {

  /**
   * Elements names that needs label translation.
   *
   * @var array
   */
  private $elementsToTranslateLabel = [
    'case_status_id',
    'case_type_id',
    'link_to_case_id',
  ];

  /**
   * Translate some case form labels that Civi did not run translation for.
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

    $this->translateFormLabels($form);
  }

  /**
   * Translate some case form labels that Civi did not run translation for.
   *
   * Some Form labels are not ran through Civ's Ts function. We need to
   * do this, so this function does that.
   *
   * @param CRM_Core_Form $form
   *   Page class.
   */
  private function translateFormLabels(CRM_Core_Form $form) {
    foreach ($this->elementsToTranslateLabel as $elementName) {
      if ($form->elementExists($elementName)) {
        $element = &$form->getElement($elementName);
        $this->translateLabel($element);
      }
    }
  }

  /**
   * Translate the form labels for a form elements.
   *
   * @param object $element
   *   For Elements array.
   */
  private function translateLabel($element) {
    $label = ts($element->getLabel());
    $element->setLabel($label);
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

    return in_array($form->_activityTypeName, [
      'Change Case Type',
      'Change Case Status',
      'Link Cases',
    ]);
  }

}
