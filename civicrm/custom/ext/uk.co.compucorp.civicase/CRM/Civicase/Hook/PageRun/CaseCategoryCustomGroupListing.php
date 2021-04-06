<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Handles the custom group listing page display for case categories.
 */
class CRM_Civicase_Hook_PageRun_CaseCategoryCustomGroupListing {

  /**
   * Stores display formatter objects.
   *
   * @var CRM_Civicase_Service_BaseCustomGroupDisplayFormatter[]
   */
  private $displayFormatters;

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

    $this->processDisplay($page);
  }

  /**
   * Processes the custom group page display based on instance formatter.
   *
   * @param object $page
   *   Page Object.
   */
  private function processDisplay(&$page) {
    $rows = $page->get_template_vars('rows');
    foreach ($rows as &$row) {
      if ($row['extends'] == 'Case' && !empty($row['extends_entity_column_id'])) {
        $displayFormatter = $this->getInstanceDisplayFormatter($row['extends_entity_column_id']);
        $displayFormatter->processDisplay($row);
      }
    }

    $page->assign('rows', $rows);
  }

  /**
   * Returns the display formatter object for the category instance.
   *
   * @param int $caseCategoryValue
   *   Case category Value.
   *
   * @return CRM_Civicase_Service_BaseCustomGroupDisplayFormatter
   *   Category instance display formatter.
   */
  private function getInstanceDisplayFormatter($caseCategoryValue) {
    if (empty($this->displayFormatters[$caseCategoryValue])) {
      $caseCategoryInstance = CaseCategoryHelper::getInstanceObject($caseCategoryValue);
      $displayFormatter = $caseCategoryInstance->getCustomGroupDisplayFormatter();
      $this->displayFormatters[$caseCategoryValue] = $displayFormatter;
    }

    return $this->displayFormatters[$caseCategoryValue];
  }

  /**
   * Determines if the hook will run.
   *
   * Runs when listing the custom field groups. Since the create and update
   * form use the same page we check that the action URL parameter is not
   * defined so we avoid running the hook for those forms.
   *
   * @param object $page
   *   Page Object.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($page) {
    $action = (CRM_Utils_Request::retrieve('action', 'String'));

    return $page instanceof CRM_Custom_Page_Group && (empty($action) || (!empty($action) && $action == CRM_Core_Action::BROWSE));
  }

}
