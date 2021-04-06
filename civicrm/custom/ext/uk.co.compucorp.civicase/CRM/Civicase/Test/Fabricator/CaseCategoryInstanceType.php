<?php

/**
 * CaseCategoryInstanceType Fabricator class.
 */
class CRM_Civicase_Test_Fabricator_CaseCategoryInstanceType {

  /**
   * Fabricate a case category instance type.
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
    $randomIdentifier = rand();
    $defaultParams = [
      'name' => 'test_category_instance_type_' . $randomIdentifier,
      'label' => 'Test Category Instance Type ' . $randomIdentifier,
      'grouping' => 'CRM_Civicase_Service_CaseManagementUtils',
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ];

    $mergedParams = array_merge($defaultParams, $params);
    $mergedParams['option_group_id'] = 'case_category_instance_type';

    return $mergedParams;
  }

}
