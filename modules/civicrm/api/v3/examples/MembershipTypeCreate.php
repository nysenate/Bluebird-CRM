<?php



/*
 
 */
function membership_type_create_example(){
$params = array( 
  'name' => '40+ Membership',
  'description' => 'people above 40 are given health instructions',
  'member_of_contact_id' => 1,
  'contribution_type_id' => 1,
  'domain_id' => '1',
  'minimum_fee' => '200',
  'duration_unit' => 'month',
  'duration_interval' => '10',
  'period_type' => 'rolling',
  'visibility' => 'public',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership_type','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_type_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 2,
  'values' => array( 
      '2' => array( 
          'id' => 2,
          'domain_id' => 1,
          'name' => '40+ Membership',
          'description' => 'people above 40 are given health instructions',
          'member_of_contact_id' => 1,
          'contribution_type_id' => 1,
          'minimum_fee' => '200',
          'duration_unit' => 'month',
          'duration_interval' => '10',
          'period_type' => 'rolling',
          'fixed_period_start_day' => '',
          'fixed_period_rollover_day' => '',
          'relationship_type_id' => '',
          'relationship_direction' => '',
          'visibility' => 'public',
          'weight' => '',
          'renewal_msg_id' => '',
          'renewal_reminder_day' => '',
          'receipt_text_signup' => '',
          'receipt_text_renewal' => '',
          'autorenewal_msg_id' => '',
          'auto_renew' => '',
          'is_active' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_type_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/