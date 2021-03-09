<?php

use CRM_Civicase_Factory_CaseTypeCategoryEventHandler as CaseTypeCategoryEventHandlerFactory;
use CRM_Civicase_Test_Fabricator_CaseCategory as CaseCategoryFabricator;
use CRM_Civicase_Service_CaseManagementUtils as CaseManagementUtils;

/**
 * Test class for the CaseTypeCategoryEventHandler.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseTypeCategoryEventHandlerTest extends BaseHeadlessTest {

  /**
   * Test the on create event when a case category is created.
   */
  public function testOnCreateEventForCaseCategory() {
    $caseCategoryEventHandler = $this->getEventHandlerObject();
    $caseTypeCategory = CaseCategoryFabricator::fabricate();
    $caseCategoryInstance = $this->getCaseCategoryInstance();
    $caseCategoryEventHandler->onCreate($caseCategoryInstance, $caseTypeCategory['name']);
    $caseCategoryMenu = $this->getCaseCategoryParentMenu($caseTypeCategory['name']);

    // Verify that the case category main menu is created and not duplicated.
    $this->assertCount(1, $caseCategoryMenu);

    // Verify that the Custom Group Entity Is added for the Case category.
    $customGroupEntity = $this->getCustomGroupEntity($caseTypeCategory['name']);
    $this->assertCount(1, $customGroupEntity);
  }

  /**
   * Test the update events when a case category is updated.
   */
  public function testOnUpdateEventForCaseCategory() {
    $caseCategoryEventHandler = $this->getEventHandlerObject();
    $caseTypeCategory = CaseCategoryFabricator::fabricate();
    $caseCategoryInstance = $this->getCaseCategoryInstance();
    $caseCategoryEventHandler->onCreate($caseCategoryInstance, $caseTypeCategory['name']);

    // Update the case type category.
    $caseTypeCategory = CaseCategoryFabricator::fabricate(
      [
        'id' => $caseTypeCategory['id'],
        'is_active' => 0,
        'icon' => 'fa-folder-open-o',
      ]
    );
    $caseCategoryEventHandler->onUpdate($caseCategoryInstance, $caseTypeCategory['id'], $caseTypeCategory['is_active'], $caseTypeCategory['icon']);

    $caseCategoryMenu = $this->getCaseCategoryParentMenu($caseTypeCategory['name']);

    // Verify that the case category main menu is disabled because the
    // case category is inactive.
    $this->assertCount(1, $caseCategoryMenu);
    $this->assertEquals(0, $caseCategoryMenu[0]['is_active']);
    $this->assertEquals('crm-i ' . $caseTypeCategory['icon'], $caseCategoryMenu[0]['icon']);
  }

  /**
   * Test the delete events when a case category is deleted.
   */
  public function testOnDeleteEventForCaseCategory() {
    $caseCategoryEventHandler = $this->getEventHandlerObject();
    $caseTypeCategory = CaseCategoryFabricator::fabricate();
    $caseCategoryInstance = $this->getCaseCategoryInstance();
    $caseCategoryEventHandler->onCreate($caseCategoryInstance, $caseTypeCategory['name']);
    // On delete event.
    $caseCategoryEventHandler->onDelete($caseCategoryInstance, $caseTypeCategory['name']);

    $caseCategoryMenu = $this->getCaseCategoryParentMenu($caseTypeCategory['name']);
    // Verify that the case category main menu is deleted because the
    // case category is inactive.
    $this->assertCount(0, $caseCategoryMenu);
    // Verify that the Custom Group Entity Is deleted for the Case category.
    $customGroupEntity = $this->getCustomGroupEntity($caseTypeCategory['name']);
    $this->assertCount(0, $customGroupEntity);
  }

  /**
   * Returns Event Handler Object.
   *
   * @return object
   *   CaseTypeCategoryEventHandler.
   */
  private function getEventHandlerObject() {
    return CaseTypeCategoryEventHandlerFactory::create();
  }

  /**
   * Returns the Case Category Instance.
   *
   * @return \CRM_Civicase_Service_CaseManagementUtils
   *   Case Instance class.
   */
  private function getCaseCategoryInstance() {
    return new CaseManagementUtils();
  }

  /**
   * Returns the Case category parent menu.
   *
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return array
   *   Case category parent menu details
   */
  private function getCaseCategoryParentMenu($caseCategoryName) {
    $result = civicrm_api3('Navigation', 'get', [
      'name' => $caseCategoryName,
      'sequential' => 1,
    ]);

    return $result['values'];
  }

  /**
   * Returns the Custom group entity for the case category.
   *
   * @param string $caseCategoryName
   *   Case category name.
   *
   * @return array
   *   Custom group entity.
   */
  private function getCustomGroupEntity($caseCategoryName) {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cg_extend_objects',
      'name' => 'civicrm_case',
      'label' => "Case ({$caseCategoryName})",
      'sequential' => 1,
    ]);

    return $result['values'];
  }

}
