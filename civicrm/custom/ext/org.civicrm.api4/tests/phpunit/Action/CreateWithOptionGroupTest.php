<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class CreateWithOptionGroupTest extends BaseCustomValueTest {

  /**
   * Remove the custom tables
   */
  public function setUp() {
    $this->dropByPrefix('civicrm_value_financial');
    $this->dropByPrefix('civicrm_value_favorite');
    parent::setUp();
  }

  public function testGetWithCustomData() {
    $customGroupId = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->addValue('name', 'FavoriteThings')
      ->addValue('extends', 'Contact')
      ->execute()
      ->first()['id'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'FavColor')
      ->addValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'FavFood')
      ->addValue('options', ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    $customGroupId = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->addValue('name', 'FinancialStuff')
      ->addValue('extends', 'Contact')
      ->execute()
      ->first()['id'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'Salary')
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Number')
      ->addValue('data_type', 'Money')
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->addValue('first_name', 'Jerome')
      ->addValue('last_name', 'Tester')
      ->addValue('contact_type', 'Individual')
      ->addValue('FavoriteThings.FavColor', 'r')
      ->addValue('FavoriteThings.FavFood', '1')
      ->addValue('FinancialStuff.Salary', 50000)
      ->execute();

    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('first_name')
      ->addSelect('FavoriteThings.FavColor.label')
      ->addSelect('FavoriteThings.FavFood.label')
      ->addSelect('FinancialStuff.Salary')
      ->addWhere('FavoriteThings.FavFood.label', 'IN', ['Corn', 'Potatoes'])
      ->addWhere('FinancialStuff.Salary', '>', '10000')
      ->execute()
      ->first();

    $this->assertArrayHasKey('FavoriteThings', $result);
    $favoriteThings = $result['FavoriteThings'];
    $favoriteFood = $favoriteThings['FavFood'];
    $favoriteColor = $favoriteThings['FavColor'];
    $financialStuff = $result['FinancialStuff'];
    $this->assertEquals('Red', $favoriteColor['label']);
    $this->assertEquals('Corn', $favoriteFood['label']);
    $this->assertEquals(50000, $financialStuff['Salary']);
  }

  public function testWithCustomDataForMultipleContacts() {
    $customGroupId = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->addValue('name', 'FavoriteThings')
      ->addValue('extends', 'Contact')
      ->execute()
      ->first()['id'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'FavColor')
      ->addValue('options', ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'FavFood')
      ->addValue('options', ['1' => 'Corn', '2' => 'Potatoes', '3' => 'Cheese'])
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Select')
      ->addValue('data_type', 'String')
      ->execute();

    $customGroupId = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->addValue('name', 'FinancialStuff')
      ->addValue('extends', 'Contact')
      ->execute()
      ->first()['id'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->addValue('label', 'Salary')
      ->addValue('custom_group_id', $customGroupId)
      ->addValue('html_type', 'Number')
      ->addValue('data_type', 'Money')
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->addValue('first_name', 'Red')
      ->addValue('last_name', 'Corn')
      ->addValue('contact_type', 'Individual')
      ->addValue('FavoriteThings.FavColor', 'r')
      ->addValue('FavoriteThings.FavFood', '1')
      ->addValue('FinancialStuff.Salary', 10000)
      ->execute();

    Contact::create()
      ->setCheckPermissions(FALSE)
      ->addValue('first_name', 'Blue')
      ->addValue('last_name', 'Cheese')
      ->addValue('contact_type', 'Individual')
      ->addValue('FavoriteThings.FavColor', 'b')
      ->addValue('FavoriteThings.FavFood', '3')
      ->addValue('FinancialStuff.Salary', 500000)
      ->execute();

    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('first_name')
      ->addSelect('last_name')
      ->addSelect('FavoriteThings.FavColor.label')
      ->addSelect('FavoriteThings.FavFood.label')
      ->addSelect('FinancialStuff.Salary')
      ->addWhere('FavoriteThings.FavFood.label', 'IN', ['Corn', 'Cheese'])
      ->execute();

    $blueCheese = NULL;
    foreach ($result as $contact) {
      if ($contact['first_name'] === 'Blue') {
        $blueCheese = $contact;
      }
    }

    $this->assertEquals('Blue', $blueCheese['FavoriteThings']['FavColor']['label']);
    $this->assertEquals('Cheese', $blueCheese['FavoriteThings']['FavFood']['label']);
    $this->assertEquals(500000, $blueCheese['FinancialStuff']['Salary']);
  }

}
