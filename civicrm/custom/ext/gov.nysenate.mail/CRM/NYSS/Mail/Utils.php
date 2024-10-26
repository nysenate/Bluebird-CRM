<?php

class CRM_NYSS_Mail_Utils {
  /**
   * @param false $all
   * @return array
   *
   * create missing thumbnails for Mosaico images if they don't exist
   * delete thumbnails if there is no corresponding base file
   * optionally recreate thumbnails for all files
   */
  static function createMosaicoThumbnails($all = FALSE) {
    if (!class_exists('CRM_Mosaico_Utils')) {
      return NULL;
    }

    $results = [
      'processed' => [],
      'skipped' => [],
      'removed' => [],
      'thumb_removed' => [],
    ];

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
            $allowedExts = ['gif','png','jpg','jpeg','jfif','bmp','webp'];
            $disallowedMimeTypes = ['image/heic', 'application/pdf'];
            //CRM_Core_Error::debug_var(__FUNCTION__.' $fileSize', $fileSize, TRUE, TRUE, 'mosaico');

            //if the file is not one of the allowed extension types, skip it
            if (!in_array(strtolower($ext), $allowedExts)) {
              //skip and leave in place
              $results['skipped'][] = $file_name;
              continue;
            }
            //check size of file and mime type
            elseif ($fileSize > 5 || in_array(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file_path), $disallowedMimeTypes)) {
              //delete the file
              $results['removed'][] = $file_name;
              unlink($file_path);
            }
            else {
              Civi::service('mosaico_graphics')->createResizedImage($file_path, $thumbnail_path, $config['THUMBNAIL_WIDTH'], $config['THUMBNAIL_HEIGHT']);
              $results['processed'][] = $file_name;

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
          $results['thumb_removed'][] = $file_name;
          unlink($file_path);
        }
      }
    }
    catch (\Exception $e) {
      //let's not actually trigger errors/get emails about this... will get annoying very quickly
      CRM_Core_Error::debug_var(__FUNCTION__.' $e', $e, TRUE, TRUE, 'mosaico');
    }

    return $results;
  }
}