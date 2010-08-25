<?

function markTime($id='default') {

	global $rayTimeMarker;

	$rayTimeMarker[$id] = microtime(1);
}

function getElapsedDescription($id='default') {

        global $rayTimeMarker;
	$elapsed_time = microtime(1)-$rayTimeMarker[$id];
	echo "\n\nelapsed time = $elapsed_time sec\n\n";
}

function getElapsed($id='default') {
	
	global $rayTimeMarker;
        return microtime(1)-$rayTimeMarker[$id];
}

function cLog($num=0, $type='notice', $message='', $debug=false) {

	if (is_object($message)) $message = print_r($message,true);

	if (RAYDEBUG || $debug || $type!='debug') print($num.' ['.$type.'] '.date("Y-m-d H:i:s")." ".$message."\n");
	
}

function fputcsv2 ($fh, array $fields, $delimiter = ',', $enclosure = '"', $mysql_null = false, $blank_as_null = false) {

    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
        if ($mysql_null && ($field === null || ($blank_as_null && strlen($field)==0))) {
            $output[] = 'NULL';
            continue;
        }

        $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
            $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
        ) : $field;
    }
    fwrite($fh, join($delimiter, $output) . "\n");
}

function getLine($inFile) {

        global $rayGetLineFile;

        //open or reopen the file so it can continually find data

        if (!isset($rayGetLineFile[$inFile]) || $rayGetLineFile[$inFile]==null) {

                try {

                        cLog(0,'info',"opening file $inFile.");

                        @fclose($rayGetLineFile[$inFile]);
                        $rayGetLineFile[$inFile] = fopen($inFile, "r");

                } catch(Exception $e) {

                        echo "file $inFile not found\n.";
                };
        }

        if ($rayGetLineFile[$inFile]===null || feof($rayGetLineFile[$inFile])) {
		unset($rayGetLineFile[$inFile]);
		return false;
	}

        return fgets($rayGetLineFile[$inFile]); //fgets reads one line at a time
}

function getLineAsArray($inFile, $delim=',') {

	$line = getLine($inFile);
	
	if ($line) return explode($delim,$line);
	else return false;
}

function confirmCheck($fn,$description = '') {

        if (file_exists(RAYTMP.$fn)) {
                unlink(RAYTMP.$fn);
                return true;
        } else {
                echo "please confirm you want to \"{$fn}\" {$description}. by running the exact same command again.\n";
                runCmd("touch ".RAYTMP.$fn);
                return false;
        }
}

function runCmd($cmd, $description=null, $debug=false, &$aOut=null) {

        $aOut=array();
        if ($description!=null) print($description."\n");

        if (RAYDEBUG || $debug) print($cmd);

        exec($cmd,&$aOut);

        //results
        if (RAYDEBUG | debug) {
                print("\n");
                print(implode("\n",$aOut));
                print("\n");
        }
}

function countFileLines($file) {

	$handle = fopen($file, "r");

	if (!$handle) return false;

	while(!feof($handle)){

		fgets($handle);
		$lineCount++;
	}

	return $lineCount;

}

function prettyFromSeconds($sec) {

	$val = number_format($sec/60/60/24,2); //days
	if ($val>=1) return "$val days";

        $val = number_format($sec/60/60,2); //hours
        if ($val>=1) return "$val hours";

        $val = number_format($sec/60,2); //minutes
        if ($val>=1) return "$val minutes";
        
	return "$sec seconds";
}

function writeToFile($fhout, $aOut, $done=1, $totalNum=2) {

        global $idOffset;
        global $getLineFile;

        //remove the offset so the numbers match
        $done=$done-$idOffset;

        foreach($aOut as $id=>$field) {
                //$aOut[$id] = str_replace("\\","\\\\",$aOut[$id]);
                $aOut[$id] = str_replace("'","",$aOut[$id]);
                $aOut[$id] = str_replace("\"","",$aOut[$id]);
        }

        fputcsv2($fhout, $aOut,"\t",'',true,true);

        if ($done%1000==0) { echo "wrote $done lines.\n"; flush();ob_flush();}

        if ($done>=$totalNum) {

                echo "processed total of $done lines.\n";
                echo "closing file.\n";
                fclose($fhout);
                return false;
        }

        return true;
}
//OMIS date parser
function formatDate($str, $forceMillenium=null) {

	//get rid of spaces
	$str = trim($str);

	//add a leading zero in case it is missing
	if (strlen($str)==5) $str = '0'.$str;

	//return if it's the wrong length
        if (strlen($str)!=6 && strlen($str)!=8) return 'NULL';

	if (strlen($str)==6) {

		if (is_numeric($forceMillenium)) {

			$str=substr($str,0,4).$forceMillenium.substr($str,4,2);

		} else if (substr($str,4,2) <= substr(date('Y'),2,2)) {
		
			$str = substr($str,0,4).'20'.substr($str,4,2); 

		} else {

			$str =  substr($str,0,4).'19'.substr($str,4,2);
		}
	}

	//if the date doesn't look good now, just quit.
	if (strlen($str)!=8) return 'NULL';

	$str = substr($str,4,4).'-'.substr($str,0,2).'-'.substr($str,2,2);

	$time=strToTime($str);

	//if not parseable, assume 19 and return the string
	if ($time==0) return $str;
	else return date('Y-m-d', $time);

#if ($str=='1950-02-15') echo date_parse($str);
#	return date('y-m-d',date_parse($str));
	#else return 'NULL';
}

function cleanData($str) {

	$str = trim($str);
	
	return strlen($str)>0 ? $str : 'NULL';
} 
?>
