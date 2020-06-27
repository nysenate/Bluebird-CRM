<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Case URL Helper.
 *
 * Returns URLs for case pages.
 */
class CRM_Civicase_Helper_CaseUrl {

  /**
   * Returns the URL for the case details page.
   *
   * @param int $caseId
   *   The ID of the case we want the details URL for.
   *
   * @return string
   *   The case details URL.
   */
  public static function getDetailsPage($caseId) {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $caseId,
      'return' => [
        'case_type_id.name',
        'case_type_id.case_type_category',
        'status_id',
      ],
    ]);
    $caseCategoryName = CaseCategoryHelper::getCaseCategoryNameFromOptionValue(
      $case['case_type_id.case_type_category']
    );
    $caseFilters = [
      'case_type_id' => [$case['case_type_id.name']],
      'status_id' => [$case['status_id']],
    ];
    $caseDetailsUrlPath = 'civicrm/case/a/?case_type_category=' . $caseCategoryName
      . '#/case/list?caseId=' . $caseId
      . '&sf=id&sd=DESC&cf='
      . urlencode(json_encode($caseFilters));

    return CRM_Utils_System::url($caseDetailsUrlPath, NULL, TRUE);
  }

}
