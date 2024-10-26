<?php

use CRM_Civicase_Hook_Permissions_ExportCasesAndReports as ExportCasesAndReports;

/**
 * Removes export option from report actions based on permissions.
 */
class CRM_Civicase_Hook_BuildForm_RemoveExportActionFromReports {

  /**
   * Value of export action in reports.
   *
   * @var string export option value
   */
  const EXPORT_OPTION_VALUE = 'report_instance.csv';

  /**
   * Remove export action from reports actions.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($form)) {
      return;
    }

    $this->removeExportAction($form);
  }

  /**
   * Remove export action.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   */
  private function removeExportAction(CRM_Core_Form $form) {
    if ($form->elementExists('task') && $form->getElement('task') instanceof HTML_QuickForm_select) {
      $element = $form->getElement('task');
      $this->removeExportOption($element->_options);
    }
  }

  /**
   * Remove export from options.
   *
   * @param array $options
   *   List of options.
   */
  private function removeExportOption(array &$options) {
    foreach ($options as $k => $option) {
      if ($option['attr']['value'] === self::EXPORT_OPTION_VALUE) {
        unset($options[$k]);
      }
    }
  }

  /**
   * Determines if the hook will run.
   *
   * Will run if the user does not have export permissions.
   *
   * @param CRM_Core_Form $form
   *   Form class object.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun(CRM_Core_Form $form) {
    return !CRM_Core_Permission::check(ExportCasesAndReports::PERMISSION_NAME)
      && $form instanceof CRM_Report_Form;
  }

}
