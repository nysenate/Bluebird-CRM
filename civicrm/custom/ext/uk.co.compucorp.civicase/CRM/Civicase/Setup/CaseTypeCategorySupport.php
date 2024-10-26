<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;

class CRM_Civicase_Setup_CaseTypeCategorySupport {

  public function apply() {
    $this->addCaseCategoryDBColumn();
    $this->createCaseCategoryOptionGroup();

    return TRUE;
  }

  /**
   * Add Case Type Category Column to the Case Type table
   */
  private function addCaseCategoryDBColumn () {
    $caseTypeTable = CRM_Case_BAO_CaseType::getTableName();
    $caseCategoryColumnName = 'case_type_category';

    if (!SchemaHandler::checkIfFieldExists($caseTypeTable, $caseCategoryColumnName)) {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE {$caseTypeTable}
        ADD COLUMN {$caseCategoryColumnName} INT(10)");
    }
  }

  /**
   * Create Case Type Category option group
   */
  private function createCaseCategoryOptionGroup () {
    CRM_Core_BAO_OptionGroup::ensureOptionGroupExists([
      'name' => 'case_type_categories',
      'title' => ts('Case Type Categories'),
      'is_reserved' => 1,
    ]);
  }

}
