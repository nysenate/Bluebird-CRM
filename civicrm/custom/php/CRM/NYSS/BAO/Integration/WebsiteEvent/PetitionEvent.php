<?php

use CRM_NYSS_BAO_Integration_WebsiteEvent_PollEvent;

class CRM_NYSS_BAO_Integration_WebsiteEvent_PetitionEvent extends CRM_NYSS_BAO_Integration_WebsiteEvent_PollEvent implements CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  use CRM_NYSS_BAO_Integration_WebsiteEvent_FollowableEvent;

  const PARENT_TAG_NAME = 'Website Petitions';

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {

    parent::__construct($event_data);

    if (empty($this->getPetitionName())) {
      throw new InvalidArgumentException("name must be in event info.");
    }

    // Archive Table Name
    $this->setArchiveTableName('archive_poll');
    $this->archiveFields = ['petition_name'];

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function process(int $contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to process event.");
    }

    parent::process($contact_id);

    // Get Tag
    $this->setTagName($this->getPetitionName());
    $this->setTag($this->findTag($this->getTagName(), $this->getParentTagId(), true));

    // Process Specific Action
    switch ($this->getEventAction()) {
      case self::ACTION_FOLLOW: // Petition Sign
        if (!$this->isFollowing($contact_id)) {
          $this->follow($contact_id);
        }
        break;
      case self::ACTION_UNFOLLOW: // Petition Unsign
        if ($this->isFollowing($contact_id)) {
          $this->unfollow($contact_id);
        }
        break;
      default:
        throw new CRM_Core_Exception("Unable to determine petition action");
    }

    return $this;
  }

  public function getPetitionName(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info->name ?? NULL;
  }
  function getParentTagName(): string {
    return self::PARENT_TAG_NAME;
  }

  public function getEventDetails(): string {
    return $this->getEventAction() . ' :: ' . $this->getPetitionName();
  }

  public function getEventDescription(): string {
    return self::ACTIVITY_TYPE;
  }

  public function getArchiveValues(): ?array {
    return [
      $this->getPetitionName(),
    ];
  }

}