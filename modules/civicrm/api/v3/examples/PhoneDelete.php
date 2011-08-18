<?php



/*
 
 */
function phone_delete_example(){
$params = array( 
  'contact_id' => 1,
  'location_type_id' => 7,
  'phone' => '021 512 755',
  'is_primary' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'phone','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function phone_delete_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'values' => '3',
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* phone_delete 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/