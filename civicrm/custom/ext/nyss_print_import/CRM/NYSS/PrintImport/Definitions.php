<?php
declare(strict_types = 1);

/**
 * Static field maps and lookup tables for the NYSS Print Import Legacy extension.
 *
 * Refactored from modules/nyss_io/nyss_io_definitions.inc.
 */
class CRM_NYSS_PrintImport_Definitions {

  /**
   * Field maps keyed by table name.
   *
   * Each entry maps a CSV column name to a field definition array with keys:
   *   - fld: the database column name
   *   - PK: (optional) true if this is the primary key
   *   - required: (optional) true if the field is required
   *   - handler: (optional) callable [Handler::class, 'method'] to transform the value
   *   - handler_context: (optional) initial context array merged with runtime context before calling handler
   *   - FK: (optional) foreign key descriptor with keys: table, fk_id, fk_alias
   *
   * @return array
   */
  public static function getFields(): array {
    return [
      'civicrm_contact' => [
        'id' => [
          'fld' => 'id',
          'PK' => TRUE,
        ],
        'first_name' => [
          'fld' => 'first_name',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'middle_name' => [
          'fld' => 'middle_name',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'last_name' => [
          'fld' => 'last_name',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'prefix_id' => [
          'fld' => 'prefix_id',
        ],
        'suffix_id' => [
          'fld' => 'suffix_id',
        ],
        'gender_id' => [
          'fld' => 'gender_id',
        ],
        'birth_date' => [
          'fld' => 'birth_date',
        ],
        'is_deleted' => [
          'fld' => 'is_deleted',
        ],
        'display_name' => [
          'fld' => 'display_name',
        ],
        'sort_name' => [
          'fld' => 'sort_name',
        ],
        'contact_type' => [
          'fld' => 'contact_type',
        ],
        'job_title' => [
          'fld' => 'job_title',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'organization_name' => [
          'fld' => 'organization_name',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
      ],

      'civicrm_address' => [
        'address_id' => [
          'fld' => 'id',
          'PK' => TRUE,
        ],
        'contact_id' => [
          'fld' => 'contact_id',
        ],
        'street_number' => [
          'fld' => 'street_number',
        ],
        'street_number_suffix' => [
          'fld' => 'street_number_suffix',
        ],
        'location_type_id' => [
          'fld' => 'location_type_id',
        ],
        'street_name' => [
          'fld' => 'street_name',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'street_unit' => [
          'fld' => 'street_unit',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'supplemental_address_1' => [
          'fld' => 'supplemental_address_1',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'supplemental_address_2' => [
          'fld' => 'supplemental_address_2',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'city' => [
          'fld' => 'city',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'st' => [
          'fld' => 'state_province_id',
        ],
        'state_province_id' => [
          'fld' => 'state_province_id',
        ],
        'postal_cod' => [
          'fld' => 'postal_code',
        ],
        'postal_code' => [
          'fld' => 'postal_code',
        ],
        'postal' => [
          'fld' => 'postal_code_suffix',
        ],
        'postal_code_suffix' => [
          'fld' => 'postal_code_suffix',
        ],
        'street_address' => [
          'fld' => 'street_address',
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertProperCase'],
          'handler_context' => ['skipMixed' => true],
        ],
        'country_id' => [
          'fld' => 'country_id',
        ],
        'address_is_primary' => [
          'fld' => 'is_primary',
        ],
        'geo_code_1' => [
          'fld' => 'geo_code_1',
        ],
        'geo_code_2' => [
          'fld' => 'geo_code_2',
        ],
      ],

      'civicrm_email' => [
        'email_id' => [
          'fld' => 'id',
          'PK' => TRUE,
        ],
        'email_contact_id' => [
          'fld' => 'contact_id',
        ],
        'email' => [
          'fld' => 'email',
          'required' => TRUE,
          'handler' => [CRM_NYSS_PrintImport_Handler::class, 'convertLowerCase'],
        ],
        'email_location_type_id' => [
          'fld' => 'location_type_id',
        ],
        'email_is_primary' => [
          'fld' => 'is_primary',
        ],
      ],

      'civicrm_phone' => [
        'phone_id' => [
          'fld' => 'id',
          'PK' => TRUE,
        ],
        'phone_contact_id' => [
          'fld' => 'contact_id',
        ],
        'phone' => [
          'fld' => 'phone',
          'required' => TRUE,
        ],
        'phone_location_type_id' => [
          'fld' => 'location_type_id',
        ],
        'phone_type_id' => [
          'fld' => 'phone_type_id',
        ],
        'phone_is_primary' => [
          'fld' => 'is_primary',
        ],
      ],

      'civicrm_value_district_information_7' => [
        'districtinfo_id' => [
          'fld' => 'id',
          'PK' => TRUE,
        ],
        'entity_id' => [
          'fld' => 'entity_id',
          'FK' => [
            'table' => 'civicrm_address',
            'fk_id' => 'id',
            'fk_alias' => 'address_id',
          ],
        ],
        'town_52' => [
          'fld' => 'town_52',
        ],
        'ward_53' => [
          'fld' => 'ward_53',
        ],
        'elec' => [
          'fld' => 'election_district_49',
        ],
        'election_district_49' => [
          'fld' => 'election_district_49',
        ],
        'con' => [
          'fld' => 'congressional_district_46',
        ],
        'congressional_district_46' => [
          'fld' => 'congressional_district_46',
        ],
        'ny_' => [
          'fld' => 'ny_senate_district_47',
        ],
        'ny_senate_district_47' => [
          'fld' => 'ny_senate_district_47',
        ],
        'ny_a' => [
          'fld' => 'ny_assembly_district_48',
        ],
        'ny_assembly_district_48' => [
          'fld' => 'ny_assembly_district_48',
        ],
        'scho' => [
          'fld' => 'school_district_54',
        ],
        'school_district_54' => [
          'fld' => 'school_district_54',
        ],
        'cou' => [
          'fld' => 'county_50',
        ],
        'county_50' => [
          'fld' => 'county_50',
        ],
        'last_import_57' => [
          'fld' => 'last_import_57',
        ],
      ],

      'civicrm_value_constituent_information_1' => [
        'constinfo_id' => [
          'fld' => 'id',
          'PK' => TRUE,
        ],
        'constinfo_entity_id' => [
          'fld' => 'entity_id',
          'FK' => [
            'table' => 'civicrm_contact',
            'fk_id' => 'id',
            'fk_alias' => 'id',
          ],
        ],
        'contact_source_60' => [
          'fld' => 'contact_source_60',
        ],
        'boe_date_of_registration_24' => [
          'fld' => 'boe_date_of_registration_24',
        ],
        'voter_registration_status_23' => [
          'fld' => 'voter_registration_status_23',
        ],
      ],
    ];
  }

  /**
   * Suffix lookup table.
   *
   * Keys are uppercased CSV values; values are the canonical CiviCRM suffix strings.
   *
   * @return array
   */
  public static function getSuffixes(): array {
    return [
      'JR'  => 'Jr.',
      '2ND' => 'II',
      '3RD' => 'III',
      '4TH' => 'IV',
      'DR'  => 'M.D.',
      'S'   => 'Sr.',
      'SR'  => 'Sr.',
    ];
  }

  /**
   * Prefix lookup table.
   *
   * Keys are uppercased CSV values; values are the canonical CiviCRM prefix strings.
   *
   * @return array
   */
  public static function getPrefixes(): array {
    return [
      'MR'  => 'Mr.',
      'MRS' => 'Mrs.',
      'MS'  => 'Ms.',
      'REV' => 'Rev.',
      'DR'  => 'Dr.',
    ];
  }

}
