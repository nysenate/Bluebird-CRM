<?php

namespace CRM_NYSS_BAO_Integration_WebsiteEvent;

use Civi\API\Exception\UnauthorizedException;

trait FollowableEvent
{
    const ACTION_FOLLOW = 'follow';
    const ACTION_UNFOLLOW = 'unfollow';

    protected function isFollowing(): bool {
        if (empty($this->base_tag)) {
            throw new \CRM_Core_Exception('No base tag set.');
        }
        return $this->hasEntityTag($this->getContactId(), $this->base_tag['id']);
    }

    /**
     * @throws UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    protected function follow(): static {
        // if we don't already have the base tag info, then we likely need to create it.
        // getTag() will do the creation with the 3rd parameter set to true.
        if (empty($this->base_tag)) {
            $this->findTag($this->getTagName(),$this->getParentTagId(), true);
        }
        // associate the tag to the entity (the contact) through an Entity Tag
        $this->createEntityTag($this->getContactId(),$this->base_tag['id']);
        return $this;
    }

    /**
     * @throws UnauthorizedException
     * @throws \CRM_Core_Exception
     */
    protected function unfollow(): static {
        if (empty($this->base_tag)) {
            // if we don't already have the base tag info, then we likely need to create it.
            // getTag() will do the creation with the 3rd parameter set to true.
            $this->findTag($this->getTagName(),$this->getParentTagId(), true);
        }
        // disassociate the tag from the entity (the contact) by removing the Entity Tag
        $this->deleteEntityTag($this->getContactId(),$this->base_tag['id']);
        return $this;
    }
}