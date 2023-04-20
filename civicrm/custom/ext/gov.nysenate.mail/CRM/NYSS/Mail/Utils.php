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
          $thumbnail_path = $config['BASE_DIR'] . $config['THUMBNAILS_DIR'] . $file_name;
          if ($all || !file_exists($thumbnail_path)) {
            //CRM_Core_Error::debug_var(__FUNCTION__.' $thumbnail_path', $thumbnail_path, TRUE, TRUE, 'mosaico');
            //CRM_Core_Error::debug_var(__FUNCTION__.' $file_path', $file_path, TRUE, TRUE, 'mosaico');

            $fileSize = number_format(filesize($file_path) / 1048576, 2);
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $allowedExts = ['gif','png','jpg','jpeg','jfif','tif','tiff','bmp','ico','webp','avif','heic'];
            //CRM_Core_Error::debug_var(__FUNCTION__.' $fileSize', $fileSize, TRUE, TRUE, 'mosaico');

            //if the file is not one of the allowed extension types, skip it
            if (!in_array($ext, $allowedExts)) {
              //skip and leave in place
            }
            //check size of file; we are limited in the size that can be processed
            elseif ($fileSize > 8) {
              //delete the filef
              unlink($file_path);
            }
            else {
              Civi::service('mosaico_graphics')->createResizedImage($file_path, $thumbnail_path, $config['THUMBNAIL_WIDTH'], $config['THUMBNAIL_HEIGHT']);
            }
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
      CRM_Core_Error::debug_var(__FUNCTION__.' $e', $e, TRUE, TRUE, 'mosaico');
    }
  }
}