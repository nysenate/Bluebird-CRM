<?php

use CRM_Civicase_Service_CaseCategoryInstance as CaseCategoryInstanceService;
use CRM_Civicase_Test_Fabricator_CaseCategoryInstance as CaseCategoryInstanceFabricator;
use CRM_Civicase_Test_Fabricator_CaseCategory as CaseCategoryFabricator;

/**
 * Runs tests on CaseCategoryInstance Service tests.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseCategoryInstanceTest extends BaseHeadlessTest {

  /**
   * Test getCaseCategoryInstances without parameters.
   *
   * Verify that all category instances are returned, and the result is
   * formatted as expected.
   */
  public function testGetCategoryInstances() {
    // Clean current category instances for doing an appropriate count later.
    $this->cleanCategoryInstances();
    // And fabricate two random.
    $categoryInstanceOne = CaseCategoryInstanceFabricator::fabricate();
    $categoryInstanceTwo = CaseCategoryInstanceFabricator::fabricate();

    // We call the method for getting the list.
    $categoryInstances = (new CaseCategoryInstanceService())->getCaseCategoryInstances();

    // And we verify the structure and content of the list received.
    $this->assertCount(2, $categoryInstances);
    $this->assertArrayHasKey($categoryInstanceOne['id'], $categoryInstances);
    $this->assertArrayHasKey($categoryInstanceTwo['id'], $categoryInstances);
    $this->assertEquals(
      $categoryInstanceOne['category_id'],
      $categoryInstances[$categoryInstanceOne['id']]->category_id
    );
    $this->assertEquals(
      $categoryInstanceOne['instance_id'],
      $categoryInstances[$categoryInstanceOne['id']]->instance_id
    );
    $this->assertEquals(
      $categoryInstanceTwo['category_id'],
      $categoryInstances[$categoryInstanceTwo['id']]->category_id
    );
    $this->assertEquals(
      $categoryInstanceTwo['instance_id'],
      $categoryInstances[$categoryInstanceTwo['id']]->instance_id
    );
  }

  /**
   * Test getCaseCategoryInstances with parameters.
   *
   * Verify that category instances are correctly filtered by instance type
   * name, and the result is formatted as expected.
   */
  public function testGetCategoryInstancesByInstanceName() {
    // Clean current category instances for doing an appropriate count later.
    $this->cleanCategoryInstances();
    // We generate two different category instance types.
    $categoryInstanceTypeOne = $this->createCategoryInstanceType();
    $categoryInstanceTypeTwo = $this->createCategoryInstanceType();
    // And two random category instances associated with them.
    $categoryInstanceOne = CaseCategoryInstanceFabricator::fabricate([
      'instance_id' => $categoryInstanceTypeOne['value'],
    ]);
    $categoryInstanceTwo = CaseCategoryInstanceFabricator::fabricate([
      'instance_id' => $categoryInstanceTypeTwo['value'],
    ]);

    // We call the method with categoryInstanceTypeOne name as a parameter.
    $categoryInstances = (new CaseCategoryInstanceService())->getCaseCategoryInstances(
      $categoryInstanceTypeOne['name']
    );

    // We verify that only category one is on the results.
    $this->assertCount(1, $categoryInstances);
    $this->assertArrayHasKey($categoryInstanceOne['id'], $categoryInstances);
    $this->assertArrayNotHasKey($categoryInstanceTwo['id'], $categoryInstances);
    $this->assertEquals(
      $categoryInstanceOne['category_id'],
      $categoryInstances[$categoryInstanceOne['id']]->category_id
    );
    $this->assertEquals(
      $categoryInstanceOne['instance_id'],
      $categoryInstances[$categoryInstanceOne['id']]->instance_id
    );
  }

  /**
   * Test the correct creation of an instance for an existing category.
   */
  public function testAssignInstanceForExistingCaseCategoriesWithoutAnInstance() {
    // Generate a random category.
    $categoryWithoutInstance = CaseCategoryFabricator::fabricate();
    // And a category instance, not related with the previous.
    CaseCategoryInstanceFabricator::fabricate();
    $categoryInstanceService = new CaseCategoryInstanceService();

    // We assert that this category has no instance.
    $categoryInstances = $categoryInstanceService->getCaseCategoryInstances();
    $this->assertFalse($this->categoryHasInstance($categoryWithoutInstance, $categoryInstances));

    // We call the method for creating the corresponding instance.
    $categoryInstanceService->assignInstanceForExistingCaseCategories();

    // And we check that was correctly created.
    $categoryInstances = $categoryInstanceService->getCaseCategoryInstances();
    $this->assertTrue($this->categoryHasInstance($categoryWithoutInstance, $categoryInstances));
  }

  /**
   * Check if the given category has an instance on the array received.
   */
  private function categoryHasInstance($caseCategory, $caseCategoryInstances) {
    foreach ($caseCategoryInstances as $caseCategoryInstance) {
      if ($caseCategoryInstance->category_id == $caseCategory['value']) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Generates a random category instance type.
   *
   * @return array
   *   Category instance type details.
   */
  private function createCategoryInstanceType() {
    $randomIdentifier = rand();
    $params = [
      'option_group_id' => 'case_category_instance_type',
      'name' => 'test_category_instance_type_' . $randomIdentifier,
      'label' => 'Test Category Instance Type ' . $randomIdentifier,
      'grouping' => 'CRM_Civicase_Service_CaseManagementUtils',
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ];

    $result = civicrm_api3('OptionValue', 'create', $params);

    return array_shift($result['values']);
  }

  /**
   * Delete all existent category instances.
   */
  private function cleanCategoryInstances() {
    CRM_Core_DAO::executeQuery('DELETE FROM civicrm_case_category_instance');
  }

}
