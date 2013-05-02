<?php



/*
 demonstrate use of getfields to interogate api
 */
function group_getfields_example(){
$params = array( 
  'version' => 3,
  'action' => 'create',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'group','getfields',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_getfields_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 17,
  'values' => array( 
      'id' => array( 
          'name' => 'id',
          'type' => 1,
          'required' => true,
        ),
      'name' => array( 
          'name' => 'name',
          'type' => 2,
          'title' => 'Name',
          'maxlength' => 64,
          'size' => 30,
        ),
      'title' => array( 
          'name' => 'title',
          'type' => 2,
          'title' => 'Title',
          'maxlength' => 64,
          'size' => 30,
          'api.required' => 1,
        ),
      'description' => array( 
          'name' => 'description',
          'type' => 32,
          'title' => 'Description',
          'rows' => 2,
          'cols' => 60,
        ),
      'source' => array( 
          'name' => 'source',
          'type' => 2,
          'title' => 'Source',
          'maxlength' => 64,
          'size' => 30,
        ),
      'saved_search_id' => array( 
          'name' => 'saved_search_id',
          'type' => 1,
          'FKClassName' => 'CRM_Contact_DAO_SavedSearch',
        ),
      'is_active' => array( 
          'name' => 'is_active',
          'type' => 16,
          'api.default' => 1,
        ),
      'visibility' => array( 
          'name' => 'visibility',
          'type' => 2,
          'title' => 'Visibility',
          'default' => 'User and User Admin Only',
          'enumValues' => 'User and User Admin Only,Public Pages',
          'options' => array( 
              '0' => 'User and User Admin Only',
              '1' => 'Public Pages',
            ),
        ),
      'where_clause' => array( 
          'name' => 'where_clause',
          'type' => 32,
          'title' => 'Where Clause',
        ),
      'select_tables' => array( 
          'name' => 'select_tables',
          'type' => 32,
          'title' => 'Select Tables',
        ),
      'where_tables' => array( 
          'name' => 'where_tables',
          'type' => 32,
          'title' => 'Where Tables',
        ),
      'group_type' => array( 
          'name' => 'group_type',
          'type' => 2,
          'title' => 'Group Type',
          'maxlength' => 128,
          'size' => 45,
        ),
      'cache_date' => array( 
          'name' => 'cache_date',
          'type' => 12,
          'title' => 'Cache Date',
        ),
      'parents' => array( 
          'name' => 'parents',
          'type' => 32,
          'title' => 'Parents',
        ),
      'children' => array( 
          'name' => 'children',
          'type' => 32,
          'title' => 'Children',
        ),
      'is_hidden' => array( 
          'name' => 'is_hidden',
          'type' => 16,
        ),
      'is_reserved' => array( 
          'name' => 'is_reserved',
          'type' => 16,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testgetfields and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/GroupTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/