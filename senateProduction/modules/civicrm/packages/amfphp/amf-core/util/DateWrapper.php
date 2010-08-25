<?php
/**
 * The DateWrapper allows easy handling of Flash dates 
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @version $Id: DateWrapper.php,v 1.1 2005/03/24 22:19:48 pmineault Exp $
 */

class DateWrapper 
{
	var $_date;
	/** 
	 * Contructor
	 */
	function DateWrapper($input = "")
	{
		if(is_int($input) || is_float($input))
		{
			$this->_date = $input/1000;
		}
		else
		{
			$this->_date = time();
		}
	}
	
	/**
	 * Get date according to client timezone
	 */
	function getClientDate()
	{
		return $this->_date + DateWrapper::getTimezone();
	}
	
	/**
	 * Get date according to server timezone
	 */
	function getServerDate()
	{
		return ($this->_date + date("Z"));
	}
	
	/**
	 * Get raw date
	 */
	function getRawDate()
	{
		return $this->_date;
	}
	
	/**
	 * Set utc date
	 */
	function setDate($input)
	{
		$this->_date = $input;
	}
	
	/**
	 * Get timezone
	 */
	function getTimezone($val=NULL)
	{
		static $timezone = 0;
		if($val != NULL)
		{
			$timezone = $val;
		}
		return $timezone;
	}
	
	/**
	 * Set timezone
	 */
	function setTimezone($val=0){
		return DateWrapper::getTimezone($val);
	}
} 

?>