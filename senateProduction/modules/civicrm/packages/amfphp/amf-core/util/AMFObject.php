<?php
/**
 * AMFObject is a datatype representing the parsed representation of the binary AMF data.
 * 
 * This object contains 2 major sections, headers and bodys.  Headers contain all of the 
 * header keys along with their associated data and body elements which include the target
 * URI, the response URI and the data to pass to the method.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @version $Id: AMFObject.php,v 1.14 2005/07/05 07:40:53 pmineault Exp $
 */

class AMFObject {
	/**
	 * The place to keep the headers data
	 * 
	 * @access private 
	 * @var array 
	 */
	var $_incomingHeaders;

	/**
	 * The variable to store outgoing headers
	 * 
	 * @access private 
	 * @var array 
	 */
	var $_outgoingHeaders;

	/**
	 * The header table is a quick lookup table for
	 * a header by it's key
	 */
	var $_headerTable;

	/**
	 * The place to keep the body elements
	 * 
	 * @access private 
	 * @var array 
	 */
	var $_bodys;
	
	var $outputStream;

	/**
	 * The constructor function for a new amf object.
	 * 
	 * All the constructor does is initialize the headers and bodys containers
	 */
	function AMFObject($rawData = NULL) {
		$this->rawData = $rawData;
		$this->outputStream = "";
		$this->_incomingHeaders = array();
		$this->_outgoingHeaders = array();
		$this->_bodys = array();
		$this->_headerTable = array();
	} 

	/**
	 * addHeader places a new header into the pool of headers.
	 * 
	 * Each header has 3 properties, they header key, the required flag
	 * and the data associated with the header.
	 * 
	 * @param object $header The AMFHeader object to add to the list
	 */
	function addHeader(&$header) {
		//$len = array_push($this->_incomingHeaders, $header);
		$this->_incomingHeaders[] = $header;
		$name = $header->name;
		$this->_headerTable[$name] = $header;
	} 

	/**
	 * addOutgoingHeader places a new header into the pool of outbound headers.
	 * 
	 * Each header has 3 properties, they header key, the required flag
	 * and the data associated with the header.
	 * 
	 * @param object $header The AMFHeader object to add to the list
	 */
	function addOutgoingHeader(&$header) {
		//$len = array_push($this->_outgoingHeaders, $header);
		$this->_outgoingHeaders[] = $header;
	} 

	/**
	 * getHeader returns a header record for a given key
	 * 
	 * @param string $key The header key
	 * @return mixed The header record
	 */
	function getHeader ($key) {
		if (isset($this->_headerTable[$key])) {
			return $this->_headerTable[$key];
		} 
		return false;
	} 

	/**
	 * Gets the number of headers for this amf packet
	 * 
	 * @return int The header count
	 */
	function numHeader() {
		return count($this->_incomingHeaders);
	} 

	/**
	 * Gets the number of outgoing headers for this amf packet
	 * 
	 * @return int The header count
	 */
	function numOutgoingHeader() {
		return count($this->_outgoingHeaders);
	} 

	/**
	 * Get the header at the specified position.
	 * 
	 * If you pass an id this method will return the header
	 * located at that id, otherwise it will return the first header
	 * 
	 * @param int $id Optional id field
	 * @return array The header object
	 */
	function &getHeaderAt($id = 0) {
		return $this->_incomingHeaders[$id];
	} 

	/**
	 * Get the header at the specified position from the outgoing header queue.
	 * 
	 * If you pass an id this method will return the header
	 * located at that id, otherwise it will return the first header
	 * 
	 * @param int $id Optional id field
	 * @return array The header object
	 */
	function &getOutgoingHeaderAt($id = 0) {
		return $this->_outgoingHeaders[$id];
	} 

	/**
	 * addBody has the job of adding a new body element to the bodys array.
	 * 
	 * @param string $t The target URI
	 * @param string $r The response URI
	 * @param mixed $v The value of the object
	 * @param string $ty The type of the results
	 * @param int $ps The pagesize of a recordset
	 */
	function addBody($body) {
		$this->_bodys[] = $body;
	} 

	/**
	 * addBodyAt provides an interface to push a body element to a desired
	 * position in the array.
	 * 
	 * @param int $pos The position to add the body element
	 * @param AMFBody $body The body element to add
	 */
	function addBodyAt($pos, $body) {
		array_splice($this->_bodys, $pos, 0, array($body)); // splice the new body into the array
	} 
	
	/**
	 * removeBodyAt provides an interface to remove a body element to a desired
	 * position in the array.
	 * 
	 * @param int $pos The position to add the body element
	 * @param AMFBody $body The body element to add
	 */
	function removeBodyAt($pos) {
		array_splice($this->_bodys, $pos, 1); // splice the new body into the array
	} 

	/**
	 * numBody returns the total number of body elements.  There is one body
	 * element for each method call.
	 * 
	 * @return int The number of body elements
	 */
	function numBody() {
		return count($this->_bodys);
	} 

	/**
	 * getBodyAt returns the current body element the specified position.
	 * 
	 * If a integer is passed this method will return the element at the given position.
	 * Otherwise the first element will be returned.
	 * 
	 * @param int $id The id of the body element desired
	 * @return array The body element
	 */
	function &getBodyAt($id = 0) {
		return $this->_bodys[$id];
	} 
} 

?>