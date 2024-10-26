<?php

/**
 * CaseCategory Fabricator class.
 */
class CRM_Civicase_Test_Fabricator_CaseCategory {

  /**
   * Fabricate a case category.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Results.
   */
  public static function fabricate(array $params = []) {
    $params = self::mergeDefaultParams($params);
    $result = civicrm_api3('OptionValue', 'create', $params);

    return array_shift($result['values']);
  }

  /**
   * Merge to default parameters.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Resulting merged parameters.
   */
  private static function mergeDefaultParams(array $params) {
    $name = 'n' . rand(1000, 9999);
    $defaultParams = [
      'option_group_id' => 'case_type_categories',
      'label' => $name,
      'name' => $name,
      'is_active' => 1,
    ];

    return array_merge($defaultParams, $params);
  }

}
