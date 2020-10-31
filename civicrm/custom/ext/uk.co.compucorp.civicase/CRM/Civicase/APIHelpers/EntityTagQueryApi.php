<?php

/**
 * Class CRM_Civicase_APIHelpers_EntityTagQueryApi.
 */
class CRM_Civicase_APIHelpers_EntityTagQueryApi {

  /**
   * Validates the Entity Tag Query related API parameters.
   *
   * @param array $params
   *   API parameters.
   */
  public function validateParameters(array $params) {
    if (!empty($params['params']) && !empty($params['entity_id'])) {
      throw new API_Exception('Please send either the params or Entity ID');
    }

    if (empty($params['params']) && empty($params['entity_id'])) {
      throw new API_Exception('Both params and Entity ID cannot be empty');
    }
  }

  /**
   * Returns the name of an entity given the database table name.
   *
   * @param string $tableName
   *   Table name.
   *
   * @return null|string
   *   Entity Name.
   */
  public function getEntityNameFromTable($tableName) {
    $className = CRM_Core_DAO_AllCoreTables::getClassForTable($tableName);
    return CRM_Core_DAO_AllCoreTables::getBriefName($className);
  }

}
