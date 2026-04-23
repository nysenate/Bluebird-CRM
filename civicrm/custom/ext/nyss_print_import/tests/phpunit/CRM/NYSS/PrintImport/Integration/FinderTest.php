<?php
declare(strict_types = 1);

require_once __DIR__ . '/IntegrationTestCase.php';

/**
 * Integration tests for CRM_NYSS_PrintImport_Finder.
 *
 * Each test creates its own fixture contacts and cleans up after itself.
 *
 * @group integration
 */
class CRM_NYSS_PrintImport_Integration_FinderTest
  extends CRM_NYSS_PrintImport_Integration_IntegrationTestCase {

  private function makeFinder(): CRM_NYSS_PrintImport_Finder {
    return new CRM_NYSS_PrintImport_Finder(static::$conn);
  }

  /**
   * Assert that the dedupe infrastructure required by findContact() is present.
   *
   * Fails the test with a descriptive message if any of the following are missing:
   *   - shadow_contact table (Bluebird persistent shadow table)
   *   - BB_NORMALIZE() MySQL function (Bluebird custom normalizer)
   *   - nyssdedupe.normalizer Civi service (gov.nysenate.dedupe extension)
   */
  private function requireDedupeInfrastructure(): void {
    // 1. shadow_contact table
    $result = mysqli_query(static::$conn, "SHOW TABLES LIKE 'shadow_contact'");
    if (mysqli_num_rows($result) === 0) {
      $this->fail('Dedupe infrastructure missing: shadow_contact table does not exist.');
    }
    mysqli_free_result($result);

    // 2. BB_NORMALIZE() MySQL function
    $result = mysqli_query(static::$conn, "SELECT BB_NORMALIZE('test') AS n");
    if (!$result) {
      $this->fail('Dedupe infrastructure missing: BB_NORMALIZE() MySQL function is not defined — ' . mysqli_error(static::$conn));
    }
    mysqli_free_result($result);

    // 3. nyssdedupe.normalizer Civi service
    if (!Civi::container()->has('nyssdedupe.normalizer')) {
      $this->fail('Dedupe infrastructure missing: nyssdedupe.normalizer service is not registered. Is gov.nysenate.dedupe enabled?');
    }
  }

  /**
   * Create a fixture address for a contact and return its ID.
   */
  private function createAddress(int $contactId, array $params = []): int {
    $defaults = [
      'contact_id'        => $contactId,
      'location_type_id'  => 1,
      'street_number'     => '123',
      'street_name'       => 'Test St',
      'street_address'    => '123 Test St',
      'city'              => 'Albany',
      'state_province_id' => 1031,
      'postal_code'       => '12210',
      'is_primary'        => 1,
    ];
    $result    = civicrm_api3('address', 'create', array_merge($defaults, $params));
    $addressId = (int) $result['id'];
    // Register for raw SQL DELETE in tearDown so MySQL's ON DELETE CASCADE
    // fires and cleans up any associated district info rows.
    $this->trackForCleanup('civicrm_address', $addressId);
    return $addressId;
  }

  /**
   * Create a fixture email for a contact and return its ID.
   */
  private function createEmail(int $contactId, string $email): int {
    $result = civicrm_api3('email', 'create', [
      'contact_id'       => $contactId,
      'email'            => $email,
      'location_type_id' => 1,
      'is_primary'       => 1,
    ]);
    return (int) $result['id'];
  }

  // ---------------------------------------------------------------------------
  // findRelatedCustom
  // ---------------------------------------------------------------------------

  private function createConstituentInfo(int $contactId): int {
    mysqli_query(static::$conn,
      "INSERT INTO civicrm_value_constituent_information_1 (entity_id) VALUES ({$contactId})"
    );
    return (int) mysqli_insert_id(static::$conn);
  }

  public function testFindRelatedCustomSetsConstinfoId(): void {
    $contactId    = $this->createContact();
    $constinfoId  = $this->createConstituentInfo($contactId);
    $this->trackForCleanup('civicrm_value_constituent_information_1', $constinfoId);

    $l = ['id' => $contactId];
    $this->makeFinder()->findRelatedCustom($l);

    $this->assertArrayHasKey('constinfo_id', $l);
    $this->assertSame((string) $constinfoId, (string) $l['constinfo_id']);
  }

  public function testFindRelatedCustomNoMatchLeavesRecordUnchanged(): void {
    $contactId = $this->createContact();
    $l         = ['id' => $contactId];

    $this->makeFinder()->findRelatedCustom($l);

    $this->assertArrayNotHasKey('constinfo_id', $l);
  }

  public function testFindRelatedCustomNoOpWhenContactIdAbsent(): void {
    $l = [];
    $this->makeFinder()->findRelatedCustom($l);
    $this->assertArrayNotHasKey('constinfo_id', $l);
  }

  // ---------------------------------------------------------------------------
  // findDistrict
  // ---------------------------------------------------------------------------

  private function createDistrictInfo(int $addressId): int {
    // Use the CiviCRM API to create the district info row so that CiviCRM's
    // own FK wiring is in place. The ON DELETE CASCADE on entity_id then
    // handles cleanup automatically when the address is deleted in tearDown,
    // so no explicit trackForCleanup is needed here.
    \Civi\Api4\Address::update(FALSE)
      ->addValue('District_Information.NY_Senate_District', 45)
      ->addWhere('id', '=', $addressId)
      ->execute();

    $result = mysqli_query(static::$conn,
      "SELECT id FROM civicrm_value_district_information_7 WHERE entity_id = {$addressId}"
    );
    if (!$result || !($row = mysqli_fetch_assoc($result))) {
      $this->fail("createDistrictInfo: no row found in district_information_7 for address_id={$addressId}");
    }
    mysqli_free_result($result);
    return (int) $row['id'];
  }

  public function testFindDistrictSetsDistrictinfoId(): void {
    $contactId      = $this->createContact();
    $addressId      = $this->createAddress($contactId);
    $districtInfoId = $this->createDistrictInfo($addressId);
    // district row is cleaned up via ON DELETE CASCADE when address is deleted

    $l = ['address_id' => $addressId];
    $this->makeFinder()->findDistrict($l);

    $this->assertArrayHasKey('districtinfo_id', $l);
    $this->assertSame((string) $districtInfoId, (string) $l['districtinfo_id']);
  }

  public function testFindDistrictNoOpWhenAddressIdAbsent(): void {
    $l = [];
    $this->makeFinder()->findDistrict($l);
    $this->assertArrayNotHasKey('districtinfo_id', $l);
  }

  public function testFindDistrictNoOpWhenDistrictinfoIdAlreadySet(): void {
    $contactId      = $this->createContact();
    $addressId      = $this->createAddress($contactId);
    $this->createDistrictInfo($addressId);

    $l = ['address_id' => $addressId, 'districtinfo_id' => 99999];
    $this->makeFinder()->findDistrict($l);

    $this->assertSame(99999, $l['districtinfo_id']);
  }

  // ---------------------------------------------------------------------------
  // findAddress
  // ---------------------------------------------------------------------------

  public function testFindAddressSetsAddressId(): void {
    $contactId = $this->createContact();
    $addressId = $this->createAddress($contactId);

    $l = [
      'id'            => $contactId,
      'street_number' => '123',
      'street_name'   => 'Test St',
      'city'          => 'Albany',
      'postal_code'   => '12210',
    ];

    $this->makeFinder()->findAddress($l);

    $this->assertSame((string) $addressId, (string) $l['address_id']);
    $this->assertSame('1', (string) $l['address_is_primary']);
  }

  public function testFindAddressNoMatchLeavesRecordUnchanged(): void {
    $contactId = $this->createContact();

    $l = [
      'id'            => $contactId,
      'street_number' => '999',
      'street_name'   => 'Nowhere Ln',
      'city'          => 'Albany',
      'postal_code'   => '00000',
    ];

    $this->makeFinder()->findAddress($l);

    $this->assertArrayNotHasKey('address_id', $l);
    $this->assertArrayNotHasKey('address_is_primary', $l);
  }

  public function testFindAddressNoOpWhenContactIdAbsent(): void {
    $l = [
      'street_number' => '123',
      'street_name'   => 'Test St',
      'city'          => 'Albany',
      'postal_code'   => '12210',
    ];

    $this->makeFinder()->findAddress($l);

    $this->assertArrayNotHasKey('address_id', $l);
  }

  public function testFindAddressNoOpWhenAddressIdAlreadySet(): void {
    $contactId = $this->createContact();
    $addressId = $this->createAddress($contactId);

    $l = [
      'id'            => $contactId,
      'address_id'    => 99999,
      'street_number' => '123',
      'street_name'   => 'Test St',
      'city'          => 'Albany',
      'postal_code'   => '12210',
    ];

    $this->makeFinder()->findAddress($l);

    // Should not overwrite the pre-existing address_id
    $this->assertSame(99999, $l['address_id']);
  }

  // ---------------------------------------------------------------------------
  // findEmail
  // ---------------------------------------------------------------------------

  public function testFindEmailSetsEmailId(): void {
    $contactId = $this->createContact();
    $emailId   = $this->createEmail($contactId, 'test@example.com');

    $l = [
      'id'    => $contactId,
      'email' => 'test@example.com',
    ];

    $this->makeFinder()->findEmail($l);

    $this->assertSame((string) $emailId, (string) $l['email_id']);
  }

  public function testFindEmailNoMatchLeavesRecordUnchanged(): void {
    $contactId = $this->createContact();

    $l = [
      'id'    => $contactId,
      'email' => 'nomatch@example.com',
    ];

    $this->makeFinder()->findEmail($l);

    $this->assertArrayNotHasKey('email_id', $l);
  }

  public function testFindEmailNoOpWhenContactIdAbsent(): void {
    $l = ['email' => 'test@example.com'];

    $this->makeFinder()->findEmail($l);

    $this->assertArrayNotHasKey('email_id', $l);
  }

  public function testFindEmailNoOpWhenEmailAbsent(): void {
    $contactId = $this->createContact();
    $l         = ['id' => $contactId];

    $this->makeFinder()->findEmail($l);

    $this->assertArrayNotHasKey('email_id', $l);
  }

  public function testFindEmailNoOpWhenEmailIdAlreadySet(): void {
    $contactId = $this->createContact();
    $this->createEmail($contactId, 'test@example.com');

    $l = [
      'id'       => $contactId,
      'email'    => 'test@example.com',
      'email_id' => 99999,
    ];

    $this->makeFinder()->findEmail($l);

    // Should not overwrite the pre-existing email_id
    $this->assertSame(99999, $l['email_id']);
  }

  // ---------------------------------------------------------------------------
  // findContact
  // ---------------------------------------------------------------------------

  public function testFindContactStrictMatchByNameAndAddress(): void {
    $this->requireDedupeInfrastructure();

    $contactId = $this->createContact([
      'first_name' => 'Jane',
      'last_name'  => 'Dedupe',
    ]);
    $addr = civicrm_api3('address', 'create', [
      'contact_id'      => $contactId,
      'location_type_id'=> 1,
      'street_address'  => '123 Main St',
      'postal_code'     => '12210',
      'is_primary'      => 1,
    ]);
    $this->trackForCleanup('civicrm_address', (int) $addr['id']);

    $l = [
      'first_name'     => 'Jane',
      'last_name'      => 'Dedupe',
      'street_address' => '123 Main St',
      'postal_code'    => '12210',
    ];

    // Rule ID 1 = Individual Strict
    $this->makeFinder()->findContact($l, 1);

    $this->assertArrayHasKey('id', $l, 'findContact should set $l[id] on a strict match');
    $this->assertSame((string) $contactId, (string) $l['id']);
  }

  public function testFindContactStrictMatchByNameAndEmail(): void {
    $this->requireDedupeInfrastructure();

    $contactId = $this->createContact([
      'first_name' => 'John',
      'last_name'  => 'Dedupe',
    ]);
    $this->createEmail($contactId, 'john.dedupe@example.com');

    $l = [
      'first_name' => 'John',
      'last_name'  => 'Dedupe',
      'email'      => 'john.dedupe@example.com',
    ];

    $this->makeFinder()->findContact($l, 1);

    $this->assertArrayHasKey('id', $l, 'findContact should set $l[id] on a strict email match');
    $this->assertSame((string) $contactId, (string) $l['id']);
  }

  public function testFindContactStrictNoMatchLeavesIdUnset(): void {
    $this->requireDedupeInfrastructure();

    $l = [
      'first_name'     => 'Nobody',
      'last_name'      => 'Thistersonnotexist',
      'street_address' => '999 Nowhere Ln',
      'postal_code'    => '00001',
    ];

    $this->makeFinder()->findContact($l, 1);

    $this->assertArrayNotHasKey('id', $l, 'findContact should not set $l[id] when no match found');
  }

  public function testFindContactMultipleMatchesReturnsLowestId(): void {
    $this->requireDedupeInfrastructure();

    $params = [
      'first_name' => 'Dupe',
      'last_name'  => 'Duplicate',
    ];
    $contactId1 = $this->createContact($params);
    $contactId2 = $this->createContact($params);

    foreach ([$contactId1, $contactId2] as $cid) {
      $addr = civicrm_api3('address', 'create', [
        'contact_id'       => $cid,
        'location_type_id' => 1,
        'street_address'   => '789 Dupe Ave',
        'postal_code'      => '12201',
        'is_primary'       => 1,
      ]);
      $this->trackForCleanup('civicrm_address', (int) $addr['id']);
    }

    $l = [
      'first_name'     => 'Dupe',
      'last_name'      => 'Duplicate',
      'street_address' => '789 Dupe Ave',
      'postal_code'    => '12201',
    ];

    $this->makeFinder()->findContact($l, 1);

    $expectedId = min($contactId1, $contactId2);
    $this->assertSame((string) $expectedId, (string) $l['id'],
      'findContact should return the lowest contact ID when multiple matches are found');
  }

  public function testFindContactFuzzyMatchByNameAndAddress(): void {
    $this->requireDedupeInfrastructure();

    $contactId = $this->createContact([
      'first_name' => 'Mary',
      'last_name'  => 'Fuzzy',
    ]);
    $addr = civicrm_api3('address', 'create', [
      'contact_id'      => $contactId,
      'location_type_id'=> 1,
      'street_address'  => '456 Elm St',
      'postal_code'     => '12206',
      'is_primary'      => 1,
    ]);
    $this->trackForCleanup('civicrm_address', (int) $addr['id']);

    $l = [
      'first_name'     => 'Mary',
      'last_name'      => 'Fuzzy',
      'street_address' => '456 Elm St',
    ];

    // Rule ID 2 = Individual Fuzzy
    $this->makeFinder()->findContact($l, 2);

    $this->assertArrayHasKey('id', $l, 'findContact should set $l[id] on a fuzzy match');
    $this->assertSame((string) $contactId, (string) $l['id']);
  }

}
