<?php

use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtendsService;

/**
 * Runs tests on CaseCategoryCustomFieldExtends Service tests.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseCategoryCustomFieldExtendsTest extends BaseHeadlessTest {

  /**
   * Test CG extend option value creation using all the parameters.
   */
  public function testIsSuccessfullyCreatedWithAllParameters() {
    $entityValue = 'Test Entity Value';
    $label = 'Test Label';
    $entityTypeFunction = 'Test Entity Type Function';

    (new CaseCategoryCustomFieldExtendsService())->create($entityValue, $label, $entityTypeFunction);

    $cgExtendOptionValue = $this->getCgExtendOptionValue($entityValue);
    $this->assertEquals(1, $cgExtendOptionValue['count']);
    $this->assertEquals($cgExtendOptionValue['values'][0]['value'], $entityValue);
    $this->assertEquals($cgExtendOptionValue['values'][0]['label'], $label);
    $this->assertEquals($cgExtendOptionValue['values'][0]['description'], $entityTypeFunction);
  }

  /**
   * Test CG extend option value creation using only the required parameters.
   */
  public function testIsSuccessfullyCreatedWithOnlyRequiredParameters() {
    $entityValue = 'Test Entity Value';
    $label = 'Test Label';

    (new CaseCategoryCustomFieldExtendsService())->create($entityValue, $label);

    $cgExtendOptionValue = $this->getCgExtendOptionValue($entityValue);
    $this->assertEquals(1, $cgExtendOptionValue['count']);
    $this->assertEquals($cgExtendOptionValue['values'][0]['value'], $entityValue);
    $this->assertEquals($cgExtendOptionValue['values'][0]['label'], $label);
  }

  /**
   * Test that calling twice the create method does not produce an error.
   */
  public function testIsNotCreatedTwice() {
    $entityValue = 'Test Entity Value';
    $label = 'Test Label';
    $entityTypeFunction = 'Test Entity Type Function';
    $cgCustomFieldExtendsService = new CaseCategoryCustomFieldExtendsService();

    // First call.
    $cgCustomFieldExtendsService->create($entityValue, $label, $entityTypeFunction);
    // Second call.
    $cgCustomFieldExtendsService->create($entityValue, $label, $entityTypeFunction);

    $cgExtendOptionValue = $this->getCgExtendOptionValue($entityValue);
    $this->assertEquals(1, $cgExtendOptionValue['count']);
  }

  /**
   * Test deleting an existing CG extend option value.
   */
  public function testIsSuccessfullyDeleted() {
    // Create a random CG extend option value.
    $cgExtendOptionValue = $this->createCgExtendOptionValue();
    // We verify that is correctly stored.
    $this->assertEquals(1, $this->getCgExtendOptionValue($cgExtendOptionValue['value'])['count']);

    // Delete the CG extend option value.
    (new CaseCategoryCustomFieldExtendsService())->delete($cgExtendOptionValue['value']);

    // And verify that is not counted anymore.
    $cgExtendOptionValue = $this->getCgExtendOptionValue($cgExtendOptionValue['value']);
    $this->assertEquals(0, $cgExtendOptionValue['count']);
  }

  /**
   * Test that calling twice the delete method does not produce an error.
   */
  public function testDeleteCanBeCalledTwice() {
    // Create a random CG extend option value.
    $cgExtendOptionValue = $this->createCgExtendOptionValue();
    // We verify that is correctly stored.
    $this->assertEquals(1, $this->getCgExtendOptionValue($cgExtendOptionValue['value'])['count']);

    $cgCustomFieldExtendsService = new CaseCategoryCustomFieldExtendsService();
    // First call to delete method.
    $cgCustomFieldExtendsService->delete($cgExtendOptionValue['value']);
    // Second call to delete method.
    $cgCustomFieldExtendsService->delete($cgExtendOptionValue['value']);

    // And verify is not counted anymore.
    $cgExtendOptionValue = $this->getCgExtendOptionValue($cgExtendOptionValue['value']);
    $this->assertEquals(0, $cgExtendOptionValue['count']);
  }

  /**
   * Returns the CG extend option value details.
   *
   * @param string $value
   *   Value of the CG extend option.
   *
   * @return array
   *   CG extend option value details, with count information
   */
  private function getCgExtendOptionValue($value) {
    return civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'value' => $value,
      'option_group_id' => 'cg_extend_objects',
    ]);
  }

  /**
   * Creates a random CG extend option value.
   *
   * @return array
   *   CG extend option value details
   */
  private function createCgExtendOptionValue() {
    $randNumber = rand(0, 1000);
    $params = [
      'option_group_id' => 'cg_extend_objects',
      'name' => 'civicrm_case',
      'label' => 'Test Label ' . $randNumber,
      'value' => 'Test Value ' . $randNumber,
      'description' => 'Test Description ' . $randNumber,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ];

    $result = civicrm_api3('OptionValue', 'create', $params);

    return array_shift($result['values']);
  }

}
