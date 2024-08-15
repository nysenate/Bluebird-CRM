<?php

use CRM_NYSS_BAO_Integration_WebsiteEvent as WebsiteEvent;
class CRM_NYSS_BAO_Integration_WebsiteEventFactory
{
    public static function create(CRM_NYSS_BAO_Integration_WebsiteEventData $data) {
        switch ($data->getEventType()) {
            case WebsiteEvent::EVENT_TYPE_BILL:
                return new CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent($data);
            default:
                throw new Exception("Event type not recognized.");
        }
    }
}