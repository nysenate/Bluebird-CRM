<?php



/*
 demonstrates use of Sort param (available in many api functions). Also, getsingle
 */
function option_value_getvalue_example(){
$params = array( 
  'option_group_id' => 1,
  'version' => 3,
  'options' => array( 
      'sort' => 'label DESC',
      'limit' => 1,
    ),
);

  require_once 'api/api.php';
  $result = civicrm_api( 'option_value','getvalue',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function option_value_getvalue_expectedresult(){

  $expectedResult = array( 
  'id' => '4',
  'option_group_id' => '1',
  'label' => 'SMS',
  'value' => '4',
  'filter' => 0,
  'weight' => '4',
  'is_optgroup' => 0,
  'is_reserved' => 0,
  'is_active' => '1',
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetValueOptionValueSort and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3OptionValueTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/