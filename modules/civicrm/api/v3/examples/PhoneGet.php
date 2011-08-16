<?php



/*
 
 */
function phone_get_example(){
$params = array( 
  'contact_id' => '',
  'phone' => '',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'phone','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function phone_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 2,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'location_type_id' => '1',
          'is_primary' => 0,
          'is_billing' => 0,
          'phone' => '204 222-1001',
          'phone_type_id' => '1',
        ),
      '4' => array( 
          'id' => '4',
          'contact_id' => '1',
          'location_type_id' => '11',
          'is_primary' => '1',
          'is_billing' => 0,
          'phone' => '021 512 755',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* phone_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/