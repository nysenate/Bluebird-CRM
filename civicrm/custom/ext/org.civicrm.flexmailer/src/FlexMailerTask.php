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

/**
 * Class FlexMailerTask
 * @package Civi\FlexMailer
 *
 * A FlexMailerTask describes an individual message that needs to be
 * composed and delivered. Generally, it's used in three steps:
 *  - At the start, we instantiate the task with a few inputs
 *    (e.g. $contactId and $address).
 *  - During composition, we read those values and fill-in the
 *    message's content ($task->setMailParams(...));
 *  - During delivery, we read the message ($task->getMailParams())
 *    and send it.
 */
class FlexMailerTask {

  /**
   * @var int
   */
  private $eventQueueId;

  /**
   * @var int
   */
  private $contactId;

  /**
   * @var string
   */
  private $hash;

  /**
   * @var string
   *
   * WAS: email
   */
  private $address;

  /**
   * The individual email message to send (per alterMailParams).
   *
   * @var array
   * @see MailParams
   */
  private $mailParams = array();

  /**
   * FlexMailerTask constructor.
   *
   * @param int $eventQueueId
   * @param int $contactId
   * @param string $hash
   * @param string $address
   */
  public function __construct(
    $eventQueueId,
    $contactId,
    $hash,
    $address
  ) {
    $this->eventQueueId = $eventQueueId;
    $this->contactId = $contactId;
    $this->hash = $hash;
    $this->address = $address;
  }

  /**
   * @return int
   */
  public function getEventQueueId() {
    return $this->eventQueueId;
  }

  /**
   * @return int
   */
  public function getContactId() {
    return $this->contactId;
  }

  /**
   * @return string
   */
  public function getHash() {
    return $this->hash;
  }

  /**
   * @return string
   */
  public function getAddress() {
    return $this->address;
  }

  /**
   * @return bool
   */
  public function hasContent() {
    return !empty($this->mailParams['html']) || !empty($this->mailParams['text']);
  }

  /**
   * @return array
   * @see CRM_Utils_Hook::alterMailParams
   */
  public function getMailParams() {
    return $this->mailParams;
  }

  /**
   * @param \array $mailParams
   * @return FlexMailerTask
   * @see CRM_Utils_Hook::alterMailParams
   */
  public function setMailParams($mailParams) {
    $this->mailParams = $mailParams;
    return $this;
  }

  /**
   * @param string $key
   * @param string $value
   * @return $this
   * @see CRM_Utils_Hook::alterMailParams
   */
  public function setMailParam($key, $value) {
    $this->mailParams[$key] = $value;
    return $this;
  }

  /**
   * @param string $key
   * @return string
   * @see CRM_Utils_Hook::alterMailParams
   */
  public function getMailParam($key) {
    return isset($this->mailParams[$key]) ? $this->mailParams[$key] : NULL;
  }

}
