<?php
/**
 * Array filtered, typed adapter base class
 * 
 * Arrayft is like Arrayf but solves an even more obscure problem:
 * If you are using methods from standard PHP projects (WordPress and the like)
 * Chances are the SQL queries (based on mySQL) are made elsewhere and you will
 * receive not recordsets but arrays of associative arrays from the db abstraction layer
 * Also a lot of times you will receive WAY more columns than you need to send back to Flash
 * Finally, mySQL returns everything as a string which bothers the hell out of some people
 *
 * The solution is to send back the array of associative array, an array
 * of the column names you want to include, and then a string count($columnNames) long
 * of s and d for string and digit respectively for typing purposes
 * 
 * So if you receive an array like this from a db abstraction layer:
 *
 * $data = array(array('id' => '1', 'data' => 'toaster', 'uselessstuff' => 'ASDASDASD'), 
 *      array('id' => '2', 'data' => 'some data'. , 'uselessstuff' => 'ASDASDASD'));
 * 
 * Then you will want to :
 *
 * include_once('adapters/lib/Arrayft.php');
 * return new Arrayft($data, array('id', 'data'). 'ds');
 * 
 * And you will receive this in Flash correctly with the tight types and
 * only the columns you need, and as a mx.remoting.RecordSet for fun!
 * (if you omit param #3, everything will be sent as a string)
 */
class Arrayft
{
	var $data;
	var $filter;
	var $types;

	function Arrayft($data, $filter, $types = NULL)
	{
		$this->data = $data;
		$this->filter = $filter;
		$this->types = $types;
	}
}
?>
