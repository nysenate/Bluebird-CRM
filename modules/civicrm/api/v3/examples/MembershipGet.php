<?php



/*
 
 */
function membership_get_example(){
$params = array( 
  'version' => 3,
  'membership_type_id' => 9,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 6,
  'values' => array( 
      '6' => array( 
          'id' => '6',
          'contact_id' => '1',
          'membership_type_id' => '9',
          'join_date' => '2009-01-21',
          'start_date' => '2009-01-21',
          'end_date' => '2009-12-21',
          'source' => 'Payment',
          'status_id' => '16',
          'is_override' => '1',
          'is_test' => 0,
          'is_pay_later' => 0,
          'membership_name' => 'General',
          'relationship_name' => 'Child of',
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
* testGetWithParamsMemberShipIdAndCustom and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3MembershipTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/