<?php



/*
 
 */
function phone_create_example(){
$params = array( 
  'contact_id' => 1,
  'location_type_id' => 6,
  'phone' => '021 512 755',
  'is_primary' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'phone','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function phone_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => 1,
          'contact_id' => 1,
          'location_type_id' => 6,
          'is_primary' => 1,
          'is_billing' => '',
          'mobile_provider_id' => '',
          'phone' => '021 512 755',
          'phone_ext' => '',
          'phone_type_id' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testCreatePhone and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/PhoneTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/