<?php

/**
 * CRM_Civicase_Upgrader_Steps_Step0011 class.
 */
class CRM_Civicase_Upgrader_Steps_Step0011 {

  /**
   * Performs Upgrade.
   */
  public function apply() {
    $this->updateFileUploadCategoryGrouping();

    return TRUE;
  }

  /**
   * Update file upload category grouping.
   */
  private function updateFileUploadCategoryGrouping() {
    try {
      // Find category.
      $category = civicrm_api3('OptionValue', 'getsingle', [
        'name' => 'File Upload',
        'component_id' => 'CiviCase',
        'option_group_id' => 'activity_type',
        'grouping' => ['IS NULL' => 1],
      ]);

      // Set it's grouping to 'file'.
      if (!empty($category['id'])) {
        $category['grouping'] = 'file';
        civicrm_api3('OptionValue', 'create', $category);
      }
    }
    catch (Exception $e) {
    }
  }

}
