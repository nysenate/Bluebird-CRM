<?php
/**
 * This Adapter translates the specific Database type links to the data and pulls the data into very
 * specific local variables to later be retrieved by the gateway and returned to the client.
 *
 * pdoAdapter is a contribution of Andrea Giammarchi
 *
 * Now using fast serialization
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage adapters
 * @version $Id: sqliteAdapter.php,v 1.1 2005/07/05 07:56:29 pmineault Exp $
 */

require_once(AMFPHP_BASE . "adapters/RecordSetAdapter.php");

class pdoAdapter extends RecordSetAdapter 
{
	function pdoAdapter($d) {
		parent::RecordSetAdapter($d);
		$ob = "";
		$fc = pack('N', $d->columnCount());
		$this->numRows = 0;
		
		if($d->rowCount() > 0)
		{
			$line = $d->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, 0);
			do {
				if($this->numRows === 0)
					$c_index = 0;
				$ob .= "\12" . $fc;
				foreach ($line as $k => &$v) {
					if ($this->numRows === 0)
						$this->columnNames[$c_index++] = $this->_directCharsetHandler->transliterate($k);
					if (is_string($v)) { // actually PDO ( and PDOStatement too ) doesn't have a fieldType method
						$os = $this->_directCharsetHandler->transliterate($v);
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
					else {
						$b = pack('d', $v);
						if ($this->isBigEndian)
							$r = strrev($b);
						else
							$r = $b;
						$ob .= "\0" . $r;
					}
				}
				$this->numRows++;
			} while ($line = $d->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) ;
		}
		$this->serializedData = $ob;
	}
}
?>