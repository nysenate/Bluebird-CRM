<?php
/**
 * The VerboseException class adds level, code, file, and line info to a regular exception
 * so that PHP5 errors are as verbose as possible
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage exception
 * @author Justin Watkins Original Design 
 * @version $Id: AMFException.php,v 1.2 2005/04/02 18:37:23 pmineault Exp $
 */
 
class VerboseException extends Exception
{
	public $description;
	public $level;
	public $file;
	public $line;
	public $code;
	
	function VerboseException($string, $level, $file, $line)
	{
		$this->description = $string;
		$this->level = $level;
		$this->code = "AMFPHP_RUNTIME_ERROR";
		$this->file = $file;
		$this->line = $line;
		Exception::__construct($string);
	}
}

function amfErrorHandler($level, $string, $file, $line, $context)
{
	//forget about errors not defined at reported
	$amfphpErrorLevel = $GLOBALS['amfphp']['errorLevel'];

	if( ($amfphpErrorLevel | $level) == $amfphpErrorLevel )
	{
		throw new VerboseException($string, $level, $file, $line);
	}
}

set_error_handler("amfErrorHandler");
?>