<?php

use CRM_Core_DAO_CustomGroup as CustomGroup;

/**
 * The base class for case category custom group processor classes.
 */
abstract class CRM_Civicase_Service_BaseCustomGroupPostProcessor {

  /**
   * Case type categories.
   *
   * @var array
   */
  protected $caseTypeCategories;

  /**
   * Stores the case type categories in a variable.
   */
  public function __construct() {
    $caseTypeCategories = CRM_Civicase_Helper_CaseCategory::getCaseCategories();
    $this->caseTypeCategories = array_column($caseTypeCategories, 'value', 'name');
  }

  /**
   * Handles the saving of a custom group related to a case type category.
   *
   * @param \CRM_Core_DAO_CustomGroup $customGroup
   *   Custom group object.
   */
  abstract public function saveCustomGroupForCaseCategory(CustomGroup $customGroup);

  /**
   * Updates the custom group entity.
   *
   * @param \CRM_Core_DAO_CustomGroup $customGroup
   *   Custom Group object.
   * @param string $columnValue
   *   Extends entity column value.
   */
  public function updateCustomGroup(CustomGroup $customGroup, $columnValue) {
    $customGroup->extends_entity_column_value = $columnValue;
    $customGroup->extends_entity_column_id = $this->caseTypeCategories[$customGroup->extends];
    $customGroup->extends = 'Case';
    $customGroup->save();
  }

}
