<?

//DEFINITIONS AND CONSTANTS
global $aOmisCols;
$aOmisCols[] = 'KEY';
$aOmisCols[] = 'LAST';
$aOmisCols[] = 'FIRST';
$aOmisCols[] = 'MI';
$aOmisCols[] = 'SUFFIX';
$aOmisCols[] = 'HOUSE';
$aOmisCols[] = 'STREET';
$aOmisCols[] = 'MAIL';
$aOmisCols[] = 'CITY';
$aOmisCols[] = 'STATE';
$aOmisCols[] = 'ZIP5';
$aOmisCols[] = 'ZIP4';
$aOmisCols[] = 'SKF';
$aOmisCols[] = 'SKEY';
$aOmisCols[] = 'RT';
$aOmisCols[] = 'MS';
$aOmisCols[] = 'RCD';
$aOmisCols[] = 'SEX';
$aOmisCols[] = 'WD';
$aOmisCols[] = 'TN';
$aOmisCols[] = 'CT';
$aOmisCols[] = 'SD';
$aOmisCols[] = 'CD';
$aOmisCols[] = 'ED';
$aOmisCols[] = 'AD';
$aOmisCols[] = 'TC1';
$aOmisCols[] = 'TC2';
$aOmisCols[] = 'BMM';
$aOmisCols[] = 'BDD';
$aOmisCols[] = 'BYY';
$aOmisCols[] = 'PHONE';
$aOmisCols[] = 'CHG';
$aOmisCols[] = 'DEO';
$aOmisCols[] = 'REQ';
$aOmisCols[] = 'OVERFLOW';
$aOmisCols[] = 'LGD';
$aOmisCols[] = 'FAM1';
$aOmisCols[] = 'FAM2';
$aOmisCols[] = 'OTITLE';
$aOmisCols[] = 'OCOMPANY';
$aOmisCols[] = 'INSIDE1';
$aOmisCols[] = 'SALUTE1';
$aOmisCols[] = 'INSIDE2';
$aOmisCols[] = 'SALUTE2';
$aOmisCols[] = 'LONGSTATE';
$aOmisCols[] = 'TITLE';
$aOmisCols[] = 'ADDR_WORK_STREET1';
$aOmisCols[] = 'ADDR_WORK_STREET2';
$aOmisCols[] = 'ADDR_WORK_CITY';
$aOmisCols[] = 'ADDR_WORK_STATE';
$aOmisCols[] = 'ADDR_WORK_ZIP';
$aOmisCols[] = 'PHONE_WORK';
$aOmisCols[] = 'PHONE_WORK_EXT';
$aOmisCols[] = 'PHONE_MOBILE';
$aOmisCols[] = 'FAX_HOME';
$aOmisCols[] = 'FAX_WORK';
$aOmisCols[] = 'EMAIL';
$aOmisCols[] = 'CONTACT_TYPE';
$aOmisCols[] = 'SPOUSE';
$aOmisCols[] = 'CHILDREN';
$aOmisCols[] = 'LOVES_LIZ';
$aOmisCols[] = 'GROUPS';
$aOmisCols[] = 'WEBSITE';
$aOmisCols[] = 'SENIORS';
$aOmisCols[] = 'NON_DISTRICT';

global $aSuffixMap;
$aSuffixMap['Jr.'] = 1;
$aSuffixMap['Sr.'] = 2;
$aSuffixMap['II'] = 3;
$aSuffixMap['III'] = 4;
$aSuffixMap['IV'] = 5;
$aSuffixMap['V'] = 6;
$aSuffixMap['VI'] = 7;
$aSuffixMap['VII'] = 8;
$aSuffixMap['Esq.'] = 9;

global $aRelLookup;
$aRelLookup['H']=2;
$aRelLookup['W']=2;
$aRelLookup['S']=3;
$aRelLookup['D']=3;

global $aPrefixMap;
$aPrefixMap[1] = 'Mr.';
$aPrefixMap[2] = 'Mrs.';
$aPrefixMap[3] = 'Mr. and Mrs.';
$aPrefixMap[4] = 'Ms.';
$aPrefixMap[5] = 'Dr.';
$aPrefixMap[6] = 'Dr. and Mrs.';
$aPrefixMap[7] = 'The Honorable';
$aPrefixMap[8] = 'The Honorable and Mrs.';
$aPrefixMap[9] = 'Miss';
$aPrefixMap[10] = 'Reverend';
$aPrefixMap[11] = 'Reverend and Mrs.';
$aPrefixMap[12] = 'The Honorable';
$aPrefixMap[13] = 'The Honorable and Mrs.';
$aPrefixMap[14] = 'The Honorable';
$aPrefixMap[15] = 'The Honorable and Mrs.';
$aPrefixMap[16] = 'General';
$aPrefixMap[17] = 'Colonel';
$aPrefixMap[18] = 'Captain';
$aPrefixMap[19] = 'Sister';
$aPrefixMap[20] = 'The Reverend';
$aPrefixMap[21] = 'First Lieutenant';
$aPrefixMap[22] = 'The Honorable';
$aPrefixMap[23] = 'Rear Admiral';
$aPrefixMap[24] = 'Major';
$aPrefixMap[25] = 'Cadet';
$aPrefixMap[26] = 'Major General';
$aPrefixMap[27] = 'Professor';
$aPrefixMap[28] = 'The Honorable';
$aPrefixMap[29] = 'Congressman';
$aPrefixMap[30] = 'Congressman and Mrs.';
$aPrefixMap[31] = 'LTC and Mrs.';
$aPrefixMap[32] = 'Colonel and Mrs.';
$aPrefixMap[33] = 'Lieutenant Colonel';
$aPrefixMap[34] = 'Cantor';
$aPrefixMap[35] = 'Cantor and Mrs.';
$aPrefixMap[36] = 'Honorable and Mrs.';
$aPrefixMap[37] = 'Chancellor';
$aPrefixMap[38] = 'Chancellor and Mrs.';
$aPrefixMap[39] = 'Messrs.';
$aPrefixMap[40] = 'Lieutenant';
$aPrefixMap[41] = 'Lieutenant Commander';
$aPrefixMap[42] = 'Captain and Mrs.';
$aPrefixMap[43] = 'Chief Warrant Officer';
$aPrefixMap[44] = 'Ensign';
$aPrefixMap[45] = 'Lieutenant Junior Grade';
$aPrefixMap[46] = 'Commander';
$aPrefixMap[47] = 'Warrant Officer';
$aPrefixMap[48] = 'Second Lieutenant';
$aPrefixMap[49] = '';
$aPrefixMap[50] = '';
$aPrefixMap[51] = 'Rabbi';
$aPrefixMap[52] = 'Rear Admiral and Mrs.';
$aPrefixMap[53] = 'Monsignor';
$aPrefixMap[54] = 'Vice Admiral';
$aPrefixMap[55] = 'Admiral';
$aPrefixMap[56] = 'Lieutenant General';
$aPrefixMap[57] = '';
$aPrefixMap[58] = 'The Honorable';
$aPrefixMap[59] = 'The Honorable and Mrs.';
$aPrefixMap[60] = 'Brigadier General';
$aPrefixMap[61] = 'The Honorable';
$aPrefixMap[62] = 'The Honorable and Mrs.';
$aPrefixMap[63] = 'Brother';
$aPrefixMap[64] = '';
$aPrefixMap[65] = 'Rabbi and Mrs.';
$aPrefixMap[66] = 'The Honorable';
$aPrefixMap[67] = 'The Honorable';
$aPrefixMap[68] = 'The Honorable';
$aPrefixMap[69] = 'The Honorable';
$aPrefixMap[70] = 'Most Reverend';
$aPrefixMap[71] = '';
$aPrefixMap[72] = 'Most Reverend';
$aPrefixMap[73] = 'Dean';
$aPrefixMap[74] = 'The Reverend Dr.';
$aPrefixMap[75] = '';
$aPrefixMap[76] = '';
$aPrefixMap[77] = 'Pastor';
$aPrefixMap[78] = 'Pastor and Mrs.';
$aPrefixMap[79] = '';
$aPrefixMap[80] = '';
$aPrefixMap[81] = 'Major and Mrs.';
$aPrefixMap[82] = 'Bishop';
$aPrefixMap[83] = 'Sergeant';
$aPrefixMap[84] = 'Mr. and Dr.';
$aPrefixMap[85] = '';
$aPrefixMap[86] = '';
$aPrefixMap[87] = '';
$aPrefixMap[88] = 'Reverend Mother';
$aPrefixMap[89] = '';
$aPrefixMap[90] = 'The Honorable and Mr.';
$aPrefixMap[91] = 'The Honorable and Mr.';
$aPrefixMap[92] = 'The Chief Justice';
$aPrefixMap[93] = 'Mr. Justice';
$aPrefixMap[94] = 'The Honorable';
$aPrefixMap[95] = 'Dr. and Dr.';
$aPrefixMap[96] = 'The Honorable';
$aPrefixMap[97] = '';
$aPrefixMap[98] = '';
$aPrefixMap[99] = 'Reverend Monsignor';
$aPrefixMap[100] = 'The__________Family';
$aPrefixMap[101] = 'Adjutant';
$aPrefixMap[102] = 'Administrative Major';
$aPrefixMap[103] = 'Ambassador and Mrs.';
$aPrefixMap[104] = '';
$aPrefixMap[105] = 'Professor and Mrs.';
$aPrefixMap[106] = '';

//importFields
global $dbTable;

$dbTable['contact'] = array('id',
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
        'addressee_id',
        'addressee_custom',
        'addressee_display',
        'postal_greeting_id',
        'postal_greeting_custom',
        'postal_greeting_display',
        'organization_name',
        'job_title',
        'prefix_id',
        'suffix_id',
	'do_not_mail',
	'employer_id',
	'nick_name'
);

$dbTable['address'] = array('id',
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
        'state_province_id');

$dbTable['phone'] = array('contact_id',
        'location_type_id',
        'is_primary',
        'phone_type_id',
        'phone');

$dbTable['district_information'] = array(
        'entity_id',
        'congressional_district_46',
        'election_district_49',
        'ny_assembly_district_48',
        'ny_senate_district_47',
        'ward_53',
        'town_52',
        'county_50');

$dbTable['tag'] = array('entity_table',
        'entity_id',
        'tag_id');

$dbTable['note'] = array('contact_id',
        'entity_table',
        'subject',
        'modified_date',
        'entity_id,
        note');

$dbTable['email'] = array('contact_id',
        'location_type_id',
        'email',
        'is_primary');

$dbTable['relationship'] = array('contact_id_a',
        'contact_id_b',
        'relationship_type_id');

$dbTable['activity'] = array('id',
        'source_contact_id',
        'subject',
        'activity_date_time',
        'status_id',
        'details',
        'activity_type_id');

$dbTable['activity_target'] = array('activity_id',
        'target_contact_id');

$dbTable['activity_custom'] = array('entity_id',
        'place_of_inquiry_43');

?>
