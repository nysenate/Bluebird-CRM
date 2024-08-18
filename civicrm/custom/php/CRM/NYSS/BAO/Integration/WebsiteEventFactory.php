<?php

use CRM_NYSS_BAO_Integration_WebsiteEvent as WebsiteEvent;

class CRM_NYSS_BAO_Integration_WebsiteEventFactory {

  /**
   * @throws \Exception
   */
  public static function create(CRM_NYSS_BAO_Integration_WebsiteEventData $data) {
    /*
    switch ($data->getEventType()) {
      case WebsiteEvent::EVENT_TYPE_BILL:
        return new CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent($data);
      case WebsiteEvent::EVENT_TYPE_COMMITTEE:
        return new CRM_NYSS_BAO_Integration_WebsiteEvent_CommitteeEvent($data);
      default:
        throw new Exception("Event type not recognized.");
    }
    */
    $class = match ($data->getEventType()) {
      WebsiteEvent::EVENT_TYPE_BILL => 'CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent',
      WebsiteEvent::EVENT_TYPE_COMMITTEE => 'CRM_NYSS_BAO_Integration_WebsiteEvent_CommitteeEvent',
      default => '',
    };

    if ($class) {
      return new $class($data);
    }
    else {
      throw new Exception('Event type not recognized');
    }

  }

}