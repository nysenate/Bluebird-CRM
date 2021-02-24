<?php

class CRM_NYSS_Mail_Utils {
  /**
   * @param false $all
   *
   * create missing thumbnails for Mosaico images if they don't exist
   * delete thumbnails if there is no corresponding base file
   * optionally recreate thumbnails for all files
   */
  static function createMosaicoThumbnails($all = FALSE) {
    if (!class_exists('CRM_Mosaico_Utils')) {
      return;
    }

    try {
      $config = CRM_Mosaico_Utils::getConfig();

      //create thumbnail images if they don't exist or we are recaching all of them
      $uploads = scandir($config['BASE_DIR'] . $config['UPLOADS_DIR']);
      foreach ($uploads as $file_name) {
        $file_path = $config['BASE_DIR'] . $config['UPLOADS_DIR'] . $file_name;

        if (is_file($file_path)) {
          if ($all || !file_exists($config['BASE_DIR'] . $config['THUMBNAILS_DIR'] . $file_name)) {
            $thumbnail_path = $config['BASE_DIR'] . $config['THUMBNAILS_DIR'] . $file_name;
            Civi::service('mosaico_graphics')->createResizedImage($file_path, $thumbnail_path, $config['THUMBNAIL_WIDTH'], $config['THUMBNAIL_HEIGHT']);
          }
        }
      }

      //delete thumbnail if there is no corresponding base image
      $thumbnails = scandir($config['BASE_DIR'] . $config['THUMBNAILS_DIR']);
      foreach ($thumbnails as $file_name) {
        $file_path = $config['BASE_DIR'] . $config['THUMBNAILS_DIR'] . $file_name;

        if (is_file($file_path) &&
          !file_exists($config['BASE_DIR'] . $config['UPLOADS_DIR'] . $file_name)
        ) {
          unlink($file_path);
        }
      }
    }
    catch (\Exception $e) {
      //let's not actually trigger errors/get emails about this... will get annoying very quickly
      //CRM_Core_Error::debug_var(__FUNCTION__.' $e', $e, TRUE, TRUE, 'mosaico');
    }
  }
}