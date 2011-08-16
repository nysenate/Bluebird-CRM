<?php



/*
 
 */
function activity_get_example(){
$params = array( 
  'activity_id' => 13,
  'version' => 3,
  'sequential' => 1,
  'return.assignee_contact_id' => 1,
  'api.contact.get' => array( 
      'id' => '$value.source_contact_id',
    ),
);

  require_once 'api/api.php';
  $result = civicrm_api( 'activity','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 13,
  'values' => array( 
      '0' => array( 
          'id' => '13',
          'source_contact_id' => '17',
          'activity_type_id' => '1',
          'subject' => 'test activity type id',
          'status_id' => '1',
          'priority_id' => '1',
          'assignee_contact_id' => array( 
              '0' => '18',
            ),
          'api.contact.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'id' => 17,
              'values' => array( 
                  '0' => array( 
                      'contact_id' => '17',
                      'contact_type' => 'Individual',
                      'display_name' => 'Test Contact',
                      'is_opt_out' => 0,
                      'first_name' => 'Test',
                      'last_name' => 'Contact',
                      'contact_is_deleted' => 0,
                      'id' => '17',
                    ),
                ),
            ),
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testActivityGetGoodID1 and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3ActivityTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/