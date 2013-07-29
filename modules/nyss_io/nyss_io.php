<?php

function nyss_iodbDateFromStr($str) {
	return nyss_iodbDate(nyss_iotimeFromStr($str));
}

function nyss_iodbDate($t) {
	if (strlen($t)==0) return null;

	return date('Y-m-d H:i:s',$t);
}

function nyss_ioshortDate($t) {
	$d=date('Y-m-d',$t);

	return $d;
}

function nyss_ioout($type,$v,$toscreen=false) {
	global $nyss_ioline, $nyss_iototalLines;

	if(!empty($v) && (($type=='debug' && NYSSDEBUG) || $type!='debug')) {
		$v = print_r($v,true);

		error_log($v. " (line: $nyss_ioline)");

		if ($toscreen) {
			echo "<pre>$v (line: $nyss_ioline of $nyss_iototalLines)</pre>";
			flush();
			ob_flush();
		}
	}
}

function nyss_ioaddMsg($type,$msg) {
	global $nyss_ioimportMsg;
	global $nyss_ioline;

	if (is_numeric($nyss_ioline)) $msg." (line $line)";

	//add to mem array for use later. might use a lot of memory.
	$nyss_ioimportMsg[$type][] = $msg;

	//set the msg for output too
	nyss_ioout('status',$msg,true);

	//send to drupal
	drupal_set_message($msg,$status, false);
}

//replicated from CRM_Utils_String::stripSpaces
//in order to process off c3.2.x core base
function nyss_stripSpaces( $string ) {
  if ( empty($string) ) return $string;

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
        
	return preg_replace( $pat, $rep, $string );
}

function convertLowerCase ( $string ) {
	return strtolower($string);
}

/*
 * browsers have changed how they support flushing content to screen
 * this function calls the necessary PHP flushing actions
 */
function nyss_flush() {
  ob_end_flush();
  ob_flush();
  flush();
  ob_start();
}
