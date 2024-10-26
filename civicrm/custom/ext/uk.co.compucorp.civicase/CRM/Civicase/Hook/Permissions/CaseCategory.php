<?php

use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;
use CRM_Case_BAO_CaseType as CaseType;
use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Class CRM_Civicase_Hook_Permissions_CaseCategory.
 */
class CRM_Civicase_Hook_Permissions_CaseCategory {

  /**
   * Civi permissions array.
   *
   * @var array
   */
  private $permissions;

  /**
   * Case Category Permission service.
   *
   * @var \CRM_Civicase_Service_CaseCategoryPermission
   */
  private $permissionService;

  /**
   * CRM_Civicase_Hook_Permissions_CaseCategory constructor.
   *
   * @param array $permissions
   */
  public function __construct(array &$permissions) {
    $this->permissions = &$permissions;
    $this->permissionService = new CaseCategoryPermission();
  }

  /**
   * Run function.
   */
  public function run() {
    $this->addPermissions();
  }

  /**
   * Adds permissions to the Civi permission array.
   */
  private function addPermissions() {
    $this->addCivicaseDefaultPermissions();
    $this->addCaseCategoryPermissions();
  }

  /**
   * Adds the default permissions proviced by Civicase.
   */
  private function addCivicaseDefaultPermissions() {
    $caseCategoryPermissions = $this->permissionService->get();
    $this->permissions[$caseCategoryPermissions['BASIC_CASE_CATEGORY_INFO']['name']] = [
      $caseCategoryPermissions['BASIC_CASE_CATEGORY_INFO']['label'],
      ts($caseCategoryPermissions['BASIC_CASE_CATEGORY_INFO']['description']),
    ];
  }

  /**
   * Adds permissions provided by Case categories excluding Civicase.
   */
  private function addCaseCategoryPermissions() {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    if (empty($caseTypeCategories)) {
      return;
    }
    foreach ($caseTypeCategories as $caseTypeCategory) {
      if ($caseTypeCategory == CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME) {
        continue;
      }

      $caseCategoryPermissions = $this->permissionService->get($caseTypeCategory);
      foreach ($caseCategoryPermissions as $caseCategoryPermission) {
        $this->permissions[$caseCategoryPermission['name']] = [
          $caseCategoryPermission['label'],
          ts($caseCategoryPermission['description']),
        ];
      }
    }
  }

}
