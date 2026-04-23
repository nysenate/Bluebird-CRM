<?php
declare(strict_types = 1);

/**
 * CSV file reader for the NYSS Print Import Legacy extension.
 *
 * Refactored from NYSS_IOFileObject in modules/nyss_io/nyss_lib.php.
 */
class CRM_NYSS_PrintImport_IOFileObject {

  public string $filepath;
  public string $type = ',';
  public int $hasHeader = 1;
  public int $linecount = 0;
  public array $header = [];

  private $file_handle;

  public function __construct(string $filepath, string $type = ',') {
    $this->type = $type;
    $this->filepath = $filepath;

    ini_set('auto_detect_line_endings', true);

    $this->file_handle = fopen($filepath, 'r');

    $line = $this->getLineAsArray();

    if (!is_array($line)) {
      CRM_Core_Session::setStatus(
        ts('Bad file. Either the file is not a csv or has the wrong number of headers.'),
        ts('Error'),
        'error'
      );
      return;
    }

    $this->header = [];
    foreach ($line as $key => $val) {
      $this->header[strtolower($val)] = $key;
    }
  }

  public function countLines(): int {
    $ns = $rs = 0;
    $fh = fopen($this->filepath, 'r');

    while ($chunk = fread($fh, 1024000)) {
      $ns += substr_count($chunk, "\n");
      $rs += substr_count($chunk, "\r");
    }

    fclose($fh);

    return $rs > $ns ? $rs : $ns;
  }

  /**
   * Return the next data row as a column-name-keyed array, or FALSE at EOF.
   *
   * @return array|false
   */
  public function getLine() {
    if ($line = $this->getLineAsArray()) {
      ++$this->linecount;
      $aLine = [];
      foreach ($this->header as $colname => $colnum) {
        $aLine[$colname] = $line[$colnum];
      }
      return $aLine;
    }

    return FALSE;
  }

  /**
   * Return the next raw line split on the delimiter, or FALSE at EOF.
   *
   * @return array|false
   */
  public function getLineAsArray() {
    $line = fgets($this->file_handle);
    if ($line) {
      return explode($this->type, rtrim($line));
    }

    return FALSE;
  }

}
