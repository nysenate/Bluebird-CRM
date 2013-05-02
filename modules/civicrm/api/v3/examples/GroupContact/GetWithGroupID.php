<?php



/*
 Get all from group and display contacts
 */
function group_contact_get_example(){
$params = array( 
  'group_id' => 1,
  'version' => 3,
  'api.contact.get' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'group_contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'group_id' => '1',
          'contact_id' => '1',
          'status' => 'Added',
          'api.contact.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'id' => 1,
              'values' => array( 
                  '0' => array( 
                      'contact_id' => '1',
                      'contact_type' => 'Individual',
                      'contact_sub_type' => '',
                      'sort_name' => 'Anderson, Anthony',
                      'display_name' => 'Mr. Anthony Anderson II',
                      'do_not_email' => 0,
                      'do_not_phone' => 0,
                      'do_not_mail' => 0,
                      'do_not_sms' => 0,
                      'do_not_trade' => 0,
                      'is_opt_out' => 0,
                      'legal_identifier' => '',
                      'external_identifier' => '',
                      'nick_name' => '',
                      'legal_name' => '',
                      'image_URL' => '',
                      'preferred_mail_format' => 'Both',
                      'first_name' => 'Anthony',
                      'middle_name' => 'J.',
                      'last_name' => 'Anderson',
                      'job_title' => '',
                      'birth_date' => '',
                      'is_deceased' => 0,
                      'deceased_date' => '',
                      'household_name' => '',
                      'organization_name' => '',
                      'sic_code' => '',
                      'contact_is_deleted' => 0,
                      'gender_id' => '',
                      'gender' => '',
                      'prefix_id' => '3',
                      'prefix' => 'Mr.',
                      'suffix_id' => '3',
                      'suffix' => 'II',
                      'current_employer' => '',
                      'address_id' => '',
                      'street_address' => '',
                      'supplemental_address_1' => '',
                      'supplemental_address_2' => '',
                      'city' => '',
                      'postal_code_suffix' => '',
                      'postal_code' => '',
                      'geo_code_1' => '',
                      'geo_code_2' => '',
                      'state_province_id' => '',
                      'state_province_name' => '',
                      'state_province' => '',
                      'country_id' => '',
                      'country' => '',
                      'phone_id' => '',
                      'phone_type_id' => '',
                      'phone' => '',
                      'email_id' => '2',
                      'email' => 'anthony_anderson@civicrm.org',
                      'on_hold' => 0,
                      'im_id' => '',
                      'provider_id' => '',
                      'im' => '',
                      'worldregion_id' => '',
                      'world_region' => '',
                      'id' => '1',
                    ),
                ),
            ),
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetGroupID and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/GroupContactTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/