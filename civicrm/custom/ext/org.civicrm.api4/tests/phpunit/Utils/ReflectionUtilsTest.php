<?php

namespace Civi\Test\Api4\Utils;

use Civi\Api4\Utils\ReflectionUtils;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class ReflectionUtilsTest extends UnitTestCase {

  /**
   * Test that class annotations are returned across @inheritDoc
   */
  public function testGetDocBlockForClass() {
    $grandChild = new TestV4ReflectionGrandchild();
    $reflection = new \ReflectionClass($grandChild);
    $doc = ReflectionUtils::getCodeDocs($reflection);

    $this->assertEquals(TRUE, $doc['internal']);
    $this->assertEquals('Grandchild class', $doc['description']);

    $expectedComment = 'This is an extended description.

There is a line break in this description.

This is the base class.';

    $this->assertEquals($expectedComment, $doc['comment']);
  }

  /**
   * Test that property annotations are returned across @inheritDoc
   */
  public function testGetDocBlockForProperty() {
    $grandChild = new TestV4ReflectionGrandchild();
    $reflection = new \ReflectionClass($grandChild);
    $doc = ReflectionUtils::getCodeDocs($reflection->getProperty('foo'), 'Property');

    $this->assertEquals('This is the foo property.', $doc['description']);
    $this->assertEquals("In the child class, foo has been barred.\n\nIn general, you can do nothing with it.", $doc['comment']);
  }

}
