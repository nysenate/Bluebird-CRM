<?php



/*
 
 */
function option_group_create_example(){
$params = array( 
  'version' => 3,
  'sequential' => 1,
  'name' => 'civicrm_event.amount.560',
  'is_reserved' => 1,
  'is_active' => 1,
  'api.OptionValue.create' => array( 
      'label' => 'workshop',
      'value' => 35,
      'is_default' => 1,
      'is_active' => 1,
      'format.only_id' => 1,
    ),
);

  require_once 'api/api.php';
  $result = civicrm_api( 'option_group','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function option_group_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 70,
  'values' => array( 
      '0' => array( 
          'id' => 70,
          'name' => 'civicrm_event.amount.560',
          'label' => '',
          'description' => '',
          'is_reserved' => 1,
          'is_active' => 1,
          'api.OptionValue.create' => 570,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* option_group_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/