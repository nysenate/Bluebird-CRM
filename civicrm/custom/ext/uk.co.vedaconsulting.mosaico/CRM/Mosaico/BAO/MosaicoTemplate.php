<?php

class CRM_Mosaico_BAO_MosaicoTemplate extends CRM_Mosaico_DAO_MosaicoTemplate {

  /**
   * Helps updating the URLs in templates so they can be reused
   * after restoring a dump database in a new server.
   *
   * URL's can be include either scheme/host or path or both
   *
   * @param string $fromUrl URL of the server where the
   *   templates were created
   * @param string $toUrl URL of the current server
   */
  public static function replaceUrls($fromUrl, $toUrl) {
    $from = parse_url($fromUrl);
    $to = parse_url($toUrl);

    // Update 'template': only uses path without leading slash
    if ($from['path']) {
      $replaceQuery = "UPDATE civicrm_mosaico_template
        SET metadata = JSON_REPLACE(metadata, '$.template',
          REPLACE(
            JSON_UNQUOTE(
              JSON_EXTRACT(metadata, '$.template')
            ),
          %1, %2)
        );";

      CRM_Core_DAO::executeQuery($replaceQuery, [
        1 => [ltrim($from['path'], '/'), 'String'],
        2 => [ltrim($to['path'] ?? '', '/'), 'String'],
      ]);
    }

    // Update 'html' and 'content'
    $replaceQuery = "UPDATE civicrm_mosaico_template
      SET html = REPLACE(html, %1, %2),
          content = REPLACE(content, %1, %2)
      ;";

    // ... with unencoded strings
    CRM_Core_DAO::executeQuery($replaceQuery, [
      1 => [$fromUrl, 'String'],
      2 => [$toUrl, 'String'],
    ]);

    // ... with encoded strings
    CRM_Core_DAO::executeQuery($replaceQuery, [
      1 => [urlencode($fromUrl), 'String'],
      2 => [urlencode($toUrl), 'String'],
    ]);

    // Images load from https://example.com/civicrm/mosaico/img, so update the host part as well
    $hostFrom = str_replace($from['path'], '', $fromUrl);
    $hostTo = str_replace($to['path'], '', $toUrl);
    if ($hostFrom && $hostTo) {
      CRM_Core_DAO::executeQuery($replaceQuery, [
        1 => [$hostFrom . "/civicrm/mosaico", 'String'],
        2 => [$hostTo . "/civicrm/mosaico", 'String'],
      ]);
    }

    // However, 'content' is a json string, and the encoded representation depends on the json encoding options
    // The above works where the JSON_UNESCAPED_SLASHES flag has been used so that the encoded representation is like '/my/path'
    // But without that option, the encoded representation is '\/my\/path'
    // Comparing the decoded values would be better, but this should work for our purposes...
    // executeQuery doesn't like backslahes in 'String', so do this directly.

    $slashedFrom = str_replace('/', '\\\/', $fromUrl);
    $slashedTo = str_replace('/', '\\\/', $toUrl);
    CRM_Core_DAO::executeQuery("UPDATE civicrm_mosaico_template SET content = REPLACE(content, '$slashedFrom', '$slashedTo');");
  }

  /**
   * @return mixed
   */
  public static function findBaseTemplates($ignoreCache = FALSE, $dispatchHooks = TRUE) {
    if (!isset(Civi::$statics[__CLASS__]['bases']) || $ignoreCache) {
      $templatesDir = CRM_Core_Resources::singleton()->getPath('uk.co.vedaconsulting.mosaico');
      if (!$templatesDir) {
        return FALSE;
      }
      $templatesDir .= '/packages/mosaico/templates';
      if (!is_dir($templatesDir)) {
        return FALSE;
      }

      $templatesUrl = CRM_Mosaico_Utils::getTemplatesUrl('absolute');

      $templatesLocation[] = ['dir' => $templatesDir, 'url' => $templatesUrl];

      $customTemplatesDir = \Civi::paths()->getPath(\Civi::settings()->get('mosaico_custom_templates_dir'));
      $customTemplatesUrl = \Civi::paths()->getUrl(\Civi::settings()->get('mosaico_custom_templates_url'), 'absolute');
      if (!is_null($customTemplatesDir) && !is_null($customTemplatesUrl)) {
        if (is_dir($customTemplatesDir)) {
          $templatesLocation[] = ['dir' => $customTemplatesDir, 'url' => $customTemplatesUrl];
        }
      }

      // get list of base templates that needs be to hidden from the UI
      $templatesToHide = \Civi::settings()->get('mosaico_hide_base_templates');

      $records = [];

      foreach ($templatesLocation as $templateLocation) {
        foreach (glob("{$templateLocation['dir']}/*", GLOB_ONLYDIR) as $dir) {
          $template = basename($dir);
          $templateHTML = "{$templateLocation['url']}/{$template}/template-{$template}.html";
          $templateThumbnail = "{$templateLocation['url']}/{$template}/edres/_full.png";

          // let's add hidden flag to templates that needs to be excluded from the display
          $isHidden = !empty($templatesToHide) && in_array($template, $templatesToHide);

          $records[$template] = [
            'name' => $template,
            'title' => $template,
            'thumbnail' => $templateThumbnail,
            'path' => $templateHTML,
            'is_hidden' => $isHidden,
          ];
        }
      }
      // Sort the base templates into alphabetical order
      ksort($records, SORT_NATURAL | SORT_FLAG_CASE);

      if (class_exists('\Civi\Core\Event\GenericHookEvent') && $dispatchHooks) {
        \Civi::dispatcher()->dispatch('hook_civicrm_mosaicoBaseTemplates',
          \Civi\Core\Event\GenericHookEvent::create([
            'templates' => &$records,
          ])
        );
      }

      Civi::$statics[__CLASS__]['bases'] = $records;
    }

    return Civi::$statics[__CLASS__]['bases'];
  }

  public static function getBaseTemplateOptions(): array {
    $suffixMap = [
      'id' => 'name',
      'name' => 'name',
      'label' => 'title',
      'url' => 'thumbnail',
    ];
    $options = [];
    foreach (self::findBaseTemplates() as $template) {
      $option = [];
      if (empty($template['is_hidden'])) {
        foreach ($suffixMap as $suffix => $key) {
          $option[$suffix] = $template[$key];
        }
        $options[] = $option;
      }
    }
    return $options;
  }

}
