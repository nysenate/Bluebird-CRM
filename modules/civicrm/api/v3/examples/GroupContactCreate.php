<?php



/*
 
 */
function group_contact_create_example(){
$params = array( 
  'contact_id' => 1,
  'contact_id.2' => 2,
  'group_id' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'group_contact','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 3,
  'values' => array( 
      'not_added' => 1,
      'added' => 1,
      'total_count' => 2,
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* group_contact_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/