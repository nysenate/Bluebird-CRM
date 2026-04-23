<?php
declare(strict_types = 1);

/**
 * Utility methods for the NYSS Print Import Legacy extension.
 *
 * Refactored from modules/nyss_io/nyss_lib.php.
 */
class CRM_NYSS_PrintImport_Utils {

  public static bool $debug = FALSE;

  /** @var array Cache for getCiviOptions() results, keyed by "class.field". */
  private static array $optionsCache = [];
  /**
   * Get a mysqli connection from a bbcfg array.
   *
   * @param array $bbcfg
   *   Configuration array with keys: db.host, db.user, db.pass, civicrm_db_name.
   *
   * @return \mysqli
   */
  public static function getConnection(array $bbcfg): \mysqli {
    $conn = mysqli_connect(
      $bbcfg['db.host'],
      $bbcfg['db.user'],
      $bbcfg['db.pass'],
      $bbcfg['civicrm_db_name']
    );

    if (mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
      exit();
    }

    return $conn;
  }

  /**
   * Output a debug/error/info message, optionally to screen.
   *
   * @param string $type
   *   One of 'debug', 'error', or any other string for general output.
   * @param mixed $v
   *   The value to output.
   * @param bool $toscreen
   *   Whether to echo the value to screen.
   * @param int $ioline
   *   Current line number being processed.
   * @param int $iototallines
   *   Total number of lines being processed.
   */
  public static function out(string $type, $v, bool $toscreen = FALSE, int $ioline = 0, int $iototallines = 0): void {
    if (!empty($v) && (($type == 'debug' && self::$debug) || $type != 'debug')) {
      $v = print_r($v, TRUE);

      if ($type == 'error') {
        error_log($v . " (line: $ioline)");
      }

      if ($toscreen) {
        echo "<pre>$v (line: $ioline of $iototallines memory: " . (round(memory_get_usage() / 1048576, 4)) . " MB)</pre>\n";
        flush();
        ob_flush();
      }
      elseif ($type == 'debug' && self::$debug) {
        CRM_Core_Error::debug_var("$type", $v, TRUE, TRUE, 'nyssio');
      }
    }
  }

  /**
   * Strip leading, trailing, and extra internal whitespace from a string.
   *
   * Replicates CRM_Utils_String::stripSpaces.
   *
   * @param string $s
   *
   * @return string
   */
  public static function stripSpaces(string $s): string {
    if (empty($s)) {
      return $s;
    }

    $pat = [
      "/^\s+/",
      "/\s{2,}/",
      "/\s+$/",
    ];

    $rep = [
      "",
      " ",
      "",
    ];

    return preg_replace($pat, $rep, $s);
  }


  /**
   * Flush output buffers to the browser.
   *
   * Browsers have changed how they support flushing content to screen;
   * this calls all necessary PHP flushing actions.
   */
  public static function flush(): void {
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
  }

  /**
   * Flush accumulated bulk-insert buffers for leaf tables.
   *
   * Called just before each transaction COMMIT so all buffered rows are
   * written within the same transaction as the civicrm_contact/address inserts
   * they depend on. Rows are grouped by column signature so every row in a
   * given multi-row INSERT statement has identical columns.
   *
   * @param array $insertBuffer
   *   Buffer of rows keyed by table name and column-signature group.
   *   Passed by reference and cleared on completion.
   * @param \mysqli $conn
   *   Active mysqli connection.
   */
  public static function flushInsertBuffer(array &$insertBuffer, \mysqli $conn): void {
    if (empty($insertBuffer)) {
      return;
    }

    foreach ($insertBuffer as $tbl => $groups) {
      foreach ($groups as $group) {
        if (empty($group['rows'])) {
          continue;
        }
        $sql = "INSERT INTO {$tbl} ({$group['columns']}) VALUES " . implode(',', $group['rows']);
        mysqli_query($conn, $sql) or die(mysqli_error($conn));
        self::out('debug', "bulk inserted " . count($group['rows']) . " rows into {$tbl}", FALSE);
      }
    }

    $insertBuffer = [];
  }

    /**
     * Retrieve a list of entity/field specific options via APIv4
     *
     * @param string $class
     *   The API Entity class, eg. '\Civi\Api4\Contact'
     * @param string $field
     *   The Entity field associated with the list of options, eg. 'gender_id'
     */
  public static function getCiviOptions(string $class, string $field): array {
    $cacheKey = $class . '.' . $field;

    if (isset(self::$optionsCache[$cacheKey])) {
      return self::$optionsCache[$cacheKey];
    }

    if (!method_exists($class, 'getFields')) {
      return self::$optionsCache[$cacheKey] = [];
    }

    $fields = $class::getFields(FALSE)
      ->addWhere('name', '=', $field)
      ->setLoadOptions(TRUE)
      ->execute()
      ->first();

    return self::$optionsCache[$cacheKey] = $fields['options'] ?? [];
  }

  /**
   * Log the memory footprint of each variable in the provided array.
   *
   * Serializes each value to measure its approximate memory cost and emits
   * the results via out(). Intended for debug/diagnostic use only.
   *
   * @param array $vars
   *   Associative array of variable names to values, typically from
   *   get_defined_vars() called in the caller's scope.
   */
  public static function logVarMemory(array $vars): void {
    self::out('debug', 'generating details about variables', TRUE);
    $var_details = [];
    foreach ($vars as $name => $value) {
      $start = memory_get_usage();
      unserialize(serialize($value));
      $var_details[$name] = memory_get_usage() - $start;
    }
    self::out('debug', $var_details, TRUE);
  }

    /**
     * Convert a PHP memory_limit string (e.g. "256M") to a human-readable size.
     *
     * @param int $size
     *   Size in bytes.
     *
     * @return string
     */
    public static function convertSize(int $size): string {
        $unit = ['b','kb','mb','gb','tb','pb'];
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

}
