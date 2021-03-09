<?php

use CRM_Civicase_Service_CaseTypeCategoryEventHandler as CaseTypeCategoryEventHandler;
use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataType;
use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;

/**
 * CaseTypeCategoryEventHandler Factory.
 *
 * This class helps to create a case category event handler object
 * that can be used to respond to events on a case type category.
 */
class CRM_Civicase_Factory_CaseTypeCategoryEventHandler {

  /**
   * Creates instance of CaseTypeCategoryEventHandler.
   *
   * @return \CRM_Civicase_Service_CaseTypeCategoryEventHandler
   *   CaseTypeCategoryEventHandler instance.
   */
  public static function create() {
    return new CaseTypeCategoryEventHandler(
      new CaseCategoryCustomDataType(),
      new CaseCategoryCustomFieldExtends()
    );
  }

}
