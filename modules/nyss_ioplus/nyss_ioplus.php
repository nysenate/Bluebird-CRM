<?php

function nyss_ioplusdbDateFromStr($str) {

	return nyss_ioplusdbDate(nyss_ioplustimeFromStr($str));
}

function nyss_ioplusdbDate($t) {

	if (strlen($t)==0) return null;

	return date('Y-m-d H:i:s',$t);
}

function nyss_ioplusshortDate($t) {


	$d=date('Y-m-d',$t);

	return $d;
}

function nyss_ioplusout($type,$v,$toscreen=false) {

	global $nyss_ioplusline, $nyss_ioplustotalLines;

	if(!empty($v) && (($type=='debug' && NYSSDEBUG) || $type!='debug')) {

		$v = print_r($v,true);

		error_log($v. " (line: $nyss_ioplusline)");
		if ($toscreen) {

			echo "<pre>$v (line: $nyss_ioplusline of $nyss_ioplustotalLines)</pre>";
			flush();
			ob_flush();
		}
	}
}

function nyss_ioplusaddMsg($type,$msg) {

	global $nyss_ioplusimportMsg;
	global $nyss_ioplusline;

	if (is_numeric($nyss_ioplusline)) $msg." (line $line)";

	//add to mem array for use later. might use a lot of memory.
	$nyss_ioplusimportMsg[$type][] = $msg;

	//set the msg for output too
	nyss_ioplusout('status',$msg,true);

	//send to drupal
	drupal_set_message($msg,$status, false);

}

//replicated from CRM_Utils_String::stripSpaces
//in order to process off c3.2.x core base
function nyss_ioplus_stripSpaces( $string ) {
    if ( empty($string) ) return $string;
        
    $pat = array( 0 => "/^\s+/",
                  1 =>  "/\s{2,}/", 
                  2 => "/\s+\$/" );
        
    $rep = array( 0 => "",
                  1 => " ",
                  2 => "" );
        
	return preg_replace( $pat, $rep, $string );
}
