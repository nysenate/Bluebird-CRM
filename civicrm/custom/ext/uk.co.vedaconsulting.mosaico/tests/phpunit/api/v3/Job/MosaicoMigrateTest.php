<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Job.MosaicoMigrate API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Job_MosaicoMigrateTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  /**
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   * See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Simple example test case.
   *
   * Note how the function name begins with the word "test".
   */
  public function testMigrate() {
    $this->createExampleLegacyTemplate();
    $this->assertEquals(1, CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_mosaico_msg_template'));
    $this->assertEquals(0, CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_mosaico_template'));

    $result = civicrm_api3('Job', 'mosaico_migrate', []);
    $this->assertEquals(1, count($result['values']));
    foreach ($result['values'] as $k => $v) {
      $this->assertEquals($k, $v['id']);
      $this->assertEquals('versafix-1', $v['base']);
    }
    $this->assertEquals(1, CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_mosaico_msg_template'));
    $this->assertEquals(1, CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_mosaico_template'));

    $tpl = civicrm_api3('MosaicoTemplate', 'getsingle', []);
    $this->assertEquals('The Name', $tpl['title']);
    $this->assertEquals('versafix-1', $tpl['base']);

    $result = civicrm_api3('Job', 'mosaico_purge', []);
    $this->assertEquals(0, CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_mosaico_msg_template'));
    $this->assertEquals(1, CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_mosaico_template'));
  }

  protected function createExampleLegacyTemplate() {
    $msgTpl = civicrm_api3('MessageTemplate', 'create', [
      'msg_title' => 'The Title',
      'msg_subject' => 'The Subject',
      'msg_html' => '<p>Placeholder</p>',
    ]);

    CRM_Core_DAO::executeQuery('
      INSERT INTO civicrm_mosaico_msg_template (msg_tpl_id, hash_key, name, html, metadata, template)
      VALUES (%1, "1234abcd", "The Name", "<p>The markup</p>", %2, %3)
    ', [
      1 => [$msgTpl['id'], 'Positive'],
      2 => [
        '{"template":"http://dcase.l/sites/all/modules/civicrm/ext/mosaico/packages/mosaico/templates/versafix-1/template-versafix-1.html","name":"No name","created":1512016950525,"editorversion":"0.15.0","templateversion":"1.0.5","changed":1512016954547}',
        'String',
      ],
      3 => [json_encode(['type' => 'template']), 'String'],
    ]);
  }

}
