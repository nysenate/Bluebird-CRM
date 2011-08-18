<?php



/*
 
 */
function membership_update_example(){
$params = array( 
  'contact_id' => 1,
  'membership_type_id' => 25,
  'join_date' => '2009-01-21',
  'start_date' => '2009-01-21',
  'end_date' => '2009-12-21',
  'source' => 'Payment',
  'is_override' => 1,
  'status_id' => 31,
  'version' => 3,
  'custom_3' => 'custom string',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership','update',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_update_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 17,
  'values' => array( 
      '17' => array( 
          'id' => 17,
          'contact_id' => 1,
          'membership_type_id' => 25,
          'join_date' => '20090121000000',
          'start_date' => '20090121000000',
          'end_date' => '20091221000000',
          'source' => 'Payment',
          'status_id' => 31,
          'is_override' => 1,
          'reminder_date' => 'null',
          'owner_membership_id' => '',
          'is_test' => '',
          'is_pay_later' => '',
          'contribution_recur_id' => '',
          'campaign_id' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testUpdateWithCustom and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3MembershipTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/