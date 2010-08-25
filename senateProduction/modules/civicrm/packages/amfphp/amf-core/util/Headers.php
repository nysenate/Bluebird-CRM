<?php
/**
 * The Headers class includes a static method getHeader available from all services
 * that allows one to get an AMF header from any service 
 * like the Flash one
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @version $Id: Headers.php,v 1.1 2005/07/05 07:40:54 pmineault Exp $
 */

class Headers 
{   
	function setHeader($key=NULL, $val=NULL)
	{
		static $headers = array();
		if($val !== NULL)
		{
			$headers[$key] = $val;
		}
		return $headers[$key];
	}
	
	function getHeader($key)
	{
		return Headers::setHeader($key);
	}
} 

?>