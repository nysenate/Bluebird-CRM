<?php



/*
 
 */
function activity_update_example(){
$params = array( 
  'id' => 1,
  'source_contact_id' => 17,
  'subject' => 'Make-it-Happen Meeting',
  'status_id' => 1,
  'activity_name' => 'Test activity type',
  'activity_date_time' => '20110720',
  'custom_1' => 'Updated my test data',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'activity','update',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_update_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => 1,
          'source_contact_id' => 17,
          'source_record_id' => '',
          'activity_type_id' => 1,
          'subject' => 'Make-it-Happen Meeting',
          'activity_date_time' => '20110720000000',
          'duration' => '',
          'location' => '',
          'phone_id' => '',
          'phone_number' => '',
          'details' => '',
          'status_id' => 1,
          'priority_id' => '',
          'parent_id' => '',
          'is_test' => '',
          'medium_id' => '',
          'is_auto' => '',
          'relationship_id' => '',
          'is_current_revision' => '',
          'original_id' => '',
          'result' => '',
          'is_deleted' => '',
          'campaign_id' => '',
          'engagement_level' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testActivityUpdateCustom and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3ActivityTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/