<?php

namespace Civi\Test\Api4\Utils;

use Civi\Test\Api4\Utils;

/**
 * @inheritDoc
 */
class TestV4ReflectionChild extends Utils\TestV4ReflectionBase {
  /**
   * @inheritDoc
   *
   * In the child class, foo has been barred.
   */
  public $foo = ['bar' => 1];

}
