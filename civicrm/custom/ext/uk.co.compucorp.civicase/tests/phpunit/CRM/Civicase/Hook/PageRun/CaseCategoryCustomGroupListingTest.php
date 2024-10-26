<?php

use CRM_Custom_Page_Group as CustomGroupPage;
use CRM_Civicase_Hook_PageRun_CaseCategoryCustomGroupListing as CaseCategoryCustomGroupListing;
use CRM_Civicase_Setup_ProcessCaseCategoryForCustomGroupSupport as CaseCategoryForCustomGroupSupport;

/**
 * Contains tests for the CaseCategoryCustomGroupListing class.
 *
 * @group headless
 */
class CRM_Civicase_Hook_PageRun_CaseCategoryCustomGroupListingTest extends BaseHeadlessTest {

  /**
   * Test the CaseCategoryCustomGroupListing run method.
   */
  public function testRun() {
    $rows = [
      [
        'extends_entity_column_id' => 1,
        'extends' => 'Case',
      ],
    ];
    $page = $this->getCustomGroupPageObject($rows);

    $customGroupListing = new CaseCategoryCustomGroupListing();
    $customGroupListing->run($page);

    // Modify rows to suite expected result.
    $rows[0]['extends_display'] = CaseCategoryForCustomGroupSupport::CASE_CATEGORY_LABEL;
    $this->assertEquals($rows, $page->get_template_vars('rows'));
  }

  /**
   * Returns the custom group page object.
   *
   * @param array $rows
   *   Page rows.
   *
   * @return \CRM_Custom_Page_Group
   *   Page object.
   */
  private function getCustomGroupPageObject(array $rows) {
    $customGroupPage = new CustomGroupPage();
    $customGroupPage->assign('rows', $rows);

    return $customGroupPage;
  }

}
