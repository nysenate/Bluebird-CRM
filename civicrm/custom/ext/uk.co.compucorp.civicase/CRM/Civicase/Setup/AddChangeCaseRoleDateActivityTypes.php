<?php

/**
 * Creates the Change Case Role Date activity types.
 */
class CRM_Civicase_Setup_AddChangeCaseRoleDateActivityTypes {

  /**
   * Runs the setup commands.
   */
  public function apply() {
    $this->insertUniqueActivityType(
      'Change Case Role Start Date',
      ts('Change Case Role Start Date')
    );
    $this->insertUniqueActivityType(
      'Change Case Role End Date',
      ts('Change Case Role End Date')
    );

    return TRUE;
  }

  /**
   * Creates an activity type if it doesn't already exist.
   *
   * @param string $name
   *   The activity type's name.
   * @param string $label
   *   The activity type's label.
   */
  private function insertUniqueActivityType($name, $label) {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'activity_type',
      'name' => $name,
      'label' => $label,
      'is_active' => TRUE,
    ]);
  }

}
