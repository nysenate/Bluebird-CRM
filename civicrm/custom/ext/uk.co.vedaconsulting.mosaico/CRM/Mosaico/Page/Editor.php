<?php

use CRM_Mosaico_ExtensionUtil as E;

class CRM_Mosaico_Page_Editor extends CRM_Core_Page {
  const DEFAULT_MODULE_WEIGHT = 200;

  public function run() {
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('baseUrl', CRM_Mosaico_Utils::getMosaicoDistUrl('relative'));
    $smarty->assign('scriptUrls', $this->getScriptUrls());
    $smarty->assign('styleUrls', $this->getStyleUrls());
    $smarty->assign('mosaicoConfig', json_encode(
      $this->createMosaicoConfig(),
      defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0
    ));
    $smarty->assign('mosaicoPlugins', $this->getMosaicoPlugins());
    echo $smarty->fetch(self::getTemplateFileName());
    CRM_Utils_System::civiExit();
  }

  protected function getScriptUrls() {
    $cacheCode = CRM_Core_Resources::singleton()->getCacheCode();
    $mosaicoDistUrl = CRM_Mosaico_Utils::getMosaicoDistUrl('relative');
    $mosaicoExtUrl = CRM_Core_Resources::singleton()->getUrl('uk.co.vedaconsulting.mosaico');
    return [
      "{$mosaicoDistUrl}/vendor/knockout.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery.min.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery-ui.min.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery.ui.touch-punch.min.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/load-image.all.min.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/canvas-to-blob.min.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery.iframe-transport.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery.fileupload.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery.fileupload-process.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery.fileupload-image.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/jquery.fileupload-validate.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/knockout-jqueryui.min.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/tinymce.min.js?r={$cacheCode}",
      "{$mosaicoDistUrl}/mosaico.min.js?v=0.15?&={$cacheCode}",
    ];
  }

  protected function getStyleUrls() {
    $cacheCode = CRM_Core_Resources::singleton()->getCacheCode();
    $mosaicoDistUrl = CRM_Mosaico_Utils::getMosaicoDistUrl('relative');
    // $mosaicoExtUrl = CRM_Core_Resources::singleton()->getUrl('uk.co.vedaconsulting.mosaico');
    return [
      "{$mosaicoDistUrl}/mosaico-material.min.css?v=0.10&r={$cacheCode}",
      "{$mosaicoDistUrl}/vendor/notoregular/stylesheet.css?r={$cacheCode}",
    ];
  }

  /**
   * Generate the configuration options for `Mosaico.init()`.
   *
   * @return array
   */
  protected function createMosaicoConfig() {
    $res = CRM_Core_Resources::singleton();
    $mailTokens = civicrm_api3('Mailing', 'gettokens', [
      'entity' => ['contact', 'mailing'],
      'sequential' => 1,
    ]);

    $config = [
      'imgProcessorBackend' => $this->getUrl('civicrm/mosaico/img', NULL, TRUE),
      'imgPlaceholderUrl' => $this->getUrl('civicrm/mosaico/img/placeholder', NULL, FALSE),
      'emailProcessorBackend' => 'unused-emailProcessorBackend',
      'titleToken' => 'MOSAICO Responsive Email Designer',
      'fileuploadConfig' => [
        'url' => $this->getUrl('civicrm/mosaico/upload', NULL, FALSE),
        'maxFileSize' => $this->getMaxFileSize(),
        // messages??
      ],

      // Note: Mosaico displays TinyMCE using two configurations.
      // "tinymceConfig": The standard/base configuration for headings, etc.
      // "tinymceConfigFull": A derivative configuration for paragraphs, etc.
      //    It extends "tinymceConfig" and adds more plugins/buttons.
      // See also: https://www.tinymce.com/docs/configure/integration-and-setup/
      'tinymceConfig' => [
        'convert_urls' => FALSE,
        'external_plugins' => [
          'civicrmtoken' => $res->getUrl('uk.co.vedaconsulting.mosaico', 'js/tinymce-plugins/civicrmtoken/plugin.js', 1),
        ],
        'plugins' => ['paste civicrmtoken'],
        'toolbar1' => 'bold italic civicrmtoken',
        'civicrmtoken' => [
          'tokens' => $mailTokens['values'],
          'hotlist' => CRM_Mosaico_Utils::getMailingTokens(TRUE),
        ],
        'browser_spellcheck' => TRUE,
      ],
      'tinymceConfigFull' => [
        'plugins' => ['link hr paste lists textcolor code civicrmtoken'],
        'toolbar1' => 'bold italic forecolor backcolor hr bullist styleselect removeformat | civicrmtoken | link unlink | pastetext code',
      ],
    ];

    // Adding translation strings if exist
    $locale = CRM_Core_I18n::getLocale();
    $lang = CRM_Core_I18n_PseudoConstant::shortForLong($locale);
    $translationFile = CRM_Core_Resources::singleton()->getPath(E::LONG_NAME, "packages/mosaico/dist/lang/mosaico-{$lang}.json");
    if (file_exists($translationFile)) {
      $config['strings'] = json_decode(file_get_contents($translationFile));
    }

    // Allow configuration to be modified by a hook
    if (class_exists('\Civi\Core\Event\GenericHookEvent')) {
      \Civi::dispatcher()->dispatch('hook_civicrm_mosaicoConfig',
        \Civi\Core\Event\GenericHookEvent::create([
          'config' => &$config,
        ])
      );
    }

    return $config;
  }

  /**
   * Get the URL for a Civi route.
   *
   * @param string $path
   *   Ex: 'civicrm/admin/foo'.
   * @param string $query
   *   Ex: 'reset=1&id=123'.
   * @param bool $frontend
   * @return string
   */
  protected function getUrl($path, $query, $frontend) {
    // This function shouldn't really exist, but it's tiring to set `$htmlize`
    // to false every.single.time we need a URL.
    // These URLs should be absolute -- this influences the final URLs
    // for any uploaded images, and those will need to be absolute to work
    // correctly in all forms of composition/delivery.
    return CRM_Utils_System::url($path, $query, TRUE, NULL, FALSE, $frontend);
  }

  /**
   * @return int
   */
  protected function getMaxFileSize() {
    $fakeUnlimited = 25 * 1024 * 1024;
    $iniVal = ini_get('upload_max_filesize') ? CRM_Utils_Number::formatUnitSize(ini_get('upload_max_filesize'), TRUE) : $fakeUnlimited;
    $settingVal = Civi::settings()->get('maxFileSize') ? (1024 * 1024 * Civi::settings()->get('maxFileSize')) : $fakeUnlimited;
    return (int) min($iniVal, $settingVal);
  }

  /**
   * Gets the plugins for `Mosaico.init()`.
   *
   * @return array
   */
  public function getMosaicoPlugins() {
    $plugins = [];

    // Allow plugins to be added by a hook.
    if (class_exists('\Civi\Core\Event\GenericHookEvent')) {
      \Civi::dispatcher()->dispatch('hook_civicrm_mosaicoPlugin',
        \Civi\Core\Event\GenericHookEvent::create([
          'plugins' => &$plugins,
        ])
      );
    }

    $plugins = '[ ' . implode(',', $plugins) . ' ]';

    return $plugins;
  }

}
