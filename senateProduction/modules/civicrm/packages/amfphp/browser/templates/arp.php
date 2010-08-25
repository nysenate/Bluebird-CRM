<?php
class arp
{
	function arp()
	{
		$this->description = "ARP framework";
		$this->author = "Patrick Mineault, Aral Balkan";
		$this->priority = 50;
	}
	
	function format($info)
	{
		$clearLine = "\r\n//-------------------------------------------------------------\r\n";
		$templates = $this->createTemplates($info);
		
		$template = $templates['locator'] . $clearLine;
		$template .= $templates['controller'] . $clearLine;
		
		foreach($templates['commands'] as $commandName => $command)
		{
			$template .= $command . $clearLine;
		}
		
		return $template;
	}
	
	function save($info, $where, $overwrite)
	{
		$templates = $this->createTemplates($info);
		
		$package = str_replace('.', '/', $info['package']);
		
		//First create package hierarchy
		if(!is_dir($where . '/' . $package))
		{
			//Create the directory
			$attempt = @makeDirs($where . '/' . $package);
			if($attempt == FALSE)
			{
				return "could not create directory $where/$package";
			}
		}
		
		chdir($where . '/' . $package);
		
		//Make standard ARP dirs
		$dirs = array('business', 'control', 'command', 'view', 'vo');
		
		foreach($dirs as $dir)
		{
			if(!is_dir($dir))
			{
				//Create the directory
				$attempt = @mkdir($dir);
				if($attempt == FALSE)
				{
					return "Could not create directory $dir, permissions set correctly?"; 
				}
			}
		}
		
		//Save
		$error = "Could not write file ";
		if($overwrite || !file_exists('control/Controller.as'))
		{
			$r = file_put_contents('control/Controller.as', $templates['controller']);
			if($r === FALSE)
			{
				return $error . 'Controller.as';
			}
		}
		
		if($overwrite || !file_exists('business/ServiceLocator.as'))
		{
			$r = file_put_contents('business/ServiceLocator.as', $templates['locator']);
			if($r === FALSE)
			{
				return $error . 'ServiceLocator.as';
			}
		}
		
		foreach($templates['commands'] as $commandName => $command)
		{
			if($overwrite || !file_exists('command/' . ucfirst($commandName . 'Command.as')))
			{
				$r = file_put_contents('command/' . ucfirst($commandName . 'Command.as'), $command);
				if($r === FALSE)
				{
					return $error . ucfirst($commandName . 'Command.as');
				}
			}
		}
		
		return true;
	}
	
	function createTemplates($info)
	{
		$commands = array();
		$templates = array();
		$templates['commands'] = array();
		foreach($info['methods'] as $methodName => $method)
		{
			ob_start();
			include(dirname(__FILE__)."/arpcommand.tpl");
			$commands[] = $method['methodName'] . 'Command';
			$templates['commands'][$method['methodName']] = ob_get_clean();
		}

		ob_start();
		include(dirname(__FILE__)."/arpcontroller.tpl");
		$templates['controller'] = ob_get_clean();

		ob_start();
		include(dirname(__FILE__)."/arpservicelocator.tpl");
		$templates['locator'] = ob_get_clean();

		return $templates;
	}
}