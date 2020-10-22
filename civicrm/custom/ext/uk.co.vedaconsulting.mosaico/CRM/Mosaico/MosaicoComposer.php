<?php

/**
 * Class CRM_Mosaico_MosaicoComposer
 *
 * Responsible for composing individual emails based on the email content.
 *
 * For the most part, we use the same structure as CiviMail -- with some
 * exceptions (like disabling Smarty).
 */
class CRM_Mosaico_MosaicoComposer extends \Civi\FlexMailer\Listener\DefaultComposer {

  public function isSupported(\CRM_Mailing_DAO_Mailing $mailing) {
    return $mailing->template_type === 'mosaico';
  }

  public function createTokenProcessorContext(
    \Civi\FlexMailer\Event\ComposeBatchEvent $e
  ) {
    $context = parent::createTokenProcessorContext($e);
    // Smarty would break Mosaico CSS unless we know what we are doing (eg. handling via extension)
    $context['smarty'] = $e->context['smarty'] ?? FALSE;
    return $context;
  }

  public function createMailParams(
    \Civi\FlexMailer\Event\ComposeBatchEvent $e,
    \Civi\FlexMailer\FlexMailerTask $task,
    \Civi\Token\TokenRow $row
  ) {
    $mailParams = parent::createMailParams($e, $task, $row);
    $mailParams['X-CiviMail-Mosaico'] = 'Yes';
    return $mailParams;
  }

  /**
   * Generate the message templates for use with token-processor.
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   * @return array
   *   A list of templates. Some combination of:
   *     - subject: string
   *     - html: string
   *     - text: string
   */
  public function createMessageTemplates(
    \Civi\FlexMailer\Event\ComposeBatchEvent $e
  ) {
    // Currently building on the BAO's behavior for reconciling
    // HTML/text and header/body/footer.
    $templates = $e->getMailing()->getTemplates();
    \_mosaico_civicrm_alterMailContent($templates);
    if ($this->isClickTracking($e)) {
      $templates = $this->applyClickTracking($e, $templates);
    }
    return $templates;
  }

}
