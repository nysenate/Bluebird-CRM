<?php
require_once(AMFPHP_BASE . "util/NetDebug.php");
require_once(AMFPHP_BASE . "adapters/lib/Arrayf.php");

class CachedExecutionAction
{
	function doAction (&$bodyObj) 
	{
		$className = str_replace('.php', '', str_replace('/', '.', $bodyObj->getUriClassPath()));
		$method = $bodyObj->getMethodName();
		$args = $bodyObj->getValue();

		if (!$bodyObj->getIgnoreExecution()) 
		{
			if($bodyObj->getIsDynamicPage())
			{
				$offset = $args[count($args) - 2] - 1;
				$limit = $args[count($args) -1];
				array_splice($args, -2);
			}
			else
			{
				$offset = 0;
				$limit = 3;
			}
			
			try
			{
				$records = $this->getRecords($className, $method, $args);
			}
			catch(Exception $fault)
			{
				$ex = new AMFException(E_USER_ERROR, $fault->getMessage(), $fault->getFile(), $fault->getLine());
				$records = '__amfphp_error';
				AMFException::throwException($bodyObj, $ex);
			}
			
			if($records !== '__amfphp_error')
			{
				$dataSet = array_slice($records, $offset, $limit);
				$keys = array_keys($dataSet[0]);
				array_pop($keys);
				
				if($bodyObj->getIsDynamicPage())
				{
					$results = array("cursor" => $args[count($args) - 2] + 1,
									 "data" => new Arrayf($dataSet, $keys));
					$bodyObj->setType("__DYNAMIC_PAGE__");
				}
				else
				{
					$results = array('class' => $bodyObj->getUriClassPath(), 
									 'method' => $bodyObj->getMethodName(), 
									 'count' => count($records), 
									 "args" => $args,
									 "data" => new Arrayf($dataSet, $keys));
					$bodyObj->setType('__DYNAMIC_PAGEABLE_RESULTSET__');
				}
				$bodyObj->setResults($results);
				$bodyObj->setResponseURI($bodyObj->getResponseIndex() . "/onResult");               
			}
		}
		else
		{
			if($bodyObj->getIsDynamicPage())
			{
				$bodyObj->setResults(true);
				$bodyObj->setType('boolean');
				$bodyObj->setResponseURI($bodyObj->getResponseIndex() . "/onResult");
			}
		}
		return true;
	}
	
	function getRecords($className, $method, $args)
	{
		$sig = array(
				'className' => $className,
				'method' => $method,
				'args' => $args);
		$key = md5(serialize($sig));
		
		//Use this as a key in mySQL
		mysql_pconnect('localhost', 'root', '');
		mysql_select_db('wcd');
		$rs = mysql_query(sprintf("SELECT * FROM wcd_cache where sig = '%s'", $key));
		$row = mysql_fetch_assoc($rs);
		$count = mysql_num_rows($rs);
		
		if($count == 1)
		{
			//Found correctly
			$rows = unserialize($row['results']);
			return $rows;
		}
		else
		{
			throw new Exception("Not found in cache: " . serialize($sig));
			return false;
		}
	}
}
?>