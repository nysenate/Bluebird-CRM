<?php

use CRM_Civicase_Setup_ProcessCaseCategoryForCustomGroupSupport as ProcessCaseCategoryForCustomGroupSupport;
use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * CRM_Civicase_Upgrader_Steps_Step0010 class.
 */
class CRM_Civicase_Upgrader_Steps_Step0010 {

  /**
   * Performs Upgrade.
   */
  public function apply() {
    $this->removeRestrictionToCaseTypesForDefaultCaseEntity();
    $this->ProcessCaseCategoryForCustomGroupSupport();

    return TRUE;
  }

  /**
   * Process Case Category Custom Group Support.
   */
  private function processCaseCategoryForCustomGroupSupport() {
    $step = new ProcessCaseCategoryForCustomGroupSupport();
    $step->apply();
  }

  /**
   * Remove Restriction To CaseTypes For Default Case Entity.
   *
   * Civicrm by default adds the Case entity to the cg_extends option group.
   * THis extension modifies the function for fetching the case types to only
   * return cases belonging to category case but we don't need this logic again
   * so restoring to the default function Civicrm set it to be.
   */
  private function removeRestrictionToCaseTypesForDefaultCaseEntity() {
    $result = civicrm_api3('OptionValue', 'getsingle', [
      'option_group_id' => 'cg_extend_objects',
      'label' => CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME,
    ]);

    $description = 'CRM_Case_PseudoConstant::caseType;';

    if (empty($result['id']) || $result['description'] == $description) {
      return;
    }

    civicrm_api3('OptionValue', 'create', [
      'id' => $result['id'],
      'description' => $description,
    ]);
  }

}
