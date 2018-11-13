<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\Email;
use Civi\Test\Api4\UnitTestCase;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class ReplaceTest extends UnitTestCase {

  public function testEmailReplace() {
    $cid1 = Contact::create()
      ->addValue('first_name', 'Lotsa')
      ->addValue('last_name', 'Emails')
      ->execute()
      ->first()['id'];
    $cid2 = Contact::create()
      ->addValue('first_name', 'Notso')
      ->addValue('last_name', 'Many')
      ->execute()
      ->first()['id'];
    $e0 = Email::create()
      ->setValues(['contact_id' => $cid2, 'email' => 'nosomany@example.com', 'location_type_id' => 1])
      ->execute()
      ->first()['id'];
    $e1 = Email::create()
      ->setValues(['contact_id' => $cid1, 'email' => 'first@example.com', 'location_type_id' => 1])
      ->execute()
      ->first()['id'];
    $e2 = Email::create()
      ->setValues(['contact_id' => $cid1, 'email' => 'second@example.com', 'location_type_id' => 1])
      ->execute()
      ->first()['id'];
    $replacement = [
      ['email' => 'firstedited@example.com', 'id' => $e1],
      ['contact_id' => $cid1, 'email' => 'third@example.com', 'location_type_id' => 1]
    ];
    $replaced = Email::replace()
      ->setRecords($replacement)
      ->addWhere('contact_id', '=', $cid1)
      ->execute();
    // Should have saved 2 records
    $this->assertEquals(2, $replaced->count());
    // Should have deleted email2
    $this->assertEquals([$e2], $replaced->deleted);
    // Verify contact now has the new email records
    $results = Email::get()
      ->addWhere('contact_id', '=', $cid1)
      ->execute()
      ->indexBy('id');
    $this->assertEquals('firstedited@example.com', $results[$e1]['email']);
    $this->assertEquals(2, $results->count());
    $this->assertArrayNotHasKey($e2, (array) $results);
    $this->assertArrayNotHasKey($e0, (array) $results);
    unset($results[$e1]);
    foreach ($results as $result) {
      $this->assertEquals('third@example.com', $result['email']);
    }
    // Validate our other contact's email did not get deleted
    $c2email = Email::get()
      ->addWhere('contact_id', '=', $cid2)
      ->execute()
      ->first();
    $this->assertEquals('nosomany@example.com', $c2email['email']);
  }

}
