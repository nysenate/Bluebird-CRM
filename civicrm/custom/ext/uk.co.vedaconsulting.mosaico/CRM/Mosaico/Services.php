<?php


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Civi\FlexMailer\FlexMailer as FM;

/**
 * Class CRM_Mosaico_Services
 *
 * Define the services
 */
class CRM_Mosaico_Services {

  public static function registerServices(ContainerBuilder $container) {
    if (version_compare(\CRM_Utils_System::version(), '4.7.0', '>=')) {
      $container->addResource(new \Symfony\Component\Config\Resource\FileResource(__FILE__));
    }

    if (!CRM_Extension_System::singleton()->getMapper()->isActiveModule('flexmailer')) {
      return;
    }
    $container->setDefinition('mosaico_flexmail_composer', new Definition('CRM_Mosaico_MosaicoComposer'));
    $container->setDefinition('mosaico_flexmail_url_filter', new Definition('CRM_Mosaico_UrlFilter'));
    $container->setDefinition('mosaico_required_tokens', new Definition('CRM_Mosaico_MosaicoRequiredTokens'));

    foreach (self::getListenerSpecs() as $listenerSpec) {
      $container->findDefinition('dispatcher')->addMethodCall('addListenerService', $listenerSpec);
    }
  }

  protected static function getListenerSpecs() {
    $listenerSpecs = array();

    if (class_exists('\Civi\FlexMailer\Validator')) {
      // TODO Simplify by removing conditional. Wait until at least Feb 2018.
      $listenerSpecs[] = array(\Civi\FlexMailer\Validator::EVENT_CHECK_SENDABLE, array('mosaico_required_tokens', 'onCheckSendable'), FM::WEIGHT_MAIN);
    }
    $listenerSpecs[] = array(FM::EVENT_COMPOSE, array('mosaico_flexmail_composer', 'onCompose'), FM::WEIGHT_MAIN);
    $listenerSpecs[] = array(FM::EVENT_COMPOSE, array('mosaico_flexmail_url_filter', 'onCompose'), FM::WEIGHT_ALTER - 100);

    return $listenerSpecs;
  }

}
