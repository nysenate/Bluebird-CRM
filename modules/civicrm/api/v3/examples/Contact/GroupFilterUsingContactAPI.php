<?php



/*
 Get all from group and display contacts
 */
function contact_get_example(){
$params = array( 
  'filter.group_id' => array( 
      '0' => 1,
      '1' => 26,
    ),
  'version' => 3,
  'contact_type' => 'Individual',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'contact_id' => '1',
          'contact_type' => 'Individual',
          'sort_name' => 'man2@yahoo.com',
          'display_name' => 'man2@yahoo.com',
          'do_not_email' => 0,
          'do_not_phone' => 0,
          'do_not_mail' => 0,
          'do_not_sms' => 0,
          'do_not_trade' => 0,
          'is_opt_out' => 0,
          'preferred_mail_format' => 'Both',
          'is_deceased' => 0,
          'contact_is_deleted' => 0,
          'email_id' => '1',
          'email' => 'man2@yahoo.com',
          'on_hold' => 0,
          'id' => '1',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetGroupIDFromContact and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ContactTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/