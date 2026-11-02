<?php

class BluebirdSeleniumSettings {

	var $publicSandbox  = false;

	var $browser = '*chrome';

	var $sandboxURL = 'http://sd99/';

	var $sandboxPATH = '';
	
	var $username = '';

	var $password = '';

	var $adminUsername = '';
	
	var $adminPassword = '';

    var $UFemail = '';

    var $sleepTime = 0;
    
	function __construct() {
		$this->fullSandboxPath = $this->sandboxURL . $this->sandboxPATH;
	}
}

?>
