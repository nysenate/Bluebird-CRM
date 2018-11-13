<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Contact;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;

/**
 * @group headless
 */
class BasicCustomFieldTest extends BaseCustomValueTest {

  public function testWithSingleField() {

    $customGroup = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->addValue('name', 'MyContactFields')
      ->addValue('extends', 'Contact')
      ->execute()
      ->first();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'FavColor')
      ->addValue('custom_group_id', $customGroup['id'])
      ->addValue('html_type', 'Text')
      ->addValue('data_type', 'String')
      ->execute();

    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->addValue('first_name', 'Johann')
      ->addValue('last_name', 'Tester')
      ->addValue('contact_type', 'Individual')
      ->addValue('MyContactFields.FavColor', 'Red')
      ->execute()
      ->first()['id'];

    $contact = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('first_name')
      ->addSelect('MyContactFields.FavColor')
      ->addWhere('id', '=', $contactId)
      ->addWhere('MyContactFields.FavColor', '=', 'Red')
      ->execute()
      ->first();

    $this->assertArrayHasKey('MyContactFields', $contact);
    $contactFields = $contact['MyContactFields'];
    $this->assertArrayHasKey('FavColor', $contactFields);
    $this->assertEquals('Red', $contactFields['FavColor']);

    Contact::update()
      ->addWhere('id', '=', $contactId)
      ->addValue('MyContactFields.FavColor', 'Blue')
      ->execute();

    $contact = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('MyContactFields.FavColor')
      ->addWhere('id', '=', $contactId)
      ->execute()
      ->first();

    $contactFields = $contact['MyContactFields'];
    $this->assertEquals('Blue', $contactFields['FavColor']);
  }

}
