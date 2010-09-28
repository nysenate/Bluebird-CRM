<?php
/**
 * AMFBody is a data type that encapsulates all of the various properties a body object can have.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @version $Id: AMFBody.php,v 1.6 2005/07/05 07:40:53 pmineault Exp $
 */

class AMFBody {

	var $targetURI = "";
	var $responseURI = "";
	var $uriClassPath = "";
	var $classPath = "";
	var $className = "";
	var $methodName = "";
	var $responseTarget = "null";
	var $noExec = false;
	
	var $_value = NULL;
	var $_results = NULL;
	var $_classConstruct = NULL;
	var $_specialHandling = NULL;
	var $_metaData = array();
	
	/**
	 * AMFBody is the Contstructor method for the class
	 */
	function AMFBody ($targetURI = "", $responseIndex = "", $value = "") {
		$GLOBALS['amfphp']['lastMethodCall'] = $responseIndex;
		$this->responseIndex = $responseIndex;
		$this->targetURI = $targetURI;
		$this->responseURI = $this->responseIndex . "/onStatus"; // default to the onstatus method
		$this->setValue($value);
	}
	
	/**
	 * setter for the results from the process execution
	 * 
	 * @param mixed $results The returned results from the process execution
	 */
	function setResults ($result) {
		$this->_results = $result;
	} 

	/**
	 * getter for the result of the process execution
	 * 
	 * @return mixed The results
	 */
	function &getResults () {
		return $this->_results;
	} 

	/**
	 * setter for the class construct
	 * 
	 * @param object $classConstruct The instance of the service class
	 */
	function setClassConstruct (&$classConstruct) {
		$this->_classConstruct = &$classConstruct;
	} 

	/**
	 * getter for the class construct
	 * 
	 * @return object The class instance
	 */
	function &getClassConstruct () {
		return $this->_classConstruct;
	} 
	
	/**
	 * setter for the value property
	 * 
	 * @param mixed $value The value of the body object
	 */
	function setValue ($value) {
		$this->_value = $value;
	} 

	/**
	 * getter for the value property
	 * 
	 * @return mixed The value property
	 */
	function &getValue () {
		return $this->_value;
	} 
	
	/**
	 * Set special handling type for this body
	 */
	function setSpecialHandling($type)
	{
		$this->_specialHandling = $type;
	}
	
	/**
	 * Get special handling type for this body
	 */
	function getSpecialHandling()
	{
		return $this->_specialHandling;
	}

	/**
	 * Check if this body is handled special against an array of special cases
	 */
	function isSpecialHandling($against = NULL)
	{
		if($against !== NULL)
		{
			return in_array($this->_specialHandling, $against);
		}
		else
		{
			return ($this->_specialHandling != NULL);
		}
	}
	
	function getFastArrayProcessing()
	{
		return $this->_fastArrayProcessing;
	}
	
	function setFastArrayProcessing($d = true)
	{
		$this->_fastArrayProcessing = $d;
	}
	
	function setMetaData($key, $val)
	{
		$this->_metaData[$key] = $val;
	}
	
	function getMetaData($key)
	{
		if(isset($this->_metaData[$key]))
		{
			return $this->_metaData[$key];
		}
		else
		{
			return NULL;
		}
	}
} 

?>
