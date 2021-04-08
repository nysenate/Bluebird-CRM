<?php

use CRM_Core_BAO_CustomGroup as CustomGroup;
use CRM_Civicase_Hook_Post_CaseCategoryCustomGroupSaver as CaseCategoryCustomGroupSaver;

/**
 * Test class for CaseCategoryCustomGroupSaverTest.
 *
 * @group headless
 */
class CRM_Civicase_Hook_Post_CaseCategoryCustomGroupSaverTest extends BaseHeadlessTest {

  /**
   * Test the run method.
   *
   * The purpose of this test is to ensure that the customGroupPostProcessor
   * instance class is being triggered for only when the object name is
   * `CustomGroup` for the create and edit operations.
   *
   * @param string $objectName
   *   Object name.
   * @param string $op
   *   Operation being performed (create|edit).
   * @param mixed $expectedExtendsId
   *   Custom group extends Id.
   *
   * @dataProvider getDataForRun
   */
  public function testRun($objectName, $op, $expectedExtendsId) {
    $objectId = 1;
    $objectRef = $this->getCustomGroupObject();
    $caseCategoryCustomGroupSaver = new CaseCategoryCustomGroupSaver();
    $caseCategoryCustomGroupSaver->run($op, $objectName, $objectId, $objectRef);
    $this->assertEquals($expectedExtendsId, $objectRef->extends_entity_column_id);
  }

  /**
   * Provides sample data for testRun test.
   *
   * @return array
   *   An array of sample data.
   */
  public function getDataForRun() {
    return [
      [
        'CustomGroup',
        'create',
        // This is the case category value for the case category type.
        1,
      ],
      [
        'CustomGroup',
        'edit',
        // This is the case category value for the case category type.
        1,
      ],
      [
        'CaseType',
        'edit',
        '',
      ],
      [
        'CaseType',
        'create',
        '',
      ],
    ];
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

}
