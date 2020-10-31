<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Class CRM_Civicase_Service_CaseCategoryPermission.
 */
class CRM_Civicase_Service_CaseCategoryPermission {

  /**
   * Returns the permission set for a Civcase extension.
   *
   * This permission array is the original set of permissions defined in
   * the Case Core extension. Each Civicase extension variant will use
   * this same set of permissions but with proper word replacements
   * depending on the Case category.
   *
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return array
   *   Array of Permissions.
   */
  public function get($caseCategoryName = CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME) {
    $caseCategoryName = $this->getCaseCategoryName($caseCategoryName);

    return [
      'DELETE_IN_CASE_CATEGORY' => [
        'name' => $this->replaceWords('delete in CiviCase', $caseCategoryName),
        'label' => $this->replaceWords('CiviCase: delete in CiviCase', $caseCategoryName),
        'description' => $this->replaceWords('Delete cases', $caseCategoryName),
      ],
      'ADD_CASE_CATEGORY' => [
        'name' => $this->replaceWords('add cases', $caseCategoryName),
        'label' => $this->replaceWords('CiviCase: add cases', $caseCategoryName),
        'description' => $this->replaceWords('Open a new case', $caseCategoryName),
      ],
      'ACCESS_CASE_CATEGORY_AND_ACTIVITIES' => [
        'name' => $this->replaceWords('access all cases and activities', $caseCategoryName),
        'label' => $this->replaceWords('CiviCase: access all cases and activities', $caseCategoryName),
        'description' => $this->replaceWords('View and edit all cases (for visible contacts)', $caseCategoryName),
      ],
      'ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES' => [
        'name' => $this->replaceWords('access my cases and activities', $caseCategoryName),
        'label' => $this->replaceWords('CiviCase: access my cases and activities', $caseCategoryName),
        'description' => $this->replaceWords('View and edit only those cases managed by this user', $caseCategoryName),
      ],
      'BASIC_CASE_CATEGORY_INFO' => [
        'name' => $this->replaceWords('basic case information', $caseCategoryName),
        'label' => $this->replaceWords('CiviCase: basic case information', $caseCategoryName),
        'description' => $this->replaceWords('Allows a user to view only basic information of cases.', $caseCategoryName),
      ],
    ];
  }

  /**
   * Returns the case category name.
   *
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return string
   *   Case category name.
   */
  private function getCaseCategoryName($caseCategoryName) {
    $caseCategoryName = $caseCategoryName ? $caseCategoryName : CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME;

    return strtolower($caseCategoryName);
  }

  /**
   * Returns the appropriate permission after proper word replacements.
   *
   * The word replacements is based on the value of the case category name.
   * It returns the equivalent case category permission based on the original
   * case permission and the category name.
   *
   * @param string $casePermission
   *   String for word replacements.
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return string
   *   Word replaced string.
   */
  public function replaceWords($casePermission, $caseCategoryName) {
    $caseCategoryName = $this->getCaseCategoryName($caseCategoryName);
    if ($caseCategoryName == strtolower(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME)) {
      return $casePermission;
    }

    return str_replace(
      ['CiviCase', 'cases', 'case'],
      [
        "Civi" . ucfirst($caseCategoryName),
        $caseCategoryName,
        $caseCategoryName,
      ],
      $casePermission
    );
  }

  /**
   * The basic civicase permission set.
   *
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return array
   *   The basic permission set.
   */
  public function getBasicCasePermissions($caseCategoryName) {
    $caseCategoryPermissions = $this->get($caseCategoryName);

    return [
      $caseCategoryPermissions['ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES']['name'],
      $caseCategoryPermissions['ACCESS_CASE_CATEGORY_AND_ACTIVITIES']['name'],
      $caseCategoryPermissions['BASIC_CASE_CATEGORY_INFO']['name'],
    ];
  }

}
