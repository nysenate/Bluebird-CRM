<?php

use CRM_Civicase_Helper_CaseManagementCustomGroupPostProcess as CaseManagementPostProcessHelper;
use CRM_Civicase_Service_CaseManagementCaseTypePostProcessor as CaseManagementCaseTypePostProcessor;

/**
 * Runs tests on CaseManagementCustomGroupPostProcessor methods.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseManagementCaseTypePostProcessorTest extends BaseHeadlessTest {

  /**
   * Test process case type custom group on case type create.
   *
   * @param int $caseTypeId
   *   Case type Id.
   * @param mixed $entityColumnValues
   *   Custom group entity column values.
   * @param mixed $expectedEntityColumnValues
   *   Expected entity column values for matched custom groups.
   *
   * @dataProvider getDataForTestProcessCaseTypeCustomGroupsOnCreate
   */
  public function testProcessCaseTypeCustomGroupsOnCreate($caseTypeId, $entityColumnValues, $expectedEntityColumnValues) {
    $customGroup = $this->createCustomGroup($entityColumnValues);
    $customGroupId = $customGroup[0]['id'];
    $caseManagementHelper = $this->getCaseManagementHelperMock($customGroup, []);
    $caseManagementProcessor = new CaseManagementCaseTypePostProcessor($caseManagementHelper);
    $caseManagementProcessor->processCaseTypeCustomGroupsOnCreate($caseTypeId);
    $expectedCustomGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $customGroupId]);

    $this->assertEquals($expectedCustomGroup['extends_entity_column_value'], $expectedEntityColumnValues);
  }

  /**
   * Test process case type custom group on case type update.
   *
   * @param int $caseTypeId
   *   Case type Id.
   * @param mixed $entityColumnValues
   *   Custom group entity column values.
   * @param mixed $mismatchEntityColumnValues
   *   Entity column values for mismatched custom groups.
   * @param mixed $expectedEntityColumnValues
   *   Expected entity column values for matched custom groups.
   * @param mixed $correctedMismatchValues
   *   Expected/corrected entity column values for mismatched custom groups.
   *
   * @dataProvider getDataForTestProcessCaseTypeCustomGroupsOnUpdate
   */
  public function testProcessCaseTypeCustomGroupsOnUpdate(
    $caseTypeId,
    $entityColumnValues,
    $mismatchEntityColumnValues,
    $expectedEntityColumnValues,
    $correctedMismatchValues) {
    $customGroup = $this->createCustomGroup($entityColumnValues);
    $mismatchCustomGroup = $this->createCustomGroup($mismatchEntityColumnValues);
    $customGroupId = $customGroup[0]['id'];
    $mismatchCustomGroupId = $mismatchCustomGroup[0]['id'];
    $caseManagementHelper = $this->getCaseManagementHelperMock($customGroup, $mismatchCustomGroup);
    $caseManagementProcessor = new CaseManagementCaseTypePostProcessor($caseManagementHelper);
    $caseManagementProcessor->processCaseTypeCustomGroupsOnUpdate($caseTypeId);
    $expectedCustomGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $customGroupId]);
    $expectedMismatchCustomGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $mismatchCustomGroupId]);

    $this->assertEquals($expectedCustomGroup['extends_entity_column_value'], $expectedEntityColumnValues);
    $this->assertEquals($expectedMismatchCustomGroup['extends_entity_column_value'], $correctedMismatchValues);
  }

  /**
   * Data set for TestProcessCaseTypeCustomGroupsOnCreate.
   *
   * @return array
   *   Data set.
   */
  public function getDataForTestProcessCaseTypeCustomGroupsOnCreate() {
    return [
      [5, [1, 2, 3], [1, 2, 3, 5]],
      [4, [1], [1, 4]],
      [5, NULL, [5]],
    ];
  }

  /**
   * Data set for TestProcessCaseTypeCustomGroupsOnUpdate.
   *
   * @return array
   *   Data set.
   */
  public function getDataForTestProcessCaseTypeCustomGroupsOnUpdate() {
    return [
      [5, [1, 2, 3], [4, 5, 6], [1, 2, 3, 5], [4, 6]],
      [8, [1, 2, 3], [4, 5, 8], [1, 2, 3, 8], [4, 5]],
      [2, NULL, [4, 5, 2], [2], [4, 5]],
    ];
  }

  /**
   * Creates a custom group object and returns value as array.
   *
   * @param array|null $entityColumnValues
   *   Entity custom values for custom group.
   */
  private function createCustomGroup($entityColumnValues) {
    $cusGroup = new CRM_Core_BAO_CustomGroup();
    $cusGroup->title = 'Group' . uniqid();
    $cusGroup->extends = 'Case';
    $entityColValue = is_null($entityColumnValues) ? 'null' : CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $entityColumnValues) . CRM_Core_DAO::VALUE_SEPARATOR;
    $cusGroup->extends_entity_column_value = $entityColValue;
    $cusGroup->save();

    return [
      [
        'id' => $cusGroup->id,
        'extends_entity_column_value' => $entityColumnValues,
      ],
    ];
  }

  /**
   * Returns a mock object for CaseManagementHelper.
   *
   * @param mixed $customGroupReturn
   *   What to return for the getCaseTypeCustomGroups' method.
   * @param mixed $customGroupMismatchReturn
   *   What to return for the
   *   getCaseTypeCustomGroupsWithCategoryMismatch method.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   CaseManagementHelper mock object.
   */
  private function getCaseManagementHelperMock($customGroupReturn, $customGroupMismatchReturn) {
    $caseManagementHelper = $this->getMockBuilder(CaseManagementPostProcessHelper::class)
      ->setMethods(
        [
          'getCaseTypeCustomGroups',
          'getCaseTypeCustomGroupsWithCategoryMismatch',
          'getCaseCategoryForCaseType',
        ]
      )
      ->getMock();
    $caseManagementHelper->method('getCaseTypeCustomGroups')->willReturn($customGroupReturn);
    $caseManagementHelper->method('getCaseCategoryForCaseType')->willReturn(1);
    $caseManagementHelper->method('getCaseTypeCustomGroupsWithCategoryMismatch')->willReturn($customGroupMismatchReturn);

    return $caseManagementHelper;
  }

}
