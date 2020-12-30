<?php

class CRM_Tutorial_BAO_Tutorial {

  /**
   * @param array $params
   * @return array
   */
  public static function create($params) {
    $whitelist = array_column(self::fields(), 'name');
    $tutorial = array_intersect_key($params, array_flip($whitelist));
    $dir = $filePath = Civi::paths()->getPath('[civicrm.files]/crm-tutorials');
    if (!is_dir($dir)) {
      mkdir($dir);
    }
    $allFiles = CRM_Tutorial_BAO_Tutorial::get();
    // Create fileName for id
    if (empty($tutorial['id'])) {
      $fileNames = array_column($allFiles, 'id');
      $tutorial['id'] = CRM_Utils_String::munge($tutorial['url'], '-') . '-' . CRM_Utils_String::munge($tutorial['title']);
      // Add suffix if filename is not already unique
      $suffix = '';
      while (in_array($tutorial['id'] . $suffix, $fileNames)) {
        $suffix = $suffix ? $suffix + 1 : 1;
      }
      $tutorial['id'] .= $suffix;
    }
    $id = $tutorial['id'];
    $filePath = Civi::paths()->getPath('[civicrm.files]/crm-tutorials/' . $id . '.js');
    // Update file if it exists
    if (!empty($params['id'])) {
      foreach ($allFiles as $path => $file) {
        if ($id === $file['id'] && $path == $filePath) {
          $tutorial += $file;
        }
      }
    }
    file_put_contents($filePath, CRM_Tutorial_BAO_Tutorial::encode($tutorial) . "\n");
    Civi::cache('community_messages')->delete('tutorials');
    return $tutorial;
  }

  /**
   * @return string[][]
   */
  public static function fields() {
    return [
      [
        'name' => 'id',
        'data_type' => 'String',
      ],
      [
        'name' => 'url',
        'data_type' => 'String',
      ],
      [
        'name' => 'title',
        'data_type' => 'String',
      ],
      [
        'name' => 'auto_start',
        'data_type' => 'Boolean',
      ],
      [
        'name' => 'groups',
        'data_type' => 'Array',
      ],
      [
        'name' => 'steps',
        'data_type' => 'Array',
      ],
    ];
  }

  /**
   * @return array|mixed
   */
  public static function get() {
    $files = Civi::cache('community_messages')->get('tutorials');
    if ($files === NULL) {
      $files = $paths = [];
      $directories = array_unique(explode(PATH_SEPARATOR, get_include_path()));
      // Files in this directory override others, as this is where user-configured files go.
      $directories[] = Civi::paths()->getPath('[civicrm.files]/.');
      foreach ($directories as $directory) {
        $directory = \CRM_Utils_File::addTrailingSlash($directory);
        $dir = $directory . 'crm-tutorials';
        if (is_dir($dir)) {
          $domain = NULL;
          $source = NULL;
          // If this file is in an extension, read the name & domain from its info.xml file
          if (is_readable($directory . 'info.xml')) {
            $info = strstr(file_get_contents($directory . 'info.xml'), '<extension ');
            if ($info) {
              $domain = strstr(substr(strstr($info, 'key="'), 5), '"', TRUE);
              $source = strstr(substr(strstr($info, '<name>'), 6), '<', TRUE);
            }
          }
          foreach (glob("$dir/*.js") as $file) {
            $matches = [];
            preg_match('/([-a-z_A-Z0-9]*).js/', $file, $matches);
            $id = $matches[1];
            $paths[$id] = $file;
            // Retain original source when overriding file
            if (!$source && !empty($files[$id]['source'])) {
              $source = $files[$id]['source'];
            }
            $files[$id] = self::decode(file_get_contents($file), $domain);
            $files[$id]['id'] = $id;
            $files[$id]['source'] = $source;
          }
        }
      }
      $files = array_combine($paths, $files);
      Civi::cache('community_messages')->set('tutorials', $files, (60 * 60 * 24 * 30));
    }
    return $files;
  }

  /**
   * Encodes json and places ts() around translatable strings.
   *
   * @param $tutorial
   * @return string
   */
  public static function encode($tutorial) {
    // Id is redundant with filename
    unset($tutorial['id']);
    $json = json_encode($tutorial, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return preg_replace('#"(title|content)": (".+")#', '"$1": ts($2)', $json);
  }

  /**
   * Decodes json after localizing strings
   *
   * @param string $json
   * @param string $domain
   * @return array
   */
  public static function decode($json, $domain = NULL) {
    $json = preg_replace_callback('#: ts\((".*")\)#', function ($matches) use ($domain) {
      $text = json_decode($matches[1]);
      $params = $domain ? ['domain' => $domain] : [];
      return ': ' . json_encode(ts($text, $params), JSON_UNESCAPED_SLASHES);
    }, $json);
    $result = json_decode($json, TRUE);
    return $result + ['domain' => $domain];
  }

  /**
   * See if a tutorial matches the current path
   *
   * @param $currentPath
   * @param $tutorialPath
   * @return bool
   */
  public static function matchURL($currentPath, $tutorialPath) {
    $url = parse_url($tutorialPath);
    if (trim($currentPath, '/') == trim($url['path'], '/')) {
      if (!empty($url['query'])) {
        foreach (explode('&', $url['query']) as $item) {
          list($param, $val) = array_pad(explode('=', $item), 2, '');
          if ($item && CRM_Utils_Array::value($param, $_GET) != $val) {
            return FALSE;
          }
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $tutorial
   * @param int $cid
   * @return bool
   * @throws \CiviCRM_API3_Exception
   */
  public static function matchGroup($tutorial, $cid = NULL) {
    if (empty($tutorial['groups'])) {
      return TRUE;
    }
    $contact = civicrm_api3('Contact', 'getsingle', [
      'id' => $cid ?: 'user_contact_id',
      'return' => ["group"],
    ]);
    if (empty($contact['groups'])) {
      return FALSE;
    }
    $groups = civicrm_api3('Group', 'get', [
      'return' => ["name"],
      'id' => ['IN' => explode(',', $contact['groups'])],
    ]);
    $groups = array_column($groups['values'], 'name');

    return !!array_intersect($groups, $tutorial['groups']);
  }

  /**
   * @param $urlPath
   */
  public static function load($urlPath) {
    // Because this hook gets called twice sometimes
    static $ranAlready = FALSE;
    $cid = CRM_Core_Session::getLoggedInContactID();
    if ($cid && !$ranAlready &&
      !CRM_Core_Resources::isAjaxMode() && CRM_Utils_Array::value('HTTP_X_REQUESTED_WITH', $_SERVER) != 'XMLHttpRequest' &&
      CRM_Core_Permission::check('access CiviCRM')
    ) {
      $ranAlready = TRUE;
      $resources = Civi::resources()
        ->addStyleFile('org.civicrm.tutorial', 'vendor/hopscotch/css/hopscotch.min.css')
        // Using patched version of hopscotch.js
        ->addScriptFile('org.civicrm.tutorial', 'vendor/hopscotch/js/hopscotch.js', -103, 'html-header')
        ->addScriptFile('org.civicrm.tutorial', 'js/tutorial.js', -102, 'html-header');
      if (CRM_Core_Permission::check('administer CiviCRM')) {
        $resources
          ->addScriptFile('org.civicrm.tutorial', 'js/tutorial-admin.js', -101, 'html-header')
          ->addPermissions(['administer CiviCRM'])
          ->addVars('tutorial', [
            'basePath' => $resources->getUrl('org.civicrm.tutorial'),
            'urlPath' => $urlPath,
          ]);
        // Add strings from the html files for i18n.
        $strings = new CRM_Core_Resources_Strings(Civi::cache('js_strings'));
        foreach (glob(__DIR__ . '/html/*.html') as $file) {
          $resources->addString($strings->get('org.civicrm.tutorial', $file, 'text/html'), 'org.civicrm.tutorial');
        }
      }
      $tutorials = self::get();
      $matches = [];
      foreach ($tutorials as $path => $tutorial) {
        if (self::matchURL($urlPath, $tutorial['url']) && self::matchGroup($tutorial)) {
          // Check if user has viewed this tutorial already
          $views = (array) Civi::contactSettings($cid)->get('tutorials');
          $tutorial['viewed'] = !empty($views[$tutorial['id']]);
          $matches['items'][$tutorial['id']] = $tutorial;
        }
      }
      $resources->addVars('tutorial', $matches);
    }
  }

  /**
   * @param string[] $params
   */
  public static function delete($params) {
    $filePath = Civi::paths()->getPath('[civicrm.files]/crm-tutorials/' . $params['id'] . '.js');
    unlink($filePath);
    Civi::cache('community_messages')->delete('tutorials');
  }

  /**
   * Mark tutorial as viewed by the current user
   *
   * @param array $params
   */
  public static function mark($params) {
    $cid = CRM_Core_Session::getLoggedInContactID();
    if ($cid) {
      /** @var Civi\Core\SettingsBag $settings */
      $settings = Civi::service('settings_manager')->getBagByContact(NULL, $cid);
      $views = (array) $settings->get('tutorials');
      $views[$params['id']] = date('Y-m-d H:i:s');
      $settings->set('tutorials', $views);
      return [
        'id' => $params['id'],
        'timestamp' => $views[$params['id']],
      ];
    }
    throw new CRM_Core_Exception("Cannot mark tutorial '{$params['id']}': no logged-in user.");
  }

}
