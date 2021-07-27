<?php
namespace CCL;

/**
 * Quick-and-dirty task library
 */
class Tasks {

  /**
   * Generate SCSS files.
   *
   * @see \CCL\Tasks\Scss::compile
   */
  public static function scss(array $tasks) {
    \CCL\Tasks\Scss::compile($tasks);
  }

  /**
   * Generate PHP files using JSON templates.
   *
   * @see \CCL\Tasks\Template::compile
   */
  public static function template(array $tasks) {
    \CCL\Tasks\Template::compile($tasks);
  }

}
