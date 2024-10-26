<?php
use Civi\FlexMailer\Listener\RequiredTokens;

use CRM_Mosaico_ExtensionUtil as E;

/**
 * Class CRM_Mosaico_MosaicoRequiredTokens
 *
 * The token format for Mosaico extends the traditional format -- all
 * traditional tokens (eg "{action.unsubscribeUrl}") are supported, and
 * a few additional aliases (eg "[unsubscribe_link]") are also
 * supported.
 *
 * When validating required tokens, we should accept the aliases.
 */
class CRM_Mosaico_MosaicoRequiredTokens extends RequiredTokens {

  public function __construct() {
    parent::__construct(['mosaico'], []);
  }

  public function getRequiredTokens() {
    $requiredTokens = Civi::service('civi_flexmailer_required_tokens')
      ->getRequiredTokens();

    // Mosaico's templates handle the mailing-address/contact-info
    // differently than the CiviMail templates -- one of the standard
    // blocks provides a section where it prompts you to fill in this info.
    //
    // Arguably, this makes the `{domain.address}` requirement redundant.
    // Arguably, the `{domain.address}` approach is better.
    //
    // For the moment, it's convenient to go along with the Mosaico-way.
    // But it's quite patch-welcome to change (eg inject `{domain.address}`
    // to the default layout, and then enforce the requirement).
    unset($requiredTokens['domain.address']);

    return $requiredTokens;
  }

  public function findMissingTokens($str) {
    $content = ['body_unspecified' => $str];
    _mosaico_civicrm_alterMailContent($content);
    return parent::findMissingTokens($content['body_unspecified']);
  }

}
