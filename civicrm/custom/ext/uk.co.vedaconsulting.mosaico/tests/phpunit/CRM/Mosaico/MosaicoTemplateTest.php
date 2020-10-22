<?php

use Civi\Test\EndToEndInterface;

require_once __DIR__ . '/TestCase.php';

/**
 * Test the MosaicoTemplate API.
 *
 * @group e2e
 * @see cv
 */
class CRM_Mosaico_MosaicoTemplateTest extends CRM_Mosaico_TestCase implements EndToEndInterface {

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
    CRM_Core_DAO::executeQuery('DELETE FROM civicrm_mosaico_template WHERE title LIKE "MosaicoTemplateTest%"');
    parent::tearDown();
  }

  public function testCreateGetDelete() {
    $r = rand();
    $createResult = $this->callAPISuccess('MosaicoTemplate', 'create', [
      'title' => 'MosaicoTemplateTest ' . $r,
      'base' => 'versafix-1',
      'html' => '<p>hello</p>',
      'metadata' => json_encode(['foo' => 'bar']),
      'content' => json_encode(['abc' => 'def']),
    ]);

    $getResult = $this->callAPISuccess('MosaicoTemplate', 'get', [
      'title' => 'MosaicoTemplateTest ' . $r,
    ]);
    $this->assertEquals(1, $getResult['count']);
    $this->assertEquals(1, count($getResult['values']));
    $this->assertTrue(is_array($getResult['values'][$createResult['id']]));
    foreach ($getResult['values'] as $value) {
      $this->assertEquals('<p>hello</p>', $value['html']);
      $this->assertEquals(['foo' => 'bar'], json_decode($value['metadata'], 1));
      $this->assertEquals(['abc' => 'def'], json_decode($value['content'], 1));
    }

    $this->callAPISuccess('MosaicoTemplate', 'delete', [
      'id' => $createResult['id'],
    ]);
  }

  public function testClone() {
    $createResult = $this->callAPISuccess('MosaicoTemplate', 'create', [
      'title' => 'MosaicoTemplateTest foo',
      'base' => 'versafix-1',
      'html' => '<p>hello</p>',
      'metadata' => json_encode(['foo' => 'bar']),
      'content' => json_encode(['abc' => 'def']),
    ]);

    $cloneResult = $this->callAPISuccess('MosaicoTemplate', 'clone', [
      'id' => $createResult['id'],
      'title' => 'MosaicoTemplateTest bar',
    ]);
    $clone = $cloneResult['values'][$cloneResult['id']];

    $this->assertNotEquals($clone['id'], $createResult['id']);
    $this->assertEquals('MosaicoTemplateTest bar', $clone['title']);
    $this->assertEquals('versafix-1', $clone['base']);
    $this->assertEquals('<p>hello</p>', $clone['html']);
    $this->assertEquals(json_encode(['foo' => 'bar']), $clone['metadata']);
    $this->assertEquals(json_encode(['abc' => 'def']), $clone['content']);
  }

}
