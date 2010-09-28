<?php
/**
 * The NetDebug class includes a NetDebug::trace function that works
 * like the Flash one
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @version $Id: NetDebug.php,v 1.1 2005/03/24 22:19:48 pmineault Exp $
 */

class NetDebug 
{
	/**
	 * Don't do anything, just in case something pops up that needs to be initialized
	 */
	function initialize()
	{
		
	}
	
	/**
	 * A static function that traces stuff in the NetDebug window
	 * 
	 * Note emulation of static variables
	 */
	function trace($what)
	{
		NetDebug::getTraceStack($what);
	}
		
	function getTraceStack($val=NULL)
	{
		static $traceStack = array();
		if($val !== NULL)
		{
			$traceStack[] = $val;
		}
		return $traceStack;
	}
} 

?>