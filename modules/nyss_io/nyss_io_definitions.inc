<?php

global $nyss_iofields;

$nyss_iofields['civicrm_contact'] = array(
	'id' => array(
		'fld' => 'id',
		'PK'=> true,
	),
	'first_name' => array(
		'fld' => 'first_name',
		'handler' => 'convertProperCase',
	),
	'mid' => array(
		'fld' => 'middle_name',
	),
	'last_name' => array(
		'fld' => 'last_name',
                'handler' => 'convertProperCase',
	),
	'suffix_id' => array(
		'fld' => 'suffix_id',
	),
	'gender' => array(
		'fld' => 'gender_id',
	),
	'birth_date' => array(
		'fld' => 'birth_date',
	),
	'is' => array(
		'fld' => 'is_deleted',
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
	'lo' => array(
		'fld' => 'location_type_id',
	),
	'street_name' => array(
		'fld' => 'street_name',
                'handler' => 'convertProperCase',
	),
	'street_unit' => array(
		'fld' => 'street_unit',
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
	'postal_cod' => array(
		'fld' => 'postal_code',
	),
	'postal' => array(
		'fld' => 'postal_code_suffix',
	),
	'street_address' => array(
                'fld' => 'street_address',
        ),
);

$nyss_iofields['civicrm_email'] = array(
	'email_id' => array(
		'fld' => 'id',
		'PK'=> true,
	),
        'contact_id' => array(
                'fld' => 'contact_id',
        ),
	'email' => array(
		'fld' => 'email',
		'required' => true,
	),
);

$nyss_iofields['civicrm_phone'] = array(
	'phone_id' => array(
		'fld' => 'id',
		'PK'=> true,
	),
        'contact_id' => array(
                'fld' => 'contact_id',
        ),
	'phone' => array(
		'fld' => 'phone',
                'required' => true,
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
	'con' => array(
		'fld' => 'congressional_district_46',
	),
	'ny_' => array(
		'fld' => 'ny_senate_district_47',
	),
	'ny_a' => array(
		'fld' => 'ny_assembly_district_48',
	),
	'scho' => array(
		'fld' => 'school_district_54',
	),
	'cou' => array(
		'fld' => 'county_50',
	),
);
?>
