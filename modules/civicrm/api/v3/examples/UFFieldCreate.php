<?php



/*
 
 */
function uf_field_create_example(){
$params = array( 
  'field_name' => 'country',
  'field_type' => 'Contact',
  'visibility' => 'Public Pages and Listings',
  'weight' => 1,
  'label' => 'Test Country',
  'is_searchable' => 1,
  'is_active' => 1,
  'version' => 3,
  'uf_group_id' => 11,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'uf_field','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_field_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 65,
  'values' => array( 
      '65' => array( 
          'id' => 65,
          'uf_group_id' => 11,
          'field_name' => 'country',
          'is_active' => 1,
          'is_view' => '',
          'is_required' => '',
          'weight' => 1,
          'help_post' => '',
          'help_pre' => '',
          'visibility' => 'Public Pages and Listings',
          'in_selector' => '',
          'is_searchable' => 1,
          'location_type_id' => 'null',
          'phone_type_id' => '',
          'label' => 'Test Country',
          'field_type' => 'Contact',
          'is_reserved' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testCreateUFField and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/UFFieldTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/