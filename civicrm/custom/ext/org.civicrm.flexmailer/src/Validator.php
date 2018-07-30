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
namespace Civi\FlexMailer;

use Civi\FlexMailer\Event\CheckSendableEvent;
use \Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Validator
 * @package Civi\FlexMailer
 *
 * The *validator* determines whether a mailing is completely specified
 * (sendable). If not delivery.
 */
class Validator {

  const EVENT_CHECK_SENDABLE = 'civi.flexmailer.checkSendable';

  /**
   * @param \CRM_Mailing_DAO_Mailing $mailing
   *   The mailing which may or may not be sendable.
   * @return array
   *   List of error messages.
   */
  public static function createAndRun($mailing) {
    $validator = new \Civi\FlexMailer\Validator();
    return $validator->run(array(
      'mailing' => $mailing,
      'attachments' => \CRM_Core_BAO_File::getEntityFile('civicrm_mailing', $mailing->id),
    ));
  }

  /**
   * @var EventDispatcherInterface
   */
  private $dispatcher;

  /**
   * FlexMailer constructor.
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(EventDispatcherInterface $dispatcher = NULL) {
    $this->dispatcher = $dispatcher ? $dispatcher : \Civi::service('dispatcher');
  }

  /**
   * @param array $context
   *   An array which must define options:
   *     - mailing: \CRM_Mailing_BAO_Mailing
   *     - attachments: array
   * @return array
   *   List of error messages.
   *   Ex: array('subject' => 'The Subject field is blank').
   *   Example keys: 'subject', 'name', 'from_name', 'from_email', 'body', 'body_html:unsubscribeUrl'.
   */
  public function run($context) {
    $checkSendable = new CheckSendableEvent($context);
    $this->dispatcher->dispatch(static::EVENT_CHECK_SENDABLE, $checkSendable);
    return $checkSendable->getErrors();
  }

}
