<?php



/*
 
 */
function participant_create_example(){
$params = array( 
  'contact_id' => 2,
  'event_id' => 1,
  'status_id' => 1,
  'role_id' => 1,
  'register_date' => '2007-07-21 00:00:00',
  'source' => 'Online Event Registration: API Testing',
  'version' => 3,
  'custom_1' => 'custom string',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'participant','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function participant_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 4,
  'values' => array( 
      '4' => array( 
          'id' => 4,
          'contact_id' => 2,
          'event_id' => 1,
          'status_id' => 1,
          'role_id' => 1,
          'register_date' => '20070721000000',
          'source' => 'Online Event Registration: API Testing',
          'fee_level' => '',
          'is_test' => '',
          'is_pay_later' => '',
          'fee_amount' => '',
          'registered_by_id' => '',
          'discount_id' => '',
          'fee_currency' => '',
          'campaign_id' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* participant_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/