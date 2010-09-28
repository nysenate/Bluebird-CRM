<?php
/**
 * The mysqlf adapter is a filtered mySQL adapter riggged 
 * to only transmit certain column names. Must be typed manually.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: mysqlfAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class mysqlfAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	 
	function mysqlfAdapter($d) {
		
		$f = $d['filter'];
		$d = $d['data'];
		parent::RecordSetAdapter($d);
		
		$fieldcount = count($f);
		$truefieldcount = mysql_num_fields($d);
		$be = $this->isBigEndian;
		
		$isintcache = array();
		for($i = 0; $i < $truefieldcount; $i++) {
			//mysql_fetch_* usually returns only strings, 
			//hack it into submission
			$type = mysql_field_type($d, $i);
			$name = mysql_field_name($d, $i);
			$isintcache[$name] = in_array($type, array('int', 'real', 'year'));
		}

		$isint = array();
		for($i = 0; $i < $fieldcount; $i++) {
			$this->columnNames[$i] = $this->_charsetHandler->transliterate($f[$i]);
			$isint[$i] = isset($isintcache[$f[$i]]) && $isintcache[$f[$i]];
		}

		//Start fast serializing
		$ob = "";
		$fc = pack('N', $fieldcount);
		
		if(mysql_num_rows($d) > 0)
		{
			mysql_data_seek($d, 0); 
			while ($line = mysql_fetch_assoc($d)) {
				//Write array flag + length
				$ob .= "\12" . $fc;
				
				$i = 0;
				foreach($f as $key)
				{
					$value = $line[$key];
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
					$i++;
				}
			}
		}
		
		$this->numRows = mysql_num_rows($d);
		$this->serializedData = $ob;
	}
}

?>