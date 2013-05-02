<?php



/*
 
 */
function grant_delete_example(){
$params = array( 
  'version' => 3,
  'contact_id' => 1,
  'application_received_date' => 'now',
  'decision_date' => 'next Monday',
  'amount_total' => '500',
  'status_id' => 1,
  'rationale' => 'Just Because',
  'currency' => 'USD',
  'grant_type_id' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'grant','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function grant_delete_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'values' => true,
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testDeleteGrant and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/GrantTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/