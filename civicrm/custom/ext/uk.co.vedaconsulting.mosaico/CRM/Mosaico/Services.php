<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Civi\FlexMailer\FlexMailer as FM;
use CRM_Mosaico_ExtensionUtil as E;

/**
 * Class CRM_Mosaico_Services
 *
 * Define the services
 */
class CRM_Mosaico_Services {

  public static function registerServices(ContainerBuilder $container) {
    $container->addResource(new \Symfony\Component\Config\Resource\FileResource(__FILE__));

    if (!CRM_Extension_System::singleton()->getMapper()->isActiveModule('flexmailer')) {
      return;
    }
    $container->setDefinition('mosaico_flexmail_composer', new Definition('CRM_Mosaico_MosaicoComposer'))->setPublic(TRUE);
    $container->setDefinition('mosaico_flexmail_url_filter', new Definition('CRM_Mosaico_UrlFilter'))->setPublic(TRUE);
    $container->setDefinition('mosaico_image_filter', new Definition('CRM_Mosaico_ImageFilter'))->setPublic(TRUE);
    $container->setDefinition('mosaico_required_tokens', new Definition('CRM_Mosaico_MosaicoRequiredTokens'))->setPublic(TRUE);
    $container->setDefinition('mosaico_graphics', new Definition('CRM_Mosaico_Graphics_Interface'))
      ->setFactory([__CLASS__, 'createGraphics'])->setPublic(TRUE);

    foreach (self::getListenerSpecs() as $listenerSpec) {
      $container->findDefinition('dispatcher')->addMethodCall('addListenerService', $listenerSpec);
    }
  }

  protected static function getListenerSpecs() {
    $listenerSpecs = [];

    $listenerSpecs[] = [\Civi\FlexMailer\Validator::EVENT_CHECK_SENDABLE, ['mosaico_required_tokens', 'onCheckSendable'], FM::WEIGHT_MAIN];
    $listenerSpecs[] = [FM::EVENT_COMPOSE, ['mosaico_flexmail_composer', 'onCompose'], FM::WEIGHT_MAIN];
    $listenerSpecs[] = [FM::EVENT_COMPOSE, ['mosaico_flexmail_url_filter', 'onCompose'], FM::WEIGHT_ALTER - 100];
    $listenerSpecs[] = ['hook_civicrm_alterMailContent', ['mosaico_image_filter', 'alterMailContent']];

    return $listenerSpecs;
  }

  /**
   * @return \CRM_Mosaico_Graphics_Interface
   */
  public static function createGraphics() {
    $graphics = Civi::settings()->get('mosaico_graphics');

    // Apply translations for imprecise settings.
    switch ($graphics) {
      case 'auto':
        self::applyAdhocClassloaderSafely();
        if (CRM_Mosaico_Graphics_Intervention::isClassLoaded() && extension_loaded('gd')) {
          $graphics = 'iv-gd';
        }
        elseif (CRM_Mosaico_Graphics_Intervention::isClassLoaded() && extension_loaded('imagick') && class_exists("Imagick")) {
          $graphics = 'iv-imagick';
        }
        elseif (!CRM_Mosaico_Graphics_Intervention::isClassLoaded() && extension_loaded('imagick') && class_exists("Imagick")) {
          $graphics = 'imagick';
        }
        break;
    }

    // Instantiate the actual driver.
    switch ($graphics) {
      case 'imagick':
        return new CRM_Mosaico_Graphics_Imagick();

      case 'iv-gd':
        self::applyAdhocClassloaderSafely();
        return new CRM_Mosaico_Graphics_Intervention(['driver' => 'gd']);

      case 'iv-imagick':
        self::applyAdhocClassloaderSafely();
        return new CRM_Mosaico_Graphics_Intervention(['driver' => 'imagick']);

      default:
        throw new CRM_Mosaico_Graphics_Exception("Failed to locate Mosaico graphics driver. Either \"mosaico_graphics\" is invalid or the autodetection failed.");
    }
  }

  /**
   * In a proper world, we would have one class-loader which captures
   * all PHP packages for all extensions. We're not there.
   *
   * This conditionally loads "{mosaico}/vendor/autoload.php" (if available).
   *
   * We do not strictly require the file to exist -- e.g. if we find ourselves
   * in a new world where there is one master class-loader, this still ought to
   * work properly.
   */
  protected static function applyAdhocClassloaderSafely() {
    $path = E::path('vendor/autoload.php');
    if (file_exists($path)) {
      require_once $path;
    }
  }

}
