<?php

// 	=][= 2-17-1

class BluebirdSeleniumSettings {

	var $publicSandbox  = false;

	var $browser = '*firefox';

	var $sandboxURL = 'http://sd99/';

	var $sandboxPATH = '';
	
	var $username = 'demo';

	var $password = 'demo';

	var $adminUsername = 'senateroot';
	
	var $adminPassword = 'mysql';

    var $UFemail = 'noreply@civicrm.org';

    var $sleepTime = 0;

	function __construct() {
		$this->fullSandboxPath = $this->sandboxURL . $this->sandboxPATH;
	}
}

?>
