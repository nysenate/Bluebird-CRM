<?php

/**
 * Sets up Case Category Instance Support.
 */
class CRM_Civicase_Setup_CaseCategoryInstanceSupport {

  const INSTANCE_OPTION_GROUP = 'case_category_instance_type';

  /**
   * Adds Case Category Instance Support.
   */
  public function apply() {
    $this->createCaseCategoryInstanceOptionGroup();
    $this->createDefaultInstanceOptionValue();
  }

  /**
   * Create Default Instance Type option value.
   */
  private function createDefaultInstanceOptionValue() {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists(
      [
        'option_group_id' => self::INSTANCE_OPTION_GROUP,
        'name' => 'case_management',
        'label' => 'Case Management',
        'grouping' => 'CRM_Civicase_Service_CaseManagementUtils',
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]
    );
  }

  /**
   * Create Case Category Instance Type option group.
   */
  public function createCaseCategoryInstanceOptionGroup() {
    CRM_Core_BAO_OptionGroup::ensureOptionGroupExists(
      [
        'name' => self::INSTANCE_OPTION_GROUP,
        'title' => ts('Case Category Instance Type'),
        'is_reserved' => 1,
      ]
    );
  }

}
