<?php



/*
 
 */
function domain_get_example(){
$params = array( 
  'version' => 3,
  'current_domain' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'domain','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function domain_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'name' => 'Default Domain Name',
          'version' => '3.4.0',
          'domain_email' => '',
          'domain_phone' => array( 
              'phone_type' => '',
              'phone' => '',
            ),
          'domain_address' => array( 
              'street_address' => '',
              'supplemental_address_1' => '',
              'supplemental_address_2' => '',
              'city' => '',
              'state_province_id' => '',
              'postal_code' => '',
              'country_id' => '',
              'geo_code_1' => '',
              'geo_code_2' => '',
            ),
          'from_email' => 'info@FIXME.ORG',
          'from_name' => 'FIXME',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* domain_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/