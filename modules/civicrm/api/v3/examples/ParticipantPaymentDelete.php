<?php



/*
 
 */
function participant_payment_delete_example(){
$params = array( 
  'id' => 3,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'participant_payment','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function participant_payment_delete_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'values' => 1,
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* participant_payment_delete 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/