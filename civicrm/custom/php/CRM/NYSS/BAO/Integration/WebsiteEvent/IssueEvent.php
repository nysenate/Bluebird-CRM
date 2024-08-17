<?php

use Civi\API\Exception\UnauthorizedException;
use CRM_Core_Exception;
use NYSS_Integration\WebsiteEvent;
use NYSS_Integration\WebsiteEventInterface;
use InvalidArgumentException;

class CRM_NYSS_BAO_Integration_WebsiteEvent_IssueEvent extends CRM_NYSS_BAO_Integration_WebsiteEvent implements CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  use CRM_NYSS_BAO_Integration_WebsiteEvent_FollowableEvent;

  const PARENT_TAG_NAME = 'Website Issues';

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {
    parent::__construct($event_data);

    if (empty($this->getIssueName())) {
      throw new InvalidArgumentException("issue_name must be in event info.");
    }

    // Archive Table Name
    $this->setArchiveTableName('archive_committee');
    $this->archiveFields = [];

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
    $this->setTagName($this->getIssueName());
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

  public function getIssueName(): ?string {
    $event_info = $this->getEventInfo();
    return $event_info['issue_name'] ?? NULL;
  }

  public function getParentTagName(): string {
    return self::PARENT_TAG_NAME;
  }

  public function getEventDetails(): string {
    // TODO: Implement getEventDetails() method.
  }

  public function getEventDescription(): string {
    // TODO: Implement getEventDescription() method.
  }

  public function getActivityData(): ?string {
    // TODO: Implement getActivityData() method.
  }

  public function getArchiveValues(): ?array {
    // TODO: Implement getArchiveValues() method.
  }

  public function getArchiveSQL(int $archive_id, ?string $prefix = NULL): string {
    // TODO: Implement getArchiveSQL() method.
  }

}