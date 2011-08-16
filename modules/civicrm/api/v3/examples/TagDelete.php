<?php



/*
 
 */
function tag_delete_example(){
$params = array( 
  'tag_id' => array( 
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'id' => 8,
      'values' => array( 
          '8' => array( 
              'id' => 8,
              'name' => 'New Tag325041',
              'description' => 'This is description for New Tag 1078',
              'parent_id' => '',
              'is_selectable' => '',
              'is_reserved' => '',
              'is_tagset' => '',
              'used_for' => 'civicrm_contact',
              'created_id' => '',
              'created_date' => '20110711195209',
            ),
        ),
    ),
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'tag','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function tag_delete_expectedresult(){

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
* tag_delete 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/