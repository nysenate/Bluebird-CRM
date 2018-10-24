<?php

namespace Civi\Test\Api4\Spec;

use Civi\Api4\Service\Spec\CustomFieldSpec;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;
use Civi\Api4\Service\Spec\SpecFormatter;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class SpecFormatterTest extends UnitTestCase {

  public function testSpecToArray() {
    $spec = new RequestSpec('Contact', 'get');
    $fieldName = 'last_name';
    $field = new FieldSpec($fieldName, 'Contact');
    $spec->addFieldSpec($field);
    $arraySpec = SpecFormatter::specToArray($spec->getFields());

    $this->assertEquals('String', $arraySpec[$fieldName]['data_type']);
  }

  /**
   * @dataProvider arrayFieldSpecProvider
   *
   * @param array $fieldData
   * @param string $expectedName
   * @param string $expectedType
   */
  public function testArrayToField($fieldData, $expectedName, $expectedType) {
    $field = SpecFormatter::arrayToField($fieldData, 'TestEntity');

    $this->assertEquals($expectedName, $field->getName());
    $this->assertEquals($expectedType, $field->getDataType());
  }

  public function testCustomFieldWillBeReturned() {
    $customGroupId = 1432;
    $customFieldId = 3333;
    $name = 'MyFancyField';

    $data = [
      'custom_group_id' => $customGroupId,
      'custom_group' => ['name' => 'my_group'],
      'id' => $customFieldId,
      'name' => $name,
      'data_type' => 'String',
      'html_type' => 'MultiSelect',
    ];

    /** @var CustomFieldSpec $field */
    $field = SpecFormatter::arrayToField($data, 'TestEntity');

    $this->assertInstanceOf(CustomFieldSpec::class, $field);
    $this->assertEquals('my_group', $field->getCustomGroupName());
    $this->assertEquals($customFieldId, $field->getCustomFieldId());
    $this->assertEquals(\CRM_Core_DAO::SERIALIZE_SEPARATOR_BOOKEND, $field->getSerialize());
  }

  /**
   * @return array
   */
  public function arrayFieldSpecProvider() {
    return [
      [
        [
          'name' => 'Foo',
          'title' => 'Bar',
          'type' => \CRM_Utils_Type::T_STRING
        ],
        'Foo',
        'String'
      ],
      [
        [
          'name' => 'MyField',
          'title' => 'Bar',
          'type' => \CRM_Utils_Type::T_STRING,
          'data_type' => 'Boolean' // this should take precedence
        ],
        'MyField',
        'Boolean'
      ],
    ];
  }

}
