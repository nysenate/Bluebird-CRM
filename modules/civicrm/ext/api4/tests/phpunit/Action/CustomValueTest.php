<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\CustomValue;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class CustomValueTest extends BaseCustomValueTest {

  protected $contactID;

  /**
   * Create dummy Custom group, custom field and contact for testing
   */
  public function createCustomData() {
    $optionValues = ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'];

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->addValue('name', 'MyContactFields')
      ->addValue('extends', 'Contact')
      ->addValue('is_multiple', TRUE)
      ->execute()
      ->first();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'Color')
      ->addValue('options', $optionValues)
      ->addValue('custom_group_id', $customGroup['id'])
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    $customField = CustomField::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('label', '=', 'Color')
      ->execute()
      ->first();

    $this->contactID = Contact::create()
      ->setCheckPermissions(FALSE)
      ->addValue('first_name', 'Johann')
      ->addValue('last_name', 'Tester')
      ->addValue('contact_type', 'Individual')
      ->execute()
      ->first()['id'];
  }

  /**
   * Test CustomValue::getFields
   */
  public function testGetFields() {
    // Create custom group and its field
    $this->createCustomData();

    // Retrieve and check the fields of CustomValue = Custom_MyContactFields
    $fields = CustomValue::getFields('MyContactFields')->execute();
    $expectedResult = [
      [
        'custom_field_id' => 1,
        'custom_group' => 'MyContactFields',
        'table_name' => 'civicrm_value_mycontactfiel_1',
        'column_name' => 'color_1',
        'name' => 'Color',
        'title' => ts('Color'),
        'entity' => 'Custom_MyContactFields',
        'data_type' => 'String',
        'fk_entity' => NULL,
      ],
      [
        'name' => 'id',
        'title' => ts('Custom Table Unique ID'),
        'entity' => 'Custom_MyContactFields',
        'data_type' => 'Integer',
        'fk_entity' => NULL,
      ],
      [
        'name' => 'entity_id',
        'title' => ts('Entity ID'),
        'entity' => 'Custom_MyContactFields',
        'data_type' => 'Integer',
        'fk_entity' => 'Contact',
      ],
    ];

    foreach ($expectedResult as $key => $field) {
      foreach ($field as $attr => $value) {
        $this->assertEquals($expectedResult[$key][$attr], $fields[$key][$attr]);
      }
    }
  }

  /**
   * Test CustomValue::Get/Create/Update/Replace/Delete
   */
  public function testCRUD() {
    $this->createCustomData();

    // CASE 1: Test CustomValue::create
    // Create two records for a single contact and using CustomValue::get ensure that two records are created
    CustomValue::create('MyContactFields')
      ->addValue("Color", 'Green')
      ->addValue("entity_id", $this->contactID)
      ->execute();
    CustomValue::create('MyContactFields')
      ->addValue("Color", 'Red')
      ->addValue("entity_id", $this->contactID)
      ->execute();
    // fetch custom values using API4 CustomValue::get
    $result = CustomValue::get('MyContactFields')->execute();

    // check if two custom values are created
    $this->assertEquals(2, count($result));
    $expectedResult = [
      [
        'id' => 1,
        'Color' => 'Green',
        'entity_id' => $this->contactID,
      ],
      [
        'id' => 2,
        'Color' => 'Red',
        'entity_id' => $this->contactID,
      ],
    ];
    // match the data
    foreach ($expectedResult as $key => $field) {
      foreach ($field as $attr => $value) {
        $this->assertEquals($expectedResult[$key][$attr], $result[$key][$attr]);
      }
    }

    // CASE 2: Test CustomValue::update
    // Update a records whose id is 1 and change the custom field (name = Color) value to 'White' from 'Green'
    CustomValue::update('MyContactFields')
      ->addWhere("id", "=", 1)
      ->addValue("Color", 'White')
      ->execute();

    // ensure that the value is changed for id = 1
    $color = CustomValue::get('MyContactFields')
      ->addWhere("id", "=", 1)
      ->execute()
      ->first()['Color'];
    $this->assertEquals('White', $color);

    // CASE 3: Test CustomValue::replace
    // create a second contact which will be used to replace the custom values, created earlier
    $secondContactID = Contact::create()
      ->setCheckPermissions(FALSE)
      ->addValue('first_name', 'Adam')
      ->addValue('last_name', 'Tester')
      ->addValue('contact_type', 'Individual')
      ->execute()
      ->first()['id'];
    // Replace all the records which was created earlier with entity_id = first contact
    //  with custom record ['Color' => 'Rainbow', 'entity_id' => $secondContactID]
    CustomValue::replace('MyContactFields')
      ->setRecords([['Color' => 'Rainbow', 'entity_id' => $secondContactID]])
      ->addWhere('entity_id', '=', $this->contactID)
      ->execute();

    // Check the two records created earlier is replaced by new contact
    $result = CustomValue::get('MyContactFields')->execute();
    $this->assertEquals(1, count($result));

    $expectedResult = [
      [
        'id' => 3,
        'Color' => 'Rainbow',
        'entity_id' => $secondContactID,
      ],
    ];
    foreach ($expectedResult as $key => $field) {
      foreach ($field as $attr => $value) {
        $this->assertEquals($expectedResult[$key][$attr], $result[$key][$attr]);
      }
    }

    // CASE 4: Test CustomValue::delete
    // There is only record left whose id = 3, delete that record on basis of criteria id = 3
    CustomValue::delete('MyContactFields')->addWhere("id", "=", 3)->execute();
    $result = CustomValue::get('MyContactFields')->execute();
    // check that there are no custom values present
    $this->assertEquals(0, count($result));
  }

}
