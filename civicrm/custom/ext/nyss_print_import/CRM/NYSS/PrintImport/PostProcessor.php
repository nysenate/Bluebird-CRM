<?php
declare(strict_types = 1);

/**
 * Post-import processing methods for the NYSS Print Import Legacy extension.
 *
 * Called after the main per-record import loop to handle operations that
 * require the full set of imported contact IDs or that operate on related
 * records (notes, tags, groups, addresses).
 *
 * Refactored from modules/nyss_io/nyss_io.module (_processCurrentEmployer,
 * _processNote, _processTags, _processGroup, _processEmailImport,
 * _processAddressDelete).
 */
class CRM_NYSS_PrintImport_PostProcessor {

  /** @var \mysqli */
  private \mysqli $conn;

  /** @var bool When true, no database changes are made. */
  private bool $dryrun;

  public function __construct(\mysqli $conn, bool $dryrun) {
    $this->conn   = $conn;
    $this->dryrun = $dryrun;
  }

  /**
   * Link the imported contact to an existing or newly-created employer org.
   *
   * Looks up an org contact by organization_name. If none exists, creates one.
   * Then inserts the "Employee of" relationship and updates the contact's
   * employer_id cache — both via raw mysqli to avoid conflicts with the
   * surrounding transaction wrapper.
   *
   * No-ops when $l['current_employer'] is empty.
   *
   * Refactored from _processCurrentEmployer() (line 1595).
   *
   * @param array $l Import record, passed by reference.
   */
  public function processCurrentEmployer(array &$l): void {
    if (!array_key_exists('current_employer', $l) || empty($l['current_employer'])) {
      return;
    }

    $employer_id   = NULL;
    $employer_name = NULL;

    // Look up existing org by name
    $org_name = mysqli_real_escape_string($this->conn, $l['current_employer']);
    $sql = "
      SELECT id, organization_name
      FROM civicrm_contact
      WHERE organization_name = '{$org_name}'
        AND contact_type = 'Organization'
        AND is_deleted = 0
      LIMIT 1
    ";
    $result = mysqli_query($this->conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
      $employer_id   = $row['id'];
      $employer_name = $row['organization_name'];
      mysqli_free_result($result);
    }
    else {
      // Org not found — create it
      try {
        $employer_name = CRM_NYSS_PrintImport_Handler::convertProperCase($l['current_employer']);
        $new_org       = civicrm_api3('contact', 'create', [
          'contact_type'      => 'Organization',
          'organization_name' => $employer_name,
        ]);
        $employer_id = $new_org['id'];
      }
      catch (CiviCRM_API3_Exception $e) {
        Civi::log()->debug('processCurrentEmployer: org create failed', ['e' => $e]);
      }
    }

    if ($this->dryrun || empty($employer_id)) {
      CRM_NYSS_PrintImport_Utils::out('debug', "{$employer_name}::{$employer_id}", TRUE);
      return;
    }

    // Get the "Employee of" relationship type ID
    $rel_type_id = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType',
      'Employee of', 'id', 'name_a_b');

    if (!$rel_type_id) {
      return;
    }

    try {
      // Insert relationship directly — avoids lock wait timeouts from BAO/API
      // under the surrounding transaction wrapper.
      $contact_id  = (int) $l['id'];
      $employer_id = (int) $employer_id;
      $rel_type_id = (int) $rel_type_id;

      mysqli_query($this->conn, "
        INSERT IGNORE INTO civicrm_relationship
          (relationship_type_id, contact_id_a, contact_id_b, is_active)
        VALUES
          ({$rel_type_id}, {$contact_id}, {$employer_id}, 1)
      ");

      // Update employer_id cache on the contact record
      $escaped_name = mysqli_real_escape_string($this->conn, $employer_name);
      mysqli_query($this->conn, "
        UPDATE civicrm_contact
        SET employer_id = {$employer_id}, organization_name = '{$escaped_name}'
        WHERE id = {$contact_id}
      ");
    }
    catch (Exception $e) {
      Civi::log()->debug('processCurrentEmployer: relationship insert failed', ['e' => $e]);
    }
  }

  /**
   * Insert a note for the imported contact.
   *
   * Only acts when $l['note'] is set and non-empty. Inserted via raw mysqli
   * to avoid conflicts with the surrounding transaction wrapper.
   *
   * Refactored from _processNote() (line 1681).
   *
   * @param array $l Import record, passed by reference.
   */
  public function processNote(array &$l): void {
    if (!array_key_exists('note', $l)) {
      CRM_NYSS_PrintImport_Utils::out('debug', "processNote: skipping — note field not present in record.", TRUE);
      return;
    }
    if (empty($l['note'])) {
      CRM_NYSS_PrintImport_Utils::out('debug', "processNote: skipping — note field is empty.", TRUE);
      return;
    }

    $contact_id = (int) $l['id'];
    $note       = mysqli_real_escape_string($this->conn, $l['note']);
    $date       = date('Ymd');

    CRM_NYSS_PrintImport_Utils::out('debug', "processNote: contact_id={$contact_id}", TRUE);

    if ($this->dryrun) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "dry run: would insert note for contact {$contact_id}: {$l['note']}", TRUE);
      return;
    }

    mysqli_query($this->conn, "
      INSERT INTO civicrm_note
        (entity_table, entity_id, note, contact_id, modified_date, subject, privacy)
      VALUES
        ('civicrm_contact', {$contact_id}, '{$note}', {$contact_id}, '{$date}', 'Import Note', 0)
    ");
  }

  /**
   * Assign keyword and petition tags to imported contacts.
   *
   * Called once per tag type after the main loop. $tag_contacts is keyed by
   * tag name, with each value being an array of contact IDs. Tags are looked
   * up by name under the given $type parent; created if not found. Contact
   * assignments are bulk-inserted in batches of BATCHSIZETAG.
   *
   * Refactored from _processTags() (line 1715).
   *
   * @param array  $tag_contacts Tag name => contact ID array map.
   * @param string $type         Parent tag name (e.g. 'Keywords', 'Website Petitions').
   */
  public function processTags(array $tag_contacts, string $type = 'Keywords'): void {
    if (empty($tag_contacts)) {
      return;
    }

    $parent_id = $this->findParentTagId($type);
    if (empty($parent_id)) {
      CRM_NYSS_PrintImport_Utils::out('debug', "processTags: unable to find parent tag for type: {$type}", TRUE);
      return;
    }

    if ($this->dryrun) {
      CRM_NYSS_PrintImport_Utils::out('debug', "dry run: tags to be processed for {$type}:", TRUE);
      CRM_NYSS_PrintImport_Utils::out('debug', $tag_contacts, TRUE);
      return;
    }

    CRM_NYSS_PrintImport_Utils::out('debug', "processing tags for {$type}...", TRUE);
    $tag_start = time();

    foreach ($tag_contacts as $tag => $contacts) {
      $tag_id = $this->findOrCreateTag($tag, (int) $parent_id);
      if (empty($tag_id)) {
        continue;
      }

      // Bulk-insert entity_tag rows in batches
      $rows      = [];
      $batch_num = 0;
      $total     = count($contacts);

      foreach ($contacts as $i => $contact_id) {
        $rows[] = "('civicrm_contact', {$contact_id}, {$tag_id})";

        $is_last_row   = ($i === $total - 1);
        $batch_is_full = ($i > 0 && $i % CRM_NYSS_PrintImport_Form_Import::BATCHSIZETAG === 0);

        if ($batch_is_full || $is_last_row) {
          $batch_start = time();
          CRM_Core_DAO::executeQuery(
            'INSERT IGNORE INTO civicrm_entity_tag (entity_table, entity_id, tag_id) VALUES ' . implode(', ', $rows)
          );
          $rows = [];
          $batch_num++;
          CRM_NYSS_PrintImport_Utils::out('debug',
            "{$type} tag: {$tag} | batch: {$batch_num} | record: {$i} | time: " . (time() - $batch_start) . "s", TRUE);
        }
      }
    }

    CRM_NYSS_PrintImport_Utils::out('debug',
      "tag processing time for {$type}: " . (time() - $tag_start) . "s", TRUE);
  }

  /**
   * Find a tag by name under a given parent, creating it if it doesn't exist.
   *
   * Results are cached in-memory for the lifetime of this PostProcessor
   * instance to avoid redundant queries across processTags() calls.
   *
   * @param string $name      Tag name.
   * @param int    $parent_id Parent tag ID.
   * @return int|null Tag ID, or null on failure.
   */
  private function findOrCreateTag(string $name, int $parent_id): ?int {
    $cache_key = "{$parent_id}:{$name}";
    if (isset($this->tag_cache[$cache_key])) {
      return $this->tag_cache[$cache_key];
    }

    $tag_id = CRM_Core_DAO::singleValueQuery(
      "SELECT id FROM civicrm_tag WHERE name = %1 AND parent_id = %2",
      [1 => [$name, 'String'], 2 => [$parent_id, 'Positive']]
    );

    if (empty($tag_id)) {
      CRM_NYSS_PrintImport_Utils::out('debug', "creating tag: {$name}", TRUE);
      try {
        CRM_Core_DAO::executeQuery(
          "INSERT INTO civicrm_tag (name, label, parent_id, is_selectable, used_for, created_id, created_date)
           VALUES (%1, %1, %2, 1, 'civicrm_contact,civicrm_activity,civicrm_case', 1, NOW())",
          [1 => [$name, 'String'], 2 => [$parent_id, 'Positive']]
        );
        $tag_id = CRM_Core_DAO::singleValueQuery(
          "SELECT id FROM civicrm_tag WHERE name = %1 AND parent_id = %2",
          [1 => [$name, 'String'], 2 => [$parent_id, 'Positive']]
        );
      }
      catch (CRM_Core_Exception $e) {
        Civi::log()->debug('findOrCreateTag failed', ['name' => $name, 'e' => $e]);
        return NULL;
      }
    }

    return $this->tag_cache[$cache_key] = (int) $tag_id;
  }

  /**
   * Send a summary email to the logged-in user after import completes.
   *
   * No-ops if the logged-in user's email cannot be determined.
   *
   * @param string $filename          Import filename.
   * @param int    $num_updated       Count of updated contacts.
   * @param int    $num_created       Count of newly created contacts.
   * @param int    $num_deleted       Count of deleted contacts.
   * @param int    $address_del_count Count of deleted addresses.
   * @param int    $total_lines       Total lines processed.
   */
  public function sendSummaryEmail(
    string $filename,
    int $num_updated,
    int $num_created,
    int $num_deleted,
    int $address_del_count,
    int $total_lines
  ): void {
    CRM_NYSS_PrintImport_Utils::out('debug', "processing summary email...", TRUE);

    $to_email = CRM_Core_Session::singleton()->getLoggedInContactEmail();
    if (empty($to_email)) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "sendSummaryEmail: could not determine logged-in user email.", TRUE);
      return;
    }

    $body = implode("\n", [
      "Import complete.",
      "",
      "File: {$filename}",
      "",
      "{$num_updated} updated, {$num_created} created, {$num_deleted} deleted.",
      "{$address_del_count} addresses deleted.",
      "Imported {$total_lines} lines.",
    ]);

    CRM_Utils_Mail::send([
      'from'    => 'import@cms.nysenate.gov',
      'toEmail' => $to_email,
      'subject' => 'Import Data Summary ' . date('Y-m-d H:i:s') . ' filename: ' . basename($filename),
      'text'    => $body,
    ]);
  }

  /** @var array In-memory cache for findOrCreateTag(), keyed by "parent_id:name". */
  private array $tag_cache = [];

  /** @var array In-memory cache for findParentTagId(), keyed by type name. */
  private array $parent_tag_cache = [];

  /**
   * Find the ID of a top-level parent tag by name.
   *
   * Results are cached for the lifetime of this PostProcessor instance.
   *
   * @param string $type Parent tag name (e.g. 'Keywords', 'Website Petitions').
   * @return int|null Tag ID, or null if not found.
   */
  private function findParentTagId(string $type): ?int {
    if (isset($this->parent_tag_cache[$type])) {
      return $this->parent_tag_cache[$type];
    }

    $id = CRM_Core_DAO::singleValueQuery(
      "SELECT id FROM civicrm_tag WHERE name = %1 AND parent_id IS NULL LIMIT 1",
      [1 => [$type, 'String']]
    );

    return $this->parent_tag_cache[$type] = $id ? (int) $id : NULL;
  }

  /**
   * Add imported contacts to a CiviCRM group.
   *
   * Looks up the group by title, creating it if it doesn't exist. Contacts
   * to add are determined by $add_to_group_handling: 'all' adds every imported
   * contact, 'new' adds only newly inserted contacts, 'none' skips entirely.
   *
   * Refactored from _processGroup() (line 1841).
   *
   * @param string $group_name            Title of the group to add contacts to.
   * @param string $add_to_group_handling One of 'all', 'new', or 'none'.
   * @param array  $imported_contacts     All contact IDs processed this run.
   * @param array  $new_contacts          Contact IDs that were newly inserted.
   */
  public function processGroup(string $group_name, string $add_to_group_handling, array $imported_contacts, array $new_contacts): void {
    if (empty($group_name) || $add_to_group_handling === 'none') {
      return;
    }

    CRM_NYSS_PrintImport_Utils::out('debug', "processing group...", TRUE);

    // Look up group by title
    $group    = civicrm_api3('group', 'get', ['title' => $group_name, 'sequential' => 1, 'options' => ['limit' => 1]]);
    $group_id = $group['id'] ?? NULL;

    if (empty($group_id)) {
      if ($this->dryrun) {
        CRM_NYSS_PrintImport_Utils::out('status',
          "The {$group_name} group does not exist; it will be created during import.", TRUE);
      }
      else {
        $new_group = civicrm_api3('group', 'create', ['title' => $group_name, 'is_active' => 1]);
        $group_id  = $new_group['id'];
        CRM_NYSS_PrintImport_Utils::out('status',
          "The {$group_name} group did not exist; it was created during import.", TRUE);
      }
    }

    $group_contacts = match ($add_to_group_handling) {
      'all'   => $imported_contacts,
      'new'   => $new_contacts,
      default => [],
    };

    CRM_NYSS_PrintImport_Utils::out('debug', "group handling: {$add_to_group_handling}", TRUE);
    CRM_NYSS_PrintImport_Utils::out('status',
      "Adding " . count($group_contacts) . " contacts to the {$group_name} group.", TRUE);

    if (empty($group_contacts) || $this->dryrun) {
      return;
    }

    try {
      civicrm_api3('group_contact', 'create', [
        'group_id'   => $group_id,
        'contact_id' => $group_contacts,
        'status'     => 'Added',
      ]);
    }
    catch (CRM_Core_Exception $e) {
      CRM_NYSS_PrintImport_Utils::out('debug', $e->getMessage(), TRUE);
    }
  }

  /**
   * Add newly imported contacts to the Email Only group and set do_not_mail.
   *
   * Looks up the 'Email_Only' group by name, creating it if it doesn't exist.
   * Adds all $new_contacts to the group and sets do_not_mail = 1 on each
   * contact via a bulk UPDATE.
   *
   * Refactored from _processEmailImport() (line 1912).
   *
   * @param array $new_contacts Contact IDs that were newly inserted.
   */
  public function processEmailImport(array $new_contacts): void {
    CRM_NYSS_PrintImport_Utils::out('debug', "processing email only group...", TRUE);

    if (empty($new_contacts)) {
      return;
    }

    // Look up the Email Only group by name
    $email_group    = civicrm_api3('group', 'getsingle', ['name' => 'Email_Only']);
    $email_group_id = NULL;

    if ($email_group['is_error']) {
      if ($this->dryrun) {
        CRM_NYSS_PrintImport_Utils::out('status',
          "The 'Email Only' group does not exist; it will be created during import.", TRUE);
      }
      else {
        $new_group      = civicrm_api3('group', 'create', [
          'title'       => 'Email Only',
          'is_active'   => 1,
          'description' => 'Contacts in this group will be excluded from postal mailings processed through Corporate Woods, and may be excluded from internal exports using the print processing option.',
        ]);
        $email_group_id = $new_group['id'];
        CRM_NYSS_PrintImport_Utils::out('status',
          "The 'Email Only' group did not exist; it was created during import.", TRUE);
      }
    }
    else {
      $email_group_id = $email_group['id'];
    }

    CRM_NYSS_PrintImport_Utils::out('status',
      "Adding " . count($new_contacts) . " contacts to the Email Only group.", TRUE);

    if ($this->dryrun || empty($email_group_id)) {
      return;
    }

    // Add contacts to the group
    $gc_result = civicrm_api3('group_contact', 'create', [
      'group_id'   => $email_group_id,
      'contact_id' => $new_contacts,
      'status'     => 'Added',
    ]);
    if (!empty($gc_result['is_error'])) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "processEmailImport: group_contact create failed: " . $gc_result['error_message'], TRUE);
    }

    // Set do_not_mail on all new contacts
    $ids    = implode(',', array_map('intval', $new_contacts));
    $result = mysqli_query($this->conn, "UPDATE civicrm_contact SET do_not_mail = 1 WHERE id IN ({$ids})");
    if (!$result) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "processEmailImport: do_not_mail UPDATE failed: " . mysqli_error($this->conn), TRUE);
    }
  }

  /**
   * Delete address records flagged for removal during the import.
   *
   * Called once after the main loop with the list of address IDs that had
   * street_address = 'delete' in the import file.
   *
   * Refactored from _processAddressDelete() (line 1975).
   *
   * @param array $address_del Address IDs to delete.
   */
  public function processAddressDelete(array $address_del): void {
    if (empty($address_del)) {
      return;
    }

    if ($this->dryrun) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "dry run: would delete address IDs: " . implode(', ', $address_del), TRUE);
      return;
    }

    CRM_NYSS_PrintImport_Utils::out('debug', "processing addresses to be deleted...", TRUE);

    $ids    = implode(',', array_map('intval', $address_del));
    $result = mysqli_query($this->conn, "DELETE FROM civicrm_address WHERE id IN ({$ids})");
    if (!$result) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "processAddressDelete: DELETE failed: " . mysqli_error($this->conn), TRUE);
    }
  }

}
