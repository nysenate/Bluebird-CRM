<?php

/**
 * Custom Groups API Helper Class.
 */
class CRM_Civicase_APIHelpers_CustomGroups {

  /**
   * Returns a list of active custom groups for the given entity.
   *
   * @param string $entityName
   *   The name of the entity to get active groups for.
   *
   * @return array
   *   Custom Group Api response.
   */
  public static function getAllActiveGroupsForEntity($entityName) {
    return civicrm_api3('CustomGroup', 'get', [
      'extends' => $entityName,
      'options' => ['limit' => 0],
      'is_active' => 1,
    ]);
  }

  /**
   * Returns the custom group ID for the given custom group name.
   *
   * Returns NULL if no group was found.
   *
   * @param string $customGroupName
   *   A custom group name.
   *
   * @return int|null
   *   A custom group id or NULL.
   */
  public static function getIdForGroupName($customGroupName) {
    try {
      $result = civicrm_api3('CustomGroup', 'getsingle', [
        'return' => ['id'],
        'name' => $customGroupName,
      ]);

      return !empty($result['id']) ? $result['id'] : NULL;
    }
    catch (CiviCRM_API3_Exception $e) {
    }

    return NULL;
  }

}
