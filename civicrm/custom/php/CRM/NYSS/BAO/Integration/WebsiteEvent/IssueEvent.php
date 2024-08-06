<?php

namespace CRM_NYSS_BAO_Integration_WebsiteEvent;

use CRM_Core_Exception;
use CRM_NYSS_BAO_Integration\WebsiteEventInterface;
use InvalidArgumentException;

class IssueEvent extends BaseEvent implements WebsiteEventInterface
{
    use FollowableEvent;


    const PARENT_TAG_NAME = 'Website Issues';

    public function __construct(int $contact_id, string $action, array $event_info)
    {
        parent::__construct($contact_id,$action, $event_info);

        if (empty($this->getIssueName())) {
            throw new InvalidArgumentException("issue_name must be in event info.");
        }

        // Get Tag
        $this->setTagName($this->getIssueName());
        $this->setTag($this->findTag($this->getTagName(),$this->getParentTagId(), false));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function process(): static
    {
        // Process Specific Action
        switch ($this->getAction()) {
            case self::ACTION_FOLLOW:
                if (! $this->isFollowing()) {
                    $this->follow();
                }
                break;
            case self::ACTION_UNFOLLOW:
                if ($this->isFollowing()) {
                    $this->unfollow();
                }
                break;
            default:
                throw new CRM_Core_Exception("Unable to determine bill action");
        }

        return $this;
    }

    public function getIssueName(): ?string {
        $event_info = $this->getEventInfo();
        return $event_info['issue_name'] ?? null;
    }

    public function getParentTagName(): string
    {
        return self::PARENT_TAG_NAME;
    }

}