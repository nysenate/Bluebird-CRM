<?php
/**
 * Add a few 4.3.0 functions to old versions of PHP
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage io
 * @version $Id$
 */
 
if (!function_exists("ob_get_clean")) {
   function ob_get_clean() {
	   $ob_contents = ob_get_contents();
	   ob_end_clean();
	   return $ob_contents;
   }
}



if(!function_exists("file_put_contents")) {
	if (!defined('FILE_APPEND')) {
		define('FILE_APPEND', 8);
	}
	function file_put_contents($file, $string, $modifiers = NULL) {
		$mode = $modifiers == FILE_APPEND ? 'a' : 'w';
		$f=fopen($file, $mode);
		$result = fwrite($f, $string);
		fclose($f);
		return $result;
	}
}

?>