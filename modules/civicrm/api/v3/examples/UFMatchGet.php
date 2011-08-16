<?php



/*
 
 */
function uf_match_get_example(){
$params = array( 
  'contact_id' => 69,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'uf_match','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_match_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'domain_id' => '1',
          'uf_id' => '42',
          'contact_id' => '69',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* uf_match_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/