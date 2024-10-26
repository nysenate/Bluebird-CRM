<?php

/**
 * Class for managing support for case type category custom fields.
 */
class CRM_Civicase_Service_CaseCategoryCustomFieldExtends {

  /**
   * Entity table value.
   *
   * @var string
   */
  protected $entityTable = 'civicrm_case';

  /**
   * Creates the Custom field extend option group for case category.
   *
   * @param string $entityValue
   *   Entity Value for the custom entity.
   * @param string $label
   *   Label.
   * @param string $entityTypeFunction
   *   Function to fetch entity types for the entity.
   */
  public function create($entityValue, $label, $entityTypeFunction = NULL) {
    $result = $this->getCgExtendOptionValue($entityValue);

    if ($result['count'] > 0) {
      return;
    }

    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'cg_extend_objects',
      'name' => $this->entityTable,
      'label' => $label,
      'value' => $this->getCustomEntityValue($entityValue),
      'description' => $entityTypeFunction,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

  /**
   * Deletes the Custom field extend option group for case category.
   *
   * @param string $entityValue
   *   Entity Value for the custom entity.
   */
  public function delete($entityValue) {
    $result = $this->getCgExtendOptionValue($entityValue);

    if ($result['count'] == 0) {
      return;
    }

    CRM_Core_BAO_OptionValue::del($result['values'][0]['id']);
  }

  /**
   * Return CG Extend option value.
   *
   * @param string $entityValue
   *   Entity Value for the custom entity.
   *
   * @return array
   *   Cg Extend option value.
   */
  protected function getCgExtendOptionValue($entityValue) {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'value' => $this->getCustomEntityValue($entityValue),
      'option_group_id' => 'cg_extend_objects',
      'name' => $this->entityTable,
    ]);

    return $result;
  }

  /**
   * Returns the custom entity value.
   *
   * @param string $entityValue
   *   Entity Value for the custom entity.
   *
   * @return string
   *   Custom entity value.
   */
  protected function getCustomEntityValue($entityValue) {
    return $entityValue;
  }

}
