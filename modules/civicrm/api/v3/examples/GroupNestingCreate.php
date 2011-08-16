<?php



/*
 
 */
function group_nesting_create_example(){
$params = array( 
  'parent_group_id' => 1,
  'child_group_id' => 3,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'group_nesting','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_nesting_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 'is_error',
  'values' => array( 
      'is_error' => 0,
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* group_nesting_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/