<?php
/**
 * This	Adapter	translates the specific	Database type links	to the data	and	pulls the data into	very
 * specific	local variables	to later be	retrieved by the gateway and returned to the client.
 *
 * Correct typing for MySQL databases contributed by Patrick Gutlich
 * 
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package	flashservices
 * @subpackage adapters
 * @version	$Id: adodbAdapter.php,v	1.2	2005/07/22 10:58:09	pmineault Exp $
 */
 
require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class adodbAdapter extends RecordSetAdapter	{
	/**
	 * Constructor method for the adapter.	This constructor implements	the	setting	of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	
	function adodbAdapter($d) {
		parent::RecordSetAdapter($d);
		$fieldcount	= $d->FieldCount();	// grab	the	number of fields
		
		$ob	= "";
		$be	= $this->isBigEndian;
		$fc	= pack('N',	$fieldcount);
		
		if($d->RecordCount() > 0)
		{
			$d->MoveFirst();
			while ($line = $d->FetchRow()) {
				// write all of	the	array elements
				$ob	.= "\12" . $fc;
			
				for ($n=0; $n < $d->Fieldcount(); $n++)
				{ 
					$value 		= $line[$n];
					$fieldObj 	= $d->FetchField($n);
					$fieldtype 	= $d->MetaType($fieldObj->type);
					switch($fieldtype){
						case "C":
						case "X":
						case "D":
						case "T":
						case "B":
							$type = "string";
						break;
						case "N":
						case "I":
						case "R": 
							$type = "number";
						break;
						case "L":
							$type = "boolean";
						break;
					}
					// write all of the array	elements
					
					if ($type=="string") 
					{ // type as string
						$os	= $this->_directCharsetHandler->transliterate($value);
						//string flag, string length, and string
						$len = strlen($os);
						if($len	< 65536)
						{
							$ob	.= "\2"	. pack('n',	$len) .	$os ;
						}
						else
						{
							$ob	.= "\14" . pack('N', $len) . $os;
						}
					}
					
					elseif ($type=="number")	
					{ // type as double
						$b = pack('d', $value);	// pack	the	bytes
						if ($be) { // if we	are	a big-endian processor
							$r = strrev($b);
						} else { //	add	the	bytes to the output
							$r = $b;
						} 
						$ob	.= "\0"	. $r;
					} 
					elseif ($type=="boolean") 
					{ //type as	bool
						$ob	.= "\1";
						$ob	.= pack('c', $value);
					} 
					elseif (is_null($value)) 
					{ // null
						$ob	.= "\5";
					} 
				} 
			} 
		}
		$this->serializedData =	$ob;
		
		for($i = 0;	$i < $fieldcount; $i++)	{ // loop over all of the fields
			$fld = $d->FetchField($i);
			$this->columnNames[] = $this->_directCharsetHandler->transliterate($fld->name);
		} 
		$this->numRows = $d->RecordCount();
	} 
} 

?>