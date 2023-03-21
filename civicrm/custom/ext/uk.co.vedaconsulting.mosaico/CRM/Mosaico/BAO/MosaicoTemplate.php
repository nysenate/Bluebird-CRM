<?php

class CRM_Mosaico_BAO_MosaicoTemplate extends CRM_Mosaico_DAO_MosaicoTemplate {

  /**
   * Create a new MosaicoTemplate based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Mosaico_DAO_MosaicoTemplate|NULL
   *
  public static function create($params) {
    $className = 'CRM_Mosaico_DAO_MosaicoTemplate';
    $entityName = 'MosaicoTemplate';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

  /**
   * Helps updating the URLs in templates so they can be reused
   * after restoring a dump database in a new server.
   *
   * @param string $fromUrl URL of the server where the
   *   templates were created
   * @param string $toUrl URL of the current server
   */
  public static function replaceUrls($fromUrl, $toUrl) {
    $replaceQuery = "UPDATE civicrm_mosaico_template
      SET metadata = json_replace(metadata, '$.template',
          replace(
              json_unquote(
                  json_extract(metadata, '$.template')
              ),
          %1, %2)
      );";

    CRM_Core_DAO::executeQuery($replaceQuery, [
      1 => [$fromUrl, 'String'],
      2 => [$toUrl, 'String'],
    ]);
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
      $customTemplatesUrl = \Civi::paths()->getUrl(\Civi::settings()->get('mosaico_custom_templates_url'),'absolute');
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

}
