<?php

require_once 'CRM/Core/Page.php';

class CRM_Test_Page_Sage extends CRM_Core_Page {
	static public function preProcess() {
		$this->assign( 'tplFile' 'CRM/Test/Page/Sage.tpl' );
	}
}

?>