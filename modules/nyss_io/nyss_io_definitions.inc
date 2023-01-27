<?php

global $nyss_iofields;
global $nyss_iosuffixes;
global $nyss_ioprefixes;

$nyss_iofields['civicrm_contact'] = [
  'id' => [
    'fld' => 'id',
    'PK'=> true,
  ],
  'first_name' => [
    'fld' => 'first_name',
    'handler' => 'convertProperCase',
  ],
  /*'mid' => array(
    'fld' => 'middle_name',
  ),*/
  'middle_name' => [
    'fld' => 'middle_name',
    'handler' => 'convertProperCase',
  ],
  'last_name' => [
    'fld' => 'last_name',
    'handler' => 'convertProperCase',
  ],
  'prefix_id' => [
    'fld' => 'prefix_id',
  ],
  'suffix_id' => [
    'fld' => 'suffix_id',
  ],
  /*'gender' => array(
    'fld' => 'gender_id',
  ),*/
  'gender_id' => [
    'fld' => 'gender_id',
  ],
  'birth_date' => [
    'fld' => 'birth_date',
  ],
  /*'is' => array(
    'fld' => 'is_deleted',
  ),*/
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
    'handler' => 'convertProperCase',
  ],
  'organization_name' => [
    'fld' => 'organization_name',
    'handler' => 'convertProperCase',
  ],
];

$nyss_iofields['civicrm_address'] = [
  'address_id' => [
    'fld' => 'id',
    'PK'=> true,
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
  /*'lo' => array(
    'fld' => 'location_type_id',
  ),*/
  'location_type_id' => [
    'fld' => 'location_type_id',
  ],
  'street_name' => [
    'fld' => 'street_name',
    'handler' => 'convertProperCase',
  ],
  'street_unit' => [
    'fld' => 'street_unit',
    'handler' => 'convertProperCase',
  ],
  'supplemental_address_1' => [
    'fld' => 'supplemental_address_1',
    'handler' => 'convertProperCase',
  ],
  'supplemental_address_2' => [
    'fld' => 'supplemental_address_2',
    'handler' => 'convertProperCase',
  ],
  'city' => [
    'fld' => 'city',
    'handler' => 'convertProperCase',
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
    'handler' => 'convertProperCase',
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
];

$nyss_iofields['civicrm_email'] = [
  'email_id' => [
    'fld' => 'id',
    'PK'=> true,
  ],
  'email_contact_id' => [
    'fld' => 'contact_id',
  ],
  'email' => [
    'fld' => 'email',
    'required' => true,
    'handler' => 'convertLowerCase',
  ],
  'email_location_type_id' => [
    'fld' => 'location_type_id',
  ],
  'email_is_primary' => [
    'fld' => 'is_primary',
  ],
];

$nyss_iofields['civicrm_phone'] = [
  'phone_id' => [
    'fld' => 'id',
    'PK'=> true,
  ],
  'phone_contact_id' => [
    'fld' => 'contact_id',
  ],
  'phone' => [
    'fld' => 'phone',
    'required' => true,
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
];

$nyss_iofields['civicrm_value_district_information_7'] = [
  'districtinfo_id' => [
    'fld' => 'id',
    'PK'=> true,
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
];

$nyss_iofields['civicrm_value_constituent_information_1'] = [
  'constinfo_id' => [
    'fld' => 'id',
    'PK'=> true,
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
];

//key should be all caps as we strtoupper() when comparing
$nyss_iosuffixes = [
  'JR' => 'Jr.',
  '2ND' => 'II',
  '3RD' => 'III',
  '4TH' => 'IV',
  'DR' => 'M.D.',
  'S' => 'Sr.',
  'SR' => 'Sr.',
];

$nyss_ioprefixes = [
  'MR' => 'Mr.',
  'MRS' => 'Mrs.',
  'MS' => 'Ms.',
  'REV' => 'Rev.',
  'DR' => 'Dr.',
];
