<?php

namespace api\v4\Mock;

/**
 * @inheritDoc
 */
class MockV4ReflectionChild extends MockV4ReflectionBase {
  /**
   * @inheritDoc
   *
   * In the child class, foo has been barred.
   */
  public $foo = ['bar' => 1];

}
