<?php



/*
 
 */
function line_item_get_example(){
$params = array( 
  'version' => 3,
  'entity_table' => 'civicrm_contribution',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'line_item','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function line_item_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 2,
  'values' => array( 
      '2' => array( 
          'id' => '2',
          'entity_table' => 'civicrm_contribution',
          'entity_id' => '2',
          'price_field_id' => '1',
          'label' => '',
          'qty' => 0,
          'unit_price' => '0.00',
          'line_total' => '0.00',
          'price_field_value_id' => '1',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetBasicLineItem and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/LineItemTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/