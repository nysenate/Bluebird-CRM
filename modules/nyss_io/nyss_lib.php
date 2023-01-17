<?php

/**
 * get a mysqli connection from the db string
 */
function nyss_getConnection($bbcfg) {
  //Civi::log()->debug('getConnection', array('bbcfg' => $bbcfg));

  $conn = mysqli_connect($bbcfg['db.host'], $bbcfg['db.user'], $bbcfg['db.pass'], $bbcfg['civicrm_db_name']);

  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
  }
  else {
    return $conn;
  }
}

function nyss_out($type, $v, $toscreen = false)
{
  global $nyss_ioline, $nyss_iototallines, $logFile;

  if (!empty($v) && (($type == 'debug' && NYSSIODEBUG) || $type !='debug')) {
    $v = print_r($v, true);

    if ($type == 'error') {
      error_log($v. " (line: $nyss_ioline)");
    }

    if ($toscreen) {
      echo "<pre>$v (line: $nyss_ioline of $nyss_iototallines memory: ".(round(memory_get_usage()/1048576,4))." MB".")</pre>";
      flush();
      ob_flush();
    }
    elseif ($type == 'debug' && defined('NYSSIODEBUG') && NYSSIODEBUG) {
      fwrite($logFile, $v."\n\n");
    }

    unset($v);
  }
} // nyss_out()


//replicated from CRM_Utils_String::stripSpaces
//in order to process off c3.2.x core base
function nyss_stripSpaces($s)
{
  if (empty($s)) {
    return $s;
  }

  $pat = array(
    0 => "/^\s+/",
    1 =>  "/\s{2,}/",
    2 => "/\s+\$/"
  );

  $rep = array(
    0 => "",
    1 => " ",
    2 => ""
  );

  return preg_replace($pat, $rep, $s);
} // nyss_stripSpaces()


function convertLowerCase($s)
{
  return strtolower($s);
} // convertLowerCase()


/*
 * browsers have changed how they support flushing content to screen
 * this function calls the necessary PHP flushing actions
 */
function nyss_flush()
{
  ob_end_flush();
  ob_flush();
  flush();
  ob_start();
} // nyss_flush()


class NYSS_IOFileObject
{
  public $filepath;
  public $type = ',';
  public $hasHeader = 1;
  public $linecount = 0;
  public $header = array();

  private $file_handle;

  function __construct($filepath, $type = ',')
  {
    $this->type = $type;
    $this->filepath = $filepath;

    ini_set('auto_detect_line_endings', true);

    // open the file for reading
    $this->file_handle = fopen($filepath, 'r');

    $line = $this->getLineAsArray();

    if (!is_array($line)) {
      //set error message and exit
      drupal_set_message('Bad file. Either the file is not a csv or has the wrong number of headers.', 'error');
      return false;
    }

    //get the header row
    $this->header = array();
    foreach ($line as $key => $val) {
      $this->header[strtolower($val)] = $key;
    }
  } // __construct()


  public function countLines()
  {
    $ns = $rs = 0;
    $fh = fopen($this->filepath, 'r');

    while ($chunk = fread($fh, 1024000)) {
      $ns += substr_count($chunk, "\n");
      $rs += substr_count($chunk, "\r");
    }

    if ($rs > $ns) {
      $ns = $rs;
    }
    return $rs;
  } // countLines()


  public function getLine()
  {
    if ($line = $this->getLineAsArray()) {
      ++$this->linecount;
      $aLine = array();
      foreach ($this->header as $colname => $colnum) {
        $aLine[$colname] = $line[$colnum];
      }
      return $aLine;
    }
    else {
      return false;
    }
  } // getLine()


  function getLineAsArray()
  {
    $line = fgets($this->file_handle);
    if ($line) {
      return explode($this->type, rtrim($line));
    }
    else {
      return false;
    }
  } // getLineAsArray()
} // NYSS_IOFileObject
