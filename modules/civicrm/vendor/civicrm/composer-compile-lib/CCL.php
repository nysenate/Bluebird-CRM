<?php
// AUTO-GENERATED VIA /home/jenkins/bknix-min/build/cividist/src/vendor/civicrm/composer-compile-lib/src/StubsTpl.php
// If this file somehow becomes invalid (eg when patching CCL), you may safely delete and re-run install.
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CCL {

  /**
   * @return Symfony\Component\Filesystem\Filesystem
   */
  public static function _sym() {
    static $singleton = NULL;
    $singleton = $singleton ?: new \Symfony\Component\Filesystem\Filesystem();
    return $singleton;
  }

  /**
   * @return CCL\Functions
   */
  public static function _ccl() {
    static $singleton = NULL;
    $singleton = $singleton ?: new \CCL\Functions();
    return $singleton;
  }

  /**
   * Copies a file.
   *
   * If the target file is older than the origin file, it's always overwritten.
   * If the target file is newer, it is overwritten only when the
   * $overwriteNewerFiles option is set to true.
   *
   * @throws FileNotFoundException When originFile doesn't exist
   * @throws IOException           When copy fails
   */
  public static function copy($originFile, $targetFile, bool $overwriteNewerFiles = TRUE) {
    self::_sym()->copy($originFile, $targetFile, $overwriteNewerFiles);
  }

  /**
   * Creates a directory recursively.
   *
   * @param string|iterable $dirs The directory path
   *
   * @throws IOException On any directory creation failure
   */
  public static function mkdir($dirs, $mode = 511) {
    self::_sym()->mkdir($dirs, $mode);
  }

  /**
   * Checks the existence of files or directories.
   *
   * @param string|iterable $files A filename, an array of files, or a \Traversable instance to check
   *
   * @return bool
   */
  public static function exists($files) {
    return self::_sym()->exists($files);
  }

  /**
   * Sets access and modification time of file.
   *
   * @param string|iterable $files A filename, an array of files, or a \Traversable instance to create
   * @param int|null        $time  The touch time as a Unix timestamp, if not supplied the current system time is used
   * @param int|null        $atime The access time as a Unix timestamp, if not supplied the current system time is used
   *
   * @throws IOException When touch fails
   */
  public static function touch($files, $time = NULL, $atime = NULL) {
    self::_sym()->touch($files, $time, $atime);
  }

  /**
   * Removes files or directories.
   *
   * @param string|iterable $files A filename, an array of files, or a \Traversable instance to remove
   *
   * @throws IOException When removal fails
   */
  public static function remove($files) {
    self::_sym()->remove($files);
  }

  /**
   * Change mode for an array of files or directories.
   *
   * @param string|iterable $files     A filename, an array of files, or a \Traversable instance to change mode
   * @param int             $mode      The new mode (octal)
   * @param int             $umask     The mode mask (octal)
   * @param bool            $recursive Whether change the mod recursively or not
   *
   * @throws IOException When the change fails
   */
  public static function chmod($files, $mode, $umask = 0, $recursive = FALSE) {
    self::_sym()->chmod($files, $mode, $umask, $recursive);
  }

  /**
   * Change the owner of an array of files or directories.
   *
   * @param string|iterable $files     A filename, an array of files, or a \Traversable instance to change owner
   * @param string|int      $user      A user name or number
   * @param bool            $recursive Whether change the owner recursively or not
   *
   * @throws IOException When the change fails
   */
  public static function chown($files, $user, $recursive = FALSE) {
    self::_sym()->chown($files, $user, $recursive);
  }

  /**
   * Change the group of an array of files or directories.
   *
   * @param string|iterable $files     A filename, an array of files, or a \Traversable instance to change group
   * @param string|int      $group     A group name or number
   * @param bool            $recursive Whether change the group recursively or not
   *
   * @throws IOException When the change fails
   */
  public static function chgrp($files, $group, $recursive = FALSE) {
    self::_sym()->chgrp($files, $group, $recursive);
  }

  /**
   * Renames a file or a directory.
   *
   * @throws IOException When target file or directory already exists
   * @throws IOException When origin cannot be renamed
   */
  public static function rename($origin, $target, $overwrite = FALSE) {
    self::_sym()->rename($origin, $target, $overwrite);
  }

  /**
   * Creates a symbolic link or copy a directory.
   *
   * @throws IOException When symlink fails
   */
  public static function symlink($originDir, $targetDir, $copyOnWindows = FALSE) {
    self::_sym()->symlink($originDir, $targetDir, $copyOnWindows);
  }

  /**
   * Creates a hard link, or several hard links to a file.
   *
   * @param string|string[] $targetFiles The target file(s)
   *
   * @throws FileNotFoundException When original file is missing or not a file
   * @throws IOException           When link fails, including if link already exists
   */
  public static function hardlink($originFile, $targetFiles) {
    self::_sym()->hardlink($originFile, $targetFiles);
  }

  /**
   * Resolves links in paths.
   *
   * With $canonicalize = false (default)
   *      - if $path does not exist or is not a link, returns null
   *      - if $path is a link, returns the next direct target of the link without considering the existence of the target
   *
   * With $canonicalize = true
   *      - if $path does not exist, returns null
   *      - if $path exists, returns its absolute fully resolved final version
   *
   * @return string|null
   */
  public static function readlink($path, $canonicalize = FALSE) {
    return self::_sym()->readlink($path, $canonicalize);
  }

  /**
   * Given an existing path, convert it to a path relative to a given starting path.
   *
   * @return string
   */
  public static function makePathRelative($endPath, $startPath) {
    return self::_sym()->makePathRelative($endPath, $startPath);
  }

  /**
   * Mirrors a directory to another.
   *
   * Copies files and directories from the origin directory into the target directory. By default:
   *
   *  - existing files in the target directory will be overwritten, except if they are newer (see the `override` option)
   *  - files in the target directory that do not exist in the source directory will not be deleted (see the `delete` option)
   *
   * @param \Traversable|null $iterator Iterator that filters which files and directories to copy, if null a recursive iterator is created
   * @param array             $options  An array of boolean options
   *                                    Valid options are:
   *                                    - $options['override'] If true, target files newer than origin files are overwritten (see copy(), defaults to false)
   *                                    - $options['copy_on_windows'] Whether to copy files instead of links on Windows (see symlink(), defaults to false)
   *                                    - $options['delete'] Whether to delete files that are not in the source directory (defaults to false)
   *
   * @throws IOException When file type is unknown
   */
  public static function mirror($originDir, $targetDir, $iterator = NULL, $options = []) {
    self::_sym()->mirror($originDir, $targetDir, $iterator, $options);
  }

  /**
   * Returns whether the file path is an absolute path.
   *
   * @return bool
   */
  public static function isAbsolutePath($file) {
    return self::_sym()->isAbsolutePath($file);
  }

  /**
   * Creates a temporary file with support for custom stream wrappers.
   *
   * @param string $prefix The prefix of the generated temporary filename
   *                       Note: Windows uses only the first three characters of prefix
   * @param string $suffix The suffix of the generated temporary filename
   *
   * @return string The new temporary filename (with path), or throw an exception on failure
   */
  public static function tempnam($dir, $prefix) {
    return self::_sym()->tempnam($dir, $prefix);
  }

  /**
   * Atomically dumps content into a file.
   *
   * @param string|resource $content The data to write into the file
   *
   * @throws IOException if the file cannot be written to
   */
  public static function dumpFile($filename, $content) {
    self::_sym()->dumpFile($filename, $content);
  }

  /**
   * Appends content to an existing file.
   *
   * @param string|resource $content The content to append
   * @param bool            $lock    Whether the file should be locked when writing to it
   *
   * @throws IOException If the file is not writable
   */
  public static function appendToFile($filename, $content) {
    self::_sym()->appendToFile($filename, $content);
  }

  /**
   * Assert that we are properly executing within the context of a compilation task.
   *
   * If this script tries to run in any other context, then you will get some
   * kind of error (e.g. class not found or RuntimeException).
   */
  public static function assertTask() {
    self::_ccl()->assertTask();
  }

  /**
   * Array-map function. Similar to array_map(), but tuned to key-value pairs.
   *
   * Example:
   *   $data = [100 => 'apple', 200 => 'banana'];
   *   $opposite = mapkv($data, function($k, $v){ return [-1 * $k => strtoupper($v)]; });
   *
   * This would return [-100 => 'APPLE', -200 => 'BANANA']
   *
   * By convention, mapping functions should return an 1-row array "[newKey => newValue]".
   *
   * Some unconventional forms are also defined:
   *  - Return empty array ==> Skip/omit the row
   *  - Return multiple items ==> Add all items to the result
   *  - Return an unkeyed (numeric) array ==> Discard original keys. Items are appended numerically (`$arr[] = $value`).
   *
   * @param array $array
   *   Values to iterate over
   * @param callable $func
   *   Callback function.
   *   function(scalar $key, mixed $value): array
   * @return array
   *   The filtered array.
   */
  public static function mapkv($array, $func) {
    return self::_ccl()->mapkv($array, $func);
  }

  /**
   * Map file-names.
   *
   * @param string $matchPat
   *   Ex: 'src/*.json'
   * @param string $outPat
   *   Ex: 'dest/#1.json'
   * @param bool $flip
   *   The orientation of the result map.
   *   If false, returned as "original => filtered".
   *   If true, returned as "filtered => original".
   * @return array
   *   List of files and the corresponding names.
   */
  public static function globMap($matchPat, $outPat, $flip = FALSE) {
    return self::_ccl()->globMap($matchPat, $outPat, $flip);
  }

     
  public static function chdir($directory) {
    self::_ccl()->chdir($directory);
  }

  /**
   * @param string|string[] $pats
   *   List of glob patterns.
   * @param null|int $flags
   * @return array
   *   List of matching files.
   */
  public static function glob($pats, $flags = NULL) {
    return self::_ccl()->glob($pats, $flags);
  }

  /**
   * Read a set of files and concatenate the results
   *
   * @param string|string[] $srcs
   *   Files to read. These may be globs.
   * @param string $newLine
   *   Whether to ensure that joined files have a newline separator.
   *   Ex: 'raw' (as-is), 'auto' (add if missing)
   * @return string
   *   The result of joining the files.
   */
  public static function cat($srcs, $newLine = 'auto') {
    return self::_ccl()->cat($srcs, $newLine);
  }

}
