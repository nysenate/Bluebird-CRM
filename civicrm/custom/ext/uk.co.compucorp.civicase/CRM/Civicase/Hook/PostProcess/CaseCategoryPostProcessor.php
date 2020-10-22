<?php

use CRM_Civicase_Factory_CaseTypeCategoryEventHandler as CaseTypeCategoryEventHandlerFactory;

/**
 * Class CRM_Civicase_Hook_PostProcess_CaseCategoryPostProcessor.
 */
class CRM_Civicase_Hook_PostProcess_CaseCategoryPostProcessor {

  /**
   * Case Category Menu Links Processor.
   *
   * Creates/Deletes menus for the Case category option group is saved/deleted
   * based on the form action.
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

    // Get object data from submitted from.
    $formValues = $form->_submitValues;
    $caseCategoryValues = $form->getVar('_values');
    $categoryId = $form->getVar('_id');
    $categoryName = !empty($caseCategoryValues['name']) ? $caseCategoryValues['name'] : $formValues['label'];
    $categoryStatus = $formValues['is_active'];
    $categoryIcon = $formValues['icon'];

    $formAction = $form->getVar('_action');
    $handler = CaseTypeCategoryEventHandlerFactory::create();

    if ($formAction == CRM_Core_Action::UPDATE) {
      $handler->onUpdate($categoryId, $categoryStatus, $categoryIcon);
    }
    elseif ($formAction == CRM_Core_Action::ADD) {
      $handler->onCreate($categoryName);
    }
    elseif ($formAction == CRM_Core_Action::DELETE) {
      $handler->onDelete($categoryName);
    }

    // Flush all caches using the API.
    civicrm_api3('System', 'flush');
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
