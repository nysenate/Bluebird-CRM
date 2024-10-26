<?php

/**
 * Class CRM_Civicase_Hook_PostProcess_SetUserContextForSaveAndNewCase.
 */
class CRM_Civicase_Hook_PostProcess_SetUserContextForSaveAndNewCase {

  /**
   * Sets the User context URL when saving and adding a new case.
   *
   * @param string $formName
   *   Form name.
   * @param CRM_Core_Form $form
   *   Form object class.
   */
  public function run($formName, CRM_Core_Form &$form) {
    if (!$this->shouldRun($form, $formName)) {
      return;
    }

    CRM_Core_Session::singleton()->replaceUserContext($form->controller->_entryURL);
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
    $buttonName = $form->controller->getButtonName();
    return $formName == CRM_Case_Form_Case::class && $buttonName == $form->getButtonName('upload', 'new');
  }

}
