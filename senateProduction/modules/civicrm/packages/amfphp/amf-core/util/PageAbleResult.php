<?php
/**
 * PageAbleResult is an AMFPHP service class which is used internally by AMFPHP
 * to provide support for pageable recordsets. The methods of PageAbleResult
 * are called automatically by the Flash player when implementing pageable
 * recordsets. To use pageable recordsets the developer need only
 * include the pagesize value in the service class method table and use
 * setDeliveryMode in the Flash client.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage sql
 * @version $Id: PageAbleResult.php,v 1.2 2005/07/05 07:40:53 pmineault Exp $
 */

class PageAbleResult {
	/**
	 * Constructor function.
	 * 
	 * Contains the methodTable data and sets getRecords to return a record set page
	 * instead of a normal array.
	 */
	function PageAbleResult() {
		$this->methodTable = array("getRecords" => array("access" => "remote",
				"returns" => "__RECORDSETPAGE__"
				),
			"release" => array("access" => "remote"
				)
			);
	} 
	/**
	 * Collects the page of the recordset from the session and returns it along
	 * with the cursor position of the first record.
	 * 
	 * @param string $id The session id
	 * @param int $c The cursor position
	 * @param int $ps The page size
	 * @return array Contains the cursor position of the first record and the page data
	 */
	function getRecords($id, $c, $ps) {
		$keys = explode("=", $id);
		$currset = intval($keys[1]);
		session_id($keys[0]);
		session_start();
		$pageData = array();
		$pageData['Cursor'] = $c;
		$pageData['Page'] = array_slice($_SESSION['amfphp_recordsets'][$currset]['data'], $c - 1, $ps);

		for($i = 0; $i < $ps; $i++)
		{
			$_SESSION['amfphp_recordsets'][$currset]['indexes'][$c + $i] = true;
		}
		return $pageData;
	} 
	/**
	 * Unsets the recordset data from the session
	 * Flash, for some reason does not give back the recordid, so it's  difficult to see
	 * what exactly is going on, this is why we store sent data in another session var
	 *
	 */
	function release() {
		foreach($_SESSION['amfphp_recordsets'] as $key => $value)
		{
			$found = false;
			foreach($value['indexes'] as $recordid => $recordsent)
			{
				if(!$recordsent)
				{
					$found = true;
					break;
				}
			}
			if(!$found)
			{
				//Release recordset
				unset($_SESSION['amfphp_recordsets'][$key]);
			}
		}
		return;
	} 
} 

?>