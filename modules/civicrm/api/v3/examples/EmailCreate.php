<?php



/*
 
 */
function email_create_example(){
$params = array( 
  'contact_id' => 1,
  'location_type_id' => 6,
  'email' => 'api@a-team.com',
  'is_primary' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'email','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function email_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 2,
  'values' => array( 
      '2' => array( 
          'id' => 2,
          'contact_id' => 1,
          'location_type_id' => 6,
          'email' => 'api@a-team.com',
          'is_primary' => 1,
          'is_billing' => '',
          'on_hold' => '',
          'is_bulkmail' => '',
          'hold_date' => '',
          'reset_date' => '',
          'signature_text' => '',
          'signature_html' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* email_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/