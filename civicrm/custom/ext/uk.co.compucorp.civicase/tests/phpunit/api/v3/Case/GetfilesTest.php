<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

require_once 'BaseTestCase.php';

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
class api_v3_Case_GetfilesTest extends api_v3_Case_BaseTestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  public function setUp() {
    parent::setUp();
    CRM_Core_DAO::executeQuery('UPDATE civicrm_option_value SET grouping = "milestone" WHERE option_group_id = 2 AND name = "Medical evaluation"');
    $this->cleanupFiles();
  }

  public function tearDown() {
    parent::tearDown();
    $this->cleanupFiles();
  }

  public function getExamples() {
    $cases = array();

    // $cases[] = array(
    //   0 => 'actSubject',
    //   1 => 'actDetails',
    //   2 => 'fileName',
    //   3 => 'searchText',
    //   4 => expectMatch,
    // );

    $cases[0] = array(
      // Match any file if there's no filter.
      0 => 'Give bread a chance',
      1 => 'With a little butter and jam',
      2 => self::getFilePrefix() . 'theStuff.txt',
      3 => array(),
      4 => TRUE,
    );
    $cases[1] = array(
      // Match any file if the text filter is blank.
      0 => 'Give bread a chance',
      1 => 'With a little butter and jam',
      2 => self::getFilePrefix() . 'theStuff.txt',
      3 => array('text' => ''),
      4 => TRUE,
    );
    $cases[2] = array(
      // This doesn't match "cheese" to anything.
      0 => 'Give bread a chance',
      1 => 'With a little butter and jam',
      2 => self::getFilePrefix() . 'theStuff.txt',
      3 => array('text' => 'cheese'),
      4 => FALSE,
    );
    $cases[3] = array(
      // Match on subject.
      0 => 'Give cheese a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theStuff.txt',
      3 => array('text' => 'cheese'),
      4 => TRUE,
    );
    $cases[4] = array(
      // Match on details.
      0 => 'Give bread a chance',
      1 => 'But make it with cheesey goodness',
      2 => self::getFilePrefix() . 'theStuff.txt',
      3 => array('text' => 'cheese'),
      4 => TRUE,
    );
    $cases[5] = array(
      // Match on file name.
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('text' => 'cheese'),
      4 => TRUE,
    );
    $cases[6] = array(
      // Match on file type (miss).
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('mime_type' => 'text/html'),
      4 => FALSE,
    );
    $cases[7] = array(
      // Match on file type.
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('mime_type' => 'text/plain'),
      4 => TRUE,
    );
    $cases[8] = array(
      // Match on file category.
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('mime_type_cat' => 'doc'),
      4 => TRUE,
    );
    $cases[9] = array(
      // Match on file category (miss).
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('mime_type_cat' => 'sheet'),
      4 => FALSE,
    );
    $cases[10] = array(
      // Match on activity type (existing record).
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('activity_type_id' => 'Medical evaluation'),
      4 => TRUE,
    );
    $cases[11] = array(
      // Match on multiple activity types (existing record).
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array(
        'activity_type_id' => array(
          'IN',
          array('Medical evaluation', 'Incoming Email'),
        ),
      ),
      4 => TRUE,
    );
    $cases[12] = array(
      // Match on activity type (existent but unused type name).
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('activity_type_id' => 'Incoming Email'),
      4 => FALSE,
    );
    $cases[13] = array(
      // Match on activity type (non-existent type name).
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('activity_type_id' => 'Federated republic of blergistan'),
      4 => FALSE,
    );

    $cases[14] = array(
      // Match on activity type grouping (existing record)
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('activity_type_id.grouping' => array('LIKE' => '%milestone%')),
      4 => TRUE,
    );
    $cases[15] = array(
      // Match on activity type grouping (no match)
      0 => 'Give bread a chance',
      1 => '',
      2 => self::getFilePrefix() . 'theCheeseIsGoodForYou.txt',
      3 => array('activity_type_id.grouping' => array('LIKE' => '%document%')),
      4 => FALSE,
    );

    return $cases;
  }

  /**
   * Create an activity with an attachment. Run a search. See if it matches.
   *
   * @param string $actSubject
   *   Set the activity's subject
   * @param string $actDetails
   *   Set the activity's details.
   * @param string $fileName
   *   Set the attachment's file name.
   * @param array $searchParams
   *   Search criteria.
   *   Ex: array('text' => 'hello').
   * @param bool $expectMatch
   *   Whether the $searchText matches the activity.
   * @dataProvider getExamples
   */
  public function testSearch($actSubject, $actDetails, $fileName, $searchParams, $expectMatch) {
    $cases[0] = $this->callAPISuccess('Case', 'create', array(
      'contact_id' => 1,
      'creator_id' => 1,
      'case_type_id' => 'housing_support',
      'subject' => 'Hello world',
    ));
    $cases[1] = $this->callAPISuccess('Case', 'create', array(
      'contact_id' => 2,
      'creator_id' => 2,
      'case_type_id' => 'housing_support',
      'subject' => 'Hello world',
    ));

    $medEval = $this->callAPISuccess('Activity', 'getsingle', array(
      'case_id' => $cases[0]['id'],
      'activity_type_id' => 'Medical evaluation',
    ));
    CRM_Core_DAO::executeQuery('UPDATE civicrm_activity SET subject = %2, details = %3 WHERE id = %1', array(
      1 => array($medEval['id'], 'Integer'),
      2 => array($actSubject, 'String'),
      3 => array($actDetails, 'String'),
    ));
    $attachment = $this->callAPISuccess('Attachment', 'create', array(
      'name' => $fileName,
      'mime_type' => 'text/plain',
      'content' => 'My test content',
      'entity_table' => 'civicrm_activity',
      'entity_id' => $medEval['id'],
    ));

    $getfiles = $this->callAPISuccess('Case', 'getfiles', $searchParams + array(
      'case_id' => $cases[0]['id'],
    ));
    if ($expectMatch) {
      $attId = $attachment['id'];
      $this->assertEquals(1, $getfiles['count']);
      $this->assertEquals($cases[0]['id'], $getfiles['values'][$attId]['case_id']);
      $this->assertEquals($medEval['id'], $getfiles['values'][$attId]['activity_id']);
      $this->assertEquals($attachment['id'], $getfiles['values'][$attId]['id']);
      $this->assertTrue(!isset($getfiles['xref']));
    }
    else {
      $this->assertEquals(0, $getfiles['count']);
      $this->assertTrue(!isset($getfiles['xref']));
    }
  }

  /**
   * Create an activity with an attachment. Run a search. See if it matches.
   *
   * @param string $actSubject
   *   Set the activity's subject
   * @param string $actDetails
   *   Set the activity's details.
   * @param string $fileName
   *   Set the attachment's file name.
   * @param array $searchParams
   *   Search criteria.
   *   Ex: array('text' => 'hello').
   * @param bool $expectMatch
   *   Whether the $searchText matches the activity.
   * @dataProvider getExamples
   */
  public function testReviseThenAttachThenSearch($actSubject, $actDetails, $fileName, $searchParams, $expectMatch) {
    $cases[0] = $this->callAPISuccess('Case', 'create', array(
      'contact_id' => 1,
      'creator_id' => 1,
      'case_type_id' => 'housing_support',
      'subject' => 'Hello world',
    ));
    $cases[1] = $this->callAPISuccess('Case', 'create', array(
      'contact_id' => 2,
      'creator_id' => 2,
      'case_type_id' => 'housing_support',
      'subject' => 'Hello world',
    ));

    $medEval = $this->callAPISuccess('Activity', 'getsingle', array(
      'case_id' => $cases[0]['id'],
      'activity_type_id' => 'Medical evaluation',
    ));
    $update = $this->callAPISuccess('Activity', 'create', array(
      'activity_id' => $medEval['id'],
      'subject' => $actSubject,
      'details' => $actDetails,
    ));
    $attachment = $this->callAPISuccess('Attachment', 'create', array(
      'name' => $fileName,
      'mime_type' => 'text/plain',
      'content' => 'My test content',
      'entity_table' => 'civicrm_activity',
      'entity_id' => $update['id'],
    ));

    $getfiles = $this->callAPISuccess('Case', 'getfiles', $searchParams + array(
      'case_id' => $cases[0]['id'],
    ));
    if ($expectMatch) {
      $attId = $attachment['id'];
      $this->assertEquals(1, $getfiles['count']);
      $this->assertEquals($cases[0]['id'], $getfiles['values'][$attId]['case_id']);
      $this->assertEquals($update['id'], $getfiles['values'][$attId]['activity_id']);
      $this->assertEquals($attachment['id'], $getfiles['values'][$attId]['id']);
      $this->assertTrue(!isset($getfiles['xref']));
    }
    else {
      $this->assertEquals(0, $getfiles['count']);
      $this->assertTrue(!isset($getfiles['xref']));
    }
  }

  /**
   * Create an activity with an attachment. Run a search. See if it matches.
   *
   * @param string $actSubject
   *   Set the activity's subject
   * @param string $actDetails
   *   Set the activity's details.
   * @param string $fileName
   *   Set the attachment's file name.
   * @param array $searchParams
   *   Search criteria.
   *   Ex: array('text' => 'hello').
   * @param bool $expectMatch
   *   Whether the $searchText matches the activity.
   * @dataProvider getExamples
   */
  public function testAttachThenReviseThenSearch($actSubject, $actDetails, $fileName, $searchParams, $expectMatch) {
    $cases[0] = $this->callAPISuccess('Case', 'create', array(
      'contact_id' => 1,
      'creator_id' => 1,
      'case_type_id' => 'housing_support',
      'subject' => 'Hello world',
    ));
    $cases[1] = $this->callAPISuccess('Case', 'create', array(
      'contact_id' => 2,
      'creator_id' => 2,
      'case_type_id' => 'housing_support',
      'subject' => 'Hello world',
    ));

    $medEval = $this->callAPISuccess('Activity', 'getsingle', array(
      'case_id' => $cases[0]['id'],
      'activity_type_id' => 'Medical evaluation',
    ));
    $attachment = $this->callAPISuccess('Attachment', 'create', array(
      'name' => $fileName,
      'mime_type' => 'text/plain',
      'content' => 'My test content',
      'entity_table' => 'civicrm_activity',
      'entity_id' => $medEval['id'],
    ));
    $update = $this->callAPISuccess('Activity', 'create', array(
      'activity_id' => $medEval['id'],
      'subject' => $actSubject,
      'details' => $actDetails,
    ));

    $getfiles = $this->callAPISuccess('Case', 'getfiles', $searchParams + array(
      'case_id' => $cases[0]['id'],
    ));
    if ($expectMatch) {
      $attId = $attachment['id'];
      $this->assertEquals(1, $getfiles['count']);
      $this->assertEquals($cases[0]['id'], $getfiles['values'][$attId]['case_id']);
      $this->assertEquals($update['id'], $getfiles['values'][$attId]['activity_id']);
      $this->assertEquals($attachment['id'], $getfiles['values'][$attId]['id']);
      $this->assertTrue(!isset($getfiles['xref']));
    }
    else {
      $this->assertEquals(0, $getfiles['count']);
      $this->assertTrue(!isset($getfiles['xref']));
    }
  }

  /**
   * Test that `options.xref==1` causes lookups for all related records.
   */
  public function testXref() {
    $cases[0] = $this->callAPISuccess('Case', 'create', array(
      'contact_id' => 1,
      'creator_id' => 1,
      'case_type_id' => 'housing_support',
      'subject' => 'Hello world',
    ));

    $medEval = $this->callAPISuccess('Activity', 'getsingle', array(
      'case_id' => $cases[0]['id'],
      'activity_type_id' => 'Medical evaluation',
    ));
    CRM_Core_DAO::executeQuery('UPDATE civicrm_activity SET subject = %2, details = %3 WHERE id = %1', array(
      1 => array($medEval['id'], 'Integer'),
      2 => array('The subject', 'String'),
      3 => array('The details', 'String'),
    ));
    $attachment = $this->callAPISuccess('Attachment', 'create', array(
      'name' => self::getFilePrefix() . 'TheFile.txt',
      'mime_type' => 'text/plain',
      'content' => 'My test content',
      'entity_table' => 'civicrm_activity',
      'entity_id' => $medEval['id'],
    ));

    $getfiles = $this->callAPISuccess('Case', 'getfiles', array(
      'case_id' => $cases[0]['id'],
      'options' => array(
        'xref' => 1,
      ),
    ));
    $this->assertEquals(1, $getfiles['count']);
    $attId = $attachment['id'];
    $this->assertEquals($cases[0]['id'], $getfiles['values'][$attId]['case_id']);
    $this->assertEquals($medEval['id'], $getfiles['values'][$attId]['activity_id']);
    $this->assertEquals($attachment['id'], $getfiles['values'][$attId]['id']);

    $this->assertEquals('The subject', $getfiles['xref']['activity'][$medEval['id']]['subject']);
    $this->assertEquals('The details', $getfiles['xref']['activity'][$medEval['id']]['details']);
    $this->assertEquals(self::getFilePrefix() . 'TheFile.txt', $getfiles['xref']['file'][$attId]['name']);
    $this->assertEquals('Hello world', $getfiles['xref']['case'][$cases[0]['id']]['subject']);
  }

  public function testEmpty() {
    $this->callAPIFailure('Case', 'getfiles', array());
  }

}
