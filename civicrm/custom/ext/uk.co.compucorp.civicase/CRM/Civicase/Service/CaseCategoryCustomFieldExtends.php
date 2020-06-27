<?php

/**
 * Class CRM_Civicase_Service_CaseCategoryCustomFieldExtends.
 */
class CRM_Civicase_Service_CaseCategoryCustomFieldExtends {

  /**
   * Creates the Custom field extend option group for case category.
   *
   * @param string $caseCategoryName
   *   Case Category Name.
   * @param string $label
   *   Label.
   */
  public function create($caseCategoryName, $label) {
    $result = $this->getCgExtendOptionValue($caseCategoryName);

    if ($result['count'] > 0) {
      return;
    }

    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'cg_extend_objects',
      'name' => 'civicrm_case',
      'label' => $label,
      'value' => $caseCategoryName,
      'description' => NULL,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

  /**
   * Deletes the Custom field extend option group for case category.
   *
   * @param string $caseCategoryName
   *   Case Category Name.
   */
  public function delete($caseCategoryName) {
    $result = $this->getCgExtendOptionValue($caseCategoryName);

    if ($result['count'] == 0) {
      return;
    }

    CRM_Core_BAO_OptionValue::del($result['values'][0]['id']);
  }

  /**
   * Return CG Extend option value.
   *
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return array
   *   Cg Extend option value.
   */
  private function getCgExtendOptionValue($caseCategoryName) {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'value' => $caseCategoryName,
      'option_group_id' => 'cg_extend_objects',
    ]);

    return $result;
  }

}
