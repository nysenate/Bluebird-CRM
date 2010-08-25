<?php
/**
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: odbcAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */
 
require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class odbcAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * The body of this method was provided by Mario Falomir... Thanks.
	 * 
	 * @param resource $d The datasource resource
	 */
	function odbcAdapter($d) {
		parent::RecordSetAdapter($d);
		// count number of fields
		$fieldcount = odbc_num_fields($d); 
		
		$ob = "";
		$be = $this->isBigEndian;
		$fc = pack('N', $fieldcount);
		
		if(odbc_num_rows($d) > 0)
		{
			$line = odbc_fetch_row($d, 0);
			do {
				// write all of the array elements
				$ob .= "\12" . $fc;
	
				for($i = 1; $i <= $fieldcount; $i++) 
				{ // write all of the array elements
					$value = odbc_result($d, $i);
					if (is_string($value)) 
					{ // type as string
						$os = $this->_directCharsetHandler->transliterate($value);
						//string flag, string length, and string
						$len = strlen($os);
						if($len < 65536)
						{
							$ob .= "\2" . pack('n', $len) . $os;
						}
						else
						{
							$ob .= "\14" . pack('N', $len) . $os;
						}
					}
					elseif (is_float($value) || is_int($value)) 
					{ // type as double
						$b = pack('d', $value); // pack the bytes
						if ($be) { // if we are a big-endian processor
							$r = strrev($b);
						} else { // add the bytes to the output
							$r = $b;
						} 
						$ob .= "\0" . $r;
					} 
					elseif (is_bool($value)) 
					{ //type as bool
						$ob .= "\1";
						$ob .= pack('c', $value);
					} 
					elseif (is_null($value)) 
					{ // null
						$ob .= "\5";
					} 
				} 
			} while ($line = odbc_fetch_row($d)) ;
		}
		$this->serializedData = $ob;
		
		// grab the number of fields
		// loop over all of the fields
		for($i = 1; $i <= $fieldcount; $i++) {
			// decode each field name ready for encoding when it goes through serialization
			// and save each field name into the array
			$this->columnNames[$i - 1] = $this->_directCharsetHandler->transliterate(odbc_field_name($d, $i));
		} 
		$this->numRows = odbc_num_rows($d);
	} 
} 

?>