<?php



/*
 
 */
function email_replace_example(){
$params = array( 
  'version' => 3,
  'contact_id' => 1,
  'values' => array( 
      '0' => array( 
          'location_type_id' => 18,
          'email' => '1-1@example.com',
          'is_primary' => 1,
        ),
      '1' => array( 
          'location_type_id' => 18,
          'email' => '1-2@example.com',
          'is_primary' => 0,
        ),
      '2' => array( 
          'location_type_id' => 18,
          'email' => '1-3@example.com',
          'is_primary' => 0,
        ),
      '3' => array( 
          'location_type_id' => 19,
          'email' => '2-1@example.com',
          'is_primary' => 0,
        ),
      '4' => array( 
          'location_type_id' => 19,
          'email' => '2-2@example.com',
          'is_primary' => 0,
        ),
    ),
);

  require_once 'api/api.php';
  $result = civicrm_api( 'email','replace',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function email_replace_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 5,
  'values' => array( 
      '11' => array( 
          'id' => 11,
          'contact_id' => 1,
          'location_type_id' => 18,
          'email' => '1-1@example.com',
          'is_primary' => 1,
          'is_billing' => '',
          'on_hold' => '',
          'is_bulkmail' => '',
          'hold_date' => '',
          'reset_date' => '',
          'signature_text' => '',
          'signature_html' => '',
        ),
      '12' => array( 
          'id' => 12,
          'contact_id' => 1,
          'location_type_id' => 18,
          'email' => '1-2@example.com',
          'is_primary' => 0,
          'is_billing' => '',
          'on_hold' => '',
          'is_bulkmail' => '',
          'hold_date' => '',
          'reset_date' => '',
          'signature_text' => '',
          'signature_html' => '',
        ),
      '13' => array( 
          'id' => 13,
          'contact_id' => 1,
          'location_type_id' => 18,
          'email' => '1-3@example.com',
          'is_primary' => 0,
          'is_billing' => '',
          'on_hold' => '',
          'is_bulkmail' => '',
          'hold_date' => '',
          'reset_date' => '',
          'signature_text' => '',
          'signature_html' => '',
        ),
      '14' => array( 
          'id' => 14,
          'contact_id' => 1,
          'location_type_id' => 19,
          'email' => '2-1@example.com',
          'is_primary' => 0,
          'is_billing' => '',
          'on_hold' => '',
          'is_bulkmail' => '',
          'hold_date' => '',
          'reset_date' => '',
          'signature_text' => '',
          'signature_html' => '',
        ),
      '15' => array( 
          'id' => 15,
          'contact_id' => 1,
          'location_type_id' => 19,
          'email' => '2-2@example.com',
          'is_primary' => 0,
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
* 
* testReplaceEmail and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/EmailTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/