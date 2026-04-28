<?php
declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * Base class for integration tests that require a real DB connection.
 *
 * Provides:
 *   - A shared \mysqli connection (opened once per test class).
 *   - createContact() helper for creating fixture contacts via API,
 *     with automatic teardown registration.
 *   - tearDown() that deletes all registered fixture contacts and any
 *     additional IDs registered via $this->trackForCleanup().
 *
 * Raw mysqli queries bypass CiviCRM's transaction wrapper, so we do
 * explicit DELETE cleanup rather than relying on transaction rollback.
 *
 * @group integration
 */
abstract class CRM_NYSS_PrintImport_Integration_IntegrationTestCase extends TestCase {

  /** @var \mysqli Shared connection, opened once per subclass. */
  protected static \mysqli $conn;

  /** @var int[] Contact IDs to DELETE in tearDown(). */
  private array $contactsToDelete = [];

  /** @var array<string, int[]> Extra table => IDs to DELETE in tearDown(). */
  private array $rowsToDelete = [];

  // ---------------------------------------------------------------------------
  // Connection lifecycle
  // ---------------------------------------------------------------------------

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    $bbcfg        = get_bluebird_instance_config();
    static::$conn = CRM_NYSS_PrintImport_Utils::getConnection($bbcfg);
    if (getenv('NYSS_DEBUG')) {
      CRM_NYSS_PrintImport_Utils::$debug = TRUE;
    }
  }

  public static function tearDownAfterClass(): void {
    if (isset(static::$conn)) {
      mysqli_close(static::$conn);
    }
    parent::tearDownAfterClass();
  }

  // ---------------------------------------------------------------------------
  // Teardown
  // ---------------------------------------------------------------------------

  protected function tearDown(): void {
    // Raw SQL deletes run FIRST, before BAO touches civicrm_contact.
    //
    // Many block tables (address, email, phone, entity_tag, constituent_info,
    // etc.) have AFTER DELETE triggers that do:
    //   UPDATE civicrm_contact SET modified_date = ... WHERE id = OLD.contact_id
    // If BAO's deleteContact() modifies civicrm_contact first and then removes
    // these rows as part of the same operation, MySQL rejects the trigger with
    // "Can't update table already used by statement that invoked trigger".
    // Deleting all such rows via separate raw SQL statements while the contact
    // row is still untouched lets each trigger's UPDATE complete cleanly.
    // The ON DELETE CASCADE on civicrm_value_district_information_7 fires when
    // addresses are deleted, so district info rows are removed for free.
    foreach ($this->rowsToDelete as $table => $ids) {
      if (empty($ids)) {
        continue;
      }
      $safe = implode(',', array_map('intval', $ids));
      mysqli_query(static::$conn, "DELETE FROM {$table} WHERE id IN ({$safe})");
    }

    // Delete block records for each fixture contact via raw SQL before BAO runs.
    // Every table below has an AFTER DELETE trigger that does:
    //   UPDATE civicrm_contact SET modified_date = CURRENT_TIMESTAMP WHERE id = ...
    // When BAO's deleteContact() modifies civicrm_contact and then tries to
    // remove these rows as part of the same operation, MySQL rejects the trigger
    // with "Can't update table already used by statement that invoked trigger".
    // Deleting them first — as separate statements while the contact row is still
    // untouched — lets each trigger's UPDATE complete cleanly before BAO runs.
    // Rows already removed in step 1 are a no-op here.
    foreach ($this->contactsToDelete as $contactId) {
      $cid = (int) $contactId;
      // civicrm_address: ON DELETE CASCADE removes district_information_7 rows.
      mysqli_query(static::$conn, "DELETE FROM civicrm_address WHERE contact_id = {$cid}");
      mysqli_query(static::$conn, "DELETE FROM civicrm_email WHERE contact_id = {$cid}");
      mysqli_query(static::$conn, "DELETE FROM civicrm_phone WHERE contact_id = {$cid}");
      mysqli_query(static::$conn, "DELETE FROM civicrm_entity_tag WHERE entity_table = 'civicrm_contact' AND entity_id = {$cid}");
      mysqli_query(static::$conn, "DELETE FROM civicrm_value_constituent_information_1 WHERE entity_id = {$cid}");
    }

    // Hard-delete the contacts directly. All block records with AFTER DELETE
    // triggers have been removed above, so no trigger conflict occurs. The
    // civicrm_contact_after_delete trigger only logs to the audit table and
    // deletes from shadow_contact — it does not UPDATE civicrm_contact.
    // We use raw SQL rather than BAO::deleteContact() because BAO may require
    // a CiviCRM session context that is not guaranteed in integration tests.
    foreach ($this->contactsToDelete as $contactId) {
      $cid = (int) $contactId;
      mysqli_query(static::$conn, "DELETE FROM civicrm_contact WHERE id = {$cid}");
    }

    $this->contactsToDelete = [];
    $this->rowsToDelete     = [];

    parent::tearDown();
  }

  // ---------------------------------------------------------------------------
  // Fixture helpers
  // ---------------------------------------------------------------------------

  /**
   * Create an Individual contact via APIv3 and register it for teardown.
   *
   * @param array $params  APIv3 contact.create params (merged with defaults).
   * @return int  The new contact ID.
   */
  protected function createContact(array $params = []): int {
    $defaults = [
      'contact_type' => 'Individual',
      'first_name'   => 'Test',
      'last_name'    => 'Contact' . uniqid(),
    ];
    $result = civicrm_api3('contact', 'create', array_merge($defaults, $params));
    $id     = (int) $result['id'];
    $this->contactsToDelete[] = $id;
    return $id;
  }

  /**
   * Register a contact ID for deletion in tearDown().
   *
   * Use this when a contact was created outside of createContact() — e.g. by
   * the import pipeline itself — and still needs to be cleaned up.
   *
   * @param int $contactId
   */
  protected function registerContactForCleanup(int $contactId): void {
    $this->contactsToDelete[] = $contactId;
  }

  /**
   * Register a row in an arbitrary table for deletion in tearDown().
   *
   * Use this for rows (notes, addresses, tags, groups, etc.) that are not
   * automatically cleaned up by deleteContact().
   *
   * @param string $table  Table name (e.g. 'civicrm_note').
   * @param int    $id     Row ID.
   */
  protected function trackForCleanup(string $table, int $id): void {
    $this->rowsToDelete[$table][] = $id;
  }

}
