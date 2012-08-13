<?php



/*
 demonstrates is_current filter
 */
function relationship_get_example(){
$params = array( 
  'version' => 3,
  'filters' => array( 
      'is_current' => 1,
    ),
);

  require_once 'api/api.php';
  $result = civicrm_api( 'relationship','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 2,
  'values' => array( 
      '2' => array( 
          'id' => '2',
          'contact_id_a' => '1',
          'contact_id_b' => '2',
          'relationship_type_id' => '31',
          'start_date' => '2008-12-20',
          'is_active' => '1',
          'is_permission_a_b' => 0,
          'is_permission_b_a' => 0,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetIsCurrent and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/RelationshipTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/