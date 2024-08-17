<?php

use Civi\API\Exception\UnauthorizedException;

trait CRM_NYSS_BAO_Integration_WebsiteEvent_FollowableEvent {

  const ACTION_FOLLOW = 'follow';

  const ACTION_UNFOLLOW = 'unfollow';

  protected function isFollowing($contact_id): bool {
    if (empty($this->getTag())) {
      throw new \CRM_Core_Exception('No base tag set.');
    }
    return $this->hasEntityTag($contact_id, $this->getTag()['id']);
  }

  /**
   * @throws UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  protected function follow($contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to follow.");
    }

    // if we don't already have the base tag info, then we likely need to create it.
    // findTag() will do the creation with the 3rd parameter set to true.
    if (empty($this->getTag())) {
      $this->findTag($this->getTagName(), $this->getParentTagId(), TRUE);
    }
    // associate the tag to the entity (the contact) through an Entity Tag
    $this->createEntityTag($contact_id, $this->getTag()['id']);
    return $this;
  }

  /**
   * @throws UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  protected function unfollow($contact_id): static {
    if (empty($contact_id)) {
      throw new InvalidArgumentException("Contact ID required to unfollow.");
    }

    if (empty($this->getTag())) {
      // if we don't already have the base tag info, then we likely need to create it.
      // findTag() will do the creation with the 3rd parameter set to true.
      $this->findTag($this->getTagName(), $this->getParentTagId(), TRUE);
    }
    // disassociate the tag from the entity (the contact) by removing the Entity Tag
    $this->deleteEntityTag($contact_id, $this->getTag()['id']);
    return $this;
  }

}