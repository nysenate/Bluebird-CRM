<?php
declare(strict_types = 1);

/**
 * Record-level preparation methods for the NYSS Print Import Legacy extension.
 *
 * Each method receives a record array by reference and mutates it in place,
 * resolving raw CSV values to the form expected by the database.
 *
 * Refactored from modules/nyss_io/nyss_io.module.
 */
class CRM_NYSS_PrintImport_Preparer {

  /**
   * Prepare gender field as a CiviCRM gender_id integer.
   *
   * @param array $l
   *   Import record, passed by reference.
   * @param array $aGender
   *   Map of gender label => option value id (e.g. ['Male' => 2, 'Female' => 1]).
   */
  public static function fixGender(array &$l, array $aGender): void {
      if (!empty($l['gender'])) {
          $gender_key = 'gender';
      }
      elseif (!empty($l['gender_id'])) {
          $gender_key = 'gender_id';
      }

      if (!empty($gender_key)) {
          switch ($l[$gender_key]) {
              case 'M':
                  $l['gender'] = 'Male';
                  break;
              case 'F':
                  $l['gender'] = 'Female';
                  break;
          }

          $l['gender_id'] = $aGender[$l['gender']];

          //remove text value
          unset($l['gender']);
      }
  }

  /**
   * Prepare birth_date from YYYYMMDD integer format to YYYY-MM-DD string.
   * @TODO Feels a little too locked in on a specific date format. Where is this documented? How would anyone know what to do if the format changed?
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function fixBirthdate(array &$l): void {
      if (isset($l['birth_date'])) {
          $d = $l['birth_date'];

          //remove/exclude errant records
          if (!empty($d) && $d != 0 && $d != 19000101 && $d != 19010101 && $d > 19000000) {
              $l['birth_date'] = substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
          }
          else {
              $l['birth_date'] = null;
          }
      }
  }

  /**
   * Prepare location_type_id from a label string to a CiviCRM integer id.
   *
   * @param array $l
   *   Import record, passed by reference.
   * @param array $aLocType
   *   Map of location type label => id.
   */
  public static function fixLocType(array &$l, array $aLocType): void {
      if (isset($l['location_type_id']) && !is_numeric($l['location_type_id'])) {
          $lti = $l['location_type_id'];
          $l['location_type_id'] = $aLocType[$lti];
      }
  }

  /**
   * Prepare boe_date_of_registration_24 from YYYYMMDD format to YYYY-MM-DD string.
   * @TODO Again, Feels a little too locked in on a specific date format. Where is this documented? How would anyone know what to do if the format changed?
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function fixBOERegDate(array &$l): void {
      if (isset($l['boe_date_of_registration_24'])) {
          $boe_reg = $l['boe_date_of_registration_24'];

          //remove errant records
          if (!empty($boe_reg) && $boe_reg != 0 && $boe_reg != 19000101 && $boe_reg != 19010101) {
              $l['boe_date_of_registration_24'] = substr($boe_reg,0,4).'-'.substr($boe_reg,4,2).'-'.substr($boe_reg,6,2);
          }
          else {
              $l['boe_date_of_registration_24'] = null;
          }
      }
  }

  /**
   * Parse a full_name field into prefix/first/middle/last/suffix components.
   *
   * Requires files/nameparse.php to be loaded before calling.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function fixParseName(array &$l): void {
      require_once __DIR__ . '/../../../files/nameparse.php';
      $parsedValues = parse_name($l['full_name']);

      $l['prefix_id']   = $parsedValues['title'];
      $l['first_name']  = $parsedValues['first'];
      $l['middle_name'] = $parsedValues['middle'];
      $l['last_name']   = $parsedValues['last'];
      $l['suffix_id']   = $parsedValues['suffix'];

      //if parser found fname and no lname, we swap and assume it was an lname found
      if (!empty($l['first_name']) && empty($l['last_name'])) {
          $l['last_name']  = $l['first_name'];
          $l['first_name'] = '';
      }
  }

  /**
   * Prepare prefix_id from a raw string to a CiviCRM option value id.
   *
   * @param array $l
   *   Import record, passed by reference.
   * @param array $aPrefix
   *   Map of prefix label => option value id.
   * @param array $prefixMap
   *   Abbreviation-to-canonical-label map from Definitions::getPrefixes().
   */
  public static function fixPrefix(array &$l, array $aPrefix, array $prefixMap): void {
      if (isset($l['prefix_id'])) {
          $prefix_val = '';

          //if a valid value, retain
          if (array_key_exists($l['prefix_id'], $aPrefix)) {
              $prefix_val = $l['prefix_id'];
          }
          //check if in prefix map
          elseif (array_key_exists(strtoupper($l['prefix_id']), $prefixMap)) {
              $prefix_val = $prefixMap[strtoupper($l['prefix_id'])];
          }

          $l['prefix_id'] = $aPrefix[$prefix_val];
      }
  }

  /**
   * Prepare suffix_id from a raw string to a CiviCRM option value id.
   *
   * @param array $l
   *   Import record, passed by reference.
   * @param array $aSuffix
   *   Map of suffix label => option value id.
   * @param array $suffixMap
   *   Abbreviation-to-canonical-label map from Definitions::getSuffixes().
   */
  public static function fixSuffix(array &$l, array $aSuffix, array $suffixMap): void {
      if (isset($l['suffix_id'])) {
          //if a valid value, retain
          if (array_key_exists($l['suffix_id'], $aSuffix)) {
              $l['suffix_id'] = $aSuffix[$l['suffix_id']];
          }
          //check if in suffix map
          elseif (array_key_exists(strtoupper($l['suffix_id']), $suffixMap)) {
              $l['suffix_id'] = $aSuffix[$suffixMap[strtoupper($l['suffix_id'])]];
          }
          //if odd, ignore
          else {
              $l['suffix_id'] = '';
          }
      }
  }

  /**
   * Prepare state as a CiviCRM state_province_id integer.
   *
   * Handles both 'st' alias and 'state_province_id' column names.
   *
   * @param array $l
   *   Import record, passed by reference.
   * @param array $aStates
   *   Map of state abbreviation/label => state_province_id.
   */
  public static function fixState(array &$l, array $aStates): void {
      if (!empty($l['st']) && !empty($aStates[$l['st']]) ) {
          $l['state_province_id'] = $aStates[$l['st']];
          unset($l['st']); // remove alias to prevent duplicate column in INSERT - Claude
      }
      else if (!empty($l['state_province_id']) && !empty($aStates[$l['state_province_id']]) ) {
          $l['state_province_id'] = $aStates[$l['state_province_id']];
      }
      else {
          $l['state_province_id'] = "";
      }
  }

  /**
   * Prepend "Apt." to street_unit values that lack a unit-type prefix.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function fixStreetUnit(array &$l): void {
      if (isset($l['street_unit']) && $l['street_unit']) {
          $needle_found = 0;
          $unit_needles = [
              'apt',
              'unit',
              'super',
              'sup',
              'supt',
              'ste',
              'rm',
              'pvt',
              'fl',
              'bsmt',
              'lot',
          ];

          foreach ($unit_needles as $unit_needle) {
              $needle_search = stripos($l['street_unit'], $unit_needle);
              if ($needle_search !== false) {
                  $needle_found = 1;
              }
          }

          if (!$needle_found) {
              $l['street_unit'] = 'Apt. '.$l['street_unit'];
          }
      }
  }

  /**
   * Build or parse street_address from component fields.
   *
   * If street_name is present, assembles street_address from number + name + unit.
   * Otherwise parses an existing street_address string into its components.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function fixStreetAddress(array &$l): void {
      if (!empty($l['street_name'])) {
          $so = CRM_NYSS_PrintImport_Handler::convertProperCase($l['street_number'], ['skipMixed' => true]);
          $sn = CRM_NYSS_PrintImport_Handler::convertProperCase($l['street_name'], ['skipMixed' => true]);
          $su = CRM_NYSS_PrintImport_Handler::convertProperCase($l['street_unit'], ['skipMixed' => true]);
          $l['street_address'] = $so.' '.$sn;

          if ($su) {
              $l['street_address'] = $l['street_address'].', '.$su;
          }
      }
      elseif (!empty($l['street_address'])) {
          //else if we're provided street_address, send through parser
          require_once 'CRM/Core/BAO/Address.php';
          $parsedFields = CRM_Core_BAO_Address::parseStreetAddress($l['street_address']);
          //nyss_out('debug', $parsedFields, true); exit();

          $l['street_number'] = $parsedFields['street_number'];
          $l['street_name'] = $parsedFields['street_name'];
          $l['street_unit'] = $parsedFields['street_unit'];
      }
  }

  /**
   * Split street_number into numeric part and suffix (e.g. "123A" → 123, "A").
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function fixStreetNumber(array &$l): void {
      //borrowed from CRM_Core_BAO_Address::parseStreetAddress()
      if (isset($l['street_number']) && !empty($l['street_number'])) {
          $streetNumAndSuffix = $l['street_number'];
          $matches = [];
          if (preg_match('/^(\d+)/', $streetNumAndSuffix, $matches)) {
              $l['street_number'] = $matches[0];
              $suffix = preg_replace('/^(\d+)/', '', $streetNumAndSuffix);
              $l['street_number_suffix'] = trim($suffix);
              $matches = [];
          }
      }
  }



  /**
   * Stamp the record with the current import timestamp.
   *
   * Sets last_import_57 to the current datetime. Should only be called after
   * the caller has confirmed address fields are present (e.g. via
   * Importer::hasFieldsForTable).
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function prepareAddressLastImport(array &$l): void {
    $l['last_import_57'] = date('Y-m-d H:i:s');
  }

  /**
   * Set BOE-specific field values on the import record.
   *
   * Sets location_type_id to 6 (BOE), contact_source_60 to 'boe', and
   * voter_registration_status_23 to 'registered'. Called by the orchestration
   * layer when the BOE import option is enabled.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function prepareBOE(array &$l): void {
    $l['location_type_id']             = 6;
    $l['contact_source_60']            = 'boe';
    $l['voter_registration_status_23'] = 'registered';
  }

  /**
   * Set location_type_id for an address record.
   *
   * Defaults to 1 (Home) if location_type_id is not already present in the
   * record. BOE imports set location_type_id to 6 via prepareBOE() before
   * this method runs, so no BOE branch is needed here.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function prepareAddressLocationType(array &$l): void {
    if (empty($l['location_type_id'])) {
      $l['location_type_id'] = 1;
    }
  }

  /**
   * Set address_is_primary if not already present in the record.
   *
   * Checks whether the contact already has an address in the database.
   * If not, marks this address as primary.
   *
   * Called by the orchestration layer only when processing civicrm_address.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function prepareAddressPrimary(array &$l): void {
    if (array_key_exists('address_is_primary', $l)) {
      return;
    }

    if (empty($l['contact_id'])) {
      return;
    }

    $exists = CRM_Core_DAO::singleValueQuery(
      "SELECT 1 FROM civicrm_address WHERE contact_id = %1 LIMIT 1",
      [1 => [$l['contact_id'], 'Integer']]
    );

    if (!$exists) {
      $l['address_is_primary'] = 1;
    }
  }

  /**
   * Set country_id to the United States (1228) for an address record.
   *
   * Called by the orchestration layer only when processing civicrm_address.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function prepareAddressCountry(array &$l): void {
    $l['country_id'] = 1228;
  }

  /**
   * Set contact_type based on raw name fields.
   *
   * Defaults to Individual. If first_name and last_name are both empty but
   * current_employer is set, treats the record as an Organization contact and
   * populates display_name, sort_name, and organization_name accordingly.
   *
   * Does not depend on prepareContactName having run first.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function prepareContactType(array &$l): void {
    if (empty($l['first_name']) && empty($l['last_name']) && !empty($l['current_employer'])) {
      $l['contact_type']      = 'Organization';
      $l['organization_name'] = $l['current_employer'];
      $l['display_name']      = $l['current_employer'];
      $l['sort_name']         = $l['current_employer'];
      $l['current_employer']  = '';
    }
    else {
      $l['contact_type'] = 'Individual';
    }
  }

  /**
   * Build display_name and sort_name from name component fields.
   *
   * Also resolves the 'mid' alias to 'middle_name' if present.
   *
   * @param array $l
   *   Import record, passed by reference.
   * @param array $prefixes
   *   Map of id => label (e.g. [1 => 'Mr.']) — the non-flipped getCiviOptions() result.
   * @param array $suffixes
   *   Map of id => label (e.g. [5 => 'Jr.']) — the non-flipped getCiviOptions() result.
   */
  public static function prepareContactName(array &$l, array $prefixes, array $suffixes): void {
    if (!empty($l['mid'])) {
      $l['middle_name'] = $l['mid'];
    }

    $suffix  = (!empty($l['suffix_id']) && !empty($suffixes[$l['suffix_id']])) ? ', ' . $suffixes[$l['suffix_id']] : '';
    $prefix  = (!empty($l['prefix_id']) && !empty($prefixes[$l['prefix_id']])) ? $prefixes[$l['prefix_id']] . ' ' : '';
    $first   = trim(CRM_NYSS_PrintImport_Handler::convertProperCase($l['first_name'] ?? '', ['skipMixed' => true]));
    $middle  = trim(CRM_NYSS_PrintImport_Handler::convertProperCase($l['middle_name'] ?? '', ['skipMixed' => true]));
    $last    = trim(CRM_NYSS_PrintImport_Handler::convertProperCase($l['last_name'] ?? '', ['skipMixed' => true]));
    $firstMi = $middle ? $first . ' ' : $first;

    $l['display_name'] = CRM_Utils_String::stripSpaces($prefix . $firstMi . $middle . ' ' . $last . $suffix);

    if (!empty($l['display_name'])) {
      $l['sort_name'] = $last . ', ' . $firstMi . $middle . $suffix;
    }
  }

  /**
   * Set phone type, location type, and primary flag defaults for a phone record.
   *
   * If a phone value is present, sets phone_type_id to 1 (Phone), defaults
   * phone_location_type_id to 1 (Home), and defaults phone_is_primary to 1.
   * If no phone value is present, unsets phone_contact_id so an empty record
   * is not created.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function preparePhone(array &$l): void {
    if (!empty($l['phone'])) {
      $l['phone_type_id'] = 1; //set to phone
      if (empty($l['phone_location_type_id'])) {
        $l['phone_location_type_id'] = 1; //set to home
      }
      if (empty($l['phone_is_primary'])) {
        $l['phone_is_primary'] = 1; //set to primary
      }
    }
    else {
      //if no phone value, unset the contact_id so empty record is not created; TODO: handle higher in the process
      unset($l['phone_contact_id']);
    }
  }

  /**
   * Set email location type and primary flag defaults for an email record.
   *
   * If an email value is present, defaults email_location_type_id to 1 (Home)
   * and email_is_primary to 1 if not already set.
   * If no email value is present, unsets email_contact_id so an empty record
   * is not created.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function prepareEmail(array &$l): void {
    if (!empty($l['email'])) {
      if (!$l['email_location_type_id']) {
        $l['email_location_type_id'] = 1; //set to home
      }
      if (!$l['email_is_primary']) {
        $l['email_is_primary'] = 1; //set to primary
      }
    }
    else {
      //if no email value, unset the contact_id so empty record is not created; TODO: handle higher in the process
      unset($l['email_contact_id']);
    }
  }

  /**
   * Run a general cleanup pass on a record, stripping junk values.
   *
   * @param array $l
   *   Import record, passed by reference.
   */
  public static function cleanData(array &$l): void {
      foreach ($l as $k => &$v) {
          if (is_string($v)) {
              $v = CRM_Utils_String::stripSpaces($v);
              $v = CRM_Utils_Type::escape($v, 'String');
          }
      }
  }

}
