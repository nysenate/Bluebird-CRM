<?php

/**
 * CRM_Civicase_Helper_GlobRecursive class.
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

}
