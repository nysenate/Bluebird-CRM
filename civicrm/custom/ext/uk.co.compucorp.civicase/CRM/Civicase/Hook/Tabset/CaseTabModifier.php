<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Class CRM_Civicase_Hook_Tabset_CaseTabModifier.
 */
class CRM_Civicase_Hook_Tabset_CaseTabModifier {

  /**
   * Determines what happens if the hook is handled.
   *
   * @param string $tabsetName
   *   Tabset name.
   * @param array $tabs
   *   Tabs list.
   * @param array $context
   *   Context.
   * @param bool $useAng
   *   Whether to use angular.
   */
  public function run($tabsetName, array &$tabs, array $context, &$useAng) {
    if (!$this->shouldRun($tabsetName)) {
      return;
    }

    $caseTabPresent = FALSE;
    foreach ($tabs as $key => &$tab) {
      if ($tab['id'] === 'case') {
        $caseTabPresent = TRUE;
        $useAng = TRUE;
        $tab['url'] = $this->getCaseTabUrl($context['contact_id']);
        $tab['count'] = CaseCategoryHelper::getCaseCount(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME, $context['contact_id']);
      }
    }

    if (!$caseTabPresent && CRM_Core_Permission::check('basic case information')) {
      $useAng = TRUE;
      $tabs[] = [
        'id' => 'case',
        'url' => $this->getCaseTabUrl($context['contact_id']),
        'title' => ts('Cases'),
        'weight' => 20,
        'count' => CaseCategoryHelper::getCaseCount(CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME, $context['contact_id']),
        'class' => 'livePage',
      ];
    }
  }

  /**
   * Returns the URL for case contact tab.
   *
   * @param int $contactId
   *   Contact ID.
   *
   * @return string
   *   The URL.
   */
  private function getCaseTabUrl($contactId) {
    $caseCategoryOptions = array_flip(CRM_Case_BAO_CaseType::buildOptions('case_type_category', 'validate'));
    return CRM_Utils_System::url('civicrm/case/contact-case-tab', [
      'cid' => $contactId,
      'case_type_category' => $caseCategoryOptions[CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME],
    ]);
  }

  /**
   * Checks if the hook should run.
   *
   * @param string $tabsetName
   *   Tabset name.
   *
   * @return bool
   *   Return value.
   */
  private function shouldRun($tabsetName) {
    return $tabsetName === 'civicrm/contact/view';
  }

}
