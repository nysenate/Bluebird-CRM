<?php
class RecordSet
{
	function init()
	{
		$this->data = array();
		foreach($this->serverInfo['initialData'] as $key => $val)
		{
			foreach($this->serverInfo['columnNames'] as $key2 => $val2)
			{
				$this->data[$key][$val2] = $val[$key2];
			}
		}
		unset($this->serverInfo);
	}
}
?>