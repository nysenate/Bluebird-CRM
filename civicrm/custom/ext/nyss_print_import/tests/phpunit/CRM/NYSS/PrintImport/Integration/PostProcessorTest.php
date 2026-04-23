<?php
declare(strict_types = 1);

require_once __DIR__ . '/IntegrationTestCase.php';

/**
 * Integration tests for CRM_NYSS_PrintImport_PostProcessor.
 *
 * Each test creates its own fixture contacts and cleans up after itself.
 * Raw mysqli is used in assertions because PostProcessor itself uses raw
 * mysqli — we verify what actually landed in the database.
 *
 * @group integration
 */
class CRM_NYSS_PrintImport_Integration_PostProcessorTest
  extends CRM_NYSS_PrintImport_Integration_IntegrationTestCase {

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  private function makeProcessor(bool $dryrun = FALSE): CRM_NYSS_PrintImport_PostProcessor {
    return new CRM_NYSS_PrintImport_PostProcessor(static::$conn, $dryrun);
  }

  /**
   * Fetch all notes for a contact, ordered by id DESC.
   */
  private function fetchNotes(int $contactId): array {
    $result = mysqli_query(static::$conn,
      "SELECT * FROM civicrm_note WHERE entity_table = 'civicrm_contact' AND entity_id = {$contactId} ORDER BY id DESC"
    );
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[] = $row;
    }
    mysqli_free_result($result);
    return $rows;
  }

  // ---------------------------------------------------------------------------
  // processNote
  // ---------------------------------------------------------------------------

  public function testProcessNoteInsertsRow(): void {
    $contactId = $this->createContact();
    $l = ['id' => $contactId, 'note' => 'Integration test note'];

    $this->makeProcessor()->processNote($l);

    $notes = $this->fetchNotes($contactId);
    $this->assertCount(1, $notes);
    $this->assertSame('Integration test note', $notes[0]['note']);
    $this->assertSame('Import Note', $notes[0]['subject']);
    $this->assertSame((string) $contactId, $notes[0]['entity_id']);

    $this->trackForCleanup('civicrm_note', (int) $notes[0]['id']);
  }

  public function testProcessNoteNoOpWhenNoteAbsent(): void {
    $contactId = $this->createContact();
    $l = ['id' => $contactId];

    $this->makeProcessor()->processNote($l);

    $this->assertCount(0, $this->fetchNotes($contactId));
  }

  public function testProcessNoteNoOpWhenNoteEmpty(): void {
    $contactId = $this->createContact();
    $l = ['id' => $contactId, 'note' => ''];

    $this->makeProcessor()->processNote($l);

    $this->assertCount(0, $this->fetchNotes($contactId));
  }

  public function testProcessNoteDryRunDoesNotInsert(): void {
    $contactId = $this->createContact();
    $l = ['id' => $contactId, 'note' => 'Dry run note'];

    // Confirm the note is non-empty and would be inserted without dryrun.
    $this->makeProcessor(FALSE)->processNote($l);
    $notes = $this->fetchNotes($contactId);
    $this->assertCount(1, $notes, 'Baseline: note should be inserted without dryrun');
    $this->trackForCleanup('civicrm_note', (int) $notes[0]['id']);

    // With dryrun on, a second call must not insert another row.
    $this->makeProcessor(TRUE)->processNote($l);
    $this->assertCount(1, $this->fetchNotes($contactId), 'Dryrun: no additional note should be inserted');
  }

  // ---------------------------------------------------------------------------
  // processGroup
  // ---------------------------------------------------------------------------

  private function createGroup(string $title): int {
    $result = civicrm_api3('group', 'create', ['title' => $title, 'is_active' => 1]);
    $id = (int) $result['id'];
    $this->trackForCleanup('civicrm_group', $id);
    return $id;
  }

  private function getGroupMemberIds(int $groupId): array {
    $result = civicrm_api3('group_contact', 'get', [
      'group_id'   => $groupId,
      'status'     => 'Added',
      'sequential' => 1,
      'options'    => ['limit' => 0],
    ]);
    return array_column($result['values'], 'contact_id');
  }

  public function testProcessGroupAddsAllContacts(): void {
    $groupId    = $this->createGroup('Test Group All ' . uniqid());
    $groupTitle = civicrm_api3('group', 'getvalue', ['id' => $groupId, 'return' => 'title']);
    $contactId1 = $this->createContact();
    $contactId2 = $this->createContact();

    $this->makeProcessor()->processGroup($groupTitle, 'all', [$contactId1, $contactId2], [$contactId1]);

    $members = $this->getGroupMemberIds($groupId);
    $this->assertContains((string) $contactId1, $members);
    $this->assertContains((string) $contactId2, $members);
  }

  public function testProcessGroupAddsOnlyNewContacts(): void {
    $groupId    = $this->createGroup('Test Group New ' . uniqid());
    $groupTitle = civicrm_api3('group', 'getvalue', ['id' => $groupId, 'return' => 'title']);
    $contactId1 = $this->createContact();
    $contactId2 = $this->createContact();

    // contactId1 is "imported" (updated), contactId2 is "new"
    $this->makeProcessor()->processGroup($groupTitle, 'new', [$contactId1, $contactId2], [$contactId2]);

    $members = $this->getGroupMemberIds($groupId);
    $this->assertContains((string) $contactId2, $members, 'New contact should be in group');
    $this->assertNotContains((string) $contactId1, $members, 'Updated contact should not be in group');
  }

  public function testProcessGroupCreatesGroupWhenNotFound(): void {
    $groupTitle = 'Auto Created Group ' . uniqid();
    $contactId  = $this->createContact();

    $this->makeProcessor()->processGroup($groupTitle, 'all', [$contactId], []);

    $group = civicrm_api3('group', 'get', ['title' => $groupTitle, 'sequential' => 1]);
    $this->assertSame(1, $group['count'], 'Group should have been created');
    $groupId = (int) $group['values'][0]['id'];
    $this->trackForCleanup('civicrm_group', $groupId);

    $members = $this->getGroupMemberIds($groupId);
    $this->assertContains((string) $contactId, $members);
  }

  public function testProcessGroupNoOpWhenHandlingIsNone(): void {
    $groupId    = $this->createGroup('Test Group None ' . uniqid());
    $groupTitle = civicrm_api3('group', 'getvalue', ['id' => $groupId, 'return' => 'title']);
    $contactId  = $this->createContact();

    $this->makeProcessor()->processGroup($groupTitle, 'none', [$contactId], [$contactId]);

    $this->assertEmpty($this->getGroupMemberIds($groupId));
  }

  public function testProcessGroupDryRunDoesNotAddContacts(): void {
    $groupId    = $this->createGroup('Test Group Dryrun ' . uniqid());
    $groupTitle = civicrm_api3('group', 'getvalue', ['id' => $groupId, 'return' => 'title']);
    $contactId  = $this->createContact();

    $this->makeProcessor(TRUE)->processGroup($groupTitle, 'all', [$contactId], []);

    $this->assertEmpty($this->getGroupMemberIds($groupId), 'Dryrun: no contacts should be added');
  }

  // ---------------------------------------------------------------------------
  // processAddressDelete
  // ---------------------------------------------------------------------------

  /**
   * Create a fixture address for a contact and return its ID.
   */
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

  public function testProcessAddressDeleteRemovesRows(): void {
    $contactId = $this->createContact();
    $addressId = $this->createAddress($contactId);

    $this->assertTrue($this->addressExists($addressId), 'Baseline: address should exist before delete');

    $this->makeProcessor()->processAddressDelete([$addressId]);

    $this->assertFalse($this->addressExists($addressId), 'Address should be deleted');
  }

  public function testProcessAddressDeleteNoOpWhenEmpty(): void {
    $contactId = $this->createContact();
    $addressId = $this->createAddress($contactId);
    $this->trackForCleanup('civicrm_address', $addressId);

    $this->makeProcessor()->processAddressDelete([]);

    $this->assertTrue($this->addressExists($addressId), 'Address should still exist after no-op');
  }

  public function testProcessAddressDeleteDryRunDoesNotDelete(): void {
    $contactId = $this->createContact();
    $addressId = $this->createAddress($contactId);

    // Confirm a real delete works without dryrun.
    $this->makeProcessor(FALSE)->processAddressDelete([$addressId]);
    $this->assertFalse($this->addressExists($addressId), 'Baseline: address should be deleted without dryrun');

    // Create a second address and confirm dryrun leaves it intact.
    $addressId2 = $this->createAddress($contactId);
    $this->trackForCleanup('civicrm_address', $addressId2);

    $this->makeProcessor(TRUE)->processAddressDelete([$addressId2]);
    $this->assertTrue($this->addressExists($addressId2), 'Dryrun: address should still exist');
  }

  public function testProcessAddressDeleteNonExistentIdDoesNotThrow(): void {
    $this->makeProcessor()->processAddressDelete([999999999]);
    // No exception thrown and no rows affected is the expected outcome.
    $this->assertFalse($this->addressExists(999999999));
  }

}
