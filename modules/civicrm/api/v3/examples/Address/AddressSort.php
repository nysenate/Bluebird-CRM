<?php



/*
 Demonstrates Use of sort filter
 */
function address_get_example(){
$params = array( 
  'options' => array( 
      'sort' => 'street_address DESC',
    ),
  'version' => 3,
  'sequential' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'address','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function address_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 2,
  'values' => array( 
      '0' => array( 
          'id' => '5',
          'contact_id' => '1',
          'location_type_id' => '9',
          'is_primary' => '1',
          'is_billing' => 0,
          'street_address' => 'Ambachtstraat 23',
          'street_number' => '23',
          'street_name' => 'Ambachtstraat',
          'city' => 'Brummen',
          'postal_code' => '6971 BN',
          'country_id' => '1152',
        ),
      '1' => array( 
          'id' => '1',
          'location_type_id' => '1',
          'is_primary' => '1',
          'is_billing' => '1',
          'street_address' => '15S El Camino Way E',
          'street_number' => '14',
          'street_number_suffix' => 'S',
          'street_name' => 'El Camino',
          'street_type' => 'Way',
          'city' => 'Collinsville',
          'state_province_id' => '1006',
          'postal_code' => '6022',
          'country_id' => '1228',
          'geo_code_1' => '41.8328',
          'geo_code_2' => '-72.9253',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetAddressSort and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/AddressTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/