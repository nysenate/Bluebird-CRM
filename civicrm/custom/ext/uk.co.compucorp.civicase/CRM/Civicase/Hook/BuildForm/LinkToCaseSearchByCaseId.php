<?php

/**
 * Class CRM_Civicase_Hook_BuildForm_LinkToCaseSearchByCaseId.
 */
class CRM_Civicase_Hook_BuildForm_LinkToCaseSearchByCaseId {

  /**
   * Adds a parameter that allows to search by Case Id.
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

    $this->setSearchByCaseIdParams($form);
  }

  /**
   * Sets the search by Case ID parameter.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   */
  private function setSearchByCaseIdParams(CRM_Core_Form $form) {
    $linkToCase = &$form->getElement('link_to_case_id');
    $dataApiParams = $linkToCase->_attributes['data-api-params'];
    if (empty($dataApiParams)) {
      return;
    }
    $dataApiParams = json_decode($dataApiParams, TRUE);
    $dataApiParams['params']['search_by_case_id'] = TRUE;
    $linkToCase->_attributes['data-api-params'] = json_encode($dataApiParams);
  }

  /**
   * Determines if the hook will run.
   *
   * Will run if the form is the Link Case activity form.
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
    return $formName == CRM_Case_Form_Activity::class && $form->_activityTypeName == 'Link Cases';
  }

}
