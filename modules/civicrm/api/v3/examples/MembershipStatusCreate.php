<?php



/*
 
 */
function membership_status_create_example(){
$params = array( 
  'name' => 'test membership status',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership_status','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_status_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 2,
  'id' => 17,
  'values' => array( 
      'id' => 17,
      'is_error' => 0,
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_status_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/