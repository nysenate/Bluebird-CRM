<?php

global $nyss_iofields;
global $nyss_iosuffixes;
global $nyss_ioprefixes;

$nyss_iofields['civicrm_contact'] = array(
  'id' => array(
    'fld' => 'id',
    'PK'=> true,
  ),
  'first_name' => array(
    'fld' => 'first_name',
    'handler' => 'convertProperCase',
  ),
  /*'mid' => array(
    'fld' => 'middle_name',
  ),*/
  'middle_name' => array(
    'fld' => 'middle_name',
    'handler' => 'convertProperCase',
  ),
  'last_name' => array(
    'fld' => 'last_name',
    'handler' => 'convertProperCase',
  ),
  'prefix_id' => array(
    'fld' => 'prefix_id',
  ),
  'suffix_id' => array(
    'fld' => 'suffix_id',
  ),
  /*'gender' => array(
    'fld' => 'gender_id',
  ),*/
  'gender_id' => array(
    'fld' => 'gender_id',
  ),
  'birth_date' => array(
    'fld' => 'birth_date',
  ),
  /*'is' => array(
    'fld' => 'is_deleted',
  ),*/
  'is_deleted' => array(
    'fld' => 'is_deleted',
  ),
  'display_name' => array(
    'fld' => 'display_name',
  ),
  'sort_name' => array(
    'fld' => 'sort_name',
  ),
  'contact_type' => array(
    'fld' => 'contact_type',
  ),
  'job_title' => array(
    'fld' => 'job_title',
    'handler' => 'convertProperCase',
  ),
  'organization_name' => array(
    'fld' => 'organization_name',
    'handler' => 'convertProperCase',
  ),
);

$nyss_iofields['civicrm_address'] = array(
  'address_id' => array(
    'fld' => 'id',
    'PK'=> true,
  ),
    'contact_id' => array(
    'fld' => 'contact_id',
  ),
  'street_number' => array(
    'fld' => 'street_number',
  ),
  'street_number_suffix' => array(
    'fld' => 'street_number_suffix',
  ),
  /*'lo' => array(
    'fld' => 'location_type_id',
  ),*/
  'location_type_id' => array(
    'fld' => 'location_type_id',
  ),
  'street_name' => array(
    'fld' => 'street_name',
    'handler' => 'convertProperCase',
  ),
  'street_unit' => array(
    'fld' => 'street_unit',
    'handler' => 'convertProperCase',
  ),
  'supplemental_address_1' => array(
    'fld' => 'supplemental_address_1',
    'handler' => 'convertProperCase',
  ),
  'supplemental_address_2' => array(
    'fld' => 'supplemental_address_2',
    'handler' => 'convertProperCase',
  ),
  'city' => array(
    'fld' => 'city',
    'handler' => 'convertProperCase',
  ),
  'st' => array(
    'fld' => 'state_province_id',
  ),
  'state_province_id' => array(
    'fld' => 'state_province_id',
  ),
  'postal_cod' => array(
    'fld' => 'postal_code',
  ),
  'postal_code' => array(
    'fld' => 'postal_code',
  ),
  'postal' => array(
    'fld' => 'postal_code_suffix',
  ),
  'postal_code_suffix' => array(
    'fld' => 'postal_code_suffix',
  ),
  'street_address' => array(
    'fld' => 'street_address',
    'handler' => 'convertProperCase',
  ),
  'country_id' => array(
    'fld' => 'country_id',
  ),
  'address_is_primary' => array(
    'fld' => 'is_primary',
  ),
  'geo_code_1' => array(
    'fld' => 'geo_code_1',
  ),
  'geo_code_2' => array(
    'fld' => 'geo_code_2',
  ),
);

$nyss_iofields['civicrm_email'] = array(
  'email_id' => array(
    'fld' => 'id',
    'PK'=> true,
  ),
  'email_contact_id' => array(
    'fld' => 'contact_id',
  ),
  'email' => array(
    'fld' => 'email',
    'required' => true,
    'handler' => 'convertLowerCase',
  ),
  'email_location_type_id' => array(
    'fld' => 'location_type_id',
  ),
  'email_is_primary' => array(
    'fld' => 'is_primary',
  ),
);

$nyss_iofields['civicrm_phone'] = array(
  'phone_id' => array(
    'fld' => 'id',
    'PK'=> true,
  ),
  'phone_contact_id' => array(
    'fld' => 'contact_id',
  ),
  'phone' => array(
    'fld' => 'phone',
    'required' => true,
  ),
  'phone_location_type_id' => array(
    'fld' => 'location_type_id',
  ),
  'phone_type_id' => array(
    'fld' => 'phone_type_id',
  ),
  'phone_is_primary' => array(
    'fld' => 'is_primary',
  ),
);

$nyss_iofields['civicrm_value_district_information_7'] = array(
  'districtinfo_id' => array(
    'fld' => 'id',
    'PK'=> true,
  ),
  'entity_id' => array(
    'fld' => 'entity_id',
  ),
  'town_52' => array(
    'fld' => 'town_52',
  ),
  'ward_53' => array(
    'fld' => 'ward_53',
  ),
  'elec' => array(
    'fld' => 'election_district_49',
  ),
  'election_district_49' => array(
    'fld' => 'election_district_49',
  ),
  'con' => array(
    'fld' => 'congressional_district_46',
  ),
  'congressional_district_46' => array(
    'fld' => 'congressional_district_46',
  ),
  'ny_' => array(
    'fld' => 'ny_senate_district_47',
  ),
  'ny_senate_district_47' => array(
    'fld' => 'ny_senate_district_47',
  ),
  'ny_a' => array(
    'fld' => 'ny_assembly_district_48',
  ),
  'ny_assembly_district_48' => array(
    'fld' => 'ny_assembly_district_48',
  ),
  'scho' => array(
    'fld' => 'school_district_54',
  ),
  'school_district_54' => array(
    'fld' => 'school_district_54',
  ),
  'cou' => array(
    'fld' => 'county_50',
  ),
  'county_50' => array(
    'fld' => 'county_50',
  ),
  'last_import_57' => array(
    'fld' => 'last_import_57',
  ),
);

$nyss_iofields['civicrm_value_constituent_information_1'] = array(
  'constinfo_id' => array(
    'fld' => 'id',
    'PK'=> true,
  ),
  'constinfo_entity_id' => array(
    'fld' => 'entity_id',
  ),
  'contact_source_60' => array(
    'fld' => 'contact_source_60',
  ),
  'boe_date_of_registration_24' => array(
    'fld' => 'boe_date_of_registration_24',
  ),
  'voter_registration_status_23' => array(
    'fld' => 'voter_registration_status_23',
  ),
);

//key should be all caps as we strtoupper() when comparing
$nyss_iosuffixes = array(
  'JR' => 'Jr.',
  '2ND' => 'II',
  '3RD' => 'III',
  '4TH' => 'IV',
  'DR' => 'M.D.',
  'S' => 'Sr.',
  'SR' => 'Sr.',
);

$nyss_ioprefixes = array(
  'MR' => 'Mr.',
  'MRS' => 'Mrs.',
  'MS' => 'Ms.',
  'REV' => 'Rev.',
  'DR' => 'Dr.',
);
