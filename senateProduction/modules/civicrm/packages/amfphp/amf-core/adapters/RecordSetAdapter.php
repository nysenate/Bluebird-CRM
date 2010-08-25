<?php
/**
 * RecordSetAdapter is the superclass of all database adapter implementations.
 * 
 * To keep the apadters encapsulated, the  getter methods have been added to
 * this superclass instead of direct property access.  This superclass is really "abstract"
 * even though abstraction isn't supported until PHP5.  This class must be extended
 * and an implementation to set the 3 properties needs to be defined.
 * 
 * The implementation for setting the 3 properties can be defined either in the constructor
 * or by overwriting the getter methods.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: RecordSetAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

class RecordSetAdapter {
	/**
	 * The 2 dimensional array representing the recordset data
	 * 
	 * @access private 
	 * @var array 
	 */
	var $initialData = array();

	/**
	 * An array with the column names in the same order they appear in
	 * the initialData object
	 * 
	 * @access private 
	 * @var array 
	 */
	var $columnNames = array();

	/**
	 * The number of rows in this recordset
	 * 
	 * @access private 
	 * @var int 
	 */
	var $numRows;

	/**
	 * The session id of the saved recordset
	 * 
	 * @access private 
	 * @var int 
	 */
	var $id;
	/**
	 * Dummy constructor function.
	 * 
	 * @param resource $d The result resource
	 */

	function RecordSetAdapter ($d) {
		$this->_resultResource = $d;
		$this->_charsetHandler = new CharsetHandler('sqltophp');
		$this->_directCharsetHandler = new CharsetHandler('sqltoflash');
		$this->isBigEndian = AMFPHP_BIG_ENDIAN;
	} 
	/**
	 * Saves the full recordset into the session.
	 * 
	 * The session is started normally to let php negotiate the automatic generation of
	 * session id's.  An array within the session is stored to make getting
	 * multiple recordsets on the same request possible.  The key of the array is
	 * appended to the end of the session key seperated by an = sign to make
	 * exploding simple on the return trip.
	 */
	function _saveRecordSet($paging) 
	{
		if (!isset($_SESSION['amfphp_recordsets'])) 
		{ // has the session been started
			$_SESSION['amfphp_recordsets'] = array(); // create the recordsets array
		}
		$_SESSION['amfphp_recordsets'][] = array(
					"numconsumed" => min($paging['ps'], $paging['count']), 
					"mode"    => 'dynamic',
					"args"    => $paging['args'],
					"method"  => $paging['method'],
					"class"   => $paging['class']
		);
		$this->id = session_id(); // start with the session id
		$this->id .= "=" . (count($_SESSION['amfphp_recordsets'])-1); // add the position of this recordset
	} 
	/**
	 * getter for the recordset data
	 * If the pagesize value is set in the method table it saves the recordset
	 * and returns a paged recordset of the stated size, otherwise it just 
	 * returns the whole recordset.
	 * 
	 * ::TODO:: add error handling for if a session could not be created.
	 * if no sessions are available then just dump the entire payload.
	 * 
	 * @param int $ps The pagesize
	 * @return array The recordset data
	 */
	function getRecordSet($paging = -1)
	{
		if ($paging != -1) {
			$this->_saveRecordSet($paging);
			return array_slice($this->initialData, 0, $paging['ps']);
		} else {
			return $this->initialData;
		} 
	}

	/**
	 * getter for the number of rows
	 * 
	 * @return int The number of rows
	 */
	function getRowCount () {
		return $this->numRows;
	} 
	

	/**
	 * getter for the column names array
	 * 
	 * @return array The column names
	 */
	function getColumnNames () {
		return $this->columnNames;
	} 

	/**
	 * getter for the id
	 * 
	 * @return string The id to this result set
	 */
	function getID() {
		return $this->id;
	} 
} 

?>