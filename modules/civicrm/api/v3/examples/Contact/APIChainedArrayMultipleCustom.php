<?php



/*
 /*this demonstrates the usage of chained api functions. A variety of techniques are used
 */
function contact_get_example(){
$params = array( 
  'id' => 1,
  'version' => 3,
  'api.website.getValue' => array( 
      'return' => 'url',
    ),
  'api.Contribution.getCount' => array(),
  'api.CustomValue.get' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'contact_id' => '1',
          'contact_type' => 'Individual',
          'sort_name' => 'xyz3, abc3',
          'display_name' => 'abc3 xyz3',
          'do_not_email' => 0,
          'do_not_phone' => 0,
          'do_not_mail' => 0,
          'do_not_sms' => 0,
          'do_not_trade' => 0,
          'is_opt_out' => 0,
          'preferred_mail_format' => 'Both',
          'first_name' => 'abc3',
          'last_name' => 'xyz3',
          'is_deceased' => 0,
          'contact_is_deleted' => 0,
          'email_id' => '1',
          'email' => 'man3@yahoo.com',
          'on_hold' => 0,
          'id' => '1',
          'api.website.getValue' => 'http://civicrm.org',
          'api.Contribution.getCount' => 2,
          'api.CustomValue.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 3,
              'values' => array( 
                  'testGetIndividualWithChainedArraysAndMultipleCustom' => array( 
                      '0' => array( 
                          'testGetIndividualWithChainedArraysAndMultipleCustom' => 'value 4',
                        ),
                    ),
                  'API_Custom_Group' => array( 
                      '1' => array( 
                          'Cust_Field' => 'value 2',
                          'field_2' => 'warm beer',
                          'field_3' => '',
                        ),
                      '2' => array( 
                          'Cust_Field' => 'value 3',
                          'field_2' => '',
                          'field_3' => '',
                        ),
                    ),
                  'another_group' => array( 
                      '1' => array( 
                          'Cust_Field' => '',
                          'field_2' => 'vegemite',
                          'field_3' => '',
                        ),
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
* contact_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/