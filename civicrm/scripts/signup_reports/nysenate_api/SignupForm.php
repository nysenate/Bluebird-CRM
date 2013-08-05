<?php
require_once 'get_services/xmlrpc-api-signups.inc';
require_once 'Form.php';

class SignupForm extends Form
{
  function __construct($api_key, $domain_name)
  {
    parent::__construct($api_key, $domain_name);
  }


  function getRawEntries($start_date, $end_date, $start_id, $end_id, $limit = 1000)
  {
    $service = new SignupGet($this->domain_name, $this->api_key);
    $values = $service->get(array(
        'start_date' => $start_date,
        'end_date' => $end_date,
        'start_sid' => $start_id,
        'end_sid' => $end_id,
        'limit' => $limit,
    ));

    return $values;
  } // getRawEntries()


  function getFormContacts($start_date, $end_date, $start_id, $end_id, $limit = 1000)
  {
    $values = $this->getRawEntries($start_date, $end_date,
                                   $start_id, $end_id, $limit);
    $contacts = array();

    foreach ($values['accounts'] as $account) {
      $senator = NULL;
      if ($this->valid_instance($account['name'])) {
        $senator = true;
      }
      else if ($account['name'] == "nyss") {
        $senator = false;
      }
      else {
        continue;
      }

      foreach ($account['contacts'] as $entry) {
        $formContact = $this->formContactFromEntry($entry);

        if ($senator) {
          $formContact->senator_short_name = $account['name'];
        }
        else {
          if (array_key_exists('custom_47_-1', $formContact->civicrm_contact_params['address'][0])) {
            //custom_47_-1 is the bluebird mapping for senate district
            $sd = $formContact->civicrm_contact_params['address'][0]['custom_47_-1'];
            if ($sd && !preg_match('/^\s*$/',$sd)) {
              //get senator map to map district number
              //to senator short name
              $senators = $this->get_senator_map($this->api_key, $this->domain_name);
              $formContact->senator_short_name = $senators[$sd]['short_name'];
            }
          }
          else {
            //could not be geocoded
            //TODO
          }
        }

        if ($formContact->senator_short_name) {
          $contacts[] = $formContact;
        }
      }
    }
    return $contacts;
  } // getFormContacts()


  /*
   * given an entry from the services module
   * convert it in to a NySenateContact object
   *
   * note that this does not add senator_short_name
   * since that data lies outside of the individual
   * entry record
   */
  function formContactFromEntry($entry)
  {
    $params = $this->paramsFromEntry($entry);

    $formContact = new NySenateContact();
    $formContact->civicrm_contact_params = $params;
    $formContact->activity =
      array(
        'activity_name' => 'Other',
        'activity_type' => 'Website Signup',
        'activity_subject' => 'Signup Submission',
        'keywords' => $entry['issues']
      );
    $formContact->issues = $entry['issues'];
    return $formContact;
  } // formContactFromEntry()


  /**
   *
   * Given a signup entry from the services module
   * convert it to params readable by civi
   * @param $entry
   * @param $contact_type
   */
  function paramsFromEntry($entry, $contact_type = "Individual")
  {
    static $config;

    if (!$config) {
      $config = $this->get_config();
    }

    $params = array();
    $params['contact_type'] = $contact_type;
    $params['first_name'] = $entry['firstName'];
    $params['last_name'] = $entry['lastName'];

    $address = array(
      'street_address' => $entry['address1'],
      'street_address2' => $entry['address2'],
      'city' => $entry['city'],
      'state_province' => $entry['state'],
      'postal_code' => $entry['zip']
    );

    //geocode, dist assign and format address
    require_once 'CRM/Utils/SAGE.php';
    CRM_Utils_SAGE::lookup($address);

    $address['location_type_id'] = '1';
    $address['is_primary'] = true;

    $params['address'] = array();
    $params['address'][] = $address;

    $params['phone'] = array(
      'phone' => $entry['phoneMobile'],
      'location_type_id' => '1',
      'phone_type_id' => '1',
      'is_primary' => true
    );

    $params['email'] = array();
    $params['email'][] = array(
      'email' => $entry['email'],
      'email_type_id' => '1',
      'is_primary' => true
    );

    $params['source'] = "Website - Signup";
    return $params;
  } // paramsFromEntry()
}
