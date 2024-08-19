<?php

use CRM_NYSS_BAO_Integration_WebsiteEvent as WebsiteEvent;

class CRM_NYSS_BAO_Integration_WebsiteEventFactory {

  final public const EVENT_TYPE_BILL = 'bill';
  final public const EVENT_TYPE_ISSUE = 'issue';
  final public const EVENT_TYPE_COMMITTEE = 'committee';
  final public const EVENT_TYPE_PETITION = 'petition';
  final public const EVENT_TYPE_PROFILE = 'profile';
  final public const EVENT_TYPE_ACCOUNT = 'account';
  final public const EVENT_TYPE_SURVEY = 'survey';

  public static function getClassName(string $type) : string {
    return match ($type) {
      self::EVENT_TYPE_BILL => 'CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent',
      self::EVENT_TYPE_COMMITTEE => 'CRM_NYSS_BAO_Integration_WebsiteEvent_CommitteeEvent',
      self::EVENT_TYPE_ISSUE => 'CRM_NYSS_BAO_Integration_WebsiteEvent_IssueEvent',
      self::EVENT_TYPE_PETITION => 'CRM_NYSS_BAO_Integration_WebsiteEvent_PetitionEvent',
      default => '',
    };
  }

  /**
   * @throws \Exception
   */
  public static function create(CRM_NYSS_BAO_Integration_WebsiteEventData $data) {

    $class = self::getClassName($data->getEventType());
    if (!empty($class)) {
      return new $class($data);
    }
    else {
      throw new Exception('Event type not recognized');
    }

  }

  public static function canCreate(string $type) : bool {
    return (!empty(self::getClassName($type)));
  }

}