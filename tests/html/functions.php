<?php
	
	$mysql_host = 'localhost';
	$mysql_user = 'root';
	$mysql_pwd  = 'mysql';
	$mysql_db   = 'selenium';
	$dir		= "c:\selenium\\";
	$phpunit    = "c:\wamp\phpunit";
	$tempfile   = 'temp.log';

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
			    	$temp = explode("\t",trim(substr($buffer,1)));
			    	$domain[] = array('id'=>trim($temp[0]), 'domain'=>trim($temp[1]));
			    }
		    }
		}
		fclose($handle);
	}


// read databases list
// this function is not necessary, because now we get data through API
	function readDBlist($filename) {
		global $domain;
		global $list;
		$handle = @fopen($filename, "r");
		if ($handle) {
		   	while (($buffer = fgets($handle, 4096)) != false ) {
		   		$buffer = trim($buffer);
		   		if (strlen($buffer)>0 && $buffer[0]!='#') {
		   			$sArray = explode("\t", $buffer);
		   			$sArray[0] = trim($sArray[0]);
		   			$sArray[1] = trim($sArray[1]);
		   			$list[$sArray[0]][] = $sArray[1];
		   		}
		   	}
		}
		fclose($handle);
	}// function


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

	function dump($data) {
		global $mysql_connect;
		global $mysql_user;
		global $mysql_pwd;
		global $mysql_db;
		global $tempfile;

		$link = mysql_connect($mysql_host, $mysql_user, $mysql_pwd);
		mysql_select_db($mysql_db);

	    $result = mysql_query("SELECT `tid` FROM `test` WHERE `time` = (SELECT MAX(`time`) FROM `test`);", $link);
	    $record = mysql_fetch_array($result);
	    $record = $record['tid'];

		$log = '';
		foreach($data as $d)
			$log .= $d . "<br/>";
		$log = addslashes($log);

		$query = "INSERT INTO `log`(`tid`,`text`) VALUES ('$record', '$log'); ";
		mysql_query($query, $link);
		mysql_close($link);

		unlink($tempfile);
	}

	function readInstances(){
		global $domain;
		global $list;
		
		foreach ($domain as $d) {
			//////// LOCAL ENVIRONMENT ////////
			if ($d['domain']=='/') $url = 'crm/api/1.0/getInstances'; else {
			///////////////////////////////////
			if ($d['domain'][0]=='.')
				$url = substr($d['domain'],1);
				else $url = $d['domain'];
			if ($d['domain'][strlen($d['domain'])-1]=='/')
				$url .= 'api/1.0/getInstances';
				else $url .= '/api/1.0/getInstances';
			} // else
			$content = getFile($url);
			$data = explode(' ', $content);
			foreach ($data as $dat) {
				$list[$d['id']][] = trim($dat);
			}
		}
}

function getFile($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$tmp = curl_exec($ch);
	curl_close($ch);
	if ($tmp != false){
		return $tmp;
	}
}

?>