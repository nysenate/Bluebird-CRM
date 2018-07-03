<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class OptionValueJoinTest extends UnitTestCase {

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

  public function testCommunicationMethodJoin() {
    $query = new Api4SelectQuery('Contact', FALSE);
    $query->select[] = 'first_name';
    $query->select[] = 'preferred_communication_method.label';
    $results = $query->run();
    $first = array_shift($results);
    $firstPreferredMethod = array_shift($first['preferred_communication_method']);

    $this->assertEquals(
      'Phone',
      $firstPreferredMethod['label']
    );
  }

}
