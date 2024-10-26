<?php

use CRM_Civicase_Service_CaseCategorySetting as CaseCategorySetting;
use CRM_Civicase_Hook_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Removes the popup for add case action if web form is available.
 */
class CRM_Civicase_Hook_SummaryActions_AlterAddCaseAction {

  /**
   * Case category Setting.
   *
   * @var CRM_Civicase_Service_CaseCategorySetting
   *   CaseCategorySetting service.
   */
  private $caseCategorySetting;

  /**
   * Initialize dependencies.
   */
  public function __construct() {
    $this->caseCategorySetting = new CaseCategorySetting();
  }

  /**
   * Removes the popup for add case action if web form is available.
   *
   * @param array $actions
   *   List of actions.
   * @param int $contactID
   *   Contact id.
   */
  public function run(array &$actions, $contactID) {
    if (!$this->shouldRun($actions, $contactID)) {
      return;
    }
    $this->changeAddCaseAction($actions);
  }

  /**
   * Checks the form name and contact id.
   *
   * @param array $actions
   *   List of actions.
   * @param int $contactID
   *   Contact id.
   *
   * @return bool
   *   Whether this hook should run or not.
   */
  private function shouldRun(array $actions, $contactID) {
    return (
      !empty($actions['case']) && $contactID > 0
    );
  }

  /**
   * Remove the popup for add case action.
   *
   * @param array $actions
   *   List of actions.
   */
  private function changeAddCaseAction(array &$actions) {
    $webFormUrl = CaseTypeCategoryHelper::getNewCaseCategoryWebformUrl('Cases', $this->caseCategorySetting);
    if ($webFormUrl) {
      $actions['case']['class'] = 'no-popup';
    }
  }

}
