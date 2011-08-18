<?php



/*
 
 */
function participant_get_example(){
$params = array( 
  'id' => 14,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'participant','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function participant_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 14,
  'values' => array( 
      '14' => array( 
          'contact_id' => '2',
          'contact_type' => 'Individual',
          'sort_name' => 'Anderson, Anthony',
          'display_name' => 'Mr. Anthony Anderson II',
          'event_id' => '5',
          'event_title' => 'Annual CiviCRM meet',
          'event_start_date' => '2008-10-21 00:00:00',
          'event_end_date' => '2008-10-23 00:00:00',
          'participant_id' => '14',
          'event_type' => 'Conference',
          'participant_status_id' => '2',
          'participant_status' => 'Attended',
          'participant_role_id' => '1',
          'participant_register_date' => '2007-02-19 00:00:00',
          'participant_source' => 'Wimbeldon',
          'participant_is_pay_later' => 0,
          'participant_is_test' => 0,
          'id' => '14',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* participant_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/