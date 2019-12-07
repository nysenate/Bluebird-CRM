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

use Civi\FlexMailer\Event\ComposeBatchEvent;
use Civi\FlexMailer\Event\RunEvent;
use Civi\FlexMailer\FlexMailerTask;
use Civi\Token\TokenProcessor;
use Civi\Token\TokenRow;

/**
 * Class DefaultComposer
 * @package Civi\FlexMailer\Listener
 *
 * The DefaultComposer uses a TokenProcessor to generate all messages as
 * a batch.
 */
class DefaultComposer extends BaseListener {

  public function onRun(RunEvent $e) {
    // FIXME: This probably doesn't belong here...
    if (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY) {
      \CRM_Core_Smarty::registerStringResource();
    }
  }

  /**
   * Determine whether this composer knows how to handle this mailing.
   *
   * @param \CRM_Mailing_DAO_Mailing $mailing
   * @return bool
   */
  public function isSupported(\CRM_Mailing_DAO_Mailing $mailing) {
    return TRUE;
  }

  /**
   * Given a mailing and a batch of recipients, prepare
   * the individual messages (headers and body) for each.
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   */
  public function onCompose(ComposeBatchEvent $e) {
    if (!$this->isActive() || !$this->isSupported($e->getMailing())) {
      return;
    }

    $tp = new TokenProcessor(\Civi::service('dispatcher'),
      $this->createTokenProcessorContext($e));

    $tpls = $this->createMessageTemplates($e);
    $tp->addMessage('subject', $tpls['subject'], 'text/plain');
    $tp->addMessage('body_text', isset($tpls['text']) ? $tpls['text'] : '',
      'text/plain');
    $tp->addMessage('body_html', isset($tpls['html']) ? $tpls['html'] : '',
      'text/html');

    $hasContent = FALSE;
    foreach ($e->getTasks() as $key => $task) {
      /** @var FlexMailerTask $task */
      if (!$task->hasContent()) {
        $tp->addRow()->context($this->createTokenRowContext($e, $task));
        $hasContent = TRUE;
      }
    }

    if (!$hasContent) {
      return;
    }

    $tp->evaluate();

    foreach ($tp->getRows() as $row) {
      /** @var TokenRow $row */
      /** @var FlexMailerTask $task */
      $task = $row->context['flexMailerTask'];
      $task->setMailParams(array_merge(
        $this->createMailParams($e, $task, $row),
        $task->getMailParams()
      ));
    }
  }

  /**
   * Define the contextual parameters for the token-processor.
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   * @return array
   */
  public function createTokenProcessorContext(ComposeBatchEvent $e) {
    return array(
      'controller' => get_class($this),
      // FIXME: Use template_type, template_options
      'smarty' => defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY ? TRUE : FALSE,
      'mailingId' => $e->getMailing()->id,
    );
  }

  /**
   * Create contextual data for a message recipient.
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   * @param FlexMailerTask $task
   * @return array
   *   Contextual data describing the recipient.
   *   Typical values are `contactId` or `mailingJobId`.
   */
  public function createTokenRowContext(
    ComposeBatchEvent $e,
    FlexMailerTask $task
  ) {
    return array(
      'contactId' => $task->getContactId(),
      'mailingJobId' => $e->getJob()->id,
      'mailingActionTarget' => array(
        'id' => $task->getEventQueueId(),
        'hash' => $task->getHash(),
        'email' => $task->getAddress(),
      ),
      'flexMailerTask' => $task,
    );
  }

  /**
   * For a given task, prepare the mailing.
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   * @param FlexMailerTask $task
   * @param TokenRow $row
   * @return array
   *   A list of email parameters, such as "Subject", "text", and/or "html".
   * @see \CRM_Utils_Hook::alterMailParams
   */
  public function createMailParams(
    ComposeBatchEvent $e,
    FlexMailerTask $task,
    TokenRow $row
  ) {
    return array(
      'Subject' => $row->render('subject'),
      'text' => $row->render('body_text'),
      'html' => $row->render('body_html'),
    );
  }

  /**
   * Generate the message templates for use with token-processor.
   *
   * @param ComposeBatchEvent $e
   * @return array
   *   A list of templates. Some combination of:
   *     - subject: string
   *     - html: string
   *     - text: string
   */
  public function createMessageTemplates(ComposeBatchEvent $e) {
    $templates = $e->getMailing()->getTemplates();
    if ($this->isClickTracking($e)) {
      $templates = $this->applyClickTracking($e, $templates);
    }
    return $templates;
  }

  /**
   * (Tentative) Alter hyperlinks to perform click-tracking.
   *
   * This functionality probably belongs somewhere else. The
   * current placement feels quirky, and it's hard to inspect
   * via `cv debug:event-dispatcher', but it produces the expected
   * interactions among tokens and click-tracking.
   *
   * @param ComposeBatchEvent $e
   * @param array $templates
   * @return array
   *   Updated templates.
   */
  protected function applyClickTracking(ComposeBatchEvent $e, $templates) {
    $mailing = $e->getMailing();

    if (!empty($templates['html'])) {
      $templates['html'] = \Civi::service('civi_flexmailer_html_click_tracker')
        ->filterContent($templates['html'], $mailing->id,
          '{action.eventQueueId}');
    }
    if (!empty($templates['text'])) {
      $templates['text'] = \Civi::service('civi_flexmailer_text_click_tracker')
        ->filterContent($templates['text'], $mailing->id,
          '{action.eventQueueId}');
    }

    return $templates;
  }

  /**
   * Determine whether to enable click-tracking.
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   * @return bool
   */
  public function isClickTracking(ComposeBatchEvent $e) {
    // Don't track clicks on previews. Doing so would accumulate a lot
    // of garbage data.
    return $e->getMailing()->url_tracking && !$e->isPreview();
  }

}
