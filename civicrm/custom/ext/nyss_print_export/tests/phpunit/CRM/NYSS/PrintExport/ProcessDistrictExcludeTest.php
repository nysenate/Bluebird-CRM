<?php
declare(strict_types = 1);

use Civi\Test\EndToEndInterface;

/**
 * Baseline behavior test for processDistrictExclude(), captured before
 * refactoring it to stop calling BB_NORMALIZE()/BB_NORMALIZE_ADDR() per row
 * inside the exclusion JOIN (NYSS #18887 follow-up: those calls run against
 * the raw, un-normalized export temp table, even though shadow_contact/
 * shadow_address already carry pre-normalized values for every contact via
 * the gov.nysenate.dedupe triggers). These tests pin down the current
 * matching semantics -- including the normalization-insensitivity that the
 * refactor must preserve -- so a rewrite that joins shadow tables directly
 * can be checked against identical expected behavior.
 *
 * processDistrictExclude() resolves the given district ID to a configured
 * Bluebird instance (via bluebird.cfg) and queries that instance's database
 * directly with unescaped `$db.table` identifiers. To run safely against
 * any instance without risking a write to a different instance's database,
 * this test only proceeds when the current instance's own configured
 * district resolves back to the database this test is already connected
 * to (bluebird.cfg's training1/training2/example instances share
 * district=99 for exactly this purpose) -- otherwise it skips with an
 * explanation rather than guessing.
 *
 * Run from the extension directory:
 *   INSTANCE=<self-referential-instance> HTTP_HOST=<same> phpunit
 *
 * @group e2e
 */
class CRM_NYSS_PrintExport_ProcessDistrictExcludeTest extends \PHPUnit\Framework\TestCase implements EndToEndInterface {

  private static int $districtID;

  private array $contactIds = [];

  private array $tables = [];

  public static function setUpBeforeClass(): void {
    \Civi\Test::e2e()->installMe(__DIR__)->apply();
  }

  public function setUp(): void {
    parent::setUp();

    $funcCount = (int) CRM_Core_DAO::singleValueQuery(
      "SELECT COUNT(*) FROM information_schema.ROUTINES
       WHERE ROUTINE_SCHEMA = DATABASE()
         AND ROUTINE_NAME IN ('BB_NORMALIZE', 'BB_NORMALIZE_ADDR')"
    );
    if ($funcCount !== 2) {
      $this->markTestSkipped(
        'BB_NORMALIZE/BB_NORMALIZE_ADDR are not installed on this instance (gov.nysenate.dedupe not active?).'
      );
    }

    $instance = getenv('HTTP_HOST') ?: getenv('INSTANCE');
    if (!$instance) {
      $this->markTestSkipped('Neither HTTP_HOST nor INSTANCE is set; cannot determine which Bluebird instance is running.');
    }

    if (!function_exists('get_bluebird_instance_config') || !function_exists('get_bluebird_config')) {
      $this->markTestSkipped('Bluebird config helpers are not available in this bootstrap.');
    }

    $ownConfig = get_bluebird_instance_config($instance);
    $districtID = $ownConfig['district'] ?? NULL;
    if (!$districtID) {
      $this->markTestSkipped(
        "Instance '$instance' has no district= set in bluebird.cfg. " .
        'Run against an instance that does (e.g. training1/training2/example).'
      );
    }

    // Replicate processDistrictExclude()'s own instance-resolution loop
    // (PrintExportReport.php::processDistrictExclude) so we know in advance
    // which database it will target for this district ID.
    $bbFullConfig = get_bluebird_config();
    $resolvedDb = NULL;
    foreach ($bbFullConfig as $group => $details) {
      if (!empty($group) && strpos($group, 'instance:') !== FALSE
        && ($details['district'] ?? NULL) == $districtID
      ) {
        $resolvedDb = ($bbFullConfig['globals']['db.civicrm.prefix'] ?? '') . $details['db.basename'];
        break;
      }
    }

    $currentDb = CRM_Core_DAO::singleValueQuery('SELECT DATABASE()');
    if ($resolvedDb === NULL || $resolvedDb !== $currentDb) {
      $this->markTestSkipped(
        "district={$districtID} for instance '$instance' resolves to database " .
        ($resolvedDb ?? '(none found)') . ", not the current database ($currentDb). " .
        'This test only runs when the district lookup is self-referential, to avoid ' .
        "writing test data into a different instance's database."
      );
    }

    self::$districtID = (int) $districtID;
  }

  public function tearDown(): void {
    foreach ($this->tables as $table) {
      CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS `$table`");
    }
    foreach ($this->contactIds as $id) {
      civicrm_api3('Contact', 'delete', ['id' => $id, 'skip_undelete' => 1]);
    }
    $this->tables = [];
    $this->contactIds = [];
    parent::tearDown();
  }

  // -------------------------------------------------------------------------
  // Helpers
  // -------------------------------------------------------------------------

  /**
   * Creates a real do-not-mail contact + address. This is the "seed" record
   * processDistrictExclude() cross-references on the district side --
   * created via the real API so the actual civicrm_contact/civicrm_address
   * triggers fire and populate shadow_contact/shadow_address for real,
   * exactly as they would in production.
   */
  private function createDistrictSeed(array $contactParams, ?array $addressParams = NULL): int {
    $result = civicrm_api3('Contact', 'create', $contactParams + [
      'do_not_mail' => 1,
    ]);
    $id = (int) $result['id'];
    $this->contactIds[] = $id;

    if ($addressParams) {
      civicrm_api3('Address', 'create', $addressParams + [
        'contact_id' => $id,
        'location_type_id' => 1,
        'is_primary' => 1,
      ]);
    }

    return $id;
  }

  /**
   * Builds a source export temp table using the exact schema
   * getColumns('columns') produces in production, and inserts the given
   * rows. Row ids are caller-chosen synthetic values, not real contact ids
   * -- processDistrictExclude() only reads/writes this table by structure.
   */
  private function buildSourceTable(array $rows): string {
    $ref = new ReflectionClass(CRM_NYSS_PrintExport_Form_Task_PrintExportReport::class);
    $form = $ref->newInstanceWithoutConstructor();
    $getColumns = $ref->getMethod('getColumns');
    $getColumns->setAccessible(TRUE);
    $cFlds = $getColumns->invoke($form, 'columns');

    $tbl = 'nyss_test_export_' . uniqid();
    CRM_Core_DAO::executeQuery("CREATE TABLE `$tbl` ( $cFlds ) ENGINE=myisam");
    $this->tables[] = $tbl;

    foreach ($rows as $row) {
      $cols = implode(', ', array_keys($row));
      $vals = implode(', ', array_map(function ($v) {
        return $v === NULL ? 'NULL' : "'" . CRM_Core_DAO::escapeString((string) $v) . "'";
      }, array_values($row)));
      CRM_Core_DAO::executeQuery("INSERT INTO `$tbl` ($cols) VALUES ($vals)");
    }

    return $tbl;
  }

  private function callProcessDistrictExclude(string $tbl, $localSeedsList = 0): void {
    $ref = new ReflectionClass(CRM_NYSS_PrintExport_Form_Task_PrintExportReport::class);
    $form = $ref->newInstanceWithoutConstructor();
    $method = $ref->getMethod('processDistrictExclude');
    $method->setAccessible(TRUE);
    $method->invoke($form, self::$districtID, $tbl, $localSeedsList);
  }

  /**
   * Ids below the addExternalSeeds() id offset (+1000000000) -- isolates
   * our synthetic source rows from any real district seed-group members
   * that method may separately (and correctly) inject.
   */
  private function remainingIds(string $tbl): array {
    $dao = CRM_Core_DAO::executeQuery("SELECT id FROM `$tbl` WHERE id < 900000000 ORDER BY id");
    $ids = [];
    while ($dao->fetch()) {
      $ids[] = (int) $dao->id;
    }
    return $ids;
  }

  // -------------------------------------------------------------------------
  // Tests
  // -------------------------------------------------------------------------

  public function testExactMatchIsExcluded(): void {
    $this->createDistrictSeed(
      ['contact_type' => 'Individual', 'first_name' => 'Jane', 'last_name' => 'Smith'],
      ['street_address' => '456 Main Street', 'city' => 'Albany', 'postal_code' => '12207']
    );

    $tbl = $this->buildSourceTable([
      [
        'id' => 1001, 'contact_type' => 'Individual',
        'first_name' => 'Jane', 'last_name' => 'Smith',
        'street_address' => '456 Main Street', 'city' => 'Albany', 'postal_code' => '12207',
      ],
    ]);

    $this->callProcessDistrictExclude($tbl);

    $this->assertSame([], $this->remainingIds($tbl), 'Exact name+address+postal match should be excluded.');
  }

  public function testDifferentAddressIsNotExcluded(): void {
    $this->createDistrictSeed(
      ['contact_type' => 'Individual', 'first_name' => 'Jane', 'last_name' => 'Smith'],
      ['street_address' => '456 Main Street', 'city' => 'Albany', 'postal_code' => '12207']
    );

    $tbl = $this->buildSourceTable([
      [
        'id' => 1002, 'contact_type' => 'Individual',
        'first_name' => 'Jane', 'last_name' => 'Smith',
        'street_address' => '789 Other Ave', 'city' => 'Albany', 'postal_code' => '12207',
      ],
    ]);

    $this->callProcessDistrictExclude($tbl);

    $this->assertSame([1002], $this->remainingIds($tbl), 'Different street address should not be excluded.');
  }

  public function testDifferentNameIsNotExcluded(): void {
    $this->createDistrictSeed(
      ['contact_type' => 'Individual', 'first_name' => 'Jane', 'last_name' => 'Smith'],
      ['street_address' => '456 Main Street', 'city' => 'Albany', 'postal_code' => '12207']
    );

    $tbl = $this->buildSourceTable([
      [
        'id' => 1003, 'contact_type' => 'Individual',
        'first_name' => 'John', 'last_name' => 'Smith',
        'street_address' => '456 Main Street', 'city' => 'Albany', 'postal_code' => '12207',
      ],
    ]);

    $this->callProcessDistrictExclude($tbl);

    $this->assertSame([1003], $this->remainingIds($tbl), 'Different first name should not be excluded.');
  }

  public function testNameCaseAndPunctuationInsensitiveMatch(): void {
    $this->createDistrictSeed(
      ['contact_type' => 'Individual', 'first_name' => 'john', 'last_name' => 'Obrien'],
      ['street_address' => '100 Main St', 'city' => 'Albany', 'postal_code' => '12207']
    );

    $tbl = $this->buildSourceTable([
      [
        'id' => 1004, 'contact_type' => 'Individual',
        'first_name' => 'JOHN', 'last_name' => "O'Brien",
        'street_address' => '100 Main St', 'city' => 'Albany', 'postal_code' => '12207',
      ],
    ]);

    $this->callProcessDistrictExclude($tbl);

    $this->assertSame([], $this->remainingIds($tbl), 'BB_NORMALIZE should make case/apostrophe differences match.');
  }

  public function testAddressAbbreviationInsensitiveMatch(): void {
    $this->createDistrictSeed(
      ['contact_type' => 'Individual', 'first_name' => 'Alice', 'last_name' => 'Green'],
      ['street_address' => '50 N Broadway', 'city' => 'Albany', 'postal_code' => '12207']
    );

    $tbl = $this->buildSourceTable([
      [
        'id' => 1005, 'contact_type' => 'Individual',
        'first_name' => 'Alice', 'last_name' => 'Green',
        'street_address' => '50 North Broadway', 'city' => 'Albany', 'postal_code' => '12207',
      ],
    ]);

    $this->callProcessDistrictExclude($tbl);

    $this->assertSame([], $this->remainingIds($tbl), 'BB_NORMALIZE_ADDR should treat "North" and "N" as equivalent.');
  }

  public function testOrganizationMatchIsExcluded(): void {
    $this->createDistrictSeed(
      ['contact_type' => 'Organization', 'organization_name' => 'Acme Corp'],
      ['street_address' => '1 Industrial Way', 'city' => 'Troy', 'postal_code' => '12180']
    );

    $tbl = $this->buildSourceTable([
      [
        'id' => 1006, 'contact_type' => 'Organization',
        'organization_name' => 'Acme Corp',
        'street_address' => '1 Industrial Way', 'city' => 'Troy', 'postal_code' => '12180',
      ],
    ]);

    $this->callProcessDistrictExclude($tbl);

    $this->assertSame([], $this->remainingIds($tbl), 'Matching organization_name + address should be excluded.');
  }

  public function testLocalSeedIsNeverExcludedEvenIfMatched(): void {
    $this->createDistrictSeed(
      ['contact_type' => 'Individual', 'first_name' => 'Jane', 'last_name' => 'Smith'],
      ['street_address' => '456 Main Street', 'city' => 'Albany', 'postal_code' => '12207']
    );

    $tbl = $this->buildSourceTable([
      [
        'id' => 1007, 'contact_type' => 'Individual',
        'first_name' => 'Jane', 'last_name' => 'Smith',
        'street_address' => '456 Main Street', 'city' => 'Albany', 'postal_code' => '12207',
      ],
    ]);

    // 1007 matches the seed exactly, but is itself a protected local seed --
    // it must survive the exclusion delete.
    $this->callProcessDistrictExclude($tbl, '1007');

    $this->assertSame([1007], $this->remainingIds($tbl), 'A local seed contact must not be deleted even if it matches a district exclusion.');
  }

}
