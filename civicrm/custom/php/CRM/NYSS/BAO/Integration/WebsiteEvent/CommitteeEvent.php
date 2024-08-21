<?php

use Civi\API\Exception\UnauthorizedException;
use CRM_Core_Exception;
use CRM_NYSS_BAO_Integration_WebsiteEvent;
use CRM_NYSS_BAO_Integration_WebsiteEventInteface;
use InvalidArgumentException;

class CRM_NYSS_BAO_Integration_WebsiteEvent_CommitteeEvent extends CRM_NYSS_BAO_Integration_WebsiteEvent implements CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  use CRM_NYSS_BAO_Integration_WebsiteEvent_FollowableEvent;

  const ACTIVITY_TYPE = 'Committee';

  const PARENT_TAG_NAME = 'Website Committees';

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {
    parent::__construct($event_data);

    if (empty($this->getCommitteeName())) {
      throw new InvalidArgumentException("committee_name must be in event info.");
    }

    // Archive Table Name
    $this->setArchiveTableName('archive_committee');
    $this->archiveFields = ['committee_name'];

    return $this;
  }

  /**
   * @throws UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function process(int $contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to process event.");
    }

    parent::process($contact_id);

    // Get Tag
    $this->setTagName($this->getCommitteeName());
    $this->setTag($this->findTag($this->getTagName(), $this->getParentTagId(), FALSE));

    // Process Specific Action
    switch ($this->getEventAction()) {
      case self::ACTION_FOLLOW:
        if (!$this->isFollowing($contact_id)) {
          $this->follow($contact_id);
        }
        break;
      case self::ACTION_UNFOLLOW:
        if ($this->isFollowing($contact_id)) {
          $this->unfollow($contact_id);
        }
        break;
      default:
        throw new CRM_Core_Exception("Unable to determine committee action");
    }

    return $this;
  }

  public function getCommitteeName(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info->committee_name ?? NULL;
  }

  function getParentTagName(): string {
    return self::PARENT_TAG_NAME;
  }

  public function getEventDetails(): string {
    return $this->getEventAction() . ' :: ' . $this->getCommitteeName();
  }

  public function getEventDescription(): string {
    return self::ACTIVITY_TYPE;
  }

  public function getArchiveValues(): ?array {
    return [
      $this->getCommitteeName(),
    ];
  }

}