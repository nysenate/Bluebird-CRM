<?php
	
	class configClass {
		public $id;
		public $displayName;
		public $fileName;
		public $settings;
		public $comment;
		public $help;
	}


// read configuration file
	function readSettings($filename) {
		global $config;
		global $nConfig;
		global $domain;
		$sArray = array();
		
		$handle = @fopen($filename, "r");
		if ($handle) {
		    while (($buffer = fgets($handle, 4096)) != false ) {
		    	$buffer = trim($buffer);
		    	if (strlen($buffer)>0 && $buffer[0]!='#' && $buffer[0]!=';') {
		    		
		    		$sArray = explode("\t", $buffer);			    	
			    	$cfg = new configClass();
			    	$cfg->id = trim($sArray[0]);
			    	$cfg->displayName = $sArray[1];
			    	$cfg->fileName = trim($sArray[2]);
			    	$cfg->settings = trim($sArray[3]);
			    	$cfg->comment = trim($sArray[4]);
			    	$config[$nConfig++] = $cfg;
			    }
			    if ($buffer[0]==';') {                 // setting up the domain
			    	$domain = trim(substr($buffer,1));
			    }
		    }
		}
		fclose($handle);
	}

// read help file
	function readHelpFile($filename) {
		global $config;
		global $nConfig;
		$sArray = array();
		
		$handle = @fopen($filename, "r");
		if ($handle) {
		    while (($buffer = fgets($handle, 4096)) != false ) {
		    	$buffer = trim($buffer);
		    	if (strlen($buffer)>0 && $buffer[0]!='#' && $buffer[0]!=';') {
		    		$sArray = explode("\t", $buffer);
		    		$sArray[0] = trim($sArray[0]);	    	
			    	for ($i=0;$i<$nConfig;$i++)
			    		if ($config[$i]->id == $sArray[0])
			    			$config[$i]->help = trim($sArray[1]);
			    }
		    }
		}
		fclose($handle);
	}


?>