<?php

/**
 * CaseCategoryInstance BAO.
 */
class CRM_Civicase_BAO_CaseCategoryInstance extends CRM_Civicase_DAO_CaseCategoryInstance {

  /**
   * Create a new CaseCategoryInstance based on array-data.
   *
   * @param array $params
   *   Key-value pairs.
   *
   * @return CRM_Civicase_DAO_CaseCategoryInstance
   *   Case category instance.
   */
  public static function create(array $params) {
    $entityName = 'CaseCategoryInstance';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

}
