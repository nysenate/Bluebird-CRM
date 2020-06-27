<?php

use CRM_Case_BAO_CaseType as CaseType;
use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;

/**
 * CRM_Civicase_Helper_CaseCategory class.
 */
class CRM_Civicase_Helper_CaseCategory {

  const CASE_TYPE_CATEGORY_NAME = 'Cases';

  /**
   * Returns the case category name for the case Id.
   *
   * @param int $caseId
   *   The Case ID.
   *
   * @return string|null
   *   The Case Category Name.
   */
  public static function getCategoryName($caseId) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    try {
      $result = civicrm_api3('Case', 'getsingle', [
        'id' => $caseId,
        'return' => ['case_type_id.case_type_category'],
      ]);

      if (!empty($result['case_type_id.case_type_category'])) {
        $caseCategoryId = $result['case_type_id.case_type_category'];

        return $caseTypeCategories[$caseCategoryId];
      }
    }
    catch (Exception $e) {
      return NULL;
    }

    return NULL;
  }

  /**
   * Returns the case category name for the case typeId.
   *
   * @param int $caseTypeId
   *   The Case Type ID.
   *
   * @return string|null
   *   The Case Category Name.
   */
  public static function getCategoryNameForCaseType($caseTypeId) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category');
    try {
      $result = civicrm_api3('CaseType', 'getvalue', [
        'id' => $caseTypeId,
        'return' => ['case_type_category'],
      ]);

      if (!empty($result['is_error'])) {
        $caseCategoryId = $result['result'];

        return $caseTypeCategories[$caseCategoryId];
      }
    }
    catch (Exception $e) {
      return NULL;
    }

    return NULL;
  }

  /**
   * Returns the case type category name given the option value.
   *
   * @param mixed $caseCategoryValue
   *   Category Option value.
   *
   * @return string
   *   Case Category Name.
   */
  public static function getCaseCategoryNameFromOptionValue($caseCategoryValue) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');

    return $caseTypeCategories[$caseCategoryValue];
  }

  /**
   * Returns the case types for the cases category.
   *
   * @return array
   *   Array of Case Types indexed by Id.
   */
  public static function getCaseTypesForCase() {
    $result = civicrm_api3('CaseType', 'get', [
      'sequential' => 1,
      'return' => ['title', 'id'],
      'case_type_category' => self::CASE_TYPE_CATEGORY_NAME,
    ]);

    return array_column($result['values'], 'title', 'id');
  }

  /**
   * Returns the case type category word replacements.
   *
   * @param string $caseTypeCategoryName
   *   Case Category Name.
   *
   * @return array
   *   The word to be replaced and replacement array.
   */
  public static function getWordReplacements($caseTypeCategoryName) {
    $optionName = $caseTypeCategoryName . "_word_replacement";
    try {
      $result = civicrm_api3('OptionValue', 'getsingle', [
        'option_group_id' => 'case_type_category_word_replacement_class',
        'name' => $optionName,
      ]);

    }
    catch (Exception $e) {
      if (!$caseTypeCategoryName || strtolower($caseTypeCategoryName) == 'cases') {
        return [];
      }

      return [
        'Case' => ucfirst($caseTypeCategoryName),
        'Cases' => ucfirst($caseTypeCategoryName) . 's',
        'case' => strtolower($caseTypeCategoryName),
        'cases' => strtolower($caseTypeCategoryName) . 's',
      ];
    }

    if (empty($result['id'])) {
      return [];
    }

    $replacementClass = $result['value'];
    if (class_exists($replacementClass) && isset(class_implements($replacementClass)[CRM_Civicase_WordReplacement_BaseInterface::class])) {
      $replacements = new $replacementClass();

      return $replacements->get();
    }

    return [];
  }

  /**
   * Return case count for contact for a case category.
   *
   * @param string $caseTypeCategoryName
   *   Case category name.
   * @param int $contactId
   *   Contact ID.
   *
   * @return int
   *   Case count.
   */
  public static function getCaseCount($caseTypeCategoryName, $contactId) {
    $params = [
      'is_deleted' => 0,
      'contact_id' => $contactId,
      'case_type_id.case_type_category' => $caseTypeCategoryName,
    ];
    try {
      return civicrm_api3('Case', 'getcount', $params);
    }
    catch (CiviCRM_API3_Exception $e) {
      // Lack of permissions will throw an exception.
      return 0;
    }
  }

  /**
   * Returns the actual case category name stored in case type category.
   *
   * The case type category parameter passed in the URL may have been changed
   * by user e.g might have been changed to upper case or all lower case while
   * the actual value stored in db might be different. This might cause issues
   * because Core uses some function to check if the case category value
   * (especially for custom field) extends match what is stored in some option
   * values array, if these don't match, it might cause an issue in the
   * application behaviour.
   *
   * @param string $caseCategoryNameFromUrl
   *   Case category name passed from URL.
   *
   * @return string
   *   Actual case category name.
   */
  public static function getActualCaseCategoryName($caseCategoryNameFromUrl) {
    $caseCategoryNameFromUrl = strtolower($caseCategoryNameFromUrl);
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    $caseTypeCategoriesCombined = array_combine($caseTypeCategories, $caseTypeCategories);
    $caseTypeCategoriesLower = array_change_key_case($caseTypeCategoriesCombined);

    return !empty($caseTypeCategoriesLower[$caseCategoryNameFromUrl]) ? $caseTypeCategoriesLower[$caseCategoryNameFromUrl] : NULL;

  }

  /**
   * Returns the Case Type Categories that the user has access to.
   *
   * If the Contact Id is not passed, the logged in contact ID is used.
   *
   * @param int|null $contactId
   *   The contact Id to check for.
   *
   * @return array
   *   Case category access.
   */
  public static function getAccessibleCaseTypeCategories($contactId = NULL) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    $caseCategoryPermission = new CaseCategoryPermission();
    $permissionToCheck = 'basic case information';
    $caseCategoryAccess = [];
    $contactId = $contactId ? $contactId : CRM_Core_Session::getLoggedInContactID();

    foreach ($caseTypeCategories as $id => $caseTypeCategoryName) {
      $permission = $caseCategoryPermission->replaceWords($permissionToCheck, $caseTypeCategoryName);
      if (CRM_Core_Permission::check($permission, $contactId)) {
        $caseCategoryAccess[$id] = $caseTypeCategoryName;
      }
    }

    return $caseCategoryAccess;
  }

  /**
   * Returns the Case Type Categories the user can access the activities for.
   *
   * If the Contact Id is not passed, the logged in contact ID is used.
   *
   * @param int|null $contactId
   *   The contact Id to check for.
   *
   * @return array
   *   Case category access.
   */
  public static function getWhereUserCanAccessActivities($contactId = NULL) {
    $caseTypeCategories = CaseType::buildOptions('case_type_category', 'validate');
    $caseCategoryPermission = new CaseCategoryPermission();
    $permissionsToCheck = ['access my cases and activities', 'access all cases and activities'];
    $caseCategoryAccess = [];
    $contactId = $contactId ? $contactId : CRM_Core_Session::getLoggedInContactID();
    foreach ($caseTypeCategories as $id => $caseTypeCategoryName) {
      foreach ($permissionsToCheck as $permissionToCheck) {
        $permission = $caseCategoryPermission->replaceWords($permissionToCheck, $caseTypeCategoryName);

        if (CRM_Core_Permission::check($permission, $contactId)) {
          array_push($caseCategoryAccess, $caseTypeCategoryName);

          continue 2;
        }
      }
    }

    return $caseCategoryAccess;
  }

  /**
   * Updates breadcrumb for a case category page.
   *
   * The breadcrumb is updated so that the necessary words can be replaced and
   * also so that the dashboard leads to the case category dashboard page
   * depending on the case category.
   *
   * @param string $caseCategoryName
   *   Case category name.
   */
  public static function updateBreadcrumbs($caseCategoryName) {
    CRM_Utils_System::resetBreadCrumb();
    $breadcrumb = [
      [
        'title' => ts('Home'),
        'url' => CRM_Utils_System::url(),
      ],
      [
        'title' => ts('CiviCRM'),
        'url' => CRM_Utils_System::url('civicrm', 'reset=1'),
      ],
      [
        'title' => ts('Case Dashboard'),
        'url' => CRM_Utils_System::url('civicrm/case/a/', ['case_type_category' => $caseCategoryName], TRUE,
          "/case?case_type_category={$caseCategoryName}"),
      ],
    ];

    CRM_Utils_System::appendBreadCrumb($breadcrumb);
  }

}
