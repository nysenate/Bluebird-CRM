<?php

use CRM_NYSS_BAO_Integration_WebsiteEvent as WebsiteEvent;

class CRM_NYSS_BAO_Integration_WebsiteEventFactory {

  final public const EVENT_TYPE_BILL = 'bill';

  final public const EVENT_TYPE_ISSUE = 'issue';

  final public const EVENT_TYPE_COMMITTEE = 'committee';

  final public const EVENT_TYPE_PETITION = 'petition';
  final public const EVENT_TYPE_POLL = 'poll';

  final public const EVENT_TYPE_PROFILE = 'profile';

  final public const EVENT_TYPE_ACCOUNT = 'account';

  final public const EVENT_TYPE_SURVEY = 'survey';
  final public const EVENT_ACTION_FOLLOW = 'follow';
  final public const EVENT_ACTION_UNFOLLOW = 'unfollow';
  final public const EVENT_ACTION_WEBFORM = 'webform';

  public static function getClassName(CRM_NYSS_BAO_Integration_WebsiteEventData $data): string {
    $type = $data->getEventType();
    if ($data->getEventType() === self::EVENT_TYPE_POLL) {
      if ($data->getEventAction() == self::EVENT_ACTION_FOLLOW || $data->getEventAction() == self::EVENT_ACTION_UNFOLLOW) {
        // It's a petition
        $type = self::EVENT_TYPE_PETITION;
      } elseif ($data->getEventAction() == self::EVENT_ACTION_WEBFORM) {
        $type = self::EVENT_TYPE_SURVEY;
      }

    }

    return match ($type) {
      self::EVENT_TYPE_BILL => 'CRM_NYSS_BAO_Integration_WebsiteEvent_BillEvent',
      self::EVENT_TYPE_COMMITTEE => 'CRM_NYSS_BAO_Integration_WebsiteEvent_CommitteeEvent',
      self::EVENT_TYPE_ISSUE => 'CRM_NYSS_BAO_Integration_WebsiteEvent_IssueEvent',
      self::EVENT_TYPE_PETITION => 'CRM_NYSS_BAO_Integration_WebsiteEvent_PetitionEvent',
      self::EVENT_TYPE_SURVEY => 'CRM_NYSS_BAO_Integration_WebsiteEvent_SurveyEvent',
      self::EVENT_TYPE_ACCOUNT => 'CRM_NYSS_BAO_Integration_WebsiteEvent_AccountEvent',
      default => '',
    };
  }

  /**
   * @throws \Exception
   */
  public static function create(CRM_NYSS_BAO_Integration_WebsiteEventData $data) {
    $class = self::getClassName($data);
    if (!empty($class)) {
      return new $class($data);
    }
    else {
      throw new Exception('Event type not recognized');
    }
  }

  public static function canCreate(CRM_NYSS_BAO_Integration_WebsiteEventData $data): bool {
    return (!empty(self::getClassName($data)));
  }

}