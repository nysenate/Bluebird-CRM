<?php
declare(strict_types = 1);

/**
 * Per-record import logic for the NYSS Print Import Legacy extension.
 *
 * Refactored from modules/nyss_io/nyss_io.module.
 */
class CRM_NYSS_PrintImport_Importer {

    const MODE_INSERT = 'insert';
    const MODE_UPDATE = 'update';

    const OPT_DRYRUN = 'opt_dry';
    const OPT_NULL   = 'opt_null';
    const OPT_BOE    = 'opt_boe';
    const OPT_DELETE = 'opt_delete';

  // Tables whose auto-increment IDs are not needed back in the same record's
  // processing chain — safe to accumulate and bulk-insert per batch.
  const BULK_INSERT_TABLES = [
    'civicrm_phone',
    'civicrm_email',
  ];

  /** @var \mysqli */
  private \mysqli $conn;

  /**
   * Import options. Keyed by OPT_* constants; truthy value = enabled.
   *
   * Example:
   *   [
   *     self::OPT_DRYRUN => true,   // print SQL, do not execute
   *     self::OPT_NULL   => false,  // do not null out unset fields
   *     self::OPT_BOE    => true,   // Board of Elections import mode
   *     self::OPT_DELETE => true,   // allow delete contact when delete_contact field is included
   *   ]
   *
   * @var array
   */
  private array $options;

  /** @var array Field map from Definitions::getFields(), keyed by table name. */
  private array $fields;

  /**
   * Built once from $fields; maps each table to its SELECT clause and PK info.
   * Keys per table: pk_import, pk_db, fields.
   *
   * @var array
   */
  private array $selectMap = [];

  /** @var array Bulk insert buffer, keyed by table name and column signature. */
  private array $insertBuffer = [];

  /**
   * Attempt to delete a contact record.
   *
   * The caller is responsible for checking delete_contact=1 and for skipping
   * further import processing on that record. This method only performs the
   * deletion and reports whether it succeeded.
   *
   * Returns FALSE without touching the database when:
   *   - OPT_DELETE is not enabled
   *   - No contact ID is provided
   *   - OPT_DRYRUN is set
   *
   * Any exception thrown by the underlying BAO deleteContact call is allowed
   * to propagate to the caller.
   *
   * @param array $l        Import record.
   * @param int   $line_num Current line number, used for logging.
   *
   * @return bool TRUE if the contact was deleted, FALSE if it was not.
   */
  public function deleteContact(array $l, int $line_num): bool {
    if (!$this->optOn(self::OPT_DELETE)) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "contact flagged for deletion at line {$line_num} but allow_delete is not enabled.", FALSE);
      return FALSE;
    }

    if (empty($l['id'])) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "contact flagged for deletion at line {$line_num} but no ID provided.", FALSE);
      return FALSE;
    }

    if ($this->optOn(self::OPT_DRYRUN)) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "dry run: would delete contact id {$l['id']} at line {$line_num}.", FALSE);
      return FALSE;
    }

    $deleted = CRM_Contact_BAO_Contact::deleteContact($l['id'], FALSE, TRUE);
    if ($deleted) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        "deleted contact id {$l['id']} at line {$line_num}.", FALSE);
      return TRUE;
    }

    CRM_NYSS_PrintImport_Utils::out('debug',
      "contact id {$l['id']} at line {$line_num} could not be deleted.", FALSE);
    return FALSE;
  }

  /** @var array Contact IDs successfully deleted during this import run. */
  private array $deletedContactIds = [];



  public function __construct(\mysqli $conn, array $options) {
    $this->conn    = $conn;
    $this->options = $options;
    // Build some meta data about the database fields and import columns
    $this->fields  = CRM_NYSS_PrintImport_Definitions::getFields();
    $this->buildSelectMap();
  }

  /**
   * Insert or update a single record into one CiviCRM table.
   *
   * @param string $tbl  Target table name (e.g. 'civicrm_contact').
   * @param array  $l    Import record, passed by reference. PK written back after insert.
   * @param int    $line_num    Current line number from import file iteration
   * @throws Exception
   */
  public function importData(string $tbl, array &$l, int $line_num): void {
    $mode = $this->detectImportMode($tbl, $l);

    // Apply field handlers before insert/update branching.
    $runtimeContext = ['mode' => $mode];
    foreach ($l as $key => &$data) {
      if (isset($this->fields[$tbl][$key]['handler'])) {
        $fieldContext = $this->fields[$tbl][$key]['handler_context'] ?? [];
        $context = array_merge($fieldContext, $runtimeContext);
        $data = ($this->fields[$tbl][$key]['handler'])($data, $context);
      }
    }
    unset($data);

    // No Contact ID in the import record. We will do an insert.
    if ($mode === self::MODE_INSERT) {
      $insert = $this->buildInsertFields($tbl, $l);
      if ($insert === null) {
        return;
      }
      if ($insert['keys'] && $insert['data']) {
        $sql = "INSERT INTO {$tbl} (" . implode(',', $insert['keys']) . ") VALUES (" . implode(',', $insert['data']) . ")";
        if ($this->optOn(self::OPT_DRYRUN)) {
          CRM_NYSS_PrintImport_Utils::out('debug', "inserting into {$tbl} at line {$line_num}: " . $sql, FALSE);
        }
        elseif (in_array($tbl, self::BULK_INSERT_TABLES)) {
          // Buffer this row; flushed as a multi-row INSERT just before each COMMIT.
          // IDs for these tables are not needed back in the same record's pipeline.
          $colSig = implode(',', $insert['keys']);
          if (!isset($this->insertBuffer[$tbl][$colSig])) {
            $this->insertBuffer[$tbl][$colSig] = [
              'columns' => implode(',', $insert['keys']),
              'rows'    => [],
            ];
          }
          $this->insertBuffer[$tbl][$colSig]['rows'][] = '(' . implode(',', $insert['data']) . ')';
        }
        else {
          CRM_NYSS_PrintImport_Utils::out('debug', "inserting into {$tbl} at line {$line_num}: " . $sql, FALSE);
          $result = mysqli_query($this->conn, $sql);
          if (!$result) {
            CRM_NYSS_PrintImport_Utils::out('debug', "ERROR: insert into {$tbl} failed for {$line_num}.", FALSE);
            throw new \RuntimeException("INSERT failed for {$tbl}: " . mysqli_error($this->conn) . " SQL: {$sql}");
          }
          $l[$this->getPKImportColumnName($tbl)] = mysqli_insert_id($this->conn);
        }
      }
    }
    // We've received a contact ID in the import. We will update that record.
    elseif ($mode === self::MODE_UPDATE) {
        $up = $this->buildUpdateFields($tbl, $l);
        /*
         * I've carried over this code for historical reference in case any questions arise, but I don't believe it's working or needed.
         * 1. $key = 'street_address' is wrong. It's using an assignment operator (not a comparison operator)
         * 2. When the NULL Override option is set, buildUpdateFields is already clearing geo_code fields. This is redundant.
        if ($key = 'street_address' &&
            (empty($l['geo_code_1']) || empty($l['geo_code_2']))) {
            $l['geo_code_1'] = null;
            $l['geo_code_2'] = null;
        }
        */

        // Build SQL UPDATE and execute.
        $sql_update = "UPDATE {$tbl} SET ".implode(',',$up). " WHERE {$this->fields[$tbl]['pk_db']}={$l[$this->fields[$tbl]['pk_import']]}";
        if (!$this->optOn(self::OPT_DRYRUN)) {
            CRM_NYSS_PrintImport_Utils::out('debug',"updating table {$tbl} at line {$line_num}: " . $sql_update,FALSE);
            $result = mysqli_query($this->conn, $sql_update);
            if (!$result) {
                CRM_NYSS_PrintImport_Utils::out('debug',"ERROR: update table {$tbl} failed for {$line_num}." ,FALSE);
                throw new \RuntimeException("UPDATE failed for {$tbl}: " . mysqli_error($this->conn) . " SQL: {$sql_update}");
            }
        }
    }
  }

  /**
   * Flush all buffered bulk-insert rows as multi-row INSERT statements.
   *
   * Called just before each transaction COMMIT so buffered rows land in the
   * same transaction as the civicrm_contact/address inserts they belong to.
   * Rows are grouped by column signature so every row in a given statement
   * has identical columns. Resets the buffer after flushing.
   */
  public function flushInsertBuffer(): void {
    if (empty($this->insertBuffer)) {
      return;
    }
    foreach ($this->insertBuffer as $tbl => $groups) {
      foreach ($groups as $group) {
        if (empty($group['rows'])) {
          continue;
        }
        $sql = "INSERT INTO {$tbl} ({$group['columns']}) VALUES " . implode(',', $group['rows']);
        $result = mysqli_query($this->conn, $sql);
        if (!$result) {
          throw new \RuntimeException("Bulk INSERT failed for {$tbl}: " . mysqli_error($this->conn));
        }
        CRM_NYSS_PrintImport_Utils::out('debug', "bulk inserted " . count($group['rows']) . " rows into {$tbl}", FALSE);
      }
    }
    $this->insertBuffer = [];
  }

  /**
   * Build the column and value arrays for a SQL INSERT.
   *
   * Returns null when a required field has no value, signalling the caller to
   * skip this record entirely. Otherwise returns an array with two keys:
   *   'keys' — backtick-quoted column name strings
   *   'data'  — single-quoted value strings
   *
   * Fields not defined for $tbl, or empty (and not 0), are silently excluded.
   * Handlers are expected to have already been applied before this is called.
   *
   * @param string $tbl  Target table name.
   * @param array  $l    Import record.
   * @return array{keys: string[], data: string[]}|null  Null signals skip.
   */
  private function buildInsertFields(string $tbl, array $l): ?array {
    $keys = [];
    $data = [];
    foreach ($l as $key => $value) {
      if (!isset($this->fields[$tbl][$key])) {
        continue;
      }
      // required field missing — abort this record
      if (empty($value) && $value !== 0 && $this->fields[$tbl][$key]['required']) {
        return null;
      }
      // empty but not required — skip this field
      if (empty($value) && $value !== 0) {
        continue;
      }
      $keys[] = "`{$this->fields[$tbl][$key]['fld']}`";
      $data[] = "'{$value}'";
    }
    return ['keys' => $keys, 'data' => $data];
  }

  /**
   * Build the SET clause fragments for a SQL UPDATE.
   *
   * Returns an array of strings like ["field='value'", "other=NULL"].
   * Fields absent from $l, or empty when OPT_NULL is off, are excluded.
   *
   * @param string $tbl  Target table name.
   * @param array  $l    Import record.
   * @return array
   */
  private function buildUpdateFields(string $tbl, array $l): array {
    $up = [];
    foreach ($l as $key => $data) {
      if (!isset($this->fields[$tbl][$key])) {
        continue;
      }
      $fld = $this->fields[$tbl][$key]['fld'];
      if (empty($data)) {
        if ($this->optOn(self::OPT_NULL)) {
          $up[] = "{$fld}=NULL";
        }
      }
      else {
        $up[] = "{$fld}='{$data}'";
      }
    }
    return $up;
  }

  /**
   * Returns true if the import record contains any field defined for $tbl.
   *
   * When $nullOverride is true, returns true unconditionally as long as the
   * table has at least one defined field (the caller intends to null out fields
   * regardless of what the record contains).
   *
   * @param string $tbl         Table name (must be a key in $this->fields).
   * @param array  $l           Import record.
   * @param bool   $nullOverride Whether the null/empty-field override option is on.
   */
  public function hasFieldsForTable(string $tbl, array $l, bool $nullOverride = FALSE): bool {
    if (empty($this->fields[$tbl])) {
      return FALSE;
    }
    foreach ($this->fields[$tbl] as $field => $def) {
      if (array_key_exists($field, $l) || $nullOverride) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns true if the given option is enabled.
   */
  private function optOn(string $option): bool {
    return !empty($this->options[$option]);
  }

  /**
   * Returns MODE_INSERT or MODE_UPDATE based on whether the import record has an existing PK.
   */
  public function detectImportMode(string $tbl, array $l): string {
    return empty($l[$this->getPKImportColumnName($tbl)])
      ? self::MODE_INSERT
      : self::MODE_UPDATE;
  }

  /**
   * Returns the SELECT clause for fetching an existing record from $tbl.
   */
  private function getSelectFields(string $tbl): string {
    return $this->selectMap[$tbl]['fields'];
  }

  /**
   * Returns the DB column name of the primary key for $tbl.
   */
  private function getPKField(string $tbl): string {
    return $this->selectMap[$tbl]['pk_db'];
  }

  /**
   * Returns the name of the column that holds the primary key value for $tbl.
   */
  private function getPKImportColumnName(string $tbl): string {
    return $this->selectMap[$tbl]['pk_import'];
  }

  /**
   * Grabs and stores database table/field metadata from CRM_NYSS_PrintImport_Definitions.
   * Builds per-table SELECT clause and PK field names (import alias and DB column).
   * @see CRM_NYSS_PrintImport_Definitions
   */
  private function buildSelectMap(): void {
    if (!empty($this->selectMap)) {
      return;
    }

    foreach ($this->fields as $table => $tableDetails) {
      $tmp = [];
      foreach ($tableDetails as $key => $importField) {
        $tmp[] = "`{$importField['fld']}` as '{$key}'";

        if (isset($importField['PK']) && $importField['PK']) {
          $this->selectMap[$table]['pk_import'] = $key;
          $this->selectMap[$table]['pk_db']     = $importField['fld'];
        }
      }
      $this->selectMap[$table]['fields'] = implode(',', $tmp);
    }
  }

}
