<?php
// $Id$

/**
 * @file
 * Common classes for handling NY Senate email list signups.
 *
 */

/**
 * For handling Signup data
 *
 */
class SignupData
{
  // property declarations
  public $accounts;
  public $timestamp;
  public $startingId;
  public $endingId;

  // method declarations/overrides
  public function __construct()
  {
    $this->accounts = array();
    $this->timestamp = time();
  }
}

/**
 * For handling an individual account, e.g., a senator or a committee
 *
 */
class Account
{
  // property declarations
  public $id;
  public $name;
  public $lists;
  public $contacts;

  // method declarations/overrides
  public function __construct($name='')
  {
    $this->name = $name;
    $this->lists = array();
    $this->contacts = array();
  }
}

/**
 * For handling a single list in a senator or committee's account
 *
 */
class MailingList
{
  // property declarations
  public $id;
  public $name;
  public $memberCount;

  // method declarations/overrides
  public function __construct($name='')
  {
    $this->name = $name;
  }
}

/**
 * For handling an individual contact
 *
 */
class Contact
{
  // property declarations
  public $id;
  public $firstName;
  public $lastName;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $zip;
  public $phoneMobile;
  public $issues;
  public $email;
  public $status;   /* status can be: "active", "approved", "archived", "bounced", "onboarding", "sent", "skipped", "unsubscribed" */
  public $source;   /* source can be: "api", "import", "manual", "website" */
  public $created;
  public $modified;
  public $lists;

  // method declarations/overrides
  public function __construct($id, $firstName, $lastName, $address1, $address2, $city, $state, $zip, $phoneMobile, $issues, $email)
  {
    $this->id = $id;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->address1 = $address1;
    $this->address2 = $address2;
    $this->city = $city;
    $this->state = $state;
    $this->zip = $zip;
    $this->phoneMobile = $phoneMobile;
    $this->issues = $issues;
    $this->email = $email;
  }
}
