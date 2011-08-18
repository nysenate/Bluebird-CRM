<?php



/*
 
 */
function contribution_get_example(){
$params = array( 
  'contribution_id' => 1,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'contribution','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_get_expectedresult(){

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
          'contribution_id' => '1',
          'currency' => 'USD',
          'receive_date' => '2011-07-15 00:00:00',
          'non_deductible_amount' => '10.00',
          'total_amount' => '100.00',
          'fee_amount' => '51.00',
          'net_amount' => '91.00',
          'trxn_id' => '23456',
          'invoice_id' => '78910',
          'contribution_source' => 'SSF',
          'is_test' => 0,
          'is_pay_later' => 0,
          'contribution_type_id' => '11',
          'contribution_type' => 'Prize',
          'accounting_code' => '1005',
          'contribution_status_id' => '1',
          'contribution_status' => 'Completed',
          'id' => '1',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* contribution_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/