<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Class CaseCategoryWordReplacementsForChangeCase.
 */
class CRM_Civicase_Hook_PreProcess_CaseCategoryWordReplacementsForChangeCase {

  /**
   * Adds the word replacements array to Civi's translation locale.
   *
   * @param string $formName
   *   Form Name.
   * @param CRM_Core_Form $form
   *   Form Class object.
   */
  public function run($formName, CRM_Core_Form &$form) {
    if (!$this->shouldRun($formName, $form)) {
      return;
    }

    $this->addWordReplacements($form);
    $this->setPageTitle($form);
  }

  /**
   * Adds the word replacements array to Civi's translation locale.
   *
   * This will make Civi automatically translate form labels that are
   * displayed using the ts function.
   *
   * @param CRM_Core_Form $form
   *   Page class.
   */
  private function addWordReplacements(CRM_Core_Form $form) {
    $caseCategoryName = CaseCategoryHelper::getCategoryName($form->_caseId[0]);
    CRM_Civicase_Hook_Helper_CaseTypeCategory::addWordReplacements($caseCategoryName);
  }

  /**
   * Sets the Page title.
   *
   * We need to translate this manually as Civi did not translate the page
   * title. Because the form object has no function to get the current
   * page title, we need to re-construct the page title similar to what is
   * done in activity form class.
   *
   * @param CRM_Core_Form $form
   *   Form Object.
   */
  private function setPageTitle(CRM_Core_Form $form) {
    $pageTitle = ts($form->get_template_vars('activityTypeName'));
    $displayName = $this->getContactDisplayName($form);
    if ($displayName) {
      CRM_Utils_System::setTitle($displayName . ' - ' . $pageTitle);
    }
    else {
      CRM_Utils_System::setTitle(ts('%1 Activity', [1 => $pageTitle]));
    }
  }

  /**
   * Function to get currently viewed contact display name.
   *
   * @param CRM_Core_Form $form
   *   Form object.
   *
   * @return null|string
   *   Contact display name.
   */
  private function getContactDisplayName(CRM_Core_Form $form) {
    if ($form->_currentlyViewedContactId) {
      $displayName = CRM_Contact_BAO_Contact::displayName($form->_currentlyViewedContactId);

      return $displayName;
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
    return $formName == 'CRM_Case_Form_Activity';
  }

}
