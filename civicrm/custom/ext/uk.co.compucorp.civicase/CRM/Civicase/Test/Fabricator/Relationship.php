<?php

/**
 * Relationship Fabricator class.
 */
class CRM_Civicase_Test_Fabricator_Relationship {

  /**
   * Default parameters.
   *
   * @var array
   */
  private static $defaultParams = [
    'is_active' => 1,
    'start_date' => NULL,
    'end_date' => NULL,
  ];

  /**
   * Fabricate a relationship.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   API results.
   */
  public static function fabricate(array $params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3('Relationship', 'create', $params);

    return array_shift($result['values']);
  }

}
