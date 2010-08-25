<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: mssqlAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */
 
require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class mssqlAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	function mssqlAdapter($d) {
		parent::RecordSetAdapter($d);

		$fieldcount = mssql_num_fields($d); // grab the number of fields

		$ob = "";
		$be = $this->isBigEndian;
		$fc = pack('N', $fieldcount);
		
		if(mssql_num_rows($d) > 0)
		{
			mssql_data_seek($d, 0);
			while ($line = mssql_fetch_row($d)) {
				// write all of the array elements
				$ob .= "\12" . $fc;
	
				foreach($line as $value)
				{ // write all of the array elements
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
			}
		}
		$this->serializedData = $ob;
		
		for($i = 0; $i < $fieldcount; $i++) { // loop over all of the fields
			$this->columnNames[] = $this->_directCharsetHandler->transliterate(mssql_field_name($d, $i));
		} 
		$this->numRows = mssql_num_rows($d);
	} 
} 

?>