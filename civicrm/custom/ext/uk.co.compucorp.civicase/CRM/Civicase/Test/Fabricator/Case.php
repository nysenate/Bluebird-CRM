<?php

/**
 * Fabricates cases.
 */
class CRM_Civicase_Test_Fabricator_Case {

  private static $defaultParams = array(
    'subject' => 'test test',
  );

  public static function fabricate($params = array()) {
    if (empty($params['contact_id'])) {
      throw new Exception('Please specify contact_id value');
    }

    if (empty($params['creator_id'])) {
      throw new Exception('Please specify creator_id value');
    }

    if (empty($params['case_type_id'])) {
      throw new Exception('Please specify case_type_id value');
    }

    $params = array_merge(self::$defaultParams, $params);
    $result = civicrm_api3('Case', 'create', $params);

    return array_shift($result['values']);
  }

}
