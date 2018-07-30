<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */
namespace Civi\FlexMailer\Listener;

use Civi\FlexMailer\Event\RunEvent;

/**
 * Class Abdicator
 * @package Civi\FlexMailer\Listener
 *
 * FlexMailer is in incubation -- it's a heavily reorganized version
 * of the old MailingJob::deliver*() functions. It hasn't been tested as
 * thoroughly and may not have perfect parity.
 *
 * During incubation, we want to mostly step-aside -- for traditional
 * mailings, simply continue using the old system.
 */
class Abdicator {

  /**
   * @param \CRM_Mailing_BAO_Mailing $mailing
   * @return bool
   */
  public static function isFlexmailPreferred($mailing) {
    // Hidden setting: "experimentalFlexMailerEngine" (bool)
    // If TRUE, we will always use FlexMailer's events.
    // Otherwise, we'll generally abdicate.
    if (\CRM_Core_BAO_Setting::getItem('Mailing Preferences', 'experimentalFlexMailerEngine')) {
      return TRUE;
    }

    // Use FlexMailer for new-style email blasts (with custom `template_type`).
    if ($mailing->template_type && $mailing->template_type !== 'traditional' && !$mailing->sms_provider_id) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Abdicate; defer to the old system during delivery.
   *
   * @param \Civi\FlexMailer\Event\RunEvent $e
   */
  public function onRun(RunEvent $e) {
    if (self::isFlexmailPreferred($e->getMailing())) {
      return; // OK, we'll continue running.
    }

    // Nope, we'll abdicate.
    $e->stopPropagation();
    $isDelivered = $e->getJob()->deliver(
      $e->context['deprecatedMessageMailer'],
      $e->context['deprecatedTestParams']
    );
    $e->setCompleted($isDelivered);
  }

  /**
   * Abdicate; defer to the old system when checking completeness.
   *
   * @param \Civi\FlexMailer\Event\CheckSendableEvent $e
   */
  public function onCheckSendable($e) {
    if (self::isFlexmailPreferred($e->getMailing())) {
      return; // OK, we'll continue running.
    }

    $e->stopPropagation();
    $errors = \CRM_Mailing_BAO_Mailing::checkSendable($e->getMailing());
    if (is_array($errors)) {
      foreach ($errors as $key => $message) {
        $e->setError($key, $message);;
      }
    }
  }

}
