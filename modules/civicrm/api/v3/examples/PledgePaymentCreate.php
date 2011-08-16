<?php



/*
 
 */
function pledge_payment_create_example(){
$params = array( 
  'contact_id' => 1,
  'pledge_id' => 1,
  'contribution_id' => 1,
  'version' => 3,
  'status_id' => 1,
  'actual_amount' => 20,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'pledge_payment','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_payment_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 16,
  'values' => array( 
      '16' => array( 
          'id' => '16',
          'pledge_id' => 1,
          'contribution_id' => 1,
          'scheduled_amount' => '',
          'actual_amount' => 20,
          'currency' => 'USD',
          'scheduled_date' => '',
          'reminder_date' => '',
          'reminder_count' => '',
          'status_id' => 1,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* pledge_payment_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/