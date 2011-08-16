<?php



/*
 
 */
function entity_tag_create_example(){
$params = array( 
  'contact_id' => 1,
  'tag_id' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'entity_tag','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function entity_tag_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'not_added' => 0,
  'added' => 1,
  'total_count' => 1,
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* entity_tag_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/