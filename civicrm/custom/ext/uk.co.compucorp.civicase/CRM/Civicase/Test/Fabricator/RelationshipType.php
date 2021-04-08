<?php

/**
 * RelationshipType Fabricator class.
 */
class CRM_Civicase_Test_Fabricator_RelationshipType {

  /**
   * Default parameters.
   *
   * @var array
   */
  private static $defaultParams = [
    'sequential' => 1,
    'name_a_b' => 'test AB',
    'name_b_a' => 'test BA',
    'contact_type_a' => 'Individual',
    'contact_type_b' => 'Individual',
  ];

  /**
   * Fabricate a relationship type.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   API results.
   */
  public static function fabricate(array $params = []) {
    $params = array_merge(self::$defaultParams, $params);
    $result = civicrm_api3(
      'RelationshipType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

}
