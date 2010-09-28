<?php
/**
 * Arrayf was designed to solve the following problem:
 * Say you have to maipulate SQL data in such a way that it is 
 * impossible or impractical to do in pure SQL, so that you have 
 * to process it using PHP. Then you don't have a mysql recordset to
 * return, but rather a processed array of associative arrays, such as:
 *
 * $data = array(array('id' => 1, 'data' => 'toaster'), 
 *      array('id' => 2, 'data' => 'some data'));
 *
 * You want to return this to Flash as a RecordSet object
 * Then you use this:
 *
 * include_once(AMFPHP_BASE . "adapters/lib/Arrayf.php");
 * 
 * then in your method:
 * 
 * return new Arrayf($data, array_keys($data[0]));
 * 
 * And thus the arrayfAdapter.php will be invoked and it will 
 * work just as if you used return mysql_query('SELECT * FROM daTable')
 *
 * The second parameter is an array of columns you want to return
 * (usually you can use array_keys on the first item in the array,
 *  but sometimes you will want only some columns returned)
 *
 * The f is for filtered
 */
class Arrayf
{
	var $data;
	var $filter;
	var $types;
	
	function Arrayf($data, $filter)
	{
		$this->data = $data;
		$this->filter = $filter;
	}
}
?>