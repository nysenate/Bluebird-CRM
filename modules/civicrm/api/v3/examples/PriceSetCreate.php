<?php



/*
 
 */
function price_set_create_example(){
$params = array( 
  'version' => 3,
  'name' => 'default_goat_priceset',
  'title' => 'Goat accessories',
  'is_active' => 1,
  'help_pre' => 'Please describe your goat in detail',
  'help_post' => 'thank you for your time',
  'extends' => 2,
  'contribution_type_id' => 1,
  'is_quick_config' => 1,
  'is_reserved' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'price_set','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function price_set_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 3,
  'values' => array( 
      '3' => array( 
          'id' => 3,
          'domain_id' => '',
          'name' => 'default_goat_priceset',
          'title' => 'Goat accessories',
          'is_active' => 1,
          'help_pre' => 'Please describe your goat in detail',
          'help_post' => 'thank you for your time',
          'javascript' => '',
          'extends' => 2,
          'contribution_type_id' => 1,
          'is_quick_config' => 1,
          'is_reserved' => 1,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testCreatePriceSet and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/PriceSetTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/