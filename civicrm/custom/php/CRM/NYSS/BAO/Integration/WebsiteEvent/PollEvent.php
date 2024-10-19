<?php

use Civi\API\Exception\UnauthorizedException;

abstract class CRM_NYSS_BAO_Integration_WebsiteEvent_PollEvent extends CRM_NYSS_BAO_Integration_WebsiteEvent implements \CRM_NYSS_BAO_Integration_WebsiteEventInterface {

  const ACTIVITY_TYPE = 'Poll';

  public function __construct(CRM_NYSS_BAO_Integration_WebsiteEventData $event_data) {
    return parent::__construct($event_data);
  }

}