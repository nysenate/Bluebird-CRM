<?php

function runCmd($cmd, $description = null, $debug = false, &$aOut = null)
{
  global $SC;

  $aOut = array();
  if ($description != null) cLog(0, 'INFO', $description);

  if ($SC['debug'] || $debug) cLog(0, 'INFO', $cmd); 

  if (!$SC['noExec']) exec($cmd, $aOut);

  //results
  if ($SC['debug'] || debug) {
    print(implode("\n", $aOut));
  }
} // runCmd()



function cLog($num=0, $type='notice', $message='', $debug=false)
{
  if (is_object($message)) $message = print_r($message, true);
  if ($debug || $type!='debug') print($num.' ['.$type.'] '.date("Y-m-d H:i:s")." ".$message."\n");
} // cLog()



function confirmCheck($fn)
{
  global $SC;
  if ($SC['noExec']) return true;

  if (file_exists($SC['tmp'].$fn)) {
    unlink($SC['tmp'].$fn);
    return true;
  }
  else {
    echo "\n\n-------------------------------------------------------------------------------------------------------\n";
    echo "please confirm you want to \"{$fn}\" by running the exact same command again.\n";
    echo "-------------------------------------------------------------------------------------------------------\n\n\n";
    runCmd("touch {$SC['tmp']}{$fn}");
    return false;
  }
} // confirmCheck()



function getLine($inFile)
{
  global $rayGetLineFile;

  //open or reopen the file so it can continually find data
  if (!isset($rayGetLineFile[$inFile]) || $rayGetLineFile[$inFile]==null || feof($rayGetLineFile[$inFile])) {
    try {
      cLog(0,'info',"opening file $inFile.");
      @fclose($rayGetLineFile[$inFile]);
      $rayGetLineFile[$inFile] = fopen($inFile, "r");
    }
    catch (Exception $e) {
      echo "file $inFile not found\n.";
    }
  }

  if ($rayGetLineFile[$inFile]===null) {
    unset($rayGetLineFile[$inFile]);
    return false;
  }

  return fgets($rayGetLineFile[$inFile]); //fgets reads one line at a time
} // getLine()



function getLineAsArray($inFile, $delim = ',')
{
  $line = getLine($inFile);
  if ($line) return csv_string_to_array($line);
  else return false;
} // getLineAsArray()



function csv_string_to_array($str)
{
  $expr = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
  $results = preg_split($expr, trim($str));
  return preg_replace("/^\"(.*)\"$/","$1", $results);
} // csv_string_to_array()



function fputcsv2($fh, array $fields, $delimiter = ',', $enclosure = '"',
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

    $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? ( $enclosure.str_replace($enclosure, $enclosure.$enclosure, $field).$enclosure ) : $field;
  }

  fwrite($fh, join($delimiter, $output) . "\n");
} // fputcsv2()

?>
