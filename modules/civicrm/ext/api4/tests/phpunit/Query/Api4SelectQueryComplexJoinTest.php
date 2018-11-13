<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class Api4SelectQueryComplexJoinTest extends UnitTestCase {

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
    $this->loadDataSet('SingleContact');
    return parent::setUpHeadless();
  }

  public function testWithComplexRelatedEntitySelect() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'display_name';
    $query->select[] = 'phones.phone';
    $query->select[] = 'emails.email';
    $query->select[] = 'emails.location_type.name';
    $query->select[] = 'created_activities.contact_id';
    $query->select[] = 'created_activities.activity.subject';
    $query->select[] = 'created_activities.activity.activity_type.name';
    $query->where[] = ['first_name', '=', 'Single'];
    $results = $query->run();

    $testActivities = [
      $this->getReference('test_activity_1'),
      $this->getReference('test_activity_2'),
    ];
    $activitySubjects = array_column($testActivities, 'subject');

    $this->assertCount(1, $results);
    $firstResult = array_shift($results);
    $this->assertArrayHasKey('created_activities', $firstResult);
    $firstCreatedActivity = array_shift($firstResult['created_activities']);
    $this->assertArrayHasKey('activity', $firstCreatedActivity);
    $firstActivity = $firstCreatedActivity['activity'];
    $this->assertContains($firstActivity['subject'], $activitySubjects);
    $this->assertArrayHasKey('activity_type', $firstActivity);
    $activityType = $firstActivity['activity_type'];
    $this->assertArrayHasKey('name', $activityType);
  }

  public function testWithSelectOfOrphanDeepValues() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'first_name';
    $query->select[] = 'emails.location_type.name'; // emails not selected
    $results = $query->run();
    $firstResult = array_shift($results);

    $this->assertEmpty($firstResult['emails']);
  }

  public function testOrderDoesNotMatter() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'id';
    $query->select[] = 'first_name';
    $query->select[] = 'emails.location_type.name'; // before emails selection
    $query->select[] = 'emails.email';
    $results = $query->run();
    $firstResult = array_shift($results);

    $this->assertNotEmpty($firstResult['emails'][0]['location_type']['name']);
  }

}
