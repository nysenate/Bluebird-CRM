<?php
namespace CCL;

class Functions {

  /**
   * Assert that we are properly executing within the context of a compilation task.
   *
   * If this script tries to run in any other context, then you will get some
   * kind of error (e.g. class not found or RuntimeException).
   */
  public function assertTask() {
    \Civi\CompilePlugin\Util\Script::assertTask();
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
  public function mapkv($array, $func) {
    $r = [];
    foreach ($array as $k => $v) {
      foreach ($func($k, $v) as $out_k => $out_v) {
        if (isset($r[$out_k])) {
          $r[] = $out_v;
        }
        else {
          $r[$out_k] = $out_v;
        }
      }
    }
    return $r;
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
  public function globMap($matchPat, $outPat, $flip = FALSE) {
    $inFiles = glob($matchPat);
    $regex = ';' . preg_quote($matchPat, ';') . ';';
    $regex = str_replace(preg_quote('*', ';'), '(.*)', $regex);
    $replacement = preg_replace(';#(\d+);', '\\' . '\\\1', $outPat);
    $outFiles = preg_replace($regex, $replacement, $inFiles);
    return $flip ? array_combine($outFiles, $inFiles) : array_combine($inFiles, $outFiles);
  }

  public function chdir($directory) {
    if (!\chdir($directory)) {
      throw new IOException("Failed to change directory ($directory)");
    }
  }

  /**
   * @param string|string[] $pats
   *   List of glob patterns.
   * @param null|int $flags
   * @return array
   *   List of matching files.
   */
  public function glob($pats, $flags = NULL) {
    $r = [];
    $pats = (array) $pats;
    foreach ($pats as $pat) {
      $r = array_unique(array_merge($r, (array) \glob($pat, $flags)));
    }
    sort($r);
    return $r;
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
  public function cat($srcs, $newLine = 'auto') {
    $buf = '';
    foreach (glob($srcs) as $file) {
      if (!is_readable($file)) {
        throw new \RuntimeException("Cannot read $file");
      }
      $buf .= file_get_contents($file);
      switch ($newLine) {
        case 'auto':
          if (substr($buf, -1) !== "\n") {
            $buf .= "\n";
          }
          break;

        case 'raw':
          // Don't
          break;
      }
    }
    return $buf;
  }

  ///**
  // * Atomically dumps content into a file.
  // *
  // * @param string $filename The file to be written to
  // * @param string $content  The data to write into the file
  // *
  // * @throws IOException if the file cannot be written to
  // */
  //public function write($file, $content) {
  //    \CCL\dumpFile($file, $content);
  //}

  ///**
  // * Copy file(s) to a destination.
  // *
  // * This does work with files or directories. However, if you wish to reference a directory, then
  // * it *must* end with a trailing slash. Ex:
  // *
  // * Copy "infile.txt" to "outfile.txt"
  // *   cp("infile.txt", "outfile.txt");
  // *
  // * Copy "myfile.txt" to "out-dir/myfile.txt"
  // *   cp("myfile.txt", "out-dir/");
  // *
  // * Recursively copy "in-dir/*" into "out-dir/"
  // *   cp("in-dir/*", "out-dir/");
  // *
  // * Recursively copy the whole "in-dir/" into "out-dir/deriv/"
  // *   cp("in-dir/", "out-dir/deriv/");
  // *
  // * @param string $srcs
  // * @param string $dest
  // */
  //public function cp($srcs, $dest) {
  //    $destType = substr($dest, -1) === '/' ? 'D' : 'F';
  //
  //    foreach (glob($srcs, MARK) as $src) {
  //        $srcType = substr($src, -1) === '/' ? 'D' : 'F';
  //        switch ($srcType . $destType) {
  //        }
  //    }
  //
  //}

}
