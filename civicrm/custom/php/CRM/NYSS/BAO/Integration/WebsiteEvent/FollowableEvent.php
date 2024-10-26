<?php

use Civi\API\Exception\UnauthorizedException;

trait CRM_NYSS_BAO_Integration_WebsiteEvent_FollowableEvent {

  const ACTION_FOLLOW = 'follow';

  const ACTION_UNFOLLOW = 'unfollow';

  protected function isFollowing($contact_id): bool {
    if (empty($this->getTag())) {
      return FALSE; // If the tag doesn't exist, it's not being followed
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
      $tag = $this->findTag($this->getTagName(), $this->getParentTagId(), TRUE);
      $this->setTag($tag);
    }

    // associate the tag to the entity (the contact) through an Entity Tag
    if (! empty($this->getTag())) {
      $this->createEntityTag($contact_id, $this->getTag()['id']);
    } else {
      throw new CRM_NYSS_BAO_Integration_TagNotFoundException($this->getTagName(), $this->getParentTagId());
    }

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
      $tag = $this->findTag($this->getTagName(), $this->getParentTagId(), TRUE);
      $this->setTag($tag);
    }
    // disassociate the tag from the entity (the contact) by removing the Entity Tag
    // associate the tag to the entity (the contact) through an Entity Tag
    if (! empty($this->getTag())) {
      $this->deleteEntityTag($contact_id, $this->getTag()['id']);
    } else {
      throw new CRM_NYSS_BAO_Integration_TagNotFoundException($this->getTagName(), $this->getParentTagId());
    }

    return $this;
  }

}