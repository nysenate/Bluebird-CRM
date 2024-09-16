<?php

require_once __DIR__ . '/TestCase.php';

/**
 * Class CRM_Mosaico_AbDemuxTest
 *
 * General integration test to ensure that A/B tests are generated when
 * Mosaico mailings specify multiple variants.
 *
 * @group headless
 */
class CRM_Mosaico_AbDemuxTest extends CRM_Mosaico_TestCase implements \Civi\Test\HeadlessInterface, \Civi\Test\TransactionalInterface {

  use \Civi\Test\ContactTestTrait;
  use \Civi\Test\DbTestTrait;
  use \Civi\Test\GenericAssertionsTrait;
  use \Civi\Test\MailingTestTrait;

  /**
   * Generated Entity IDs keyed by the entity name
   *
   * We don't use `$ids` directly, but `ContactTestTrait` does. Prior to
   * 5.64, the declaration satisfied an undeclared property issue. In 5.64, it became declared
   * (by way of `ContactTestTrait` => `EntityTrait`).
   *
   * For the moment, we must match `EntityTrait::$ids` exactly to be portable.
   * Consider removing this declaration once 5.63 goes EOL.
   *
   * @var array
   */
  protected $ids = [];

  /**
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   * See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install(['org.civicrm.search_kit', 'org.civicrm.flexmailer', 'uk.co.vedaconsulting.mosaico'])
      ->apply();
  }

  protected function setUp(): void {
    parent::setUp();

    Civi::settings()->set('disable_mandatory_tokens_check', TRUE);
    $this->createLoggedInUser();
  }

  public function getVariantExamples(): array {
    $cases = array();

    // array($field, $inputValue, $expectRegex).

    $cases[] = [
      ['subject' => 'New Subject A'],
      ['subject' => 'New Subject B'],
      ['subject' => 'New Subject A', 'body_html' => 'Placeholder'],
      ['subject' => 'New Subject B', 'body_html' => 'Placeholder'],
    ];
    $cases[] = [
      ['subject' => 'New Subject A', 'body_html' => 'New Html A'],
      ['subject' => 'New Subject B', 'body_html' => 'New Html B'],
      ['subject' => 'New Subject A', 'body_html' => 'New Html A'],
      ['subject' => 'New Subject B', 'body_html' => 'New Html B'],
    ];

    return $cases;
  }

  /**
   * Generate a preview of an outgoing email. Check that the field is
   * rendered the expected way.
   *
   * @dataProvider getVariantExamples
   */
  public function testMailingSubmit($inputVariantA, $inputVariantB, $expectVariantA, $expectVariantB): void {
    $this->assertTrue(Civi::container()->has('mosaico_ab_demux'), 'Mosaico services should be active in test environment');
    $createParams = [
      'template_type' => 'mosaico',
      'template_options' => [
        'variants' => [
          0 => $inputVariantA,
          1 => $inputVariantB,
        ],
        'variantsPct' => 15,
      ],
      'subject' => 'Placeholder',
      'body_text' => "Placeholder",
      'body_html' => "Placeholder",
      'name' => 'AbDemuxTest' . md5(uniqid()),
      'header_id' => '',
      'footer_id' => '',
    ];
    $createResult = $this->callAPISuccess('mailing', 'create', $createParams);

    $submitParams = [
      'id' => $createResult['id'],
      'scheduled_date' => '2014-12-13 10:00:00',
      'approval_date' => '2014-12-13 00:00:00',
    ];
    $submitResult = $this->callAPISuccess('mailing', 'submit', $submitParams, __FUNCTION__, __FILE__);
    $this->assertTrue(is_numeric($submitResult['id']));
    $this->assertEquals($createResult['id'], $submitResult['id']);
    $this->assertTrue(is_numeric($submitResult['values'][$createResult['id']]['scheduled_id']));
    // $this->assertEquals($submitParams['scheduled_date'], $submitResult['values'][$createResult['id']]['scheduled_date']);

    $ab = $submitResult['values'][$createResult['id']]['_mailing_ab'];
    $mailingJobCounts = [
      ['created', $createResult['id'], 1],
      ['a', $ab['mailing_id_a'], 1],
      ['b', $ab['mailing_id_b'], 1],
      ['c', $ab['mailing_id_c'], 0],
    ];
    foreach ($mailingJobCounts as $mailingJobCount) {
      list ($name, $mailingId, $expectJobCount) = $mailingJobCount;
      $this->assertDBQuery($expectJobCount, 'SELECT count(*) FROM civicrm_mailing_job WHERE mailing_id = %1', [
        1 => [$mailingId, 'Integer'],
      ], "Check that item \"$name\" has $expectJobCount job(s)");
    }
    $this->assertEquals(15, $ab['group_percentage']);

    $aLoad = $this->callAPISuccess('Mailing', 'getsingle', ['id' => $ab['mailing_id_a']]);
    $bLoad = $this->callAPISuccess('Mailing', 'getsingle', ['id' => $ab['mailing_id_b']]);
    $this->assertAttributesEquals($expectVariantA, $aLoad);
    $this->assertAttributesEquals($expectVariantB, $bLoad);
  }

  /**
   * @return array
   */
  public function groupPctProvider() {
    // array(int $totalSize, int $groupPct, int $expectedCountA, $expectedCountB, $expectedCountC)
    $cases = array();
    $cases[] = array(1, 10, 1, 0, 0);
    $cases[] = array(2, 10, 1, 1, 0);
    $cases[] = array(3, 10, 1, 1, 1);
    $cases[] = array(50, 10, 5, 5, 40);
    $cases[] = array(50, 20, 10, 10, 30);
    return $cases;
  }

  /**
   * Create a test and ensure that all three mailings (A/B/C) wind up with the correct
   * number of recipients.
   *
   * @param $totalGroupContacts
   * @param $groupPct
   * @param $expectedCountA
   * @param $expectedCountB
   * @param $expectedCountC
   * @dataProvider groupPctProvider
   */
  public function testDistribution($totalGroupContacts, $groupPct, $expectedCountA, $expectedCountB, $expectedCountC): void {
    $this->assertTrue(Civi::container()->has('mosaico_ab_demux'), 'Mosaico services should be active in test environment');

    $groupId = $this->groupCreate();
    $result = $this->groupContactCreate($groupId, $totalGroupContacts, TRUE);
    $this->assertEquals($totalGroupContacts, $result['added'], "in line " . __LINE__);

    $mailingIdA = $this->createMailing([
      'template_options' => [
        'variants' => [
          0 => ['subject' => 'Subject 1'],
          1 => ['subject' => 'Subject 2'],
        ],
        'variantsPct' => $groupPct,
      ],
    ]);

    $this->callAPISuccess('Mailing', 'create', [
      'id' => $mailingIdA,
      'groups' => ['include' => [$groupId]],
    ]);
    $this->assertJobCounts(0, $mailingIdA);

    $this->callAPISuccess('Mailing', 'submit', [
      'id' => $mailingIdA,
      'scheduled_date' => 'now',
      'approval_date' => 'now',
    ]);

    $ab = $this->callAPISuccess('MailingAB', 'getsingle', ['mailing_id_a' => $mailingIdA]);

    $this->assertRecipientCounts($expectedCountA, $ab['mailing_id_a']);
    $this->assertRecipientCounts($expectedCountB, $ab['mailing_id_b']);
    $this->assertRecipientCounts($expectedCountC, $ab['mailing_id_c']);
    $this->assertJobCounts(1, $ab['mailing_id_a']);
    $this->assertJobCounts(1, $ab['mailing_id_b']);
    $this->assertJobCounts(0, $ab['mailing_id_c']);

    $this->callAPISuccess('MailingAB', 'submit', [
      'id' => $ab['id'],
      'winner_id' => $ab['mailing_id_a'],
      'status' => 'Final',
      'scheduled_date' => 'now',
      'approval_date' => 'now',
    ]);
    $this->assertRecipientCounts($expectedCountA, $ab['mailing_id_a']);
    $this->assertRecipientCounts($expectedCountB, $ab['mailing_id_b']);
    $this->assertRecipientCounts($expectedCountC, $ab['mailing_id_c']);
    $this->assertJobCounts(1, $ab['mailing_id_a']);
    $this->assertJobCounts(1, $ab['mailing_id_b']);
    $this->assertJobCounts(1, $ab['mailing_id_c']);
  }

  protected function assertRecipientCounts($expectCount, $mailingId) {
    $actualCount = $this->callAPISuccess('MailingRecipients', 'getcount', ['mailing_id' => $mailingId]);
    $this->assertEquals($expectCount, $actualCount, "check mailing recipients in line " . __LINE__);
  }

  protected function assertJobCounts($expectCount, $mailingId) {
    $this->assertDBQuery($expectCount, 'SELECT count(*) FROM civicrm_mailing_job WHERE mailing_id = %1', [
      1 => [$mailingId, 'Integer'],
    ]);
  }

}
