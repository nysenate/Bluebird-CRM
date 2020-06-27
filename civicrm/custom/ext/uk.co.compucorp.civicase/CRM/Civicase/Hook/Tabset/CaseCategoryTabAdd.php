<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;

/**
 * Class CRM_Civicase_Hook_TabsetCaseCategoryTabAdd.
 */
class CRM_Civicase_Hook_Tabset_CaseCategoryTabAdd {

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

    $this->addCaseCategoryContactTabs($tabs, $context['contact_id'], $useAng);
  }

  /**
   * Add Case Category Contact Tabs.
   *
   * @param array $tabs
   *   Tabs list.
   * @param int $contactID
   *   Contact ID.
   * @param bool $useAng
   *   Whether to use angular.
   */
  private function addCaseCategoryContactTabs(array &$tabs, $contactID, &$useAng) {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'case_type_categories',
      'is_active' => 1,
    ]);

    if (empty($result['values'])) {
      return;
    }

    $permissionService = new CaseCategoryPermission();
    $caseTabWeight = $this->getCaseTabWeight($tabs);
    foreach ($result['values'] as $caseCategory) {
      $caseCategoryPermissions = $permissionService->get($caseCategory['name']);
      $permissionsToCheck = $this->getBasicCaseCategoryPermissions($caseCategoryPermissions);
      if ($caseCategory['name'] == CaseCategoryHelper::CASE_TYPE_CATEGORY_NAME || !CRM_Core_Permission::check($permissionsToCheck)) {
        continue;
      }

      $caseTabWeight++;
      $useAng = TRUE;
      $icon = !empty($caseCategory['icon']) ? "crm-i {$caseCategory['icon']}" : '';
      $tabs[] = [
        'id' => $caseCategory['name'],
        'url' => CRM_Utils_System::url('civicrm/case/contact-case-tab', [
          'cid' => $contactID,
          'case_type_category' => $caseCategory['value'],
        ]),
        'title' => ts($caseCategory['label']),
        'weight' => $caseTabWeight,
        'count' => CaseCategoryHelper::getCaseCount($caseCategory['name'], $contactID),
        'class' => 'livePage',
        'icon' => $icon,
      ];
    }
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

  /**
   * Return the tab weight of the case tab.
   *
   * @param array $tabs
   *   Tabs.
   *
   * @return int
   *   Weight of case tab.
   */
  private function getCaseTabWeight(array $tabs) {
    foreach ($tabs as $key => $tab) {
      if ($tab['id'] === 'case') {
        return $tab['weight'];
      }
    }

    return 0;
  }

  /**
   * The basic Case category permission set.
   *
   * @param array $caseCategoryPermissions
   *   Case category permissions.
   *
   * @return array
   *   The basic permission set.
   */
  private function getBasicCaseCategoryPermissions(array $caseCategoryPermissions) {
    return [
      $caseCategoryPermissions['ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES']['name'],
      $caseCategoryPermissions['ACCESS_CASE_CATEGORY_AND_ACTIVITIES']['name'],
      $caseCategoryPermissions['BASIC_CASE_CATEGORY_INFO']['name'],
    ];
  }

}
