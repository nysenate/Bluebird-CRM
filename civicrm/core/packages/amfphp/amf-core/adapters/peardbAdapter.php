<?php
/**
 * The newest version of the PearDB adapter includes a hack to type number column
 * types as numbers, despite the fact that PHP does not offer this kind of info by default
 *
 * A contribution of Jaybee Reeves
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: peardbAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class peardbAdapter extends RecordSetAdapter {
	/**
	 * Constructor method for the adapter.  This constructor implements the setting of the
	 * 3 required properties for the object.
	 * 
	 * @param resource $d The datasource resource
	 */
	 
	function peardbAdapter($d) {
		
		parent::RecordSetAdapter($d);
		$fieldcount = $d->numCols();

		//Start fast serializing
		$ob = "";
		$fc = pack('N', $fieldcount);
		
		$be = $this->isBigEndian;
		
		$isint = array();
		$info = $d->dbh->tableInfo($d);
		for($i = 0; $i < $fieldcount; $i++) {
			$this->columnNames[$i] = $this->_charsetHandler->transliterate($info[$i]['name']);
			
			$type = $info[$i]['type'];
			$isint[] = in_array($type, array('int', 'real', 'year'));
		}
		
		$rows = 0;
		if($d->numRows() > 0)
		{
			$line = $d->fetchRow(DB_FETCHMODE_ORDERED, 0);
			do {
				$rows ++;
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
			} while ($line = $d->fetchRow(DB_FETCHMODE_ORDERED, $rows));
		}
		$this->numRows = $rows;

		// just in case the recordset is not capable of returning a record count we will use the 
		if(is_object($this->numRows)) $this->numRows = $rows;
		$this->serializedData = $ob;
	}
}

?>