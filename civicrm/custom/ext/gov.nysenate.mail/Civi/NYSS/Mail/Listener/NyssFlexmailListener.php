<?php
namespace Civi\NYSS\Mail\Listener;

use Civi\Core\Service\AutoService;
use Civi\FlexMailer\Event\ComposeBatchEvent;
use Civi\FlexMailer\Listener\IsActiveTrait;

/**
 * @service nyss_flexmail_listener
 */
class NyssFlexmailListener extends AutoService {
  use IsActiveTrait;

  public static string $SERVICE_NAME = 'nyss_flexmail_listener';
  public static string $PARAM_JOB_INFO = 'job_info';
  public static string $PARAM_CONTACT_ID = 'contact_id';
  public static string $PARAM_EVENT_Q_ID = 'event_queue_id';

  /**
   * Inject basic headers
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   */
  public function onComposePrepare(ComposeBatchEvent $e) {
    if (!$this->isActive()) {
      return;
    }
    foreach ($e->getTasks() as $task) {
      $mailParams = [];
      $mailParams[self::$PARAM_JOB_INFO] = [
        'is_test' => $e->getJob()->is_test,
        'mailing_id' => $e->getMailing()->id,
        'mailing_name' => $e->getMailing()->name,
        'mailing_hash' => $e->getMailing()->hash,
        'job_id' => $e->getJob()->id,
      ];
      $mailParams[self::$PARAM_CONTACT_ID] = $task->getContactId();
      $mailParams[self::$PARAM_EVENT_Q_ID] = $task->getEventQueueId();

      $task->setMailParams(array_merge(
        $mailParams,
        $task->getMailParams()
      ));
    }
  }

  /**
   * Inject basic headers
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   */
  public function onComposeEnd(ComposeBatchEvent $e) {
    if (!$this->isActive()) {
      return;
    }
    foreach ($e->getTasks() as $task) {
      // clean up to avoid this info from ending up in the email header
      $mailParams = $task->getMailParams();
      unset($mailParams[self::$PARAM_JOB_INFO]);
      unset($mailParams[self::$PARAM_CONTACT_ID]);
      unset($mailParams[self::$PARAM_EVENT_Q_ID]);
      $task->setMailParams($mailParams);
    }
  }
}