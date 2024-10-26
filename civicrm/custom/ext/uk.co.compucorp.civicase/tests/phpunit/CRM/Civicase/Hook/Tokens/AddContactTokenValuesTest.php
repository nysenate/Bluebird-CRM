<?php

use CRM_Civicase_Hook_Tokens_AddContactTokensValues as AddContactTokenValues;

/**
 * Test class for the CRM_Civicase_Hook_Tokens_AddContactTokensValues.
 *
 * @group headless
 */
class CRM_Civicase_Hook_AddContactTokenValuesTest extends BaseHeadlessTest {

  /**
   * Test the run method.
   *
   * @param array $contactFields
   *   List of contact fields.
   * @param array $customFields
   *   List of contact custom fields.
   * @param array $values
   *   Contact field values.
   * @param array $result
   *   Expected result.
   *
   * @dataProvider getTestDataForRunMethod
   */
  public function testGet(array $contactFields, array $customFields, array $values, array $result) {
    $contactValues = [];
    $customFieldService = $this->getMockBuilder(CRM_Civicase_Service_ContactCustomFieldsProvider::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();
    $customFieldService->method('get')
      ->willReturn($customFields);
    $contactFieldService = $this->getMockBuilder(CRM_Civicase_Service_ContactFieldsProvider::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMock();
    $contactFieldService->method('get')
      ->willReturn($contactFields);
    $service = $this->getMockBuilder(AddContactTokenValues::class)
      ->setMethods(['getContactValues'])
      ->setConstructorArgs([$contactFieldService, $customFieldService])
      ->getMock();
    $service->method('getContactValues')
      ->willReturn($values);

    $service->run($contactValues, [1], 1, [CRM_Civicase_Hook_Tokens_AddContactTokens::TOKEN_KEY => ['contact_id']], '');

    $this->assertEquals($result, $contactValues);
  }

  /**
   * Provides data for run method testing.
   *
   * @return array
   *   List of fields and result for get method.
   */
  public function getTestDataForRunMethod() {
    return [
      [
        [
          'contact_id',
          'contact_type',
          'contact_sub_type',
        ],
        [
          'custom_11' => 'Eligible_for_Gift_Aid',
          'custom_12' => 'Address',
          'custom_13' => 'Post_Code',
        ],
        [
          'contact_id' => 2,
          'contact_type' => 'Individual',
          'contact_sub_type' => '',
          'custom_11' => 'test value',
          'custom_12' => 'test address',
          'custom_13' => 'test value',
        ],
        [
          1 => [
            'current_user.contact_contact_id' => 2,
            'current_user.contact_contact_type' => 'Individual',
            'current_user.contact_contact_sub_type' => '',
            'current_user.contact_custom_11' => 'test value',
            'current_user.contact_custom_12' => 'test address',
            'current_user.contact_custom_13' => 'test value',
          ],
        ],
      ],
      [
        [],
        [],
        [],
        [
          1 => [],
        ],
      ],

    ];
  }

}
