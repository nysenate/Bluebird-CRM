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
namespace Civi\FlexMailer\Event;

/**
 * Class CheckSendableEvent
 * @package Civi\FlexMailer\Event
 */
class CheckSendableEvent extends \Symfony\Component\EventDispatcher\Event {

  /**
   * @var array
   *   An array which must define options:
   *     - mailing: \CRM_Mailing_BAO_Mailing
   *     - attachments: array
   */
  public $context;

  /**
   * @var array
   *   A list of error messages.
   *   Ex: array('subject' => 'The Subject field is blank').
   *   Example keys: 'subject', 'name', 'from_name', 'from_email', 'body', 'body_html:unsubscribeUrl'.
   */
  protected $errors = array();

  /**
   * CheckSendableEvent constructor.
   * @param array $context
   */
  public function __construct(array $context) {
    $this->context = $context;
  }

  /**
   * @return \CRM_Mailing_BAO_Mailing
   */
  public function getMailing() {
    return $this->context['mailing'];
  }

  /**
   * @return array|NULL
   */
  public function getAttachments() {
    return $this->context['attachments'];
  }

  public function setError($key, $message) {
    $this->errors[$key] = $message;
    return $this;
  }

  public function getErrors() {
    return $this->errors;
  }

  /**
   * Get the full, combined content of the header, body, and footer.
   *
   * @param string $field
   *   Name of the field -- either 'body_text' or 'body_html'.
   * @return string|NULL
   *   Either the combined header+body+footer, or NULL if there is no body.
   */
  public function getFullBody($field) {
    if ($field !== 'body_text' && $field !== 'body_html') {
      throw new \RuntimeException("getFullBody() only supports body_text and body_html");
    }
    $mailing = $this->getMailing();
    $header = $mailing->header_id && $mailing->header_id != 'null' ? \CRM_Mailing_BAO_Component::findById($mailing->header_id) : NULL;
    $footer = $mailing->footer_id && $mailing->footer_id != 'null' ? \CRM_Mailing_BAO_Component::findById($mailing->footer_id) : NULL;
    if (empty($mailing->{$field})) {
      return NULL;
    }
    return ($header ? $header->{$field} : '') . $mailing->{$field} . ($footer ? $footer->{$field} : '');
  }

}
