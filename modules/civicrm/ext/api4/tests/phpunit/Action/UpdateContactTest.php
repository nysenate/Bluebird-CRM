<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Contact;
use Civi\Test\Api4\UnitTestCase;

/**
 * Class UpdateContactTest
 * @package Civi\Test\Api4\Action
 * @group headless
 */
class UpdateContactTest extends UnitTestCase {

  public function testUpdateWillWork() {
    $contactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->addValue('first_name', 'Johann')
      ->addValue('last_name', 'Tester')
      ->addValue('contact_type', 'Individual')
      ->execute()
      ->first()['id'];

    $contact = Contact::update()
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $contactId)
      ->addValue('first_name', 'Testy')
      ->execute()
      ->first();
    $this->assertEquals('Testy', $contact['first_name']);
    $this->assertEquals('Tester', $contact['last_name']);
  }

}
