<?php

use CRM_NyssPrintExport_ExtensionUtil as E;
use GuzzleHttp\Psr7\Response;

/**
 * Streams a print production export file from the printProduction upload
 * directory. Copied from nyss_civihooks_getfile() (Drupal route nyss_getfile),
 * which remains in place for the legacy nyss_io import module.
 */
class CRM_NYSS_PrintExport_Page_GetFile extends CRM_Core_Page
{
  // must match xml/Menu/nyss_print_export.xml
  public const PATH = 'civicrm/nyss/printexport/getfile';

  /**
   * Absolute download URL for a file in the printProduction directory.
   */
  public static function url(string $filename): string
  {
    return CRM_Utils_System::url(self::PATH, ['file' => $filename], TRUE, NULL, FALSE);
  }

  public function run()
  {
    $config = CRM_Core_Config::singleton();
    $file = CRM_Utils_Request::retrieveValue('file', 'String');

    if (empty($file)) {
      self::sendError(400, E::ts('No file was specified. Make sure you have entered the full URL.'));
    }

    $basepath = rtrim($config->uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'printProduction';
    $realbase = realpath($basepath);
    $realfullpath = $realbase === FALSE ? FALSE : realpath($realbase . DIRECTORY_SEPARATOR . $file);

    // security check. prevent path traversal attack
    if ($realfullpath !== FALSE && strpos($realfullpath, $realbase . DIRECTORY_SEPARATOR) !== 0) {
      self::sendError(400, E::ts('Invalid file provided. Make sure you have entered the full URL.'));
    }

    if ($realfullpath === FALSE || !is_file($realfullpath)) {
      self::sendError(404, E::ts('The requested file was not found. It may have been removed.'));
    }

    $fh = fopen($realfullpath, 'r');
    if (!$fh) {
      self::sendError(500, E::ts('Unable to open the requested file.'));
    }

    // discard any buffered page output so it doesn't corrupt the download
    while (ob_get_level()) {
      ob_end_clean();
    }

    // these exports contain constituent data; don't let browsers or proxies keep a copy
    $headers = [
      'Content-Type' => 'text/tab-separated-values; charset=utf-8',
      'Content-Disposition' => 'attachment; filename="' . basename($realfullpath) . '"',
      'Content-Length' => (string) filesize($realfullpath),
      'Cache-Control' => 'private, no-store',
      'X-Content-Type-Options' => 'nosniff',
    ];
    foreach ($headers as $name => $value) {
      CRM_Utils_System::setHttpHeader($name, $value);
    }

    // stream rather than using CRM_Utils_System::download()/sendResponse(),
    // both of which read the whole (potentially large) file into memory
    fpassthru($fh);
    fclose($fh);
    CRM_Utils_System::civiExit();
  }

  /**
   * Send a plain-text error response and exit.
   */
  private static function sendError(int $status, string $message)
  {
    CRM_Utils_System::sendResponse(new Response($status, ['Content-Type' => 'text/plain; charset=utf-8'], $message));
  }

}
