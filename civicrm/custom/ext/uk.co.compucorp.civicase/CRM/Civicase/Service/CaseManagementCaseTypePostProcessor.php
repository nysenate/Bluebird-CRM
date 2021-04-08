<?php

use CRM_Civicase_Service_BaseCaseTypePostProcessor as BaseCaseTypePostProcessor;
use CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess as CaseManagementCustomGroupPostProcess;

/**
 * Handles the custom field related post processing for a case type.
 *
 * This class is specific for the Case management instance and handles the
 * post processing as related to custom field set associated with a case type
 * when the case type is created/updated.
 */
class CRM_Civicase_Service_CaseManagementCaseTypePostProcessor extends BaseCaseTypePostProcessor {

  /**
   * Stores the CaseManagement Post process helper.
   *
   * @var \CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess
   */
  private $postProcessHelper;

  /**
   * Constructor function.
   *
   * @param \CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess $postProcessHelper
   *   Post process helper class.
   */
  public function __construct(CaseManagementCustomGroupPostProcess $postProcessHelper) {
    $this->postProcessHelper = $postProcessHelper;
  }

  /**
   * Handles case type post processing on create.
   *
   * @param int $caseTypeId
   *   Case Type ID.
   */
  public function processCaseTypeCustomGroupsOnCreate($caseTypeId) {
    $customGroups = $this->postProcessHelper->getCaseTypeCustomGroups($caseTypeId);
    if (empty($customGroups)) {
      return;
    }
    foreach ($customGroups as $cusGroup) {
      $extendColValue = !empty($cusGroup['extends_entity_column_value']) ? $cusGroup['extends_entity_column_value'] : [];
      $entityColumnValues = array_merge($extendColValue, [$caseTypeId]);
      $this->updateCustomGroup($cusGroup['id'], $entityColumnValues);
    }
  }

  /**
   * Handles case type post processing on update.
   *
   * @param int $caseTypeId
   *   Case type Id.
   */
  public function processCaseTypeCustomGroupsOnUpdate($caseTypeId) {
    $mismatchCustomGroups = $this->postProcessHelper->getCaseTypeCustomGroupsWithCategoryMismatch($caseTypeId);
    if (empty($mismatchCustomGroups)) {
      return;
    }
    foreach ($mismatchCustomGroups as $cusGroup) {
      $entityColumnValues = array_diff($cusGroup['extends_entity_column_value'], [$caseTypeId]);
      $entityColumnValues = $entityColumnValues ? $entityColumnValues : NULL;
      $this->updateCustomGroup($cusGroup['id'], $entityColumnValues);
    }

    $this->processCaseTypeCustomGroupsOnCreate($caseTypeId);
  }

}
