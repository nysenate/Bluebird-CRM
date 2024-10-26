<?php

use CRM_Civicase_Test_Fabricator_CaseCategory as CaseCategoryFabricator;

/**
 * CaseCategoryInstance Fabricator class.
 */
class CRM_Civicase_Test_Fabricator_CaseCategoryInstance {

  /**
   * Fabricate a case category instance.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Results.
   */
  public static function fabricate(array $params = []) {
    $params = self::mergeDefaultParams($params);
    $result = civicrm_api3('CaseCategoryInstance', 'create', $params);

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
    $defaultParams = [];
    if (!isset($params['category_id'])) {
      $defaultParams['category_id'] = CaseCategoryFabricator::fabricate()['value'];
    }
    if (!isset($params['instance_id'])) {
      $defaultParams['instance_id'] = array_rand(
        CRM_Core_OptionGroup::values('case_category_instance_type')
      );
    }

    return array_merge($defaultParams, $params);
  }

}
