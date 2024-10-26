<?php

use CRM_Civicase_Service_BaseCustomGroupDisplayFormatter as BaseCustomGroupDisplayFormatter;
use CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess as CaseManagementCustomGroupPostProcess;

/**
 * Case Management Instance Custom Group page formatter.
 */
class CRM_Civicase_Service_CaseManagementCustomGroupDisplayFormatter extends BaseCustomGroupDisplayFormatter {

  /**
   * Stores the CaseManagement Post process helper.
   *
   * @var \CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess
   */
  private $postProcessHelper;

  /**
   * Stores case type categories.
   *
   * @var array
   */
  private $caseTypeCategories;

  /**
   * Stores option values for  `cg_extends` option group.
   *
   * @var array
   */
  private $cgExtendValues;

  /**
   * Constructor function.
   *
   * @param \CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess $postProcessHelper
   *   Post process helper class.
   */
  public function __construct(CaseManagementCustomGroupPostProcess $postProcessHelper) {
    $this->postProcessHelper = $postProcessHelper;
    $this->caseTypeCategories = $postProcessHelper->getCaseTypeCategories();
    $this->cgExtendValues = $postProcessHelper->getCgExtendValues();
  }

  /**
   * Sets the correct label for custom group category on listing page.
   *
   * @param array $row
   *   One of the rows of the custom group listing page.
   */
  public function processDisplay(array &$row) {
    $row['extends_display'] = $this->cgExtendValues[$this->caseTypeCategories[$row['extends_entity_column_id']]];
  }

}
