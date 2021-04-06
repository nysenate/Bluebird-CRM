<?php

use CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess as CaseManagementPostProcessHelper;
use CRM_Core_BAO_CustomGroup as CustomGroup;
use CRM_Civicase_Service_CaseManagementCustomGroupPostProcessor as CaseManagementCustomGroupPostProcessor;

/**
 * Runs tests on CaseManagementCustomGroupPostProcessor methods.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseManagementCustomGroupPostProcessorTest extends BaseHeadlessTest {

  /**
   * Test for the SaveCustomGroupForCaseCategory method.
   *
   * @param int $expectedExtendsId
   *   Expected Extends Id.
   * @param string $expectedExtendsValue
   *   Expected extends value.
   * @param mixed $caseTypes
   *   Case types for the cases category.
   *
   * @dataProvider getDataForCaseManagementCustomGroup
   */
  public function testSaveCustomGroupForCaseCategory($expectedExtendsId, $expectedExtendsValue, $caseTypes) {
    $customGroup = $this->getCustomGroupObject();
    $caseManagementHelper = $this->getCaseManagementHelperMock($caseTypes);
    $caseManagementProcessor = new CaseManagementCustomGroupPostProcessor($caseManagementHelper);
    $caseManagementProcessor->saveCustomGroupForCaseCategory($customGroup);
    $this->assertEquals($expectedExtendsId, $customGroup->extends_entity_column_id);
    $this->assertEquals($expectedExtendsValue, $customGroup->extends_entity_column_value, $expectedExtendsValue);
  }

  /**
   * Returns a mock object for CaseManagementHelper.
   *
   * @param mixed $toReturn
   *   What to return for the getCaseTypeIdsForCaseCategory method.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   CaseManagementHelper mock object.
   */
  private function getCaseManagementHelperMock($toReturn) {
    $caseManagementHelper = $this->getMockBuilder(CaseManagementPostProcessHelper::class)
      ->setMethods(['getCaseTypeIdsForCaseCategory'])
      ->getMock();
    $caseManagementHelper->method('getCaseTypeIdsForCaseCategory')->willReturn($toReturn);

    return $caseManagementHelper;
  }

  /**
   * Returns a custom group object.
   *
   * @return CRM_Core_BAO_CustomGroup
   *   Custom group object.
   */
  private function getCustomGroupObject() {
    $customGroup = new CustomGroup();
    $customGroup->extends = 'Cases';
    $customGroup->title = 'Group' . uniqid();

    return $customGroup;
  }

  /**
   * Provides sample data for the SaveCustomGroupForCaseCategory test.
   *
   * @return array
   *   An array of sample data.
   */
  public function getDataForCaseManagementCustomGroup() {
    return [
      [
        // This is the case category value for the case category type.
        1,
        CRM_Core_DAO::VALUE_SEPARATOR .
        implode(CRM_Core_DAO::VALUE_SEPARATOR, [1, 2, 3]) .
        CRM_Core_DAO::VALUE_SEPARATOR,
        [1, 2, 3],
      ],
      [
        1,
        'null',
        NULL,
      ],
      [
        1,
        CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, [4]) . CRM_Core_DAO::VALUE_SEPARATOR,
        [4],
      ],
    ];
  }

}
