<?php

/**
 * Class CRM_Civicase_Page_ActivityFiles.
 *
 * Handles downloading of attachments of activities.
 */
class CRM_Civicase_Page_ActivityFiles {

  /**
   * Download all activity files contained in a single zip file.
   */
  public static function downloadAll() {
    $activities = self::getActivityFromRequest();

    $zipName = self::getZipName($activities);
    $zipDestination = self::getDestinationPath();
    $zipFullPath = $zipDestination . '/' . $zipName;
    $files = self::getActivityFilePaths($activities);

    $zipFileResource = self::createZipFile($zipFullPath, $files);

    unlink($zipFullPath);
    self::downloadZipFileResource($zipName, $zipFileResource);
  }

  /**
   * Returns the activity specified by the request.
   *
   * In case the request gives an invalid activity id it
   * throws a 404 status code.
   */
  private static function getActivityFromRequest() {
    $activityIds = (array) CRM_Utils_Array::value('activity_ids', $_GET);
    $searchParams = CRM_Utils_Array::value('searchParams', $_GET);

    if (!empty($activityIds)) {
      $activityResult = civicrm_api3('Activity', 'get', [
        'id' => ['IN' => $activityIds],
        'return' => ['activity_type_id.label'],
      ]);
    }
    elseif (!empty($searchParams)) {
      $activityResult = civicrm_api3('Case', 'getfiles', $searchParams);

      // Case.getfiles api returns activity id in "activity_id" instead of "id".
      // Hence reassigning to make it similar like Activity.get.
      foreach ($activityResult['values'] as &$activity) {
        $activity['id'] = $activity['activity_id'];
      }
    }
    else {
      return self::throwStatusCode(404);
    }

    if ($activityResult['count'] === 0) {
      return self::throwStatusCode(404);
    }

    return $activityResult['values'];
  }

  /**
   * Throws a specific status code and closes the connection.
   *
   * @param int $statusCode
   *   Status code.
   */
  private static function throwStatusCode($statusCode) {
    http_response_code($statusCode);
    CRM_Utils_System::civiExit();
  }

  /**
   * Returns the name of the zipped file for the given activity.
   *
   * Ex: Activity Open Case 123.zip.
   *
   * @param array $activities
   *   Activity.
   *
   * @return string
   *   Zip File name.
   */
  private static function getZipName(array $activities) {
    $zipName = '';

    if (count($activities) === 1) {
      $activity = CRM_Utils_Array::first($activities);
      $name = 'Activity ' . $activity['activity_type_id.label'] . ' ' . $activity['id'];

      return CRM_Utils_String::munge($name, ' ') . '.zip';
    }
    else {
      return 'Activities.zip';
    }
  }

  /**
   * Returns the destination path for the zip file.
   *
   * @return string
   *   Destination path.
   */
  private static function getDestinationPath() {
    $config = CRM_Core_Config::singleton();

    return $config->customFileUploadDir;
  }

  /**
   * Returns a list of file paths that are part of a given activity.
   *
   * @param array $activities
   *   Activity ID.
   *
   * @return array
   *   Activity file paths.
   */
  private static function getActivityFilePaths(array $activities) {
    $filePaths = [];

    foreach ($activities as $activity) {
      $activityFiles = CRM_Core_BAO_File::getEntityFile('civicrm_activity', $activity['id']);

      foreach ($activityFiles as $activityFile) {
        $filePaths[] = $activityFile['fullPath'];
      }
    }

    return $filePaths;
  }

  /**
   * Creates a zip file at the given path and containing the given files.
   *
   * @param string $zipFullPath
   *   Zip file path.
   * @param array $filePaths
   *   Individual file paths.
   *
   * @return resource
   *   Resource.
   */
  private static function createZipFile($zipFullPath, array $filePaths) {
    $mode = ZipArchive::CREATE | ZipArchive::OVERWRITE;
    $zip = new ZipArchive();
    $zipName = basename($zipFullPath);
    $zipFileResource = NULL;

    $zip->open($zipFullPath, $mode);

    foreach ($filePaths as $filePath) {
      $fileName = basename($filePath);

      $zip->addFile($filePath, $fileName);
    }

    $zip->close();

    return readfile($zipFullPath, FALSE, $zipFileResource);
  }

  /**
   * Setups the given zip file resource so it can be downloaded by the browser.
   *
   * @param string $zipName
   *   Zip file name.
   * @param resource $zipFileResource
   *   Zip File Resource.
   */
  private static function downloadZipFileResource($zipName, $zipFileResource) {
    CRM_Utils_System::download($zipName, 'application/zip', $fileResource);
  }

}
