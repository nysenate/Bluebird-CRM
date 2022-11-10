<?php

require_once 'senate.suffixes.php';

define('DELIM', '~');
define("DBNULL", null);

// ParentID of all top-level category tags in the issue code hierarchy.
define('CATEGORY_TAG_PARENT_ID', 291);
// Parent of all position (legislative) tags.
define('POSITION_TAG_PARENT_ID', 292);
// ParentID of all freeform tags.
define('FREEFORM_TAG_PARENT_ID', 296);
define('COUNTRY_CODE_USA', 1228);

// location type IDs
define('LOC_TYPE_HOME', 1);
define('LOC_TYPE_WORK', 2);
define('LOC_TYPE_MAIN', 3);
define('LOC_TYPE_OTHER', 4);

// phone type IDs
define('PHONE_TYPE_PHONE', 1);
define('PHONE_TYPE_MOBILE', 2);
define('PHONE_TYPE_FAX', 3);

// addressee formats
// check civicrm_option_value table to confirm these values
define('ADDRESSEE_FULL', 1);
define('ADDRESSEE_HN', 2);
define('ADDRESSEE_ORG', 3);
define('ADDRESSEE_CUSTOM', 4);
define('ADDRESSEE_THE_HN', 5);

// postal and email greeting formats (currently, they are in sync)
// check civicrm_option_value table to confirm these values
define('GREETING_FN', 1);
define('GREETING_PREFIX_FN_LN', 2);
define('GREETING_PREFIX_LN', 3);
define('GREETING_CUSTOM', 4);
define('GREETING_HN', 5);
define('GREETING_FAM_HN', 6);
define('GREETING_NICK', 7);
define('GREETING_FRIENDS', 8);
define('GREETING_NICK2', 9);
define('GREETING_FRIEND', 10);

// gender IDs
define('GENDER_FEMALE', 1);
define('GENDER_MALE', 2);
define('GENDER_TRANS', 3);
define('GENDER_OTHER', 4);


$aRelLookup = array(
  'H' => 2,
  'W' => 2,
  'S' => 3,
  'D' => 3,
  'HoH' => 6,
  'MoH' => 7,
  'employeeOf' => 4
);


// OMIS database fields

$omis_ct_fields = array(
	"KEY", "LAST", "FIRST", "MI", "SUFFIX",
	"HOUSE", "STREET", "MAIL", "CITY", "STATE", "ZIP5", "ZIP4",
	"SKF", "SKEY", "RT", "MS", "RCD", "SEX",
	"WD", "TN", "CT", "SD", "SCD", "ED", "AD", "TC1", "TC2",
	"BMM", "BDD", "BYY", "PHONE", "CHG", "DEO", "REQ",
	"OVERFLOW", "LGD", "FAM1", "FAM2", "OTITLE", "OCOMPANY",
	"INSIDE1", "SALUTE1", "INSIDE2", "SALUTE2", "LONGSTATE",
	"ADDR_WORK_STREET1", "ADDR_WORK_STREET2",
	"ADDR_WORK_CITY", "ADDR_WORK_STATE", "ADDR_WORK_ZIP",
	"PHONE_WORK", "PHONE_WORK_EXT", "PHONE_MOBILE",
	"FAX_HOME", "FAX_WORK", "EMAIL", "CONTACT_TYPE", "SPOUSE",
	"CHILDREN", "LOVES_LIZ", "GROUPS", "WEBSITE", "SENIORS",
	"NON_DISTRICT"
);

$omis_is_fields = array(
	"KEY", "ISSUECODE", "UPDATED", "ISSUEDESCRIPTION",
	"CATEGORY", "IS_TAG"
);

$omis_cs_fields = array(
	"KEY", "CASENUM", "CSUBJECT", "CSTAFF", "COPENTIME", "COPENDATE",
	"CCLOSEDATE", "CHOMEPH", "CWORKPH", "CFAXPH",
	"CSNUM", "CLAB1", "CID1", "CLAB2", "CID2", "CISSUE", "CFORM", "CPLACE",
	"CNOTE1", "CNOTE2", "CNOTE3", "CLASTUPDATE", "LEGISLATION"
);

$omis_nt_fields = array(
	"KEY", "HNUM", "HPAG", "HL1", "HL2", "HL3", "HL4", "HL5", "HL6", "HL7",
	"HL8", "HL9", "HL10", "HL11", "HL12", "HL13", "HL14", "HL15"
);

// The OMIS extended fields that are dumped into Notes.
//     true = boolean field, false = string field
$omis_ext_fields = array(
	"CONTACT_TYPE" => false,
	"SPOUSE" => false,
	"CHILDREN" => false,
	"LOVES_LIZ" => true,
	"GROUPS" => true,
	"WEBSITE" => false,
	"SENIORS" => true,
	"NON_DISTRICT" => true);


// Bluebird database tables and fields

$bluebird_db_info = array(
  "activity" => array(
      "abbrev" => "ac",
      "table" => "civicrm_activity",
      "cols" => array(
          'id',
          'source_contact_id',
          'subject',
          'activity_date_time',
          'status_id',
          'details',
          'activity_type_id'
      )
  ),
  "activitycustom" => array(
      "abbrev" => "acu",
      "table" => "civicrm_value_activity_details_6",
      "cols" => array(
          'entity_id',
          'place_of_inquiry_43'
      )
  ),
  "activitytarget" => array(
      "abbrev" => "act",
      "table" => "civicrm_activity_contact",
      "cols" => array(
          'activity_id',
          'contact_id',
          'recordt_type_id'
      )
  ),
  "address" => array(
      "abbrev" => "ad",
      "table" => "civicrm_address",
      "cols" => array(
          'id',
          'contact_id',
          'location_type_id',
          'is_primary',
          'street_number',
          'street_unit',
          'street_name',
          'street_address',
          'supplemental_address_1',
          'supplemental_address_2',
          'city',
          'postal_code',
          'postal_code_suffix',
          'country_id',
          'state_province_id'
      )
  ),
  "contact" => array(
      "abbrev" => "ct",
      "table" => "civicrm_contact",
      "cols" => array(
          'id',
          'contact_type',
          'external_identifier',
          'first_name',
          'middle_name',
          'last_name',
          'sort_name',
          'display_name',
          'gender_id',
          'source',
          'birth_date',
          'prefix_id',
          'suffix_id',
          'addressee_id',
          'addressee_custom',
          'addressee_display',
          'postal_greeting_id',
          'postal_greeting_custom',
          'postal_greeting_display',
          'email_greeting_id',
          'email_greeting_custom',
          'email_greeting_display',
          'organization_name',
          'job_title',
          'do_not_mail',
          'employer_id',
          'nick_name',
	  'household_name'
      )
  ),
  "district" => array(
      "abbrev" => "di",
      "table" => "civicrm_value_district_information_7",
      "cols" => array(
          'entity_id',
          'congressional_district_46',
          'ny_senate_district_47',
          'ny_assembly_district_48',
          'election_district_49',
          'county_50',
          'county_legislative_district_51',
          'town_52',
          'ward_53',
          'school_district_54',
          'new_york_city_council_55',
          'last_import_57'
      )
  ),
  "constituentinformation" => array(
      "abbrev" => "constinf",
      "table" => "civicrm_value_constituent_information_1",
      "cols" => array(
          'entity_id',
          'record_type_61'
      )
  ),
  "email" => array(
      "abbrev" => "em",
      "table" => "civicrm_email",
      "cols" => array(
          'contact_id',
          'location_type_id',
          'email',
          'is_primary'
      )
  ),
  "entitytag" => array(
      "abbrev" => "et",
      "table" => "civicrm_entity_tag",
      "cols" => array(
          'entity_table',
          'entity_id',
          'tag_id'
      )
  ),
  "note" => array(
      "abbrev" => "no",
      "table" => "civicrm_note",
      "cols" => array(
          'entity_table',
          'entity_id',
          'note',
          'contact_id',
          'modified_date',
          'subject'
      )
  ),
  "phone" => array(
      "abbrev" => "ph",
      "table" => "civicrm_phone",
      "cols" => array(
          'contact_id',
          'location_type_id',
          'is_primary',
          'phone_type_id',
          'phone'
      )
  ),
  "relationship" => array(
      "abbrev" => "re",
      "table" => "civicrm_relationship",
      "cols" => array(
          'contact_id_a',
          'contact_id_b',
          'relationship_type_id'
      )
  ),
  "tag" => array(
      "abbrev" => "ta",
      "table" => "civicrm_tag",
      "cols" => array(
          'id',
          'name',
          'description',
          'parent_id',
          'is_selectable',
          'is_reserved',
          'is_tagset',
          'used_for'
      )
  )
);

?>
