<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;
use CRM_Case_BAO_CaseType as CaseType;
use CRM_Civicase_Service_CaseCategoryFromUrl as CaseCategoryFromUrl;

/**
 * Class CRM_Civicase_Hook_PermissionCheck_CaseCategory.
 */
class CRM_Civicase_Hook_PermissionCheck_CaseCategory {

  /**
   * Case category permission service.
   *
   * @var CRM_Civicase_Service_CaseCategoryPermission
   */
  private $caseCategoryPermission;

  /**
   * Modify permission check based on case category.
   *
   * @param string $permission
   *   Permission name.
   * @param bool $granted
   *   Whether permission is granted or not.
   * @param int|null $contactId
   *   The contact ID to check permission for.
   */
  public function run($permission, &$granted, $contactId) {
    $this->caseCategoryPermission = new CaseCategoryPermission();

    if (!$this->shouldRun($permission, $granted, $contactId)) {
      return;
    }

    $url = CRM_Utils_System::currentPath();
    $caseCategoryFromUrl = new CaseCategoryFromUrl();
    $isAjaxRequest = $url == 'civicrm/ajax/rest';
    // We need to exclude this permission for this page because the permission
    // will return true as the logic for equivalent case category permission
    // will be applied.
    $isAdvancedSearchPage = $url == 'civicrm/contact/search/advanced' && $permission != 'basic case information';
    $caseCategoryName = $caseCategoryFromUrl->get($url);

    if ($caseCategoryName) {
      $this->modifyPermissionCheckForCategory($permission, $granted, $caseCategoryName);
    }
    elseif (($isAjaxRequest && !$caseCategoryFromUrl->getIsCaseEntity()) || $isAdvancedSearchPage) {
      $this->checkForEquivalentCaseCategoryPermission($permission, $granted);
    }
  }

  /**
   * Checks for Equivalent Case Category Permission.
   *
   * This function checks that the user has at least any of the equivalent
   * civicase permissions.
   * Useful for pages like advanced search and the ajax request page where the
   * case category is not passed but we need to restrict or grant some access
   * based on case type categories.
   *
   * @param string $permission
   *   Permission String.
   * @param bool $granted
   *   Whether permission is granted or not.
   */
  private function checkForEquivalentCaseCategoryPermission($permission, &$granted) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    foreach ($caseTypeCategories as $caseTypeCategory) {
      if ($caseTypeCategory == CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME) {
        continue;
      }
      $caseCategoryPermission = $this->caseCategoryPermission->replaceWords($permission, $caseTypeCategory);
      if (!CRM_Core_Permission::check($caseCategoryPermission)) {
        $granted = FALSE;
      }
      else {
        $granted = TRUE;
        break;
      }
    }
  }

  /**
   * Modify permission check based on case category.
   *
   * @param string $permission
   *   Permission name.
   * @param bool $granted
   *   Whether permission is granted or not.
   * @param string $caseCategoryName
   *   Case category name.
   */
  private function modifyPermissionCheckForCategory($permission, &$granted, $caseCategoryName) {
    if ($caseCategoryName && strtolower($caseCategoryName) == strtolower(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME)) {
      return;
    }

    $caseCategoryPermission = $this->caseCategoryPermission->replaceWords($permission, $caseCategoryName);

    if (!CRM_Core_Permission::check($caseCategoryPermission)) {
      $granted = FALSE;
    }
    else {
      $granted = TRUE;
    }
  }

  /**
   * Determines if the hook will run.
   *
   * @param string $permission
   *   Permission name.
   * @param bool $granted
   *   Whether permission is granted or not.
   * @param int|null $contactId
   *   The contact ID to check permission for.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($permission, $granted, $contactId) {
    $defaultCasePermissions = array_column($this->caseCategoryPermission->get(), 'name');

    return in_array($permission, $defaultCasePermissions) && !$granted && !$contactId;
  }

}
