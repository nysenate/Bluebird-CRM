<?php
/**
 * The Exception class is the internal static class used to output user defined
 * exceptions to the output stream.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage exception
 * @author Justin Watkins Original Design 
 * @version $Id: AMFException.php,v 1.2 2005/04/02 18:37:23 pmineault Exp $
 */

/**
 * Linked classes
 */
require_once(AMFPHP_BASE . "app/Constants.php");

/**
 * Remove the html formatting of the error messages so they can be easily formatted
 * inside flash.
 */
@ini_set("html_errors", 0);

class AMFException {
	/**
	 * Constructor for the Exception class. This is how you build a new
	 * error instance.
	 * 
	 * @param string $code The code string to return to the flash client :: THIS SHOULD PROBABLY BE SET AUTOMATICALLY ::
	 * @param string $description A short reason why the error occured
	 * @param string $file The file name that the error occured
	 * @param int $line The line number where the error was detected
	 */
	function AMFException ($code, $description, $file, $line, $detailCode = 'AMFPHP_RUNTIME_ERROR') {
		$this->code = $detailCode;
		$this->description = $description; // pass the description    
		$this->details = $file; // pass the details
		$this->level = AMFException::getFriendlyError($code); 
		$this->line = $line; // pass the line number
	}
	
	/**
	 * throwException provides the means to raise an exception.  This method will 
	 * stop the further execution of the remote method, but not hault the execution
	 * of the entire process.  Using the built in PHP exception system will stop
	 * the entire process and not allow us to report very detailed information back
	 * to the client, especially if there are multiple methods.
	 * 
	 * When we upgrade to PHP 5, using the try...catch syntax will make this much easier.
	 * 
	 * @static
	 * @param AMFBody $body The AMFBody object to apply the exception to.
	 * @param AMFException @exception The exception object to throw
	 * @see AMFBody
	 */ 
	function throwException (&$body, $exception) {
		$body->responseURI = $body->responseIndex . "/onStatus";
		$results = &$body->getResults();

		$results["description"] = $exception->description;
		$results["details"] = $exception->details;
		$results["level"] = $exception->level;
		$results["line"] = $exception->line;
		$results["code"] = $exception->code;
	} 
	
	function getFriendlyError ($err) {
		$errortype = array (1 => "Error",
			2 => "Warning",
			4 => "Parsing Error",
			8 => "Notice",
			16 => "Core Error",
			32 => "Core Warning",
			64 => "Compile Error",
			128 => "Compile Warning",
			256 => "User Error",
			512 => "User Warning",
			1024 => "User Notice",
			2048 => "Strict error",
			);
		if(isset($errortype[$err]))
		{
			return $errortype[$err];
		}
		else
		{
			return "Unknown error type";
		}
	} 
} 

?>