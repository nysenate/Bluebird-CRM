<?php

/**
 * Create absolute urls for Mosaico/imagemagick images when sending an email in CiviMail.
 * Convert string below into just the absolute url with addition of static directory where correctly sized image is stored
 * Mosaico image urls are in this format:
 * img?src=BASE_URL+UPLOADS_URL+imagename+imagemagickparams
 */
class CRM_Mosaico_ImageFilter extends \Civi\FlexMailer\Listener\BaseListener {

  /**
   * @var array
   * @see CRM_Mosaico_Utils::getConfig()
   */
  protected $config;

  /**
   * CRM_Mosaico_ImageFilter constructor.
   * @param array $config
   *   The active Mosaico path configuration.
   */
  public function __construct($config = NULL) {
    $this->config = $config === NULL ? CRM_Mosaico_Utils::getConfig() : $config;
  }

  /**
   * @see CRM_Utils_Hook::alterMailContent()
   */
  public function alterMailContent(\Civi\Core\Event\GenericHookEvent $e) {
    if (!$this->isActive()) {
      return;
    }

    $mosaico_image_upload_dir = rawurlencode($this->config['BASE_URL'] . $this->config['UPLOADS_URL']);

    $e->content = preg_replace_callback(
      "/src=\".+img[\/]?\?src=(" . $mosaico_image_upload_dir . ")(.+)&.*\"/U",
      function($matches){
        return "src=\"" . rawurldecode($matches[1]) . "static/" . rawurldecode($matches[2]) . "\"";
      },
      $e->content
    );
  }

}
