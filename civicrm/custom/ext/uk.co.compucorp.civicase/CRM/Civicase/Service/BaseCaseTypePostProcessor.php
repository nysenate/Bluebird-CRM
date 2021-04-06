<?php

/**
 * The base class for case category case type processor classes.
 */
abstract class CRM_Civicase_Service_BaseCaseTypePostProcessor {

  /**
   * This function updates the custom groups for the case type on create.
   *
   * The case type when created need to be added to the custom group
   * extending the Case entity for the case category of the case type.
   *
   * @param int $caseTypeId
   *   Custom group object..
   */
  abstract public function processCaseTypeCustomGroupsOnCreate($caseTypeId);

  /**
   * This function updates the custom groups for the case type on update.
   *
   * The case type when created need to be added to the custom group extending
   * the Case entity for the case category of the case type and also needs to be
   * removed from custom groups extending the Case entity for case category that
   * is not same as the case type (For the case when the case category of case
   * type is updated to another).
   *
   * @param int $caseTypeId
   *   Custom group object.
   */
  abstract public function processCaseTypeCustomGroupsOnUpdate($caseTypeId);

  /**
   * Updates a custom group.
   *
   * We are using the custom group object here rather than the API because if
   * this is updated via the API the `extends_entity_column_id` field will be
   * set to NULL and this is needed to keep track of custom groups extending
   * case categories.
   *
   * @param int $id
   *   Custom group Id.
   * @param array|null $entityColumnValues
   *   Entity custom values for custom group.
   */
  protected function updateCustomGroup($id, $entityColumnValues) {
    $cusGroup = new CRM_Core_BAO_CustomGroup();
    $cusGroup->id = $id;
    $entityColValue = is_null($entityColumnValues) ? 'null' : CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $entityColumnValues) . CRM_Core_DAO::VALUE_SEPARATOR;
    $cusGroup->extends_entity_column_value = $entityColValue;
    $cusGroup->save();
  }

}
