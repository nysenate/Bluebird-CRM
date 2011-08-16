<?php



/*
 
 */
function uf_field_get_example(){
$params = array( 
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'uf_field','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_field_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 37,
  'values' => array( 
      '37' => array( 
          'id' => '37',
          'uf_group_id' => '11',
          'field_name' => 'country',
          'is_active' => '1',
          'is_view' => 0,
          'is_required' => 0,
          'weight' => '1',
          'visibility' => 'Public Pages and Listings',
          'in_selector' => 0,
          'is_searchable' => '1',
          'label' => 'Test Country',
          'field_type' => 'Contact',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* uf_field_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/