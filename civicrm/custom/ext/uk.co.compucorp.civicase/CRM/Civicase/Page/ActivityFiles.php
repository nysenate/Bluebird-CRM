<?php

class CRM_Civicase_Page_ActivityFiles {

  /**
   * Download all activity files contained in a single zip file.
   */
  public static function downloadAll() {
    $activity = self::getActivityFromRequest();

    $zipName = self::getZipName($activity);
    $zipDestination = self::getDestinationPath();
    $zipFullPath = $zipDestination . '/' . $zipName;
    $files = self::getActivityFilePaths($activity['id']);
    $zipFileResource = self::createZipFile($zipFullPath, $files);

    unlink($zipFullPath);
    self::downloadZipFileResource($zipName, $zipFileResource);
  }

  /**
   * Returns the activity specified by the request. In case the request gives
   * an invalid activity id it throws a 404 status code.
   */
  private static function getActivityFromRequest() {
    $activityId = CRM_Utils_Array::value('activity_id', $_GET);

    self::validateActivityId($activityId);

    $activityResult = civicrm_api3('Activity', 'get', [
      'id' => $activityId,
      'return' => [ 'activity_type_id.label' ]
    ]);

    if ($activityResult['count'] === 0) {
      return self::throwStatusCode(404);
    }

    return CRM_Utils_Array::first($activityResult['values']);
  }

  /**
   * Validates that the activity id was provided. If not, it returns a 404 status code.
   *
   * @param string|null $activityId
   */
  private static function validateActivityId($activityId) {
    if (empty($activityId)) {
      self::throwStatusCode(400);
    }
  }

  /**
   * Throws a specific status code and closes the connection.
   *
   * @param int $statusCode
   */
  private static function throwStatusCode($statusCode) {
    http_response_code($statusCode);
    CRM_Utils_System::civiExit();
  }

  /**
   * Given an activity, it returns the name for the zip file containing all of
   * its files. Ex: Activity Open Case 123.zip
   *
   * @param array $activity
   *
   * @return string
   */
  private static function getZipName($activity) {
    $name = 'Activity ' . $activity['activity_type_id.label'] . ' ' . $activity['id'];

    return CRM_Utils_String::munge($name, ' ') . '.zip';
  }

  /**
   * Returns the destination path for the zip file.
   *
   * @return string
   */
  private static function getDestinationPath() {
    $config = CRM_Core_Config::singleton();

    return $config->customFileUploadDir;
  }

  /**
   * Returns a list of file paths that are part of a given activity.
   *
   * @param int|string $activityId
   *
   * @return array
   */
  private static function getActivityFilePaths($activityId) {
    $filePaths = [];
    $activityFiles = CRM_Core_BAO_File::getEntityFile('civicrm_activity', $activityId);

    foreach ($activityFiles as $activityFile) {
      $filePaths[] = $activityFile['fullPath'];
    }

    return $filePaths;
  }

  /**
   * Creates a zip file at the given path and containing the given files.
   *
   * @param string $zipFullPath
   * @param array $filePaths
   *
   * @return resource
   */
  private static function createZipFile($zipFullPath, $filePaths) {
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
   * @param resource $zipFileResource
   */
  private static function downloadZipFileResource($zipName, $zipFileResource) {
    CRM_Utils_System::download($zipName, 'application/zip', $fileResource);
  }

}
