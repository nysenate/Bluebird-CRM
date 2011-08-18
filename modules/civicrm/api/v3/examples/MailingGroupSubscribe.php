<?php



/*
 
 */
function mailing_group_subscribe_example(){
$params = array( 
  'email' => 'test@test.test',
  'group_id' => 2,
  'contact_id' => 1,
  'version' => 3,
  'hash' => 'b15de8b64e2cec34',
  'time_stamp' => '20101212121212',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'mailing_group','subscribe',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function mailing_group_subscribe_expectedresult(){

  $expectedResult = array( 
  'is_error' => 1,
  'error_message' => 'mail(): Failed to connect to mailserver at "localhost" port 25, verify your "SMTP" and "smtp_port" setting in php.ini or use ini_set()',
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* mailing_group_subscribe 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/