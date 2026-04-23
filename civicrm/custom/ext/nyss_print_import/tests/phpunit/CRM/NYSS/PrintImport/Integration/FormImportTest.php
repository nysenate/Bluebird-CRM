<?php
declare(strict_types = 1);

require_once __DIR__ . '/IntegrationTestCase.php';

/**
 * Exposes protected methods on CRM_NYSS_PrintImport_Form_Import for testing.
 */
class TestableFormImport extends CRM_NYSS_PrintImport_Form_Import {
  public function flagAddressDeletion(int $addressId): void {
    parent::flagAddressDeletion($addressId);
  }

  public function getFlaggedAddressDeletions(): array {
    return parent::getFlaggedAddressDeletions();
  }
}

/**
 * Integration tests for CRM_NYSS_PrintImport_Form_Import.
 *
 * @group integration
 */
class CRM_NYSS_PrintImport_Integration_FormImportTest
  extends CRM_NYSS_PrintImport_Integration_IntegrationTestCase {

  private function makeForm(): TestableFormImport {
    return new TestableFormImport();
  }

  // ---------------------------------------------------------------------------
  // flagAddressDeletion / getFlaggedAddressDeletions
  // ---------------------------------------------------------------------------

  public function testFlagAddressDeletionAddsId(): void {
    $form = $this->makeForm();
    $form->flagAddressDeletion(42);
    $this->assertSame([42], $form->getFlaggedAddressDeletions());
  }

  public function testFlagAddressDeletionAccumulatesMultipleIds(): void {
    $form = $this->makeForm();
    $form->flagAddressDeletion(1);
    $form->flagAddressDeletion(2);
    $form->flagAddressDeletion(3);
    $this->assertSame([1, 2, 3], $form->getFlaggedAddressDeletions());
  }

  public function testGetFlaggedAddressDeletionsEmptyByDefault(): void {
    $form = $this->makeForm();
    $this->assertSame([], $form->getFlaggedAddressDeletions());
  }

  public function testFlagAddressDeletionDoesNotDeduplicate(): void {
    $form = $this->makeForm();
    $form->flagAddressDeletion(99);
    $form->flagAddressDeletion(99);
    $this->assertCount(2, $form->getFlaggedAddressDeletions());
  }

  // ---------------------------------------------------------------------------
  // processAddressDelete via flagged IDs
  // ---------------------------------------------------------------------------

  private function makeProcessor(): CRM_NYSS_PrintImport_PostProcessor {
    return new CRM_NYSS_PrintImport_PostProcessor(static::$conn, FALSE);
  }

  private function createAddress(int $contactId): int {
    $result = civicrm_api3('address', 'create', [
      'contact_id'        => $contactId,
      'location_type_id'  => 1,
      'street_address'    => '123 Test St',
      'city'              => 'Albany',
      'state_province_id' => 1031,
      'postal_code'       => '12210',
    ]);
    return (int) $result['id'];
  }

  private function addressExists(int $addressId): bool {
    $result = mysqli_query(static::$conn,
      "SELECT id FROM civicrm_address WHERE id = {$addressId}"
    );
    $exists = mysqli_num_rows($result) > 0;
    mysqli_free_result($result);
    return $exists;
  }

  public function testFlaggedAddressesAreDeleted(): void {
    $contactId  = $this->createContact();
    $addressId1 = $this->createAddress($contactId);
    $addressId2 = $this->createAddress($contactId);

    $form = $this->makeForm();
    $form->flagAddressDeletion($addressId1);
    $form->flagAddressDeletion($addressId2);

    $this->makeProcessor()->processAddressDelete($form->getFlaggedAddressDeletions());

    $this->assertFalse($this->addressExists($addressId1), 'First flagged address should be deleted');
    $this->assertFalse($this->addressExists($addressId2), 'Second flagged address should be deleted');
  }

  public function testUnflaggedAddressIsNotDeleted(): void {
    $contactId         = $this->createContact();
    $flaggedAddressId  = $this->createAddress($contactId);
    $keepAddressId     = $this->createAddress($contactId);
    $this->trackForCleanup('civicrm_address', $keepAddressId);

    $form = $this->makeForm();
    $form->flagAddressDeletion($flaggedAddressId);

    $this->makeProcessor()->processAddressDelete($form->getFlaggedAddressDeletions());

    $this->assertFalse($this->addressExists($flaggedAddressId), 'Flagged address should be deleted');
    $this->assertTrue($this->addressExists($keepAddressId), 'Unflagged address should be untouched');
  }

}
