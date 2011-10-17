<?php



/*
 /*this demonstrates the use of CustomValue get
 */
function custom_value_get_example(){
$params = array( 
  'id' => 2,
  'version' => 3,
  'entity_id' => 2,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'custom_value','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function custom_value_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 7,
  'values' => array( 
      '8' => array( 
          'entity_id' => 2,
          'latest' => 'value 1',
          'id' => '8',
          '0' => 'value 1',
        ),
      '9' => array( 
          'entity_id' => 2,
          'latest' => 'value 3',
          'id' => '9',
          '1' => 'value 2',
          '2' => 'value 3',
        ),
      '10' => array( 
          'entity_id' => 2,
          'latest' => '',
          'id' => '10',
          '1' => 'warm beer',
          '2' => '',
        ),
      '11' => array( 
          'entity_id' => 2,
          'latest' => '',
          'id' => '11',
          '1' => 'fl* w*',
          '2' => '',
        ),
      '12' => array( 
          'entity_id' => 2,
          'latest' => 'coffee',
          'id' => '12',
          '1' => '',
          '2' => 'coffee',
        ),
      '13' => array( 
          'entity_id' => 2,
          'latest' => 'value 4',
          'id' => '13',
          '1' => '',
          '2' => 'value 4',
        ),
      '14' => array( 
          'entity_id' => 2,
          'latest' => '',
          'id' => '14',
          '1' => 'vegemite',
          '2' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetMultipleCustomValues and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/CustomValueTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/