<?php



/*
 
 */
function pledge_get_example(){
$params = array( 
  'pledge_id' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'pledge','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'contact_id' => '1',
          'contact_type' => 'Individual',
          'sort_name' => 'Anderson, Anthony',
          'display_name' => 'Mr. Anthony Anderson II',
          'pledge_id' => '1',
          'pledge_amount' => '100.00',
          'pledge_create_date' => '2011-07-29 00:00:00',
          'pledge_status' => 'Pending',
          'pledge_next_pay_date' => '2011-07-31 00:00:00',
          'pledge_next_pay_amount' => '20.00',
          'pledge_contribution_type' => 'Donation',
          'pledge_frequency_interval' => '5',
          'pledge_frequency_unit' => 'year',
          'pledge_is_test' => 0,
          'id' => '1',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetPledge and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3PledgeTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/