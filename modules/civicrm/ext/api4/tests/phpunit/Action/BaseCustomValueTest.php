<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;
use Civi\Test\Api4\Traits\TableDropperTrait;

abstract class BaseCustomValueTest extends UnitTestCase {

  use \Civi\Test\Api4\Traits\OptionCleanupTrait {
    setUp as setUpOptionCleanup;
  }
  use TableDropperTrait;

  /**
   * Set up baseline for testing
   */
  public function setUp() {
    $this->setUpOptionCleanup();
    $cleanup_params = [
      'tablesToTruncate' => [
        'civicrm_custom_group',
        'civicrm_custom_field',
      ],
    ];

    $this->dropByPrefix('civicrm_value_mycontact');
    $this->cleanup($cleanup_params);
  }

}
