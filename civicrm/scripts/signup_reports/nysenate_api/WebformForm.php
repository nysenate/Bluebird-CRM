<?php
require_once 'get_services/xmlrpc-api-webforms.inc';
require_once 'Form.php';


class WebformForm extends Form
{
  public static $contact_regexes =
    array(
      'first_name' => '((?!(.*(last|business|of|career|language|plow|online).*))(?=.*(first|name).*).*)',
      'last_name' => '((?!(.*(first).*))(?=.*(last).*).*)',
      'address' => '((?!(.*(2|e_?mail|city|zip|of|plow|support|cause).*))(?=.*(address|addr|street|add1).*).*)',
      'address2' => '((?!(.*(e_?mail|city|zip|of|plow|support).*))(?=.*(address_?2|add2).*).*)',
      'city' => '((?!(.*(state|zip|hall).*))(?=.*(city|town|village).*).*)',
      'state' => 'state',
      'zip5' => '((?!(.*(address|city|4).*))(?=.*(zip_?5?).*).*)',
      'full_address' => '(city_state|address_city__zip|city_state_zip_code)',
      'phone' => '((?!(.*(cell).*))(?=.*(phone).*).*)',
      'cell' => '((?=.*((cell_?(phone)?|mobile_number).*)).*)',
      'email' => '((?!(.*(2).*))(?=.*(e[-_]?mail).*).*)'
    );

  public static $accepted_form_types =
                   array('textfield', 'select', 'textarea', 'grid');

  public static $form_cache = array();


  function __construct($api_key, $domain_name) {
    parent::__construct($api_key, $domain_name);
  }


  function getRawEntries($start_date, $end_date, $start_id, $end_id, $limit = 1000)
  {
    $service = new WebformResponses($this->domain_name, $this->api_key);
    $values = $service->get(array(
        'start_date' => $start_date,
        'end_date' => $end_date,
        'start_sid' => $start_id,
        'end_sid' => $end_id,
        'limit' => $limit,
        'status' => 1,
        'nid' => NULL,
        'uid' => NULL
    ));

    return $values;
  } // getRawEntries()


  function getFormContacts($start_date, $end_date, $start_id, $end_id, $limit = 1000)
  {
    $values = $this->getRawEntries(
      $start_date, $end_date, $start_id, $end_id, $limit
    );

    $nids = $values["nids"];

    $contacts = array();

    foreach ($nids as $nid => $form) {
      $form_mapper = $this->getFormMapper($nid, $form);

      //skip if we don't have the short name
      if (preg_match("/^\s$/",$form_mapper['senator_short_name'])
          || !$this->valid_instance($form_mapper['senator_short_name'])) {
        continue;
      }

      $submissions = $form['sids'];

      //make sure current form is readable
      if ($this->canDedupe($form_mapper['contact_mapper'])) {
        foreach ($submissions as $sid => $entry) {
          //get contact, no need to check if form mapper is valid
          $contact = $this->formContactFromEntry($entry, true);

          if ($contact) {
            $contacts[] = $contact;
          }
        }
      }
    }

    return $contacts;
  } // getFormContacts()


  function formContactFromEntry($entry, $force = false)
  {
    $form_mapper = $this->getFormMapper($entry['nid']);

    if ($force || $form_mapper && $this->canDedupe($form_mapper['contact_mapper'])) {
      $contact_mapper = $form_mapper['contact_mapper'];
      $params = array();

      foreach ($contact_mapper as $key => $value) {
        //if a field was added in after forms submission
        //it's possible it will come across as null
        if (array_key_exists($value, $entry['values'])) {
          $data = $entry['values'][$value]['data'][0];

          //check if data is null or whitespace
          if ($data && !preg_match("/^\s*$/", $data)) {
            $params[$key] = $entry['values'][$value]['data'][0];
          }
          else {
            $params[$key] = null;
          }
        }
      }

      //one last check to see if the user submitted enough
      //information to dedupe
      if ($this->canDedupe($params)) {
        $params = $this->paramsFromEntry($params);
        $formContact = new NySenateContact();
        $formContact->civicrm_contact_params = $params;
        $response = $this->htmlFromQuestions($this->questionsFromEntry($entry, $form_mapper));

        $formContact->activity =
          array(
            'activity_name' => 'Other',
            'activity_type' => 'Website Initiative',
            'activity_subject' => $form_mapper['title'],
            'activity_details' => $response
          );

        $formContact->senator_short_name = $form_mapper['senator_short_name'];
        $formContact->initiative = array('title' => $form_mapper['title']);
        return $formContact;
      }
    }

    return null;
  } // formContactFromEntry()


  private function questionsFromEntry($entry, $form_mapper)
  {
    $questions = array();

    foreach ($entry['values'] as $field_key => $form_field) {
      if (!array_key_exists($field_key, $form_mapper['questions'])) {
        continue;
      }

      $question = $form_mapper['questions'][$field_key];

      if ($question) {
        $data = $form_field['data'];
        $arr = array('question' => $question['question']);

        switch ($question['type']) {
          case 'textfield':
          case 'textarea':
            $arr['response'] = $data[0];
            break;
          case 'select':
            foreach ($data as $option_id => $value) {
              $selection[] = $question['items'][$value];
            }
            $arr['response'] = implode(", ", $selection);
            break;
          case 'grid':
            $raw_response = '';
            foreach ($data as $option_id => $value) {
              $raw_response = $raw_response.$question['questions'][$option_id].': '.$question['options'][$value]."\n";
            }
            $arr['response'] = $raw_response;
            break;
        }

        $questions[] = $arr;
      }
    }

    return $questions;
  } // questionsFromEntry()


  private function htmlFromQuestions($questions)
  {
    $str = '';

    foreach ($questions as $question) {
      $str = $str."<p><span style=\"font-size:16px;\">".$question['question']."</span></p>\n";
      $str = $str.'<p>'.$question['response']."</p>\n";
    }

    return $str;
  } // htmlFromQuestions()


  private function paramsFromEntry($contact_params)
  {
    static $config;

    if (!$config) {
      $config = $this->get_config();
    }

    $contact_params['contact_type'] = "Individual";

    $address = array(
      'street_address'   => $this->value($contact_params, 'address'),
      'street_address2'  => $this->value($contact_params, 'address2'),
      'city'          => $this->value($contact_params, 'city'),
      'state_province'   => $this->value($contact_params, 'state'),
      'postal_code'      => $this->value($contact_params, 'zip5')
    );

    unset($contact_params['address']);
    unset($contact_params['address2']);
    unset($contact_params['city']);
    unset($contact_params['state']);
    unset($contact_params['zip5']);

    //geocode, dist assign and format address
    require_once 'CRM/Utils/SAGE.php';
    CRM_Utils_SAGE::lookup(&$address);

    $address['location_type_id'] = '1';
    $address['is_primary'] = true;

    $contact_params['address'] = $address;

    if ($this->valid($contact_params, 'full_address')) {
      //handle this exception
    }
    unset($contact_params['full_address']);

    if ($this->valid($contact_params, 'phone')) {
      $contact_params['phone'] = array(
          'phone' => $contact_params['phone'],
          'location_type_id' => '1',
          'phone_type_id' => '1',
          'is_primary' => true
        );
    }

    if ($this->valid($contact_params, 'cell')) {
      $contact_params['phone'] = array(
            'phone' => $contact_params['cell'],
            'location_type_id' => '1',
            'phone_type_id' => '1',
            'is_primary' => true
        );
    }
    unset($contact_params['cell']);

    if ($this->valid($contact_params, 'email')) {
      $email = $contact_params['email'];
      $contact_params['email'] = array();
      $contact_params['email'][] = array(
          'email' => $email,
          'email_type_id' => '1',
          'is_primary' => true
        );
    }

    $contact_params['source'] = "Website - Initiative";
    return $contact_params;
  } // paramsFromEntry()


  private function valid($params, $value) {
    return array_key_exists($value, $params) && $params[$value];
  } // valid()


  private function value($params, $value) {
    if ($this->valid($params, $value)) {
      return $params[$value];
    }
    return NULL;
  } // value()


  /*
   * a form is only readable if it has values
   * necessary for level 3 and/or level4 dedupe
   */
  function canDedupe($value) {
    if ($value['first_name'] && $value['last_name']) {
      if ($value['email']
          || ($value['address'] && $value['city'] && $value['zip5'])) {
        return true;
      }
    }
    return false;
  } // canDedupe()


  function getFormMapper($nid, $form = NULL)
  {
    $form_mapper = NULL;

    if (array_key_exists($nid, self::$form_cache)) {
      $form_mapper = self::$form_cache[$nid];
    }
    else {
      if ($form) {
        $form_mapper = $this->formMapperFromFields(
                $form['fields'],
                self::$contact_regexes,
                self::$accepted_form_types);
        $form_mapper['title'] = $form['webform_title'];
        $form_mapper['senator_short_name'] = $form['senator_short_name'];
        $form_mapper['senator_district'] = $form['senator_district'];
        self::$form_cache[$nid] = $form_mapper;
      }
    }

    return $form_mapper;
  } // getFormMapper()


  /*
   * create a contact mapper that attempts to map form field
   * values to civi contact values, then create a list of form
   * questions to map answers to
   */
  function formMapperFromFields($fields, $contact_regexes, $accepted_form_types)
  {
    $contact_mapper = array();
    $questions = array();

    foreach ($fields as $field) {
      $found = false;

      foreach ($contact_regexes as $type => $regexp) {
        //found key we're looking for
        if (preg_match('/^'.$regexp.'$/i', $field['form_key'])) {
          $found = true;
          $contact_mapper[$type] = $field['form_key'];
          break;
        }
      }

      //if field doesn't map to a value for the contact
      //then we should treat it as a non specific form field
      if ($found == false) {
        if (in_array($field['type'], $accepted_form_types)) {
          $question = $this->questionFromField($field);
          $questions[$question['form_key']] = $question;
        }
      }
    }

    return array(
      'contact_mapper'   => $contact_mapper,
      'questions'      => $questions
    );
  } // formMapperFromFields()


  /*
   * generate reasonably formed question
   * from services data
   */
  function questionFromField($field)
  {
    $question = array();
    $question['type'] = $field['type'];
    $question['question'] = preg_replace('/(<.+?>)/', '', $field['name']);
    $question['form_key'] = $field['form_key'];

    switch ($question['type']) {
      case 'textfield':
      case 'textarea' :
        break;
      case 'select'  :
        $question['items'] = $this->getOptions($field['extra']['items']);
        break;
      case 'grid'    :
        $question['questions'] = $this->getOptions($field['extra']['questions']);
        $question['options'] = $this->getOptions($field['extra']['options']);
        break;
    }

    return $question;
  } // questionFromField()


  /*
   * grid and select fields come with their options
   * delimited by new lines and their option # and option
   * delimited by pipes, (ex. "1|opt1\n2|opt2") this
   * breaks them up and returns an array
   */
  function getOptions($options_string) {
    $options_string = preg_replace('/(<.+?>|\n$)/', '', $options_string);

    $options_raw = split("\n", $options_string);

    $options = array();

    //$option[0] = option #
    //$option[1] = option text
    foreach ($options_raw as $option) {
      $option = split('\|', $option);
      $options[$option[0]] = $option[1];
    }

    return $options;
  } // getOptions()
}
