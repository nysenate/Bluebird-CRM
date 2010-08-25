<?php
/**
 * The newest version of the MySQL adapter includes a hack to type number column
 * types as numbers, despite the fact that PHP does not offer this kind of info by default
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: mysqlAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class mysqlAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	 
	function mysqlAdapter($d) {
		
		parent::RecordSetAdapter($d);
		$fieldcount = mysql_num_fields($d);

		//Start fast serializing
		$ob = "";
		$fc = pack('N', $fieldcount);
		
		$be = $this->isBigEndian;
		
		$isint = array();
		for($i = 0; $i < $fieldcount; $i++) {
			$this->columnNames[$i] = $this->_charsetHandler->transliterate(mysql_field_name($d, $i));
			
			//mysql_fetch_* usually returns only strings, 
			//hack it into submission
			$type = mysql_field_type($d, $i);
			$isint[] = in_array($type, array('int', 'real', 'year'));
		}
		
		if($d && mysql_num_rows($d) > 0)
		{
			mysql_data_seek($d, 0); 
			while ($line = mysql_fetch_row($d)) {
				//Write array flag + length
				$ob .= "\12" . $fc;
				
				$to = count($line);
				for($i = 0; $i < $to; $i++)
				{
					$value = $line[$i];
					if(!$isint[$i]) //type as string
					{
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
					else //type as num
					{
						$b = pack('d', $value); // pack the bytes
						if ($be) { // if we are a big-endian processor
							$r = strrev($b);
						} else { // add the bytes to the output
							$r = $b;
						}
						$ob .= "\0" . $r; 
					}
				}
				
			}
		}
		
		$this->numRows = mysql_num_rows($d);
		$this->serializedData = $ob;
	}
}

?>