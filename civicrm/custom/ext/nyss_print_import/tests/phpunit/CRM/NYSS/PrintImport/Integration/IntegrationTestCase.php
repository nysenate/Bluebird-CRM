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
    // The civicrm_address_after_delete trigger does:
    //   UPDATE civicrm_contact SET modified_date = ... WHERE id = OLD.contact_id
    // If BAO's deleteContact() runs first it puts civicrm_contact in a
    // modified state; any subsequent address DELETE then fires the trigger
    // which tries to UPDATE civicrm_contact again — MySQL rejects that with
    // "Can't update table already used by statement that invoked trigger".
    // Deleting addresses via raw SQL while the contact still exists avoids
    // the conflict entirely. The ON DELETE CASCADE on district_information_7
    // fires here too, so tracked district info rows are cleaned up for free.
    foreach ($this->rowsToDelete as $table => $ids) {
      if (empty($ids)) {
        continue;
      }
      $safe = implode(',', array_map('intval', $ids));
      mysqli_query(static::$conn, "DELETE FROM {$table} WHERE id IN ({$safe})");
    }

    // Delete block records (email, phone) for each fixture contact via raw SQL
    // before BAO runs. The civicrm_email_after_delete and _phone_after_delete
    // triggers also do UPDATE civicrm_contact SET modified_date = ..., so the
    // same trigger conflict applies as with addresses.
    foreach ($this->contactsToDelete as $contactId) {
      $cid = (int) $contactId;
      mysqli_query(static::$conn, "DELETE FROM civicrm_email WHERE contact_id = {$cid}");
      mysqli_query(static::$conn, "DELETE FROM civicrm_phone WHERE contact_id = {$cid}");
    }

    // Delete fixture contacts after their block records are gone.
    foreach ($this->contactsToDelete as $contactId) {
      try {
        CRM_Contact_BAO_Contact::deleteContact($contactId, FALSE, TRUE);
      }
      catch (Exception $e) {
        // Best-effort; if the contact was already deleted by the test, ignore.
      }
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
