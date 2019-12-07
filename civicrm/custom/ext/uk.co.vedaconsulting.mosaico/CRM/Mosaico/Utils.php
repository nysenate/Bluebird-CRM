<?php

//this may not be required as it doesn't appear to be used anywhere?
//require_once 'packages/premailer/premailer.php';

use CRM_Mosaico_ExtensionUtil as E;

/**
 * Class CRM_Mosaico_Utils
 */
class CRM_Mosaico_Utils {

  public static function isBootstrap() {
    return strpos(CRM_Mosaico_Utils::getLayoutPath(), '/crmstar-') === FALSE;
  }

  /**
   * Get a list of layout options.
   *
   * @return array
   *   Array (string $machineName => string $label).
   */
  public static function getLayoutOptions() {
    return array(
      'auto' => E::ts('Automatically select a layout'),
      'crmstar-single' => E::ts('Single Page (crm-*)'),
      'bootstrap-single' => E::ts('Single Page (Bootstrap CSS)'),
      'bootstrap-wizard' => E::ts('Wizard (Bootstrap CSS)'),
    );
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
   * Get the path to the Mosaico layout file.
   *
   * @return string
   *   Ex: `~/crmMosaico/EditMailingCtrl/mosaico.html`
   * @see getLayoutOptions()
   */
  public static function getLayoutPath() {
    $layout = CRM_Core_BAO_Setting::getItem('Mosaico Preferences', 'mosaico_layout');
    $prefix = '~/crmMosaico/EditMailingCtrl';

    $paths = array(
      'crmstar-single' => "$prefix/crmstar-single.html",
      'bootstrap-single' => "$prefix/bootstrap-single.html",
      'bootstrap-wizard' => "$prefix/bootstrap-wizard.html",
    );

    if (empty($layout) || $layout === 'auto') {
      return CRM_Extension_System::singleton()->getMapper()->isActiveModule('shoreditch')
        ? $paths['bootstrap-wizard'] : $paths['crmstar-single'];
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
    static $mConfig = array();

    if (empty($mConfig)) {
      $civiConfig = CRM_Core_Config::singleton();

      $mConfig = array(
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
      );
    }

    return $mConfig;
  }


  /**
   * handler for upload requests
   */
  public static function processUpload() {
    $config = self::getConfig();

    global $http_return_code;

    $messages = array();
    _mosaico_civicrm_check_dirs($messages);
    if (!empty($messages)) {
      CRM_Core_Error::debug_log_message('Mosaico uploader failed. Check system status for directory errors.');
      $http_return_code = 500;
      return;
    }

    $files = array();

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
      $dir = scandir($config['BASE_DIR'] . $config['UPLOADS_DIR']);

      foreach ($dir as $file_name) {
        $file_path = $config['BASE_DIR'] . $config['UPLOADS_DIR'] . $file_name;

        if (is_file($file_path)) {
          $size = filesize($file_path);

          $file = array(
            "name" => $file_name,
            "url" => $config['BASE_URL'] . $config['UPLOADS_DIR'] . $file_name,
            "size" => $size,
          );

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

            $image = new Imagick($file_path);

            $image->resizeImage($config['THUMBNAIL_WIDTH'], $config['THUMBNAIL_HEIGHT'], Imagick::FILTER_LANCZOS, 1.0, TRUE);
            // $image->writeImage( $config['BASE_DIR'] . $config[ THUMBNAILS_DIR ] . $file_name );
            if ($f = fopen($config['BASE_DIR'] . $config['THUMBNAILS_DIR'] . $file_name, "w")) {
              $image->writeImageFile($f);
            }
            $image->destroy();

            $file = array(
              "name" => $file_name,
              "url" => $config['BASE_URL'] . $config['UPLOADS_DIR'] . $file_name,
              "size" => $size,
              "thumbnailUrl" => $config['BASE_URL'] . $config['THUMBNAILS_URL'] . $file_name,
            );

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

    echo json_encode(array("files" => $files));
    CRM_Utils_System::civiExit();
  }

  /**
   * handler for img requests
   */
  public static function processImg() {
    $config = self::getConfig();
    $methods = ['placeholder', 'resize', 'cover'];
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
      $method = CRM_Utils_Array::value('method', $_GET, 'cover');
      if (!in_array($method, $methods)) {
        $method = 'cover'; // Old behavior. Seems silly. Being cautious.
      }

      $params = explode(",", $_GET["params"]);
      $width = (int) $params[0];
      $height = (int) $params[1];

      switch ($method) {
        case 'placeholder':
          Civi::service('mosaico_graphics')->sendPlaceholder($width, $height);
          break;

        case 'resize':
        case 'cover':
          $func = ($method === 'resize') ? 'createResizedImage' : 'createCoveredImage';

          $path_parts = pathinfo($_GET["src"]);
          $src_file = $config['BASE_DIR'] . $config['UPLOADS_DIR'] . $path_parts["basename"];
          $cache_file = $config['BASE_DIR'] . $config['STATIC_DIR'] . $path_parts["basename"];
          // $cache_file = $config['BASE_DIR'] . $config['STATIC_DIR'] . $method . '-' . $width . "x" . $height . '-' . $path_parts["basename"];
          // The current naming convention for cache-files is buggy because it means that all variants
          // of the basename *must* have the same size, which breaks scenarios for re-using images
          // from the gallery. However, to fix it, one must also fix CRM_Mosaico_ImageFilter.

          if (!file_exists($src_file)) {
            throw new \Exception("Failed to locate source file: " . $path_parts["basename"]);
          }
          if (!file_exists($cache_file)) {
            Civi::service('mosaico_graphics')->$func($src_file, $cache_file, $width, $height);
          }
          self::sendImage($cache_file);
          break;

      }
    }
    CRM_Utils_System::civiExit();
  }

  /**
   * @param string $file
   *   Full path to the image file.
   */
  public static function sendImage($file) {
    $mimeMap = [
      'gif' => 'image/gif',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png' => 'image/png',
    ];
    $mime_type = CRM_Utils_Array::value(
      pathinfo($file, PATHINFO_EXTENSION), $mimeMap, 'image/jpeg');

    $expiry_time = 2592000;  //30days (60sec * 60min * 24hours * 30days)
    header("Pragma: cache");
    header("Cache-Control: max-age=" . $expiry_time . ", public");
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expiry_time) . ' GMT');
    header("Content-type:" . $mime_type);

    $fh = fopen($file, 'r');
    if ($fh === FALSE) {
      throw new \Exception("Failed to read image file: $file");
    }
    while (!feof($fh)) {
      echo fread($fh, 2048);
    }
    fclose($fh);
  }

}
