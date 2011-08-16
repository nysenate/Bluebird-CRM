<?php



/*
 
 */
function activity_get_example(){
$params = array( 
  'contact_id' => 17,
  'activity_type_id' => 1,
  'version' => 3,
  'sequential' => 1,
  'return.custom_1' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'activity','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '0' => array( 
          'source_contact_id' => '17',
          'id' => 1,
          'activity_type_id' => '1',
          'subject' => 'test activity type id',
          'location' => '',
          'activity_date_time' => '2011-06-02 14:36:13',
          'details' => '',
          'status_id' => '2',
          'activity_name' => 'Test activity type',
          'status' => 'Completed',
          'custom_1' => 'custom string',
          'custom_1_1' => 'custom string',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testActivityGetContact_idCustom and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3ActivityTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/