<?php

/**
 * Class CRM_Civicase_Hook_PageRun_ViewCasePageRedirect.
 */
class CRM_Civicase_Hook_PageRun_ViewCasePageRedirect {

  /**
   * Redirects the core view case page to an angular page provided by civicase.
   *
   * @param object $page
   *   Page Object.
   */
  public function run(&$page) {
    $caseId = CRM_Utils_Request::retrieve('id', 'Positive');

    if (!$this->shouldRun($page, $caseId)) {
      return;
    }

    $this->redirectViewCasePage($caseId);
  }

  /**
   * Redirects the core view case page to an angular page provided by civicase.
   *
   * OLD: http://localhost/civicrm/contact/view/case?reset=1&action=view&cid=129&id=51
   * NEW: http://localhost/civicrm/case/a/?case_type_category=case_category_name#/case/list?
   * sf=contact_id.sort_name&sd=ASC&focus=0&cf=%7B%7D&caseId=51&tab=summary&sx=0
   *
   * We also inherit the *tab* parameter from the current URL and pass it to the
   * case details URL.
   *
   * @param int $caseId
   *   Case Id.
   */
  private function redirectViewCasePage($caseId) {
    $relevantUrlParams = [['name' => 'tab', 'type' => 'String']];
    $caseDetailsUrl = CRM_Civicase_Helper_CaseUrl::getDetailsPage($caseId);
    $this->addRelevantUrlParamsToFragment($caseDetailsUrl, $relevantUrlParams);

    CRM_Utils_System::redirect($caseDetailsUrl);
  }

  /**
   * Parameters from the URL that we are intrested in appending to the fragment.
   *
   * @param string $fragment
   *   Fragment.
   * @param array $relevantUrlParams
   *   Url parameters.
   */
  private function addRelevantUrlParamsToFragment(&$fragment, array $relevantUrlParams) {
    foreach ($relevantUrlParams as $relevantUrlParam) {
      $value = CRM_Utils_Request::retrieve($relevantUrlParam['name'], $relevantUrlParam['type']);
      if ($value) {
        $fragment .= "&{$relevantUrlParam['name']}={$value}";
      }
    }
  }

  /**
   * Determines if the hook will run.
   *
   * @param object $page
   *   Page Object.
   * @param int $caseId
   *   Case Id.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($page, $caseId) {
    return $page instanceof CRM_Case_Page_Tab && $caseId;
  }

}
