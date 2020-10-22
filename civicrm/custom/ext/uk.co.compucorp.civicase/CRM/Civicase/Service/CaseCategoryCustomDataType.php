<?php

/**
 * Class CRM_Civicase_Service_CaseCategoryCustomDataType.
 */
class CRM_Civicase_Service_CaseCategoryCustomDataType {

  /**
   * Creates the Custom data type option value for case category.
   *
   * Without this value, it will not be possible to set the case category value
   * in the `extends_entity_column_id` column of the custom group table as the
   * column must contain valid values from the custom_data_type option group.
   *
   * @param string $caseCategoryName
   *   Case Category Name.
   */
  public function create($caseCategoryName) {
    $result = $this->getCustomDataOptionValue($caseCategoryName);

    if ($result['count'] > 0) {
      return;
    }

    $caseCategoryOptions = array_flip(CRM_Case_BAO_CaseType::buildOptions('case_type_category', 'validate'));

    try {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => 'custom_data_type',
        'name' => $caseCategoryName,
        'label' => $caseCategoryName,
        'value' => $caseCategoryOptions[$caseCategoryName],
        'description' => NULL,
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]);
    }
    catch (Exception $e) {

    }

  }

  /**
   * Creates the Custom data type option value for case category.
   *
   * @param string $caseCategoryName
   *   Case Category Name.
   */
  public function delete($caseCategoryName) {
    $result = $this->getCustomDataOptionValue($caseCategoryName);

    if ($result['count'] == 0) {
      return;
    }

    CRM_Core_BAO_OptionValue::del($result['values'][0]['id']);
  }

  /**
   * Return Custom Data type option value.
   *
   * @param string $caseCategoryName
   *   Case Category Name.
   *
   * @return array
   *   Custom data type option value.
   */
  private function getCustomDataOptionValue($caseCategoryName) {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'name' => $caseCategoryName,
      'option_group_id' => 'custom_data_type',
    ]);

    return $result;
  }

}
