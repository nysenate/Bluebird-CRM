<?php

use CRM_Civicase_Service_ContactCustomFieldsProvider as ContactCustomFieldsProvider;

/**
 * Test class for the CRM_Civicase_Service_ContactCustomFieldsProvider.
 *
 * @group headless
 */
class CRM_Civicase_Service_ContactCustomFieldsProviderTest extends BaseHeadlessTest {

  /**
   * Test the get method.
   *
   * @param array $fields
   *   List of fields.
   * @param array $result
   *   Expected result.
   *
   * @dataProvider getTestDataForGetMethod
   */
  public function testGet(array $fields, array $result) {
    $service = $this->getMockBuilder(ContactCustomFieldsProvider::class)
      ->setMethods(['getCustomFields'])
      ->disableOriginalConstructor()
      ->getMock();
    $service->method('getCustomFields')
      ->willReturn($fields);

    $this->assertEquals($result, $service->get());
  }

  /**
   * Provides data for get method testing.
   *
   * @return array
   *   List of fields and result for get method.
   */
  public function getTestDataForGetMethod() {
    return [
      [
        [],
        [],
      ],
      [
        [
          'values' => [
            11 => [
              'name' => 'First test field',
            ],
            12 => [
              'name' => 'Second test field',
            ],
          ],
        ],
        [
          'custom_11' => 'First test field',
          'custom_12' => 'Second test field',
        ],
      ],
    ];
  }

}
