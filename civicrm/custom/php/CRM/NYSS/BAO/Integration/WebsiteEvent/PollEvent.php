<?php

use Civi\API\Exception\UnauthorizedException;
use CRM_Core_Exception;
use CRM_NYSS_BAO_Integration_WebsiteEvent_FollowableEvent;
use CRM_NYSS_BAO_Integration_WebsiteEventData;
use InvalidArgumentException;
use CRM_NYSS_BAO_Integration_WebsiteEvent;
use CRM_NYSS_BAO_Integration_WebsiteEventInteface;

abstract class CRM_NYSS_BAO_Integration_WebsiteEvent_PollEvent extends CRM_NYSS_BAO_Integration_WebsiteEvent implements \CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  const ACTIVITY_TYPE = 'Poll';

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {
    return parent::__construct($event_data);
  }

}