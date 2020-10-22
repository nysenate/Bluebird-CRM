<?php

/**
 * Class CRM_Civicase_Hook_PageRun_CaseCategoryCustomGroupListing.
 */
class CRM_Civicase_Hook_PageRun_CaseCategoryCustomGroupListing {

  /**
   * Add resources (CSS and JS)for this Page.
   *
   * @param object $page
   *   Page Object.
   */
  public function run(&$page) {
    if (!$this->shouldRun($page)) {
      return;
    }

    $this->setCorrectLabelForCaseCategory($page);
  }

  /**
   * Sets the correct label for custom group category on listing page.
   *
   * @param object $page
   *   Page Object.
   */
  private function setCorrectLabelForCaseCategory(&$page) {
    $rows = $page->get_template_vars('rows');
    $caseTypeCategories = (CRM_Case_BAO_CaseType::buildOptions('case_type_category', 'validate'));
    $cgExtendValues = $this->getCgExtendValues();
    foreach ($rows as &$row) {
      if ($row['extends'] == 'Case' && !empty($row['extends_entity_column_id'])) {
        $row['extends_display'] = $cgExtendValues[$caseTypeCategories[$row['extends_entity_column_id']]];
      }
    }

    $page->assign('rows', $rows);
  }

  /**
   * Returns values from cg_extend_object option group.
   *
   * @return array
   *   CG extends values.
   */
  private function getCgExtendValues() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => "cg_extend_objects",
    ]);

    return array_column($result['values'], 'label', 'value');
  }

  /**
   * Determines if the hook will run.
   *
   * @param object $page
   *   Page Object.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($page) {
    return $page instanceof CRM_Custom_Page_Group;
  }

}
