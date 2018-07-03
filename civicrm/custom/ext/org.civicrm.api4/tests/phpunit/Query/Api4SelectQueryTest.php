<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class Api4SelectQueryTest extends UnitTestCase {

  public function setUpHeadless() {
    $relatedTables = [
      'civicrm_contact',
      'civicrm_address',
      'civicrm_email',
      'civicrm_phone',
      'civicrm_openid',
      'civicrm_im',
      'civicrm_website',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_activity_contact',
    ];
    $this->cleanup(['tablesToTruncate' => $relatedTables]);
    $this->loadDataSet('DefaultDataSet');
    $displayNameFormat = '{contact.first_name}{ }{contact.last_name}';
    \Civi::settings()->set('display_name_format', $displayNameFormat);

    return parent::setUpHeadless();
  }

  public function testBasicSelect() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $results = $query->run();

    $this->assertCount(2, $results);
    $this->assertEquals('Test', array_shift($results)['first_name']);
  }

  public function testWithSingleWhereJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->where[] = ['phones.phone', '=', $phoneNum];
    $results = $query->run();

    $this->assertCount(1, $results);
  }

  public function testOneToManyJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];

    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'first_name';
    $query->select[] = 'phones.phone';
    $query->where[] = ['phones.phone', '=', $phoneNum];
    $results = $query->run();

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('phones', $firstResult);
    $firstPhone = array_shift($firstResult['phones']);
    $this->assertEquals($phoneNum, $firstPhone['phone']);
  }

  public function testManyToOneJoin() {
    $phoneNum = $this->getReference('test_phone_1')['phone'];
    $contact = $this->getReference('test_contact_1');

    $query = new Api4SelectQuery('Phone', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'phone';
    $query->select[] = 'contact.display_name';
    $query->select[] = 'contact.first_name';
    $query->where[] = ['phone', '=', $phoneNum];
    $results = $query->run();

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('contact', $firstResult);
    $resultContact = $firstResult['contact'];
    $this->assertEquals($contact['display_name'], $resultContact['display_name']);
  }

  public function testOneToManyMultipleJoin() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'first_name';
    $query->select[] = 'phones.phone';
    $results = $query->run();

    $this->assertCount(2, $results);

    foreach ($results as $result) {
      if ($result['id'] == 2) {
        // Contact has no phones
        $this->assertEmpty($result['phones']);
      }
      elseif ($result['id'] == 1) {
        $this->assertCount(2, $result['phones']);
      }
    }
  }

}
