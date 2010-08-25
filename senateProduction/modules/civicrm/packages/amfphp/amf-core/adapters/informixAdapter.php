<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 *
 * Thanks to Andrew Robins for this contribution
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: informixAdapter.php,v 1.2 2005/07/22 10:58:09 pmineault Exp $
 */

require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class informixAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 *
	 * @param resource $d The datasource resource
	 */
	
	function informixAdapter($d) 
	{
		parent::RecordSetAdapter($d);
		
		$ob = "";
		$fieldcount = ifx_num_fields($d);
		$be = $this->isBigEndian;
		$fc = pack('N', $fieldcount);
		
		if(ifx_num_rows($d) > 0)
		{
			$line = ifx_fetch_row($d,"FIRST");
			do 
			{
				//Write inner inner (data) array
				$ob .= "\12" . $fc;
				
				foreach($line as $key => $value) 
				{
					if (is_string($value))
					{ // string
						$os = $this->_directCharsetHandler->transliterate($value);
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
					{ //numberic
						$b = pack('d', $value); // pack the bytes
						if ($be) 
						{ // if we are a big-endian processor
							$r = strrev($b);
						} 
						else 
						{ // add the bytes to the output
							$r = $b;
						}
						$ob .= "\0" . $r;
					}
					elseif (is_bool($value))
					{ //bool
						$ob .= "\1" . pack('c', $value);
					}
					elseif (is_null($value))
					{ // null
						$ob .= "\5";
					}
				}
			} while ($line = ifx_fetch_row($d,"NEXT") );
		}
		
		$this->serializedData = $ob;
		
		$properties=ifx_fieldproperties($d);
		
		for($i = 0; $i < $fieldcount; $i++) {
			$this->columnNames[$i] = $this->_directCharsetHandler->transliterate(key($properties));
			next($properties);
		}
		$this->numRows = ifx_num_rows($d);
	}
}
?> 