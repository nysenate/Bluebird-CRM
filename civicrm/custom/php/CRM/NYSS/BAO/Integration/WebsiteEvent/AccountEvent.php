<?php

use CRM_Core_Exception;
use CRM_NYSS_BAO_Integration_Website;
use CRM_NYSS_BAO_Integration_WebsiteEventData;
use Exception;
use InvalidArgumentException;
use stdClass;

class CRM_NYSS_BAO_Integration_WebsiteEvent_AccountEvent extends \CRM_NYSS_BAO_Integration_WebsiteEvent implements \CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  const ACTIVITY_TYPE = 'Account';

  const ACTION_CREATE = 'created';

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {
    parent::__construct($event_data);
    return $this;
  }

  public function process(int $contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to process event.");
    }

    parent::process($contact_id);

    // Process Specific Action
    switch ($this->getEventAction()) {
      case self::ACTION_CREATE:
        $this->processAccount($contact_id);
        $this->processProfile($contact_id);
        break;
      default:
        throw new CRM_Core_Exception("Unable to determine account action");
    }

    return $this;
  }

  protected function processAccount(int $contact_id): void {
    // Not much is done with $params. So, using empty stdClass.
    $result = CRM_NYSS_BAO_Integration_Website::processAccount(
      $contact_id,
      $this->getEventAction(),
      new stdClass(),
      $this->event_data->getCreatedAt()
    );
    if ($result['is_error'] == 1) {
      throw new Exception("Error processing account: " . $result['error_message']);
    }
  }

  protected function processProfile(int $contact_id): void {
    //reconstruct $row data for backwards compatibility
    $row = new stdClass();
    $row->first_name = $this->event_data->getFirstName();
    $row->last_name = $this->event_data->getLastName();
    $row->email_address = $this->event_data->getEmail();
    $row->address1 = $this->event_data->getStreetAddress();
    $row->address2 = ''; // ??? check this on account edited
    $row->city = $this->event_data->getCity();
    $row->state = $this->event_data->getState();
    $row->zip = $this->event_data->getZipCode();
    $row->dob = (! is_null($this->event_data->getDob())) ? $this->event_data->getDob()->getTimestamp() : null;
    $row->gender = $this->event_data->getGender();
    $row->top_issue = ''; // check on edit account
    $row->user_is_verified = $this->event_data->getUserIsVerified();
    $row->created_at = $this->event_data->getCreatedAtAsDateTime()->getTimestamp();

    $params = new stdClass();
    $params->status = ''; // not sure how much this matters anymore. There is no "status" in the data.

    // Not much is done with $params. So, using empty stdClass.
    $result = CRM_NYSS_BAO_Integration_Website::processProfile(
      $contact_id,
      'account edited', // force it to work
      $params,
      $row
    );
    if ($result['is_error'] == 1) {
      throw new Exception("Error processing account: " . $result['error_message']);
    }
  }

  // No Parent Tag Involved with Webform actions
  public function findParentTagId(): int {
    return 0;
  }

  function getParentTagName(): string {
    return ''; // No tagging done with Account actions
  }

  public function getEventDetails(): string {
    return $this->getEventAction();
  }

  public function getEventDescription(): string {
    return self::ACTIVITY_TYPE;
  }

  public function getArchiveValues(): ?array {
    return [];
  }

  public function getArchiveSQL(int $archive_id, ?string $prefix = NULL): string {
    return '';
  }

}