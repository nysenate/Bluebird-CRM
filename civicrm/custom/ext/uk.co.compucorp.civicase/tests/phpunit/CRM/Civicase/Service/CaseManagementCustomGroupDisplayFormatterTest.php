<?php

use CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess as CaseManagementPostProcessHelper;
use CRM_Civicase_Service_CaseManagementCustomGroupDisplayFormatter as CaseManagementCustomGroupDisplayFormatter;
use CRM_Civicase_Setup_ProcessCaseCategoryForCustomGroupSupport as CaseCategoryForCustomGroupSupport;

/**
 * Runs tests for CaseManagementCustomGroupDisplayFormatterTest.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseManagementCustomGroupDisplayFormatterTest extends BaseHeadlessTest {

  /**
   * Test process display function.
   */
  public function testProcessDisplay() {
    $caseCategories = [1 => 'Cases'];
    $expectedDisplay = CaseCategoryForCustomGroupSupport::CASE_CATEGORY_LABEL;
    $cgExtends = ['Cases' => $expectedDisplay];
    $caseManagementHelper = $this->getCaseManagementHelperMock($caseCategories, $cgExtends);
    $row['extends_entity_column_id'] = 1;
    $displayFormatter = new CaseManagementCustomGroupDisplayFormatter($caseManagementHelper);
    $displayFormatter->processDisplay($row);
    $this->assertEquals($expectedDisplay, $row['extends_display']);
  }

  /**
   * Returns a mock object for CaseManagementHelper.
   *
   * @param mixed $caseCategoryReturn
   *   What to return for the `getCaseTypeCategories` method.
   * @param mixed $cgExtendReturn
   *   What to return for the `getCgExtendValues` method.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   CaseManagementHelper mock object.
   */
  private function getCaseManagementHelperMock($caseCategoryReturn, $cgExtendReturn) {
    $caseManagementHelper = $this->getMockBuilder(CaseManagementPostProcessHelper::class)
      ->setMethods(
        [
          'getCaseTypeCategories',
          'getCgExtendValues',
        ]
      )
      ->getMock();
    $caseManagementHelper->method('getCaseTypeCategories')->willReturn($caseCategoryReturn);
    $caseManagementHelper->method('getCgExtendValues')->willReturn($cgExtendReturn);

    return $caseManagementHelper;
  }

}
