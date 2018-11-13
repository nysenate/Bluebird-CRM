<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;
use Civi\Api4\Activity;
use Civi\Api4\Contact;

/**
 * @group headless
 */
class FkJoinTest extends UnitTestCase {

  public function setUpHeadless() {
    $relatedTables = [
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_phone',
      'civicrm_activity_contact',
    ];
    $this->cleanup(['tablesToTruncate' => $relatedTables]);
    $this->loadDataSet('DefaultDataSet');

    return parent::setUpHeadless();
  }

  /**
   * Fetch all activities for housing support cases. Expects a single activity
   * loaded from the data set.
   */
  public function testThreeLevelJoin() {
    $results = Activity::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('activity_type.name', '=', 'housing_support')
      ->execute();

    $this->assertCount(1, $results);
  }

  public function testActivityContactJoin() {
    $results = Activity::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('assignees.id')
      ->addSelect('assignees.first_name')
      ->addSelect('assignees.display_name')
      ->addWhere('assignees.first_name', '=', 'Test')
      ->execute();

    $firstResult = $results->first();

    $this->assertCount(1, $results);
    $this->assertTrue(is_array($firstResult['assignees']));

    $firstAssignee = array_shift($firstResult['assignees']);
    $this->assertEquals($firstAssignee['first_name'], 'Test');
  }

  public function testContactPhonesJoin() {
    $testContact = $this->getReference('test_contact_1');
    $testPhone = $this->getReference('test_phone_1');

    $results = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('phones.phone')
      ->addWhere('id', '=', $testContact['id'])
      ->addWhere('phones.location_type.name', '=', 'Home')
      ->execute()
      ->first();

    $this->assertArrayHasKey('phones', $results);
    $this->assertCount(1, $results['phones']);
    $firstPhone = array_shift($results['phones']);
    $this->assertEquals($testPhone['phone'], $firstPhone['phone']);
  }

}
