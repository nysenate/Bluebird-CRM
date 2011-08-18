<?php



/*
 
 */
function membership_payment_get_example(){
$params = array( 
  'contribution_id' => 2,
  'membership_id' => 2,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership_payment','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_payment_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 2,
  'values' => array( 
      '2' => array( 
          'membership_id' => '2',
          'contribution_id' => '2',
          'membership_contribution_id' => '2',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_payment_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/