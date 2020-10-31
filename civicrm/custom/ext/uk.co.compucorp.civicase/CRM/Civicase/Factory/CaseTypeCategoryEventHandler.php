<?php

use CRM_Civicase_Service_CaseTypeCategoryEventHandler as CaseTypeCategoryEventHandler;
use CRM_Civicase_Service_CaseCategoryMenu as CaseCategoryMenu;
use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataType;
use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;

/**
 * Class CRM_Civicase_Factory_CaseTypeCategoryEventHandler.
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
      new CaseCategoryMenu(),
      new CaseCategoryCustomDataType(),
      new CaseCategoryCustomFieldExtends()
    );
  }

}
