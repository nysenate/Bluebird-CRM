<?php



/*
 
 */
function constant_get_example(){
$params = array( 
  'name' => 'locationType',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'constant','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function constant_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 4,
  'values' => array( 
      '5' => 'Billing',
      '1' => 'Home',
      '3' => 'Main',
      '2' => 'Work',
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* constant_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/