<?php

define('BLUEBIRD_BASEDIR', realpath(dirname(__FILE__).'/../../../'));
define('RAYCIVIPATH', BLUEBIRD_BASEDIR.'/modules/civicrm/');
define('RAYDEBUG', true);
define('RAYIMPORTDIR', BLUEBIRD_BASEDIR.'/importData/');
define('RAYROOTDOMAIN', ".crm.nysenate.gov");
define('RAYROOTDIR', BLUEBIRD_BASEDIR."/drupal/");
define('RAYTMP', '/tmp/');


function markTime($id='default')
{
  global $rayTimeMarker;
  $rayTimeMarker[$id] = microtime(1);
} // markTime()



function getElapsedDescription($id='default')
{
  global $rayTimeMarker;
  $elapsed_time = microtime(1)-$rayTimeMarker[$id];
  echo "\n\nelapsed time = $elapsed_time sec\n\n";
} // getElapsedDescription()



function getElapsed($id='default')
{
  global $rayTimeMarker;
  return microtime(1)-$rayTimeMarker[$id];
} // getElapsed()



function cLog($num=0, $type='notice', $message='', $debug=false)
{
  if (is_object($message)) $message = print_r($message,true);
  if (RAYDEBUG || $debug || $type!='debug') print($num.' ['.$type.'] '.date("Y-m-d H:i:s")." ".$message."\n");
} // cLog()



function fputcsv2 ($fh, array $fields, $delimiter = ',', $enclosure = '"',
                   $mysql_null = false, $blank_as_null = false)
{
  $delimiter_esc = preg_quote($delimiter, '/');
  $enclosure_esc = preg_quote($enclosure, '/');
  $output = array();

  foreach ($fields as $field) {
    if ($mysql_null && ($field === null || ($blank_as_null && strlen($field)==0))) {
      $output[] = '\N';
      continue;
    }

    $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? ($enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure) : $field;
  }

  fwrite($fh, implode($delimiter, $output) . "\n");
} // fputcsv2()



function getLine($inFile)
{
  global $rayGetLineFile;

  //open or reopen the file so it can continually find data
  if (!isset($rayGetLineFile[$inFile]) || $rayGetLineFile[$inFile]==null) {
    try {
      cLog(0,'info',"opening file $inFile.");
      @fclose($rayGetLineFile[$inFile]);
      $rayGetLineFile[$inFile] = fopen($inFile, "r");
    } catch(Exception $e) {
      echo "file $inFile not found\n.";
    }
  }

  if ($rayGetLineFile[$inFile]===null || feof($rayGetLineFile[$inFile])) {
    unset($rayGetLineFile[$inFile]);
    return false;
  }

  //fgets reads one line at a time
  $line = fgets($rayGetLineFile[$inFile]);
  if ($line) {
    $line = rtrim($line);
  }
  return $line;
} // getLine()



function getLineAsArray($inFile, $delim = ',')
{
  $line = getLine($inFile);
  if ($line) {
    return explode($delim, $line);
  }
  else {
    return false;
  }
} // getLineAsArray()


function getLineAsAssocArray($inFile, $delim = ',', $fld_names)
{
  $line = getLine($inFile);
  if ($line) {
    remove_omis_line_terminator($line);
    $res = array();
    $fidx = 0;
    $lidx = 0;
    $didx = strpos($line, $delim);
    while ($didx !== false) {
      if ($fidx >= count($fld_names)) {
        echo "Warning: Too many field values in line: [$line]\n";
        break;
      }
      $len = $didx - $lidx;
      $res[$fld_names[$fidx++]] = fix_value(substr($line, $lidx, $len));
      $lidx = $didx + 1;
      $didx = strpos($line, $delim, $lidx);
    }
    // Handle the last field.
    if ($fidx < count($fld_names)) {
      $res[$fld_names[$fidx++]] = fix_value(substr($line, $lidx));
    }
    return $res;
  }
  else {
    return false;
  }
} // getLineAsAssocArray()



// Remove the last two characters of an OMIS export line.
function remove_omis_line_terminator(&$str)
{
  if (substr($str, -2) == "~|") {
    $str = substr($str, 0, -2);
  }
} // remove_omis_line_terminator()



// Handle backslashes within field values.
function fix_value($str)
{
  if (strpos($str, '\\') !== false) {
    // Remove trailing backslashes.
    while (substr($str, -1) == '\\') {
      $str = substr($str, 0, -1);
    }
    // Replace any remaining backslashes with double-backslashes.
    return str_replace('\\', '\\\\', $str);
  }
  else {
    return $str;
  }
} // fix_value()



function confirmCheck($fn, $description = '')
{
  if (file_exists(RAYTMP.$fn)) {
    unlink(RAYTMP.$fn);
    return true;
  } else {
    echo "please confirm you want to \"{$fn}\" {$description}. by running the exact same command again.\n";
    runCmd("touch ".RAYTMP.$fn);
    return false;
  }
} // confirmCheck()



function runCmd($cmd, $description=null, $debug=false, &$aOut=null)
{
  $aOut=array();
  if ($description!=null) print($description."\n");

  if (RAYDEBUG || $debug) print($cmd);

  exec($cmd, $aOut);

  //results
  if (RAYDEBUG | debug) {
    print("\n");
    print(implode("\n",$aOut));
    print("\n");
  }
} // runCmd()



function countFileLines($file)
{
  $handle = fopen($file, "r");
  if (!$handle) return false;

  while(!feof($handle)){
    fgets($handle);
    $lineCount++;
  }
  return $lineCount;
} // countfileLines()



function prettyFromSeconds($sec)
{
  $val = number_format($sec/60/60/24,2); //days
  if ($val>=1) return "$val days";

  $val = number_format($sec/60/60,2); //hours
  if ($val>=1) return "$val hours";

  $val = number_format($sec/60,2); //minutes
  if ($val>=1) return "$val minutes";
        
  return "$sec seconds";
} // prettyFromSeconds()



function writeToFile($fhout, $aOut, $done=1, $totalNum=2)
{
  global $idOffset;

  //remove the offset so the numbers match
  $done = $done - $idOffset;

  // Remove all single and double quotes from all field values.
  //$aOut = str_replace(array("'", "\""), "", $aOut);

  fputcsv2($fhout, $aOut, "\t", '', true, false);

  if ($done % 1000 == 0) {
    echo "wrote $done lines.\n";
    flush();
    ob_flush();
  }

  if ($done >= $totalNum) {
    echo "processed total of $done lines.\n";
    echo "closing file.\n";
    fclose($fhout);
    return false;
  }

  return true;
} // writeToFile()



//OMIS date parser
function formatDate($str, $forceMillenium = null)
{
  //get rid of spaces
  $str = trim($str);

  //add a leading zero in case it is missing
  if (strlen($str) == 5) {
    $str = '0'.$str;
  }

  //return if it's the wrong length
  if (strlen($str) != 6 && strlen($str) != 8) {
    return null;
  }

  if (strlen($str) == 6) {
    // reformat from MMDDYY to MMDDYYYY
    if (is_numeric($forceMillenium)) {
      $str = substr($str,0,4).$forceMillenium.substr($str,4,2);
    } else if (substr($str,4,2) <= substr(date('Y'),2,2)) {
      $str = substr($str,0,4).'20'.substr($str,4,2); 
    } else {
      $str = substr($str,0,4).'19'.substr($str,4,2);
    }
  }

  //if the date doesn't look good now, just quit.
  if (strlen($str) != 8) {
    return null;
  }

  // reformat from MMDDYYYY to YYYY-MM-DD
  $str = substr($str,4,4).'-'.substr($str,0,2).'-'.substr($str,2,2);

  $time = strtotime($str);

  //if not parseable, assume 19 and return the string
  if ($time == 0) {
    return $str;
  }
  else {
    return date('Y-m-d', $time);
  }
} // formatDate()



function formatTime($str)
{
  $str = trim($str);
  if (preg_match('/^([01]?[0-9]|2[0-3])(:[0-5][0-9]){2}$/', $str)) {
    return $str;
  }
  else {
    return "00:00:00";
  }
} // formatTime()



function cleanData($str)
{
  $str = trim($str);
  if ($str == 'NULL') $str = '';
  $str = str_replace('array', '', $str);
  $str = str_replace('null', '', $str);
  return strlen($str) > 0 ? $str : '';
} // cleanData()
?>
