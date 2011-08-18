<?php



/*
 
 */
function relationship_delete_example(){
$params = array( 
  'contact_id_a' => 1,
  'contact_id_b' => 2,
  'relationship_type_id' => 23,
  'start_date' => '2008-12-20',
  'is_active' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'relationship','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_delete_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 5,
  'values' => array( 
      '5' => array( 
          'id' => 5,
          'moreIDs' => '5',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* relationship_delete 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/