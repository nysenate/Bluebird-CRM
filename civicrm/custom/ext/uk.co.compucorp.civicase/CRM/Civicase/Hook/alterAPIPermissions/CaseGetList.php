<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;

/**
 * Class CRM_Civicase_Hook_alterAPIPermissions_CaseGetList.
 */
class CRM_Civicase_Hook_alterAPIPermissions_CaseGetList {

  /**
   * Alters the API permissions.
   *
   * @param string $entity
   *   The API entity.
   * @param string $action
   *   The API action.
   * @param array $params
   *   The API parameters.
   * @param array $permissions
   *   The API permissions.
   */
  public function run($entity, $action, array &$params, array &$permissions) {
    if (!$this->shouldRun($entity, $action)) {
      return;
    }

    $accessibleCaseCategories = CaseCategoryHelper::getAccessibleCaseTypeCategories();
    if (empty($accessibleCaseCategories)) {
      return;
    }

    $caseCategory = array_shift($accessibleCaseCategories);
    $this->addApiPermission($caseCategory, $permissions);
  }

  /**
   * Adds the permission required to access the case.get list endpoint.
   *
   * @param string $caseCategoryName
   *   Case category name.
   * @param array $permissions
   *   Permissions.
   */
  private function addApiPermission($caseCategoryName, array &$permissions) {
    $permissionService = new CaseCategoryPermission();
    $basicCasePermissions = $permissionService->getBasicCasePermissions($caseCategoryName);
    $permissions['case']['getlist'] = [$basicCasePermissions];
    $permissions['case_contact']['get'] = [$basicCasePermissions];
  }

  /**
   * Alters the permissions for Case.getlist and CaseContact.get endpoints.
   *
   * @param string $entity
   *   The API entity.
   * @param string $action
   *   The API action.
   *
   * @return bool
   *   If the hook should run.
   */
  private function shouldRun($entity, $action) {
    return ($entity == 'case' && $action == 'getlist') || ($entity == 'case_contact' && $action == 'get');
  }

}
