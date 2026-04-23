<?php
declare(strict_types = 1);

/**
 * ID resolution methods for the NYSS Print Import Legacy extension.
 *
 * Each method receives an import record by reference and, if a matching DB
 * record is found, writes the relevant ID(s) back into $l so that the
 * downstream importData() call can detect MODE_UPDATE instead of MODE_INSERT.
 *
 * Refactored from modules/nyss_io/nyss_io.module (_findContact,
 * _findRelatedCustom, _findAddress, _findDistrict, _findEmail).
 */
class CRM_NYSS_PrintImport_Finder {

  /** @var \mysqli */
  private \mysqli $conn;

  public function __construct(\mysqli $conn) {
    $this->conn = $conn;
  }

  /**
   * Run a dedupe query to find an existing contact matching the import record.
   *
   * If a match is found, sets $l['id'] to the matched contact ID so that
   * importData('civicrm_contact') will run as MODE_UPDATE.
   *
   * @param array $l      Import record, passed by reference.
   * @param int   $ruleId CiviCRM dedupe rule group ID (default 1).
   */
  public function findContact(array &$l, int $ruleId = 1): void {
    $params = [
      'civicrm_contact' => [
        'first_name'  => $l['first_name']  ?? '',
        'middle_name' => $l['middle_name'] ?? '',
        'last_name'   => $l['last_name']   ?? '',
        'suffix_id'   => $l['suffix_id']   ?? '',
        'postal_code' => $l['postal_code'] ?? '',
      ],
    ];

    if (!empty($l['street_address'])) {
      $params['civicrm_address']['street_address'] = $l['street_address'];
    }
    else {
      $params['civicrm_address']['street_address'] =
        ($l['street_number'] ?? '') . ' ' .
        ($l['street_name']   ?? '') . ' ' .
        ($l['street_unit']   ?? '');
    }

    if (!empty($l['email'])) {
      $params['civicrm_email']['email'] = $l['email'];
    }

    $params = CRM_Dedupe_Finder::formatParams($params, 'Individual');
    $params['check_permission'] = 0;

    $ruleTitle = $this->getRuleTitle($ruleId);

    $o           = new stdClass();
    $o->title    = $ruleTitle;
    $o->params   = $params;
    $o->noRules  = FALSE;
    $tableQueries = [];
    dedupe_civicrm_dupeQuery($o, 'table', $tableQueries);

    $innerSql = $tableQueries['civicrm.custom.5'];
    $sql = "
      SELECT contact.id
      FROM civicrm_contact AS contact
      JOIN ({$innerSql}) AS dupes ON dupes.id1 = contact.id
      WHERE contact.is_deleted = 0
      ORDER BY contact.id ASC
    ";

    $result = mysqli_query($this->conn, $sql);
    if (!$result) {
      CRM_NYSS_PrintImport_Utils::out('debug',
        'findContact query failed: ' . mysqli_error($this->conn), FALSE);
      return;
    }

    $dupeIds = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $dupeIds[] = $row['id'];
    }
    mysqli_free_result($result);

    if (!empty($dupeIds)) {
      if (count($dupeIds) > 1) {
        CRM_NYSS_PrintImport_Utils::out('debug',
          'findContact: multiple matches found (' . implode(', ', $dupeIds) . '); using lowest ID ' . $dupeIds[0], FALSE);
      }
      $l['id'] = $dupeIds[0];
    }
  }

  /**
   * Find the constituent information record for an existing contact.
   *
   * Requires $l['id'] (contact ID) to be set. If a matching row exists in
   * civicrm_value_constituent_information_1, sets $l['constinfo_id'].
   *
   * @param array $l Import record, passed by reference.
   */
  public function findRelatedCustom(array &$l): void {
    if (empty($l['id'])) {
      return;
    }

    $contactId = (int) $l['id'];
    $sql = "
      SELECT id AS constinfo_id
      FROM civicrm_value_constituent_information_1
      WHERE entity_id = {$contactId}
    ";

    $result = mysqli_query($this->conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
      $l['constinfo_id'] = $row['constinfo_id'];
    }
    if ($result) {
      mysqli_free_result($result);
    }
  }

  /**
   * Find an existing address record matching the import record's address fields.
   *
   * Only runs when $l['id'] is set and $l['address_id'] is not already known.
   * On match, sets $l['address_id'] and $l['address_is_primary'] from the DB
   * row (preserving the existing primary flag rather than overwriting it).
   *
   * @param array $l Import record, passed by reference.
   */
  public function findAddress(array &$l): void {
    if (empty($l['id']) || !empty($l['address_id'])) {
      return;
    }

    $contactId    = (int) $l['id'];
    $streetNumber = mysqli_real_escape_string($this->conn, $l['street_number'] ?? '');
    $streetName   = mysqli_real_escape_string($this->conn, $l['street_name']   ?? '');
    $city         = mysqli_real_escape_string($this->conn, $l['city']          ?? '');
    $postalCode   = mysqli_real_escape_string($this->conn, $l['postal_code']   ?? '');

    $sql = "
      SELECT id AS address_id, is_primary AS address_is_primary
      FROM civicrm_address
      WHERE contact_id   = {$contactId}
        AND street_number = '{$streetNumber}'
        AND street_name   = '{$streetName}'
        AND city          = '{$city}'
        AND postal_code   = '{$postalCode}'
    ";

    $result = mysqli_query($this->conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
      $l['address_id']         = $row['address_id'];
      $l['address_is_primary'] = $row['address_is_primary'];
    }
    if ($result) {
      mysqli_free_result($result);
    }
  }

  /**
   * Find the district information record for an existing address.
   *
   * Only runs when $l['address_id'] is set and $l['districtinfo_id'] is not.
   * On match, sets $l['districtinfo_id'].
   *
   * @param array $l Import record, passed by reference.
   */
  public function findDistrict(array &$l): void {
    if (empty($l['address_id']) || !empty($l['districtinfo_id'])) {
      return;
    }

    $addressId = (int) $l['address_id'];
    $sql = "
      SELECT id AS districtinfo_id
      FROM civicrm_value_district_information_7
      WHERE entity_id = {$addressId}
    ";

    $result = mysqli_query($this->conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        print_r("GOT HERE FROM" .  debug_backtrace()[1]['function']);
        print_r($row);
      $l['districtinfo_id'] = $row['districtinfo_id'];
    }
    if ($result) {
      mysqli_free_result($result);
    }
  }

  /**
   * Find an existing email record matching the import record's email address.
   *
   * Only runs when $l['id'], $l['email'] are set and $l['email_id'] is not.
   * On match, sets $l['email_id'].
   *
   * @param array $l Import record, passed by reference.
   */
  public function findEmail(array &$l): void {
    if (empty($l['id']) || empty($l['email']) || !empty($l['email_id'])) {
      return;
    }

    $contactId = (int) $l['id'];
    $email     = mysqli_real_escape_string($this->conn, $l['email']);
    $sql = "
      SELECT id AS email_id
      FROM civicrm_email
      WHERE email = '{$email}' AND contact_id = {$contactId}
    ";

    $result = mysqli_query($this->conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
      $l['email_id'] = $row['email_id'];
    }
    if ($result) {
      mysqli_free_result($result);
    }
  }

  /**
   * Fetch and cache the title of a dedupe rule group by ID.
   *
   * @param int $ruleId
   * @return string|null
   */
  private function getRuleTitle(int $ruleId): ?string {
    static $cache = [];
    if (!isset($cache[$ruleId])) {
      $cache[$ruleId] = CRM_Core_DAO::singleValueQuery(
        'SELECT title FROM civicrm_dedupe_rule_group WHERE id = %1',
        [1 => [$ruleId, 'Integer']]
      );
    }
    return $cache[$ruleId];
  }

}
