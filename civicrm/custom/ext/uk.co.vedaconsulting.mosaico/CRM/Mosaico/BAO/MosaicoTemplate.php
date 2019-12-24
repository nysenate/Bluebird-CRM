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
   * @return mixed
   */
  public static function findBaseTemplates() {
    if (!isset(Civi::$statics[__CLASS__]['bases'])) {
      $templatesDir = CRM_Core_Resources::singleton()->getPath('uk.co.vedaconsulting.mosaico');
      if (!$templatesDir) {
        return FALSE;
      }
      $templatesDir .= '/packages/mosaico/templates';
      if (!is_dir($templatesDir)) {
        return FALSE;
      }

      $templatesUrl = CRM_Mosaico_Utils::getTemplatesUrl('absolute');

      $templatesLocation[] = array('dir' => $templatesDir, 'url' => $templatesUrl);

      $customTemplatesDir = \Civi::paths()->getPath(CRM_Core_BAO_Setting::getItem('Mosaico Preferences', 'mosaico_custom_templates_dir'));
      $customTemplatesUrl = \Civi::paths()->getUrl(CRM_Core_BAO_Setting::getItem('Mosaico Preferences', 'mosaico_custom_templates_url'));
      if (!is_null($customTemplatesDir) && !is_null($customTemplatesUrl)) {
        if (is_dir($customTemplatesDir)) {
          $templatesLocation[] = array('dir' => $customTemplatesDir, 'url' => $customTemplatesUrl);
        }
      }

      $records = array();

      foreach ($templatesLocation as $templateLocation) {
        foreach (glob("{$templateLocation['dir']}/*", GLOB_ONLYDIR) as $dir) {
          $template = basename($dir);
          $templateHTML = "{$templateLocation['url']}/{$template}/template-{$template}.html";
          $templateThumbnail = "{$templateLocation['url']}/{$template}/edres/_full.png";

          $records[$template] = array(
            'name' => $template,
            'title' => $template,
            'thumbnail' => $templateThumbnail,
            'path' => $templateHTML,
          );
        }
      }
      // Sort the base templates into alphabetical order
      ksort($records, SORT_NATURAL | SORT_FLAG_CASE);

      if (class_exists('\Civi\Core\Event\GenericHookEvent')) {
        \Civi::dispatcher()->dispatch('hook_civicrm_mosaicoBaseTemplates',
          \Civi\Core\Event\GenericHookEvent::create(array(
            'templates' => &$records,
          ))
        );
      }

      Civi::$statics[__CLASS__]['bases'] = $records;
    }

    return Civi::$statics[__CLASS__]['bases'];
  }

}
