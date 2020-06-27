<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

require_once __DIR__ . '/../../api/v3/Case/BaseTestCase.php';

/**
 * Test the "Case.getfiles" API.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Civicase_FileCategoryTest extends api_v3_Case_BaseTestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  public function getExamples() {
    $cases[] = array(
      0 => 'text/html',
      1 => array('doc'),
      2 => TRUE,
    );
    $cases[] = array(
      0 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.ZOOP',
      1 => array('doc'),
      2 => FALSE,
    );
    $cases[] = array(
      0 => 'text/html',
      1 => array('doc', 'sheet'),
      2 => TRUE,
    );
    $cases[] = array(
      0 => 'application/vnd.openxmlformats-officedocument.spreadsheetml.ZOOP',
      1 => array('doc', 'sheet'),
      2 => TRUE,
    );
    $cases[] = array(
      0 => 'application/foo-bar-whiz-bang',
      1 => array('other'),
      2 => TRUE,
    );
    $cases[] = array(
      0 => 'application/foo-bar-whiz-bang',
      1 => array('doc'),
      2 => FALSE,
    );
    return $cases;
  }

  /**
   * Check whether $mimeType matches one of the categories in $cats.
   *
   * @param string $mimeType
   *   Ex: 'text/html'.
   * @param array $cats
   *   Ex: array('doc', 'sheet').
   * @param bool $expectedMatch
   * @dataProvider getExamples
   */
  public function testQuery($mimeType, $cats, $expectedMatch) {
    $expr = CRM_Civicase_FileCategory::createSqlFilter('"' . $mimeType . '"', $cats);
    $ret = CRM_Core_DAO::singleValueQuery("SELECT IF($expr,'yes','no')");
    $this->assertTrue(
      ($ret === 'yes') == $expectedMatch,
      "Check whether $mimeType matches " . implode(',', $cats) . ' using ' . $expr);
  }

  public function testQueryAny() {
    $this->testQuery(
      'text/plain',
      array_keys(CRM_Civicase_FileCategory::getCategoryLabels()),
      TRUE
    );
  }

}
