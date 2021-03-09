<?php

use CRM_Core_DAO_CustomGroup as CustomGroup;
use CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess as CaseManagementCustomGroupPostProcess;

/**
 * Case management custom group post processor class.
 *
 * Handles events after a custom group extending a case category entity
 * is saved.
 */
class CRM_Civicase_Service_CaseManagementCustomGroupPostProcessor extends CRM_Civicase_Service_BaseCustomGroupPostProcessor {

  /**
   * Stores the CaseManagement Post process helper.
   *
   * @var \CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess
   *   Post process helper class.
   */
  private $postProcessHelper;

  /**
   * Constructor function.
   *
   * @param \CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess $postProcessHelper
   *   Post process helper class.
   */
  public function __construct(CaseManagementCustomGroupPostProcess $postProcessHelper) {
    parent::__construct();
    $this->postProcessHelper = $postProcessHelper;
  }

  /**
   * Saves case type category custom groups.
   *
   * This function allows saving the Custom groups that extends a Case category
   * (that belongs to the Case Management Instance) entity to extend the Case
   * entity and to save the ID of the case category in the
   * `extends_entity_column_id` of the `custom_group` table and also to store
   * all case types for the Case category in the `extends_entity_column_value`
   * column.
   *
   * @param \CRM_Core_DAO_CustomGroup $customGroup
   *   Custom Group Object.
   */
  public function saveCustomGroupForCaseCategory(CustomGroup $customGroup) {
    if (empty($this->caseTypeCategories[$customGroup->extends])) {
      return;
    }

    $caseTypeIds = $this->postProcessHelper->getCaseTypeIdsForCaseCategory($this->caseTypeCategories[$customGroup->extends]);
    $ids = 'null';
    if (!empty($caseTypeIds)) {
      $ids = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $caseTypeIds) . CRM_Core_DAO::VALUE_SEPARATOR;
    }
    $this->updateCustomGroup($customGroup, $ids);
  }

}
