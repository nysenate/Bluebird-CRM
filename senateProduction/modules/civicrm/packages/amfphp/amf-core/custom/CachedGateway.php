<?php

//This cached gateway is meant to override the standard Gateway and overrides most of the 
//actions, keeping only one CachedExecutionAction

include_once("app/Gateway.php");
include_once(AMFPHP_BASE . "custom/CachedExecutionAction.php");

class CachedGateway extends Gateway
{
	function registerActionChain()
	{
		$this->actions['adapter'] = new AdapterAction();
		$this->actions['exec'] = new CachedExecutionAction();   
	}
}

?>