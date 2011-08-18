<?php



/*
 
 */
function pledge_payment_update_example(){
$params = array( 
  'contact_id' => 1,
  'pledge_id' => 1,
  'contribution_id' => 1,
  'version' => 3,
  'status_id' => 2,
  'actual_amount' => 20,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'pledge_payment','update',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_payment_update_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 33,
  'values' => array( 
      '33' => array( 
          'id' => 33,
          'pledge_id' => '1',
          'contribution_id' => '1',
          'scheduled_amount' => '20.00',
          'actual_amount' => '20.00',
          'currency' => 'USD',
          'scheduled_date' => '2011-07-11 00:00:00',
          'reminder_date' => '',
          'reminder_count' => 0,
          'status_id' => 1,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* pledge_payment_update 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/