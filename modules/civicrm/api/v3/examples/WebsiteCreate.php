<?php



/*
 
 */
function website_create_example(){
$params = array( 
  'version' => 3,
  'contact_id' => 1,
  'url' => 'website.com',
  'website_type_id' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'website','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function website_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => 1,
          'contact_id' => 1,
          'url' => 'website.com',
          'website_type_id' => 1,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testCreateWebsite and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/WebsiteTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/