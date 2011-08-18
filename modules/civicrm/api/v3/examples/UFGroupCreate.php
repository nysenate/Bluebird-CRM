<?php



/*
 
 */
function uf_group_create_example(){
$params = array( 
  'add_captcha' => 1,
  'add_contact_to_group' => 2,
  'cancel_URL' => 'http://example.org/cancel',
  'created_date' => '2009-06-27 00:00:00',
  'created_id' => 69,
  'group' => 2,
  'group_type' => 'Individual,Contact',
  'help_post' => 'help post',
  'help_pre' => 'help pre',
  'is_active' => 0,
  'is_cms_user' => 1,
  'is_edit_link' => 1,
  'is_map' => 1,
  'is_reserved' => 1,
  'is_uf_link' => 1,
  'is_update_dupe' => 1,
  'name' => 'Test_Group',
  'notify' => 'admin@example.org',
  'post_URL' => 'http://example.org/post',
  'title' => 'Test Group',
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'uf_group','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_group_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 12,
  'values' => array( 
      '12' => array( 
          'id' => 12,
          'is_active' => 0,
          'group_type' => 'Individual,Contact',
          'title' => 'Test Group',
          'help_pre' => 'help pre',
          'help_post' => 'help post',
          'limit_listings_group_id' => 2,
          'post_URL' => 'http://example.org/post',
          'add_to_group_id' => 2,
          'add_captcha' => 1,
          'is_map' => 1,
          'is_edit_link' => 1,
          'is_uf_link' => 1,
          'is_update_dupe' => 1,
          'cancel_URL' => 'http://example.org/cancel',
          'is_cms_user' => 1,
          'notify' => 'admin@example.org',
          'is_reserved' => 1,
          'name' => 'Test_Group',
          'created_id' => 69,
          'created_date' => '2009-06-27 00:00:00',
          'is_proximity_search' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* uf_group_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/