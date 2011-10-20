<?php



/*
 
 */
function activity_type_create_example(){
$params = array( 
  'weight' => '2',
  'label' => 'send out letters',
  'version' => 3,
  'filter' => 0,
  'is_active' => 1,
  'is_optgroup' => 1,
  'is_default' => 0,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'activity_type','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_type_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 570,
  'values' => array( 
      '570' => array( 
          'id' => 570,
          'option_group_id' => '2',
          'label' => 'send out letters',
          'value' => 33,
          'name' => 'send out letters',
          'grouping' => '',
          'filter' => 0,
          'is_default' => 0,
          'weight' => 2,
          'description' => '',
          'is_optgroup' => 1,
          'is_reserved' => '',
          'is_active' => 1,
          'component_id' => '',
          'domain_id' => '',
          'visibility_id' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testActivityTypeCreate and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3ActivityTypeTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/