<?php

use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataService;
use CRM_Civicase_Test_Fabricator_CaseCategory as CaseCategoryFabricator;

/**
 * Runs tests on CaseCategoryCustomDataType Service tests.
 *
 * @group headless
 */
class CRM_Civicase_Service_CaseCategoryCustomDataTypeTest extends BaseHeadlessTest {
  /**
   * Instance of CaseCategoryCustomDataType service.
   *
   * @var CRM_Civicase_Service_CaseCategoryCustomDataType
   */
  private $caseCategoryCustomDataService;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->cleanCustomDataTypes();
    $this->caseCategoryCustomDataService = new CaseCategoryCustomDataService();
  }

  /**
   * Test the creation of a new Custom Data Type.
   */
  public function testCreateNewCustomDataType() {
    $this->cleanCustomDataTypes();

    $caseCategory = CaseCategoryFabricator::fabricate();
    $this->assertNull($this->getCustomDataOptionValue($caseCategory['name']));

    $this->caseCategoryCustomDataService->create($caseCategory['name']);
    $optionValueCreated = $this->getCustomDataOptionValue($caseCategory['name']);

    $this->assertNotNull($optionValueCreated);
    $this->assertEquals($caseCategory['name'], $optionValueCreated['name']);
    $this->assertEquals($caseCategory['name'], $optionValueCreated['label']);
    $this->assertEquals($caseCategory['value'], $optionValueCreated['value']);
  }

  /**
   * Try to create twice the same Custom Data Type.
   */
  public function testCreateTwiceSameCustomDataTypeDoesNotCreateDuplicates() {
    $this->cleanCustomDataTypes();

    $caseCategory = CaseCategoryFabricator::fabricate();
    $this->assertNull($this->getCustomDataOptionValue($caseCategory['name']));

    // First call.
    $this->caseCategoryCustomDataService->create($caseCategory['name']);
    // Second call.
    $this->caseCategoryCustomDataService->create($caseCategory['name']);
    $optionValueCreated = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'name' => $caseCategory['name'],
      'option_group_id' => 'custom_data_type',
    ]);

    $this->assertEquals(1, $optionValueCreated['count']);
    $optionValueCreated = array_shift($optionValueCreated['values']);
    $this->assertEquals($caseCategory['name'], $optionValueCreated['name']);
    $this->assertEquals($caseCategory['name'], $optionValueCreated['label']);
    $this->assertEquals($caseCategory['value'], $optionValueCreated['value']);
  }

  /**
   * Test delete an existent Custom Data Type.
   */
  public function testDeleteAnExistentCustomDataType() {
    $this->cleanCustomDataTypes();

    $caseCategory = CaseCategoryFabricator::fabricate();
    $this->caseCategoryCustomDataService->create($caseCategory['name']);
    $this->assertNotNull($this->getCustomDataOptionValue($caseCategory['name']));

    $this->caseCategoryCustomDataService->delete($caseCategory['name']);

    $this->assertNull($this->getCustomDataOptionValue($caseCategory['name']));
  }

  /**
   * Test delete a non existent Custom Data Type.
   */
  public function testDeleteNonExistentCustomDataType() {
    $this->cleanCustomDataTypes();

    $caseCategory = CaseCategoryFabricator::fabricate();
    $this->assertNull($this->getCustomDataOptionValue($caseCategory['name']));

    $this->caseCategoryCustomDataService->delete($caseCategory['name']);

    $this->assertNull($this->getCustomDataOptionValue($caseCategory['name']));
  }

  /**
   * Delete all existent custom data types.
   */
  private function cleanCustomDataTypes() {
    CRM_Core_DAO::executeQuery("
      DELETE v
      FROM civicrm_option_group g, civicrm_option_value v
      WHERE g.id = v.option_group_id AND g.name = 'custom_data_type'
    ");
  }

  /**
   * Return Custom Data type option value.
   *
   * @param string $caseCategoryName
   *   Case Category Name.
   *
   * @return array|null
   *   Custom data type option value.
   */
  private function getCustomDataOptionValue(string $caseCategoryName) {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'name' => $caseCategoryName,
      'option_group_id' => 'custom_data_type',
    ]);

    if (!empty($result['values'])) {
      return array_shift($result['values']);
    }

    return NULL;
  }

}
