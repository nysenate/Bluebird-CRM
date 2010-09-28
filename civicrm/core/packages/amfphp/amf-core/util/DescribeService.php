<?php
/**
 * The DescribeService is used to provide a description of the class
 * to the service browser
 *
 * This file was adapted from the old RemotingService which was a pretty
 * nasty idea all along
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @version $Id: DescribeService.php,v 1.1 2005/04/02 18:41:49 pmineault Exp $
 */

class DescribeService
{
	/**
	 * Describes the service
	 */
	function describe(&$service, $name)
	{
		$description = array();
		$description["version"] = "1.0";
		$description["address"] = $name;
		$description["functions"] = array();

		foreach ($service->methodTable as $key => $value) {
			if ($value["access"] = "remote")    {
				$args = array();
				if(is_array($value["arguments"]) && count($value["arguments"]) >= 1)
				{
					foreach($value["arguments"] as $key2 => $arg)
					{
						if(is_array($arg))
						{
						$args[] = array("name" => $key2,
								  "required" => $arg['required'] ? 'true' : 'false',
								  "type" => $arg['type'],
								  "description" => $arg['description']
								  );
						}
						else
						{
						$args[] = array("name" => $arg,
								  "required" => "true",
								  "type" => "undefined");
						}
					}
				}
				
				if( !isset( $value["returns"] ) )
				{
					$returns = 'undefined';
				}
				else
				{
					$returns = $value["returns"];
				}
				
				$description["functions"][] = array(
					"description" => $value["description"],
					"name" => $key,
					"version" => "1.0",
					"returns" => $returns,
					"arguments" => $args
				);
			}
		}
		return $description;
	}
}

?>