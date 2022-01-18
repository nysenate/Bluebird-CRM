<?php

// override Mosaico Editor.php
// ref: https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/issues/347

class CRM_NYSS_Page_MosaicoEditor extends CRM_Mosaico_Page_Editor {
  const DEFAULT_MODULE_WEIGHT = 200;

  public function run() {
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign( 'baseUrl', CRM_Mosaico_Utils::getMosaicoDistUrl( 'relative' ) );
    $smarty->assign( 'scriptUrls', $this->getScriptUrls() );
    $smarty->assign( 'styleUrls', $this->getStyleUrls() );
    $smarty->assign( 'mosaicoPlugins', $this->getMosaicoPlugins() );
    $smarty->assign( 'mosaicoConfig', json_encode(
      $this->createMosaicoConfig(),
      defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0
    ));
    echo $smarty->fetch( self::getTemplateFileName() );
    CRM_Utils_System::civiExit();
  }

  public function getScriptUrls() {
    $scripts = parent::getScriptUrls();

    // Allow scripts to be added by a hook
    if ( class_exists( '\Civi\Core\Event\GenericHookEvent') )
    {
      \Civi::dispatcher()->dispatch(
        'hook_civicrm_mosaicoScripts',
        \Civi\Core\Event\GenericHookEvent::create(
          ['scripts' => &$scripts]
        )
      );
    }

    return $scripts;
  }

  public function getStyleUrls() {
    $styles = parent::getStyleUrls();

    // Allow styles to be added by a hook
    if ( class_exists( '\Civi\Core\Event\GenericHookEvent') )
    {
      \Civi::dispatcher()->dispatch(
        'hook_civicrm_mosaicoStyles',
        \Civi\Core\Event\GenericHookEvent::create(
          ['scripts' => &$styles]
        )
      );
    }
    return $styles;
  }

  public function getMosaicoPlugins() {
    $plugins = [];

    // Allow plugins to be added by a hook
    if ( class_exists( '\Civi\Core\Event\GenericHookEvent') )
    {
      \Civi::dispatcher()->dispatch(
        'hook_civicrm_mosaicoPlugins',
        \Civi\Core\Event\GenericHookEvent::create(
          ['scripts' => &$plugins]
        )
      );
    }
    return '[ ' . implode( ',', $plugins ) . ' ]';
  }
}
