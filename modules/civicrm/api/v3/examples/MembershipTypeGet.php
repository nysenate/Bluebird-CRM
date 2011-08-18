<?php



/*
 
 */
function membership_type_get_example(){
$params = array( 
  'id' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership_type','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_type_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'domain_id' => '1',
          'name' => 'General',
          'description' => '',
          'member_of_contact_id' => '1',
          'contribution_type_id' => '1',
          'minimum_fee' => '0.00',
          'duration_unit' => 'year',
          'duration_interval' => '1',
          'period_type' => 'rolling',
          'fixed_period_start_day' => '',
          'fixed_period_rollover_day' => '',
          'relationship_type_id' => '',
          'relationship_direction' => '',
          'visibility' => '1',
          'weight' => '',
          'renewal_msg_id' => '',
          'renewal_reminder_day' => '',
          'receipt_text_signup' => '',
          'receipt_text_renewal' => '',
          'autorenewal_msg_id' => '',
          'auto_renew' => 0,
          'is_active' => '1',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_type_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/