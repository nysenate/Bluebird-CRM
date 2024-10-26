<?php

/**
 * Class CRM_Civicase_Hook_PageRun_AddCaseAngularPageResources.
 */
class CRM_Civicase_Hook_PageRun_AddCaseAngularPageResources {

  /**
   * Add resources (CSS and JS) for this Page.
   *
   * @param object $page
   *   Page Object.
   */
  public function run(&$page) {
    if (!$this->shouldRun($page)) {
      return;
    }

    $this->addResources();
  }

  /**
   * Add resources (CSS and JS) for this Page.
   */
  private function addResources() {
    // Adds Moment.js file.
    CRM_Core_Resources::singleton()
      ->addScriptFile('uk.co.compucorp.civicase', 'packages/moment.min.js');
    // Adds simplescrollbarjs.
    CRM_Core_Resources::singleton()
      ->addScriptFile('uk.co.compucorp.civicase', 'packages/simplebar.min.js');
    CRM_Core_Resources::singleton()
      ->addStyleFile('uk.co.compucorp.civicase', 'packages/simplebar.min.css', 1000, 'html-header');
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
    return $page instanceof CRM_Civicase_Page_CaseAngular;
  }

}
