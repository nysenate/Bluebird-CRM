<?php



/*
 
 */
function domain_create_example(){
$params = array( 
  'name' => 'A-team domain',
  'description' => 'domain of chaos',
  'version' => 3,
  'domain_version' => '3.4.1',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'domain','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function domain_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 2,
  'values' => array( 
      '2' => array( 
          'id' => 2,
          'name' => 'A-team domain',
          'description' => 'domain of chaos',
          'config_backend' => '',
          'version' => '3.4.1',
          'loc_block_id' => '',
          'locales' => '',
          'locale_custom_strings' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* domain_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/