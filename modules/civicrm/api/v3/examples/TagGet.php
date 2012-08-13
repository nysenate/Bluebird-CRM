<?php



/*
 
 */
function tag_get_example(){
$params = array( 
  'id' => 6,
  'name' => 'New Tag3584721333',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'tag','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function tag_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 6,
  'values' => array( 
      '6' => array( 
          'id' => '6',
          'name' => 'New Tag3584721333',
          'description' => 'This is description for New Tag 447013423',
          'is_selectable' => '1',
          'is_reserved' => 0,
          'is_tagset' => 0,
          'used_for' => 'civicrm_contact',
          'created_date' => '2012-05-11 22:57:28',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGet and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/TagTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/