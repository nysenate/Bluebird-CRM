<?php

/**
 * Overrides some properties in CaseCategoryCustomFieldExtends.
 */
class CRM_Civicase_Service_CaseCategoryCaseTypeCustomFieldExtends extends CRM_Civicase_Service_CaseCategoryCustomFieldExtends {

  /**
   * {@inheritdoc}
   */
  protected $entityTable = 'civicrm_case_type';

  /**
   * {@inheritdoc}
   */
  protected function getCustomEntityValue($entityValue) {
    return "{$entityValue}Type";
  }

}
