<?php

/**
 * Recursive Files search helpers using glob patterns.
 */
class CRM_Civicase_Helper_GlobRecursive {

  /**
   * Recursive Glob function.
   *
   * Source: http://php.net/manual/en/function.glob.php#106595
   * Does not support flag GLOB_BRACE.
   */
  public static function get($pattern, $flags = 0) {
    $files = glob($pattern, $flags);

    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
      $files = array_merge($files, CRM_Civicase_Helper_GlobRecursive::get($dir . '/' . basename($pattern), $flags));
    }

    return $files;
  }

  /**
   * Returns a list of files relatively to the given extension's path.
   *
   * @param string $extensionName
   *   The name of the extension.
   * @param string $pattern
   *   The glob pattern to use for searching files.
   *
   * @return array
   *   A list of file paths.
   */
  public static function getRelativeToExtension($extensionName, $pattern) {
    $extensionPath = CRM_Core_Resources::singleton()->getPath($extensionName);

    return self::getRelativeToPath($extensionPath, $pattern);
  }

  /**
   * Returns a list of files relatively to the given base path.
   *
   * @param string $basePath
   *   The base path.
   * @param string $pattern
   *   The glob pattern to use for searching files.
   *
   * @return array
   *   A list of file paths.
   */
  public static function getRelativeToPath($basePath, $pattern) {
    $files = self::get($basePath . DIRECTORY_SEPARATOR . $pattern);

    return array_map(function ($file) use ($basePath) {
      return str_replace($basePath . DIRECTORY_SEPARATOR, '', $file);
    }, $files);
  }

}
