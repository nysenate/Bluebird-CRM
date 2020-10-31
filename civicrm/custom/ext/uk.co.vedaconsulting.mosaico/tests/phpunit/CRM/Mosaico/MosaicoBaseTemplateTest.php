<?php

use Civi\Test\EndToEndInterface;

require_once __DIR__ . '/TestCase.php';

/**
 * Test the MosaicoBaseTemplate API.
 *
 * @group e2e
 * @see cv
 */
class CRM_Mosaico_MosaicoBaseTemplateTest extends CRM_Mosaico_TestCase implements EndToEndInterface {

  public static function setUpBeforeClass() {
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md

    // Example: Install this extension. Don't care about anything else.
    \Civi\Test::e2e()->installMe(__DIR__)->apply();

    // Example: Uninstall all extensions except this one.
    // \Civi\Test::e2e()->uninstall('*')->installMe(__DIR__)->apply();

    // Example: Install only core civicrm extensions.
    // \Civi\Test::e2e()->uninstall('*')->install('org.civicrm.*')->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  public function testGet() {
    $result = $this->callAPISuccess('MosaicoBaseTemplate', 'get', []);
    $this->assertTrue(is_array($result['values']));
    $this->assertEquals('versafix-1', $result['values']['versafix-1']['name']);
    $this->assertRegExp(';\.html$;', $result['values']['versafix-1']['path']);
    $this->assertRegExp(';https?://.*mosaico.*versafix-1.*png;', $result['values']['versafix-1']['thumbnail']);
  }

  public function testGetSingle() {
    $result = $this->callAPISuccess('MosaicoBaseTemplate', 'getsingle', ['name' => 'tedc15']);
    $this->assertEquals('tedc15', $result['name']);
    $this->assertRegExp(';\.html$;', $result['path']);
    $this->assertRegExp(';https?://.*mosaico.*tedc15.*png;', $result['thumbnail']);
  }

}
