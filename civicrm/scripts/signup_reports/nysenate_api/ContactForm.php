<?php
require_once 'get_services/xmlrpc-api-messages.inc';
require_once 'Form.php';
require_once 'NySenateContact.php';


class ContactForm extends Form
{
  function __construct($api_key, $domain_name)
  {
    parent::__construct($api_key, $domain_name);
  }


  function getRawEntries($start_date, $end_date, $start_id, $end_id, $limit = 1000)
  {
    $service = new ContactMessages($this->domain_name, $this->api_key);
    $values = $service->get(array(
        'start_date' => $start_date,
        'end_date' => $end_date,
        'start_mid' => $start_id,
        'end_mid' => $end_id,
        'limit' => $limit,
      'source_form' => NULL,
      'senator_short_name' => NULL,
      'district_number' => NULL,
    ));

    return $values;
  } // getRawEntries()


  function getFormContacts($start_date, $end_date, $start_id, $end_id, $limit = 1000)
  {
    $values = $this->getRawEntries(
      $start_date, $end_date, $start_id, $end_id, $limit
    );

    $contacts = array();

    foreach ($values['items'] as $entry) {
      if ($this->valid_instance($entry['to_short_name'])) {
        $contacts[] = $this->formContactFromEntry($entry);
      }
    }

    return $contacts;
  } // getFormContacts()


  /*
   * given an entry from the services module
   * convert it in to a NySenateContact object
   */
  function formContactFromEntry($entry)
  {
    $params = $this->paramsFromEntry($entry);

    $formContact = new NySenateContact();
    $formContact->civicrm_contact_params = $params;

    if ($entry['issues'] && count($entry['issues']) > 0) {
      if (preg_match("/^\s*$/", $entry['issues'][0])) {
        $entry['issues'] = NULL;
      }
    }

    $formContact->activity =
      array(
        'activity_name' => 'Other',
        'activity_type' => 'Website Contact',
        'activity_subject' => $entry['subject'],
        'activity_details' => $this->getActivityNotes($entry),
        'keywords' => $entry['issues']
      );

    $formContact->issues = $entry['issues'];
    $formContact->senator_short_name = $entry['to_short_name'];
    return $formContact;
  } // formContactFromEntry()


  function getActivityNotes($entry)
  {
    $notes = "";

    $notes = $notes."<p>Submitted on "
          .date("F j, Y, g:i a", $entry['submitted'])."</p>";

    $name = $entry['first_name']." ".$entry['last_name'];

    $notes = $notes."<p>".$name."'s message:</p>";
    $notes = $notes."<p>\"".$entry['message']."\"</p>";

    $notes = $notes."<p>The submitter <b>is </b>";
    if ($entry['voter_registered'] == 1) {
      $notes = $notes."<b>not</b> ";
    }
    $notes = $notes."registered to vote at the following address:<br/>";
    $notes = $notes.$entry['address']."<br/>".$entry['city']
             .", ".$entry['state'].", ".$entry['zip']."</p>";

    return $notes;
  } // getActivityNotes()


  /**
   *
   * Given a contact entry from the services module
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
    $params['first_name'] = $entry['first_name'];
    $params['last_name'] = $entry['last_name'];

    $address = array(
      'street_address' => $entry['address'],
      'city' => $entry['city'],
      'state_province' => $entry['state'],
      'postal_code' => $entry['zip']
    );

    if (array_key_exists('apartment', $entry)) {
      $address['street_address2'] = $entry['apartment'];
    }
    else if (array_key_exists('address2', $entry)) {
      $address['street_address2'] = $entry['address2'];
    }

    //geocode, dist assign and format address
    require_once 'CRM/Utils/SAGE.php';
    CRM_Utils_SAGE::lookup(&$address);

    $address['location_type_id'] = '1';
    $address['is_primary'] = true;

    $params['address'] = array();
    $params['address'][] = $address;

    $params['phone'] = array(
      'phone' => $entry['phone'],
      'location_type_id' => '1',
      'phone_type_id' => '1',
      'is_primary' => true
    );

    $params['email'] = array();
    $params['email'][] = array(
      'email' => $entry['from_email'],
      'email_type_id' => '1',
      'is_primary' => true
    );

    $params['source'] = "Website - Contact";
    return $params;
  } // paramsFromEntry()
}
