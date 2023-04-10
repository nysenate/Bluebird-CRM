<?php

//this may not be required as it doesn't appear to be used anywhere?
//require_once 'packages/premailer/premailer.php';

use CRM_Mosaico_ExtensionUtil as E;

/**
 * Class CRM_Mosaico_Utils
 */
class CRM_Mosaico_Utils {

  /**
   * Maximum image size, in total pixels, allowed for on-the-fly generation.
   *
   * @var int
   */
  const MAX_IMAGE_PIXELS = 36000000;

  /**
   * Get a list of layout options.
   *
   * @return array
   *   Array (string $machineName => string $label).
   */
  public static function getLayoutOptions() {
    return [
      'auto' => E::ts('Automatically select a layout'),
      'bootstrap-single' => E::ts('Single Page (Bootstrap CSS)'),
      'bootstrap-wizard' => E::ts('Wizard (Bootstrap CSS)'),
    ];
  }

  /**
   * Get a list of graphics handling options.
   *
   * @return array
   *   Array (string $machineName => string $label).
   */
  public static function getGraphicsOptions() {
    return [
      'auto' => E::ts('Automatically select a driver'),
      'iv-gd' => E::ts('Intervention Image API (gd)'),
      'iv-imagick' => E::ts('Intervention Image API (imagick)'),
      'imagick' => E::ts('(Deprecated) Direct ImageMagick API'),
    ];
  }

  /**
   * Get a list of image resize scale factors
   *
   * @return array
   *   Array (int $machineName => string $label).
   */
  public static function getResizeScaleFactor() {
    return [
      '' => E::ts('None'),
      3 => E::ts('3x'),
      2 => E::ts('2x'),
    ];
  }

  /**
   * Get a list of image resize scale width limits
   *
   * @return array
   *   Array (int $machineName => string $label).
   */
  public static function getResizeScaleWidthLimit() {
    return [
      ''   => E::ts('None'),
      190  => E::ts('Upto 190 pixels (e.g 3 column blocks)'),
      285  => E::ts('Upto 285 pixels (e.g 2 column blocks)'),
      999  => E::ts('Upto 570 pixels (e.g 1 column blocks)'),
      9999 => E::ts('All (other) sizes'),
    ];
  }

  /**
   * Get the list of templates directly from the disk.
   */
  public static function findBaseTemplatesFromDisk() {
    $templates = CRM_Mosaico_BAO_MosaicoTemplate::findBaseTemplates(TRUE, FALSE);
    return array_column($templates, 'name', 'name');
  }

  /**
   * Get a list of token options.
   *
   * @param boolean $hotlist
   *   return only hotlist tokens if TRUE
   *
   * @return array
   *   Array (string $machineName => string $label).
   */
  public static function getMailingTokens($hotlist = FALSE) {
    $mailTokens = civicrm_api3('Mailing', 'gettokens', [
      'entity' => ['contact', 'mailing'],
      'sequential' => 1,
    ])['values'];
    $hotlistTokens = Civi::settings()->get('mosaico_hotlist_tokens');
    $tokens = [];
    foreach ($mailTokens as $token) {
      if (!empty($token['children'])) {
        foreach ($token['children'] as $child) {
          if (!empty($child['id'])) {
            if ($hotlist) {
              if (in_array($child['id'], $hotlistTokens)) {
                $tokens[$child['text']] = $child['id'];
              }
            }
            else {
              $tokens[$child['id']] = $child['text'];
            }
          }
        }
      }
    }
    return $tokens;
  }

  /**
   * Get the path to the Mosaico layout file.
   *
   * @return string
   *   Ex: `~/crmMosaico/EditMailingCtrl/mosaico.html`
   * @see getLayoutOptions()
   */
  public static function getLayoutPath() {
    $layout = CRM_Core_BAO_Setting::getItem('Mosaico Preferences', 'mosaico_layout');
    $prefix = '~/crmMosaico/EditMailingCtrl';

    $paths = [
      'bootstrap-single' => "$prefix/bootstrap-single.html",
      'bootstrap-wizard' => "$prefix/bootstrap-wizard.html",
    ];

    // Legacy handling for 'crmstar-single' - treat as 'auto'
    if (empty($layout) || $layout === 'auto' || $layout === 'crmstar-single') {
      return $paths['bootstrap-wizard'];
    }
    elseif (isset($paths[$layout])) {
      return $paths[$layout];
    }
    else {
      throw new \RuntimeException("Failed to determine path for Mosaico layout ($layout)");

    }
  }

  /**
   * Determine the URL of the (upstream) Mosaico libraries.
   *
   * @param string $preferFormat
   *   'absolute' or 'relative'.
   * @param string|NULL $file
   *   The file within the Mosaico library.
   * @return string
   *   Ex: "https://example.com/sites/all/modules/civicrm/tools/extension/uk.co.vedaconsulting.mosaico/packages/mosaico/dist".
   */
  public static function getMosaicoDistUrl($preferFormat, $file = NULL) {
    $key = "distUrl";
    if (!isset(Civi::$statics[__CLASS__][$key])) {
      Civi::$statics[__CLASS__][$key] = CRM_Core_Resources::singleton()->getUrl('uk.co.vedaconsulting.mosaico', 'packages/mosaico/dist');
    }
    return self::filterAbsoluteRelative($preferFormat, Civi::$statics[__CLASS__][$key] . ($file ? "/$file" : ''));
  }

  /**
   * Determine the URL of the Mosaico templates folder.
   *
   * @param string $preferFormat
   *   'absolute' or 'relative'.
   * @param string|NULL $file
   *   The file within the template library.
   * @return string
   *   Ex: "https://example.com/sites/all/modules/civicrm/tools/extension/uk.co.vedaconsulting.mosaico/packages/mosaico/templates".
   */
  public static function getTemplatesUrl($preferFormat, $file = NULL) {
    $key = "templatesUrl";
    if (!isset(Civi::$statics[__CLASS__][$key])) {
      Civi::$statics[__CLASS__][$key] = CRM_Core_Resources::singleton()->getUrl('uk.co.vedaconsulting.mosaico', 'packages/mosaico/templates');
    }
    return self::filterAbsoluteRelative($preferFormat, Civi::$statics[__CLASS__][$key] . ($file ? "/$file" : ''));
  }

  /**
   * @param string $preferFormat
   *   'absolute' or 'relative'.
   * @param string $url
   * @return string
   */
  private static function filterAbsoluteRelative($preferFormat, $url) {
    if ($preferFormat === 'absolute' && !preg_match('/^https?:/', $url)) {
      $url = (\CRM_Utils_System::isSSL() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $url;
    }
    return $url;
  }

  public static function getUrlMimeType($url) {
    $buffer = file_get_contents($url);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->buffer($buffer);
  }

  public static function getConfig() {
    static $mConfig = [];

    if (empty($mConfig)) {
      $civiConfig = CRM_Core_Config::singleton();

      $mConfig = [
        /* base url for image folders */
        'BASE_URL' => $civiConfig->imageUploadURL,

        /* local file system base path to where image directories are located */
        'BASE_DIR' => $civiConfig->imageUploadDir,

        /* url to the static images folder (relative to BASE_URL) */
        'UPLOADS_URL' => "images/uploads/",

        /* local file system path to the static images folder (relative to BASE_DIR) */
        'UPLOADS_DIR' => "images/uploads/",

        /* url to the static images folder (relative to BASE_URL) */
        'STATIC_URL' => "images/uploads/static/",

        /* local file system path to the static images folder (relative to BASE_DIR) */
        'STATIC_DIR' => "images/uploads/static/",

        /* url to the thumbnail images folder (relative to'BASE_URL'*/
        'THUMBNAILS_URL' => "images/uploads/thumbnails/",

        /* local file system path to the thumbnail images folder (relative to BASE_DIR) */
        'THUMBNAILS_DIR' => "images/uploads/thumbnails/",

        /* width and height of generated thumbnails */
        'THUMBNAIL_WIDTH' => 90,
        'THUMBNAIL_HEIGHT' => 90,

        'MOBILE_MIN_WIDTH' => 246,
      ];
    }

    return $mConfig;
  }

  /**
   * handler for upload requests
   */
  public static function processUpload() {
    $config = self::getConfig();

    global $http_return_code;

    $messages = [];
    _mosaico_civicrm_check_dirs($messages);
    if (!empty($messages)) {
      CRM_Core_Error::debug_log_message('Mosaico uploader failed. Check system status for directory errors.');
      $http_return_code = 500;
      return;
    }

    $files = [];

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
      $dir = scandir($config['BASE_DIR'] . $config['UPLOADS_DIR']);

      foreach ($dir as $file_name) {
        $file_path = $config['BASE_DIR'] . $config['UPLOADS_DIR'] . $file_name;

        if (is_file($file_path)) {
          $size = filesize($file_path);

          $file = [
            "name" => $file_name,
            "url" => $config['BASE_URL'] . $config['UPLOADS_DIR'] . $file_name,
            "size" => $size,
          ];

          if (file_exists($config['BASE_DIR'] . $config['THUMBNAILS_DIR'] . $file_name)) {
            $file["thumbnailUrl"] = $config['BASE_URL'] . $config['THUMBNAILS_URL'] . $file_name;
          }

          $files[] = $file;
        }
      }
    }
    elseif (!empty($_FILES)) {
      foreach ($_FILES["files"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
          $tmp_name = $_FILES["files"]["tmp_name"][$key];

          $file_name = $_FILES["files"]["name"][$key];
          //issue - https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/issues/28
          //Change file name to unique by adding hash so every time uploading same image it will create new image name
          $file_name = CRM_Utils_File::makeFileName($file_name);

          $file_path = $config['BASE_DIR'] . $config['UPLOADS_DIR'] . $file_name;

          if (move_uploaded_file($tmp_name, $file_path) === TRUE) {
            $size = filesize($file_path);

            $thumbnail_path = $config['BASE_DIR'] . $config['THUMBNAILS_DIR'] . $file_name;
            try {
              Civi::service('mosaico_graphics')->createResizedImage($file_path, $thumbnail_path, $config['THUMBNAIL_WIDTH'], $config['THUMBNAIL_HEIGHT']);
              $file = [
                "name" => $file_name,
                "url" => $config['BASE_URL'] . $config['UPLOADS_DIR'] . $file_name,
                "size" => $size,
                "thumbnailUrl" => $config['BASE_URL'] . $config['THUMBNAILS_URL'] . $file_name,
              ];
            }
            catch (\Exception $e) {
              CRM_Core_Error::debug_log_message($e->getMessage());
              $file = [
                'error' => $e->getMessage(),
              ];
            }

            $files[] = $file;
          }
          else {
            $http_return_code = 500;
            return;
          }
        }
        else {
          $http_return_code = 400;
          return;
        }
      }
    }

    header("Content-Type: application/json; charset=utf-8");
    header("Connection: close");

    echo json_encode(["files" => $files]);
    CRM_Utils_System::civiExit();
  }

  /**
   * handler for img requests
   */
  public static function processImg() {
    $config = self::getConfig();
    $methods = ['placeholder', 'resize', 'cover'];
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
      CRM_Utils_System::civiExit();
    }

    $method = CRM_Utils_Request::retrieveValue('method', 'String', 'cover', FALSE, 'GET');
    if (!in_array($method, $methods)) {
      self::returnBadRequest('Invalid method for processImg: ' . $method);
    }

    $rawParams = CRM_Utils_Request::retrieveValue('params', 'String', NULL, FALSE, 'GET');
    if (empty($rawParams)) {
      self::returnBadRequest('Invalid params for processImg: ' . $rawParams);
    }
    $params = explode(',', $rawParams);
    $width = (int) $params[0];
    $height = (int) $params[1];

    // Apply a sensible maximum size for images in an email
    if ($width * $height > self::MAX_IMAGE_PIXELS) {
      self::returnBadRequest('The requested image size is too large');
    }

    // Sometimes output buffer started by another module or plugin causes problem with
    // image rendering. Let's clean any such buffers.
    $levels = ob_get_level();
    for ($i = 0; $i < $levels; $i++) {
      ob_end_clean();
    }

    switch ($method) {
      case 'placeholder':
        Civi::service('mosaico_graphics')->sendPlaceholder($width, $height);
        break;

      case 'resize':
      case 'cover':
        $func = ($method === 'resize') ? 'createResizedImage' : 'createCoveredImage';

        $path_parts = pathinfo(CRM_Utils_String::purifyHTML(urldecode(str_replace('%25', '%', CRM_Utils_Request::retrieveValue('src', 'String', NULL, TRUE, 'GET')))));
        $src_file = $config['BASE_DIR'] . $config['UPLOADS_DIR'] . $path_parts["basename"];
        $cache_file = $config['BASE_DIR'] . $config['STATIC_DIR'] . $path_parts["basename"];
        // $cache_file = $config['BASE_DIR'] . $config['STATIC_DIR'] . $method . '-' . $width . "x" . $height . '-' . $path_parts["basename"];
        // The current naming convention for cache-files is buggy because it means that all variants
        // of the basename *must* have the same size, which breaks scenarios for re-using images
        // from the gallery. However, to fix it, one must also fix CRM_Mosaico_ImageFilter.

        if (!file_exists($src_file)) {
          self::returnBadRequest("Failed to locate source file: {$path_parts['basename']}");
        }
        if (!file_exists($cache_file)) {
          Civi::service('mosaico_graphics')->$func($src_file, $cache_file, $width, $height);
        }
        self::sendImage($cache_file);
        break;

    }
    CRM_Utils_System::civiExit();
  }

  private static function returnBadRequest($message) {
    \Civi::log('mosaico')->error($message);
    http_response_code(400);
    CRM_Utils_System::civiExit();
  }

  /**
   * @param string $file
   *   Full path to the image file.
   */
  public static function sendImage(string $file) {
    $mimeMap = [
      'gif' => 'image/gif',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png' => 'image/png',
    ];
    $mime_type = $mimeMap[pathinfo($file, PATHINFO_EXTENSION)] ?? 'image/jpeg';

    // 30days (60sec * 60min * 24hours * 30days)
    $expiry_time = 2592000;
    header("Pragma: public");
    header("Cache-Control: max-age=" . $expiry_time . ", public");
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expiry_time) . ' GMT');
    header("Content-type:" . $mime_type);

    readfile($file);
    ob_flush();
    flush();
  }

}
