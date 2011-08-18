<?php



/*
 example demonstrates use of Replace in a nested API call
 */
function email_replace_example(){
$params = array( 
  'version' => 3,
  'id' => 1,
  'api.email.replace' => array( 
      'values' => array( 
          '0' => array( 
              'location_type_id' => 14,
              'email' => '1-1@example.com',
              'is_primary' => 1,
            ),
          '1' => array( 
              'location_type_id' => 14,
              'email' => '1-2@example.com',
              'is_primary' => 0,
            ),
          '2' => array( 
              'location_type_id' => 14,
              'email' => '1-3@example.com',
              'is_primary' => 0,
            ),
          '3' => array( 
              'location_type_id' => 15,
              'email' => '2-1@example.com',
              'is_primary' => 0,
            ),
          '4' => array( 
              'location_type_id' => 15,
              'email' => '2-2@example.com',
              'is_primary' => 0,
            ),
        ),
    ),
);

  require_once 'api/api.php';
  $result = civicrm_api( 'email','replace',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function email_replace_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'contact_id' => '1',
          'contact_type' => 'Organization',
          'sort_name' => 'Unit Test Organization',
          'display_name' => 'Unit Test Organization',
          'do_not_email' => 0,
          'do_not_phone' => 0,
          'do_not_mail' => 0,
          'do_not_sms' => 0,
          'do_not_trade' => 0,
          'is_opt_out' => 0,
          'preferred_mail_format' => 'Both',
          'is_deceased' => 0,
          'organization_name' => 'Unit Test Organization',
          'contact_is_deleted' => 0,
          'id' => '1',
          'api.email.replace' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 5,
              'values' => array( 
                  '0' => array( 
                      'id' => 10,
                      'contact_id' => '1',
                      'location_type_id' => 14,
                      'email' => '1-1@example.com',
                      'is_primary' => 1,
                      'is_billing' => '',
                      'on_hold' => '',
                      'is_bulkmail' => '',
                      'hold_date' => '',
                      'reset_date' => '',
                      'signature_text' => '',
                      'signature_html' => '',
                    ),
                  '1' => array( 
                      'id' => 11,
                      'contact_id' => '1',
                      'location_type_id' => 14,
                      'email' => '1-2@example.com',
                      'is_primary' => 0,
                      'is_billing' => '',
                      'on_hold' => '',
                      'is_bulkmail' => '',
                      'hold_date' => '',
                      'reset_date' => '',
                      'signature_text' => '',
                      'signature_html' => '',
                    ),
                  '2' => array( 
                      'id' => 12,
                      'contact_id' => '1',
                      'location_type_id' => 14,
                      'email' => '1-3@example.com',
                      'is_primary' => 0,
                      'is_billing' => '',
                      'on_hold' => '',
                      'is_bulkmail' => '',
                      'hold_date' => '',
                      'reset_date' => '',
                      'signature_text' => '',
                      'signature_html' => '',
                    ),
                  '3' => array( 
                      'id' => 13,
                      'contact_id' => '1',
                      'location_type_id' => 15,
                      'email' => '2-1@example.com',
                      'is_primary' => 0,
                      'is_billing' => '',
                      'on_hold' => '',
                      'is_bulkmail' => '',
                      'hold_date' => '',
                      'reset_date' => '',
                      'signature_text' => '',
                      'signature_html' => '',
                    ),
                  '4' => array( 
                      'id' => 14,
                      'contact_id' => '1',
                      'location_type_id' => 15,
                      'email' => '2-2@example.com',
                      'is_primary' => 0,
                      'is_billing' => '',
                      'on_hold' => '',
                      'is_bulkmail' => '',
                      'hold_date' => '',
                      'reset_date' => '',
                      'signature_text' => '',
                      'signature_html' => '',
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
* email_replace 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/