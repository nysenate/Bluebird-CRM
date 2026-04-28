<?php
declare(strict_types = 1);

require_once __DIR__ . '/IntegrationTestCase.php';

/**
 * Exposes protected members of CRM_NYSS_PrintImport_Form_Import for E2E testing.
 */
class TestableE2EFormImport extends CRM_NYSS_PrintImport_Form_Import {

  private array $testValues  = [];
  private string $fixturePath = '';

  public function setTestValues(array $values): void {
    $this->testValues = $values;
  }

  public function setFixturePath(string $path): void {
    $this->fixturePath = $path;
  }

  public function exportValues($elementList = NULL, $filterInternal = FALSE): array {
    return $this->testValues;
  }

  protected function resolveImportFilePath(array $values): string {
    return $this->fixturePath;
  }
}

/**
 * End-to-end integration tests for CRM_NYSS_PrintImport_Form_Import::postProcess().
 *
 * Runs the full import pipeline against a real database using a fixture
 * tab-delimited file. Contacts created by the importer are registered for
 * cleanup at the end of each test.
 *
 * @group integration
 */
class CRM_NYSS_PrintImport_Integration_E2EImportTest
  extends CRM_NYSS_PrintImport_Integration_IntegrationTestCase {

  private string $fixturePath;

  /** Max civicrm_contact.id before each test — scopes lookups to contacts created by this run. */
  private int $baselineMaxContactId = 0;

  /** Last names used in the fixture — used to find and clean up imported contacts. */
  private const FIXTURE_LAST_NAMES = ['E2ESmith', 'E2EJones', 'E2EWilliams', 'E2EBrown', 'E2EGarcia'];

  protected function setUp(): void {
    parent::setUp();
    $this->fixturePath = __DIR__ . '/../../../../../../tests/fixtures/basic_import.tab';

    // Snapshot the highest existing contact ID so assertions can filter to only
    // the contacts created by this specific test run, ignoring any stranded
    // rows left by previous failed runs.
    $result = mysqli_query(static::$conn,
      "SELECT COALESCE(MAX(id), 0) AS max_id FROM civicrm_contact");
    $row    = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    $this->baselineMaxContactId = (int) $row['max_id'];
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  /**
   * Build a form instance pre-loaded with fixture values.
   */
  private function makeForm(array $overrides = []): TestableE2EFormImport {
    $form = new TestableE2EFormImport();
    $form->setFixturePath($this->fixturePath);
    $form->setTestValues(array_merge($this->defaultValues(), $overrides));
    return $form;
  }

  /**
   * Minimal default values that mirror what the real form submits.
   */
  private function defaultValues(): array {
    return [
      'runnum'              => 'All',
      'dryrun'              => 0,
      'sendemail'           => 0,
      'debug'               => 0,
      'dedupe'              => 0,
      'deduperule'          => '',
      'boeimport'           => 0,
      'emailimport'         => 0,
      'parsename'           => 0,
      'fieldoverried'       => 0,
      'allowdelete'         => 0,
      'addtogroup_handling' => 'none',
      'addtogroup'          => '',
      'greetinggeneration'  => 'none',
    ];
  }

  /**
   * Find all contacts created by the fixture import and register them for teardown.
   *
   * @return array  Rows from civicrm_contact for the imported contacts.
   */
  private function findAndRegisterImportedContacts(): array {
    $names  = implode("','", self::FIXTURE_LAST_NAMES);
    $minId  = $this->baselineMaxContactId;
    $result = mysqli_query(static::$conn,
      "SELECT id, first_name, last_name, display_name
       FROM civicrm_contact
       WHERE last_name IN ('{$names}') AND is_deleted = 0 AND id > {$minId}
       ORDER BY id ASC"
    );
    $contacts = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $contacts[] = $row;
      $this->registerContactForCleanup((int) $row['id']);
    }
    mysqli_free_result($result);
    return $contacts;
  }

  // ---------------------------------------------------------------------------
  // Tests
  // ---------------------------------------------------------------------------

  public function testImportCreatesAllContacts(): void {
    $this->makeForm()->postProcess();

    $contacts = $this->findAndRegisterImportedContacts();

    $this->assertCount(5, $contacts, 'All 5 fixture contacts should be created');

    // first_name handler uses skipMixed=true — strings with existing lowercase
    // letters are left unchanged, so 'E2EAlice' stays 'E2EAlice'.
    $firstNames = array_column($contacts, 'first_name');
    $this->assertContains('E2EAlice',   $firstNames);
    $this->assertContains('E2EBob',     $firstNames);
    $this->assertContains('E2ECarol',   $firstNames);
    $this->assertContains('E2EDave',    $firstNames);
    $this->assertContains('E2EEve',     $firstNames);
  }

  public function testImportCreatesAddresses(): void {
    $this->makeForm()->postProcess();

    $contacts = $this->findAndRegisterImportedContacts();
    $this->assertNotEmpty($contacts);

    // Verify every imported contact has at least one address.
    foreach ($contacts as $contact) {
      $cid    = (int) $contact['id'];
      $result = mysqli_query(static::$conn,
        "SELECT COUNT(*) AS cnt FROM civicrm_address WHERE contact_id = {$cid}"
      );
      $row = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      $this->assertGreaterThan(0, (int) $row['cnt'],
        "Contact {$contact['display_name']} should have an address");
    }
  }

  public function testImportProcessesKeywords(): void {
    $this->makeForm()->postProcess();

    $contacts = $this->findAndRegisterImportedContacts();
    $this->assertNotEmpty($contacts);

    // E2EAlice has keywords "Newsletter|Environment" — she should have 2 tags.
    $alice = current(array_filter($contacts, fn($c) => $c['last_name'] === 'E2ESmith'));
    $this->assertNotFalse($alice, 'E2EAlice Smith should exist');

    $cid    = (int) $alice['id'];
    $result = mysqli_query(static::$conn,
      "SELECT COUNT(*) AS cnt FROM civicrm_entity_tag WHERE entity_table = 'civicrm_contact' AND entity_id = {$cid}"
    );
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    $this->assertGreaterThanOrEqual(2, (int) $row['cnt'],
      'E2EAlice should have at least 2 keyword tags (Newsletter, Environment)');
  }

  public function testImportGroupHandlingAddsAllContacts(): void {
    $groupName = 'E2E Test Group ' . uniqid();

    $this->makeForm([
      'addtogroup_handling' => 'all',
      'addtogroup'          => $groupName,
    ])->postProcess();

    $contacts = $this->findAndRegisterImportedContacts();
    $this->assertCount(5, $contacts);

    // Find the group and verify membership.
    $group = civicrm_api3('group', 'get', ['title' => $groupName, 'sequential' => 1]);
    $this->assertSame(1, $group['count'], 'Import group should have been created');

    $groupId = (int) $group['values'][0]['id'];
    $this->trackForCleanup('civicrm_group', $groupId);

    $members = civicrm_api3('group_contact', 'get', [
      'group_id'   => $groupId,
      'status'     => 'Added',
      'options'    => ['limit' => 0],
    ]);
    $this->assertSame(5, $members['count'], 'All 5 contacts should be in the group');
  }

  public function testDeleteContactRemovesContact(): void {
    // Create a contact to be targeted by the delete import.
    $contactId = $this->createContact([
      'first_name' => 'E2EDelete',
      'last_name'  => 'E2EDeleteTarget',
    ]);

    // Load the delete fixture template and substitute the real contact ID.
    $templatePath = __DIR__ . '/../../../../../../tests/fixtures/delete_contact.tab';
    $fixture      = str_replace('__CONTACT_ID__', (string) $contactId,
                      file_get_contents($templatePath));
    $tmpPath      = sys_get_temp_dir() . '/nyss_delete_' . uniqid() . '.tab';
    file_put_contents($tmpPath, $fixture);

    try {
      $form = new TestableE2EFormImport();
      $form->setFixturePath($tmpPath);
      $form->setTestValues(array_merge($this->defaultValues(), ['allowdelete' => 1]));
      $form->postProcess();
    } finally {
      unlink($tmpPath);
    }

    // Accept either hard delete (row gone) or soft delete (is_deleted = 1).
    $result = mysqli_query(static::$conn,
      "SELECT is_deleted FROM civicrm_contact WHERE id = {$contactId}");
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    $isGone = ($row === NULL || (int) $row['is_deleted'] === 1);
    $this->assertTrue($isGone,
      "Contact {$contactId} should have been deleted by the import");
  }

  public function testDryRunCreatesNoContacts(): void {
    $this->makeForm(['dryrun' => 1])->postProcess();

    $names  = implode("','", self::FIXTURE_LAST_NAMES);
    $minId  = $this->baselineMaxContactId;
    $result = mysqli_query(static::$conn,
      "SELECT COUNT(*) AS cnt FROM civicrm_contact WHERE last_name IN ('{$names}') AND is_deleted = 0 AND id > {$minId}"
    );
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    $this->assertSame(0, (int) $row['cnt'], 'Dry run should create no contacts');
  }

}
