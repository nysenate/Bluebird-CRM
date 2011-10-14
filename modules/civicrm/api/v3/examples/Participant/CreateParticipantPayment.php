<?php



/*
 single function to create contact w partipation & contribution. Note that in the
      case of 'contribution' the 'create' is implied (api.contribution.create)
 */
function participant_create_example(){
$params = array( 
  'contact_type' => 'Individual',
  'display_name' => 'dlobo',
  'version' => 3,
  'api.participant' => array( 
      'event_id' => 35,
      'status_id' => 1,
      'role_id' => 1,
      'format.only_id' => 1,
    ),
  'api.contribution.create' => array( 
      'contribution_type_id' => 11,
      'total_amount' => 100,
      'format.only_id' => 1,
    ),
  'api.participant_payment.create' => array( 
      'contribution_id' => '$value.api.contribution.create',
      'participant_id' => '$value.api.participant',
    ),
);

  require_once 'api/api.php';
  $result = civicrm_api( 'participant','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function participant_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 5,
  'values' => array( 
      '5' => array( 
          'id' => 5,
          'contact_type' => 'Individual',
          'contact_sub_type' => '',
          'do_not_email' => '',
          'do_not_phone' => '',
          'do_not_mail' => '',
          'do_not_sms' => '',
          'do_not_trade' => '',
          'is_opt_out' => '',
          'legal_identifier' => '',
          'external_identifier' => '',
          'sort_name' => '',
          'display_name' => 'dlobo',
          'nick_name' => '',
          'legal_name' => '',
          'image_URL' => '',
          'preferred_communication_method' => '',
          'preferred_language' => 'en_US',
          'preferred_mail_format' => '',
          'api_key' => '',
          'first_name' => '',
          'middle_name' => '',
          'last_name' => '',
          'prefix_id' => '',
          'suffix_id' => '',
          'email_greeting_id' => '',
          'email_greeting_custom' => '',
          'email_greeting_display' => '',
          'postal_greeting_id' => '',
          'postal_greeting_custom' => '',
          'postal_greeting_display' => '',
          'addressee_id' => '',
          'addressee_custom' => '',
          'addressee_display' => '',
          'job_title' => '',
          'gender_id' => '',
          'birth_date' => '',
          'is_deceased' => '',
          'deceased_date' => '',
          'household_name' => '',
          'primary_contact_id' => '',
          'organization_name' => '',
          'sic_code' => '',
          'user_unique_id' => '',
          'api.participant' => 110,
          'api.contribution.create' => 1,
          'api.participant_payment.create' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'id' => 1,
              'values' => array( 
                  '0' => array( 
                      'id' => 1,
                      'participant_id' => 110,
                      'contribution_id' => 1,
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
* testCreateParticipantWithPayment and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ParticipantTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/